<?php

class MinecraftServerConsoleStreamController extends HTTPController {
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {

		/* @var $USER User */
		global $USER;
		
		/* @var $serverUser MinecraftServerUser */
		/* @var $server MinecraftServer */
		
		// 		debug('TEST_DEBUG');
		$user	= &$USER;
		
		$serverID	= $request->getPathValue('serverID');
		$server		= MinecraftServer::load($serverID, false);
		
// 		if( !$user->canServerManage(CRAC_CONTEXT_APPLICATION, $server) ) {
// 			MinecraftServer::throwNotFound();
// 		}

		/**
		 * https://developer.mozilla.org/fr/docs/Server-sent_events/Using_server-sent_events
		 * http://www.html5rocks.com/en/tutorials/eventsource/basics/
		 */
		ignore_user_abort(true);// Ignore user disconnection
		set_time_limit(0);// No limit to execution
		ini_set('default_socket_timeout', 3600);// Increase timeout of sockets
		
		ini_set('output_buffering', 0);// Disable default output buffering
		apache_setenv('no-gzip', 1);// Disable compression (capturing content)
		ini_set('zlib.output_compression', 0);// Disable compression (capturing content)
		ini_set('implicit_flush', 1);// Flush as soon as possible
// 		die('Content type');
		session_write_close();// Unlock session, close it
// 		debug('Headers', headers_list());
// 		$testID = date('r');
// 			debug('Starting mc logs listen through SSH2 V14 => '.$testID);
// 		try {
			global $USER;
// 			$serverQuery = $USER->listServers();
// 			$server = $serverQuery->fetch();
			/* @var MinecraftServer $server */
			if( $server ) {
				$minecraft = $server->getConnector();
				
				$stream = $minecraft->getLogsStream();

				// Empty the buffer
				while( ob_get_level() && ob_end_clean() );
				if( headers_sent($sentFile, $sentLine) ) {
					throw new \Exception('Can not stream, header already sent by '.$sentFile.':'.$sentLine);
				}
				header('Content-Type: text/event-stream');
				header('Cache-Control: no-cache');
				
				$playerRefresh = time();
				$processRefresh = time()+3;// Delayed refresh
				$missed = 0;
				try {
					do {
						$line = fgets($stream);
						
						if( $line ) {
							// Filter
							if( !strstr($line, 'RCON Listener') ) {
								echo "data: ".str_replace("\n", '<br>', escapeText($line))."\n\n";
								flush();
	// 						} else {
	// 							echo "data: Contains listener\n\n";
	// 							flush();
							}
							$missed = 0;
						} else {
							$missed++;
							if( $missed >= 10 ) {
								// Time out 10 and test connection again if aborted
								$missed = 0;
								echo "event: status\n";//Require something sent
								echo "data: ok\n\n";//Require something sent
	// 							echo "\n\n";//Require something sent
	// 							echo "\x00";//Require something sent
								flush();
							}
							sleep(1);
						}
						
						$now = time();
// 						$now = ms();
						// Sometimes it just happens
						if( ($now - $playerRefresh) >= 5 ) {
							$playerRefresh = $now;
							echo "event: players\n";//Require something sent
							echo "data: ".json_encode($minecraft->listPlayers(true))."\n\n";//Require something sent
						}
// 						if( ($now - $processRefresh) >= 2 ) {
						if( ($now - $processRefresh) >= 5 ) {
							$processRefresh = $now;
// 							debug('process', $minecraft->getProcessInformations());
							echo "event: process\n";//Require something sent
							echo "data: ".json_encode($minecraft->getProcessInformations())."\n\n";//Require something sent
						}
// 						$i++;
					} while( !connection_aborted() );
	// 					debug('Connection aborted by user at '.date('r'));
	// 					log_debug('Connection aborted by user at '.date('r'));
				} catch ( Exception $e ) {
// 					echo $e;
					echo "event: status\n";//Require something sent
					echo "data: disconnected\n\n";//Require something sent
				}
				fclose($stream);
				$minecraft->disconnect();
			}
// 		} catch( Exception $e ) {
// // 				ob_end_flush();
// 				debug("An exception occurred - ".$e->getMessage(), $e);
// 		}
// 		die('ENDING SCRIPT => '.date('r'));
		die();
// 		return $this->renderHTML('app/user_servers');
	}

}
