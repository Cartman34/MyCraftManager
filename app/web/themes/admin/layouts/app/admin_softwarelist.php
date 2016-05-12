<?php
/* @var HTMLRendering $this */
/* @var HTTPController $Controller */
/* @var HTTPRequest $Request */
/* @var HTTPRoute $Route */
/* @var User $user */

HTMLRendering::useLayout('page_skeleton');
?>
<form method="POST">

<div class="row">
	<div class="col-lg-12">
		<?php HTMLRendering::useLayout('panel-default'); ?>
	
<div class="btn-group mb10" role="group" aria-label="Actions">
	<button type="button" class="btn btn btn-inverse" data-toggle="modal" data-target="#AddSoftwareDialog">
		<i class="fa fa-plus"></i> <?php _t('new'); ?>
	</button>
</div>

<table class="table table-bordered table-hover tablesorter">
	<thead>
		<tr>
			<th><?php _t('idColumn'); ?> <i class="fa fa-sort" title="<?php _t('sortByID'); ?>"></i></th>
			<th><?php ServerSoftware::_text('image'); ?></th>
			<th><?php ServerSoftware::_text('name'); ?> <i class="fa fa-sort" title="<?php ServerSoftware::_text('sortByName'); ?>"></i></th>
			<th><?php ServerSoftware::_text('version'); ?> <i class="fa fa-sort" title="<?php ServerSoftware::_text('sortByEmail'); ?>"></i></th>
			<th><?php ServerSoftware::_text('status'); ?> <i class="fa fa-sort" title="<?php ServerSoftware::_text('sortByStatus'); ?>"></i></th>
			<th class="sorter-false"><?php _t('actionsColumn'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		$softwareQuery = ServerSoftware::get()->orderby("id DESC");
		/* @var $software Software */
		while( $software = $softwareQuery->fetch() ) {
			$image	= $software->getImage();
			echo '
		<tr>
			<td>'.$software->id().'</td>
			<td>'.($image ? '<img src="'.$image->getLink().'" class="picture"/>' : '').'</td>
			<td>'.$software.'</td>
			<td>'.$software->version.'</td>
			<td>'.($software->published ? 'Publiée' : 'Masquée').'</td>
			<td>
				<div class="btn-group btn-group-sm" role="group" aria-label="Actions">
					<a href="'.$software->file_url.'" class="btn btn-primary" target="_blank"><i class="fa fa-download"></i></a>
					<button type="button" class="btn btn-default editbtn"><i class="fa fa-edit"></i></button>
				</div>
			</td>
		</tr>';
		}
		unset($image);
		?>
	</tbody>
</table>
		
		<?php HTMLRendering::endCurrentLayout(); ?>
	</div>
</div>
</form>

<div id="AddSoftwareDialog" class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
		<form method="POST" enctype="multipart/form-data">
		
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Nouvelle application serveur</h4>
			</div>
			<div class="modal-body">
				<p class="help-block">
					Publiez une nouvelle application serveur pour l'autoriser comme serveur à déployer.
				</p>
				<div class="form-group">
					<label for="NewSoftwareURL">Lien de téléchargement</label>
					<div class="input-group">
						<input name="software[file_url]" type="url" class="form-control" id="NewSoftwareURL" placeholder="URL de téléchargement">
						<a class="input-group-addon btn btn-default" title="Télécharger l'application" target="_blank"><i class="fa fa-external-link"></i></a>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-9">
						<div class="form-group">
							<label><?php ServerSoftware::_text('name'); ?></label>
							<input class="form-control" type="text" name="software[name]" <?php echo htmlValue('name'); ?>/>
						</div>
						<div class="form-group">
							<label><?php ServerSoftware::_text('version'); ?></label>
							<input class="form-control" type="text" name="software[version]" <?php echo htmlValue('version'); ?>>
						</div>
					</div>
					<div class="col-lg-3">
						<div class="overlay_wrapper">
							<img class="preview software_image img-thumbnail" id="NewSoftwarePreview" src="<?php echo STATIC_URL; ?>images/photoNotAvailable.png">
							<input class="overlay" type="file" name="software_image" data-preview="#NewSoftwarePreview" title="Cliquez pour changer la photo">
						</div>
					</div>
				</div>
				<div class="form-group">
					<label><?php ServerSoftware::_text('install_path'); ?></label>
					<input class="form-control" type="text" name="software[install_path]" <?php echo htmlValue('install_path'); ?> value="FTBInstall.sh">
				</div>
				<div class="form-group">
					<label><?php ServerSoftware::_text('start_path'); ?></label>
					<input class="form-control" type="text" name="software[start_path]" <?php echo htmlValue('start_path'); ?> value="ServerStart.sh">
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
				<button name="submitCreate" type="submit" class="btn btn-primary" data-submittext="Enregistrement...">Ajouter</button>
			</div>
		</form>
		</div>
	</div>
</div>

