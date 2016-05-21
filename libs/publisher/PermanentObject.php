<?php
using('sqladapter.SQLAdapter');

/** The permanent object class
 * Manage a permanent object using the SQL Adapter.
 */
abstract class PermanentObject {
	
	//Attributes
	protected static $IDFIELD			= 'id';
	protected static $instances			= array();
	
	protected static $table				= null;
	protected static $DBInstance		= null;
	// Contains all fields
	protected static $fields			= array();
	// Contains fields editables by users
	protected static $editableFields	= null;
	// Contains the validator. The default one is an array system.
	protected static $validator			= array();//! See checkUserInput()
	// Contains the domain. Used as default UserException domain.
	protected static $domain			= null;
	
	protected $modFields	= array();
	protected $data			= array();
	protected $isDeleted	= false;
	
	/**
	 * Internal static initialization
	 */
	public static function selfInit() {
		static::$fields = array(static::$IDFIELD);
	}
	
	/**
	 * Return the object as array
	 * @param array $array
	 * @return array The resulting array
	 */
	public static function set2Array(array $array) {
		foreach( $array as &$value ) {
			$value	= $value->getValue();
		}
		return $array;
	}
	
	/**
	 * Insert this object in the given array using its ID as key
	 * @param array $array
	 */
	public function setTo(array &$array) {
		$array[$this->id()]	= $this;
	}
	
	// *** OVERRIDDEN METHODS ***
	
	/** Constructor
	 * @param $data An array of the object's data to construct
	 */
	public function __construct(array $data) {
		foreach( static::$fields as $fieldname ) {
			// We condiser null as a valid value.
			$fieldValue = null;
			if( !array_key_exists($fieldname, $data) ) {
				// Data not found but should be, this object is out of date
// 				$this->reload();// Dont reload here
				// Data not in DB, this class is invalid
// 				if( ENTITY_CLASS_CHECK && !array_key_exists($fieldname, $data) ) {
				if( ENTITY_CLASS_CHECK ) {
					throw new Exception('The class '.static::getClass().' is out of date, the field "'.$fieldname.'" is unknown in database.');
// 				} else {
// 					$this->data[$fieldname]	= null;
				}
			} else {
				$fieldValue	= $data[$fieldname];
// 				$this->data[$fieldname] = $data[$fieldname];
			}
			$this->data[$fieldname] = $this->parseFieldValue($fieldname, $fieldValue);
		}
// 		$this->modFields = array();
		$this->clearModifiedFields();
		if( DEV_VERSION ) {
			$this->checkIntegrity();
		}
	}
	
	/** Destructor
	 * If something was modified, it saves the new data.
	*/
	public function __destruct() {
		if( !empty($this->modFields) ) {
			try {
				$this->save();
			} catch(Exception $e) {
				// Can be destructed outside of the matrix
				log_error($e->getMessage()."<br />\n".$e->getTraceAsString(), 'PermanentObject::__destruct(): Saving');
			}
		}
	}
	
	/** Magic getter
	 * @param string $name Name of the property to get
	 * @return The value of field $name
	 * 
	 * Gets the value of field $name.
	 * 'all' returns all fields.
	*/
	public function __get($name) {
		return $this->getValue($name == 'all' ? null : $name);
	}
	
	/** Magic setter
	 * @param $name Name of the property to set
	 * @param $value New value of the property
	 * 
	 * Sets the value of field $name.
	*/
	public function __set($name, $value) {
		$this->setValue($name, $value);
	}
	
	/** Magic isset
	 * @param $name Name of the property to check is set
	 * 
	 * Checks if the field $name is set.
	*/
	public function __isset($name) {
        return isset($this->data[$name]);
	}
	
	/** Magic toString
	 * @return The string value of the object.
	 * 
	 * The object's value when casting to string.
	*/
	public function __toString() {
		try {
			return static::getClass().'#'.$this->{static::$IDFIELD};
// 			return '#'.$this->{static::$IDFIELD}.' ('.get_class($this).')';
		} catch( Exception $e ) {
			log_error($e->getMessage()."<br />\n".$e->getTraceAsString(), 'PermanentObject::__toString()', false);
		}
	}
	
	// *** DEV METHODS ***
	
	/** Gets this permanent object's ID
	 * @return The id of this object.
	 * 
	 * Gets this object ID according to the IDFIELD attribute.
	 */
	public function id() {
		return $this->getValue(static::$IDFIELD);
	}
	
	/** Gets this permanent object's unique ID
	 * @return The uid of this object.
	 * 
	 * Gets this object ID according to the table and id.
	 */
	public function uid() {
		return $this->getTable().'#'.$this->id();
	}
	
