<?php

class ProjectHistoryController extends AdminController {
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {

		/* @var $USER User */
// 		global $USER;

		/* @var $projectUser ProjectUser */
		/* @var $project Project */
		/* @var $workingDay ProjectWorkingDay */
		
		$projectDomain	= Project::getDomain();
		$projectWorkingDayDomain	= ProjectWorkingDay::getDomain();
		
		$projectID	= $request->getPathValue('projectID');
		$project		= Project::load($projectID, false);
		$projectUser	= ProjectUser::getOne(User::getLoggedUser(), $project);
// 		$workingToday	= $projectUser->getWorkingToday();
		
		try {
// 			debug('Post', $request->getAllData());
			if( $request->hasDataKey('submitAddDayWorkDone', $workingDayID) ) {
				$workingDay = ProjectWorkingDay::load($workingDayID);
				if( $workingDay->projectuser_id != $projectUser->id() ) {
					ProjectUser::throwException('forbiddenOperation');
				}
// 				debug('submitAddWorkingToday - work_done - before => '.$workingToday->work_done);
				$workingDay->addSeconds($request->getData('add_min', 0)*60);
// 				debug('submitAddWorkingToday - work_done - after => '.$workingToday->work_done);
				reportSuccess('successAddWorkingToday', $projectWorkingDayDomain);
				
			}
		} catch( UserException $e ) {
			reportError($e);
		}
		endReportStream();
		
		return $this->renderHTML('app/user_project_history', array(
			'project'		=> $project,
			'projectUser'	=> $projectUser,
			'projectWorkingDays'	=> $projectUser->listWorkingDays(),
// 			'workingToday'	=> $workingToday
		));
	}

}
