<?php
/** The abstract publication class
 * This class implements a publication system to the abstract status class.
 * Its purpose is to be used for articles, posts, comments and other publications.
 * It manages a cache for user editable contents and register automatically some events.
 * The status of the publication can also be managed easily by automations or administrators.
 * Finally, it implements an anti-flood system to avoid spam of publications.
 *
 * Require core plugin.
 * 
 * Events:
 * - 'create'	: When the new publication is created.
 * - 'edit'		: When the publication is edited.
 * 
 * AbstractStatus fields:
 * status.
 * 
 * Publication fields (from AbstractStatus):
 * name, user_id, published, cache, create_time, create_ip, edit_time, edit_ip.
 * 
 */
abstract class AbstractPublication extends AbstractStatus {
	
	//Attributes
	protected static $status = array('draft'=>array('waiting'), 'waiting'=>array('approved', 'rejected'), 'approved'=>array('rejected'), 'rejected'=>array('approved'));
	protected static $fields = array(
		'name', 'user_id', 'user_name', 'published', 'cache',
		'create_time', 'create_ip', 'edit_time', 'edit_ip'
	);
	protected static $editableFields = array('name', 'user_id', 'user_name', 'published');
	protected static $validator = array(
		'name'			=> 'checkName',
		'user_id'		=> 'checkUserID',
		'user_name'		=> 'checkUserName',
		'published'		=> 'checkPublished'
	);
	
	public static $floodDelay = 300;//in seconds.

	// *** OVERRIDDEN METHODS ***
	
	/** Magic string conversion
	 * @return The string valu of this object.
	 * 
	 * The string value is the contents of the publication.
	*/
	public function __toString() {
		return $this->getHTML();
	}
	
	// *** DEV METHODS ***
	
	/** Update this publication object
	 * @sa PermanentObject::update()
	 * 
	 * This update method manages 'name' and 'user_name' fields.
	 */
// 	public function update($uInputData, $fields, $noEmptyError=true) {
// 		if( !User::loggedCanDo(static::$table.'_edit') ) {
// 			throw new UserException('forbiddenUpdate');
// 		}
// 		return parent::update($uInputData, $fields, $noEmptyError);
// 	}
	
	/** Get HTML contents
	 * @param $cacheUpdate True to force the cache to update.
	 * @return The cache content, the generated HTML.
	 * @sa generateHTML()
	 */
	public function getHTML($cacheUpdate=0) {
		if( !strlen($this->cache) || $cacheUpdate ) {
			$this->cache = $this->generateHTML();
		}
		return $this->cache;
	}
	
	/** Generate HTML contents
	 * @return The generated HTML contents.
	 * @overrideit
	 */
	abstract public function generateHTML();
	
	/** Gets permalink
	 * @return The permalink.
	 * @overrideit
	 * 
	 * Gets the unique and permanent link.
	 */
	abstract public function getPermalink();
	
	// *** STATIC METHODS ***
	
	/** Erase all cache for this publication type
	 */
	public static function eraseAllCache() {
		return SQLAdapter::doUpdate(array(
			'table' => static::$table,
			'what' => "cache=''",
		));
	}
	
	// 		** VALIDATION METHODS **
	
	/** Checks a name
	 * @param $inputData The input data from the user.
	 * @param $ref The reference to check the field from.
	 * @return The stripped name.
	 * 
	 * Validates the name field in array $inputData.
	 */
	public static function checkName($inputData, $ref=null) {
		if( empty($inputData['name']) ) {
			if( isset($ref) ) {//UPDATE
				return null;
			}
			throw new UserException('invalidName');
		}
		return strip_tags($inputData['name']);
	}
	
	/** Checks a user id
	 * @param $inputData The input data from the user.
	 * @param $ref The reference to check the field from.
	 * @return The user id as integer.
	 * 
	 * Validates the user_id field in array $inputData.
	 */
	public static function checkUserID($inputData, $ref=null) {
		if( !isset($inputData['user_id']) || !is_ID($inputData['user_id']) ) {
			if( !isset($inputData['user_id']) && isset($ref) ) {//UPDATE
				return null;
			}
			throw new UserException('invalidUserID');
		}
		return (int) $inputData['user_id'];
	}
	
	/** Checks a user name
	 * @param $inputData The input data from the user.
	 * @param $ref The reference to check the field from.
	 * @return The stripped user name.
	 * 
	 * Validates the user_name field in array $inputData.
	 */
	public static function checkUserName($inputData, $ref=null) {
		if( empty($inputData['user_name']) ) {
			if( isset($ref) ) {//UPDATE
				return null;
			}
			throw new UserException('invalidUserName');
		}
		return strip_tags($inputData['user_name']);
	}
	
	/** Check published status
	 * @param $inputData The input data from the user.
	 * @param $ref The reference to check the field from.
	 * @return The published status.
	 * 
	 * Validate the published field in array $inputData.
	 */
	public static function checkPublished($inputData, $ref=null) {
		return !empty($inputData['published']) ? 1 : 0;
	}
	
	/** Check for object
	 * @sa PermanentObject::checkForObject()
	*/
	public static function checkForObject($data, $ref=null) {
		if( empty($data['name']) && empty($data['user_id']) && empty($data['create_ip']) ) {
			return;//Nothing to check.
		}
		$ucheck = ($data['user_id']) ? "user_id={$data['user_id']}" : "create_ip LIKE '{$data['create_ip']}'";
		
		$publication = static::get(array(
			'what' => 'name',
			'where' => 'name = '.static::formatValue($data['name']).
				( (static::$floodDelay)
					? ' OR ( '.(($data['user_id']) ? "user_id={$data['user_id']}" : "create_ip LIKE '{$data['create_ip']}'").' AND create_time >= '.(time()-static::$floodDelay).')'
					: ''
				),
			'output' => SQLAdapter::ARR_FIRST
		));
		if( empty($publication) ) { return; }
// 		global $USER_CLASS;
		if( $publication['name'] == $data['name'] ) {
			static::throwException("entryExisting");
		} else if( static::$floodDelay && !User::loggedCanDo(static::$table.'_safeUse') ) {
			static::throwException("floodDelay");
		}
	}
}
AbstractPublication::init();