	/** 
	 * Update this permanent object from input data array
	 * @param	array $input The input data we will check and extract, used by children.
	 * @param	string[] $fields The array of fields to check. It never should be null using a validator class, it will be a security breach.
	 * @param	boolean $noEmptyWarning True to do not report warning for empty data (instead return 0). Default value is true.
	 * @param	&int $errCount Output parameter for the number of occurred errors validating fields.
	 * @param	&int $successCount Output parameter for the number of successes updating fields.
	 * @return	1 in case of success, else 0.
	 * @see runForUpdate()
	 * @overrideit
	 * 
	 * This method require to be overridden but it still be called too by the child classes.
	 * Here $uInputData is not used, it is reserved for child classes.
	 * $data must contain a filled array of new data.
	 * This method update the EDIT event log.
	 * Before saving, runForUpdate() is called to let child classes to run custom instructions.
	 * Parameter $fields is really useful to allow partial modification only (against form hack).
	 */
	public function update($input, $fields, $noEmptyWarning=true, &$errCount=0, &$successCount=0) {
		
		$operation = $this->getUpdateOperation($input, $fields);
		$operation->validate($errors);
		return $operation->runIfValid();
		
		/*
		static::onValidateInput($input, $fields, $this);
		$data	= static::checkUserInput($input, $fields, $this, $errCount);
		// Don't care about some errors, other fields should be updated.
		$found	= 0;
		foreach( $data as $fieldname => $fieldvalue ) {
			if( in_array($fieldname, static::$fields) ) {
				$found++;
// 				$found	= 1;
// 				continue;
			}
		}
// 		debug('$data', $data);
		try {
			// No data to update
			if( !$found ) {
// 			if( empty($data) ) {
				if( !$noEmptyWarning ) {
					reportWarning('updateEmptyData', static::getDomain());
				}
				return 0;
// 				static::throwException('updateEmptyData');
			}
			static::checkForObject(static::completeFields($data), $this);
		} catch( UserException $e ) { reportError($e, static::getDomain()); return 0; }

		static::onEdit($data, $this);
		$oldData	= $this->all;
		foreach($data as $fieldname => $fieldvalue) {
			// onEdit could add some fields to data
			if( in_array($fieldname, static::$fields) ) {
// 			if( in_array($fieldname, static::$fields) && in_array($fieldname, $fields) ) {
// 			if( static::isFieldEditable($fieldname) ) {
// 				text('Set value of '.$fieldname.' to '.($fieldvalue === NULL ? 'NULL' : (is_bool($fieldvalue) ? b($fieldvalue) : $fieldvalue)));
				$this->setValue($fieldname, $fieldvalue);
				$successCount++;
			}
		}
		$this->logEvent('edit');
		$this->logEvent('update');
		if( $r = $this->save() ) {
			$this->runForUpdate($data, $oldData);
		}
		return $r;
		*/
	}
	
	public function getUpdateOperation($input, $fields) {
		$operation = new UpdateTransactionOperation(static::getClass(), $input, $fields, $this);
		$operation->setSQLAdapter(static::getSQLAdapter());
		return $operation;
	}
	
	public static function onValidUpdate(&$input, $newErrors) {
		// Don't care about some errors, other fields should be updated.
		$found	= 0;
		foreach( $input as $fieldname => $fieldvalue ) {
			if( in_array($fieldname, static::$fields) ) {
				$found++;
			}
		}
		if( $found ) {
			static::fillLogEvent($input, 'edit');
			static::fillLogEvent($input, 'update');
		}
// 		debug('onValidUpdate() - $input', $input);
		return $found ? true : false;
// // 		debug('$data', $data);
// 		try {
// 			// No data to update
// 			if( !$found ) {
// // 			if( empty($data) ) {
// 				if( !$noEmptyWarning ) {
// 					reportWarning('updateEmptyData', static::getDomain());
// 				}
// 				return 0;
// // 				static::throwException('updateEmptyData');
// 			}
// // 			static::checkForObject(static::completeFields($data), $this);
// 		} catch( UserException $e ) { reportError($e, static::getDomain()); return 0; }
	}
	
	public static function extractUpdateQuery(&$input, PermanentObject $object) {
		// To do on Edit
		static::onEdit($input, $object);
// 		$oldData	= $this->all;
// 		foreach($data as $fieldname => $fieldvalue) {
// 			// onEdit could add some fields to data
// 			if( in_array($fieldname, static::$fields) ) {
// // 			if( in_array($fieldname, static::$fields) && in_array($fieldname, $fields) ) {
// // 			if( static::isFieldEditable($fieldname) ) {
// // 				text('Set value of '.$fieldname.' to '.($fieldvalue === NULL ? 'NULL' : (is_bool($fieldvalue) ? b($fieldvalue) : $fieldvalue)));
// 				$this->setValue($fieldname, $fieldvalue);
// 				$successCount++;
// 			}
// 		}
// 		$this->logEvent('edit');
// 		$this->logEvent('update');
		
// 		$data	= array();
// 		foreach($object->modFields as $fieldname) {
// 			if( $fieldname != static::$IDFIELD ) {
// 				$data[$fieldname] = static::parseFieldValue($fieldname, $this->getValue($fieldname));
// // 				$data[$fieldname] = static::parseFieldValue($fieldname, $this->getValue($fieldname));
// // 				$updQ .= ((!empty($updQ)) ? ', ' : '').static::escapeIdentifier($fieldname).'='.static::formatFieldValue($fieldname, $this->getValue($fieldname));
// 			}
// 		}

// 		$what	= array();
		foreach( $input as $fieldname => $fieldvalue ) {
			// If saving object, value is the same, validator should check if value is new
// 			if( !in_array($fieldname, static::$fields) || $fieldvalue == $object->getValue($fieldname) ) {
			if( !in_array($fieldname, static::$fields) ) {
				unset($input[$fieldname]);
			}
// 			if( in_array($fieldname, static::$fields) ) {
// 				$what[$fieldname] = static::formatFieldValue($fieldname, $fieldvalue);
// 			}
		}
		
// 		$IDFIELD	= static::$IDFIELD;
		$options	= array(
			'what'		=> $input,
			'table'		=> static::$table,
			'where'		=> static::getIDField().'='.$object->id(),
			'number'	=> 1,
		);
// 		debug('extractUpdateQuery() - Update operation options', $options);
// 		die();
		
		return $options;
// 		$r	= SQLAdapter::doUpdate($options, static::$DBInstance, static::$IDFIELD);
// 		$modFields	= $object->modFields;
// 		$object->clearModifiedFields();
		
		
		
// 		$what	= array();
// 		foreach($data as $fieldname => $fieldvalue) {
// 			if( in_array($fieldname, static::$fields) ) {
// 				$what[$fieldname]	= static::formatFieldValue($fieldname, $fieldvalue);
// 			}
// 		}
// 		$options	= array(
// 			'table'	=> static::$table,
// 			'what'	=> $what,
// 		);
// 		return $options;
// 		SQLAdapter::doInsert($options, static::$DBInstance, static::$IDFIELD);
// 		$LastInsert	= SQLAdapter::doLastID(static::$table, static::$IDFIELD, static::$DBInstance);
// 		// To do after insertion
// 		static::applyToObject($data, $LastInsert);
// 		static::onSaved($data, $LastInsert);
// 		return $LastInsert;
	}
	
