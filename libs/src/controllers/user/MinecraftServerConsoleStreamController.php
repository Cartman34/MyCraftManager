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
		
		if( !$user->canServerManage(CRAC_CONTEXT_APPLICATION, $server) ) {
			MinecraftServer::throwNotFound();
		}

		/**
		 * https://developer.mozilla.org/fr/docs/Server-sent_events/Using_server-sent_events
		 * http://www.html5rocks.com/en/tutorials/eventsource/basics/
		 */
		ignore_user_abort(true);
		set_time_limit(0);
		ini_set("default_socket_timeout", 3600);
		ini_set('output_buffering', 0);
		apache_setenv('no-gzip', 1);
		ini_set('zlib.output_compression', 0);
		ini_set('implicit_flush', 1);
// 		die('Content type');
		header('Content-Type: text/event-stream');
		header('Cache-Control: no-cache');
		session_write_close();
		while( ob_get_level() && ob_end_clean() );
// 		debug('Headers', headers_list());
// 		$testID = date('r');
// 			debug('Starting mc logs listen through SSH2 V14 => '.$testID);
		flush();
// 		try {
			global $USER;
// 			$serverQuery = $USER->listServers();
// 			$server = $serverQuery->fetch();
			/* @var MinecraftServer $server */
			if( $server ) {
				$ssh = $server->getConnectedSSH();
				$connection = $ssh->getConnection();
				$stream = ssh2_exec($connection, 'tail -n 50 -f /home/minecraft/servers/InfinityServer2016/logs/latest.log');
// 					function endSSHConnection() {
// 						global $connection;
// // 						echo 'Shutdown<br />';
// // 						log_debug('Shutdown connection at '.date('r'));
// 						$connection && fclose($connection);
// 					}
// 					register_shutdown_function('endSSHConnection');
				stream_set_blocking($stream, false);

				$i = 0;
				$missed = 0;
				do {
					$line = fgets($stream);
					if( $line ) {
						echo "data: ".escapeText($line)."\n\n";
// 						echo escapeText($line)."<br>\n";
						flush();
						$missed = 0;
					} else {
						$missed++;
						if( $missed >= 10 ) {
							// Time out 10 and test connection again if aborted
							$missed = 0;
							echo "event: ping\n";//Require something sent
// 							echo "data: ".microtime(true)."\n\n";//Require something sent
							echo "data: ".round(microtime(true)*1000)."\n\n";//Require something sent
// 							echo "data: ".date(DATE_ISO8601)."\n\n";//Require something sent
// 							echo "\n\n";//Require something sent
// 							echo "\x00";//Require something sent
							flush();
							sleep(1);
						}
// 							log_debug('Always connected at '.date('r').', status => '.connection_status());
					}
				} while( !connection_aborted() );
// 					debug('Connection aborted by user at '.date('r'));
// 					log_debug('Connection aborted by user at '.date('r'));
				fclose($connection);
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
