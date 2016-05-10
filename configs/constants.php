<?php
/** \file
 * All web site constants.
 *
 * @page constants Constants
 * 
 * This file contains all the main constants, you will often work with it and you need to define your own.
 * You will find here constants like AUTHORNAME and SITENAME, and also path constants.\n
 * Configure others carefully and only if it's really necessary, libraries may require some.\n
 * 
 * Set ERROR_LEVEL to put your website in production (with no error reports to the user).
 * This is compatible with multi-instance architecture, so you can set a dev version and
 * a production version using the same sources on you own server.
 * Official ERROR_LEVEL values are DEV_LEVEL (all errors) and PROD_LEVEL (no errors) and
 * ERROR_LEVEL is set depending on DEV_VERSION value (if set).
 */

defifn('ERROR_LEVEL',		DEV_VERSION && !defined('FORCE_ERRORS') ? DEV_LEVEL : PROD_LEVEL);

defifn('DEV_TOOLS',			DEV_VERSION && (defined('TERMINAL') || !empty($_SERVER['PHP_AUTH_USER'])));

// Theme
defifn('LAYOUT_MENU',		'menu-bootstrap3');

// LIB Initernationalization
defifn('LANGDIR',			'languages/');
defifn('LANG',				'fr_FR');
defifn('LANGBASE',			'fr');
// defifn('LANG',				'en_US');
// defifn('LANGBASE',			'en');
// defifn('LANGBASE',			array_shift(explode('_', LANG, 2)));
defifn('LOCALE',			LANG.'.utf8');

// defifn('LOGSPATH',			pathOf('logs/'));
// defifn('STOREPATH',			pathOf('store/'));
defifn('CACHEPATH',			STOREPATH.'cache/');
defifn('TEMPPATH',			STOREPATH.'temp/');
defifn('FILESTOREPATH',		STOREPATH.'files/');
defifn('DYNCONFIGPATH',		STOREPATH.'config.json');

defifn('STATIC_URL',		SITEROOT.'static/');

// Routes' contants
define('ROUTE_LOGIN',			'login');
define('ROUTE_LOGOUT',			'logout');
define('ROUTE_DASHBOARD',		'user_dashboard');

define('ROUTE_ADM_USERS',		'adm_users');
define('ROUTE_ADM_USER',		'adm_user');
define('ROUTE_ADM_MYSETTINGS',	'adm_mysettings');

define('ROUTE_USER_SERVERS',	'user_servers');
define('ROUTE_USER_SERVER',		'user_server');

// Route
defifn('DEFAULTROUTE',			ROUTE_LOGIN);
// defifn('DEFAULTMEMBERROUTE',	ROUTE_DASHBOARD);
defifn('DEFAULTMEMBERROUTE',	ROUTE_USER_SERVERS);
defifn('DEFAULTHOST',			'domain.com');
defifn('DEFAULTPATH',			'');

// Application
defifn('AUTHORNAME',		'Florent HAZARD');
defifn('SITENAME',			'MyCraft Manager');
defifn('ADMINEMAIL',		'contact@sowapps.com');

// Users
define('USER_SALT',			'f9g6ef@54ç');
defifn('DEFAULT_TIMEZONE',	'Europe/Paris');

define('CRAC_CONTEXT_APPLICATION',	1);
define('CRAC_CONTEXT_AGENCY',		2);
define('CRAC_CONTEXT_RESOURCE',		3);