	public function getDeleteOperation() {
// 		return new DeleteTransactionOperation(static::getClass(), $this);
		$operation = new DeleteTransactionOperation(static::getClass(), $this);
		$operation->setSQLAdapter(static::getSQLAdapter());
		return $operation;
	}
	
	/** Runs for Update
	 * @param $data the new data
	 * @param $oldData the old data
	 * @see update()
	 * 
	 * This function is called by update() before saving new data.
	 * $data contains only edited data, excluding invalids and not changed ones.
	 * In the base class, this method does nothing.
	*/
	public function runForUpdate($data, $oldData) { }

	/**
	 * Run for Object edit
	 * 
	 * @param array $data the new data
	 * @param PermanentObject $object the old data
	 * @see update()
	 * @see create()
	 *
	 * Replace deprecated runForUpdate()
	 * This function is called by update() and create() before saving new data.
	 * $data contains only edited data, excluding invalids and not changed ones.
	 * In the base class, this method does nothing.
	 */
	public static function onEdit(array &$data, $object) { }
	
	public static function onValidateInput(array &$input, &$fields, $object) { }
	
	public static function onSaved(array $data, $object) { }
	
	protected $onSavedInProgress = false;
	
	/** Saves this permanent object
	 * @return 1 in case of success, else 0
	 * 
	 * If some fields was modified, it saves these fields using the SQL Adapter.
	*/
	public function save() {
		if( empty($this->modFields) || $this->isDeleted() ) {
			return 0;
		}

		$data = array_filterbykeys($this->all, $this->modFields);
		if( !$data ) {
			throw new Exception('No updated data found but there is modified fields, unable to update');
		}
		$operation = $this->getUpdateOperation($data, $this->modFields);
		// Do not validate, new data are invalid due to the fact the new data are already in object
// 		$operation->validate();
		$r = $operation->run();
		$this->modFields	= array();
		if( !$this->onSavedInProgress ) {
			// Protect script against saving loops
			$this->onSavedInProgress	= true;
			static::onSaved($data, $this);
			$this->onSavedInProgress	= false;
		}
		return $r;
	}
	
	public function checkIntegrity() { }
	
	public function remove() {
		if( $this->isDeleted() ) { return; }
		$operation = $this->getDeleteOperation();
		$operation->validate($errors);
		return $operation->runIfValid();
// 		return static::delete($this->id());
	}
	public function free() {
		if( $this->remove() ) {
			$this->data			= null;
			$this->modFields	= null;
			return true;
		}
		return false;
	}
	
	/**
	 * Reload fields from database
	 * 
	 * @param string $field The field to reload, default is null (all fields).
	 * 
	 * Update the current object's fields from database.
	 * If $field is not set, it reloads only one field else all fields.
	 * Also it removes the reloaded fields from the modified ones list.
	*/
	public function reload($field=null) {
		$IDFIELD = static::getIDField();
		$options = array('where' => $IDFIELD.'='.$this->$IDFIELD, 'output' => SQLAdapter::ARR_FIRST);
		if( $field ) {
			if( !in_array($field, static::$fields) ) {
				throw new FieldNotFoundException($field, static::getClass());
			}
			$i = array_search($this->modFields);
			if( $i !== false ) {
				unset($this->modFields[$i]);
			}
			$options['what'] = $field;
		} else {
			$this->modFields = array();
		}
		try {
			$data = static::get($options);
		} catch( SQLException $e ) {
			$data = null;
		}
		if( empty($data) ) {
			$this->markAsDeleted();
			return false;
		}
		if( !is_null($field) ) {
			$this->data[$field] = $data[$field];
		} else {
			$this->data = $data;
		}
		return true;
	}
	
	/**
	 * Mark the field as modified
	 * 
	 * @param $field The field to mark as modified.
	 * 
	 * Adds the $field to the modified fields array.
	*/
	protected function addModFields($field) {
		if( !in_array($field, $this->modFields) ) {
			$this->modFields[] = $field;
		}
	}
	
	/**
	 * List all modified fields
	 * 
	 * @return string[]
	 */
	protected function listModifiedFields() {
		return $this->modFields;
	}
	
	/**
	 * Clear modified fields
	 */
	protected function clearModifiedFields() {
		$this->modFields = array();
	}
	
	/** Checks if this object is deleted
	 * @return True if this object is deleted.
	 * 
	 * Checks if this object is known as deleted.
	*/
	public function isDeleted() {
		return $this->isDeleted;
	}
	
	/** Checks if this object is valid
	 * @return True if this object is valid.
	 * 
	 * Checks if this object is not deleted.
	 * May be used for others cases.
	*/
	public function isValid() {
		return !$this->isDeleted();
	}
	
	/** Marks this object as deleted
	 * @see isDeleted()
	 * @warning Be sure what you are doing before calling this function (never out of this class' context).
	 * 
	 * Marks this object as deleted
	 */
	public function markAsDeleted() {
		$this->isDeleted = true;
	}
	
	/** Gets one value or all values.
	 * @param $key Name of the field to get.
	 * 
	 * Gets the value of field $key or all data values if $key is null.
	*/
	public function getValue($key=null) {
		if( empty($key) ) {
			return $this->data;
		}
		if( !array_key_exists($key, $this->data) ) {
// 			log_debug('Key "'.$key.'" not found in array : '.print_r($this->data, 1));
			throw new FieldNotFoundException($key, static::getClass());
		}
		return $this->data[$key];
	}
	
	/** Sets the value of a field
	 * @param $key Name of the field to set.
	 * @param $value New value of the field.
	 * 
	 * Sets the field $key with the new $value.
	*/
	public function setValue($key, $value) {
		if( !isset($key) ) {//$value
			throw new Exception("nullKey");
		} else
		if( !in_array($key, static::$fields) ) {
			throw new FieldNotFoundException($key, static::getClass());
		} else
		if( $key === static::$IDFIELD ) {
			throw new Exception("idNotEditable");
		} else
// 		if( empty($this->data[$key]) || $value !== $this->data[$key] ) {
		// If new value is different
		if( $value !== $this->data[$key] ) {
			$this->addModFields($key);
			$this->data[$key] = $value;
		}
	}
	
