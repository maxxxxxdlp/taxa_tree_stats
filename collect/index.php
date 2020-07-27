<?php

//site
//ip
//tree
//options

$date = time();
$data = $_POST['payload'];
$day = floor($date/86400);
$file_name = $day.'.data';

if(!file_exists($file_name))
	file_put_contents($file_name,$data);
else {
	$file = fopen($file_name, 'a');
	fwrite($file, $data);
	fclose($file);
}