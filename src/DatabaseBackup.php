<?php

namespace sidoc;

use think\facade\Env;

// 数据库备份
class DatabaseBackup {

    // 备份
    static public function backup($projectName,$taskId) {

        $t1 = microtime(true);

        // 0.0> 配置数据库登录信息
        // 高版本的MySql在备份数据时，必须从配置文件中读取敏感信息，此举是为了提高数据库安全性
        // 此处将数据库登录信息写入至配置文件中，以供数据库备份命令调用
        self::configLoginInfo();

        // 0.1> 备份数据库
        $databaseName = Env::get('DATABASE.DATABASE');
        $time = date('Y-m-d_H:i:s');
        $backupPath  = app_path()."dataBackup/{$databaseName}-{$time}.sql";
        $result = shell_exec("mysqldump --defaults-extra-file=/var/www/html/sidoc/app/dataBackup/mysql.conf {$databaseName} > {$backupPath} 2>&1"); // '2>&1'是让执行管道输出结果。
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

    // 配置数据库登录信息
    static private function configLoginInfo(){

        $configFile  = app_path()."dataBackup/mysql.conf";
        $hostname = Env::get('DATABASE.HOSTNAME');
        $username = Env::get('DATABASE.USERNAME');
        $password = Env::get('DATABASE.PASSWORD');
        
        // 以写入方式打开文件，并清除文件内容，如果文件不存在则尝试创建之
        $nginxServerConfig = fopen($configFile, "w");
        fwrite($nginxServerConfig,"[client]               \n");
        fwrite($nginxServerConfig,"host='{$hostname}'     \n");
        fwrite($nginxServerConfig,"user='{$username}'     \n");
        fwrite($nginxServerConfig,"password='{$password}' \n");
        fclose($nginxServerConfig);
    }

    // 备份至对象存储
    static private function backupToCos($projectName) {

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
   
        $arr['id'] = $taskId;
        $arr['des'] = "数据备份完成，耗时".$timeConsuming."秒";
        $arr['status'] = "normal";
        $domain = Env::get('SIDOC_ADMIN_SERVICE');
        RemoteCallTools::request($domain."/task/updateTask",$arr);
    }

    // 恢复
    static public function recover($prefix = "") {

    }




}