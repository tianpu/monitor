<?php
include __DIR__.'/conf.php';
include __DIR__.'/core.php';
function cron_rate($str,$raw){
	global $conn;
	$r = [];
	$r = $raw['device'][$str]['data'];
	$sql = 'select * from `inet` where `uid`=\''.data_escape($raw['dev']['device'][$str]).'\' order by `sign` desc limit 1;';
	$rs = mysqli_query($conn,$sql);
	while($row=mysqli_fetch_object($rs)){
		if(!empty($row->txin)){
			$r['txinrate'] = intval((($r['txin']-($row->txin))/($raw['dev']['check']-($row->timestamp))));
		}
		if(!empty($row->txout)){
			$r['txoutrate'] = intval((($r['txout']-($row->txout))/($raw['dev']['check']-($row->timestamp))));
		}
	}
	if($r['txinrate']<0 && $r['txoutrate']<0){
		init_show('maybe os restart, reset txrate');
		$r['txinrate'] = 0;
		$r['txoutrate'] = 0;
	}
	return $r;
}
$meta = $data = $sqls = [];
if(!empty($_POST['data'])){
	$data = unserialize($_POST['data']);
}
if(empty($data)){
	init_show('null data~');
	exit();
}
$sign = strtotime(date('Y-m-d H').':'.(date('i')-date('i')%5).':00');
$meta['sid'] = '';
$meta['addr'] = '';
$meta['conn'] = $_SERVER['REMOTE_ADDR'];
$meta['host'] = $data['kern']['hostname'];
$meta['dev'] = [
	'os'=>$data['os'],
	'cpu'=>$data['kern']['cpu'],
	'cpucore'=>$data['kern']['cpucore'],
	'cpufreq'=>$data['kern']['cpufreq'],
	'sign'=>init_sign($sign),
	'time'=>$data['resc']['time'],
	'uptime'=>$data['resc']['uptime'],
	'check'=>time(),
	'device'=>[]
];
$meta['device'] = [];
$p = 'inet';
$v = $data[$p];
foreach(array_keys($v) as $k){
	if(substr($k,0,2)!='lo' && $v[$k]['addr']!='127.0.0.1' && empty($meta['addr'])){
		$meta['addr'] = $v[$k]['addr'];
	}
	$v[$k]['timestamp'] = $meta['dev']['check'];
	$meta['device'][($p.'-'.$k.'-'.$v[$k]['addr'])] = [
		'uid'=>'',
		'type'=>$p,
		'data'=>$v[$k]
	];
}
$p = 'disk';
$v = $data[$p];
foreach(array_keys($v) as $k){
	$meta['device'][($p.'-'.$k.'-'.$v[$k]['mount'])] = [
		'uid'=>'',
		'type'=>$p,
		'data'=>$v[$k]
	];
}
foreach(['load','mem','zarc','swap'] as $p){
	if(!empty($data['resc'][$p])){
		$meta['device'][$p] = [
			'uid'=>'',
			'type'=>$p,
			'data'=>$data['resc'][$p]
		];
		if($p=='zarc' && !empty($data['resc'][$p]['compressed'])){
			$meta['device'][$p]['data']['ratio'] = intval(100*($data['resc'][$p]['uncompressed']/$data['resc'][$p]['compressed']));
		}
	}
}
$p = 'kern';
$v = $data[$p];
if(!empty($v)){
	$v['pid'] = $data['resc']['pid'];
	foreach(['cpu','cpucore','cpufreq','hostname'] as $k){
		unset($v[$k]);
	}
	$meta['device'][$p] = [
		'uid'=>'',
		'type'=>$p,
		'data'=>$v
	];
}
ksort($meta['device']);
$meta['sid'] = init_hash($meta['conn'].'-'.$meta['addr']);
foreach(array_keys($meta['device']) as $k){
	$meta['dev']['device'][$k] = init_hash($meta['sid'].'-'.$k);
	$meta['device'][$k]['uid'] = $meta['dev']['device'][$k];
}
foreach(array_keys($meta['device']) as $k){
	if($meta['device'][$k]['type']=='inet'){
		$meta['device'][$k]['data'] = cron_rate($k,$meta);
	}
}
$sqls[] = 'delete from `dev` where `sid`=\''.data_escape($meta['sid']).'\';';
$sqls[] = 'insert into `dev` (`sid`,`addr`,`conn`,`host`,`last`,`sign`) values (\''.data_escape($meta['sid']).'\',\''.data_escape($meta['addr']).'\',\''.data_escape($meta['conn']).'\',\''.data_escape($meta['host']).'\',\''.data_escape(serialize($meta['dev'])).'\',\''.data_escape($meta['dev']['sign']).'\');';
foreach($meta['device'] as $v){
	$d = ['uid'=>$v['uid'],'sign'=>$meta['dev']['sign']];
	foreach(array_keys($conf['device'][($v['type'])]) as $k){
		$d[$k] = (empty($v['data'][$k])?'0':data_escape($v['data'][$k]));
	}
	$sqls[] = 'delete from `'.$v['type'].'` where `uid`=\''.data_escape($d['uid']).'\' and `sign`=\''.data_escape($d['sign']).'\';';
	$sqls[] = 'insert into `'.$v['type'].'` (`'.implode('`,`',array_keys($d)).'`) values (\''.implode('\',\'',array_values($d)).'\');';
}
if(data_query($sqls)){
	init_show('sync okay');
}
else{
	init_show('sync fail');
}
