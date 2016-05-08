<?php
/* @var HTMLRendering $this */
/* @var HTTPController $Controller */
?><!DOCTYPE html>
<html lang="<?php echo LANGBASE; ?>">
<head>
	<title><?php echo (!empty($MODTITLE) ? $MODTITLE.' :: ' : '' ).SITENAME ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<meta name="Description" content=""/>
	<meta name="Author" content="<?php echo AUTHORNAME; ?>"/>
	<meta name="application-name" content="<?php echo SITENAME;?>" />
	<meta name="msapplication-starturl" content="<?php echo DEFAULTLINK; ?>" />
	<meta name="Keywords" content="carnet"/>
	<meta name="Robots" content="Index, Follow"/>
	<meta name="revisit-after" content="16 days"/>
	<link rel="icon" type="image/png" href="<?php echo STATIC_URL.'images/icon.png'; ?>" />
<?php
foreach(HTMLRendering::$metaprop as $property => $content) {
	echo '
	<meta property="'.$property.'" content="'.$content.'"/>';
}
?>

	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/css/bootstrap.min.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/css/bootstrap-theme.min.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css" type="text/css" media="screen" />
	
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/3.5.2/select2.min.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/3.5.2/select2-bootstrap.min.css" type="text/css" media="screen" />
	
	<link rel="stylesheet" href="<?php echo SITEROOT; ?>static/style/base.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="<?php echo HTMLRendering::getCSSURL(); ?>style.css" type="text/css" media="screen" />
<?php
foreach(HTMLRendering::$cssURLs as $url) {
	echo '
	<link rel="stylesheet" type="text/css" href="'.$url.'" media="screen" />';
}
?>
	
	<!-- External JS libraries -->
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
</head>
<?php
/*
<body class="<?php echo $Module; ?>">
*/
?>
<body>

<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
	<div class="container">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="<?php echo SITEROOT; ?>"><?php echo SITENAME ?></a>
		</div>
		<div class="collapse navbar-collapse">
<?php
// User::isLogged() ? $this->showMenu('topmenu_member') : $this->showMenu('topmenu');
$this->showMenu(User::isLogged() ? 'adminmenu' : 'topmenu');
if( !empty($TOPBAR_CONTENTS) ) { echo $TOPBAR_CONTENTS; }
?>

		</div>
	</div>
</div>

<div class="container">

<?php echo $Content; ?>

</div>
	<!-- JS libraries -->
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/js/bootstrap.min.js"></script>
<!-- 	<script type="text/javascript" src="//shared.sowapps.com/select2/select2-3.5.2/select2.min.js"></script> -->
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/select2/3.5.2/select2.min.js"></script>
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/select2/3.5.2/select2_locale_fr.min.js"></script>
	
	<!-- Our JS scripts -->
	<script type="text/javascript" src="/js/orpheus.js"></script>
	<script type="text/javascript" src="/js/script.js"></script>
<?php
foreach(HTMLRendering::$jsURLs as $url) {
	echo '
	<script type="text/javascript" src="'.$url.'"></script>';
}
if( !DEV_VERSION && HOST === 'orpheus-framework.com' ) {
	// Replace by your own & remove HOST condition
	?>
	
	
<script>
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m);
})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
ga('create', 'UA-54516318-1', 'auto'); ga('send', 'pageview');
</script>
<?php
}
?>

</body>
</html>