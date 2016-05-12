<?php
/* @var HTMLRendering $this */
/* @var HTTPController $Controller */
/* @var HTTPRequest $Request */
/* @var HTTPRoute $Route */
/* @var User $user */
/* @var MinecraftServer $server */

global $formData;

HTMLRendering::useLayout('page_skeleton');

$isInstalled	= $server->isInstalled();
$isOnline		= $isInstalled && $server->isOnline();
$formData		= array('server'=>$server->all);
$software		= $server->getServerSoftware();
// debug('$server', $server);
// debug('$formData', $formData);

// debug('date => '.date('r.u'));
// debug('DateTime => '.(new DateTime())->format('r.u'));
// debug('DateTime => '.microtime(true));
?>

<?php
if( !$isInstalled ) {
	?>
<div class="row">
	<div class="col-md-6 col-md-offset-3">
		<form method="POST">
		<?php HTMLRendering::useLayout('panel-default'); ?>
		
			<div class="text-center">
				<p class="help-block">
					Pour continuer, votre nouvelle application Minecraft doit être déployée sur votre serveur,
					elle sera automatiquement installée, configurée et prête à être démarrée par la suite.
				</p>
				<button name="submitInstall" type="submit" class="btn btn-success btn-lg" data-submittext="Installation en cours...">Installer</button>
			</div>
		
		<?php HTMLRendering::endCurrentLayout(array('title'=>'Installer le serveur')); ?>
		</form>
	</div>
</div>
	<?php
}
?>

