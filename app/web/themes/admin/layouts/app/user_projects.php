<?php
/* @var $this HTMLRendering */

HTMLRendering::useLayout('page_skeleton');

// $projectGifts = array();
// foreach( $gifts as $gift ) {
// 	if( !isset($projectGifts[$gift->project_id]) ) {
// 		$projectGifts[$gift->project_id]	= array();
// 	}
// 	$projectGifts[$gift->project_id][] = $gift;
// }
?>
<form method="POST">

<div class="row">
	<div class="col-lg-12">
<!-- 		<h2>Bordered Table</h2> -->
		<button type="button" class="btn btn-primary mb10" data-toggle="modal" data-target="#addProjectDialog">
			Nouveau projet
		</button>
		<div class="table-responsive">
			<table class="table table-bordered table-hover tablesorter">
				<thead>
					<tr>
						<th class="hidden-xs hidden-sm"><?php _t('idColumn'); ?> <i class="fa fa-sort" title="Trier par ID"></i></th>
<!-- 						<th>Image</th> -->
						<th>Nom <i class="fa fa-sort" title="Trier par Nom"></i></th>
						<th>Date de création <i class="fa fa-sort" title="Trier par Date de création"></i></th>
						<th class="sorter-false"><?php _t('actionsColumn'); ?></th>
					</tr>
				</thead>
				<tbody>
<?php
/* @var $project Project */
/* @var $projectUser ProjectUser */
// foreach( $projects as $project ) {
foreach( $projectusers as $projectUser ) {
	$project	= $projectUser->getProject();
// <tr data-project="'.escapeText(json_encode($project->getEditableObject()), ENT_QUOTES).'"
// 	$projectLink	= u(ROUTE_PROJECT, array('projectID'=>$project->id()));
	$projectLink	= $project->getLink();
	echo '
<tr>
	<td class="hidden-xs hidden-sm">'.$project->id().'</td>
	<td><a href="'.$projectLink.'">'.$project.'</a></td>
	<td>'.dt($project->create_date).'</td>
	<td>
		<div class="btn-group" role="group" aria-label="Actions">
			<a href="'.$projectLink.'" class="btn btn-default"><i class="fa fa-edit"></i></a>
			<a href="'.$project->getHistoryLink().'" class="btn btn-default"><i class="fa fa-calendar"></i></a>
			<button type="button" class="btn btn-default deletebtn"><i class="fa fa-times"></i></button>
		</div>
	</td>
</tr>';
}
?>
				</tbody>
			</table>
		</div>
	</div>
</div>
</form>

<div id="addProjectDialog" class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
		<form method="POST" id="addProjectForm" enctype="multipart/form-data">
		
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Nouveau projet</h4>
			</div>
			<div class="modal-body">
				<p class="help-block">
				Gérez vos projets, enregistrez les ici !
				</p>
				<div class="form-group">
					<label for="inputProjectName">Nom</label>
					<input name="project[name]" type="text" class="form-control" id="inputProjectName" required>
				</div>
<!-- 				<div class="form-group"> -->
<!-- 					<label for="inputProjectPrice">Prix (€)</label> -->
<!-- 					<input name="project[price]" type="text" class="form-control" id="inputProjectPrice" placeholder="Non communiqué"> -->
<!-- 					<p class="help-block"> -->
<!-- 					Si vous définissez un prix à cet article, le donateur aura la possibilité d'indiquer une participation. -->
<!-- 					</p> -->
<!-- 				</div> -->
<!-- 				<div class="form-group"> -->
<!-- 					<label for="inputProjectImage">Image</label> -->
<!-- 					<input name="project_image" type="file" class="form-control" id="inputProjectImage" required> -->
<!-- 				</div> -->
<!-- 				<div class="form-group"> -->
<!-- 					<label for="inputProjectShopUrl">Lien marchand</label> -->
<!-- 					<input name="project[shop_url]" type="text" class="form-control" id="inputProjectShopUrl"> -->
<!-- 					<p class="help-block">Vous pouvez le trouver sur <a href="http://www.amazon.fr/" target="_blank">Amazon.fr</a>.</p> -->
<!-- 				</div> -->
<!-- 				<div class="form-group"> -->
<!-- 					<label for="inputProjectNote">Note</label> -->
<!-- 					<textarea name="project[note]" rows="3" type="text" class="form-control" id="inputProjectNote"></textarea> -->
<!-- 				</div> -->
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
				<button id="submitAddProject" name="submitAddProject" type="submit" class="btn btn-primary" data-submittext="Enregistrement...">Ajouter</button>
			</div>
		</form>
		</div>
	</div>
</div>

<div id="deleteProjectDialog" class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
		<form method="POST">
			<input name="project_id" type="hidden" class="project_id">
			
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title project_name"></h4>
			</div>
			<div class="modal-body">
				<p>Souhaitez-vous réellement supprimer ce cadeau de la liste ? Cette action est irréversible.</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
				<button name="submitDeleteProject" type="submit" class="btn btn-danger" data-submittext="Suppression...">Supprimer</button>
			</div>
		</form>
		</div>
	</div>
</div>

<script>

// var DIALOG_EDITITEM;
var DIALOG_DELETEITEM;

$(function() {
// 	DIALOG_EDITITEM = $("#editProjectDialog").modal({"show": false});
	
// 	$(".editbtn").click(function() {
// // 		var project = {"id":17, "name":"Poussette bébé", "price": 50.00, "picture_link":"http://ecx.images-amazon.com/images/I/516-mk0Im6L._SX425_.jpg"};
// 		var project = $(this).closest("tr").data("project");
// 		DIALOG_EDITITEM.fill("project_", project);
// 		DIALOG_EDITITEM.modal("show");
// 	});
	
	DIALOG_DELETEITEM = $("#deleteProjectDialog").modal({"show": false});
	
	$(".deletebtn").click(function() {
// 		var project = {"id":17, "name":"Poussette bébé", "price": 50.00, "picture_link":"http://ecx.images-amazon.com/images/I/516-mk0Im6L._SX425_.jpg"};
		var project = $(this).closest("tr").data("project");
		DIALOG_DELETEITEM.fill("project_", project);
		DIALOG_DELETEITEM.modal("show");
	});
});
</script>

