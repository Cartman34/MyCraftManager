<?php

/** A class to describe an entity
 * 
 * @author Florent HAZARD
 * 
 * This class uses a YAML configuration file to describe the entity.
 * Thus you can easily update your database using dev_entities module and it validate the input data for you.
 */
class EntityDescriptor {

	protected $class;
	protected $name;
	protected $version;
    /* @var $fields Field[] */
	protected $fields	= array();
	protected $indexes	= array();
	protected $abstract	= false;
	
	const FLAG_ABSTRACT	= 'abstract';
	
	const DESCRIPTORCLASS	= 'EntityDescriptor';
	const IDFIELD			= 'id';
	const VERSION			= 3;

	/**
	 * Get all available entity descriptor
	 * 
	 * @return EntityDescriptor[]
	 */
	public static function getAllEntityDescriptors() {
		$entities	= array();
		foreach( static::getAllEntities() as $entity ) {
			$entities[$entity] = EntityDescriptor::load($entity);
		}
		return $entities;
	}

	/**
	 * Get all available entities
	 *
	 * @return string[]
	 */
	public static function getAllEntities() {
		$entities	= cleanscandir(pathOf(CONFDIR.ENTITY_DESCRIPTOR_CONFIG_PATH));
		foreach( $entities as $i => &$filename ) {
			$pi	= pathinfo($filename);
			if( $pi['extension'] != 'yaml' ) {
				unset($entities[$i]);
				continue;
			}
			$filename	= $pi['filename'];
		}
		return $entities;
	}
	
	/** Load an entity descriptor from configuraiton file
	 * @param string $name
	 * @param string $class
	 * @throws Exception
	 * @return EntityDescriptor
	 */
	public static function load($name, $class=null) {
		$descriptorPath	= ENTITY_DESCRIPTOR_CONFIG_PATH.$name;
		$cache	= new FSCache(self::DESCRIPTORCLASS, $name, filemtime(YAML::getFilePath($descriptorPath)));
		
		// Comment when editing class and entity field types
		$descriptor	= null;
		if( !defined('ENTITY_ALWAYS_RELOAD') && $cache->get($descriptor) && isset($descriptor->version) && $descriptor->version==self::VERSION ) {
			return $descriptor;
		}

		$conf	= YAML::build($descriptorPath, true);
// 		debug('Fields conf of '.$name, $conf->fields);
		if( empty($conf->fields) ) {
			throw new Exception('Descriptor file for "'.$name.'" is corrupted, empty or not found, there is no field.');
		}
		// Build descriptor
		//    Parse Config file
		//      Fields
		$fields	= array();
		if( !empty($conf->parent) ) {
			if( !is_array($conf->parent) ) {
				$conf->parent = array($conf->parent);
			}
			foreach( $conf->parent as $p ) {
				$p	= static::load($p);
				if( !empty($p) ) {
					$fields	= array_merge($fields, $p->getFields());
				}
			}
		}
		$IDField	= $class ? $class::getIDField() : self::IDFIELD;
// 		$fields[$IDField]	= (object) array('name'=>$IDField, 'type'=>'ref', 'args'=>(object)array('decimals'=>0, 'min'=>0, 'max'=>4294967295), 'writable'=>false, 'nullable'=>false);
		$fields[$IDField]	= FieldDescriptor::buildIDField($IDField);
		foreach( $conf->fields as $field => $fieldInfos ) {
			$fields[$field]	= FieldDescriptor::parseType($field, $fieldInfos);
		}

		//      Indexes
		$indexes	= array();
		if( !empty($conf->indexes) ) {
			foreach( $conf->indexes as $index ) {
				$iType		= static::parseType(null, $index);
				$indexes[]	= (object) array('name'=>$iType->default, 'type'=>strtoupper($iType->type), 'fields'=>$iType->args);
			}
		}
		//    Save cache output
		$descriptor	= new EntityDescriptor($name, $fields, $indexes, $class);
		if( !empty($conf->flags) ) {
			if( in_array(self::FLAG_ABSTRACT, $conf->flags) ) {
				$descriptor->setAbstract(true);
			}
		}
// 		debug('Entity load('.$name.')', $fields);
		$cache->set($descriptor);
		return $descriptor;
	}
	
