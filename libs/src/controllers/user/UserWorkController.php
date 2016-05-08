<?php

class UserWorkController extends AdminController {
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {

		/* @var $USER User */
		global $USER;

		return $this->renderHTML('app/user_work', array(
			'user'	=> $USER,
		));
	}

}
