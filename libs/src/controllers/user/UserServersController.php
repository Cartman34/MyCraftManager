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
// 				if( !empty($input['rcon_password']) ) {
// 					$input['rcon_password'] = (new PasswordGenerator())->generate();
// 				}
				$server = MinecraftServer::createAndGet($input, array(
					'name', 'software_id', 'ssh_host', 'ssh_port', 'ssh_user', 'ssh_password', 'path', 'rcon_port', 'rcon_password', 'query_port'
				));
// 				$image = File::uploadOne('software_image', $software->getLabel(), FILE_USAGE_SOFTWAREIMAGE, $server);
// 				if( $image ) {
// 					$server->image_id = $image->id();
// 				}
				reportSuccess(MinecraftServer::text('successCreate', $server));
// 				$formData = array();
		
			}
		} catch(UserException $e) {
			reportError($e);
		}
		
		return $this->renderHTML('app/user_servers');
	}

}
