<?php
include __DIR__.'/conf.php';
include __DIR__.'/core.php';
function html_serv_item($raw){
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
	foreach(['load','mem','swap','disk'] as $k){
		if($k=='disk'){
			foreach($raw['device'] as $v){
				if($v['type']=='disk' && $v['mount']=='/'){
					break;
				}
			}
		}
		else{
			$v = $raw['device'][$k];
		}
		$n = '';
		if($k=='disk'){
			if($v['mount']=='/'){
				$n = 'root';
			}
			else{
				$n = $v['mount'];
			}
		}
		else{
			$n = $k;
		}
		$r.= '<div class="ratebar '.$k.'" title="'.html_serv_title($k,$v).'"><span class="name"><a href="./serv.php?sid='.$raw['sid'].'&uid='.$v['uid'].'">'.$n.'</a></span><span class="value width">'.html_serv_rate($k,$raw['cpucore'],$v).'</span></div>'.PHP_EOL;
	}
	return $r;
}
function html_serv_list($raw){
	$r = '';
	$s = [];
	$r.= '<div class="servlist">'.PHP_EOL;
	foreach($raw as $v){
		$r.= '<div class="inner">'.PHP_EOL;
		$r.= '<div class="servitem" id="'.$v['sid'].'">'.PHP_EOL;
		$r.= html_serv_item($v);
		$r.= '</div>'.PHP_EOL;
		$r.= '</div>'.PHP_EOL;
		$s[] = $v['sid'];
	}
	$r.= '</div>'.PHP_EOL;
	$r.= '<script>var servlist='.json_encode($s).';var loadinit=function(){timeload();};</script>'.PHP_EOL;
	return $r;
}
function misc_install($chk){
	$s = [
		'gateway'=>($_SERVER['HTTPS']?'https://':'http://').$_SERVER['HTTP_HOST'].'/cron.php',
		'script'=>($_SERVER['HTTPS']?'https://':'http://').$_SERVER['HTTP_HOST'].str_replace('/?action=install','/?action=download',$_SERVER['REQUEST_URI']),
		'client'=>'/root/monitor'
	];
	if($chk){
		$d = file_get_contents(__DIR__.'/misc/monitor');
		$d = str_replace('{SERVER_URI}',$s['gateway'],$d);
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename='.rawurlencode(basename($s['client'])));
		header('Content-length: '.((string) strlen($d)));
		header('Cache-Control: no-cache, must-revalidate');
		header('Pragma: no-cache');
		flush();
		ob_start();
		echo $d;
		exit();
	}
	else{
		$r = '';
		$r.= '<div class="servlist">'.PHP_EOL;
		$r.= '<div class="inner full">'.PHP_EOL;
		$r.= '<div class="servitem setup">';
		$r.= '## requirements start ##'.PHP_EOL;
		$r.= '# working on freebsd #'.PHP_EOL;
		$r.= '# pkg install curl php74 php74-curl #'.PHP_EOL;
		$r.= '# net access to '.$s['gateway'].' #'.PHP_EOL;
		$r.= '## requirements end ##'.PHP_EOL;
		$r.= '<span class="shell">';
		$r.= 'curl -o \''.$s['client'].'\' \''.$s['script'].'\''.PHP_EOL;
		$r.= 'chmod 0755 \''.$s['client'].'\''.PHP_EOL;
		$r.= 'echo \'* * * * * root '.$s['client'].' >/dev/null 2>&1\' >> /etc/crontab'.PHP_EOL;
		$r.= $s['client'];
		$r.= '</span>';
		$r.= '</div>'.PHP_EOL;
		$r.= '</div>'.PHP_EOL;
		$r.= '</div>'.PHP_EOL;
	}
	return $r;
}
$auth = auth_init();
if(!empty($_GET['action']) && $_GET['action']=='download'){
	misc_install(true);
}
elseif(!empty($_GET['action']) && $_GET['action']=='install'){
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
			'main'=>misc_install(false),
			'foot'=>html_footer()
		];
	}
}
else{
	if(!$auth){
		$html = [
			'head'=>html_header(),
			'main'=>auth_html(),
			'foot'=>html_footer()
		];
	}
	else{
		$serv = serv_list(true);
		serv_check();
		$html = [
			'head'=>html_header(),
			'main'=>html_serv_list($serv),
			'foot'=>html_footer()
		];
	}
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
load("./img/embed.js");
</script>
</body>
</html>