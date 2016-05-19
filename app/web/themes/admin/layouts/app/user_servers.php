<?php
/* @var HTMLRendering $this */
/* @var User $USER  */
/* @var MinecraftServer $server */

HTMLRendering::useLayout('page_skeleton');

// $serverGifts = array();
// foreach( $gifts as $gift ) {
// 	if( !isset($serverGifts[$gift->server_id]) ) {
// 		$serverGifts[$gift->server_id]	= array();
// 	}
// 	$serverGifts[$gift->server_id][] = $gift;
// }

// $mcQuery = new MinecraftQuery('10.0.1.5');
// debug('$mcQuery', $mcQuery);
// $mcQuery->collectInformations();
// debug('Infos', $mcQuery->getInfo());
// debug('Players', $mcQuery->listPlayers());

// $rcon = new Rcon('10.0.1.5', 25575, 'dsdsdq');
// $rcon = new Rcon('10.0.1.5', 25575, 'YZgtvTSgHhkBZu6mBwVh');
// $rcon->connect();
// $command = 'help';
// debug('Send command "'.$command.'"');
// debug('=> '.$rcon->command($command));
// $command = '/say Hello connected players';
// debug('Send command "'.$command.'"');
// debug('=> '.$rcon->command($command));
// $command = '/list';
// debug('Send command "'.$command.'"');
// debug('=> '.$rcon->command($command, true));
// $rcon->disconnect();
?>
<form method="POST">

<div class="row">
	<div class="col-lg-12">
		<button type="button" class="btn btn-primary mb10" data-toggle="modal" data-target="#addMinecraftServerDialog">
			Nouveau serveur
		</button>
		<div class="table-responsive">
			<table class="table table-bordered table-hover tablesorter">
				<thead>
					<tr>
						<th class="hidden-xs hidden-sm"><?php _t('idColumn'); ?> <i class="fa fa-sort" title="Trier par ID"></i></th>
						<th>Nom <i class="fa fa-sort" title="Trier par Nom"></i></th>
						<th>Application <i class="fa fa-sort" title="Trier par Application"></i></th>
						<th>Serveur <i class="fa fa-sort" title="Trier par Serveur"></i></th>
						<th>Date de création <i class="fa fa-sort" title="Trier par Date de création"></i></th>
						<th class="sorter-false"><?php _t('actionsColumn'); ?></th>
					</tr>
				</thead>
				<tbody>
<?php
// foreach( $servers as $server ) {
$serverQuery = $USER->listServers();
while( $server = $serverQuery->fetch() ) {
// 	$ssh = $server->getConnectedSSH();
// 	$ssh->exec('pwd', $output, $error);
// 	$command = 'pwd;sfdlkfnlfkn;';
// 	debug('Run command => '.$command);
// 	$ssh->exec($command, $output, $error);
// 	debug('$output', $output);
// 	debug('$error', $error);

// 	debug('Test SSH2 => '.b($server->testSSH()));
// 	debug('Test Rcon => '.b($queryOnline = $server->testRcon()));
// // 	debug('Test MCQuery => '.b($queryOnline = $server->testMCQuery()));
// 	if( $queryOnline ) {
// 		$mcQuery = $server->getMCQuery();
// 		debug('$mcQuery', $mcQuery);
// 		$mcQuery->collectInformations();
// 		debug('Players', $mcQuery->listPlayers());
// 	}
	echo '
<tr>
	<td class="hidden-xs hidden-sm">'.$server->id().'</td>
	<td><i class="fa fa-fw fa-power-off" style="color: '.($server->isonline ? '#2FCF2E' : 'grey').';"></i> <a href="'.$server->getAdminLink().'">'.$server.'</a></td>
	<td>'.$server->getServerSoftware().'</td>
	<td>'.$server->ssh_host.'</td>
	<td>'.dt($server->create_date).'</td>
	<td>
		<div class="btn-group" role="group" aria-label="Actions">
			<a href="'.$server->getAdminLink().'" class="btn btn-default"><i class="fa fa-edit"></i></a>
			<button type="button" class="btn btn-default deletebtn"><i class="fa fa-times"></i></button>
		</div>
	</td>
</tr>';
}
/*
foreach( $serverusers as $serverUser ) {
// foreach( $serverusers as $serverUser ) {
// 	$server	= $serverUser->getMinecraftServer();
// <tr data-server="'.escapeText(json_encode($server->getEditableObject()), ENT_QUOTES).'"
// 	$serverLink	= u(ROUTE_PROJECT, array('serverID'=>$server->id()));
	$serverLink	= $server->getLink();
	echo '
<tr>
	<td class="hidden-xs hidden-sm">'.$server->id().'</td>
	<td><a href="'.$serverLink.'">'.$server.'</a></td>
	<td>'.dt($server->create_date).'</td>
	<td>
		<div class="btn-group" role="group" aria-label="Actions">
			<a href="'.$serverLink.'" class="btn btn-default"><i class="fa fa-edit"></i></a>
			<a href="'.$server->getHistoryLink().'" class="btn btn-default"><i class="fa fa-calendar"></i></a>
			<button type="button" class="btn btn-default deletebtn"><i class="fa fa-times"></i></button>
		</div>
	</td>
</tr>';
}
*/
?>
				</tbody>
			</table>
		</div>
	</div>
</div>
</form>

