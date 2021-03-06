<?php
/* @var HTMLRendering $this */
/* @var HTTPController $Controller */
/* @var HTTPRequest $Request */
/* @var HTTPRoute $Route */
/* @var User $user */
/* @var MinecraftServer $server */

global $formData;

// debug('$ContentTitle', $ContentTitle);
HTMLRendering::useLayout('page_skeleton');

$isInstalled	= $server->isInstalled();
$isOnline		= $isInstalled && $server->isOnline();
$formData		= array('server'=>$server->all);
$software		= $server->getServerSoftware();
$minecraft		= $server->getConnector();

$this->addThemeCSSFile('minecraft_server.css');
$this->addThemeJSFile('minecraft_server.js');

$command = 'help';

// $rcon = new RconBasic('10.0.1.5', 25575, 'YZgtvTSgHhkBZu6mBwVh');
// // $rcon = new Rcon('10.0.1.5', 25575, 'YZgtvTSgHhkBZu6mBwVh');
// $rcon->connect();
// debug('Send command "'.$command.'"');
// debug('=> '.$rcon->command($command));

// $rcon = new Rcon('10.0.1.5', 25575, 'YZgtvTSgHhkBZu6mBwVh');
// $rcon->connect();
// debug('Send command "'.$command.'"');
// $result = $rcon->command($command);
// debug('Result => '.$result);
// debug('Result', $result);
// debug('Analyze => '.stringify(count_chars($result, 1)));

// TODO : Change ssh user allows to enter a password to send certificate
// TODO : Owner can ask to resend certificate by checking a checkbox and entering a password


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
				<button name="submitInstallApplication" type="submit" class="btn btn-success btn-lg" data-submittext="Installation, cela peut prendre un moment...">Installer</button>
			</div>
		
		<?php HTMLRendering::endCurrentLayout(array('title'=>'Installer le serveur')); ?>
		</form>
	</div>
</div>
	<?php
}
?>

