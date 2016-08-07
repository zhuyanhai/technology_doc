<?php
/**
 * 数据库 配置文件
 * 
 * @author allen <allenifox@163.com>
 */
return array(
    'default' => array(
        'master' => array(
            'host'     => 'localhost',
            'port'     => '3306',
            'socket'   => '/data/mysqldb/mysql.sock',
            'username' => 'root',
            'password' => '19790111x',
            'charset'  => 'utf8mb4',
            'collation' => 'utf8mb4_general_ci',
        ),
        'slave' => array(
            'host'     => 'localhost',
            'port'     => '3306',
            'socket'   => '/data/mysqldb/mysql.sock',
            'username' => 'root',
            'password' => '19790111x',
            'charset'  => 'utf8mb4',
            'collation' => 'utf8mb4_general_ci',
        ),
    ),
    'user' => array(
        'dbName' => 'bxshare_user',
    ),
);