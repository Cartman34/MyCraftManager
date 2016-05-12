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
	protected $startPath;
	protected $installPath;
	
	public function __construct($ssh, $rcon, $query, $path, $startPath, $installPath) {
		$this->setSSH($ssh);
		$this->setRcon($rcon);
		$this->setQuery($query);
		$this->setPath($path);
		$this->setStartPath($startPath);
		$this->setInstallPath($installPath);
	}
	
	// Connector
	
	public function disconnect() {
		$this->ssh->disconnect();
		$this->rcon->disconnect();
		$this->query->disconnect();
	}
	
	public function start() {
		if( $this->isStarted() ) {
			throw new UserException('applicationAlreadyStarted');
		}
	}
	
	/* *** Server *** */
	
	public function getLogsStream() {
		$stream = $this->getSSH()->execRaw('tail -n 50 -f /home/minecraft/servers/InfinityServer2016/logs/latest.log');
// 		$stream = $this->getSSH()->execRaw('tail -n 50 -f /home/minecraft/servers/InfinityServer2016/logs/latest.log | grep -v "RCON Listener"');
// 		$stream = $this->getSSH()->execRaw('tail -n 50 -f /home/minecraft/servers/InfinityServer2016/logs/latest.log');
// 		$stream = ssh2_exec($connection, 'tail -n 50 -f /home/minecraft/servers/InfinityServer2016/logs/latest.log');
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
	
	public function listPlayers() {
		return $this->getQuery()->listPlayers();
	}
	
	public function getInfos() {
		return $this->getQuery()->getInfo();
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
	
	
	
}
