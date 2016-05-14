<?php

class MinecraftServerTestController extends HTTPController {
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {

		/* @var $server MinecraftServer */
		$serverID	= $request->getPathValue('serverID');
		$server		= MinecraftServer::load($serverID, false);
		
		// Everybody can check a server, so server can send a request to check it
		
		try {
			$result = 'offline';
			if( $server->testIsStarted() ) {
				$result = $server->testIsOnline() ? 'online' : 'started';
			}
			sendRESTfulJSON($result);
		} catch( Exception $e ) {
			sendRESTfulJSON($e);
		}
		
	}

}
