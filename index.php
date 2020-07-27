<?php

$date = time();
$today = floor($date/86400);

if(!array_key_exists('days',$_GET) || !is_numeric(['days']) || $_GET['days'] < 1)
	$days_to_show = 30;
else
	$days_to_show = $_GET['days'];

$data = [];
$sites = [];
$sites_stats = [];


for($i=0;$i<$days_to_show;$i++){

	$day = $today-$days_to_show+$i;
	$file_name = $day.'.data';
	$file_data = file_get_contents($files_destination.$file_name);
	$file_data = explode("\n",$file_data);

	foreach($file_data as $data){

		$data = json_decode($data,TRUE);
		$site = $data['site'];
		unset($data['site']);

		if(!array_key_exists($site,$sites)){
			$sites[$site] = [];
			$sites_stats[$site] = [];
		}

		if(!array_key_exists($day,$sites[$site]))
			$sites_stats[$site][$day] = 0;



		$sites[$site][] = $data;
		$sites_stats[$site][$day]++;

	}

}

$final_sites_stats = [];
foreach($sites_stats as $site_name => $site_stats){
	$final_sites_stats[$site_name] = [[],[]];

	foreach($site_stats as $day => $count){
		$final_sites_stats[$site_name][0][] = date(DATE_FORMATTER,$day*86400);
		$final_sites_stats[$site_name][1][] = $count;
	}
}


function unix_time_to_human_time($time){

	$time_passed = time()-$time;

	if($time_passed<60)
		$result = $time_passed.' seconds ago';

	elseif($time_passed<3600)
		$result = intval($time_passed/60).' minutes ago';

	elseif($time_passed<86400)
		$result = intval($time_passed/3600).' hours ago';

	else
		$result = intval($time_passed/86400).' days ago';

	return preg_replace('/^(1 \w+)s( ago)/','$1$2',$result);

}


function show_nodes($nodes,$node_name=''){

	$result = $node_name;

	if($nodes == 'true' || $nodes == [])
		return $result;

	$result .= '<ul>';

	foreach($nodes as $node_name => $node_data)
		$result .= '<li>'.show_nodes($node_data,$node_name).'</li>';

	$result .= '</ul>';

	return $result;

}


$chart_id = 0;
foreach($sites as $site_name => $site_data){

	$body = '';

	foreach($site_data as $record){

		$ip = $record['ip'];

		$date = $record['date'];
		$date = unix_time_to_human_time($date);

		$options = $record['options'];
		$options_translated = [];

		foreach($options as $option)
			if(array_key_exists($option,OPTIONS_DICTIONARY))
				$options_translated[] = OPTIONS_DICTIONARY[$option];

		$options_html = implode('<br>',$options_translated);

		$tree = $record['tree'];
		$tree_html = show_nodes($tree); ?>

		<tr>
			<td><a href="<?=LINK?>ip_info/?ip=<?=$ip?>" target="_blank"><?=$ip?></a></td>
			<td><?=$date?></td>
			<td><?=$options_html?></td>
			<td><?=$tree?></td>
		</tr> <?php

	} ?>

	<div>
		<h2><?=$site_name?></h2>
		<div class="content">
			<div class="chart">
				<canvas id="chart_<?=$chart_id?>" width="1000" height="300"></canvas>
				<script>
					create_chart($('#chart_<?=$chart_id?>'),'<?=$site_name?>',JSON.parse('<?=json_encode($sites_stats[$site_name][0])?>'),JSON.parse('<?=json_encode($sites_stats[$site_name][1])?>'));
				</script>
			</div>
			<table class="table table-striped">
				<thead>
					<tr>
						<th>Date</th>
						<th>IP</th>
						<th>Tree</th>
						<th>Options</th>
					</tr>
				</thead>
				<tbody>
					<?=$body?>
				</tbody>
			</table>
		</div>
	</div> <?

	$chart_id++;

}