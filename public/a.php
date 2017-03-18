<?php

$connect = mysqli_init();

        $_isConnected = @mysqli_real_connect(
            $connect,
            'localhost',
            'root',
            '19790111x',
            null,
            '3306',
		'/data/mysqldb/mysql.sock'
        );
        
        if (!$_isConnected) {

echo mysqli_connect_error();
} else {

echo 'ddd'; 

}