<div class="row">
	<div class="col-lg-6">
		<form method="POST">
		<?php HTMLRendering::useLayout('panel-default'); ?>
		
				<p class="help-block">
					Veillez à ce que la configuration soit correcte et permette à MyCraft Manager d'accéder à votre serveur.
				</p>
				<div class="row">
					<div class="col-md-6 form-group">
						<label class="control-label" for="NewMinecraftServerName">Nom</label>
						<input<?php _inputValue('server/name'); ?> name="server[name]" type="text" class="form-control" id="MinecraftServerName" required>
					</div>
					<div class="col-md-6 form-group">
						<label class="control-label">Application</label>
						<p class="form-control-static"><?php echo $software; ?></p>
						<?php /*
						<select name="server[software_id]" class="select form-control" id="MinecraftServerSoftware" required>
							<?php _htmlOptions('server/software_id', ServerSoftware::listByName()->run(), $formData['server']['software_id'], OPT_PERMANENTOBJECT); ?>
						</select>
						*/ ?>
					</div>
				</div>
				<p class="help-block">
					L'application doit se connecter à votre serveur, pour préserver la sécurité de votre serveur,
					le mot de passe n'a été pas conservé, un certificat a été laissé sur votre serveur.
				</p>
				<div class="row">
					<div class="col-sm-4 form-group">
						<label for="NewMinecraftServerSSHHost">Serveur SSH</label>
						<input<?php _inputValue('server/ssh_host'); ?> name="server[ssh_host]" type="text" class="form-control" id="MinecraftServerSSHHost" required>
					</div>
					<div class="col-sm-4 form-group">
						<label for="NewMinecraftServerSSHPort">Port SSH</label>
						<input<?php _inputValue('server/ssh_port'); ?> name="server[ssh_port]" type="text" class="form-control" id="MinecraftServerSSHPort" placeholder="22">
					</div>
					<div class="col-sm-4 form-group">
						<label for="NewMinecraftServerSSHUser">Utilisateur SSH</label>
						<input<?php _inputValue('server/ssh_user'); ?> name="server[ssh_user]" type="text" class="form-control" id="MinecraftServerSSHUser" required>
					</div>
				</div>
				<?php /*
				<div class="row">
					<div class="col-sm-8 form-group">
						<label for="NewMinecraftServerSSHUser">Utilisateur SSH</label>
						<input<?php _inputValue('server/ssh_user'); ?> name="server[ssh_user]" type="text" class="form-control" id="MinecraftServerSSHUser" required>
					</div>
					<div class="col-sm-4 form-group">
						<label for="NewMinecraftServerSSHPassword">Mot de passe SSH</label>
						<input<?php _inputValue('server/ssh_password'); ?> name="server[ssh_password]" type="password" class="form-control" id="MinecraftServerSSHPassword">
					</div>
				</div>
				*/ ?>
				<div class="row">
					<div class="col-sm-8 form-group">
						<label for="NewMinecraftServerPath">Chemin sur le serveur</label>
						<input<?php _inputValue('server/path'); ?> name="server[path]" type="text" class="form-control" id="MinecraftServerPath" placeholder="Chemin vers le dossier où installer l'application">
					</div>
					<div class="col-sm-4 form-group">
						<label for="NewMinecraftServerQueryPort">Port Query</label>
						<input<?php _inputValue('server/query_port'); ?> name="server[query_port]" type="text" class="form-control" id="MinecraftServerQueryPort" placeholder="25565">
					</div>
				</div>
				<div class="row">
					<div class="col-sm-8 form-group">
						<label for="NewMinecraftServerRconPassword">Mot de passe Rcon</label>
						<input<?php _inputValue('server/rcon_password'); ?> name="server[rcon_password]" type="password" class="form-control" id="MinecraftServerRconPassword" placeholder="Automatique">
					</div>
					<div class="col-sm-4 form-group">
						<label for="NewMinecraftServerRconPort">Port Rcon</label>
						<input<?php _inputValue('server/rcon_port'); ?> name="server[rcon_port]" type="text" class="form-control" id="MinecraftServerRconPort" placeholder="25575">
					</div>
				</div>
		
		<?php HTMLRendering::endCurrentLayout(array('title'=>'Modifier les paramètres du serveur', 'footer'=>'
<div class="panel-footer text-right">
	<button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
	<button name="submitUpdate" type="submit" class="btn btn-primary" data-submittext="Enregistrement...">'.t('save').'</button>
</div>')); ?>
		</form>
	</div>
<!-- </div> -->

<?php
// if( $isInstalled ) {
	?>
<!-- <div class="row"> -->
	<div class="col-lg-6">
		<?php HTMLRendering::useLayout('panel-default'); ?>
		
<!-- 			<p> -->
<!-- 			Gérez vos projets, enregistrez les ici ! -->
<!-- 			</p> -->
			<div class="row">
				<div class="col-md-2 text-center">
					<i class="fa fa-4x fa-power-off" style="color: <?php echo $server->isOnline() ? '#2FCF2E' : '#808080'; ?>;"></i>
				</div>
				<div class="col-md-5 form-group">
					<label class="control-label">Démarré le</label>
					<p class="form-control-static"><?php echo $server->start_date ? dt($server->start_date) : 'N/C'; ?></p>
				</div>
				<div class="col-md-5 form-group">
					<label class="control-label">Installé le</label>
					<p class="form-control-static"><?php echo $server->install_date ? dt($server->install_date) : 'N/C'; ?></p>
				</div>
			</div>
		
		<?php HTMLRendering::endCurrentLayout(array('title'=>'État du serveur', 'footer'=>'
<div class="panel-footer text-right"><form method="POST">
	<button name="submitTestServer" type="submit" class="btn btn-default" data-submittext="Test du serveur...">'.MinecraftServer::text('checkServer').'</button>
	<button name="submitTestApplication" type="submit" class="btn btn-default" data-submittext="Test de l\'application...">'.MinecraftServer::text('checkApplication').'</button>'.
	($server->isOnline() ? '
		<button name="submitStop" type="submit" class="btn btn-warning" data-submittext="Arrêt en cours...">'.MinecraftServer::text('stop').'</button>' : '
		<button name="submitStart" type="submit" class="btn btn-primary" data-submittext="Démarrage...">'.MinecraftServer::text('start').'</button>').
'</form></div>')); ?>
	</div>
	
	<div class="col-lg-6">
		<?php HTMLRendering::useLayout('panel-default'); ?>
		
<!-- 			<p> -->
<!-- 			Gérez vos projets, enregistrez les ici ! -->
<!-- 			</p> -->
			<ul class="list-group consolestream"></ul>
			<div class="input-group">
				<input type="text" class="form-control" id="ConsoleInput" placeholder="Entrez votre commande">
				<span class="input-group-btn"><button class="btn btn-default" id="ConsoleSendButton" title="Cliquez pour envoyer" type="button">Envoyer</button></span>
			</div>
		
		<?php HTMLRendering::endCurrentLayout(array('title'=>'Console')); ?>
	</div>
<script type="text/javascript">
$(function() {
	//https://developer.mozilla.org/fr/docs/Server-sent_events/Using_server-sent_events
// 	$(".consolestream")
	var source = new EventSource('http://flo.mcm.sowapps.com/user/server/8/console.html');
	var consolePing = $('<small class="ml6"></small>');
	var consoleIcon = $('<i class="fa fa-fw fa-power-off ml6" style="color: #808080;" data-offline="#808080" data-online="#2FCF2E"></i>');
	var consoleTitle = $(".consolestream").closest(".panel").find(".panel-title").first();
	consoleTitle.append(consoleIcon);
	consoleTitle.append(consolePing);
	var consoleList = $(".consolestream").first();
	var attachedScroll = true;
	var scrollMax = consoleList.height();
// 	console.log('consoleList.height', consoleList.height());
// 	console.log('consoleList.innerHeight', consoleList.innerHeight());
// 	console.log('consoleList.outerHeight', consoleList.outerHeight());
// 	console.log("source", source);
	source.addEventListener('message', function(e) {
// 		console.log(e.data);
		consoleList.append('<li class="list-group-item">'+e.data+'</li>');
		if( attachedScroll ) {
			consoleList.scrollTop(consoleList[0].scrollHeight);
		}
	}, false);
	source.addEventListener('ping', function(e) {
		// Connection was opened.
		var now = Date.now();
// 		console.log("Ping - result", e);
		consolePing.text((now-e.data)+"ms");
// 		console.log("Ping - ping", (e.data-now)*1000);
	}, false);

	source.addEventListener('open', function(e) {
		// Connection was opened.
// 		console.log("Open");
		consoleIcon.css("color", consoleIcon.data("online"));
	}, false);

	source.addEventListener('error', function(e) {
		console.log("Error", e);
		if (e.readyState == EventSource.CLOSED) {
			// Connection was closed.
			consoleIcon.css("color", consoleIcon.data("offline"));
			consolePing.text("");
		}
	}, false);

	consoleList.scroll(function(e) {
// 		console.log("scroll", e);
// 		console.log("consoleList.scrollHeight", consoleList[0].scrollHeight);
		var scrollDelta = consoleList[0].scrollHeight-consoleList.height()-consoleList.scrollTop();
// 		console.log("consoleList.scrollDelta => "+scrollDelta);
		attachedScroll = scrollDelta < scrollMax;
// 		console.log('consoleList.height', consoleList.height());
// 		console.log('consoleList.innerHeight', consoleList.innerHeight());
// 		console.log('consoleList.outerHeight', consoleList.outerHeight());
	});

	var consoleInput = $("#ConsoleInput");
	var consoleSendBtn = $("#ConsoleSendButton");
	consoleInput.pressEnter(function() {
		consoleSendBtn.click();
	});
	consoleSendBtn.click(function() {
		var command = consoleInput.val();
		if( command.length < 2 ) {
			return;
		}
		consoleInput.prop("disabled", true);
		console.log("Send command => "+command);
	});
// 	source.onmessage = function (e) {
// 		console.log("Message", e);
// // 		var message = JSON.parse(e.data);
// 		// handle message
// 	};
});
</script>
<style>
.consolestream {
	height: 300px;
	overflow-y: scroll;
}
.consolestream .list-group-item {
	padding: 4px 8px;
}
</style>

</div>
<div class="row">
	<div class="col-lg-6">
		<form method="POST">
		<?php HTMLRendering::useLayout('panel-default'); ?>
		
			<p class="help-block">
			Gérez vos projets, enregistrez les ici !
			</p>
			<div class="form-group">
				<label for="NewMinecraftServerName">Nom</label>
				<input<?php _inputValue('server/name'); ?> name="server[name]" type="text" class="form-control" id="MinecraftServerName" required>
			</div>
		
		<?php HTMLRendering::endCurrentLayout(array('title'=>'Modifier les paramètres du serveur', 'footer'=>'
<div class="panel-footer text-right">
	<button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
	<button name="submitUpdate" type="submit" class="btn btn-primary" data-submittext="Enregistrement...">'.t('save').'</button>
</div>')); ?>
		</form>
	</div>
</div>
	<?php
// }

