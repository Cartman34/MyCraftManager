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
	
	public function __construct($ssh, $rco, $query) {
		$this->setSSH($ssh);
	}
	
	// Connector
	
	public function start() {
		if( $this->testIsStarted() ) {
			throw new UserException('applicationAlreadyStarted');
		}
	}
	
	public function testIsAvailable() {
		return $this->testSSH();
	}
	
	public function testIsStarted() {
		return $this->testRcon();
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
}
