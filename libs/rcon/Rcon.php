<?php

/**
 * Interfaces with Rcon servers. Only handles sending/receiving one packet at
 * a time.
 * @author	  Jonathan Chan <jchan@icebrg.us>
 * @author	  Florent HAZARD <f.hazard@sowapps.com>
 * @copyright   2012 Jonathan Chan <jchan@icebrg.us>
 * @see		 http://wiki.vg/Rcon
 */
class Rcon {
	/**
	 * The reply type for a failed authentication request.
	 * @var int
	 */
	const PACKET_AUTH_FAILURE = -1;

	/**
	 * The packet type for server commands.
	 * @var int
	 */
	const PACKET_COMMAND = 2;
// 	const PACKET_COMMAND = 2;

	/**
	 * The packet type for authentication requests.
	 * @var int
	 */
	const PACKET_AUTH = 3;
// 	const PACKET_AUTH = 3;


	/**
	 * @var string
	 */
	private $_host;

	/**
	 * @var int
	 */
	private $_port = 27015;

	/**
	 * @var string
	 */
	private $_password;

	/**
	 * @var resource
	 */
	private $_socket = null;

	/**
	 * @var int
	 */
	private $_id = 1;

	/**
	 * @var int
	 */
	private $_connectionTimeout;

	/**
	 * @var int
	 */
	private $_timeoutSecs;

	/**
	 * @var int
	 */
	private $_timeoutUsecs;

	/**
	 * Constructs a new instance. Does not connect.
	 * @param string $host
	 * @param int $port
	 * @param string $password
	 * @param int $connectionTimeout Defaults to 5 seconds.
	 * @param int $timeoutSecs Defaults to 0 seconds.
	 * @param int $timeoutUsecs Defaults to 500 microseconds.
	 * @throws \InvalidArgumentException
	 * @see setHost
	 * @see setPort
	 * @see setPassword
	 * @see setConnectionTimeout
	 * @see setTimeout
	 */
	public function __construct($host, $port, $password=null, $connectionTimeout = 5,
								$timeoutSecs = 0, $timeoutUsecs = 5000) {
		$this->setHost($host);
		$this->setPort($port);
		$this->setPassword($password);
		$this->setConnectionTimeout($connectionTimeout);
		$this->setTimeout($timeoutSecs, $timeoutUsecs, false);
	}

	/**
	 * Destructor for this instance.
	 */
	public function __destruct() {
		$this->disconnect();
	}

	/**
	 * @return boolean Whether or not this instance is currently connected to the Rcon server.
	 */
	public function isConnected() {
		return is_resource($this->_socket);
	}

	/**
	 * Connects to the Rcon server and tries to authenticate.
	 * @throws \RuntimeException if we are already connected, on connection
	 * failure, or on authentication failure.
	 */
	public function connect() {
		if( $this->isConnected() ) {
			throw new RuntimeException("We are already connected to the server.");
		}

		$this->_socket = @fsockopen($this->_host, $this->_port, $errNo, $errStr, $this->_connectionTimeout);
// 		debug('Socket');
// 		var_dump($this->_socket); echo '<br />';

		if( !is_resource($this->_socket) ) {
			throw new RuntimeException("Failed to connect to server: $errStr ($errNo).");
		}

		socket_set_timeout($this->_socket, $this->_timeoutSecs, $this->_timeoutUsecs);

		$ret = $this->send(self::PACKET_AUTH, $this->_password);
// 		debug('Auth recv packet', $ret);
		
// 		if( $ret['type'] == self::PACKET_AUTH_FAILURE ) {
		if( $ret['id'] == self::PACKET_AUTH_FAILURE ) {
			throw new RuntimeException("Failed to authenticate.");
		}
	}

	/**
	 * Disconnects from the Rcon server.
	 */
	public function disconnect() {
		if( !$this->isConnected() ) {
			return false;
		}
		@fclose($this->_socket);

		$this->_socket = null;
		return true;
	}

	/**
	 * Sends a packet to the Rcon server and returns the response.
	 * @param $type int The packet type.
	 * @param string $body The packet body.
	 * @param bool $expectSplitResponse Whether or not to expect the response
	 * @return array An associative array of packet parts. Contains id, type,
	 * and body values.
	 * @throws \RuntimeException if we fail to write to the socket, or if the
	 * Rcon server sends an invaild response.
	 */
	public function send($type, $body = '', $expectSplitResponse=false) {
// 		debug("Send $type");
// 		debug("Send ".$body);
		$this->_sendPacket($type, $body);
		$r = $this->_receivePacket($expectSplitResponse);
// 		debug('Received packet ', $r);
// 		$id = $this->_sendPacket(self::PACKET_COMMAND, $command);
// 		$ret = $this->_receivePacket($expectSplitResponse);
		return $r;
// 		return $this->_receivePacket();
	}

	/**
	 * Sends a packet to the Rcon server.
	 * @param int $type The packet type.
	 * @param string $body The packet body.
	 * @return int The packet ID.
	 * @throws \RuntimeException if we fail to write to the socket.
	 */
	private function _sendPacket($type, $body = '') {
		$id = $this->_id;
		$this->_id++;
// 		debug('Send with id #'.$id);

		// Construct packet
		$data = pack("VV", $id, $type) . $body . chr(0) . chr(0);
		$data = pack("V", strlen($data)) . $data;

// 		debug("Write data ");
// 		debug("Write data ".strlen($data));
		$result = fwrite($this->_socket, $data, strlen($data));
// 		$result = @fwrite($this->_socket, $data, strlen($data));
// 		var_dump($result);echo '<br>';
		if( !$result ) {
			throw new RuntimeException("Failed to write to socket.");
		}

		return $id;
	}

