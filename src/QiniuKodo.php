<?php

namespace sidoc;


// 引入鉴权类
use Qiniu\Auth;
// 引入上传类
use Qiniu\Storage\UploadManager;


/*
 * 七牛云对象存储
 * */
class QiniuKodo{

   /**
    * 备份数据库备份至七牛对象存储
    *
    * @param $filePath
    * @return mixed
    * @throws \Exception
    */
   static function databaseBackup($filePath){

      // 需要填写你的 Access Key 和 Secret Key
      $accessKey = qiniu_kodo_key;
      $secretKey = qiniu_kodo_secret;
      $bucket = "databbase-backup";

      // 构建鉴权对象
      $auth = new Auth($accessKey, $secretKey);

      // 生成上传 Token
      $token = $auth->uploadToken($bucket);

      // 上传到七牛后保存的文件名
      $fileNameArray = explode(DIRECTORY_SEPARATOR,$filePath);
      $key = env.'/'.end($fileNameArray);

      // 初始化 UploadManager 对象并进行文件的上传。
      $uploadMgr = new UploadManager();

      // 调用 UploadManager 的 putFile 方法进行文件的上传。
      list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
      if ($err !== null) {
         return $err;
      }

   }

}


