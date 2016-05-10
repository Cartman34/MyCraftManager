<?php
/* @var $this HTMLRendering */

HTMLRendering::useLayout('page_skeleton');

// $serverGifts = array();
// foreach( $gifts as $gift ) {
// 	if( !isset($serverGifts[$gift->server_id]) ) {
// 		$serverGifts[$gift->server_id]	= array();
// 	}
// 	$serverGifts[$gift->server_id][] = $gift;
// }

$query = new MinecraftQuery('10.0.1.5');
$query->collectInformations();
debug('Infos', $query->getInfo());
debug('Players', $query->getPlayers());

// $rcon = new Rcon('10.0.1.5', 25575, 'dsdsdq');
// $rcon = new Rcon('10.0.1.5', 25575, 'YZgtvTSgHhkBZu6mBwVh');
// $rcon->connect();
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
						<th>Date de création <i class="fa fa-sort" title="Trier par Date de création"></i></th>
						<th class="sorter-false"><?php _t('actionsColumn'); ?></th>
					</tr>
				</thead>
				<tbody>
<?php
/* @var $server MinecraftServer */
/* @var $serverUser MinecraftServerUser */
// foreach( $servers as $server ) {
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
		<form method="POST" id="addMinecraftServerForm" enctype="multipart/form-data">
		
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Nouveau projet</h4>
			</div>
			<div class="modal-body">
				<p class="help-block">
				Gérez vos projets, enregistrez les ici !
				</p>
				<div class="form-group">
					<label for="inputMinecraftServerName">Nom</label>
					<input name="server[name]" type="text" class="form-control" id="inputMinecraftServerName" required>
				</div>
<!-- 				<div class="form-group"> -->
<!-- 					<label for="inputMinecraftServerPrice">Prix (€)</label> -->
<!-- 					<input name="server[price]" type="text" class="form-control" id="inputMinecraftServerPrice" placeholder="Non communiqué"> -->
<!-- 					<p class="help-block"> -->
<!-- 					Si vous définissez un prix à cet article, le donateur aura la possibilité d'indiquer une participation. -->
<!-- 					</p> -->
<!-- 				</div> -->
<!-- 				<div class="form-group"> -->
<!-- 					<label for="inputMinecraftServerImage">Image</label> -->
<!-- 					<input name="server_image" type="file" class="form-control" id="inputMinecraftServerImage" required> -->
<!-- 				</div> -->
<!-- 				<div class="form-group"> -->
<!-- 					<label for="inputMinecraftServerShopUrl">Lien marchand</label> -->
<!-- 					<input name="server[shop_url]" type="text" class="form-control" id="inputMinecraftServerShopUrl"> -->
<!-- 					<p class="help-block">Vous pouvez le trouver sur <a href="http://www.amazon.fr/" target="_blank">Amazon.fr</a>.</p> -->
<!-- 				</div> -->
<!-- 				<div class="form-group"> -->
<!-- 					<label for="inputMinecraftServerNote">Note</label> -->
<!-- 					<textarea name="server[note]" rows="3" type="text" class="form-control" id="inputMinecraftServerNote"></textarea> -->
<!-- 				</div> -->
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
				<button id="submitAddMinecraftServer" name="submitAddMinecraftServer" type="submit" class="btn btn-primary" data-submittext="Enregistrement...">Ajouter</button>
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