	/** Verifies equality
	 * @param $o The object to compare.
	 * @return True if this object represents the same data, else False.
	 * 
	 * Compares the class and the ID field value of the 2 objects.
	*/
	public function equals($o) {
		return (get_class($this)==get_class($o) && $this->id()==$o->id());
	}
	
	/** Logs an event
	 * @param $event The event to log in this object.
	 * @param $time A specified time to use for logging event.
	 * @param $ipAdd A specified IP Adress to use for logging event.
	 * @see getLogEvent()
	 * 
	 * Logs an event to this object's data.
	*/
	public function logEvent($event, $time=null, $ipAdd=null) {
		$log = static::getLogEvent($event, $time, $ipAdd);
		if( in_array($event.'_time', static::$fields) ) {
			$this->setValue($event.'_time', $log[$event.'_time']);
		} else
		if( in_array($event.'_date', static::$fields) ) {
			$this->setValue($event.'_date', sqlDatetime($log[$event.'_time']));
		} else {
			return;
		}
		if( in_array($event.'_agent', static::$fields) && isset($_SERVER['HTTP_USER_AGENT']) ) {
			$this->setValue($event.'_agent', $_SERVER['HTTP_USER_AGENT']);
		}
		if( in_array($event.'_referer', static::$fields) && isset($_SERVER['HTTP_REFERER']) ) {
			$this->setValue($event.'_referer', $_SERVER['HTTP_REFERER']);
		}
		try {
			$this->setValue($event.'_ip', $log[$event.'_ip']);
		} catch(FieldNotFoundException $e) {}
	}
	
	public static function fillLogEvent(&$array, $event) {
		// All event fields will be filled, if value is not available, we set to null
		if( in_array($event.'_time', static::$fields) ) {
			$array[$event.'_time'] = time();
		} else
		if( in_array($event.'_date', static::$fields) ) {
			$array[$event.'_date'] = sqlDatetime();
		} else {
			// Date or time is mandatory
			return;
		}
		if( in_array($event.'_ip', static::$fields) ) {
			$array[$event.'_ip'] = !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
		}
		if( in_array($event.'_agent', static::$fields) ) {
			$array[$event.'_agent'] = !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
		}
		if( in_array($event.'_referer', static::$fields) ) {
			$array[$event.'_referer'] = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
		}
	}
	
	/** Gets the log of an event
	 * @param $event The event to log in this object.
	 * @param $time A specified time to use for logging event.
	 * @param $ipAdd A specified IP Adress to use for logging event.
	 * @deprecated
	 * @see logEvent()
	 * 
	 * Builds a new log event for $event for this time and the user IP address.
	*/
	public static function getLogEvent($event, $time=null, $ipAdd=null) {
		return array(
			$event.'_time'	=> isset($time) ? $time : time(),
			$event.'_date'	=> isset($time) ? sqlDatetime($time) : sqlDatetime(),
			$event.'_ip'	=> isset($ipAdd) ? $ipAdd : (!empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'NONE' ),
		);
	}
	
	// *** STATIC METHODS ***

	public static function object(&$obj) {
		return $obj = is_id($obj) ? static::load($obj) : $obj;
	}

	public static function isFieldEditable($fieldname) {
		if( $fieldname == static::$IDFIELD ) { return false; }
		if( !is_null(static::$editableFields) ) { return in_array($fieldname, static::$editableFields); }
		if( method_exists(static::$validator, 'isFieldEditable') ) { return in_array($fieldname, static::$editableFields); }
		return in_array($fieldname, static::$fields);
	}
	
