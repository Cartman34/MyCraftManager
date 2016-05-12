<?php
/** PHP File for the website sources
 * It's your app's library.
 *
 * Author: Florent HAZARD (Sowapps)
 */

addAutoload('File',								'src/File');
addAutoload('UploadedFile',						'src/UploadedFile');

addAutoload('GlobalConfig',						'src/GlobalConfig');
addAutoload('User',								'src/User');
addAutoload('MinecraftServer',					'src/MinecraftServer');
addAutoload('ServerSoftware',					'src/ServerSoftware');

addAutoload('MinecraftQuery',					'src/MinecraftQuery');

addAutoload('RedirectController',				'src/controllers/RedirectController');
addAutoload('HomeController',					'src/controllers/HomeController');
addAutoload('LoginController',					'src/controllers/LoginController');
addAutoload('LogoutController',					'src/controllers/LogoutController');
addAutoload('FileDownloadController',			'src/controllers/FileDownloadController');

addAutoload('AdminController',					'src/controllers/admin/AdminController');
addAutoload('AdminMySettingsController',		'src/controllers/admin/AdminMySettingsController');

addAutoload('AdminServerSoftwaresController',	'src/controllers/admin/AdminServerSoftwaresController');

addAutoload('UserServersController',			'src/controllers/user/UserServersController');
addAutoload('MinecraftServerController',		'src/controllers/user/MinecraftServerController');
addAutoload('MinecraftServerConsoleStreamController',	'src/controllers/user/MinecraftServerConsoleStreamController');

addAutoload('FileDownloadController',			'src/controllers/FileDownloadController');
addAutoload('HomeController',					'src/controllers/HomeController');

// addAutoload('UserWorkController',				'src/controllers/user/UserWorkController');
// addAutoload('UserProjectsController',			'src/controllers/user/UserProjectsController');
// addAutoload('ProjectController',				'src/controllers/user/ProjectController');
// addAutoload('ProjectHistoryController',			'src/controllers/user/ProjectHistoryController');

addAutoload('AdminController',					'src/controllers/admin/AdminController');
addAutoload('AdminUserListController',			'src/controllers/admin/AdminUserListController');
addAutoload('AdminUserEditController',			'src/controllers/admin/AdminUserEditController');
addAutoload('AdminConfigController',			'src/controllers/admin/AdminConfigController');
addAutoload('DevEntitiesController',			'src/controllers/admin/DevEntitiesController');

addAutoload('SetupController',					'src/controllers/setup/SetupController');
addAutoload('StartSetupController',				'src/controllers/setup/StartSetupController');
addAutoload('CheckFileSystemSetupController',	'src/controllers/setup/CheckFileSystemSetupController');
addAutoload('CheckDatabaseSetupController',		'src/controllers/setup/CheckDatabaseSetupController');
addAutoload('InstallDatabaseSetupController',	'src/controllers/setup/InstallDatabaseSetupController');
addAutoload('InstallFixturesSetupController',	'src/controllers/setup/InstallFixturesSetupController');
addAutoload('EndSetupController',				'src/controllers/setup/EndSetupController');

defifn('DOMAIN_SETUP',		'setup');

// addAutoload('Session',							'sessionhandler/dbsession');

// Entities
PermanentEntity::registerEntity('File');
PermanentEntity::registerEntity('User');
PermanentEntity::registerEntity('ServerSoftware');
PermanentEntity::registerEntity('MinecraftServer');

// Fixtures
FixtureRepository::register('User');
FixtureRepository::register('MinecraftServer');

// Hooks

/** Hook 'runModule'
 * 
 */
Hook::register(HOOK_RUNMODULE, function($module) {
	if( getModuleAccess($module) > 0 ) {
		HTMLRendering::$theme = 'admin';
	}
});

function listTimezones() {
	$cache = new APCache('timezones', 'all', 7*86400);
	if( !$cache->get($timezones) ) {
		$timezones = \DateTimeZone::listIdentifiers();
		$cache->set($timezones);
	}
	return $timezones;
}

/** Hook 'startSession'
 * 
 */
// Hook::register('startSession', function () {
// 	if( version_compare(PHP_VERSION, '5.4', '>=') ) {
// 		OSessionHandler::register();
// 	}
// });

function getModuleAccess($module=null) {
	if( $module === NULL ) {
		$module = &$GLOBALS['Module'];
	}
	global $ACCESS;
	return !empty($ACCESS) && isset($ACCESS->$module) ? $ACCESS->$module : -2;
}

/**
 * @param User $user
 */
function sendUserRegistrationEmail($user) {
	$SITENAME	= SITENAME;
	$SITEURL	= DEFAULTLINK;
	$activationLink	= $user->getActivationLink();
	$e	= new Email('Activate your Project Manager');
// 	$e	= new Email('Welcome to The Project Manager');
	$e->setText(<<<BODY
Hi {$user} !

Thank you to join the project manager, <a href="{$activationLink}">click here</a> to activate your account.

Your humble, {$SITENAME}.
BODY
);
	return $e->send($user->email);
}

/**
 * @param User $user
 */
// function sendAdminRegistrationEmail($user) {
// 	$SITENAME	= SITENAME;
// 	$SITEURL	= DEFAULTLINK;
// 	$e	= new Email('Orpheus - Registration of '.$user->fullname);
// 	$e->setText(<<<BODY
// Hi master !

// A new dude just registered on <a href="{$SITEURL}">{$SITENAME}</a>, he is named {$user} ({$user->name}) with email {$user->email}.

// Your humble servant, {$SITENAME}.
// BODY
// );
// 	return $e->send(ADMINEMAIL);
// }

/**
 * @param ThreadMessage $tm
 */
function sendNewThreadMessageEmail($tm) {
// 	$user	= $tm->getUser();
	$SITENAME	= SITENAME;
	$e	= new Email('Orpheus - New message of '.$tm->user_name);
	$e->setText(<<<BODY
Hi master !

{$tm->getUser()} posted a new thread message:
{$tm}

Your humble servant, {$SITENAME}.
BODY
);
	return $e->send(ADMINEMAIL);
}