	/**
	 * Construct the entity descriptor
	 * 
	 * @param $name string
	 * @param $fields FieldDescriptor[]
	 * @param $indexes stdClass[]
	 * @param $class string
	 */
	protected function __construct($name, $fields, $indexes, $class=null) {
		$this->name		= $name;
		$this->class	= $class;
		$this->fields	= $fields;
		$this->indexes	= $indexes;
		$this->version	= self::VERSION;
	}
	
	/**
	 * @return boolean True if abstract
	 */
	public function isAbstract() {
		return $this->abstract;
	}

	/**
	 * @param boolean True to set descriptor as abstract
	 * @return EntityDescriptor the descriptor
	 */
	public function setAbstract($abstract) {
		$this->abstract = $abstract;
		return $this;
	}
	
	/**
	 * Get the name of the entity
	 * 
	 * @return string The name of the descriptor
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Get one field by name
	 * 
	 * @param $name The field name
	 * @return FieldDescriptor
	 */
	public function getField($name) {
		return isset($this->fields[$name]) ? $this->fields[$name] : null;
	}
	
	/**
	 * @return FieldDescriptor[]
	 */
	public function getFields() {
		return $this->fields;
	}

	/**
	 * @return stdClass[]
	 */
	public function getIndexes() {
		return $this->indexes;
	}

	/**
	 * @return string[]
	 */
	public function getFieldsName() {
		return array_keys($this->fields);
	}
	
	/**
	 * Validate a value for a specified field, an exception is thrown if the value is invalid
	 * @param	string $field The field to use
	 * @param	mixed $value input|output value to validate for this field
	 * @param	$input string[]
	 * @param	PermanentEntity $ref
	 * @throws	InvalidFieldException
	 */
	public function validateFieldValue($field, &$value, $input=array(), $ref=null) {
		if( !isset($this->fields[$field]) ) {
			throw new InvalidFieldException('unknownField', $field, $value, null, $this->name);
		}
		/* @var $Field Field */
		$Field	= $this->fields[$field];
		if( !$Field->writable ) {
			throw new InvalidFieldException('readOnlyField', $field, $value, null, $this->name);
		}
		$TYPE	= $Field->getType();
		
		if( $value === NULL || ($value==='' && $TYPE->emptyIsNull($Field)) ) {
			$value	= null;
			// Look for default value
			if( isset($Field->default) ) {
				$value	= $Field->getDefault();
			} else
			// Reject null value 
			if( !$Field->nullable ) {
				throw new InvalidFieldException('requiredField', $field, $value, null, $this->name);
			}
			// We will format valid null value later (in formatter)
			return;
		}
		// TYPE Validator - Use inheritance, mandatory in super class
		try {
			$TYPE->validate($Field, $value, $input, $ref);
			// Field Validator - Could be undefined
			if( !empty($Field->validator) ) {
				call_user_func_array($Field->validator, array($Field, &$value, $input, &$ref));
			}
		} catch( FE $e ) {
			throw new InvalidFieldException($e->getMessage(), $field, $value, $Field->type, $this->name, $Field->args);
		}

		// TYPE Formatter - Use inheritance, mandatory in super class
		$TYPE->format($Field, $value);
		// Field Formatter - Could be undefined
	}
	
