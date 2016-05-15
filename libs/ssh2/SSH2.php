<?php

/**
 * Interface with SSH2 servers.
 * 
 * @author	  Florent HAZARD <f.hazard@sowapps.com>
 * 
 * apt-get install php5-ssh2
 * Or apt-get install libssh2-php
 */
class SSH2 {
	
	const DEFAULT_PORT = 22;

	protected $host;
	protected $port = self::DEFAULT_PORT;
	protected $fingerprint;
// 	protected $timeout;
	
	protected $username;
	protected $password;
	
	protected $publicKeyPath;
	protected $privateKeyPath;
	protected $passphrase;
	protected $currentDirectory;
// 	protected $term = 'xterm';
// 	protected $shell;
	
	protected $connection;
	protected $allowingNewFingerprint = false;
	
	public function __construct($host, $port=self::DEFAULT_PORT, $fingerprint=null) {
		$this->setHost($host);
		// The port will always have a value
// 		$this->setPort(self::DEFAULT_PORT);
		$this->setPort($port);
		$this->setFingerprint($fingerprint);
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
// 		debug('SSH2 connect');
		$connection = ssh2_connect($this->host, $this->port);
		if( !$connection ) {
			throw new Exception('Cannot connect to server');
		}
		$fingerprint = ssh2_fingerprint($connection, SSH2_FINGERPRINT_MD5 | SSH2_FINGERPRINT_HEX);
// 		debug('Got $fingerprint => '.$fingerprint);
// 		debug('Own $fingerprint => '.$this->fingerprint);
		if( $fingerprint !== $this->fingerprint ) {
			if( $this->allowingNewFingerprint ) {
				$this->setFingerprint($fingerprint);
			} else {
				throw new Exception('Unable to verify server fingerprint');
			}
		}
		$authenticated = false;
// 		debug('SSH2', $this);
		if( $this->privateKeyPath && ssh2_auth_pubkey_file($connection, $this->username, $this->publicKeyPath, $this->privateKeyPath, $this->passphrase) ) {
			$authenticated = true;
		} else
		if( $this->password && ssh2_auth_password($connection, $this->username, $this->password) ) {
			$authenticated = true;
		}
		if( $authenticated ) {
			$this->connection = $connection;
			return true;
		}
		throw new Exception('Failed to authenticate');
// 		if (!ssh2_auth_pubkey_file($this->connection, $this->ssh_auth_user, $this->ssh_auth_pub, $this->ssh_auth_priv, $this->ssh_auth_pass)) {
// 			throw new Exception('Autentication rejected by server');
// 		}
// 		return false;
	}

// 	public function startShell() {
// 		if( !$this->isConnected() ) {
// 			$this->connect();
// 		}
// 		$this->shell = ssh2_shell($this->connection, $this->term);
// // 		stream_set_blocking($this->shell, true);
// 	}
	
// 	public function execRaw($cmd) {
// 		// This solution is not working, we are unable to get content from stream
// 		if( !$this->shell ) {
// 			$this->startShell();
// 		}
// 		debug('Exec command : '.$cmd); flush();
// 		fwrite($this->shell, $cmd.';'.PHP_EOL);
// 		sleep(1);
// 		return $this->shell;
// 	}
	public function execRaw($cmd) {
		if( !$this->isConnected() ) {
// 			debug('not connected');
			$this->connect();
		}
// 		debug('Exec command : '.$cmd);
		// http://php.net/manual/en/function.ssh2-exec.php
		$stream = ssh2_exec($this->connection, ($this->currentDirectory ? 'cd "'.$this->currentDirectory.'"; ' : '').$cmd);
		if( $stream === false ) {
			throw new Exception('SSH command failed');
		}
		return $stream;
	}
	
	public function exec($cmd, &$output=null, &$error=null) {
// 		if( !$this->isConnected() ) {
// 			$this->connect();
// 		}
// 		$stream = ssh2_exec($this->connection, $cmd);
		$stream = $this->execRaw($cmd);
		
		// Test with ssh2_shell(), never got content from stream
// 		stream_set_blocking($stream, true);
// 		$error = '';
// 		$output = '';
// 		while( $buffer = fgets($stream) ) {
// 			flush();
// 			$output .= $buffer;
// 		}
// 		$output = stream_get_contents($stream);
		
		$errorStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
		$outputStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDIO);
		
		// Enable blocking for both streams
		stream_set_blocking($errorStream, true);
		stream_set_blocking($outputStream, true);
		
		$error = stream_get_contents($errorStream);
		$output = stream_get_contents($outputStream);
		
	}
	
	public function sendCertificate($publicKeyPath) {
		// TODO : Avoid duplicate
		// TODO : Use PHP system
		// http://php.net/manual/fr/function.ssh2-publickey-init.php
		// http://php.net/manual/fr/function.ssh2-publickey-add.php
		$this->exec('mkdir -p ~/.ssh; chmod u+rw ~/.ssh ~/.ssh/authorized_keys; echo "'.file_get_contents($publicKeyPath).'" >> ~/.ssh/authorized_keys; chmod go-rwx ~/.ssh ~/.ssh/authorized_keys;');
	}
	
	public function disconnect() {
// 		if( $this->shell ) {
// 			fclose($this->shell);
// 			unset($this->shell);
// 		}
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

	public function setCertificateAuthentication($username, $privateKeyPath, $publicKeyPath=null, $passphrase=null) {
		if( !is_readable($privateKeyPath) || !is_readable($publicKeyPath) ) {
			throw new Exception('Unable to read private key file and public key file, make it readable by web server (apache => www-data).');
		}
		$this->username = $username;
		$this->privateKeyPath = $privateKeyPath;
		$this->publicKeyPath = $publicKeyPath ? $publicKeyPath : $privateKeyPath.'.pub';
		$this->passphrase = $passphrase;
		return $this;
	}

	public function __toString() {
		return 'SSH2('.$this->getHost().':'.$this->getPort().', '.($this->isConnected() ? 'Connected' : 'Disconnected').')';
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
		if( $port ) {
			$this->port = (int) $port;
		}
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
	public function getFingerprint() {
		return $this->fingerprint;
	}
	public function setFingerprint($fingerprint) {
		$this->fingerprint = $fingerprint;
		return $this;
	}
	public function getCurrentDirectory() {
		return $this->currentDirectory;
	}
	public function setCurrentDirectory($currentDirectory) {
		$this->currentDirectory = $currentDirectory;
		return $this;
	}
	
// 	public function getShell() {
// 		return $this->shell;
// 	}
// 	public function setShell($shell) {
// 		$this->shell = $shell;
// 		return $this;
// 	}
// 	public function getTerm() {
// 		return $this->term;
// 	}
// 	public function setTerm($term) {
// 		$this->term = $term;
// 		return $this;
// 	}
	
}
