<?php

// namespace xPaw;

/**
 * 
 * @author xPaw
 * @author Florent HAZARD <f.hazard@sowapps.com>
 *
 * Class written by xPaw
 * Website: http://xpaw.me
 * GitHub: https://github.com/xPaw/PHP-Minecraft-Query
 * Minecraft Query Doc: http://wiki.vg/Query
 */
class MinecraftQuery {

	const STATISTIC = 0x00;
	const HANDSHAKE = 0x09;

	protected $socket;
	protected $players;
	protected $info;
	
	protected $host;
	protected $port;
	protected $timeout;
	
	public function __construct($host, $port=25565, $timeout=3) {
		$this->setHost($host);
		$this->setPort($port);
		$this->setTimeout($timeout);
	}

// 	public function connect($ip, $port = 25565, $timeout = 3 )
	public function collectInformations() {

		$timeout = $this->getTimeout();
		$this->socket = @fsockopen('udp://'.$this->getHost(), $this->getPort(), $errno, $errstr, $timeout);

		if($errno || $this->socket === false ) {
			throw new Exception('could not create socket: ' . $errstr );
		}

		stream_set_timeout($this->socket, $timeout);
		stream_set_blocking($this->socket, true);

		try {
			$challenge = $this->getChallenge();
			$this->getStatus($challenge);
// 		} catch( Exception $e ) {
// 			// we catch this because we want to close the socket, not very elegant
// 			fclose($this->socket );

// 			throw new Exception($e->getmessage() );
// 			throw new Exception($e->getmessage() );
		} finally {
			fclose($this->socket);
		}
	}

	public function getInfo() {
		return $this->info;
// 		return $this->info ? $this->info : false;
	}

	public function getPlayers() {
		return $this->players;
// 		return $this->players ? $this->players : false;
	}

	protected function getChallenge() {
		$data = $this->writeData(self::HANDSHAKE);

		if( $data === false ) {
			throw new Exception('Failed to receive challenge.');
		}

		return pack('N', $data );
	}

	protected function getStatus($challenge) {
		$startTime = microtime(true);
		$data = $this->writeData(self::STATISTIC, $challenge.pack('c*', 0x00, 0x00, 0x00, 0x00));
		$ping = round(($d=microtime(true)-$startTime)*1000);
		debug('Delta => '.$d);
		unset($startTime);

		if( !$data ) {
			throw new Exception('Failed to receive status.');
		}

		$last = '';
		$info = array();

		$data    = substr($data, 11); // splitnum + 2 int
		$data    = explode("\x00\x00\x01player_\x00\x00", $data);

		if( !isset($data[1]) ) {
			throw new Exception("Failed to parse server's response.");
		}

		$players = substr($data[1], 0, -2 );
		$data    = explode("\x00", $data[0]);

		// array with known keys in order to validate the result
		// it can happen that server sends custom strings containing bad things (who can know!)
		$keys = array(
			'hostname'   => 'hostname',
			'gametype'   => 'gametype',
			'version'    => 'version',
			'plugins'    => 'plugins',
			'map'        => 'map',
			'numplayers' => 'numplayers',
			'maxplayers' => 'maxplayers',
			'hostport'   => 'hostport',
			'hostip'     => 'hostip',
			'game_id'    => 'gamename'
		);
// 		debug('$data', $data);

		$last = null;
		foreach( $data as $value ) {
			if( $last ) {
				// Has key
				if( isset($keys[$last]) ) {
					$info[$keys[$last]] = mb_convert_encoding($value, 'utf-8');
				}
				$last = null;
			} else {
				// No key, got key
				$last = $value;
			}
		}

		// ints
		$info['numplayers']		= intval($info['numplayers']);
		$info['maxplayers'] 	= intval($info['maxplayers']);
		$info['hostport']   	= intval($info['hostport']);
		$info['ping']   		= intval($ping);

		// parse "plugins", if any
		$plugins = $info['plugins'];
		$info['plugins'] = array();
		if( $plugins ) {
			list($info['software'], $plugins) = explodeList(': ', $plugins, 2);

			if( $plugins ) {
				$info['plugins'] = explode('; ', $plugins);
			}
// 			$info['rawplugins'] = $info['plugins'];
// 			$info['software']   = $data[ 0 ];

// 			if( count($data ) == 2 ) {
// 				$info['plugins'] = explode('; ', $data[ 1 ] );
// 			}
		} else {
			$info['software'] = 'vanilla';
// 			$info['plugins'] = array();
		}

		$this->info = (object) $info;

		if( empty($players) ) {
			$this->players = array();
		} else {
			$this->players = explode("\x00", $players);
		}
	}

	protected function writeData($command, $append='') {
		$command = pack('c*', 0xFE, 0xFD, $command, 0x01, 0x02, 0x03, 0x04).$append;
		$length  = strlen($command);

		if( $length !== fwrite($this->socket, $command, $length) ) {
			throw new Exception('Failed to write on socket.');
		}

		$data = stream_get_contents($this->socket);
// 		$data = fread($this->socket, 4096*100);
// 		$data = fread($this->socket, 4096);
// 		debug('Bin $data', explode("\x00", $data));
		
		if( $data === false ) {
			throw new Exception('Failed to read from socket.');
		}

		if( strlen($data ) < 5 || $data[0] != $command[2] ) {
			return false;
		}
// 		debug('$data', $data);
// 		die();

		return substr($data, 5);
	}
	
	public function getSocket() {
		return $this->Socket;
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
	public function getTimeout() {
		return $this->timeout;
	}
	public function setTimeout($timeout) {
		if( !is_int($timeout) || $timeout < 0 ) {
			throw new \InvalidArgumentException('Timeout must be a positive integer.');
		}
		$this->timeout = $timeout;
		return $this;
	}
	
}