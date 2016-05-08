<?php
/* @var $this HTMLRendering */
/* @var $project Project */
/* @var $projectUser ProjectUser */
/* @var $workingToday ProjectWorkingDay */

HTMLRendering::useLayout('page_skeleton');

global $ModuleTitle;
$ModuleTitle	= $project.'';
$projectHistoryLink	= $project->getHistoryLink();

$this->addJSURL('https://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.4/raphael-min.js');
$this->addJSURL('https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js');
$this->addCSSURL('https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css');

$projectUser->updateWorkDone();

// debug('Reports', getReports('projectToday', null, 0));
?>

<div class="row">

	<div class="col-lg-3">
		<form method="POST">
			<div class="form-group">
				<label for="inputProjectName">Nom</label>
				<input name="project[name]" type="text" class="form-control" id="inputProjectName" required value="<?php echo escapeQuotes($project->name, ESCAPE_DOUBLEQUOTES_TOHTML); ?>">
			</div>
			<button name="submitUpdateProject" type="submit" class="btn btn-primary" data-submittext="Enregistrement...">Enregistrer</button>
		</form>
	</div>
	<?php
	if( $projectUser ) {
	?>
	<div class="col-lg-3">
		<div class="panel panel-primary">
			<div class="panel-heading">
				<div class="row">
					<div class="col-xs-3">
						<i class="fa fa-5x fa-calendar-check-o"></i>
					</div>
					<div class="col-xs-9 text-right">
						<div class="huge"><?php echo $projectUser->getWorkDoneAsHours(); ?></div>
						<div>Heures travaillées sur ce projet !</div>
					</div>
				</div>
			</div>
			<a href="<?php echo $projectHistoryLink; ?>">
				<div class="panel-footer">
					<span class="pull-left">Voir les détails</span>
					<span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
					<div class="clearfix"></div>
				</div>
			</a>
		</div>
	</div>
	<div class="col-lg-4">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title"><i class="fa fa-clock-o fa-fw"></i> 7 derniers jours d'activité</h3>
			</div>
			<div class="panel-body">
				<div class="list-group">
					<?php 
					foreach( $projectUser->listLastWorkingDays(7) as $workingDay ) {
						echo '
					<div class="list-group-item">
						<span class="badge">'.$workingDay->getWorkDone().'</span>
						<i class="fa fa-fw '.($workingDay->work_done > $workingDay->work_planned ? 'fa-check':'fa-calendar').'"></i> '.df('fullDateFormat', $workingDay->work_date).'
					</div>';
					}
					unset($workingDay);
					?>
				</div>
				<div class="text-right">
					<a href="<?php echo $projectHistoryLink; ?>">Voir tout l'historique <i class="fa fa-arrow-circle-right"></i></a>
				</div>
			</div>
		</div>
	</div>
	<?php
	}
	?>
			
</div>


