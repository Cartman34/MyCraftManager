<?php

class ProjectController extends AdminController {
	
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
		
// 		debug('TEST_DEBUG');
		$user	= &$USER;
		
		$projectDomain	= Project::getDomain();
		$projectWorkingDayDomain	= ProjectWorkingDay::getDomain();
		
		$projectID	= $request->getPathValue('projectID');
// 		if( !is_id($projectID) ) {
// 			Project::throwException('projectNotFound');
// 		}
		$project		= Project::load($projectID, false);
		$projectUser	= ProjectUser::getOne($user, $project);
		if( !$projectUser ) {
			throw new ForbiddenException('projectUserNotFound', Project::getDomain());
		}
// 		$projectUser	= ProjectUser::getOne(User::getLoggedUser(), $project);
		$workingToday	= $projectUser->getWorkingToday();
// 		debug('$workingToday', $workingToday);
		
// 		debug('$workingToday => '.$workingToday->work_date, $workingToday);
		
		// This data could be invalid after processing incoming form
		$userActives	= $user->listActiveProjectUsers();
		$activeProject	= $userActives ? $userActives[0]->getProject() : null;
		$otherActive	= $activeProject && $activeProject->id() != $projectUser->project_id;
		
		try {
			$activeProjectExceptionThrown	= 0;
// 			debug('Post', $request->getAllData());
			if( $request->hasData('submitStartWorkingToday') ) {
				startReportStream('projectToday');
				if( $otherActive ) {
					$activeProjectExceptionThrown	= 1;
					ProjectUser::throwException(t('anotherProjectIsActive', ProjectUser::getDomain(), array('project_name'=>$activeProject.'', 'project_link'=>$activeProject->getLink())));
				}
				$projectUser->startSession();
// 				$workingToday->startSession();
// 				if( $workingToday->isworking ) {
// 					ProjectWorkingDay::throwException('alreadyCurrentlyWorking');
// 				}
// 				$workingToday->lastsubmit_date	= sqlDatetime();
// 				$workingToday->isworking	= 1;
				reportSuccess('successStartWorkingToday', $projectWorkingDayDomain);
				
			} else
			if( $request->hasData('submitUpdateWorkingToday') ) {
				startReportStream('projectToday');
				$projectUser->updateSession();
// 				$workingToday->updateSession();
// 				if( !$workingToday->isworking ) {
// 					ProjectWorkingDay::throwException('notCurrentlyWorking');
// 				}
// 				$workingToday->work_done += TIME-dateToTime($workingToday->lastsubmit_date);
// 				$workingToday->lastsubmit_date	= sqlDatetime();
				reportSuccess('successUpdateWorkingToday', $projectWorkingDayDomain);
				
			} else
			if( $request->hasData('submitEndWorkingToday') ) {
				startReportStream('projectToday');
				$projectUser->endSession();
// 				$workingToday->endSession();
// 				if( !$workingToday->isworking ) {
// 					ProjectWorkingDay::throwException('notCurrentlyWorking');
// 				}
// 				$workingToday->work_done += TIME-dateToTime($workingToday->lastsubmit_date);
// 				$workingToday->lastsubmit_date	= null;
// 				$workingToday->isworking		= 0;
				reportSuccess('successEndWorkingToday', $projectWorkingDayDomain);
				
			} else
			if( $request->hasData('submitAddWorkingToday') ) {
				startReportStream('projectToday');
// 				debug('submitAddWorkingToday - work_done - before => '.$workingToday->work_done);
				$workingToday->addSeconds($request->getData('add_min', 0)*60);
// 				debug('submitAddWorkingToday - work_done - after => '.$workingToday->work_done);
				reportSuccess('successAddWorkingToday', $projectWorkingDayDomain);
				
			} else
			if( $request->hasData('submitUpdateProject') ) {
				$project->update($request->getData('project'), array('name'));
				reportSuccess('successUpdate', $projectDomain);
				
			}
		} catch( UserException $e ) {
			reportError($e);
		}
		endReportStream();
		
// 		$projects	= Project::get()->where('')->orderby('name ASC')->run();
// 		$projectUsers	= ProjectUser::get()->where('user_id='.$USER->id())->orderby('id DESC')->run();
		
		return $this->renderHTML('app/user_project', array(
			'user'			=> $user,
			'project'		=> $project,
			'projectUser'	=> $projectUser,
			'workingToday'	=> $workingToday,
			// If form is processed with an active project, it throws an error, else a warning
			'activeProjectExceptionThrown'	=> $activeProjectExceptionThrown,
			'PageTitle'		=> $project.''
// 			'PageTitle'		=> '*'.$project.'*'
		));
	}

}
