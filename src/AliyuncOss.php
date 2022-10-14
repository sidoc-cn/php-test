<?php

namespace sidoc;

use OSS\OssClient;
use OSS\Core\OssException;
use think\exception\HttpException;

define("accessKeyId",aliyun_oss_key);
define("accessKeySecret",aliyun_oss_secret);

// 根据项目环境决定是否使用内网访问OSS; ECS内网访问OSS,速度快且流量免费；
if(env == "sidoc"){
    define("endpoint","oss-cn-hangzhou.aliyuncs.com"); // 外部访问OSS
}else{
    define("endpoint","oss-cn-beijing-internal.aliyuncs.com"); // 内网访问OSS
}


// 阿里云对象存储
class AliyuncOss{

   // 1.0 保存静态网页
   static public function saveStaticPages($filePathName,$page){
      try {
         $ossClient = new OssClient(accessKeyId, accessKeySecret, endpoint);

         if(env === 'local'){
            $bucket= "sidoc-static-pages-test"; // 测试用bucket
         }else{
            $bucket= "sidoc-static-pages"; // 正式bucket
         }

         // 文件名称
         $object = $filePathName;

         // 文件内容
         $content = $page;

         $ossClient->putObject($bucket, $object, $content);

      } catch (OssException $e) {
         $title = "阿里云对象存储OSS报错";
         $content = "阿里云对象存储OSS在 保存静态页面 时发生错误";
         ExceptionService::report($title,$content.":".json_encode($e->getMessage(),JSON_UNESCAPED_UNICODE),__FILE__, __LINE__);
         throw new HttpException(500,"保存失败");
      }
   }

   // 1.1 获取静态网页
   static public function getStaticPages($filePathName){

      try {
         $ossClient = new OssClient(accessKeyId, accessKeySecret, endpoint);

         // 存储空间名称
         if(env === 'local'){
            $bucket= "sidoc-static-pages-test"; // 测试用bucket
         }else{
            $bucket= "sidoc-static-pages"; // 正式bucket
         }

         // 文件名称
         $object = $filePathName;
         $content = $ossClient->getObject($bucket, $object);
         return $content;

      } catch (OssException $e) {
         $title = "阿里云对象存储OSS报错";
         $content = "阿里云对象存储OSS在 获取静态页面 时发生错误";
         ExceptionService::report($title,$content.":".json_encode($e->getMessage(),JSON_UNESCAPED_UNICODE),__FILE__, __LINE__);
         throw new HttpException(404,"页面找回不到了");
      }
   }

   // 1.2 删除静态网页
   static public function deleteStaticPages($filePathName){

      try {
         $ossClient = new OssClient(accessKeyId, accessKeySecret, endpoint);

         // 存储空间名称
         if(env == "sidoc_test" || env == "sidoc_pre"){
            $bucket= "sidoc-static-pages-test"; // 测试用bucket
         }else{
            $bucket= "sidoc-static-pages"; // 正式bucket
         }

         // 文件名称
         $object = $filePathName;

         $ossClient->deleteObject($bucket, $object);

      } catch (OssException $e) {
         $title = "[".env."]阿里云对象存储OSS报错";
         $content = "阿里云对象存储OSS在 删除静态页面 时发生错误";
         ExceptionService::report($title,$content.':'.json_encode($e->getMessage(),JSON_UNESCAPED_UNICODE),__FILE__, __LINE__);
         throw new HttpException(500,"删除失败");
      }
   }
}