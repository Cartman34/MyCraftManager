<?php
/**
 * The project class
 * 
 * A project is registered by an user.
 *
 * @property string $create_date
 * @property string $create_ip
 * @property integer $create_user_id
 * @property integer $owner_id
 * @property string $slug
 * @property string $name
 * @property string $version
 * @property string $file_url
 * @property integer $image_id
 * @property string $install_command
 * @property string $start_command
 */
class ServerSoftware extends PermanentEntity {
	/*
	 * http://legacy.feed-the-beast.com/server-downloads
	 */

	//Attributes
	protected static $table		= 'serversoftware';

	// Final attributes
	protected static $fields	= null;
	protected static $validator	= null;
	protected static $domain	= null;

	public function __toString() {
		return escapeText($this->getLabel());
// 		return escapeText($this->name);
	}
	
	public function getLabel() {
		return $this->name.' v'.$this->version;
	}

	public function getImage() {
		return File::load($this->image_id);
	}

	public static function listByName() {
		return static::get()->orderby("name ASC, INET_ATON(SUBSTRING_INDEX(CONCAT(version,'.0.0.0'),'.',4)) DESC");
	}
	
	public function getInstallCommand() {
		return $this->install_command;
	}
	public function getStartCommand() {
		return $this->start_command;
	}
}
ServerSoftware::init();
