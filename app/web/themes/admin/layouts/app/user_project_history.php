<?php
/* @var $this HTMLRendering */
/* @var Project $project */
/* @var ProjectUser $projectUser */
/* @var ProjectWorkingDay $workingToday */
/* @var ProjectWorkingDay[] $projectWorkingDays */
/* @var ProjectWorkingDay $workingDay */

HTMLRendering::useLayout('page_skeleton');

global $ModuleTitle;
$ModuleTitle	= $project.'';

// debug('Reports', getReports('projectToday', null, 0));
?>

<!-- <form method="POST"> -->
<div class="row">
	<div class="col-lg-12">
<!-- 		<h2>Bordered Table</h2> -->
		<a href="<?php echo $project->getLink(); ?>" class="btn btn-default"><i class="fa fa-chevron-left"></i> Retour au projet</a>
		<div class="table-responsive">
			<table class="table table-bordered table-hover tablesorter">
				<thead>
					<tr>
					<?php /*
						<th><?php _t('idColumn'); ?> <i class="fa fa-sort" title="<?php _t('sortByID'); ?>"></i></th>
					*/ ?>
						<th><?php ProjectWorkingDay::_text('date'); ?> <i class="fa fa-sort" title="<?php ProjectWorkingDay::_text('sortByDate'); ?>"></i></th>
						<th><?php ProjectWorkingDay::_text('work_done'); ?> <i class="fa fa-sort" title="<?php ProjectWorkingDay::_text('sortByWorkDone'); ?>"></i></th>
						<th><?php ProjectWorkingDay::_text('work_planned'); ?> <i class="fa fa-sort" title="<?php ProjectWorkingDay::_text('sortByWorkPlanned'); ?>"></i></th>
						<th class="sorter-false"><?php ProjectWorkingDay::_text('addtime'); ?></th>
						<th class="sorter-false"><?php _t('actionsColumn'); ?></th>
					</tr>
				</thead>
				<tbody>
<?php
$previous = null;
foreach( $projectWorkingDays as $workingDay ) {
	if( !$workingDay->work_done && !$workingDay->work_planned ) {
		continue;
	}
	if( $previous ) {
		$diff	= $workingDay->getDiffInDays($previous);
// 		var_dump($diff);
// 		debug('$diff', $diff);
		if( $diff > 1 ) {
			$diffText	= '-';
			if( $diff == 2 ) {
				$diffText	= d($workingDay->getTimestamp()+86400);
			} else
			if( $diff < 7 ) {
				$diffText	= 'diff_someDays';
			} else
			if( $diff < 13 ) {
				$diffText	= 'diff_oneWeek';
			} else
			if( $diff < 28 ) {
				$diffText	= 'diff_someWeeks';
			} else
			if( $diff < 60 ) {
				$diffText	= 'diff_oneMonth';
			} else
			if( $diff < 180 ) {
				$diffText	= 'diff_someMonths';
// 			} else {
// 				$diffText	= '-';
			}
// 			debug('$diffText => '.$diffText);
			echo '
<tr>
	<td>'.ProjectWorkingDay::text($diffText).'</td>
	<td> - </td>
	<td> - </td>
	<td> - </td>
</tr>';
		}
	}
	echo '
<tr'.($workingDay->work_done >= $workingDay->work_planned ? ' class="success"' : '').'>
	<td>'.$workingDay->getDate().'</td>
	<td>'.$workingDay->getWorkDone().'</td>
	<td>'.$workingDay->getWorkPlanned().'</td>
	<td>
		<form method="POST" class="form-inline">
			<div class="form-group">
				<label class="sr-only">'.ProjectWorkingDay::text('addtime').'</label>
   				<input name="add_min" type="number" class="form-control w100" />
  			</div>
			<button type="submit" name="submitAddDayWorkDone['.$workingDay->id().']" class="btn btn-default"><i class="fa fa-save"></i></button>
		</form>
	</td>
</tr>';
	$previous	= $workingDay;
}
// 	<td>'.
// 	( $USER_CAN_USER_EDIT ? '
// 		<div class="btn-group" role="group" aria-label="Actions">
// 			<a href="'.$user->getAdminLink().'" class="btn btn-default editbtn"><i class="fa fa-edit"></i></a>
// 		</div>' : '').
// 	'</td>
?>
				</tbody>
			</table>
		</div>
	</div>
</div>
<!-- </form> -->

