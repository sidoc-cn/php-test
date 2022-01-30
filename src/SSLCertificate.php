<?php

namespace sidoc;

use Exception;
use think\facade\Log;

class SSLCertificate{

    // 安装SSL证书
    // 注：此函数仅可在Controller中调用，不要在命令行中调用此函数，因为命令行程序执行过程中抛出的异常不会全局捕获，要做特殊处理；
    static public function installSSL($sslFiles){
        
        if(!$sslFiles){
            throw new Exception("安装SSL证书时发生错误：SSL证书文件为空");
        }

        foreach ($sslFiles as $file) {
            $fileInfo = $file->getInfo();

            // 0.1> 移动证书文件至 /var/www/ssl
            $file->move("/var/www/ssl",$fileInfo['name'],true);
            
            // 0.2> 重启nginx，以使证书生效
            exec("sudo /bin/systemctl restart nginx 2>&1",$out,$status);
            if($status != 0){
                Log::error("重启nginx - 重启nginx失败,错误信息如下：");
                Log::error($out);
                throw new Exception("部署SSL证书时发生错误","重启nginx失败,错误信息如下:".json_encode($out));
            }
        }
    }
}