	public function validate(array &$input, $fields=null, $ref=null, &$errCount=0) {
		$data	= array();
// 		$class = $this->class;
// 		debug('validate() - $fields', $fields);
// 		debug('validate() - $ref', $ref);
		foreach( $this->fields as $field => &$fData ) {
			try {
				if( $fields !== NULL && !in_array($field, $fields) ) {
// 					debug('Field not in $fields array');
					unset($input[$field]);
					// If updating, we do not modify a field not in $fields
					// If creating, we set to default a field not in $fields
					if( $ref ) { continue; }
				}
				if( !$fData->writable ) { continue; }
				if( !isset($input[$field]) ) {
					$input[$field] = null;
				}
// 				debug('validate() - '.$field, $input[$field]);
				$this->validateFieldValue($field, $input[$field], $input, $ref);
// 				if( isset($ref) ) {
// 					debug("Ref -> $field => ", $ref->getValue($field));
// 					debug("New value", $uInputData[$field]);
// 				}
// 				debug('Current value ? '.$ref->getValue($field));
				// PHP does not make difference between 0 and NULL, so every non-null value is different from null.
				if( !isset($ref) || ($ref->getValue($field)===NULL XOR $input[$field]===NULL) || $input[$field]!=$ref->getValue($field) ) {
					$data[$field]	= $input[$field];
				}

			} catch( UserException $e ) {
				$errCount++;
				if( isset($this->class) ) {
					$c	= $this->class;
					$c::reportException($e);
				} else {
					reportError($e);
// 					throw $e;
				}
			}
		}
		return $data;
	}

	protected static $types = array();
	
	public static function registerType(TypeDescriptor $type) {
		static::$types[$type->getName()] = $type;
	}
	
	/**
	 * @param string $name Name of the type to get
	 * @param string $type Output parameter for type
	 * @throws Exception
	 * @return TypeDescriptor
	 */
	public static function getType($name, &$type=null) {
		if( !isset(static::$types[$name]) ) {
			throw new Exception('unknownType_'.$name);
		}
		$type	= &static::$types[$name];
		return $type;
	}

	public static function parseType($field, $desc) {
		$result = array('type'=>null, 'args'=>array(), 'default'=>null, 'flags'=>array());
		if( !preg_match('#([^\(\[=]+)(?:\(([^\)]*)\))?(?:\[([^\]]*)\])?(?:=([^\[]*))?#', $desc, $matches) ) {
			throw new Exception('failToParseType');
		}
		$result['type']			= trim($matches[1]);
		$result['args']			= !empty($matches[2]) ? preg_split('#\s*,\s*#', $matches[2]) : array();
		$result['flags']		= !empty($matches[3]) ? preg_split('#\s#', $matches[3], -1, PREG_SPLIT_NO_EMPTY) : array();
// 		if( $field === 'user_like' ) {
// 			debug('$matches('.$desc.')', $matches);
// 		}
		if( isset($matches[4]) ) {
			$result['default']	= $matches[4];
			if( $result['default']==='true' ) {
				$result['default'] = true;
			} else
			if( $result['default']==='false' ) {
				$result['default'] = false;
			} else {
				$len = strlen($result['default']);
				if( $len && $result['default'][$len-1]==')' ) {
					$result['default'] = static::parseType($field, $result['default']);
				}
			}
		}
// 		if( $field === 'user_like' ) {
// 			debug('parseType('.$desc.')', $result);
// 		}
		return (object) $result;
	}
}

// Short Field Exception
class FE extends Exception { }

defifn('ENTITY_DESCRIPTOR_CONFIG_PATH', 'entities/');

// Primary Types

// Format number([max=2147483647 [, min=-2147483648 [, decimals=0]]])
class TypeNumber extends TypeDescriptor {
	protected $name = 'number';
	
	public function parseArgs($fArgs) {
		$args	= (object) array('decimals'=>0, 'min'=>-2147483648, 'max'=>2147483647);
		if( isset($fArgs[2]) ) {
			$args->decimals	= $fArgs[0];
			$args->min			= $fArgs[1];
			$args->max			= $fArgs[2];
		} else if( isset($fArgs[1]) ) {
			$args->min			= $fArgs[0];
			$args->max			= $fArgs[1];
		} else if( isset($fArgs[0]) ) {
			$args->max			= $fArgs[0];
		}
		return $args;
	}

