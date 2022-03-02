<?php

namespace sidoc;

use think\facade\Env;

// 数据库备份
class DatabaseBackup {

    // 备份
    static public function backup() {

        $databaseName = Env::get('DATABASE.DATABASE');
        $username = Env::get('DATABASE.USERNAME');
        $password = Env::get('DATABASE.PASSWORD');
        $time = date('Y-m-d_H:i:s');
        $backupPath  = app_path()."dataBackup/{$databaseName}-{$time}.sql";

        $result = shell_exec("mysqldump -u{$username} -p{$password} {$databaseName} > {$backupPath} 2>&1"); // '2>&1'是让执行管道输出结果。
        echo $result;
    }

    // 备份至对象存储
    static public function backupToCos() {

        

    }

    // 恢复
    static public function recover($prefix = "") {

    }






}