<?php

class UserServersController extends AdminController {
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {

		/* @var $USER User */
// 		global $USER;
		
		$this->addThisToBreadcrumb();
		
		try {
			if( $request->hasData('submitCreate') ) {
				$input = $request->getArrayData('server');
// 				if( isset($input['file_url']) && MinecraftServer::get()->where('file_url', $input['file_url'])->exists() ) {
// 					MinecraftServer::throwException('existingSoftwareByURL');
// 				}
				if( empty($input['rcon_password']) ) {
// 					$gen = new PasswordGenerator();
					$input['rcon_password'] = (new PasswordGenerator())->generate();
				}
// 				if( !empty($input['ssh_password']) ) {
// 					$input['ssh_password'] = (new PasswordGenerator())->generate();
// 				}
				$server = MinecraftServer::make($input);
// 				$server = MinecraftServer::createAndGet($input, array(
// 					'name', 'software_id', 'ssh_host', 'ssh_port', 'ssh_user', 'ssh_password', 'path', 'rcon_port', 'rcon_password', 'query_port'
// 				));
				reportSuccess(MinecraftServer::text('successCreate', $server));
			}
		} catch(UserException $e) {
			reportError($e);
		}

		if( $request->hasParameter('t') ) {
			ignore_user_abort(false);
			set_time_limit(0);
			ini_set("default_socket_timeout", 3600);
			ini_set('output_buffering', 0);
			session_write_close();
			while( ob_get_level() && ob_end_clean() );
			debug('Headers', headers_list());
			debug('Starting mc logs listen through SSH2 => '.date('r'));
			flush();
			try {
				global $USER;
// 				$serverQuery = $USER->listServers();
// 				$server = $serverQuery->fetch();
				/* @var MinecraftServer $server */
// 				if( $server ) {
// 					$ssh = $server->getConnectedSSH();
// 					$connection = $ssh->getConnection();
// // 					$stream = ssh2_exec($connection, 'tail -n 50 -f /home/minecraft/servers/InfinityServer2016/logs/latest.log');
// // 					ob_start();
// 					$stream = ssh2_exec($connection, 'tail -n 50 -f /home/minecraft/servers/InfinityServer2016/logs/latest.log');
// 					stream_set_blocking($stream, false);

// 					stream_set_blocking($stream, true);
					$i = 0;
					do {
						echo 'The i value => '.($i++).'<br />';
						flush();
						sleep(1);
// 						$line = fgets($stream);
// 						if( $line ) {
// 							echo $line.'<br>';
// // 						ob_flush();
// 							flush();
// 						}
// 						sleep(1);
// 					} while( $i++ < 40 );
					} while( 1 );
// 					ob_end_flush();
// 				}
			} catch( Exception $e ) {
// 				ob_end_flush();
				debug("An exception occurred - ".$e->getMessage(), $e);
			}
			die('ENDING SCRIPT => '.date('r'));
		}

		if( $request->hasParameter('m') ) {
// 			ignore_user_abort(false);
			ignore_user_abort(true);
			set_time_limit(0);
			ini_set("default_socket_timeout", 3600);
			ini_set('output_buffering', 0);
			apache_setenv('no-gzip', 1);
			ini_set('zlib.output_compression', 0);
			ini_set('implicit_flush', 1);
			session_write_close();
			while( ob_get_level() && ob_end_clean() );
			debug('Headers', headers_list());
			$testID = date('r');
			debug('Starting mc logs listen through SSH2 V14 => '.$testID);
			flush();
			try {
				global $USER;
				$serverQuery = $USER->listServers();
				$server = $serverQuery->fetch();
				/* @var MinecraftServer $server */
				if( $server ) {
					$ssh = $server->getConnectedSSH();
					$connection = $ssh->getConnection();
// 					$stream = ssh2_exec($connection, 'tail -n 50 -f /home/minecraft/servers/InfinityServer2016/logs/latest.log');
// 					ob_start();
					$stream = ssh2_exec($connection, 'tail -n 50 -f /home/minecraft/servers/InfinityServer2016/logs/latest.log');
					function endSSHConnection() {
						global $connection;
						echo 'Shutdown<br />';
						log_debug('Shutdown connection at '.date('r'));
						$connection && fclose($connection);
					}
					register_shutdown_function('endSSHConnection');
					stream_set_blocking($stream, false);

// 					stream_set_blocking($stream, true);
					$i = 0;
					do {
						$line = fgets($stream);
						if( $line ) {
							echo escapeText($line).'<br>';
// 						ob_flush();
							flush();
						} else {
	// 						usleep(100);//
// 							echo "-\e-";//Require something sent
// 							echo "-\x00-";//Require something sent
							echo "\x00";//Require something sent
							flush();
							sleep(1);
// 							flush();
							log_debug('Always connected at '.date('r').', status => '.connection_status());
// 							echo connection_status().'-';
// 							$i++;
// 							if( $i >= 99 ) {
// 								echo '<br>';
// 								$i = 0;
// 							}
						}
// 					} while( $i++ < 40 );
					} while( !connection_aborted() );
					debug('Connection aborted by user at '.date('r'));
					log_debug('Connection aborted by user at '.date('r'));
					fclose($connection);
// 					ob_end_flush();
				}
			} catch( Exception $e ) {
// 				ob_end_flush();
				debug("An exception occurred - ".$e->getMessage(), $e);
			}
			die('ENDING SCRIPT => '.date('r'));
		}
		
		return $this->renderHTML('app/user_servers');
	}

}
