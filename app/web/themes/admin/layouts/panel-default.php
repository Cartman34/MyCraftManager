<?php
/* @var HTMLRendering $this */
/* @var HTTPController $Controller */
/* @var User $USER */

if( !isset($title) ) {
	$title	= '';
}

if( !isset($footer) ) {
	$footer	= '';
}

if( !isset($actions) ) {
	$actions	= 0;
} else 
if( empty($title) ) {
	$title	= '&nbsp;';
}

?>
<div class="panel panel-inverse">
	<?php if( !empty($title) ) { ?>
	<div class="panel-heading">
		<h4 class="panel-title"><?php echo $title; ?></h4>
	</div>
	<?php } ?>
	<div class="panel-body">
<?php
echo $Content;
?>

	</div>
	<?php
	echo $footer;
	?>
</div>