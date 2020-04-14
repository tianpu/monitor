这是一个在freebsd系统上使用php实现的服务器监控系统，使用php调用基本系统命令写的client使用crond主动push数据到server端。

Freebsd 12运行良好，其它*nix系统应该稍微修改client下可以使用，代码越写越乱，自用暂时足够了。

**系统需求**  
*Client: curl, php74, php74-curl, 以及基本系统命令*  
*Server: php74, php-mysqli*  
  
**Client依赖的系统命令**
```csh
ps axc | wc -l
netstat -an4 | wc -l
uname -mrs
sysctl -a
top -n
df -ik
netstat -ibn
```

**基本思路**  
*假设所有代码上传到https://www.example.org/*  
*1. 配置。设置conf.php的$conf['auth']和$conf['mysql']两个字段。如有必要自定义$conf['alias']字段，是key为IP地址value为服务器名的自定义字段。*  
*2. 安装。打开https://www.example.org/?action=install 按照屏幕提示安装。*  
*3. 删除。打开https://www.example.org/?action=delete&sid={sid} 删除历史数据，如服务器不是100%在线，会在界面上主动显示删除链接。*  
  
**可能的问题**
- *如Server不是64位，网卡存储值为Byte值，其它设备为KB，php的int32非常容易越界。Client没关系不依赖数学计算，能读取到数据自然可以上传。*
- *数据存储为每五分钟一次，在此期间自动更新为最新数据。特定设备的统计图表，其值是时间周期内的平均值。*
- *服务器名显示优先级为$conf['alias']自定义，Client上传服务器名，程序设备的唯一编号sid。*
  
  
**ScreenShot**  
*服务器列表*  
![](https://raw.githubusercontent.com/tianpu/monitor/master/screenshot/servlist.png)
  
*设备列表*  
![](https://raw.githubusercontent.com/tianpu/monitor/master/screenshot/devlist.png)
  
*设备统计*  
![](https://raw.githubusercontent.com/tianpu/monitor/master/screenshot/devinfo.png)

