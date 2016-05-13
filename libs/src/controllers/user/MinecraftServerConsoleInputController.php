<?php

class MinecraftServerConsoleInputController extends HTTPController {
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {

		/* @var $USER User */
// 		global $USER;
		
		/* @var $serverUser MinecraftServerUser */
		/* @var $server MinecraftServer */
		
		// 		debug('TEST_DEBUG');
// 		$user	= &$USER;
		
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
			list($commandProg) = explode(' ', $command, 2);
			if( $commandProg === 'help' ) {
				MinecraftServer::throwException('unavailableCommand');
			}
			$minecraft = $server->getConnector();
			$result = $minecraft->sendCommand($command);
// 			$result .= ' [';
// 			foreach( count_chars($result, 1) as $char => $freq ) {
// 				$result .= ($char ? ', ' : '').$char.' : '.$freq;
// 			}
// 			$result .= ']';
			// New lines are missing due to a MC bug, NOT WORKING THIS WAY
// 			$result = str_replace(" /", "\n", $result);
// 			sendRESTfulJSON($result.'/#/'.nl2br(escapeText(trim($result))));
			sendRESTfulJSON(nl2br(escapeText(trim($result))));
		} catch( Exception $e ) {
			sendRESTfulJSON($e);
		}
		
		sendRESTfulJSON('ok');
		
	}

}