	public function validate($Field, &$value, $input, &$ref) {
		$value	= sanitizeNumber($value);
// 		$value	= str_replace(array(tc('decimal_point'), tc('thousands_sep')), array('.', ''), $value);
		if( !is_numeric($value) ) {
			throw new FE('notNumeric');
		}
		if( $value < $Field->args->min ) {
			throw new FE('belowMinValue');
		}
		if( $value > $Field->args->max ) {
			throw new FE('aboveMaxValue');
		}
	}
	
	public static function getMaxLengthOf($number, $decimals) {
		return strlen((int) $number) + ($decimals ? 1+$decimals : 0);
	}
	public function getHTMLInputAttr($Field) {
		$min		= $Field->arg('min');
		$max		= $Field->arg('max');
		$decimals	= $Field->arg('decimals');
		return array('maxlength'=>max(static::getMaxLengthOf($min, $decimals), static::getMaxLengthOf($max, $decimals)), 'min'=>$min, 'max'=>$max, 'type'=>'number');
	}
	
	public function htmlInputAttr($args) {
		return ' maxlength="'.max(static::getMaxLengthOf($args->min, $args->decimals), static::getMaxLengthOf($args->max, $args->decimals)).'"';
	}
}
EntityDescriptor::registerType(new TypeNumber());

class TypeString extends TypeDescriptor {
	protected $name = 'string';
	
	public function parseArgs($fArgs) {
		$args	= (object) array('min'=>0, 'max'=>65535);
		if( isset($fArgs[1]) ) {
			$args->min			= $fArgs[0];
			$args->max			= $fArgs[1];
		} else if( isset($fArgs[0]) ) {
			$args->max			= $fArgs[0];
		}
		return $args;
	}

	public function validate($Field, &$value, $input, &$ref) {
		$len = strlen($value);
		if( $len < $Field->args->min ) {
			throw new FE('belowMinLength');
		}
		if( $len > $Field->args->max ) {
			throw new FE('aboveMaxLength');
		}
	}
	
	public function getHTMLAttr($Field) {
		$min	= $Field->arg('min');
		$max	= $Field->arg('max');
		return array('maxlength'=>$Field->arg('max'), 'type'=>'text');
	}
	
	public function htmlInputAttr($args) {
		return ' maxlength="'.$args->max.'"';
	}
	
	public function emptyIsNull($field) {
		return $field->args->min > 0;
	}
}
EntityDescriptor::registerType(new TypeString());

class TypeDate extends TypeDescriptor {
	protected $name = 'date';
	/*
	 * Date format is storing a date, not a specific moment, we don't care about timezone
	 */
	
	public function validate($Field, &$value, $input, &$ref) {
		// FR Only for now - Should use user language
		if( is_id($value) ) { return; }
		if( !is_date($value, false, $time) && !is_date($value, false, $time, 'SQL') ) {
			throw new FE('notDate');
		}
		// Format to timestamp
		$value = $time;
	}
	
	public function format($Field, &$value) {
		$value = sqlDate($value);
	}
}
EntityDescriptor::registerType(new TypeDate());

class TypeDatetime extends TypeDescriptor {
	protected $name = 'datetime';
	/*
	 * Date format is storing a date, not a specific moment, we don't care about timezone
	 */
	
	public function validate($Field, &$value, $input, &$ref) {
		if( !empty($input[$Field->name.'_time']) ) {
			$value	.= ' '.$input[$Field->name.'_time'];//Allow HH:MM:SS and HH:MM
		}
		// FR Only for now - Should use user language
		if( is_id($value) ) { return; }
		if( !is_date($value, true, $time) && !is_date($value, true, $time, 'SQL') ) {
			throw new FE('notDatetime');
		}
		// Format to timestamp
		$value	= $time;
	}
	
	public function format($Field, &$value) {
		$value	= sqlDatetime($value);
	}
}
EntityDescriptor::registerType(new TypeDatetime());

