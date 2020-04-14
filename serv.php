<?php
include __DIR__.'/conf.php';
include __DIR__.'/core.php';
function html_serv_data($raw){
	global $sid,$uid;
	$r = '';
	$r.= '<div class="status" title="'.$raw['avail']['title'].'"><span class="avail text '.html_serv_class('avail',$raw['avail']['ratio']).'">'.$raw['avail']['ratio'].'% availability</span><span class="check text '.html_serv_class('check',$raw['check']).'" title="'.date('c',$raw['check']).'" data-diff="'.(time()-$raw['check']).'" id="'.$raw['sid'].'-diff">'.(time()-$raw['check']).'s ago</span></div>'.PHP_EOL;
	$r.= '<div class="device" title="'.html_serv_title('device',$raw).'"><span class="host"><a href="./serv.php?sid='.$raw['sid'].'">'.$raw['name'].'</a></span><span class="misc os">'.html_serv_sys('os',$raw['os']).'</span><span class="misc cpu">'.html_serv_sys('cpu',$raw['cpu']).'</span></div>'.PHP_EOL;
	$k = 'inet';
	foreach($raw['device'] as $v){
		if($v['type']=='inet' && $v['addr']!='127.0.0.1'){
			break;
		}
	}
	$r.= '<div class="network" title="'.html_serv_title($k,$v).'"><span class="uptime">'.($raw['uptime']).'</span><span class="txout">'.init_hsize($v['txoutrate']).'/s</span><span class="txin">'.init_hsize($v['txinrate']).'/s</span>'.($raw['avail']['ratio']<99.99?'<span class="delete"><a href="./?action=delete&sid='.$raw['sid'].'">DEL</a></span>':'').'</div>'.PHP_EOL;
	foreach(['load','inet','mem','zarc','swap','kern','disk'] as $k){
		foreach($raw['device'] as $v){
			if($v['type']==$k){
				$n = '';
				if($k=='disk'){
					if($v['mount']=='/'){
						$n = 'root';
					}
					else{
						$n = $v['mount'];
					}
				}
				elseif($k=='inet'){
					$n = $v['dev'];
				}
				else{
					$n = $k;
				}
				$r.= '<div class="ratebar '.$k.'" title="'.html_serv_title($k,$v).'"><span class="name"><a href="./serv.php?sid='.$sid.'&uid='.$v['uid'].'"'.($uid==$v['uid']?' class="bold"':'').'>'.$n.'</a></span><span class="value width">'.html_serv_rate($k,$raw['cpucore'],$v).'</span></div>'.PHP_EOL;
			}
		}
	}
	return $r;
}
function html_serv_tips($raw){
	$r = '';
	if(in_array($raw[0],['load'])){
		$r = init_nums($raw[2],2);
	}
	elseif(in_array($raw[0],['mem','swap'])){
		$r = init_nums($raw[2]/(1024*1024),2).' GB';
	}
	elseif(in_array($raw[0],['inet'])){
		if(in_array($raw[1],['txin','txout'])){
			$r = init_nums($raw[2]/(1024*1024*1024),2).' GB';
		}
		else{
			$r = init_nums($raw[2]/(1024),2).' KB/s';
		}
	}
	elseif(in_array($raw[0],['zarc'])){
		if(in_array($raw[1],['ratio'])){
			$r = init_nums($raw[2]/(100),2);
		}
		else{
			$r = init_nums($raw[2]/(1024*1024),2).' GB';
		}
	}
	elseif(in_array($raw[0],['disk'])){
		if(in_array($raw[1],['iused'])){
			$r = init_nums($raw[2]/(10000),2).' E4';
		}
		elseif(in_array($raw[1],['ifree'])){
			$r = init_nums($raw[2]/(1000000),2).' E6';
		}
		else{
			$r = init_nums($raw[2]/(1024*1024),2).' GB';
		}
	}
	else{
		$r = $raw[2];
	}
	return $r;
}
function html_serv_view(){
	global $conn,$conf,$uid,$tid,$dev;
	$r = [];
	$s = [
		'curr'=>0,
		'step'=>0,
		'unit'=>'',
		'item'=>[]
	];
	if(substr($tid,-1)=='d'){
		$s['curr'] = strtotime(date('Y-m-d H:').(date('i')-date('i')%(substr($conf['period'][$tid],0,-1))).':00');
		$s['step'] = substr($conf['period'][$tid],0,-1);
		$s['unit'] = 'minutes';
	}
	elseif(substr($tid,-1)=='m'){
		$s['curr'] = strtotime(date('Y-m-d ').(date('H')-date('H')%(substr($conf['period'][$tid],0,-1))).':00:00');
		$s['step'] = substr($conf['period'][$tid],0,-1);
		$s['unit'] = 'hours';
	}
	foreach(range(-300,1) as $k){
		$s['item'][$k] = [
			date_modify(date_create('@'.$s['curr']),($k>0?'+':'-').(abs($k-1)*$s['step']).' '.$s['unit'])->format('U'),
			date_modify(date_create('@'.$s['curr']),($k>0?'+':'-').(abs($k-0)*$s['step']).' '.$s['unit'])->format('U')
		];
	}
	$o = $conf['device'][($dev['type'])];
	if($dev['type']=='inet'){
		unset($o['timestamp']);
	}
	$e = [];
	$e[] = 'count(`sign`) as `count`';
	foreach(array_keys($o) as $k){
		$e[] = 'sum(`'.$k.'`) as `'.$k.'`';
	}
	foreach($s['item'] as $d){
		$n = (int) ($d[0].'000');
		$sql = 'select '.implode(',',$e).' from `'.data_escape($dev['type']).'` where `uid`=\''.data_escape($uid).'\' and `sign`>='.init_sign($d[0]).' and `sign`<'.init_sign($d[1]).';';
		$rs = mysqli_query($conn,$sql);
		$row = mysqli_fetch_assoc($rs);
		foreach(array_keys($o) as $k){
			if($row['count']>0){
				$row[$k] = $row[$k]/$row['count'];
			}
			else{
				$row[$k] = 0;
			}
			$r[$k][] = ['x'=>$n,'y'=>empty($row[$k])?null:((float) $row[$k]),'z'=>html_serv_tips([$dev['type'],$k,$row[$k]])];
		}
	}
	return $r;
}
function html_serv_stat(){
	global $conf,$dev;
	$r = [];
	$r['conf'] = [
		'rendto'=>'chart',
		'device'=>$dev['type'],
		'default'=>[],
		'series'=>$conf['device'][($dev['type'])]
	];
	foreach($conf['series'][($dev['type'])] as $k){
		$r['conf']['default'][$k] = true;
	}
	if($dev['type']=='inet'){
		unset($r['conf']['series']['timestamp']);
	}
	$r['data'] = html_serv_view();
	return $r;
}
function html_serv_time(){
	global $conf,$tid;
	$r ='';
	$v = $conf['period'];
	foreach(array_keys($v) as $k){
		if($k==$tid){
			$r.= '<span class="interval"><a href="javascript:interval(\''.$k.'\');" class="bold">'.($k).'<span class="tips">'.$v[$k].'</span></a></span>';
		}
		else{
			$r.= '<span class="interval"><a href="javascript:interval(\''.$k.'\');">'.($k).'<span class="tips">'.$v[$k].'</span></a></span>';
		}
	}
	return $r;
}
function html_serv_info($raw){
	global $uid;
	$r = '';
	$s = [];
	$s['serv'] = [$raw['sid']];
	$r.= '<div class="servlist">'.PHP_EOL;
	$r.= '<div class="inner full">'.PHP_EOL;
	$r.= '<div class="servitem" id="'.$raw['sid'].'">'.PHP_EOL;
	$r.= html_serv_data($raw);
	if(!empty($uid)){
		$r.= '<div class="servstat">'.PHP_EOL;
		$r.= '<div class="ratebar">'.html_serv_time().'</div>';
		$r.= '<div class="ratebar" id="chart"></div>'.PHP_EOL;
		$r.= '</div>'.PHP_EOL;
		$s['stat'] = html_serv_stat();
	}
	$r.= '</div>'.PHP_EOL;
	$r.= '</div>'.PHP_EOL;
	$r.= '</div>'.PHP_EOL;
	$r.= '<script>var servlist='.json_encode($s['serv']).';var statconf='.json_encode($s['stat']['conf']).';var statdata='.json_encode($s['stat']['data']).';var loadinit=function(){timeload();statload();};</script>'.PHP_EOL;
	return $r;
}
$dev = [];
$sid = $uid = $tid = '';
$serv = serv_list(true);
if(!empty($_GET['sid']) && in_array($_GET['sid'],array_keys($serv))){
	$sid = $_GET['sid'];
}
if(empty($sid)){
	http_302('./');
}
if(!empty($_GET['uid'])){
	foreach($serv[$sid]['device'] as $v){
		if($v['uid']==$_GET['uid']){
			$uid = $v['uid'];
			$dev = $v;
			break;
		}
	}
	if(empty($uid)){
		http_302('./serv.php?sid='.$sid);
	}
}
$tid = '7d';
if(!empty($_COOKIE['interval']) && in_array($_COOKIE['interval'],array_keys($conf['period']))){
	$tid = $_COOKIE['interval'];
}
$auth = auth_init();
if(!$auth){
	$html = [
		'head'=>html_header(),
		'main'=>auth_html(),
		'foot'=>html_footer()
	];
}
else{
	$html = [
		'head'=>html_header(),
		'main'=>html_serv_info($serv[$sid]),
		'foot'=>html_footer()
	];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Monitor</title>
<meta name="robots" content="all">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<link rel="shortcut icon" href="./img/favicon.ico">
<link rel="apple-touch-icon" href="./img/favicon.png">
<link rel="stylesheet" href="./img/style.css">
</head>
<body>
<div class="header">
<?php echo $html['head']; ?>
</div>
<div class="wrapper">
<?php echo $html['main']; ?>
</div>
<div class="footer">
<?php echo $html['foot']; ?>
</div>
<script type="application/javascript">
var load=function(s){var d=document.createElement("script");d.async=true;d.src=s;document.head.appendChild(d);};
<?php if(!empty($uid)) echo 'load("./img/chart.js");'.PHP_EOL; ?>
load("./img/embed.js");
</script>
</body>
</html>