	/** Gets some permanent objects
	 * @param $options The options used to get the permanents object.
	 * @return An array of array containing object's data.
	 * @see SQLAdapter
	 * 
	 * Gets an objects' list using this class' table.
	 * Take care that output=SQLAdapter::ARR_OBJECTS and number=1 is different from output=SQLAdapter::OBJECT
	 * 
	*/
	/**
	 * @param array $options
	 * @return SQLSelectRequest|static|static[]
	 */
	public static function get($options=NULL) {
// 		debug('PERM OBJECT - GET');
		if( $options === NULL ) {
// 			debug('SQLRequest from '.static::getClass().', table = '.static::$table);
// 			debug('DB Instance', static::$DBInstance);
			return SQLRequest::select(static::getSQLAdapter(), static::$IDFIELD, static::getClass())->from(static::$table)->asObjectList();
// 			return SQLRequest::select(static::$DBInstance, static::$IDFIELD, static::getClass())->from(static::$table)->asObjectList();
		}
		if( $options instanceof SQLSelectRequest ) {
			$options->setSQLAdapter(static::getSQLAdapter());
// 			$options->setInstance(static::$DBInstance);
			$options->setIDField(static::$IDFIELD);
			$options->from(static::$table);
			return $options->run();
		}
		if( is_string($options) ) {
			$options	= array();
			$args		= func_get_args();
			foreach( array('where', 'orderby') as $i => $key ) {
				if( !isset($args[$i]) ) { break; }
				$options[$key]	= $args[$i];
			}
		}
		$options['table'] = static::$table;
		// May be incompatible with old revisions (< R398)
		if( !isset($options['output']) ) {
			$options['output'] = SQLAdapter::ARR_OBJECTS;
		}
		//This method intercepts outputs of array of objects.
		$onlyOne	= $objects = 0;
		if( in_array($options['output'], array(SQLAdapter::ARR_OBJECTS, SQLAdapter::OBJECT)) ) {
			if( $options['output'] == SQLAdapter::OBJECT ) {
				$options['number']	= 1;
				$onlyOne	= 1;
			}
			$options['output']	= SQLAdapter::ARR_ASSOC;
// 			$options['what'] = '*';// Could be * or something derived for order e.g
			$objects	= 1;
		}
// 		debug($options);
		$r	= SQLAdapter::doSelect($options, static::$DBInstance, static::$IDFIELD);
		if( empty($r) && in_array($options['output'], array(SQLAdapter::ARR_ASSOC, SQLAdapter::ARR_OBJECTS, SQLAdapter::ARR_FIRST)) ) {
			return $onlyOne && $objects ? null : array();
		}
		if( !empty($r) && $objects ) {
// 			if( isset($options['number']) && $options['number'] == 1 ) {
			if( $onlyOne ) {
				$r	= static::load($r[0]);
			} else {
				foreach( $r as &$rdata ) {
					$rdata = static::load($rdata);
				}
			}
		}
		return $r;
	}
	
// 	public static function prepareSelectRequest(SQLSelectRequest $request) {
		
// // 		$options['table'] = static::$table;
// 		// May be incompatible with old revisions (< R398)
// // 		if( !isset($options['output']) ) {
// // 			$options['output'] = SQLAdapter::ARR_OBJECTS;
// // 		}
// 		//This method intercepts outputs of array of objects.
// 		$onlyOne	= $objects = 0;
// 		if( in_array($request->output(), array(SQLAdapter::ARR_OBJECTS, SQLAdapter::OBJECT)) ) {
// 			if( $request->output() == SQLAdapter::OBJECT ) {
// 				$options['number']	= 1;
// 				$onlyOne	= 1;
// 			}
// 			$options['output']	= SQLAdapter::ARR_ASSOC;
// // 			$options['what'] = '*';// Could be * or something derived for order e.g
// 			$objects	= 1;
// 		}
// 		$r	= SQLAdapter::doSelect($options, static::$DBInstance, static::$IDFIELD);
// 		if( empty($r) && in_array($options['output'], array(SQLAdapter::ARR_ASSOC, SQLAdapter::ARR_OBJECTS, SQLAdapter::ARR_FIRST)) ) {
// 			return $onlyOne && $objects ? null : array();
// 		}
// 		if( !empty($r) && $objects ) {
// // 			if( isset($options['number']) && $options['number'] == 1 ) {
// 			if( $onlyOne ) {
// 				$r	= static::load($r[0]);
// 			} else {
// 				foreach( $r as &$rdata ) {
// 					$rdata = static::load($rdata);
// 				}
// 			}
// 		}
// 		return $r;
// 	}
	
	/**
	 * Load a permanent object
	 * 
	 * @param	$in mixed|mixed[] The object ID to load or a valid array of the object's data
	 * @param	boolean $nullable True to silent errors row and return null
	 * @param	boolean $usingCache True to cache load and set cache, false to not cache
	 * @return	PermanentObject The object
	 * @see static::get()
	 * 
	 * Loads the object with the ID $id or the array data.
	 * The return value is always a static object (no null, no array, no other object).
	 */
	public static function load($in, $nullable=true, $usingCache=true) {
		if( empty($in) ) {
// 			static::throwException('invalidParameter_load_'.static::getClass());
			if( $nullable ) { return null; }
// 			throw new Exception('invalidParameter_load');
			static::throwNotFound('invalidParameter_load');
		}
		// Try to load an object from this class
		if( is_object($in) && $in instanceof static ) {
			return $in;
		}
		$IDFIELD	= static::$IDFIELD;
		// If $in is an array, we trust him, as data of the object.
		if( is_array($in) ) {
			$id		= $in[$IDFIELD];
			$data	= $in;
		} else {
			$id		= $in;
		}
		if( !is_ID($id) ) {
			static::throwException('invalidID');
		}
		// Loading cached
		if( $usingCache && isset(static::$instances[static::getClass()][$id]) ) {
			return static::$instances[static::getClass()][$id];
		}
		// If we don't get the data, we request them.
		if( empty($data) ) {
			// Getting data
			$obj = static::get(array(
				'where'	=> $IDFIELD.'='.$id,
				'output'=> SQLAdapter::OBJECT,
			));
			// Ho no, we don't have the data, we can't load the object !
			if( empty($obj) ) {
				if( $nullable ) { return null; }
				static::throwNotFound();
			}
		} else {
			$obj = new static($data);
		}
		// Caching object
// 		return static::$instances[static::getClass()][$id] = $obj;
		return $usingCache ? $obj->checkCache() : $obj;
	}
	
	public static function cacheObjects(array &$objects) {
		foreach( $objects as &$obj ) {
			$obj	= $obj->checkCache();
		}
		return $objects;
	}
	protected function checkCache() {
		if( isset(static::$instances[static::getClass()][$this->id()]) ) { return static::$instances[static::getClass()][$this->id()]; }
		static::$instances[static::getClass()][$this->id()]	= $this;
		return $this;
	}
	
	/** Deletes a permanent object
	 * @param $in The object ID to delete or the delete array.
	 * @return the number of deleted rows.
	 * 
	 * Deletes the object with the ID $id or according to the input array.
	 * It calls runForDeletion() only in case of $in is an ID.
	 * 
	 * The cached object is mark as deleted.
	 * Warning ! If several class instantiate the same db row, it only marks the one of the current class, others won't be marked as deleted, this can cause issues !
	 * We advise you to use only one class of one item row or to use it read-only.
	*/
	public static function delete($in) {
		throw new Exception("Deprecated, please use remove()");
		/*
		if( is_array($in) ) {
			$in['table'] = static::$table;
			return SQLAdapter::doDelete($in, static::$DBInstance, static::$IDFIELD);
		}
		if( !is_id($in) ) {
			static::throwException('invalidID');
		}
		if( isset(static::$instances[static::getClass()][$in]) ) {
			/* @var $obj static * /
			$obj = &static::$instances[static::getClass()][$in];
			if( $obj->isDeleted() ) { return 1; }
		}
		$IDFIELD	= static::$IDFIELD;
		$options	= array(
			'table'		=> static::$table,
			'number'	=> 1,
			'where'		=> "{$IDFIELD}={$in}",
		);
		$r = SQLAdapter::doDelete($options, static::$DBInstance, static::$IDFIELD);
		if( $r ) {
			if( isset($obj) ) {
				$obj->markAsDeleted();
			}
			static::runForDeletion($in);
		}
		return $r;
		*/
	}
	