<div id="addMinecraftServerDialog" class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
		<form method="POST" id="addMinecraftServerForm">
		
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Nouveau projet</h4>
			</div>
			<div class="modal-body">
				<p class="help-block">
				Gérez vos projets, enregistrez les ici !
				</p>
				<div class="form-group">
					<label for="NewMinecraftServerName">Nom</label>
					<input name="server[name]" type="text" class="form-control" id="NewMinecraftServerName" required>
				</div>
				<div class="form-group">
					<label for="NewMinecraftServerSoftware">Application</label>
					<select name="server[software_id]" class="select form-control" id="NewMinecraftServerSoftware" required>
						<?php _htmlOptions('server/software_id', ServerSoftware::listByName()->run(), null, OPT_PERMANENTOBJECT); ?>
					</select>
				</div>
				<p class="help-block">
					L'application doit se connecter à votre serveur, pour préserver la sécurité de votre serveur,
					le mot de passe ne sera pas conservé, un certificat sera laissé sur votre serveur.
				</p>
				<div class="row">
					<div class="col-sm-8 form-group">
						<label for="NewMinecraftServerSSHHost">Serveur SSH</label>
						<input name="server[ssh_host]" type="text" class="form-control" id="NewMinecraftServerSSHHost" required>
					</div>
					<div class="col-sm-4 form-group">
						<label for="NewMinecraftServerSSHPort">Port SSH</label>
						<input name="server[ssh_port]" type="text" class="form-control" id="NewMinecraftServerSSHPort" placeholder="22">
					</div>
				</div>
				<div class="row">
					<div class="col-sm-8 form-group">
						<label for="NewMinecraftServerSSHUser">Utilisateur SSH</label>
						<input name="server[ssh_user]" type="text" class="form-control" id="NewMinecraftServerSSHUser" required>
					</div>
					<div class="col-sm-4 form-group">
						<label for="NewMinecraftServerSSHPassword">Mot de passe SSH</label>
						<input name="server[ssh_password]" type="password" class="form-control" id="NewMinecraftServerSSHPassword">
					</div>
				</div>
				<div class="row">
					<div class="col-sm-8 form-group">
						<label for="NewMinecraftServerPath">Chemin sur le serveur</label>
						<input name="server[path]" type="text" class="form-control" id="NewMinecraftServerPath" placeholder="Chemin vers le dossier où installer l'application">
					</div>
					<div class="col-sm-4 form-group">
						<label for="NewMinecraftServerQueryPort">Port Query</label>
						<input name="server[query_port]" type="text" class="form-control" id="NewMinecraftServerQueryPort" placeholder="25565">
					</div>
				</div>
				<div class="row">
					<div class="col-sm-8 form-group">
						<label for="NewMinecraftServerRconPassword">Mot de passe Rcon</label>
						<input name="server[rcon_password]" type="password" class="form-control" id="NewMinecraftServerRconPassword" placeholder="Automatique">
					</div>
					<div class="col-sm-4 form-group">
						<label for="NewMinecraftServerRconPort">Port Rcon</label>
						<input name="server[rcon_port]" type="text" class="form-control" id="NewMinecraftServerRconPort" placeholder="25575">
					</div>
				</div>
<!-- 				<div class="row"> -->
<!-- 					<div class="col-sm-4 form-group"> -->
<!-- 						<label for="NewMinecraftServerQueryPort">Port Query</label> -->
<!-- 						<input name="server[query_port]" type="text" class="form-control" id="NewMinecraftServerQueryPort" placeholder="25565"> -->
<!-- 					</div> -->
<!-- 				</div> -->
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
				<button id="submitAddMinecraftServer" name="submitCreate" type="submit" class="btn btn-primary" data-submittext="Enregistrement...">Ajouter</button>
			</div>
		</form>
		</div>
	</div>
</div>

<div id="DeleteServerDialog" class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
		<form method="POST">
			<input name="server_id" type="hidden" class="server_id">
			
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title server_name"></h4>
			</div>
			<div class="modal-body">
				<p>Souhaitez-vous réellement supprimer ce cadeau de la liste ? Cette action est irréversible.</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
				<button name="submitDeleteMinecraftServer" type="submit" class="btn btn-danger" data-submittext="Suppression...">Supprimer</button>
			</div>
		</form>
		</div>
	</div>
</div>

<script>

// var DIALOG_EDITITEM;
var DIALOG_DELETEITEM;

$(function() {
// 	DIALOG_EDITITEM = $("#editMinecraftServerDialog").modal({"show": false});
	
// 	$(".editbtn").click(function() {
// // 		var server = {"id":17, "name":"Poussette bébé", "price": 50.00, "picture_link":"http://ecx.images-amazon.com/images/I/516-mk0Im6L._SX425_.jpg"};
// 		var server = $(this).closest("tr").data("server");
// 		DIALOG_EDITITEM.fill("server_", server);
// 		DIALOG_EDITITEM.modal("show");
// 	});
	
	DIALOG_DELETEITEM = $("#DeleteServerDialog").modal({"show": false});
	
	$(".deletebtn").click(function() {
// 		var server = {"id":17, "name":"Poussette bébé", "price": 50.00, "picture_link":"http://ecx.images-amazon.com/images/I/516-mk0Im6L._SX425_.jpg"};
		var server = $(this).closest("tr").data("server");
		DIALOG_DELETEITEM.fill("server_", server);
		DIALOG_DELETEITEM.modal("show");
	});
});
</script>

