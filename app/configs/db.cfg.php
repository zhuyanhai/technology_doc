<?php
/**
 * 数据库 配置文件
 * 
 * @author allen <allenifox@163.com>
 */
return array(
    //配置的命名空间名字
    'namespace' => 'db',
    
    'db' => array(//命名空间中的配置内容
        
        'default' => array(
            'master' => array(
                'host'     => 'localhost',
                'port'     => '3306',
                'socket'   => '/tmp/mysql.sock',
                'username' => 'root',
                'password' => '19820111x',
                'charset'  => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ),
            'slave' => array(
                'host'     => 'localhost',
                'port'     => '3306',
                'socket'   => '/tmp/mysql.sock',
                'username' => 'root',
                'password' => '19820111x',
                'charset'  => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ),
        ),
        'user' => array(
            'dbName' => 'bxshare_user',
        ),
    ),
);
