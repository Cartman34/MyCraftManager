<?php

class MinecraftServerController extends AdminController {
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {

		/* @var $server MinecraftServer */
		$serverID	= $request->getPathValue('serverID');
		$server		= MinecraftServer::load($serverID, false);
		
		$this->addRouteToBreadcrumb(ROUTE_USER_SERVERS);
		$this->addThisToBreadcrumb($server.'');
		
// 		if( !$user->canServerManage(CRAC_CONTEXT_APPLICATION, $server) ) {
// 			MinecraftServer::throwNotFound();
// 		}
		
		try {
			if( $request->hasData('submitUpdateServer') ) {
				$server->update($request->getData('server'), array(
					'name', 'ssh_host', 'ssh_port', 'ssh_user', 'path', 'rcon_port', 'rcon_password', 'query_port'
				));
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
				if( !$server->testIsStarted() ) {
					reportWarning(MinecraftServer::text('applicationIsStopped', $server));
				} else
				if( $server->testIsOnline() ) {
					reportSuccess(MinecraftServer::text('applicationIsStarted', $server));
				} else {
					reportWarning(MinecraftServer::text('applicationIsOffline', $server));
				}
				
			} else
			if( $request->hasData('submitTestInstall') ) {
				$isInstalled = $server->testInstall();
				if( $isInstalled === null ) {
					reportSuccess(MinecraftServer::text($server->isInstalled() ? 'applicationAlwaysInstalled' : 'applicationAlwaysUninstalled', $server));
				} else {
					reportWarning(MinecraftServer::text($isInstalled ? 'applicationNowInstalled' : 'applicationNowUninstalled', $server));
				}
				
			} else
			if( $request->hasData('submitStartApplication') ) {
// 				$minecraft = $server->getConnector();
				$server->start();
				reportSuccess(MinecraftServer::text('successStart', $server));
				
			} else
			if( $request->hasData('submitStopApplication') ) {
// 				$minecraft = $server->getConnector();
				$server->stop();
				reportSuccess(MinecraftServer::text('successStop', $server));
				
			} else
			if( $request->hasData('submitInstallApplication') ) {
// 				$minecraft = $server->getConnector();
// 				$minecraft->install();
				$server->install();
				reportSuccess(MinecraftServer::text('successInstall', $server));
			}
		} catch( UserException $e ) {
			reportError($e);
		}
// 		endReportStream();
// 		if( $server->isStarted() && !$server->isOnline() ) {
		if( $server->isStarting() ) {
// 			debug('Is starting');
			$server->testIsOnline();
		}
		
		return $this->renderHTML('app/user_server', array(
// 			'user'			=> $user,
			'server'		=> $server,
			'PageTitle'		=> $server.'',
			'ContentTitle'	=> $server.''
		));
	}

}