	/**
	 * Receives a packet from the Rcon server.
	 * @param bool $expectSplitResponse Whether or not to expect the response
	 * to be split into multiple packets. Note that setting this to true will
	 * effectively make the minimum time this method spends blocked to the
	 * read/write timeout.
	 * @return array An associative array of packet parts. Contains id, type,
	 * and body values.
	 * @throws \RuntimeException If the Rcon server sends an invalid response
	 * stream, or none at all.
	 */
	private function _receivePacket($expectSplitResponse=false) {
		$id = null;
		$type = null;
		$body = null;

		// Read the size (4 bytes = 32-bit integer)
		while ($size = @fread($this->_socket, 4)) {

			$size = unpack('V1size',$size);

			$bytes = @fread($this->_socket, $size["size"]);

			if( $bytes === false ) {
				throw new RuntimeException("Rcon server disconnected.");
			}

// 			debug('$bytes', $bytes);
			$packet = unpack("V1id/V1type/a*body", $bytes);
// 			debug('$packet', $packet);

			if( $id == null ) {
				$id = $packet['id'];
				$type = $packet['type'];
				$body = $packet['body'];
			} else {
				if ($id != $packet['id']) {
					throw new RuntimeException("Responses sent in wrong order - id mismatch.");
				}

				$body .= $packet['body'];
			}

			if ( ! $expectSplitResponse) {
				break;
			}
		}

		return array(
			'id'	=>  $id,
			'type'  =>  $type,
			'body'  =>  $body
		);
	}

	/**
	 * Sends a command to the Rcon server.
	 * @param string $command The command to send.
	 * @param bool $expectSplitResponse Whether or not to expect the response
	 * to be split into multiple packets. Note that setting this to true will
	 * effectively make the minimum time this method spends blocked to the
	 * read/write timeout.
	 * @return string The response body.
	 */
	public function command($command, $expectSplitResponse=false) {
		if( !$this->isConnected() ) {
			$this->connect();
		}
		$ret = $this->send(self::PACKET_COMMAND, $command, $expectSplitResponse);
// 		$id = $this->_sendPacket(self::PACKET_COMMAND, $command);
// 		$ret = $this->_receivePacket($expectSplitResponse);
// 		debug('Command result', $ret);

		return $ret['body'];
	}

	/**
	 * @return int The id of the last packet sent to the Rcon server.
	 * @throws \RuntimeException if no packet has been sent yet.
	 */
	public function getLastId()
	{
		if ($this->_id == 0) {
			throw new \RuntimeException("No packet has been sent yet.");
		}

		return $this->_id - 1;
	}

	/**
	 * @param string $host The location of the Rcon server.
	 * @throws \InvalidArgumentException
	 */
	public function setHost($host)
	{
		if ( ! is_string($host)) {
			throw new InvalidArgumentException("\$host must be a string.");
		}

		$this->_host = $host;
	}

	/**
	 * @param int $port The port the Rcon server is on.
	 * @throws \InvalidArgumentException
	 */
	public function setPort($port)
	{
		if ( ! is_int($port) || $port < 0 || $port > 65535) {
			throw new InvalidArgumentException("\$port must be an integer in the range 0 to 65535.");
		}

		$this->_port = $port;
	}

	/**
	 * @param string $password The password for the Rcon server.
	 * @throws \InvalidArgumentException
	 */
	public function setPassword($password) {
		if ( $password != null && !is_string($password) ) {
			throw new InvalidArgumentException("The password must be a string or null.");
		}
		$this->_password = $password;
	}

	/**
	 * @param float $secs How many seconds to wait when connecting to the Rcon
	 * server.
	 * @throws \InvalidArgumentException
	 */
	public function setConnectionTimeout($secs) {
		if ( ! is_numeric($secs) || $secs < 0) {
			throw new InvalidArgumentException("The connection timeout must be a number greater than 0.");
		}

		$this->_connectionTimeout = $secs;
	}

	/**
	 * @param int $secs How many seconds to wait when reading/writing data
	 * to/from the Rcon server.
	 * @param int $uSecs How many microseconds to wait when reading/writing data
	 * to/from the Rcon server.
	 * @param boolean $apply Whether or not to apply the new timeout to the
	 * socket. If false, the timeout will be applied at the next call to
	 * <code>connect()</code>.
	 * @throws \InvalidArgumentException
	 * @throws \RuntimeException if we fail to set the socket timeout.
	 */
	public function setTimeout($secs, $uSecs, $apply = true) {
		if ( ! is_int($secs) || $secs < 0) {
			throw new InvalidArgumentException("The timeout seconds must be an integer greater than 0.");
		}

		if ( ! is_int($uSecs) || $uSecs < 0) {
			throw new InvalidArgumentException("The timeout microseconds must be an integer greater than 0.");
		}

		$this->_timeoutSecs = $secs;
		$this->_timeoutUsecs = $uSecs;

		if ($apply) {
			if ( ! stream_set_timeout($this->_socket, $secs, $uSecs)) {
				throw new RuntimeException("Failed to set socket timeout.");
			}
		}
	}
}