<?php

require_once('components/header.php');
head('<link
			rel="stylesheet"
			href="'.LINK.'static/css/index'.CSS.'">
	  <script src="'.LINK.'static/js/index'.JS.'"></script>');
require_once('components/charts.php');

$date = time();
$today = intval(floor($date / 86400));

if(!array_key_exists('days', $_GET) || !is_numeric(['days']) || $_GET['days'] < 1)
	$days_to_show = 30;
else
	$days_to_show = $_GET['days'];

function get_day_from_file_path($file_path){

	$day = explode('/',$file_path);
	$day = array_pop($day);
	$day = explode('.',$day);
	$day = $day[0];
	$day = intval($day);

	return $day;

}

$data = [];
$sites = [];
$sites_stats = [];
$days_files = glob(WORKING_LOCATION.'*.data');

$days = [];
foreach($days_files as $file_path)
	$days[] = get_day_from_file_path($file_path);
natsort($days);


for($i = $days_to_show; $i >= 0; $i--){

	$day = $today - $i;

	if(!in_array($day,$days))
		continue;


	$file_name = $day . '.data';
	$file_path = WORKING_LOCATION . $file_name;

	$file_data = file_get_contents($file_path);
	$file_data = trim($file_data);
	$file_data = explode("\n", $file_data);

	foreach($file_data as $data){

		$data = json_decode($data, TRUE);
		$site = $data['site'];
		unset($data['site']);

		$ip_is_excluded = FALSE;
		foreach(IPS_TO_EXCLUDE as $ip_to_exclude)
			if(strpos($data['ip'], $ip_to_exclude)){
				$ip_is_excluded = TRUE;
				break;
			}

		if($ip_is_excluded)
			continue;

		if(!array_key_exists($site, $sites)){
			$sites[$site] = [];
			$sites_stats[$site] = [];
		}

		if(!array_key_exists($day, $sites_stats[$site]))
			$sites_stats[$site][$day] = 0;

		$sites[$site][] = $data;
		$sites_stats[$site][$day]++;

	}

}

$final_sites_stats = [];
foreach($sites_stats as $site_name => $site_stats){
	$final_sites_stats[$site_name] = [[], []];

	foreach($site_stats as $day => $count){
		$final_sites_stats[$site_name][0][] = date(DATE_FORMATTER, $day * 86400);
		$final_sites_stats[$site_name][1][] = $count;
	}
}

$sites_stats = $final_sites_stats;


function unix_time_to_human_time($time){

	$time_passed = time() - $time;

	if($time_passed < 60)
		$result = $time_passed . ' seconds ago';

	elseif($time_passed < 3600)
		$result = intval($time_passed / 60) . ' minutes ago';

	elseif($time_passed < 86400)
		$result = intval($time_passed / 3600) . ' hours ago';

	else
		$result = intval($time_passed / 86400) . ' days ago';

	return preg_replace('/^(1 \w+)s( ago)/', '$1$2', $result);

}

function unix_day_to_human_day($day){

	static $unix_day_now = FALSE;
	if($unix_day_now===FALSE)
		$unix_day_now = floor(time()/86400);

	$days_passed = $unix_day_now - $day;

	if($days_passed == 0)
		return 'today';
	elseif($days_passed == 1)
		return 'yesterday';
	else
		return $days_passed.' days ago';

}


function show_nodes(
	$nodes,
	$node_name = '',
	$level_to_capitalize = FALSE
){

	if($level_to_capitalize===0)
		$node_name = ucfirst($node_name);


	if($level_to_capitalize===0)
		$level_to_capitalize = FALSE;
	else if($level_to_capitalize!==FALSE)
		$level_to_capitalize--;

	$result = $node_name;

	if($nodes == 'true' || $nodes == [])
		return $result;

	$result .= '<ul>';

	foreach($nodes as $node_name => $node_data)
		$result .= '<li>' . show_nodes($node_data, $node_name, $level_to_capitalize) . '</li>';

	$result .= '</ul>';

	return $result;

}

function extract_tree_from_file_name($file_name){

	$file_name = str_replace('archive-','',$file_name);
	$file_name = preg_replace('/-bl\d.zip/','',$file_name);
	$file_name = explode('-',$file_name);
	$tree_html = [];
	$last_node = NULL;
	$rank_name = FALSE;

	foreach($file_name as $level){

		if($rank_name===FALSE){
			$rank_name = $level;
			continue;
		}

		$tree_html[] = ucfirst($rank_name).': '.$level;

		$rank_name = FALSE;

	}

	$tree_html = implode('<br>',$tree_html);

	return $tree_html;

}

if($days===[])
	$days = [0];

$days_count = count($days);
$oldest_record = unix_day_to_human_day($days[0]);
$newest_record = unix_day_to_human_day($days[$days_count-1]); ?>

<div class="alert alert-info">
	The oldest record is from <?=$oldest_record?><br>
	The most recent record is from <?=$newest_record?><br>
	There are records from <?=$days_count?> days
</div>

<label class="mb-5">
	Days to show:
	<input
			list="days"
			id="show_last_days"
			class="form-control"
			value="<?= $days_to_show ?>">
	<datalist id="days">
		<option value="15"></option>
		<option value="30"></option>
		<option value="45"></option>
		<option value="60"></option>
	</datalist>
</label><br>

<script>
	const link = '<?=LINK?>';
</script> <?php


if(count($sites)==0)
	die('No data found');

$chart_id = 0;
foreach($sites as $site_name => $site_data){ ?>

	<div class="mb-5">
		<h2 tabindex="0"><?= SITES_DICTIONARY[$site_name] ?> <span class="badge badge-light"><?=count($site_data)?></span></h2>
		<div
				class="content"
				style="display:none">
			<div class="chart">
				<canvas
						id="chart_<?= $chart_id ?>"
						width="1000"
						height="300"></canvas>
				<script>
					create_chart( $( '#chart_<?=$chart_id?>' ), '<?=$site_name?>', JSON.parse( '<?=json_encode($sites_stats[$site_name][0])?>' ), JSON.parse( '<?=json_encode($sites_stats[$site_name][1])?>' ) );
				</script>
			</div>
			<table class="table table-striped">
				<thead>
					<tr>
						<th>IP</th>
						<th>Date</th>
						<th>Ranks</th>
						<th>Options</th>
						<th>Tree</th>
					</tr>
				</thead>
				<tbody> <?php

					foreach($site_data as $record){

						$ip = $record['ip'];

						$date = $record['date'];
						$date = unix_time_to_human_time($date);

						if(array_key_exists('ranks',$record))
							$ranks = implode('<br>',$record['ranks']);
						else
							$ranks = '';

						$options = $record['options'];
						$options_translated = [];

						foreach($options as $option => $value)
							if(array_key_exists($option, OPTIONS_DICTIONARY))
								$options_translated[] = '<span class="option_'.$value.'">'.OPTIONS_DICTIONARY[$option].'</span>';

						$options_html = implode('<br>', $options_translated);

						$tree = $record['tree'];

						if(is_string($tree))
							$tree_html = extract_tree_from_file_name($tree);
						else
							$tree_html = show_nodes($tree,'', 1); ?>

						<tr>
							<td><a
										href="<?= LINK ?>ip_info/?ip=<?= $ip ?>"
										target="_blank"><?= $ip ?></a></td>
							<td><?= $date ?></td>
							<td><?= $ranks ?></td>
							<td><?= $options_html ?></td>
							<td><?= $tree_html ?></td>
						</tr> <?php

					} ?>

				</tbody>
			</table>
		</div>
	</div> <?php

	$chart_id++;

}