	/**
	 * Removes deleted instances
	 */
	public static function clearDeletedInstances() {
		if( !isset(static::$instances[static::getClass()]) ) { return; }
		$instances	= &static::$instances[static::getClass()];
		foreach( $instances as $id => $obj ) {
			if( $obj->isDeleted() ) {
				unset($instances[$id]);
			}
		}
	}
	/**
	 * Removes deleted instances
	 */
	public static function clearInstances() {
		return static::clearDeletedInstances();
	}
	
	/**
	 * Removes all instances
	 */
	public static function clearAllInstances() {
		if( !isset(static::$instances[static::getClass()]) ) { return; }
		unset(static::$instances[static::getClass()]);
	}
	
	/** Escape identifier through instance
	 * @param	string $Identifier The identifier to escape. Default is table name.
	 * @return	string The escaped identifier
	 * @see SQLAdapter::escapeIdentifier()
	 * @see static::ei()
	*/
	public static function escapeIdentifier($identifier=null) {
		$sqlAdapter = static::getSQLAdapter();
		return $sqlAdapter->escapeIdentifier($identifier ? $identifier : static::$table);
// 		return SQLAdapter::doEscapeIdentifier($identifier ? $identifier : static::$table, static::$DBInstance);
	}
	/** Escape identifier through instance
	 * @param	string $Identifier The identifier to escape. Default is table name.
	 * @return	string The escaped identifier
	 * @see static::escapeIdentifier()
	*/
	public static function ei($identifier=null) {
		return static::escapeIdentifier($identifier);
	}

	/**
	 * Parse the value from SQL scalar to PHP type
	 *
	 * @param string $name The field name to parse
	 * @param string $value The field value to parse
	 * @return string The parse $value
	 * @see PermanentObject::formatFieldValue()
	 */
	protected static function parseFieldValue($name, $value) {
		return $value;
	}
	
	/**
	 * Format the value
	 * 
	 * @param string $name The field name to format
	 * @param mixed $value The field value to format
	 * @return string The formatted $Value
	 * @deprecated Prefer to use parseFieldValue(), Adapter should format the data
	 * @see PermanentObject::formatValue()
	*/
	protected static function formatFieldValue($name, $value) {
		return $value;
	}
	
	/**
	 * Escape value through instance
	 * 
	 * @param scalar $value The value to format
	 * @return string The formatted $Value
	 * @see SQLAdapter::formatValue()
	*/
	public static function formatValue($value) {
		$sqlAdapter = static::getSQLAdapter();
		return $sqlAdapter->formatValue($value);
// 		return SQLAdapter::doFormatValue($value, static::$DBInstance);
	}
	/**
	 * Escape value through instance
	 * 
	 * @param scalar $value The value to format
	 * @return string The formatted $Value
	 * @see PermanentObject::formatValue()
	*/
	public static function fv($value) {
		return static::formatValue($value);
	}
	
	/**
	 * Escape values through instance and return as list string
	 * 
	 * @param array $list The list of values
	 * @return string The formatted list string
	 * @see PermanentObject::formatValue()
	 * 
	 * @todo Change to use formatFieldValue($name, $value) ?
	*/
	public static function formatValueList(array $list) {
		$str	= '';
		foreach( $list as $i => $v ) {
			$str	.= ($i ? ',' : '').static::formatValue($v);
		}
		return $str;
	}
	
	/** Runs for Deletion
	 * @param $id The deleted object ID.
	 * @see delete()
	 * 
	 * This function is called by delete() after deleting the object $id.
	 * If you need to get the object before, prefer to inherit delete()
	 * In the base class, this method does nothing.
	*/
	public static function runForDeletion($id) { }
	
	/**
	 * Create a new permanent object
	 * 
	 * @param	array $input The input data we will check, extract and create the new object.
	 * @param	array $fields The array of fields to check. Default value is null.
	 * @param	integer $errCount Output parameter to get the number of found errors. Default value is 0
	 * @return	integer The ID of the new permanent object.
	 * @see		testUserInput()
	 * @see		createAndGet()
	 * 
	 * Create a new permanent object from ths input data.
	 * To create an object, we expect that it is valid, else we throw an exception.
	*/
	public static function create($input=array(), $fields=null, &$errCount=0) {
		$operation = static::getCreateOperation($input, $fields);
		$operation->validate($errors);
		return $operation->runIfValid();
		
// 		$newErrors	= 0;
// 		static::onValidateInput($input, $fields, null);
// 		$data	= static::checkUserInput($input, $fields, null, $newErrors);
// 		$errCount	+= $newErrors;
// 		if( $newErrors ) {
// 			static::throwException('errorCreateChecking');
// 		}
// 		$data	= static::getLogEvent('create') + static::getLogEvent('edit') + $data;
		
// 		// Check if entry already exist
// 		static::checkForObject($data);
// 		// To do before insertion
// 		static::runForObject($data);
// 		// To do on Edit
// 		static::onEdit($data, null);
		
// 		$what	= array();
// 		foreach($data as $fieldname => $fieldvalue) {
// 			if( in_array($fieldname, static::$fields) ) {
// 				$what[$fieldname]	= static::formatFieldValue($fieldname, $fieldvalue);
// 			}
// 		}
// 		$options	= array(
// 			'table'	=> static::$table,
// 			'what'	=> $what,
// 		);
// 		SQLAdapter::doInsert($options, static::$DBInstance, static::$IDFIELD);
// 		$LastInsert	= SQLAdapter::doLastID(static::$table, static::$IDFIELD, static::$DBInstance);
// 		// To do after insertion
// 		static::applyToObject($data, $LastInsert);
// 		static::onSaved($data, $LastInsert);
// 		return $LastInsert;
	}
	
