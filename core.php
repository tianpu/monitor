<?php
function serv_check(){
	global $serv;
	$sid = '';
	if(!empty($_GET['sid']) && in_array($_GET['sid'],array_keys($serv))){
		$sid = $_GET['sid'];
	}
	if(!empty($sid) && !empty($_GET['action']) && $_GET['action']=='delete'){
		$sqls = [];
		$sqls[] = 'delete from `dev` where `sid`=\''.data_escape($sid).'\';';
		foreach($serv[$sid]['device'] as $v){
			$sqls[] = 'delete from `'.data_escape($v['type']).'` where `uid`=\''.data_escape($v['uid']).'\';';
		}
		data_query($sqls);
		http_302('./');
	}
}
function serv_item($raw){
	global $conf,$conn;
	$r = [];
	$r = $raw;
	$o = $conf['device'][($r['type'])];
	$sql = 'select * from `'.$r['type'].'` where `uid`=\''.data_escape($r['uid']).'\' and `sign`=\''.data_escape($r['sign']).'\';';
	$rs = mysqli_query($conn,$sql);
	$row = mysqli_fetch_assoc($rs);
	foreach(array_keys($o) as $k){
		$r[$k] = $row[$k];
	}
	return $r;
}
function serv_snap($str,$raw){
	$r = [];
	foreach(array_keys($raw) as $k){
		$p = explode('-',$k,3);
		$v = [
			'uid'=>$raw[$k],
			'sign'=>$str,
			'type'=>$p[0]
		];
		if($p[0]=='disk'){
			$v['dev'] = $p[1];
			$v['mount'] = $p[2];
		}
		elseif($p[0]=='inet'){
			$v['dev'] = $p[1];
			$v['addr'] = $p[2];
		}
		$r[$k] = serv_item($v);
	}
	return $r;
}
function serv_avail($raw){
	global $conf,$conn;
	$r = [];
	$s = [];
	$sql = 'select min(`sign`) as `min`,count(`sign`) as `num` from `load` where `uid`=\''.data_escape($raw['device']['load']).'\';';
	$rs = mysqli_query($conn,$sql);
	while($row=mysqli_fetch_object($rs)){
		$s['min'] = $row->min;
		$s['max'] = init_sign(strtotime(date('Y-m-d H').':'.(date('i')-date('i')%5).':00'));
		$s['num'] = $row->num;
	}
	if(!empty($s['min']) && !empty($s['max']) && !empty($s['num'])){
		$r['mindate'] = substr($s['min'],0,4).'-'.substr($s['min'],4,2).'-'.substr($s['min'],6,2).' '.substr($s['min'],8,2).':'.substr($s['min'],10,2).':00';
		$r['maxdate'] = substr($s['max'],0,4).'-'.substr($s['max'],4,2).'-'.substr($s['max'],6,2).' '.substr($s['max'],8,2).':'.substr($s['max'],10,2).':00';
		$r['count'] = init_nums((strtotime($r['maxdate'])-strtotime($r['mindate'])+300)/300,0);
		$r['ratio'] = init_nums(100*$s['num']/$r['count'],2);
		$r['title'] = $r['mindate'].' - '.$r['maxdate'].'&#010;'.$s['num'].' / '.$r['count'].'='.$r['ratio'].'%';
	}
	return $r;
}
function serv_list($chk){
	global $conf,$conn;
	$r = [];
	$d = [];
	$sql = 'select * from `dev`;';
	$rs = mysqli_query($conn,$sql);
	while($row=mysqli_fetch_object($rs)){
		$v = unserialize($row->last);
		$v['sid'] = $row->sid;
		$v['addr'] = $row->addr;
		$v['conn'] = $row->conn;
		$v['host'] = $row->host;
		$v['avail'] = serv_avail($v);
		if(!empty($conf['alias'][($v['addr'])])){
			$v['name'] = $conf['alias'][($v['addr'])];
		}
		elseif(!empty($conf['alias'][($v['conn'])])){
			$v['name'] = $conf['alias'][($v['conn'])];
		}
		elseif(!empty($v['host'])){
			$v['name'] = $v['host'];
		}
		else{
			$v['name'] = $v['sid'];
		}
		$d[($v['sid'])] = $v;
	}
	if(!empty($d)){
		foreach(array_keys($conf['alias']) as $p){
			foreach(array_keys($d) as $k){
				if($k==init_hash($d[$k]['conn'].'-'.$p)){
					$r[$k] = $d[$k];
					unset($d[$k]);
					break;
				}
			}
		}
		if(!empty($d)){
			foreach(array_keys($d) as $k){
				$r[$k] = $d[$k];
			}
		}
	}
	if($chk){
		foreach(array_keys($r) as $k){
			if(!empty($r[$k]['device'])){
				$r[$k]['device'] = serv_snap($r[$k]['sign'],$r[$k]['device']);
			}
		}
	}
	return $r;
}
function html_header(){
	$r = '';
	$r.= '<div class="site"><a href="./">Monitor</a></div>'.PHP_EOL;
	return $r;
}
function html_footer(){
	$r = '';
	$r.= html_load();
	$r.= '<div class="load">sign '.init_sign(time()).', start <a href="./?action=install">install</a> now.</div>'.PHP_EOL;
	$r.= '<div class="copy">'.date('Y').' &#169; All Rights Reserved</div>'.PHP_EOL;
	return $r;
}
function html_load(){
	global $conf;
	$r = '';
	$n = 0;
	if(!empty($_COOKIE['pageload'])){
		$n = init_nums($_COOKIE['pageload'],0);
	}
	$o = [10,30,60,180,300];
	if(empty($n) || !in_array($n,$o)){
		$n = 60;
	}
	$s = [];
	foreach($o as $k){
		$d = $k-(date('i')*60+date('s'))%$k+3;
		if($k>=60 && $k%60==0){
			$s[] = '<a href="javascript:pageload('.$k.','.$d.');"'.($k==$n?' class="bold"':'').'>'.intval($k/60).'m</a>';
		}
		else{
			$s[] = '<a href="javascript:pageload('.$k.','.$d.');"'.($k==$n?' class="bold"':'').'>'.intval($k/1).'s</a>';
		}
	}
	if(!empty($s)){
		$r.= '<div class="reload">load in '.init_nums((microtime(true)-$conf['time'])*1000,2).'ms, reload '.implode('',$s).'</div>'.PHP_EOL;
	}
	if(!empty($n)){
		$d = $n-(date('i')*60+date('s'))%$n+1;
		$r.= '<script type="application/javascript">window.addEventListener(\'load\',function(){pageload('.$n.','.$d.');});</script>'.PHP_EOL;
	}
	return $r;
}
function html_serv_sys($str,$val){
	$r = '';
	if($str=='os'){
		$o = ['freebsd','openbsd','netbsd','centos','rhel','fedora','ubuntu','debian','windows'];
		foreach($o as $k){
			if(stristr($val,$k)){
				$r = $k;
				break;
			}
		}
		if(empty($r)){
			$r = 'unknown';
		}
	}
	elseif($str=='cpu'){
		if(stristr($val,'intel')){
			if(stristr($val,'@')){
				$r = explode('@',$val,2)[0];
				$r = preg_replace('/\((.*?)\)/i','',$r);
				$r = preg_replace('/(intel xeon|intel core|intel atom|cpu)/i','',$r);
				$r = preg_replace('/[\s]+/i',' ',$r);
				$r = trim($r);
			}
			else{
				$r = 'intel';
			}
		}
		elseif(stristr($val,'amd')){
			$r = 'amd';
		}
		if(empty($r)){
			$r = 'unknown';
		}
	}
	return $r;
}
function html_serv_class($str,$val){
	$r = '';
	if($str=='avail'){
		if($val>99.9){
			$r = 'green';
		}
		elseif($val>99.5){
			$r = 'alert';
		}
		else{
			$r = 'error';
		}
	}
	elseif($str=='check'){
		$val = time()-$val;
		if($val<65){
			$r = 'normal';
		}
		elseif($val<300){
			$r = 'alert';
		}
		else{
			$r = 'error';
		}
	}
	return $r;
}
function html_serv_title($str,$raw){
	global $conf;
	$r = '';
	$s = [];
	if(!empty($conf['device'][$str])){
		if($str=='disk'){
			foreach(['dev','mount'] as $k){
				$s[] = init_ucwords($k).': '.$raw[$k];
			}
		}
		elseif($str=='inet'){
			foreach(['dev','addr'] as $k){
				$s[] = init_ucwords($k).': '.$raw[$k];
			}
		}
		$o = $conf['device'][$str];
		foreach(array_keys($o) as $k){
			if(in_array($str,['mem','swap','disk','inet','zarc'])){
				if(in_array($k,['iused','ifree'])){
					$s[] = $o[$k].': '.init_hnum($raw[$k]);
				}
				elseif($str=='inet'){
					if($k!='timestamp'){
						$s[] = $o[$k].': '.init_hsize($raw[$k]);
					}
				}
				elseif($str=='zarc'){
					if($k!='ratio'){
						$s[] = $o[$k].': '.init_hsize($raw[$k]*1024);
					}
				}
				else{
					$s[] = $o[$k].': '.init_hsize($raw[$k]*1024);
				}
			}
			else{
				$s[] = $o[$k].': '.$raw[$k];
			}
		}
	}
	else{
		foreach(['sid','addr','conn','cpu','os'] as $k){
			$s[] = init_ucwords($k).': '.$raw[$k];
		}
	}
	$r = implode('&#010;',$s);
	return $r;
}
function html_serv_rate($str,$num,$raw){
	$r = '';
	if($str=='load'){
		$s = init_nums(100*$raw['1m']/$num,2);
		if($s>99){
			$r.= '<span class="error" style="width:'.min(100,$s).'%;">'.$s.'%</span>';
		}
		elseif($s>85){
			$r.= '<span class="alert" style="width:'.$s.'%;">'.$s.'%</span>';
		}
		else{
			$r.= '<span class="green" style="width:'.$s.'%;">'.$s.'%</span>';
		}
		$r.= '<span class="tips">'.$raw['1m'].'/'.$num.'</span>';
	}
	elseif($str=='mem'){
		$d = $raw;
		foreach(['uid','sign','type'] as $k){
			unset($d[$k]);
		}
		$s = init_nums(100-100*$d['free']/array_sum($d),2);
		if($s>99 && $d['free']<128*1024){
			$r.= '<span class="error" style="width:'.min(100,$s).'%;">'.$s.'%</span>';
		}
		elseif($s>85 && $d['free']<256*1024){
			$r.= '<span class="alert" style="width:'.$s.'%;">'.$s.'%</span>';
		}
		else{
			$r.= '<span class="green" style="width:'.$s.'%;">'.$s.'%</span>';
		}
		$r.= '<span class="tips">'.init_hsize(1024*(array_sum($d)-$d['free'])).'/'.init_hsize(1024*array_sum($d)).'</span>';
	}
	elseif($str=='swap'){
		$s = init_nums(100*$raw['used']/$raw['total'],2);
		if($s>15 || $raw['used']>256*1024){
			$r.= '<span class="error" style="width:'.min(100,$s).'%;">'.$s.'%</span>';
		}
		elseif($s>3 || $raw['used']>128*1024){
			$r.= '<span class="alert" style="width:'.$s.'%;">'.$s.'%</span>';
		}
		else{
			$r.= '<span class="green" style="width:'.$s.'%;">'.$s.'%</span>';
		}
		$r.= '<span class="tips">'.init_hsize(1024*$raw['used']).'/'.init_hsize(1024*$raw['free']).'</span>';
	}
	elseif($str=='disk'){
		$s = init_nums(100*($raw['used']/($raw['used']+$raw['avail'])),2);
		if($s>95 && $raw['avail']<10*1024*1024){
			$r.= '<span class="error" style="width:100%;">'.$s.'%</span>';
		}
		elseif($s>85 && $raw['avail']<20*1024*1024){
			$r.= '<span class="alert" style="width:'.$s.'%;">'.$s.'%</span>';
		}
		else{
			$r.= '<span class="green" style="width:'.$s.'%;">'.$s.'%</span>';
		}
		$r.= '<span class="tips">'.init_hsize(1024*$raw['used']).'/'.init_hsize(1024*($raw['used']+$raw['avail'])).'</span>';
	}
	elseif($str=='inet'){
		$s = init_nums(100*($raw['txoutrate']/($raw['txoutrate']+$raw['txinrate'])),2);
		$r.= '<span class="green" style="width:'.$s.'%;">'.$s.'%</span>';
		$r.= '<span class="tips">'.init_hsize($raw['txoutrate']).'/'.init_hsize($raw['txoutrate']+$raw['txinrate']).'</span>';
	}
	elseif($str=='zarc'){
		$s = init_nums(100*($raw['mfu']/$raw['total']),2);
		$r.= '<span class="green" style="width:'.$s.'%;">'.$s.'%</span>';
		$r.= '<span class="tips">'.init_hsize(1024*$raw['mfu']).'/'.init_hsize(1024*$raw['total']).'</span>';
	}
	elseif($str=='kern'){
		$s = init_nums(100*($raw['conn']/$raw['connmax']),2);
		$r.= '<span class="green" style="width:'.$s.'%;">'.$s.'%</span>';
		$r.= '<span class="tips">'.$raw['conn'].'/'.$raw['connmax'].'</span>';
	}
	return $r;
}
function auth_init(){
	global $conf;
	$r = false;
	if(!empty($_COOKIE['auth']) && $_COOKIE['auth']==init_hash($conf['auth'])){
		$r = true;
	}
	if(!$r){
		if(!empty($_POST['user']) && !empty($_POST['pass'])){
			$d = [
				'user'=>$_POST['user'],
				'pass'=>$_POST['pass']
			];
			if($d==$conf['auth']){
				setcookie('auth',init_hash($conf['auth']),time()+86400*365);
				header('Refresh: 0');
				exit();
			}
		}
	}
	return $r;
}
function auth_html(){
	$r = '';
	$r.= '<div class="servlist">'.PHP_EOL;
	$r.= '<div class="inner full">'.PHP_EOL;
	$r.= '<div class="servitem signin">'.PHP_EOL;
	$r.= '<form method="post">'.PHP_EOL;
	$r.= '<div class="form"><input class="form-input" type="text" name="user" placeholder="Your name"></div>'.PHP_EOL;
	$r.= '<div class="form"><input class="form-input" type="password" name="pass" placeholder="Your password"></div>'.PHP_EOL;
	$r.= '<div class="form"><input class="form-submit" type="submit" value="submit"></div>'.PHP_EOL;
	$r.= '</form>'.PHP_EOL;
	$r.= '</div>'.PHP_EOL;
	$r.= '</div>'.PHP_EOL;
	$r.= '</div>'.PHP_EOL;
	return $r;
}
