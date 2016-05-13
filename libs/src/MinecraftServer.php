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
 * @property string $path
 * @property string $ssh_host
 * @property ineger $ssh_port
 * @property string $ssh_user
 * @property string $ssh_fingerprint
 * @property ineger $rcon_port
 * @property string $rcon_password
 * @property ineger $query_port
 * @property string $pid
 * @property string $install_date
 * @property string $start_date
 * @property boolean $isonline
 */
class MinecraftServer extends PermanentEntity implements FixtureInterface {

	//Attributes
	protected static $table		= 'minecraftserver';

	// Final attributes
	protected static $fields	= null;
	protected static $validator	= null;
	protected static $domain	= null;
	
	protected $connector;

	public function __toString() {
		return escapeText($this->name);
	}

	/**
	 * @return MinecraftServerConnector
	 */
	public function getConnector() {
		if( !$this->connector ) {
			$software = $this->getServerSoftware();
			$this->connector = new MinecraftServerConnector($this->getSSH(), $this->getRcon(), $this->getMCQuery(), $this->path,
				$software->file_url, $software->starter_path, $software->installer_path, $this->isInstalled());
		}
		return $this->connector;
	}

	/**
	 * @return ServerSoftware
	 */
	public function getServerSoftware() {
		return ServerSoftware::load($this->software_id);
	}

	public function install() {
		$minecraft = $this->getConnector();
		$minecraft->install();
		$this->install_date = sqlDatetime();
	}

	public function isInstalled() {
		return !!$this->install_date;
	}
	
	public function isStarted() {
		return !!$this->start_date;
	}
	
	public function isOnline() {
		// isOnline implies isStarted
		return !!$this->isonline;
	}
	
	public function getConsoleStreamLink() {
		return u('adm_server_console_stream', array('serverID'=>$this->id()));
	}
	
	public function getConsoleInputLink() {
		return u('adm_server_console_input', array('serverID'=>$this->id()));
	}

	/**
	 * @var MinecraftQuery $mcQuery
	 */
	protected $mcQuery;
	
	/**
	 * @return MinecraftQuery
	 */
	public function getMCQuery() {
		if( !$this->mcQuery ) {
// 			debug('MinecraftServer', $this);
			$this->mcQuery = new MinecraftQuery($this->ssh_host, $this->query_port ? $this->query_port : 25565);
		}
		return $this->mcQuery;
	}
	
	/**
	 * MC Query uses UDP, so this is useless, it always returns true
	 */
// 	public function testMCQuery() {
// 		$mcQuery	= $this->getMCQuery();
// 		$ok			= false;
// 		try {
// 			$mcQuery->connect();
// 			$ok = true;
// 		} catch( Exception $e ) {
// 			 debug('testMCQuery() - Exception - '.$e->getMessage(), $e);
// 		}
// 		$this->isonline = $ok;
// 		return $ok;
// 	}

	/**
	 * @var Rcon $rcon
	 */
	protected $rcon;
	
	/**
	 * @return Rcon
	 */
	public function getRcon() {
		if( !$this->rcon ) {
// 			debug('MinecraftServer', $this);
			$this->rcon = new Rcon($this->ssh_host, $this->rcon_port ? $this->rcon_port : 25575, $this->rcon_password);
		}
		return $this->rcon;
	}
	
	public function testRcon() {
		$rcon	= $this->getRcon();
		$ok		= false;
		try {
			$rcon->connect();
			$ok = true;
		} catch( Exception $e ) {
// 			 debug('testRcon() - Exception - '.$e->getMessage(), $e);
		}
		$this->isonline = $ok;
		return $ok;
	}

	/**
	 * @var SSH2 $ssh
	 */
	protected $ssh;
	
	/**
	 * @return SSH2
	 */
	public function getSSH() {
		if( !$this->ssh ) {
// 			debug('MinecraftServer', $this);
			$this->ssh = new SSH2($this->ssh_host, $this->ssh_port ? $this->ssh_port : 22, $this->ssh_fingerprint);
			if( $this->ssh_user ) {
				$this->ssh->setCertificateAuthentication($this->ssh_user, $this->getPrivateKeyPath(), $this->getPublicKeyPath());
			}
		}
		return $this->ssh;
	}
	
	public function testSSH() {
		$ssh	= $this->getSSH();
		$ok		= false;
		try {
			$ssh->connect();
			$ok = true;
		} catch( Exception $e ) {
			
		}
// 		$this->isonline = $ok;
		return $ok;
	}

	public function getConnectedSSH() {
		$ssh = $this->getSSH();
		if( !$ssh->isConnected() ) {
			$ssh->connect();
		}
		return $ssh;
	}

	public function exec($command) {
		$ssh = $this->getConnectedSSH();
		$ssh->exec($command);
	}
	
	public static function getPrivateKeyPath() {
		$path = GlobalConfig::instance()->get('minecraft_ssh_private_key_path');
		if( !$path ) {
			static::throwException('Add config "minecraft_ssh_private_key_path"');
		}
		return $path;
	}
	
	public static function getPublicKeyPath() {
		$path = GlobalConfig::instance()->get('minecraft_ssh_public_key_path');
		if( !$path ) {
			static::throwException('Add config "minecraft_ssh_public_key_path"');
		}
		return $path;
	}
	
	public static function make($input) {
		if( empty($input['ssh_password']) ) {
			static::throwException('invalidSSHPassword');
		}
		$server = MinecraftServer::createAndGet($input, array(
			'name', 'software_id', 'ssh_host', 'ssh_port', 'ssh_user', 'path', 'rcon_port', 'rcon_password', 'query_port'
// 			'name', 'software_id', 'ssh_host', 'ssh_port', 'ssh_user', 'ssh_password', 'path', 'rcon_port', 'rcon_password', 'query_port'
		));
		$ssh = $server->getSSH();
		$ssh->setAllowingNewFingerprint(true);
		$ssh->setPasswordAuthentication($server->ssh_user, $input['ssh_password']);
		$ssh->connect();
		$server->ssh_fingerprint = $ssh->getFingerprint();
		$ssh->sendCertificate(static::getPublicKeyPath());
	}
	
	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see FixtureInterface::loadFixtures()
	 */
	public static function loadFixtures() {
		// ssh-keygen -t rsa -C mycraft -f /home/cartman/www/MyCraftManager/config/mycraft_rsakey -N ""
		// Generate mycraft_rsakey and mycraft_rsakey.pub
// 		http://security.stackexchange.com/questions/23383/ssh-key-type-rsa-dsa-ecdsa-are-there-easy-answers-for-which-to-choose-when

		// TODO generate private & public keys on fs
		// TODO Create minecraft_ssh_private_key_path config
		// TODO Create minecraft_ssh_public_key_path config
	}


}
MinecraftServer::init();
