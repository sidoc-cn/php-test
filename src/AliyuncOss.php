<?php

namespace sidoc;

use OSS\OssClient;
use OSS\Core\OssException;
use think\facade\Env;

define("accessKeyId",aliyun_oss_key);
define("accessKeySecret",aliyun_oss_secret);

// 根据项目环境决定是否使用内网访问OSS; ECS内网访问OSS,速度快且流量免费；
if(Env::get('APP_DEBUG')){
    define("endpoint","oss-cn-hangzhou.aliyuncs.com"); // 测试桶
    define('bucket',"sidoc");
}else{
    define("endpoint","oss-cn-hangzhou.aliyuncs.com"); // 正式桶
    define('bucket',"sidoc");
}


// 阿里云对象存储
class AliyuncOss{

    /**
     * 上传文件
     *
     * @param [type] $path     文件在Bucket中的完整路径
     * @param [type] $filePath 文件在本地的路径
     * @return void
     */
    static public function uploadFile($path,$filePath){
        
        try {
            $ossClient = new OssClient(accessKeyId, accessKeySecret, endpoint);
            $ossClient->uploadFile(bucket, $path, $filePath);
        } catch (OssException $e) {
            throw $e;
        }
    }

    /**
     * 删除文件
     *
     * @param [type] $path 文件在Bucket中的完整路径
     * @return void
     */
    static public function deleteStaticPages($path){

        try {
            $ossClient = new OssClient(accessKeyId, accessKeySecret, endpoint);
            $ossClient->deleteObject(bucket, $path);
        } catch (OssException $e) {
            throw $e;
        }
    }
}