class TypeTime extends TypeString {
	protected $name 		= 'time';
	public static $format	= SYSTEM_TIME_FORMAT;
	/*
	 * If $format is changed, don't forget that the current string limit is 5
	 */
	
	public function parseArgs($fArgs) {
		return (object) array('min'=>5, 'max'=>5);
	}
	
	public function validate($Field, &$value, $input, &$ref) {
		if( !is_time($value, $value) ) {
			throw new FE('notTime');
		}
	}
	
	public function format($Field, &$value) {
		$value	= strftime(static::$format, mktime($value[1], $value[2]));
	}
}
EntityDescriptor::registerType(new TypeTime());

// Derived types
class TypeInteger extends TypeNumber {
	protected $name = 'integer';

	public function parseArgs($fArgs) {
		$args	= (object) array('decimals'=>0, 'min'=>-2147483648, 'max'=>2147483647);
		if( isset($fArgs[1]) ) {
			$args->min			= $fArgs[0];
			$args->max			= $fArgs[1];
		} else if( isset($fArgs[0]) ) {
			$args->max			= $fArgs[0];
		}
		return $args;
	}
	
	public function format($Field, &$value) {
		$value = (int) $value;
	}
}
EntityDescriptor::registerType(new TypeInteger());

class TypeBoolean extends TypeInteger {
	protected $name = 'boolean';

	public function parseArgs($fArgs) {
		return (object) array('decimals'=>0, 'min'=>0, 'max'=>1);
	}

	public function validate($Field, &$value, $input, &$ref) {
		$value = (int) !empty($value);
		parent::validate($Field, $value, $input, $ref);
	}
}
EntityDescriptor::registerType(new TypeBoolean());

// Format float([[max=2147483647, min=-2147483648], [decimals=2]]])
class TypeFloat extends TypeNumber {
	protected $name	= 'float';

	public function parseArgs($fArgs) {
		$args	= (object) array('decimals'=>2, 'min'=>-2147483648, 'max'=>2147483647);
		if( isset($fArgs[2]) ) {
			$args->decimals		= $fArgs[0];
			$args->min			= $fArgs[1];
			$args->max			= $fArgs[2];
		} else if( isset($fArgs[1]) ) {
			$args->min			= $fArgs[0];
			$args->max			= $fArgs[1];
		} else if( isset($fArgs[0]) ) {
			$args->decimals		= $fArgs[0];
		}
		return $args;
	}
}
EntityDescriptor::registerType(new TypeFloat());

class TypeDouble extends TypeNumber {
	protected $name	= 'double';

	public function parseArgs($fArgs) {
		$args	= (object) array('decimals'=>8, 'min'=>-2147483648, 'max'=>2147483647);	
		if( isset($fArgs[2]) ) {
			$args->decimals		= $fArgs[0];
			$args->min			= $fArgs[1];
			$args->max			= $fArgs[2];
		} else if( isset($fArgs[1]) ) {
			$args->min			= $fArgs[0];
			$args->max			= $fArgs[1];
		} else if( isset($fArgs[0]) ) {
			$args->decimals		= $fArgs[0];
		}
		return $args;
	}
}
EntityDescriptor::registerType(new TypeDouble());

class TypeNatural extends TypeInteger {
	protected $name		= 'natural';

	public function parseArgs($fArgs) {
		$args	= (object) array('decimals'=>0, 'min'=>0, 'max'=>4294967295);
		if( isset($fArgs[0]) ) {
			$args->max			= $fArgs[0];
		}
		return $args;
	}
}
EntityDescriptor::registerType(new TypeNatural());

class TypeRef extends TypeNatural {
	protected $name		= 'ref';
// 	protected $nullable	= false;
	// MySQL needs more logic to select a null field with an index
	// Prefer to set default to 0 instead of using nullable
	
	public function parseArgs($fArgs) {
		$args	= (object) array('entity'=>null, 'decimals'=>0, 'min'=>0, 'max'=>4294967295);
		if( isset($fArgs[0]) ) {
			$args->entity			= $fArgs[0];
		}
		return $args;
	}

