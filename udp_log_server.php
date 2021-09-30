<?php
/***************************
UDP日志服务器

日志协议格式为:  {filename},{log_timestamp},{urlencode info_1},{urlencode info_2},……,{urlencode info_n}
文件名必须以英文字母开始，只可包含英文字母和数字的最长不超过32位的字符组成
单条日志数据不可大于1024字节
日志目录按月分割，文件自动按天存放
如有疑问：请联系artfantasy@gmail.com
****************************/
function print_console ($name, $time, $info, $ip, $port) {
    $str = json_encode([
        'name' => $name,
        'time' => $time,
        'info' => $info,
        'ip' => $ip,
        'port' => $port
    ], JSON_UNESCAPED_UNICODE);
    if ($str === false) {
        echo "[udp] error: encode error \r\n";
        return;
    }
    echo '[udp] info: ' . $str . "\r\n";
    return;
}
set_time_limit(0);

$path		= '/app/data/udp_server/';	//此处需要加 / 
$address	= '0.0.0.0';
$port		= 8010;

if(!is_writable($path)) {
	echo "Log Path Access Denied.({$path})\n";
	exit;
}

if (($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) < 0) {
    echo "socket_create() failed: reason: " . socket_strerror($socket) . "\n";
    exit;
}

if (($ret = socket_bind($socket, $address, $port)) < 0) {
    echo "socket_bind() failed: reason: " . socket_strerror($ret) . "\n";
    exit;
}

echo date('Y-m-d H:i:s') . ",Socket Server Starting...\n";

while (true) {  
	$buff = $from_ip = $from_port = null;
    socket_recvfrom($socket, $buff, 2048, 0, $from_ip, $from_port);
    
    $info = explode(',', $buff);
    $name = array_shift($info);
    $time = array_shift($info);

	if(!preg_match('/^[a-z][a-z0-9_]{0,31}$/i', $name)) {
    	continue;
    }
    
    if(!preg_match('/^1[0-9]{9}$/', $time)) {
    	continue;
    }
    
    $real_path = $path . date('Ym', $time) . '/';
    if(!file_exists($real_path)) {
    	@mkdir($real_path);
    }
    
    array_unshift($info, date('Y-m-d H:i:s', $time), $from_ip);
    $info = array_map('urldecode', $info);
    error_log(implode(',', $info) . "\n", 3, $real_path . $name . '_' . date('Y-m-d', $time) . '.log');
}
socket_close($socket);
?>