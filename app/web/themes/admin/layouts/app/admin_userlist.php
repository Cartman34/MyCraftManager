<?php
HTMLRendering::useLayout('page_skeleton');
?>
<form method="POST">

<div class="row">
	<div class="col-lg-12">
		<?php HTMLRendering::useLayout('panel-default'); ?>
		
<?php
if( $USER_CAN_USER_EDIT ) {
	?>
<div class="btn-group mb10" role="group" aria-label="Actions">
	<button type="button" class="btn btn btn-inverse" data-toggle="modal" data-target="#AddUserDialog">
		<i class="fa fa-plus"></i> <?php _t('new'); ?>
	</button>
</div>
<?php
}
?>
<table class="table table-bordered table-hover tablesorter">
	<thead>
		<tr>
			<th><?php _t('idColumn'); ?> <i class="fa fa-sort" title="<?php _t('sortByID'); ?>"></i></th>
			<th><?php User::_text('name'); ?> <i class="fa fa-sort" title="<?php User::_text('sortByName'); ?>"></i></th>
			<th><?php User::_text('email'); ?> <i class="fa fa-sort" title="<?php User::_text('sortByEmail'); ?>"></i></th>
			<th><?php User::_text('role'); ?> <i class="fa fa-sort" title="<?php User::_text('sortByRole'); ?>"></i></th>
			<th class="sorter-false"><?php _t('actionsColumn'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		/* @ar $user User */
		foreach( $users as $user ) {
			echo '
		<tr>
			<td>'.$user->id().'</td>
			<td>'.$user.'</td>
			<td>'.$user->email.'</td>
			<td>'.$user->getRoleText().'</td>
			<td>'.
			( $USER_CAN_USER_EDIT ? '
				<div class="btn-group" role="group" aria-label="Actions">
					<a href="'.$user->getAdminLink().'" class="btn btn-success btn-sm editbtn"><i class="fa fa-edit"></i></a>
				</div>' : '').
			'</td>
		</tr>';
		}
		?>
	</tbody>
</table>
		
		<?php HTMLRendering::endCurrentLayout(); ?>
	</div>
</div>
</form>

<?php
if( $USER_CAN_USER_EDIT ) {
	?>
<div id="AddUserDialog" class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
		<form method="POST">
		
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Nouvel utilisateur</h4>
			</div>
			<div class="modal-body">
				<p class="help-block">
					Plus de pouvoir implique plus de responsabilités. Soyez prudent avant d'ouvrir l'accès à du nouveau personnel.
				</p>
				<div class="form-group">
					<label><?php User::_text('name'); ?></label>
					<input class="form-control" type="text" name="createUser[fullname]" <?php echo htmlValue('fullname'); ?>/>
				</div>
				<div class="form-group">
					<label><?php User::_text('email'); ?></label>
					<input class="form-control" type="text" name="createUser[email]" <?php echo htmlValue('email'); ?> autocomplete="off">
				</div>
				<div class="form-group">
					<label><?php User::_text('password'); ?></label>
					<input class="form-control" type="password" name="createUser[password]" autocomplete="off">
				</div>
				<div class="form-group">
					<label><?php User::_text('confirmPassword'); ?></label>
					<input class="form-control" type="password" name="createUser[password_conf]">
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
<?php
/*
				<h2><?php User::_text('addUser'); ?></h2>
				
<form method="POST">
<div class="row">
	<div class="col-lg-6">
		<div class="adduserform">
		<h2><?php User::_text('addUser'); ?></h2>
		<div class="form-group">
			<label><?php User::_text('name'); ?></label>
			<input class="form-control" type="text" name="createUser[fullname]" <?php echo htmlValue('fullname'); ?>/>
		</div>
		<div class="form-group">
			<label><?php User::_text('email'); ?></label>
			<input class="form-control" type="text" name="createUser[email]" <?php echo htmlValue('email'); ?> autocomplete="off">
		</div>
		<div class="form-group">
			<label><?php User::_text('password'); ?></label>
			<input class="form-control" type="password" name="createUser[password]" autocomplete="off">
		</div>
		<div class="form-group">
			<label><?php User::_text('confirmPassword'); ?></label>
			<input class="form-control" type="password" name="createUser[password_conf]">
		</div>
		<button class="btn btn-primary" type="submit" name="submitCreate"><?php _t('save'); ?></button>
		</div>
	</div>
</div>
</form>
*/
}

