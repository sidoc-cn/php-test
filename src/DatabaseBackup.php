<?php

namespace sidoc;

use think\facade\Env;

// 数据库备份
class DatabaseBackup {

    // 备份
    static public function backup($projectName,$taskId) {

        $t1 = microtime(true);

        // 0.1> 备份数据库
        $databaseName = Env::get('DATABASE.DATABASE');
        $username = Env::get('DATABASE.USERNAME');
        $password = Env::get('DATABASE.PASSWORD');
        $time = date('Y-m-d_H:i:s');
        $backupPath  = app_path()."dataBackup/{$databaseName}-{$time}.sql";
        $result = shell_exec("mysqldump -u{$username} -p{$password} {$databaseName} > {$backupPath} 2>&1"); // '2>&1'是让执行管道输出结果。
        echo $result;

        // 0.2> 加密sql文件
        FileEncryption::encryptFile($backupPath,encryptionKey,$backupPath.".enc");
        // FileEncryption::decryptFile($backupPath.".enc",encryptionKey,$backupPath.".enc.sql"); // 解密

        // 0.3> 备份至对象存储
        self::backupToCos($projectName);
        
        // 0.4> 更新任务状态
        $t2 = microtime(true);
        $timeConsuming = round($t2-$t1,3)/1000000;
        self::updateTask($taskId,$timeConsuming);
    }

    // 备份至对象存储
    static private function backupToCos($projectName) {

        // 0.2> 保存备份数据至对象存储 -------------------------------------------------------------
        $folder = app_path()."dataBackup";
        $handle = opendir($folder);
        while($file=readdir($handle)){
            $suffix = pathinfo($file, PATHINFO_EXTENSION); // 获取文件后缀
            $path = $folder."/".$file;
            if($suffix == 'enc'){
                // 上传
                TencenyunCos::pushObject($projectName."/database-backup/{$file}",$path);
            }
            if($suffix == 'sql' || $suffix == 'enc'){
                unlink($path); // 删除删除
            }
        }
         
    }

    // 更新任务
    static private function updateTask($taskId,$timeConsuming) {
   
        // 0.3> 更新任务状态 --------------------------------------------------------------------
        $arr['id'] = $taskId;
        $arr['des'] = "Sidoc后台管理备份完成，耗时".$timeConsuming."秒";
        $arr['status'] = "normal";
        $domain = Env::get('SIDOC_ADMIN_SERVICE');
        RemoteCallTools::request($domain."/task/updateTask",$arr);
    }

    // 恢复
    static public function recover($prefix = "") {

    }




}