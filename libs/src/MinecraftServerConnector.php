<?php
/**
 * The Minecraft Server Connector class
 * 
 */
class MinecraftServerConnector {
	
	/* @var SSH2 $ssh */
	protected $ssh;
	/* @var Rcon $rcon */
	protected $rcon;
	/* @var MinecraftQuery $query */
	protected $query;
	protected $path;
	protected $softwareUrl;
	protected $startPath;
	protected $installPath;
	protected $installed;
	
	public function __construct($ssh, $rcon, $query, $path, $softwareUrl, $startPath, $installPath, $installed) {
		$this->setSSH($ssh);
		$this->setRcon($rcon);
		$this->setQuery($query);
		$this->setPath($path);
		$this->setSoftwareUrl($softwareUrl);
		$this->setStartPath($startPath);
		$this->setInstallPath($installPath);
		$this->setInstalled($installed);
	}
	
	public function __toString() {
		return 'MinecraftServerConnector('.$this->getSSH()->getHost().')';
	}
	
	// Connector
	
	public function disconnect() {
		$this->ssh->disconnect();
		$this->rcon->disconnect();
		$this->query->disconnect();
	}
	
	public function install() {
		if( $this->isInstalled() ) {
			throw new UserException('applicationAlreadyInstalled');
		}
		$ssh = $this->getSSH();
		$softwareUrl = $this->getSoftwareUrl();
		$urlInfos = (object) pathinfo(urldecode($softwareUrl));

		ignore_user_abort(true);
		set_time_limit(0);
		ini_set("default_socket_timeout", 3600);
		ini_set('output_buffering', 0);
		apache_setenv('no-gzip', 1);
		ini_set('zlib.output_compression', 0);
		ini_set('implicit_flush', 1);
		session_write_close();
		while( ob_get_level() && ob_end_clean() );
		
		
		debug('Install software => '.$softwareUrl);
		debug('Install path => '.$this->getPath());
		flush();
		
		$ssh->exec('mkdir -p '.$this->getPath(), $output, $error);
// 		$ssh->exec('mkdir -p '.$this->getPath().'; echo mkdir', $output, $error);
// 		debug('mkdir - output', $output);
// 		debug('mkdir - error', $error);
// 		flush();
		
// 		$ssh->execRaw('cd '.$this->getPath());
		$ssh->setCurrentDirectory($this->getPath());
// 		$ssh->exec('cd '.$this->getPath().'; pwd', $output, $error);
// 		debug('cd - output', $output);
// 		debug('cd - error', $error);
// 		flush();
		
// 		$ssh->exec('pwd', $output, $error);
// 		debug('pwd - output', $output);
// 		debug('pwd - error', $error);
// 		flush();
		
// 		$ssh->exec('touch testfile-'.uniqid().'; echo touch', $output, $error);
// 		debug('touch - output', $output);
// 		debug('touch - error', $error);
// 		flush();
		
// 		die();
		// Wget is quiet but normally output to error stream (this is normal)
		$ssh->exec('wget -q '.$softwareUrl.' -O '.$urlInfos->basename, $output, $error);
// 		debug('wget - output', $output);
// 		debug('wget - error', $error);
// 		flush();
		
// 		filename = pathinfo($softwareUrl, PATHINFO_EXTENSION);
// 		$extension = pathinfo($softwareUrl, PATHINFO_EXTENSION);
// 		filename = pathinfo($softwareUrl, PATHINFO_EXTENSION);
// 		$extension = $urlInfos->extension;
		if( $urlInfos->extension === 'zip' ) {
			$ssh->exec('unzip -qq -of '.$urlInfos->basename, $output, $error);
// 			debug('unzip - output', $output);
// 			debug('unzip - error', $error);
// 			flush();
			
		} else {
			// jar
			// Start with java -Xmx1024M -Xms1024M -jar minecraft_server.jar nogui
			// Launch once to create configuration ?
		}
		
		$ssh->exec('rm -f '.$urlInfos->basename, $output, $error);
// 		debug('rm - output', $output);
// 		debug('rm - error', $error);
// 		flush();
// 		$ssh->execRaw('rm -f '.$urlInfos->basename);
		
		$installPath = $this->getInstallPath();
		if( $installPath ) {
// 			debug('install - output', $output);
			$ssh->execRaw('chmod u+x '.$installPath, $output, $error);
// 			debug('chmod - output', $output);
// 			debug('chmod - error', $error);
// 			flush();
			
			$ssh->exec('./'.$installPath.' 2> /dev/null', $output, $error);
			debug('install - output', $output);
			debug('install - error', $error);
			flush();
		}

		$ssh->exec('chmod u+x '.$this->getStartPath(), $output, $error);
		debug('chmod - output', $output);
		debug('chmod - error', $error);
		flush();
// 		$ssh->execRaw('chmod u+x '.$this->getStartPath());
		
		die();
		/*
		 * File exist, working
		 * ( [[ -e "modpacks^FTBInfinity^2_5_0^FTBInfinitySver.zip" ]] && echo "Exist" ) || echo "Not exist"
		 */
// 		$this->setInstalled(true);
	}
	
