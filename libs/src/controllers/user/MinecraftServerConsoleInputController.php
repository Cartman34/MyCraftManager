<?php

class MinecraftServerConsoleInputController extends HTTPController {
	
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
		
		try {
			$command = $request->getData('command');
			if( !$command ) {
				throw new UserException('invalidCommand');
			}
			$minecraft = $server->getConnector();
			sendRESTfulJSON(trim($minecraft->sendCommand($command)));
		} catch( Exception $e ) {
			sendRESTfulJSON($e);
		}
		
		sendRESTfulJSON('ok');
		
	}

}