	public static function getCreateOperation($input, $fields) {
// 		return new CreateTransactionOperation(static::getClass(), $input, $fields);
		$operation = new CreateTransactionOperation(static::getClass(), $input, $fields);
		$operation->setSQLAdapter(static::getSQLAdapter());
		return $operation;
	}

	public static function onValidCreate(&$input, $newErrors) {
		if( $newErrors ) {
			static::throwException('errorCreateChecking');
		}
		static::fillLogEvent($input, 'create');
		static::fillLogEvent($input, 'edit');
// 		$input = static::getLogEvent('create') + static::getLogEvent('edit') + $input;
		return true;
	}
	
	public static function extractCreateQuery(&$input) {
		// To do on Edit
		static::onEdit($input, null);
		
// 		$what	= array();
// 		foreach($data as $fieldname => $fieldvalue) {
// 			if( in_array($fieldname, static::$fields) ) {
// 				$what[$fieldname] = static::formatFieldValue($fieldname, $fieldvalue);
// 			}
// 		}
		foreach( $input as $fieldname => $fieldvalue ) {
			if( !in_array($fieldname, static::$fields) ) {
				unset($input[$fieldname]);
			}
		}
		
		$options	= array(
			'table'	=> static::$table,
			'what'	=> $input,
		);
		return $options;
// 		SQLAdapter::doInsert($options, static::$DBInstance, static::$IDFIELD);
// 		$LastInsert	= SQLAdapter::doLastID(static::$table, static::$IDFIELD, static::$DBInstance);
// 		// To do after insertion
// 		static::applyToObject($data, $LastInsert);
// 		static::onSaved($data, $LastInsert);
// 		return $LastInsert;
	}
	
	

	/** Create a new permanent object
	 * @param	array $input The input data we will check, extract and create the new object.
	 * @param	array $fields The array of fields to check. Default value is null.
	 * @param	integer $errCount Output parameter to get the number of found errors. Default value is 0
	 * @return	static The new permanent object
	 * @see testUserInput()
	 * @see create()
	 *
	 * Create a new permanent object from ths input data.
	 * To create an object, we expect that it is valid, else we throw an exception.
	 */
	public static function createAndGet($inputData=array(), $fields=null, &$errCount=0) {
		return static::load(static::create($inputData, $fields, $errCount));
	}
	
	/** Completes missing fields
	 * @param $data The data array to complete.
	 * @return The completed data array.
	 * 
	 * Completes an array of data of an object of this class by setting missing fields with empty string.
	*/
	public static function completeFields($data) {
		foreach( static::$fields as $fieldname ) {
			if( !isset($data[$fieldname]) ) {
				$data[$fieldname] = '';
			}
		}
		return $data;
	}
	
	public static function getFields() {
		return static::$fields;
	}
	
	/** Gets the name of this class
	 * @return The name of this class.
	*/
	public static function getClass() {
		return get_called_class();
	}
	
	/** Gets the table of this class
	 * @return The table of this class.
	*/
	public static function getTable() {
		return static::$table;
	}
	
	/** Gets the ID field name of this class
	 * @return The ID field of this class.
	*/
	public static function getIDField() {
		return static::$IDFIELD;
	}
	
	/** Gets the domain of this class
	 * @return The domain of this class.
	 * 
	 * Gets the domain of this class, can be guessed from $table or specified in $domain.
	*/
	public static function getDomain() {
		return static::$domain !== NULL ? static::$domain : static::$table;
	}
	
	/** Gets the validator of this class
	 * @return The validator of this class.
	 * 
	 * Gets the validator of this class.
	*/
	public static function getValidator() {
		return static::$validator;
	}
	
	/** Runs for object
	 * @param $data The new data to process.
	 * @see create()
	 * 
	 * This function is called by create() after checking new data and before inserting them.
	 * In the base class, this method does nothing.
	*/
	public static function runForObject(&$data) { }
	
	/**
	 * Apply for new object
	 * 
	 * @param $data The new data to process.
	 * @param $id The ID of the new object.
	 * @see create()
	 * 
	 * This function is called by create() after inserting new data.
	 * In the base class, this method does nothing.
	*/
	public static function applyToObject(&$data, $id) { }
	
	// 		** VALIDATION METHODS **
	
	/**
	 * Check user input
	 * 
	 * @param array $uInputData The user input data to check.
	 * @param string[] $fields The array of fields to check. Default value is null.
	 * @param PermanentObject $ref The referenced object (update only). Default value is null.
	 * @param int $errCount The resulting error count, as pointer. Output parameter.
	 * @return The valid data.
	 * 
	 * Check if the class could generate a valid object from $uInputData.
	 * The method could modify the user input to fix them but it must return the data.
	 * The data are passed through the validator, for different cases:
	 * - If empty, this function return an empty array.
	 * - If an array, it uses an field => checkMethod association.
	*/
	public static function checkUserInput($uInputData, $fields=null, $ref=null, &$errCount=0) {
		if( !isset($errCount) ) {
			$errCount = 0;
		}
		// Allow reversed parameters 2 & 3 - Declared as useless
// 		if( !is_array($fields) && !is_object($ref) ) {
// 			$tmp = $fields; $fields = $ref; $ref = $tmp; unset($tmp);
// 		}
// 		if( is_null($ref) && is_object($ref) ) {
// 			$ref = $fields;
// 			$fields = null;
// 		}
		if( is_array(static::$validator) ) {
			if( $fields===NULL ) {
				$fields	= static::$editableFields;
			}
			if( empty($fields) ) { return array(); }
			$data = array();
			foreach( $fields as $field ) {
				// If editing the id field
				if( $field == static::$IDFIELD ) { continue; }
				$value = $notset = null;
				try {
					try {
						// Field to validate
						if( !empty(static::$validator[$field]) ) {
							$checkMeth	= static::$validator[$field];
							// If not defined, we just get the value without check
							$value	= static::$checkMeth($uInputData, $ref);
	
						// Field to NOT validate
						} else if( array_key_exists($field, $uInputData) ) {
							$value	= $uInputData[$field];
						} else {
							$notset	= 1;
						}
						if( !isset($notset) &&
							( $ref===NULL || $value != $ref->$field) &&
							( $fields===NULL || in_array($field, $fields))
						) {
							$data[$field]	= $value;
						}

					} catch( UserException $e ) {
						if( $value===NULL && isset($uInputData[$field]) ) {
							$value	= $uInputData[$field];
						}
						throw InvalidFieldException::from($e, $field, $value);
					}
					
				} catch( InvalidFieldException $e ) {
					$errCount++;
					reportError($e, static::getDomain());
				}
			}
			return $data;
		
		} else if( is_object(static::$validator) ) {
			if( method_exists(static::$validator, 'validate') ) {
// 				debug('Pass validator with input', $uInputData);
// 				debug('Pass validator with fields', $fields);
// 				debug('Pass validator with result', static::$validator->validate($uInputData, $fields, $ref, $errCount));
				return static::$validator->validate($uInputData, $fields, $ref, $errCount);
			}
		}
		return array();
	}
	