	public function validate($Field, &$value, $input, &$ref) {
		id($value);
		parent::validate($Field, $value, $input, $ref);
	}
}
EntityDescriptor::registerType(new TypeRef());

class TypeEmail extends TypeString {
	protected $name		= 'email';

	public function parseArgs($fArgs) {
		return (object) array('min'=>5, 'max'=>100);
	}

	public function validate($Field, &$value, $input, &$ref) {
		parent::validate($Field, $value, $input, $ref);
		if( !is_email($value) ) {
			throw new FE('notEmail');
		}
	}
}
EntityDescriptor::registerType(new TypeEmail());

class TypePassword extends TypeString {
	protected $name		= 'password';

	public function parseArgs($fArgs) {
		return (object) array('min'=>5, 'max'=>128);
	}

	public function validate($Field, &$value, $input, &$ref) {
		parent::validate($Field, $value, $input, $ref);
		if( empty($input[$Field->name.'_conf']) || $value!=$input[$Field->name.'_conf'] ) {
			throw new FE('invalidConfirmation');
		}
	}
	
	public function format($Field, &$value) {
		$value = hashString($value);
	}
}
EntityDescriptor::registerType(new TypePassword());

class TypePhone extends TypeString {
	protected $name		= 'phone';

	public function parseArgs($fArgs) {
		return (object) array('min'=>10, 'max'=>20);
	}

	public function validate($Field, &$value, $input, &$ref) {
		parent::validate($Field, $value, $input, $ref);
		// FR Only for now - Should use user language
		if( !is_phone_number($value) ) {
			throw new FE('notPhoneNumber');
		}
	}
	
	public function format($Field, &$value) {
		// FR Only for now - Should use user language
		$value = standardizePhoneNumber_FR($value, '.', 2);
	}
}
EntityDescriptor::registerType(new TypePhone());

class TypeURL extends TypeString {
	protected $name	= 'url';

	public function parseArgs($fArgs) {
		return (object) array('min'=>10, 'max'=>400);
	}

	public function validate($Field, &$value, $input, &$ref) {
		parent::validate($Field, $value, $input, $ref);
		if( !is_url($value) ) {
			throw new FE('notURL');
		}
	}
}
EntityDescriptor::registerType(new TypeURL());

class TypeIP extends TypeString {
	protected $name = 'ip';

	public function parseArgs($fArgs) {
		$args	= (object) array('min'=>7, 'max'=>40, 'version'=>null);
		if( isset($fArgs[0]) ) {
			$args->version		= $fArgs[0];
		}
		return $args;
	}

	public function validate($Field, &$value, $input, &$ref) {
		parent::validate($Field, $value, $input, $ref);
		if( !is_ip($value) ) {
			throw new FE('notIPAddress');
		}	
	}
}
EntityDescriptor::registerType(new TypeIP());

class TypeEnum extends TypeString {
	protected $name = 'enum';

	public function parseArgs($fArgs) {
		$args	= (object) array('min'=>1, 'max'=>50, 'source'=>null);
		if( isset($fArgs[0]) ) {
			$args->source		= $fArgs[0];
		}
		return $args;
	}

	public function validate($Field, &$value, $input, &$ref) {
		parent::validate($Field, $value, $input, $ref);
		if( !isset($Field->args->source) ) { return; }
		$values		= call_user_func($Field->args->source, $input, $ref);
		if( is_id($value) ) {
			if( !isset($values[$value]) ) {
				throw new FE('notEnumValue');
			}
			// Get the real enum value from index
			$value	= $values[$value];
		} else
		if( !isset($values[$value]) && !in_array($value, $values) ) {
			throw new FE('notEnumValue');
		}
		// Make it unable to optimize
// 		if( isset($values[$value]) ) {
// 			if( is_scalar($values[$value]) ) {
// 				$value	= $values[$value];
// 			}
// 		} else
// 		if( !in_array($value, $values) ) {
// 			throw new FE('notEnumValue');
// 		}
	}
}
EntityDescriptor::registerType(new TypeEnum());

