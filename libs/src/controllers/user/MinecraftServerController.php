<?php

class MinecraftServerController extends AdminController {
	
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
		
		$serverDomain	= MinecraftServer::getDomain();
		
		$serverID	= $request->getPathValue('serverID');
		$server		= MinecraftServer::load($serverID, false);
		
		try {
			if( $request->hasData('submitUpdateServer') ) {
				$server->update($request->getData('server'), array('name'));
				reportSuccess('successUpdate', $serverDomain);
				
			}
		} catch( UserException $e ) {
			reportError($e);
		}
// 		endReportStream();
		
		return $this->renderHTML('app/user_server', array(
			'user'			=> $user,
			'server'		=> $server,
			'PageTitle'		=> $server.''
		));
	}

}