	/**
	 * Check for object
	 * 
	 * @param $data The new data to process.
	 * @param $ref The referenced object (update only). Default value is null.
	 * @see create()
	 * @see update()
	 * 
	 * This function is called by create() after checking user input data and before running for them.
	 * In the base class, this method does nothing.
	 */
	public static function checkForObject($data, $ref=null) { }
	
	/**
	 * Test user input
	 * 
	 * @param $uInputData The new data to process.
	 * @param $fields The array of fields to check. Default value is null.
	 * @param $ref The referenced object (update only). Default value is null.
	 * @param $errCount The resulting error count, as pointer. Output parameter.
	 * @see create()
	 * @see checkUserInput()
	 * 
	 * Does a checkUserInput() and a checkForObject()
	 */
	public static function testUserInput($uInputData, $fields=null, $ref=null, &$errCount=0) {
		$data = static::checkUserInput($uInputData, $fields, $ref, $errCount);
		if( $errCount ) { return false; }
		try {
			static::checkForObject($data, $ref);
		} catch( UserException $e ) {
			$errCount++;
			reportError($e, static::getDomain());
			return false;
		}
		return true;
	}
	
	protected static $knownClassData	= array();
// 	public static function getClassData($classData) {
	public static function getClassData(&$classData=null) {
		$class	= static::getClass();
		if( !isset(static::$knownClassData[$class]) ) {
// 			debug('Not current class data for class '.$class);
			static::$knownClassData[$class]	= (object) array(
				'sqlAdapter'	=> null,
			);
		}
// 		debug('getClassData() - static::$knownClassData[$class]', static::$knownClassData[$class]);
// 		return static::$knownClassData[$class];
		$classData = static::$knownClassData[$class];
// 		debug('getClassData() - $classData', $classData);
		return $classData;
	}
	
	/**
	 * @return SQLAdapter
	 */
	public static function getSQLAdapter() {
// 		$classData = static::getClassData();
		static::getClassData($classData);
// 		debug('$classData 1', $classData);
// 		die();
		if( !$classData->sqlAdapter ) {
			$classData->sqlAdapter = SQLAdapter::getInstance(static::$DBInstance);
		}
		// This after $knownClassData classData
		
// 		static::getClassData($classData);
// 		debug('$classData 2', $classData);
// 		die();
		
		return $classData->sqlAdapter;
	}
	
	//! Initializes class
	public static function init($isFinal=true) {
		
		$parent = get_parent_class(get_called_class());
		if( empty($parent) ) { return; }
		
		static::$fields = array_unique(array_merge(static::$fields, $parent::$fields));
		// Deprecated, no more defining editable fields, rely on form and EntityDescriptor
		if( !$parent::$editableFields ) {
			static::$editableFields = !static::$editableFields ? $parent::$editableFields : array_unique(array_merge(static::$editableFields, $parent::$editableFields));
		}
		// Deprecated, should use EntityDescriptor as validator
		if( is_array(static::$validator) && is_array($parent::$validator) ) {
			static::$validator = array_unique(array_merge(static::$validator, $parent::$validator));
		}
		if( !static::$domain ) {
			static::$domain = static::$table;
		}
	}
	
	/** Throw an UserException
	 * @param $message the text message, may be a translation string
	 * @see UserException
	 * 
	 * Throws an UserException with the current domain.
	*/
	public static function throwException($message) {
		throw new UserException($message, static::getDomain());
	}
	
	public static function throwNotFound($message=null) {
		throw new NotFoundException($message, static::getDomain());
	}
	
	/** Translate text according to the object domain
	 * @param string $text The text to translate
 	 * @param array|string $values The values array to replace in text. Could be used as second parameter.
	 * @return string The translated text
	 * @see t()
	 * 
	 * Translate text according to the object domain
	*/
	public static function text($text, $values=array()) {
		return t($text, static::getDomain(), is_array($values) ? $values : array_slice(func_get_args(), 1));
	}
	
	/** Translate text according to the object domain
	 * @param sting $text The text to translate
 	 * @param array|string $values The values array to replace in text. Could be used as second parameter.
	 * @see t()
	 * 
	 * Translate text according to the object domain
	*/
	public static function _text($text, $values=array()) {
		_t($text, static::getDomain(), is_array($values) ? $values : array_slice(func_get_args(), 1));
	}
	
	/** Report an UserException
	 * @param $e the UserException
	 * @see UserException
	 * 
	 * Throws an UserException with the current domain.
	*/
	public static function reportException(UserException $e) {
		reportError($e);
	}
}
PermanentObject::selfInit();