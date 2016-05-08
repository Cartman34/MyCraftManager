<?php
/* @var User $USER */

global $USER;

// http://fortawesome.github.io/Font-Awesome/icons/
// $modIcons = array(
// 	'adm_projects'	=> 'fa-briefcase',
// 	'adm_users'		=> 'fa-users',
// 	'adm_partners'	=> 'fa-hand-o-right',
// 	'adm_logs'		=> 'fa-bolt',
// // 	'adm_logs'		=> 'fa-archive',
// 	'dev_entities'	=> 'fa-magic',
// // 	'dev_entities'	=> 'fa-gears',
// );

echo '
<ul class="nav navbar-nav side-nav menu '.$menu.'">';
// <i class="fa fa-fw fa-arrows-v"></i>

// if( empty($items) ) { return; }
foreach( $items as $item ) {
// 	$icon = isset($modIcons[$item->module]) ? '<i class="fa '.$modIcons[$item->module].'"></i>' : '';
//'.$icon.' 
	if( $item->route === ROUTE_USERPROJECTS ) {
		if( $USER ) {
			$currentRequest			= HTTPRequest::getMainRequest();
			$rProjectActive			= is_current_route(ROUTE_PROJECT);
			$rUserProjectsActive	= is_current_route(ROUTE_USERPROJECTS);
			echo '
	<li class="item user_projects'.(($rProjectActive || $rUserProjectsActive) ? ' active' : '').'">
		<a onClick="javascript: return false;" href="'.u(ROUTE_USERPROJECTS).'" data-toggle="collapse" data-target="#myProjectsMenu">Mes Projets <i class="fa fa-fw fa-caret-down"></i></a>
		<ul id="myProjectsMenu" class="collapse'.(($rProjectActive || $rUserProjectsActive) ? ' in' : '').'">
			<li'.($rUserProjectsActive ? ' class="active"' : '').'>
				<a href="'.u(ROUTE_USERPROJECTS).'"><i class="fa fa-fw fa-list"></i> Tous mes projets</a>
			</li>';
			foreach( $USER->listProjectUsers() as $projectUser ) {
				$project	= $projectUser->getProject();
				echo '
			<li'.(($rProjectActive && $currentRequest->getPathValue('projectID')==$project->id()) ? ' class="active"' : '').'>
				<a href="'.$project->getLink().'">'.$project.'</a>
			</li>';
			}
			echo '
		</ul>
	</li>';
		}
		continue;
	}
	echo '
	<li class="item'.(isset($item->route) ? ' '.$item->route : '').(!empty($item->current) ? ' active' : '').'"><a href="'.$item->link.'">'.$item->label.'</a></li>';
}
echo '
</ul>';