	public function start() {
		if( $this->isStarted() ) {
			throw new UserException('applicationAlreadyStarted');
		}
	}
	
	public function stop() {
		if( !$this->isStarted() ) {
			throw new UserException('applicationNotStarted');
		}
		$this->getRcon()->command('stop');
	}
	
	/* *** Server *** */
	
	public function getLogsStream() {
		$stream = $this->getSSH()->execRaw('tail -n 50 -f /home/minecraft/servers/InfinityServer2016/logs/latest.log');
// 		$stream = $this->getSSH()->execRaw('tail -n 50 -f /home/minecraft/servers/InfinityServer2016/logs/latest.log | grep -v "RCON Listener"');
		stream_set_blocking($stream, false);
		return $stream;
	}
	
	public function exec($command, &$output=null, &$error=null) {
		if( !$this->isAvailable() ) {
			throw new UserException('requireServerAvailable');
		}
		$this->getSSH()->exec($cmd, $output, $error);
	}
	
	protected $available;
	public function isAvailable() {
		if( $this->available === null ) {
			$this->testIsAvailable();
		}
		return $this->available;
	}
	
	public function testIsAvailable() {
		return $this->available = $this->testSSH();
	}
	
	public function testSSH() {
		$ok		= false;
		try {
			$this->ssh->connect();
			$ok = true;
		} catch( Exception $e ) {
			
		}
		return $ok;
	}
	
	/* *** Application *** */

	protected $players;
	public function listPlayers() {
		if( $this->players === null ) {
			$this->getInfos();// Connect & ensure validity
			$this->players = $this->getQuery()->listPlayers();
		}
		return $this->players;
	}
	
	protected $infos;
	public function getInfos() {
		if( $this->infos === null ) {
			try {
				$this->infos = $this->getQuery()->getInfo();
				
			} catch( Exception $e ) {
				$this->infos = array();
				$this->players = array();
			}
		}
		return $this->infos;
	}
	
	public function getMap() {
		$infos = $this->getInfos();
		return $infos ? $infos->map : null;
	}
	
	public function getVersion() {
		$infos = $this->getInfos();
		return $infos ? $infos->version : null;
	}
	
	public function getNumPlayer() {
		$infos = $this->getInfos();
		return $infos ? $infos->numplayers : null;
	}
	
	public function getMaxPlayer() {
		$infos = $this->getInfos();
		return $infos ? $infos->maxplayers : null;
	}
	
	public function sendCommand($command) {
		if( !$this->isStarted() ) {
			throw new UserException('requireApplicationStarted');
		}
// 		debug('RCON', $this->getRcon());
		return $this->getRcon()->command($command);
	}
	
	protected $started;
	public function isStarted() {
		if( $this->started === null ) {
			$this->testIsStarted();
		}
		return $this->started;
	}
	
	public function testIsStarted() {
		return $this->started = $this->testRcon();
	}
	
	public function testRcon() {
		$ok		= false;
		try {
			$this->rcon->connect();
			$ok = true;
		} catch( Exception $e ) {
// 			 debug('testRcon() - Exception - '.$e->getMessage(), $e);
		}
		return $ok;
	}
	
	// Inherited
	
	// Setters & Getters
	public function getSSH() {
		return $this->ssh;
	}
	public function setSSH(SSH2 $ssh) {
		$this->ssh = $ssh;
		return $this;
	}
	public function getRcon() {
		return $this->rcon;
	}
	public function setRcon(Rcon $rcon) {
		$this->rcon = $rcon;
		return $this;
	}
	public function getQuery() {
		return $this->query;
	}
	public function setQuery(MinecraftQuery $query) {
		$this->query = $query;
		return $this;
	}
	public function getStartPath() {
		return $this->startPath;
	}
	public function setStartPath($startPath) {
		$this->startPath = $startPath;
		return $this;
	}
	public function getInstallPath() {
		return $this->installPath;
	}
	public function setInstallPath($installPath) {
		$this->installPath = $installPath;
		return $this;
	}
	public function getPath() {
		return $this->path;
	}
	public function setPath($path) {
		$this->path = $path;
		return $this;
	}
	public function isInstalled() {
		return $this->installed;
	}
	public function setInstalled($installed) {
		$this->installed = $installed;
		return $this;
	}
	public function getSoftwareUrl() {
		return $this->softwareUrl;
	}
	public function setSoftwareUrl($softwareUrl) {
		$this->softwareUrl = $softwareUrl;
		return $this;
	}
	
	
	
	
	
}