<?php
if( $workingToday ) {
	$userActives	= $user->listActiveProjectUsers();
	$activeProject	= $userActives ? $userActives[0]->getProject() : null;
	$otherActive	= $activeProject && $activeProject->id() != $projectUser->project_id;
	
	if( $otherActive && !$activeProjectExceptionThrown ) {
		startReportStream('projectToday');
		reportWarning(t('anotherProjectIsActive', ProjectUser::getDomain(), array('project_name'=>$activeProject.'', 'project_link'=>$activeProject->getLink())));
		endReportStream();
	}
// 	debug('$workingToday', $workingToday);
	?>
<div class="row" id="workingToday">
	
	<div class="col-lg-3">
		
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title"><i class="fa fa-clock-o fa-fw"></i> Mon projet aujourd'hui - <?php echo d($workingToday->work_date); ?></h3>
			</div>
			<div class="panel-body">
				<?php
		// 		displayReportsHTML('projectToday');
				$this->display('reports-bootstrap3', array('reportStream'=>'projectToday'));
				?>
				<form method="POST" action="#workingToday">
				<?php
				if( $projectUser->isWorking() ) {
					?>
					<div class="form-group">
						<label class="control-label">Dernière actualisation</label>
						<p class="form-control-static"><?php
						echo dt($workingToday->lastsubmit_date);
		// 				echo '/'.$workingToday->lastsubmit_date.'/'.dt($workingToday->lastsubmit_date).'/';
						?></p>
					</div>
					<button name="submitUpdateWorkingToday" type="submit" class="btn btn-primary btn-lg" data-submittext="Pointage..." title="Metre à jour immédiatement le temps de travail">Pointer</button>
					<button name="submitEndWorkingToday" type="submit" class="btn btn-warning btn-lg" data-submittext="Fin..." title="Termine la session de travail en cours">Fin du travail</button>
					<?php
				} else if( !$otherActive ) {
					?>
					<button name="submitStartWorkingToday" type="submit" class="btn btn-success btn-lg" data-submittext="Pointage..." title="Démarre une nouvelle session de travail pour cette journée">Commencer le travail</button>
					<?php
				}
				?>
				</form>
				<br />
				<form method="POST">
					<div class="form-group">
						<label for="inputWorkingTodayAddDone">Ajouter du temps</label>
						<div class="input-group">
							<input name="add_min" type="number" class="form-control" id="inputWorkingTodayAddDone" required>
							<span class="input-group-addon">minutes</span>
						</div>
					</div>
					<button name="submitAddWorkingToday" type="submit" class="btn btn-default" data-submittext="Enregistrement...">Ajouter</button>
				</form>
			</div>
		</div>
	</div>
	<div class="col-lg-3">
		<div class="panel <?php echo $workingToday->getWorkDoneAsHours() ? 'panel-primary' : 'panel-default'; ?>">
			<div class="panel-heading">
				<div class="row">
					<div class="col-xs-3">
						<i class="fa fa-5x <?php echo $workingToday->work_done >= $workingToday->work_planned ? 'fa-calendar-check-o' : 'fa-calendar-times-o'; ?>"></i>
					</div>
					<div class="col-xs-9 text-right">
						<div class="huge"><?php echo $workingToday->work_done ? df('timeFormat', $workingToday->work_done, false) : 0; ?></div>
						<div>Heures travaillées aujourd'hui !</div>
					</div>
				</div>
			</div>
			<?php /*
			<a href="#">
				<div class="panel-footer">
					<span class="pull-left">Voir les détails</span>
					<span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
					<div class="clearfix"></div>
				</div>
			</a>
			*/ ?>
		</div>
		<?php
		if( $projectUser->isWorking() ) {
// 			debug('Current time zone is '.date_default_timezone_get());
// 			$sqlDate = '2016-02-13 22:26:35';
// 			$submitTimeUTC	= dateToTime($sqlDate);
// 			$TIME	= 1455405360;
// 			debug('SQL Date : '.$sqlDate.', dateToTime()=>'.$submitTimeUTC.'('.dt($submitTimeUTC).'), strtotime()=>'.strtotime($sqlDate).'('.dt(strtotime($sqlDate)).')');
// 			debug('$TIME : '.$TIME.' ('.dt($TIME).')');
// 			debug('Seconds passed ? '.$submitTimeUTC.' => '.($TIME-$submitTimeUTC).' => '.df('timeFormat', $TIME-$submitTimeUTC, false));
// 			debug('Seconds passed ? '.$submitTimeUTC.' => '.($TIME-$submitTimeUTC).' => '.df('timeFormat', $TIME-$submitTimeUTC, false));
// 			debug('Seconds passed ? '.$submitTimeUTC.' => '.(TIME-$submitTimeUTC).' => '.df('timeFormat', TIME-$submitTimeUTC, false));
			
			$submitTimeUTC	= dateToTime($workingToday->lastsubmit_date);
// 			debug('Seconds passed ? '.$submitTimeUTC.' => '.(TIME-$submitTimeUTC).' => '.df('timeFormat', TIME-$submitTimeUTC, false));
			?>
		<div class="panel panel-success">
			<div class="panel-heading">
				<div class="row">
					<div class="col-xs-3">
						<i class="fa fa-5x fa-clock-o"></i>
					</div>
					<div class="col-xs-9 text-right">
						<div class="huge time-refresh" data-time="<?php echo $submitTimeUTC; ?>"><?php echo df('timeFormat', TIME-$submitTimeUTC, false); ?></div>
						<div>Depuis la dernière actualisation</div>
					</div>
				</div>
			</div>
		</div>
<script type="text/javascript">
$(function() {
	$(".time-refresh").each(function() {
		var _	= $(this);
		var start	= _.data("time")*1000;
		setInterval(function() {
			var diff = new Date((new Date()).getTime()-start);
			var newClock	= leadZero(diff.getUTCHours())+":"+leadZero(diff.getUTCMinutes());
			if( newClock != _.html() ) {
				_.html(newClock);
				_.addClass("blink_me");
				setTimeout(function() {
					_.removeClass("blink_me");
				}, 10000);
			}
		}, 9999);
	});
});
</script>
<style>
.blink_me {
    -webkit-animation-name: blinker;
    -webkit-animation-duration: 1s;
    -webkit-animation-timing-function: ease;
    -webkit-animation-iteration-count: 6;
    
    -moz-animation-name: blinker;
    -moz-animation-duration: 1s;
    -moz-animation-timing-function: ease;
    -moz-animation-iteration-count: 6;
    
    animation-name: blinker;
    animation-duration: 1s;
    animation-timing-function: ease;
    animation-iteration-count: 6;
}

@-moz-keyframes blinker {  
    0% { opacity: 1.0; }
    50% { opacity: 0.0; }
    100% { opacity: 1.0; }
}

@-webkit-keyframes blinker {  
    0% { opacity: 1.0; }
    50% { opacity: 0.0; }
    100% { opacity: 1.0; }
}

@keyframes blinker {  
    0% { opacity: 1.0; }
    50% { opacity: 0.0; }
    100% { opacity: 1.0; }
}
</style>
		<?php
		}
		?>
	</div>
	<?php
	if( $workingToday->work_planned ) {
		?>
	<div class="col-lg-3">
		<div class="panel panel-primary">
			<div class="panel-heading">
				<div class="row">
					<div class="col-xs-3">
						<i class="fa fa-5x fa-calendar"></i>
					</div>
					<div class="col-xs-9 text-right">
						<div class="huge"><?php echo $workingToday->getWorkPlannedAsHours(); ?></div>
						<div>Heures planifiées pour aujourd'hui !</div>
					</div>
				</div>
			</div>
			<?php /*
			<a href="#">
				<div class="panel-footer">
					<span class="pull-left">Voir les détails</span>
					<span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
					<div class="clearfix"></div>
				</div>
			</a>
			*/ ?>
		</div>
	</div>
	<?php
	}
	?>
			
</div>
<?php
}
?>


