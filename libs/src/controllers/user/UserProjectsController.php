<?php

class UserProjectsController extends AdminController {
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {

		/* @var $USER User */
		global $USER;

		/* @var $projectUser ProjectUser */
		/* @var $project Project */
		
		$projectDomain	= Project::getDomain();
		
		if( $request->hasData('submitAddProject') ) {
		
			try {
				$input 		= $request->getArrayData('project');
				$project	= Project::make($input);
// 				$project	= Project::createAndGet($input, array('name', 'slug'));
				reportSuccess(Project::text('successCreate', $project));
		
			} catch(UserException $e) {
// 				if( !empty($imageFile) ) {
// 					$imageFile->remove();
// 				}
				reportError($e, $projectDomain);
			}
		} else
		if( $request->hasData('submitEditProject') ) {
		
			try {
				$input 	= $request->getArrayData('project');
// 				debug('$input', $input);
				
				$projectUser = ProjectUser::load($request->getData('projectuser_id'), false);
				if( $projectUser->user_id != $USER->id() ) {
					ProjectUser::throwException('wrongUser');
				}
				$project	= $projectUser->getProject();
				$project->update($input, array('name'));
				
// 				$imageFile	= File::uploadOne('project_image', $project->name, FILE_USAGE_ITEM);
// 				if( $imageFile ) {
// 					$project->image_id	= $imageFile->id();
// // 					$project->save();
// 				}
				reportSuccess(Project::text('successUpdate', $project));
		
			} catch(UserException $e) {
				reportError($e, $projectDomain);
			}
		} else
		if( $request->hasData('submitDeleteProject') ) {
		
			try {
				$projectUser = ProjectUser::load($request->getData('projectuser_id'), false);
				if( $projectUser->user_id != $USER->id() ) {
					ProjectUser::throwException('wrongUser');
				}

				$project	= $projectUser->getProject();
				if( $project->owner_id != $USER->id() ) {
					ProjectUser::throwException('forbiddenOperation');
				}
// 				$project = Project::load($request->getData('project_id'));
				$project->remove();
				reportSuccess(Project::text('successDelete', $project));
		
			} catch(UserException $e) {
				reportError($e, $projectDomain);
			}
		}
		
// 		$USER_CAN_USER_EDIT	= $USER->canUserEdit();
		
// 		$projects	= Project::get()->where('')->orderby('name ASC')->run();
		$projectUsers	= $USER->listProjectUsers();
		
		return $this->renderHTML('app/user_projects', array(
			'projectusers'	=> $projectUsers
		));
	}

}