class TypeState extends TypeEnum {
/*
 DEFAULT VALUE SHOULD BE THE FIRST OF SOURCE
 */
	protected $name = 'state';

	public function validate($Field, &$value, $input, &$ref) {
		TypeString::validate($Field, $value, $input, $ref);
		if( !isset($Field->args->source) ) { return; }
		$values		= call_user_func($Field->args->source, $input, $ref);
		if( !isset($values[$value]) ) {
			throw new FE('notEnumValue');
		}
		if( $ref===NULL ) {
			$value	= key($values);
		} else if( !isset($ref->{$Field->name}) || !isset($values[$ref->{$Field->name}]) || !in_array($value, $values[$ref->{$Field->name}]) ) {
			throw new FE('unreachableValue');
		}
	}
}
EntityDescriptor::registerType(new TypeState());


class TypeObject extends TypeString {
	protected $name = 'object';

	public function parseArgs($fArgs) {
		$args	= (object) array('min'=>1, 'max'=>65535, 'class'=>null);
		if( isset($fArgs[0]) ) {
			$args->class		= $fArgs[0];
			if( $args->class === 'stdClass' ) {
				$args->class	= null;
			}
		}
		return $args;
	}

	public function parseValue(FieldDescriptor $field, $value) {
		if( is_object($value) ) {
			return $value;
		}
		/* @var string $value */
		$class = $field->arg('class');
		if( $class ) {
			if( array_key_exists('Serializable', class_implements($class, true)) ) {
				$obj = new $class();
				$obj->unserialize($value);
				return $obj;
			} else {
				return unserialize($value);
			}
			
		} else {
			return json_decode($value, false);
		}
// 		return $value;
	}

	public function formatValue(FieldDescriptor $field, $value) {
		if( is_string($value) ) {
			return $value;
		}
		/* @var mixed $value */
		$class = $field->arg('class');
		if( $class ) {
			if( !($value instanceof $class) ) {
				throw new Exception('Field '.$field.'\'s value should be an instance of '.$class.', got '.get_class($value));
			}
// 			if( array_key_exists('Serializable', class_implements($class, true)) ) {
			if( $value instanceof Serializable ) {
// 				$obj = new $class();
				return $value->serialize();
			} else {
				return serialize($value);
			}
			
		} else {
			return json_encode($value);
		}
// 		return json_encode($value);
	}
	
// 	public function validate($Field, &$value, $input, &$ref) {
// 		$len = strlen($value);
// 		if( $len < $Field->args->min ) {
// 			throw new FE('belowMinLength');
// 		}
// 		if( $len > $Field->args->max ) {
// 			throw new FE('aboveMaxLength');
// 		}
// 	}

// 	public function getHTMLAttr($Field) {
// 		$min	= $Field->arg('min');
// 		$max	= $Field->arg('max');
// 		return array('maxlength'=>$Field->arg('max'), 'type'=>'text');
// 	}

// 	public function htmlInputAttr($args) {
// 		return ' maxlength="'.$args->max.'"';
// 	}

// 	public function emptyIsNull($field) {
// 		return $field->args->min > 0;
// 	}
}
EntityDescriptor::registerType(new TypeString());

class TypeCity extends TypeString {
	protected $name = 'city';

	public function parseArgs($fArgs) {
		$args = (object) array('min'=>3, 'max'=>30);
		return $args;
	}

	public function format($args, &$value) {
		$value	= str_ucwords($value);
	}
}
EntityDescriptor::registerType(new TypeCity());

class TypePostalCode extends TypeInteger {
	protected $name = 'postalcode';

	public function parseArgs($fArgs) {
		$args	= (object) array('decimals'=>0, 'min'=>10000, 'max'=>99999);
		return $args;
	}
}
EntityDescriptor::registerType(new TypePostalCode());