<div class="panel panel-default">
	<div class="panel-heading">
		<i class="fa fa-bar-chart-o fa-fw"></i> Historique
	</div>
	<div class="panel-body">
		<div id="project-history-chart"></div>
	</div>
</div>
<script type="text/javascript">
$(function() {
	Morris.Area({
		element: 'project-history-chart',
		data: [
			<?php
			foreach( $projectUser->listWorkingDays() as $i => $workingDay ) {
				echo ($i ? ',' : '').'
			{
				period: "'.df('%Y-%m-%d', $workingDay->work_date).'",
				work_done: '.$workingDay->getWorkDoneAsHours().',
			}';
// 				work_done: '.$workingDay->work_done.',
// 				work_done: '.$workingDay->getWorkDoneAsHours().',
			}
			?>
		],
		xkey: 'period',
		xLabels: 'day',
		ykeys: ['work_done'],
		ymax: "auto "+8,
		goals:	[5],
// 		ymax: "auto "+(7*3600),
// 		yLabelFormat: function (x) {
// 			console.log("yLabelFormat("+x+")");
// 			return parseInt(x/3600)+"";
// 		},
// 		goals:	[5*3600],
		labels: ['Heures effectuées'],
		pointSize: 2,
		hideHover: 'auto',
		resize: true,
		smooth: false,
		dateFormat: function (x) {
			var date = new Date(x);
			return leadZero(date.getDate()) + "/" + leadZero(date.getMonth() + 1) + "/" + date.getFullYear();
		}
	});
	
});
</script>

