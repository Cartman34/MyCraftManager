<?php

class AdminServerSoftwaresController extends AdminController {
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {

		/* @var $USER User */
// 		global $USER;
// 		$userDomain	= User::getDomain();
		
		$this->addThisToBreadcrumb();
		
// 		$USER_CAN_USER_EDIT	= !CHECK_MODULE_ACCESS || $USER->canUserEdit();
// 		$USER_CAN_DEV_SEE	= !CHECK_MODULE_ACCESS || $USER->canSeeDevelopers();
		
// // 		$formData = array();
		try {
			if( $request->hasData('submitCreate') ) {
				$input = $request->getArrayData('software');
				if( isset($input['file_url']) && ServerSoftware::get()->where('file_url', $input['file_url'])->exists() ) {
					ServerSoftware::throwException('existingSoftwareByURL');
				}
				$software = ServerSoftware::createAndGet($input, array(
					'file_url', 'name', 'version', 'installer_path', 'starter_path'
				));
				$image = File::uploadOne('software_image', $software->getLabel(), FILE_USAGE_SOFTWAREIMAGE, $software);
				if( $image ) {
					$software->image_id = $image->id();
				}
				reportSuccess(ServerSoftware::text('successCreate', $software));
// 				$formData = array();
		
			}
		} catch(UserException $e) {
			reportError($e);
		}
		
// 		$users = User::get(array(
// 				'where'		=> $USER_CAN_DEV_SEE ? '' : 'accesslevel<='.Config::get('user_roles/administrator'),
// 				'orderby'	=> 'fullname ASC',
// 				'output'	=> SQLAdapter::ARR_OBJECTS
// 		));
		
		return $this->renderHTML('app/admin_softwarelist', array(
		));
	}

}