<div class="row">
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
				<div class="col-sm-2 text-center">
					<i class="fa fa-4x fa-power-off" style="color: <?php echo $server->isStarting() ? '#337ab7' : ($server->isOnline() ? '#2FCF2E' : '#808080'); ?>;"></i>
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
			
			<?php
			if( $isOnline ) {
				?>
				
			<div class="row">
				<div class="col-md-5 col-md-offset-2 form-group">
					<label class="control-label">Version</label>
					<p class="form-control-static"><?php echo escapeText($minecraft->getVersion()); ?></p>
				</div>
				<div class="col-md-5 form-group">
					<label class="control-label">Carte</label>
					<p class="form-control-static"><?php echo escapeText($minecraft->getMap()); ?></p>
				</div>
			</div>
			<?php
			$process = $minecraft->getProcessInformations();
			?>
			<div class="row">
				<div class="col-md-5 col-md-offset-2 form-group">
					<label class="control-label">% CPU</label>
					<p class="form-control-static process-cpu_pct"><?php echo escapeText($process->cpu_pct); ?></p>
				</div>
				<div class="col-md-5 form-group">
					<label class="control-label">Temps CPU</label>
					<p class="form-control-static process-cpu_time"><?php echo escapeText($process->cpu_time); ?></p>
				</div>
			</div>
			<div class="row">
				<div class="col-md-3 col-md-offset-2 form-group">
					<label class="control-label">% Mémoire</label>
					<p class="form-control-static process-mem_pct"><?php echo escapeText($process->mem_pct); ?></p>
				</div>
				<div class="col-md-3 form-group">
					<label class="control-label">Mém. utilisée</label>
					<p class="form-control-static process-mem_res"><?php echo escapeText(ceil($process->mem_res/1024).'Mo'); ?></p>
				</div>
				<div class="col-md-3- form-group">
					<label class="control-label">Mém. virtuelle</label>
					<p class="form-control-static process-mem_virt"><?php echo escapeText(ceil($process->mem_virt/1024).'Mo'); ?></p>
				</div>
			</div>
			<?php
			}
			/*
			$serverInfos = $minecraft->getInfos();
			if( $serverInfos ) {
				echo '<div class="form-horizontal">';
				foreach( $serverInfos as $key => $value ) {
					echo '
				<div class="form-group">
					<label class="col-sm-3 control-label">'.$key.'</label>
					<div class="col-sm-9"><p class="form-control-static">'.escapeText(is_array($value) ? '['.implode(', ', $value).']' : $value).'</p></div>
				</div>';
				}
				echo '</div>';
			}
			*/
		HTMLRendering::endCurrentLayout(array('title'=>'État du serveur', 'footer'=>'
<div class="panel-footer text-right"><form method="POST">
	<button name="submitTestServer" type="submit" class="btn btn-default" data-submittext="Test du serveur...">'.MinecraftServer::text('checkServer').'</button>
	<button name="submitTestInstall" type="submit" class="btn btn-default" data-submittext="Test de l\'installation...">'.MinecraftServer::text('checkInstall').'</button>
	<button name="submitTestApplication" type="submit" class="btn btn-default" data-submittext="Test de l\'application...">'.MinecraftServer::text('checkApplication').'</button>'.
// 	($server->isStarted() ? '
	($server->isOnline() ? '
		<button name="submitStopApplication" type="submit" class="btn btn-warning" data-submittext="Arrêt en cours...">'.MinecraftServer::text('stop').'</button>' : '
		<button name="submitStartApplication" type="submit" class="btn btn-primary" data-submittext="Démarrage...">'.MinecraftServer::text('start').'</button>').
'</form></div>'));
		?>
	</div>
	<?php
	if( $server->isStarted() ) {
// 	if( $server->isonline ) {
		?>
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
		<?php HTMLRendering::endCurrentLayout(array('title'=>'Console', 'bodyClass'=>'consolewrapper')); ?>
	</div>
		<?php
	}
	if( $isOnline ) {
		?>
	<div class="col-lg-6">
		<?php HTMLRendering::useLayout('panel-default'); ?>
			<?php
			$serverPlayers = $minecraft->listPlayers();
			if( $serverPlayers ) {
				echo '
		<ul class="list-group playerslist">';
				foreach( $serverPlayers as $player ) {
					echo '
			<li class="list-group-item">'.escapeText($player).'</li>';
				}
				echo '
		</ul>';
				echo '
		<p class="noplayer" style="display: none;">Aucun joueur connecté.</p>';
			} else {
				echo '
		<ul class="list-group playerslist" style="display: none;"></ul>
		<p class="noplayer">Aucun joueur connecté.</p>';
			}
			unset($serverPlayers);
			?>
		<?php HTMLRendering::endCurrentLayout(array('title'=>'Joueurs connectés (<span class="playercount">'.$minecraft->getNumPlayer().'</span> / '.$minecraft->getMaxPlayer().')')); ?>
	</div>
	<?php
	}
		/*
		<h4>Joueurs connectés (<span class="playercount"><?php echo $minecraft->getNumPlayer().'</span> / '.$minecraft->getMaxPlayer(); ?>)</h4>
	</div>
	
	<?php
	*/
	?>
	
	<div class="col-lg-6">
		<form method="POST">
		<?php HTMLRendering::useLayout('panel-default'); ?>

			<div style="display: none;">
				<input type="text" autocomplete="new-password" />
				<input type="password" autocomplete="new-password" />
			</div>
		
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
					<input<?php _inputValue('server/query_port'); ?> name="server[query_port]" type="text" class="form-control" id="MinecraftServerQueryPort" placeholder="25565" autocomplete="new-password">
				</div>
			</div>
			<div class="row">
				<div class="col-sm-8 form-group">
					<label for="NewMinecraftServerRconPassword">Mot de passe Rcon</label>
					<div class="input-group">
						<input<?php _inputValue('server/rcon_password'); ?> name="server[rcon_password]" type="password" class="form-control showpassword" id="MinecraftServerRconPassword" placeholder="Automatique" autocomplete="new-password">
						<span class="input-group-btn">
							<button class="btn btn-default showbtn" title="Voir le mot de passe" type="button"><i class="fa fa-eye"></i></button>
							<button class="btn btn-default hidebtn" title="Masquer le mot de passe" type="button" style="display: none;"><i class="fa fa-eye-slash"></i></button>
						</span>
					</div>
					<?php /*
					<input<?php _inputValue('server/rcon_password'); ?> name="server[rcon_password]" type="password" class="form-control" id="MinecraftServerRconPassword" placeholder="Automatique">
					*/ ?>
				</div>
				<div class="col-sm-4 form-group">
					<label for="NewMinecraftServerRconPort">Port Rcon</label>
					<input<?php _inputValue('server/rcon_port'); ?> name="server[rcon_port]" type="text" class="form-control" id="MinecraftServerRconPort" placeholder="25575">
				</div>
			</div>
		
		<?php HTMLRendering::endCurrentLayout(array('title'=>'Modifier les paramètres du serveur', 'footer'=>'
<div class="panel-footer text-right">
	<button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
	<button name="submitUpdateServer" type="submit" class="btn btn-primary" data-submittext="Enregistrement...">'.t('save').'</button>
</div>')); ?>
		</form>
	</div>

</div>
<script type="text/javascript">
CONSOLE_STREAM = '<?php echo $server->getConsoleStreamLink(); ?>';
CONSOLE_INPUT = '<?php echo $server->getConsoleInputLink(); ?>';
</script>
