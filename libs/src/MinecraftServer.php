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
 * @property ineger $software_id
 * @property string $ssh_host
 * @property ineger $ssh_port
 * @property string $ssh_user
 * @property string $ssh_password
 * @property ineger $rcon_port
 * @property string $rcon_password
 * @property string $pid
 */
class MinecraftServer extends PermanentEntity {

	//Attributes
	protected static $table		= 'minecraftserver';

	// Final attributes
	protected static $fields	= null;
	protected static $validator	= null;
	protected static $domain	= null;

	public function __toString() {
		return escapeText($this->name);
	}

	public function getServerSoftware() {
		return ServerSoftware::load($this->software_id);
	}

}
MinecraftServer::init();
