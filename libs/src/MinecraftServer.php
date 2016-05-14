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
			$this->connector = new MinecraftServerConnector($this->id(), $this->getSSH(), $this->getRcon(), $this->getMCQuery(), $this->path,
				$software->file_url, $software->getStartCommand(), $software->getInstallCommand(), $this->isInstalled());
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
		$minecraft->install($this->generateServerConfig());
		$this->install_date = sqlDatetime();
	}
	
	public function testInstall() {
		$prevInstalled = $this->isInstalled();
		$minecraft = $this->getConnector();
		$nowInstalled = $minecraft->testInstall();
		if( $prevInstalled != $nowInstalled ) {
			$this->install_date = $nowInstalled ? sqlDatetime() : null;
			if( !$this->install_date ) {
				$this->start_date = null;
				$this->isonline = false;
// 				debug('No more installed');
			}
			$this->save();
			return $nowInstalled;
		}
		return null;// No change
	}

	public function start() {
		if( $this->isStarted() ) {
			return false;
		}
		$minecraft = $this->getConnector();
		$this->start_date = sqlDatetime();
		$this->save();
		$minecraft->start();
	}
	
	public function testIsStarted() {
		$minecraft = $this->getConnector();
		$this->pid = $minecraft->getPID();
		if( !$this->pid && $this->isStarted() ) {
			// The process ended
			$this->start_date = null;
			$this->isonline = false;
// 		} else
// 		if( $this->pid && $this->isStarted() && !$this->isOnline() ) {
			// 
		}
		$this->save();
		return $this->isStarted();
	}

	public function stop() {
		if( !$this->isStarted() ) {
			return false;
		}
		$minecraft = $this->getConnector();
		$minecraft->stop();
		$this->start_date = null;
		$this->isonline = false;
		$this->save();
	}

	public function isInstalled() {
		return !!$this->install_date;
	}
	
	public function isStarted() {
		return !!$this->start_date;
	}
	
	public function isStarting() {
		return $this->isStarted() && !$this->isOnline();
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
			$this->mcQuery = new MinecraftQuery($this->getHost(), $this->getQueryPort());
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
			$this->rcon = new Rcon($this->getHost(), $this->getRconPort(), $this->getRconPassword());
		}
		return $this->rcon;
	}
	
// 	public function testRcon() {
// 		$rcon	= $this->getRcon();
// 		$ok		= false;
// 		try {
// 			$rcon->connect();
// 			$ok = true;
// 		} catch( Exception $e ) {
// // 			 debug('testRcon() - Exception - '.$e->getMessage(), $e);
// 		}
// 		$this->isonline = $ok;
// 		return $ok;
// 	}
	
	public function testIsOnline() {
		$this->isonline = $this->getConnector()->isStarted();
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
			$this->ssh = new SSH2($this->getSSHHost(), $this->getSSHPort(), $this->ssh_fingerprint);
			if( $this->ssh_user ) {
				$this->ssh->setCertificateAuthentication($this->getSSHUser(), $this->getPrivateKeyPath(), $this->getPublicKeyPath());
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
	
	public function generateServerConfig() {
		$queryPort = $this->query_port ? $this->query_port : 25565;
		$rconPort = $this->rcon_port ? $this->rcon_port : 25575;
		$config = <<<EOF
# Automatically generated file from MyCraft Manager
# Minecraft server properties
# Fri May 13 10:11:42 EDT 2016
generator-settings=
op-permission-level=4
allow-nether=true
level-name=world
allow-flight=false
announce-player-achievements=true
level-type=DEFAULT
force-gamemode=false
level-seed=
server-ip=
server-port=25565
online-mode=true
generate-structures=true
motd={$this->name}
enable-query=true
query.port={$queryPort}
enable-rcon=true
rcon.port={$rconPort}
rcon.password={$this->rcon_password}
max-players=20
resource-pack=
max-build-height=256
spawn-npcs=true
white-list=false
spawn-animals=true
snooper-enabled=true
hardcore=false
difficulty=1
pvp=true
enable-command-block=false
player-idle-timeout=0
gamemode=0
spawn-monsters=true
view-distance=10
EOF;
		return $config;	
	}
	
	public function getSlug() {
		return $this->slug;
	}
	public function getName() {
		return $this->name;
	}
	public function getPath() {
		return $this->path;
	}
	public function getHost() {
		return $this->getSSHHost();
	}
	public function getSSHHost() {
		return $this->ssh_host;
	}
	public function getSSHPort() {
		return $this->ssh_port ? $this->ssh_port : 22;
// 		return $this->ssh_port;
	}
	public function getSSHUser() {
		return $this->ssh_user;
	}
	public function getRconPort() {
		return $this->rcon_port ? $this->rcon_port : 25575;
// 		return $this->rcon_port;
	}
	public function getRconPassword() {
		return $this->rcon_password;
	}
	public function getQueryPort() {
		return $this->query_port ? $this->query_port : 25565;
// 		return $this->query_port;
	}
	public function getPid() {
		return $this->pid;
	}
	public function getInstallDate() {
		return $this->install_date;
	}
	public function getStartDate() {
		return $this->start_date;
	}
}
MinecraftServer::init();
