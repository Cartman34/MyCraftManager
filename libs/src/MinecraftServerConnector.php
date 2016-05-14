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
	protected $id;
	protected $path;
	protected $softwareUrl;
	protected $startCommand;
	protected $installCommand;
	protected $installed;
	protected $started;
	
	public function __construct($id, $ssh, $rcon, $query, $path, $softwareUrl, $startCommand, $installCommand, $installed) {
		$this->setId($id);
		$this->setSSH($ssh);
		$this->setRcon($rcon);
		$this->setQuery($query);
		$this->setPath($path);
		$this->setSoftwareUrl($softwareUrl);
		$this->setStartCommand($startCommand);
		$this->setInstallCommand($installCommand);
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
	
	public function install($config) {
		if( $this->isInstalled() ) {
			throw new UserException('applicationAlreadyInstalled');
		}
		$ssh = $this->getSSH();
		$softwareUrl = $this->getSoftwareUrl();
		$urlInfos = (object) pathinfo(urldecode($softwareUrl));

		ignore_user_abort(true);
		set_time_limit(0);
// 		ini_set("default_socket_timeout", 3600);
// 		ini_set('output_buffering', 0);
// 		apache_setenv('no-gzip', 1);
// 		ini_set('zlib.output_compression', 0);
// 		ini_set('implicit_flush', 1);
// 		session_write_close();
// 		while( ob_get_level() && ob_end_clean() );
		
		
// 		debug('Install software => '.$softwareUrl);
// 		debug('Install path => '.$this->getPath());
// 		flush();
		
		$ssh->exec('mkdir -p '.$this->getPath(), $output, $error);
// 		debug('mkdir - output', $output);
// 		debug('mkdir - error', $error);
// 		flush();
		
		$ssh->setCurrentDirectory($this->getPath());
		
		// Wget is quiet but normally output to error stream (this is normal)
		$ssh->exec('wget -q '.$softwareUrl.' -O "'.$urlInfos->basename.'"', $output, $error);
// 		debug('wget - output', $output);
// 		debug('wget - error', $error);
// 		flush();
		
		if( $urlInfos->extension === 'zip' ) {
			$ssh->exec('unzip -qq -o "'.$urlInfos->basename.'"', $output, $error);
// 			debug('unzip - output', $output);
// 			debug('unzip - error', $error);
// 			flush();
			
		} else {
			// jar
			// Start with java -Xmx1024M -Xms1024M -jar minecraft_server.jar nogui
			// Launch once to create configuration ?
		}
		
		$ssh->exec('rm -f "'.$urlInfos->basename.'"', $output, $error);
// 		debug('rm - output', $output);
// 		debug('rm - error', $error);
// 		flush();
		
		$installCommand = $this->getInstallCommand();
		if( $installCommand ) {
			$ssh->execRaw('chmod u+x '.$installCommand, $output, $error);
// 			debug('chmod - output', $output);
// 			debug('chmod - error', $error);
// 			flush();
			
			$ssh->exec($installCommand.' 2> /dev/null', $output, $error);
// 			debug('install - output', $output);
// 			debug('install - error', $error);
// 			flush();
		}

		$ssh->exec('chmod u+x '.$this->getStartCommand(), $output, $error);
		$ssh->exec('mkdir -p logs', $output, $error);
		$ssh->exec('echo "eula=true" > eula.txt', $output, $error);
		$ssh->exec('echo "'.$config.'" > server.properties', $output, $error);
// 		debug('chmod - output', $output);
// 		debug('chmod - error', $error);
// 		flush();
// 		$ssh->execRaw('chmod u+x '.$this->getStartCommand());
		
// 		die();
		/*
		 * File exist, working
		 * ( [[ -e "modpacks^FTBInfinity^2_5_0^FTBInfinitySver.zip" ]] && echo "Exist" ) || echo "Not exist"
		 */
// 		$this->setInstalled(true);
	}
	
	public function testInstall() {
		$ssh = $this->getSSH();
		$ssh->setCurrentDirectory($this->getPath());
		$ssh->exec('( [ -d "logs" ] && [ -w "logs" ] && echo -n "OK" ) || echo -n "NOPE"', $output, $error);
// 		debug('$output => ['.$output.']');
		$this->installed = $output === 'OK';
		return $this->installed;
	}
	
	public function getProcessTag() {
		return 'MC_SERVER_ID='.$this->getId();
	}
	public function start() {
		if( $this->isStarted() ) {
			throw new UserException('applicationAlreadyStarted');
		}
		$ssh = $this->getSSH();
		$ssh->setCurrentDirectory($this->getPath());
		$ssh->exec('nohup bash -c "'.$this->getProcessTag().' '.$this->getStartCommand().'" &> logs/output.log &', $output, $error);
// 		$ssh->exec('nohup bash -c "'.$this->getStartCommand().' 1> logs/output.log 2> logs/error.log &', $output, $error);
// 		$ssh->exec('nohup ./'.$this->getStartCommand().' 1> logs/output.log 2> logs/error.log &', $output, $error);
// 		$ssh->exec('nohup ./'.$this->getStartCommand().' 2> logs/output.log', $output, $error);
		debug('start - output', $output);
		debug('start - error', $error);
		$this->started = true;
	}
	
	public function stop() {
		if( !$this->isStarted() ) {
			throw new UserException('applicationNotStarted');
		}
		$this->getRcon()->command('stop');
		$this->started = false;
	}
	
	/* *** Server *** */
	
	public function getLogsStream() {
		$stream = $this->getSSH()->execRaw('tail -n 100 -f '.$this->path.'/logs/output.log');
// 		$stream = $this->getSSH()->execRaw('tail -n 50 -f /home/minecraft/servers/InfinityServer2016/logs/latest.log');
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
	
	public function getPID() {
		$ssh = $this->getSSH();
		$ssh->setCurrentDirectory($this->getPath());
		$ssh->exec("ps axe | grep '[j]ava.*".$this->getProcessTag()."' | awk '{print $1}'", $output, $error);
// 		$ssh->exec('nohup bash -c "'.$this->getStartCommand().' 1> logs/output.log 2> logs/error.log &', $output, $error);
// 		$ssh->exec('nohup ./'.$this->getStartCommand().' 1> logs/output.log 2> logs/error.log &', $output, $error);
// 		$ssh->exec('nohup ./'.$this->getStartCommand().' 2> logs/output.log', $output, $error);
// 		debug('get pid - output', $output);
// 		debug('get pid - error', $error);
		return $output ? trim($output) : null;
	}
	
	/* *** Application *** */

	protected $players;
	public function listPlayers($refresh=false) {
		if( !$this->isInstalled() || !$this->isStarted() ) {
			return array();
		}
		if( $this->players === null || $refresh ) {
			$this->getInfos($refresh);// Connect & ensure validity
			$this->players = $this->getQuery()->listPlayers();
		}
		return $this->players;
	}
	
	protected $infos;
	public function getInfos($refresh=false) {
		if( !$this->isInstalled() || !$this->isStarted() ) {
			return null;
		}
		if( $this->infos === null || $refresh ) {
			try {
				$mcQuery = $this->getQuery();
				if( $refresh ) {
					$mcQuery->collectInformations();
				}
				$this->infos = $mcQuery->getInfo();
				if( empty($this->infos) || empty($this->infos->hostname) ) {
					$this->started = false;
				}
				
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
	
	public function isStarted() {
		if( $this->started === null ) {
			$this->testIsStarted();
		}
// 		debug('Is completely started ? '.b($this->started));
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
			debug('testRcon() - Exception - '.$e->getMessage(), $e);
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
	public function getStartCommand() {
		return $this->startCommand;
	}
	public function setStartCommand($startCommand) {
		$this->startCommand = $startCommand;
		return $this;
	}
	public function getInstallCommand() {
		return $this->installCommand;
	}
	public function setInstallCommand($installCommand) {
		$this->installCommand = $installCommand;
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
	public function getId() {
		return $this->id;
	}
	public function setId($id) {
		$this->id = $id;
		return $this;
	}
	
	
	
	
	
	
}
