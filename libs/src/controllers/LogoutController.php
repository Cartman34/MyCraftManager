<?php


class LogoutController extends HTTPController {
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {
		
// 		global $USER;
		$user	= User::getLoggedUser();
// 		debug('User is logged in ? '.b(isset($user)));
		if( isset($user) ) {
// 			debug('User is logged in');
			$user->logout();
		}
// 		die('user logged out');
		return new RedirectHTTPResponse(DEFAULTROUTE);
	}

	
}
