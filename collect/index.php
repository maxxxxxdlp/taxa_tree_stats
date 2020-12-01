<?php

require_once('../components/header.php');

//validate incoming data
if(count($_POST)==0 ||

   !array_key_exists('site',$_POST) ||
   !in_array($_POST['site'],array_keys(SITES_DICTIONARY)) ||

   !array_key_exists('ip',$_POST) ||
   !preg_match('/((1?\d{1,2}|2[0-5]{1,2})\.){3}(1?\d{1,2}|2[0-5]{1,2})/',$_POST['ip']) ||

   (
   	    array_key_exists('ranks',$_POST) &&
        (
            !is_array($_POST['ranks']) ||
             count($_POST['ranks'])>100 ||
            !preg_match('/[A-Za-z]{0,20000}/',implode('a',$_POST['ranks']))
	    )
   ) ||

   !array_key_exists('options',$_POST) ||
   !is_array($_POST['options']) ||
    count($_POST['options'])>50 ||
   !preg_match('/[a-z_]{0,300}/',implode('a',array_keys($_POST['options']))) ||

   !array_key_exists('tree',$_POST) ||
   (
   	    !is_string($_POST['tree']) &&
        !is_array($_POST['tree'])
   ) ||
   (     is_string($_POST['tree']) &&
         strlen($_POST['tree'])>300
   ) ||
   (     is_array($_POST['tree']) &&
         count($_POST['tree'])>100
   )
)
	exit('Invalid POST request');

$date = time();
$_POST['date'] = $date;
$data = json_encode($_POST);
$day = floor($date/86400);
$file_name = WORKING_LOCATION.$day.'.data';

file_put_contents($file_name,$data.PHP_EOL,FILE_APPEND | LOCK_EX);