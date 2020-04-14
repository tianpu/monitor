var statrend=function(set,raw){
	var createview=function(mod){
		var view = {
			chart:{type:'spline',marginLeft:0,marginTop:0},
			legend:{enabled:true,align:'center',layout:'horizontal',verticalAlign:'bottom',itemMarginTop:5,itemStyle:{fontSize:'13px',fontWeight:'normal'}},
			credits:{enabled:false},
			rangeSelector:{enabled:false},
			navigator:{enabled: false},
			scrollbar:{enabled: false},
			plotOptions:{series:{lineWidth:1.2,marker:{enabled:false,states:{hover:{enabled:false}}},
				events:{legendItemClick:function(){this.chart.series.forEach(function(s){if(s!==this && s.visible){s.hide();}});return !this.visible?true:false}}
			}},
			xAxis:{type:'datetime',labels:{format:'{value:%Y-%m-%d}',step:2,align:'center'}},
			tooltip:{formatter:function(){return Highcharts.dateFormat('%Y-%m-%d %H:%M:%S',this.x)+'<br>'+this.series.name+': '+this.point.z;},split:false,share:true},
			series:ser
		};
		if(mod=='load'){
			view.yAxis = [
				{title:{text:null},labels:{formatter:function(){return Highcharts.numberFormat(this.value,2,'.','');}}},
				{title:{text:null},labels:{formatter:function(){return Highcharts.numberFormat(this.value,2,'.','');}}},
				{title:{text:null},labels:{formatter:function(){return Highcharts.numberFormat(this.value,2,'.','');}}}
			];
		}
		else if(mod=='mem' || mod=='swap'){
			view.yAxis = [
				{title:{text:null},labels:{formatter:function(){return Highcharts.numberFormat(this.value/(1024*1024),2,'.','')+' GB';}}},
				{title:{text:null},labels:{formatter:function(){return Highcharts.numberFormat(this.value/(1024*1024),2,'.','')+' GB';}}},
				{title:{text:null},labels:{formatter:function(){return Highcharts.numberFormat(this.value/(1024*1024),2,'.','')+' GB';}}},
				{title:{text:null},labels:{formatter:function(){return Highcharts.numberFormat(this.value/(1024*1024),2,'.','')+' GB';}}},
				{title:{text:null},labels:{formatter:function(){return Highcharts.numberFormat(this.value/(1024*1024),2,'.','')+' GB';}}},
				{title:{text:null},labels:{formatter:function(){return Highcharts.numberFormat(this.value/(1024*1024),2,'.','')+' GB';}}}
			]
		}
		else if(mod=='inet'){
			view.yAxis = [
				{title:{text:null},labels:{formatter:function(){return Highcharts.numberFormat(this.value/(1024*1024*1024),2,'.','')+' GB';}}},
				{title:{text:null},labels:{formatter:function(){return Highcharts.numberFormat(this.value/(1024),2,'.','')+' KB/s';}}},
				{title:{text:null},labels:{formatter:function(){return Highcharts.numberFormat(this.value/(1024*1024*1024),2,'.','')+' GB';}}},
				{title:{text:null},labels:{formatter:function(){return Highcharts.numberFormat(this.value/(1024),2,'.','')+' KB/s';}}}
			]
		}
		else if(mod=='zarc'){
			view.yAxis = [
				{title:{text:null},labels:{formatter:function(){return Highcharts.numberFormat(this.value/(1024*1024),2,'.','')+' GB';}}},
				{title:{text:null},labels:{formatter:function(){return Highcharts.numberFormat(this.value/(1024*1024),2,'.','')+' GB';}}},
				{title:{text:null},labels:{formatter:function(){return Highcharts.numberFormat(this.value/(1024*1024),2,'.','')+' GB';}}},
				{title:{text:null},labels:{formatter:function(){return Highcharts.numberFormat(this.value/(1024*1024),2,'.','')+' GB';}}},
				{title:{text:null},labels:{formatter:function(){return Highcharts.numberFormat(this.value/(1024*1024),2,'.','')+' GB';}}},
				{title:{text:null},labels:{formatter:function(){return Highcharts.numberFormat(this.value/(1024*1024),2,'.','')+' GB';}}},
				{title:{text:null},labels:{formatter:function(){return Highcharts.numberFormat(this.value/(1024*1024),2,'.','')+' GB';}}},
				{title:{text:null},labels:{formatter:function(){return Highcharts.numberFormat(this.value/(1024*1024),2,'.','')+' GB';}}},
				{title:{text:null},labels:{formatter:function(){return Highcharts.numberFormat(this.value/(100),2,'.','');}}}
			]
		}
		else if(mod=='disk'){
			view.yAxis = [
				{title:{text:null},labels:{formatter:function(){return Highcharts.numberFormat(this.value/(1024*1024),2,'.','')+' GB';}}},
				{title:{text:null},labels:{formatter:function(){return Highcharts.numberFormat(this.value/(1024*1024),2,'.','')+' GB';}}},
				{title:{text:null},labels:{formatter:function(){return Highcharts.numberFormat(this.value/(10000),2,'.','')+' E4';}}},
				{title:{text:null},labels:{formatter:function(){return Highcharts.numberFormat(this.value/(1000000),2,'.','')+' E6';}}}
			]
		}
		else{
			view.yAxis = [
				{title:{text:null},labels:{formatter:function(){return this.value;}}},
				{title:{text:null},labels:{formatter:function(){return this.value;}}},
				{title:{text:null},labels:{formatter:function(){return this.value;}}},
				{title:{text:null},labels:{formatter:function(){return this.value;}}},
				{title:{text:null},labels:{formatter:function(){return this.value;}}},
				{title:{text:null},labels:{formatter:function(){return this.value;}}},
				{title:{text:null},labels:{formatter:function(){return this.value;}}},
				{title:{text:null},labels:{formatter:function(){return this.value;}}},
				{title:{text:null},labels:{formatter:function(){return this.value;}}}
			]
		}
		Highcharts.stockChart(set['rendto'],view);
	};
	var num = 0;
	var ser = [];
	for(var k in set['series']){
		ser[num] = {
			name:set['series'][k],
			data:raw[k],
			yAxis:num,
			visible:false
		};
		if(set['default'][k]){
			ser[num]['visible'] = true;
		}
		num++;
	}
	createview(set['device']);
};
var statload=function(){
	statrend(statconf,statdata);
}
var timegood=function(num){
	var r = '';
	if(num>2592000){
		r = Math.round(num/2592000)+'m ago';
	}
	else if(num>86400){
		r = Math.round(num/86400)+'d ago';
	}
	else if(num>3600){
		r = Math.round(num/3600)+'h ago';
	}
	else if(num>60){
		r = Math.round(num/60)+'i ago';
	}
	else{
		r = Math.round(num/1)+'s ago';
	}
	return r;
}
var timediff=function(eid){
	var ele = document.getElementById(eid);
	window.setInterval(function(){
		var val = parseInt(ele.getAttribute('data-diff'))+1;
		ele.setAttribute('data-diff',val);
		ele.innerHTML = timegood(val);
	},1000);
}
var timeload=function(){
	for(k in servlist){
		var eid = servlist[k]+'-diff';
		timediff(eid);
	}
}
var setcookie=function(str,val){
	var t = new Date();
	t.setTime(t.getTime()+(365*24*60*60*1000));
	document.cookie = str+'='+val+';expires='+t.toUTCString()+';path=/';
};
var getcookie=function(str){
	return document.cookie.split(';').reduce(function(prev,c){
		var arr = c.split('=');
		return (arr[0].trim()===str)?arr[1]:prev;
	},undefined);
};
var pageload=function(num,mod){
	if(num){
		setInterval(function(){
			window.location.reload(1);
		},parseInt(mod*1000));
		var str = 'pageload';
		if(getcookie(str)!=num){
			setcookie(str,num);
			window.location.reload(1);
		}
	}
	return false;
};
var interval=function(num){
	if(num){
		var str = 'interval';
		if(getcookie(str)!=num){
			setcookie(str,num);
			window.location.reload(1);
		}
	}
	return false;
};
window.addEventListener('load',function(){
	if(typeof loadinit==='function'){
		loadinit();
	}
});
