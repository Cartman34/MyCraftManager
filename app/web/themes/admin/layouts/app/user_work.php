<?php
/* @var HTMLRendering $this */
/* @var Project $project */
/* @var ProjectUser $projectUser */
/* @var ProjectWorkingDay $workingToday */
/* @var ProjectWorkingDay $workingDay */
/* @var User $user */

HTMLRendering::useLayout('page_skeleton');

// global $ContentTitle;
// $ContentTitle	= $project.'';

$this->addJSURL('https://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.4/raphael-min.js');
$this->addJSURL('https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js');
$this->addCSSURL('https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css');

$projectUsers	= $user->listProjectUsers();
$projectsName = $projectsKey = $projectsWorkingDays = array();
foreach( $projectUsers as $projectUser ) {
	$project	= $projectUser->getProject();
	$projectKey	= 'project-'.$project->id();
	$projectsName[]	= $project.'';
	$projectsKey[]	= $projectKey;
	foreach( $projectUser->listWorkingDays() as $workingDay ) {
		if( !isset($projectsWorkingDays[$workingDay->work_date]) ) {
			$projectsWorkingDays[$workingDay->work_date]	= array();
		}
		$projectsWorkingDays[$workingDay->work_date][$projectKey]	= $workingDay->getWorkDoneAsHours();
	}
}

?>

<div class="row">
	<div class="col-lg-4">
		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="fa fa-bar-chart-o fa-fw"></i> Par projet
			</div>
			<div class="panel-body">
				<div id="projects-worksdone-donut"></div>
			</div>
		</div>
<script type="text/javascript">
$(function() {
	Morris.Donut({
		element: 'projects-worksdone-donut',
		data: [
			<?php
			$c	= 0;
			foreach( $projectUsers as $projectUser ) {
				echo ($c ? ',' : '').'
			{label: "'.escapeQuotes($projectUser->getProject()->name).'", value: '.$projectUser->getWorkDoneAsHours().'}';
				$c++;
			}
			?>
		]
	});
	
});
</script>
	</div>
	
	<div class="col-lg-8">
	
		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="fa fa-bar-chart-o fa-fw"></i> Par mois
			</div>
			<div class="panel-body">
				<div id="projects-monthly-chart"></div>
			</div>
		</div>
<script type="text/javascript">
$(function() {
	Morris.Bar({
		element: 'projects-monthly-chart',
		data: [
			{ y: '2006', a: 100, b: 90 },
			{ y: '2007', a: 75,	b: 65 },
			{ y: '2008', a: 50,	b: 40 },
			{ y: '2009', a: 75,	b: 65 },
			{ y: '2010', a: 50,	b: 40 },
			{ y: '2011', a: 75,	b: 65 },
			{ y: '2012', a: 100, b: 90 }
		],
		xkey: 'y',
		ykeys: ['a', 'b'],
		labels: ['Series A', 'Series B']
	});
	
});
</script>
	</div>
</div>

<div class="panel panel-default">
	<div class="panel-heading">
		<i class="fa fa-bar-chart-o fa-fw"></i> Par jour
	</div>
	<div class="panel-body">
		<div id="allprojects-history-chart"></div>
	</div>
</div>
<script type="text/javascript">
$(function() {
	Morris.Area({
		element: 'allprojects-history-chart',
		data: [
			<?php
			$c	= 0;
			foreach( $projectsWorkingDays as $workingPeriod => $periodProjects ) {
				echo ($c ? ',' : '').'
			{
				"period": "'.df('%Y-%m-%d', $workingPeriod).'",';
				foreach( $periodProjects as $projectKey => $projectWorkDone ) {
					echo '
				"'.$projectKey.'": '.$projectWorkDone.',';
				}
				echo '
			}';
				$c++;
			}
			?>
		],
		xkey: 'period',
		xLabels: 'day',
		ykeys: ["<?php echo implode('","', $projectsKey); ?>"],
		labels: ["<?php echo implode('","', $projectsName); ?>"],
		ymax: "auto "+8,
		goals:	[5],
		pointSize: 2,
		hideHover: 'auto',
		resize: true,
		dateFormat: function (x) {
			var date = new Date(x);
			return leadZero(date.getDate()) + "/" + leadZero(date.getMonth() + 1) + "/" + date.getFullYear();
		}
	});
	
});
</script>

