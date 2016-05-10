<?php

/**
 * Interface with SSH2 servers.
 * 
 * @author	  Florent HAZARD <f.hazard@sowapps.com>
 */
class SSH2 {

	protected $host;
	protected $port;
	protected $fingerprint;
// 	protected $timeout;
	
	protected $username;
	protected $password;
	
	protected $publicKeyPath;
	
	protected $connection;
	protected $allowingNewFingerprint=false;
	

// 	public function __construct($host, $port=22, $timeout=3) {
	public function __construct($host, $port=22, $fingerprint=null) {
		$this->setHost($host);
		$this->setPort($port);
		$this->setTimeout($timeout);
	}
	
	public function connect() {
		/*
		 * http://php.net/manual/fr/function.ssh2-connect.php
		 * http://php.net/manual/fr/function.ssh2-fingerprint.php
		 * http://php.net/manual/fr/function.ssh2-auth-password.php
		 */
		if( $this->isConnected() ) {
			return false;
		}
		$connection = ssh2_connect($this->host, $this->port);
		if( !$connection ) {
			throw new Exception('Cannot connect to server');
		}
		$fingerprint = ssh2_fingerprint($connection, SSH2_FINGERPRINT_MD5 | SSH2_FINGERPRINT_HEX);
		if( $fingerprint !== $this->fingerprint ) {
			if( $this->allowNewFingerprint ) {
				$this->fingerprint = $fingerprint;
			} else {
				throw new Exception('Unable to verify server fingerprint');
			}
		}
		$authenticated = false;
		if( $this->username && ssh2_auth_password($connection, $this->username, $this->password) ) {
			$authenticated = true;
		}
		if( $authenticated ) {
			$this->connection = $connection;
		}
// 		if (!ssh2_auth_pubkey_file($this->connection, $this->ssh_auth_user, $this->ssh_auth_pub, $this->ssh_auth_priv, $this->ssh_auth_pass)) {
// 			throw new Exception('Autentication rejected by server');
// 		}
		return true;
	}

	public function exec($cmd, &$output=null, &$error=null) {
		// http://php.net/manual/en/function.ssh2-exec.php
		if( !$this->isConnected() ) {
			$this->connect();
		}
		$stream = ssh2_exec($this->connection, $cmd);
		if( $stream === false ) {
			throw new Exception('SSH command failed');
		}
		$errorStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
		$outputStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDIO);
		
		// Enable blocking for both streams
		stream_set_blocking($errorStream, true);
		stream_set_blocking($outputStream, true);
		
		$error = stream_get_contents($errorStream);
		$output = stream_get_contents($outputStream);
		
	}
	
	public function disconnect() {
		unset($this->connection);
	}

	/**
	 * Destructor for this instance.
	 */
	public function __destruct() {
		$this->disconnect();
	}

	public function setPasswordAuthentication($username, $password) {
		$this->username = $username;
		$this->password = $password;
		return $this;
	}
	
	public function isConnected() {
		return !!$this->connection;
	}
	
	public function getHost() {
		return $this->host;
	}
	public function setHost($host) {
		$this->host = $host;
		return $this;
	}
	public function getPort() {
		return $this->port;
	}
	public function setPort($port) {
		$this->port = (int) $port;
		return $this;
	}
	public function getUsername() {
		return $this->username;
	}
// 	public function setUsername($username) {
// 		$this->username = $username;
// 		return $this;
// 	}
	public function getPassword() {
		return $this->password;
	}
// 	public function setPassword($password) {
// 		$this->password = $password;
// 		return $this;
// 	}
	public function getPublicKeyPath() {
		return $this->publicKeyPath;
	}
	public function setPublicKeyPath($publicKeyPath) {
		$this->publicKeyPath = $publicKeyPath;
		return $this;
	}
	public function getConnection() {
		return $this->connection;
	}
	public function isAllowingNewFingerprint() {
		return $this->allowingNewFingerprint;
	}
	public function setAllowingNewFingerprint($allowingNewFingerprint) {
		$this->allowingNewFingerprint = !!$allowingNewFingerprint;
		return $this;
	}
	
	
}
