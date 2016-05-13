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
		
// 		$serverDomain	= MinecraftServer::getDomain();
		
		$serverID	= $request->getPathValue('serverID');
		$server		= MinecraftServer::load($serverID, false);
		
		$this->addThisToBreadcrumb($server.'');
		
// 		if( !$user->canServerManage(CRAC_CONTEXT_APPLICATION, $server) ) {
// 			MinecraftServer::throwNotFound();
// 		}
		
		try {
			if( $request->hasData('submitUpdateServer') ) {
				$server->update($request->getData('server'), array('name'));
				reportSuccess(MinecraftServer::text('successUpdate', $server));
				
			} else
			if( $request->hasData('submitTestServer') ) {
				if( $server->testSSH() ) {
					reportSuccess(MinecraftServer::text('serverIsOnline', $server));
				} else {
					reportWarning(MinecraftServer::text('serverIsOffline', $server));
				}
				
			} else
			if( $request->hasData('submitTestApplication') ) {
				if( $server->testRcon() ) {
					reportSuccess(MinecraftServer::text('applicationIsStarted', $server));
				} else {
					reportWarning(MinecraftServer::text('applicationIsStopped', $server));
				}
				
			} else
			if( $request->hasData('submitInstallApplication') ) {
				$minecraft = $server->getConnector();
				$minecraft->install();
				reportSuccess(MinecraftServer::text('successInstall', $server));
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
