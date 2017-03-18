<?php
define('DEBUG', 'on');
define('WEBPATH', __DIR__);

require __DIR__.'/../../entry.php';

//启动服务
C_Im::run(array(
    //监听的HOST
    'host'   => '0.0.0.0',
    //监听的端口
    'port'   => '9503',
    //工作模式
    'workMode' => 'WebSocket',
    //存储模式
    'storeMode' => 'Redis',
    
//    'worker_num'      => 1,
//    //不要修改这里
//    'max_request'     => 0,
//    'task_worker_num' => 1,
//    //是否要作为守护进程
//    'daemonize'       => 0,
));