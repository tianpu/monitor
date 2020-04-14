<?php
date_default_timezone_set('UTC');
$conf = [
	'time'=>microtime(true),
	'alias'=>[
		'127.0.0.1'=>'server_name'
	],
	'auth'=>[
		'user'=>'admin',
		'pass'=>'admin'
	],
	'mysql'=>[
		'host'=>'localhost',
		'user'=>'{mysqluser}',
		'pass'=>'{mysqlpass}',
		'data'=>'{database}'
	],
	'device'=>[
		'disk'=>['used'=>'Disk Used','avail'=>'Disk Free','iused'=>'Inode Used','ifree'=>'Inode Free'],
		'inet'=>['txin'=>'All In','txinrate'=>'In Rate','txout'=>'All Out','txoutrate'=>'Out Rate','timestamp'=>'Timestamp'],
		'load'=>['1m'=>'1 Minute Aver','5m'=>'5 Minutes Aver','15m'=>'15 Minutes Aver'],
		'mem'=>['active'=>'Active','inact'=>'Inactive','laundry'=>'Laundry','wired'=>'Wired','buf'=>'Buffer','free'=>'Free'],
		'swap'=>['total'=>'Total','used'=>'Used','free'=>'Free'],
		'zarc'=>['total'=>'Total','mru'=>'MRU','mfu'=>'MFU','anon'=>'Anon','header'=>'Header','other'=>'Other','compressed'=>'Compressed','uncompressed'=>'Uncompressed','ratio'=>'Ratio'],
		'kern'=>['pid'=>'Pid','file'=>'File Open','filemax'=>'File Max','sock'=>'Sock Open','sockmax'=>'Sock Max','proc'=>'Proc','procmax'=>'Proc Max','conn'=>'Conn','connmax'=>'Conn Max']
	],
	'series'=>[
		'disk'=>['avail'],
		'inet'=>['txoutrate'],
		'load'=>['1m'],
		'mem'=>['free'],
		'swap'=>['used'],
		'zarc'=>['mfu'],
		'kern'=>['conn']
	],
	'period'=>[
		'1d'=>'5m',
		'3d'=>'15m',
		'7d'=>'30m',
		'1m'=>'2h',
		'3m'=>'6h',
		'6m'=>'12h'
	]
];
function http_301($str){
	header('Location: '.$str,true,301);
	exit();
}
function http_302($str){
	header('Location: '.$str,true,302); 
	exit();
}
function http_404(){
	if(empty($_GET['status'])){
		header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found',true,404);
		exit();
	}
}
function http_503(){
	if(empty($_GET['status'])){
		header($_SERVER['SERVER_PROTOCOL'].' 503 Service Unavailable',true,503);
		exit();
	}
}
function init_hsize($str){
	$r = '';
	$n = ['B','KB','MB','GB','TB','PB'];
	$k = 0;
	while(true){
		if($str>1024){
			$str = $str/1024;
			$k++;
		}
		else{
			break;
		}
	}
	if(!isset($n[$k])){
		$r = 'ERRO';
	}
	elseif(empty($str)){
		$r = 'NULL';
	}
	else{
		$r = init_nums($str,2).$n[$k];
	}
	return $r;
}
function init_hnum($str){
	$r = '';
	$n = ['','K','M','G','T','P'];
	$k = 0;
	while(true){
		if($str>1000){
			$str = $str/1000;
			$k++;
		}
		else{
			break;
		}
	}
	if(!isset($n[$k])){
		$r = 'ERRO';
	}
	elseif(empty($str)){
		$r = 'NULL';
	}
	else{
		$r = init_nums($str,2).$n[$k];
	}
	return $r;
}
function init_sign($str){
	$r = '';
	$r = date('YmdHi',$str);
	return $r;
}
function init_year($str){
	$r = '';
	$r = date('Y-m',$str);
	return $r;
}
function init_time($str){
	$r = '';
	$r = date('Y-m-d',$str);
	return $r;
}
function init_htime($str){
	$r = '';
	$n = [
		'y'=>'%s years ago',
		'm'=>'%s months ago',
		'd'=>'%s days ago',
		'h'=>'%s hours ago',
		'i'=>'%s minutes ago',
		's'=>'%s seconds ago'
	];
	$d = date_diff(date_create('@'.time()),date_create('@'.$str));
	if(($d->y)>0){
		$t = 'y';
		$r = $d->y;
		if(($d->m)>6){
			$r++;
		}
	}
	elseif(($d->m)>0){
		$t = 'm';
		$r = $d->m;
		if(($d->d)>15){
			$r++;
		}
	}
	elseif(($d->d)>0){
		$t = 'd';
		$r = $d->d;
		if(($d->h)>12){
			$r++;
		}
	}
	elseif(($d->h)>0){
		$t = 'h';
		$r = $d->h;
		if(($d->i)>30){
			$r++;
		}
	}
	elseif(($d->i)>0){
		$t = 'i';
		$r = $d->i;
		if(($d->s)>30){
			$r++;
		}
	}
	else{
		$t = 's';
		$r = $d->s;
	}
	$r = '<span title="'.date('c',$str).'">'.sprintf($n[$t],$r).'</span>';
	return $r;
}
function init_nums($str,$pre){
	$r = 0;
	$r = number_format($str,$pre,'.','');
	return $r;
}
function init_show($str){
	if(PHP_SAPI=='cli'){
		echo $str.PHP_EOL;
	}
	else{
		echo $str.'<br>'.PHP_EOL;
	}
}
function init_quote($str){
	$r = '';
	if(!empty($str)){
		$r = preg_quote($str,'/');
	}
	return $r;
}
function init_hash($raw){
	$r = '';
	$r = hash('md5',serialize($raw));
	$r = substr($r,16,8);
	return $r;
}
function init_ucwords($str){
	$r = '';
	$r = mb_convert_case($str,MB_CASE_TITLE,'UTF-8');
	return $r;
}
function init_substr($str,$len){
	$r = '';
	$r = mb_strcut($str,0,$len,'UTF-8');
	return $r;
}
function init_encode($str){
	$r = '';
	$r = htmlspecialchars($str,ENT_QUOTES);
	return $r;
}
function init_decode($str){
	$r = '';
	$r = htmlspecialchars_decode($str,ENT_QUOTES);
	return $r;
}
function init_strcode($str){
	$r = '';
	$r = $str;
	$r = htmlspecialchars_decode($r,ENT_QUOTES);
	$r = htmlspecialchars($r,ENT_QUOTES);
	$r = trim($r);
	return $r;
}
function data_query($raw){
	global $conn;
	$r = true;
	$e = [];
	$r = mysqli_begin_transaction($conn);
	if($r){
		foreach($raw as $sql){
			if(!mysqli_query($conn,$sql)){
				$e[] = mysqli_error($conn);
				$e[] = $sql;
			}
		}
		if(empty($e)){
			if(!mysqli_commit($conn)){
				$e[] = mysqli_error($conn);
			}
		}
		else{
			if(!mysqli_rollback($conn)){
				$e[] = mysqli_error($conn);
			}
		}
	}
	else{
		$e[] = mysqli_error($conn);
	}
	if(!empty($e)){
		init_show('erro: '.json_encode($e));
		$r = false;
	}
	return $r;
}
function data_escape($str){
	global $conn;
	$r = '';
	$r = mysqli_real_escape_string($conn,$str);
	return $r;
}
function data_mysql(){
	global $conf;
	$r = mysqli_connect($conf['mysql']['host'],$conf['mysql']['user'],$conf['mysql']['pass'],$conf['mysql']['data']) or http_503();
	return $r;
}
$conn = data_mysql();
