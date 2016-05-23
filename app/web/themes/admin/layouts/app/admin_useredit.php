<?php
/* @var HTMLRendering $this */
/* @var HTTPController $Controller */
/* @var HTTPRequest $Request */
/* @var HTTPRoute $Route */
/* @var User $user */

HTMLRendering::useLayout('page_skeleton');
?>
<form method="POST">

<div style="display: none;">
	<input type="text" autocomplete="new-password" />
	<input type="password" autocomplete="new-password" />
</div>

<div class="row">
	<div class="col-lg-6">
		<?php HTMLRendering::useLayout('panel-default'); ?>
		
			<div class="form-group">
				<label>Nom</label>
				<?php _adm_htmlTextInput('user/fullname'); ?>
			</div>
			<div class="form-group">
				<label>Email</label>
				<?php _adm_htmlTextInput('user/email', '', 'autocomplete="new-password"'); ?>
			</div>
			<div class="form-group">
				<label>Mot de passe</label>
				<?php _adm_htmlPassword('user/password', '', 'autocomplete="new-password" placeholder="Remplir pour modifier"'); ?>
			</div>
			<div class="form-group">
				<label>Fuseau horaire (Timezone)</label>
				<select class="form-control searchable" name="user[timezone]">
				<?php
				_htmlOptions('user/timezone', listTimezones(), DEFAULT_TIMEZONE, OPT_VALUE);
				/*
				<select class="form-control" name="user/timezone" data-value="<?php
				fillInputValue($value, 'user/timezone');
				echo $value;
				?>"></select>
				 */
				?>
				</select>
			</div>
			<?php
			if( $USER_CAN_USER_GRANT ) {
				?>
			<div class="form-group">
				<label>Accréditations</label>
				<select name="user[accesslevel]" class="form-control">
					<?php echo htmlOptions('user/accesslevel', array_filter(User::getUserRoles(), function($value) { return $value >= 0; }), null, OPT_LABEL2VALUE, 'role_', User::getDomain()); ?>
				</select>
			</div>
				<?php
			}
			?>
			<button class="btn btn-primary" type="submit" name="submitUpdate">Enregistrer</button>
			<?php
			if( $USER_CAN_USER_DELETE ) {
				?>
			<button class="btn btn-warning ml20" type="button"
				data-confirm_title="Supprimer <?php echo $user; ?>"
				data-confirm_message="Souhaitez-vous réellement supprimer l'utilisateur « <?php echo $user; ?> » ?"
				data-confirm_submit_name="submitDelete"><?php _t('delete'); ?></button>
			<?php
			}
			?>
		
		<?php HTMLRendering::endCurrentLayout(array('title'=>'Éditer un utilisateur')); ?>
	</div>
</div>

</form>
