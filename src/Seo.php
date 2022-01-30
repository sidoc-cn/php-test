<?php

namespace sidoc;

class Seo{

   // 1.0 推送资源至所有搜索引擎 --------------------------------------------------------------------------------------
   static public function pushToAllSEO($urlArray){
      if(env === 'online') {
         if($_SERVER['SERVER_NAME'] != "www.sidoc.cn"){
               return;
         }
         if(is_array($urlArray)) {
               self::pushToBaidu($urlArray);
         }else{
               self::pushToBaidu([$urlArray]);
         }
      }
   }

   // 2.0 更新资源至所有搜索引擎 --------------------------------------------------------------------------------------
   static public function updateToAllSEO($urlArray){

      if(env === 'online') {
         if($_SERVER['SERVER_NAME'] != "www.sidoc.cn"){
               return;
         }
         if(is_array($urlArray)) {
               self::updateToBaidu($urlArray);
         }else{
               self::updateToBaidu([$urlArray]);
         }
      }
   }

   // 3.0 从所有搜索引擎中删除资源 --------------------------------------------------------------------------------------
   static public function deleteResourcesOnAllSEO($urlArray){

      if(env === 'online') {
         if($_SERVER['SERVER_NAME'] != "www.sidoc.cn"){
               return;
         }
         if(is_array($urlArray)) {
               self::deleteResourcesOnBaidu($urlArray);
         }else{
               self::deleteResourcesOnBaidu([$urlArray]);
         }
      }
   }




   // ################################################### 以下为私有方法 ###############################################


   // 1.0 推送资源至百度搜索 -------------------------------------------------------------------------------------------
   static private function pushToBaidu($urlArray){

//        $urls = array(
//            'http://www.example.com/1.html',
//            'http://www.example.com/2.html',
//        );

      $urls = $urlArray;

      $api = 'http://data.zz.baidu.com/urls?site=www.sidoc.cn&token='.baidu_seo_token;
      $ch = curl_init();
      $options =  array(
         CURLOPT_URL => $api,
         CURLOPT_POST => true,
         CURLOPT_RETURNTRANSFER => true,
         CURLOPT_POSTFIELDS => implode("\n", $urls),
         CURLOPT_HTTPHEADER => array('Content-Type: text/plain'),
      );
      curl_setopt_array($ch, $options);
      $result = curl_exec($ch);
      $result = json_decode($result);

//        if($result->success <= 0){
//            throw new \Exception("百度SEO推送链接失败，此次推送成功0条；推送url为：".implode("\n", $urls));
//        }
   }

   // 1.1 更新资源至百度搜索
   static private function updateToBaidu($urlArray){

      $urls = $urlArray;

      $api = 'http://data.zz.baidu.com/update?site=www.sidoc.cn&token='.baidu_seo_token;
      $ch = curl_init();
      $options =  array(
         CURLOPT_URL => $api,
         CURLOPT_POST => true,
         CURLOPT_RETURNTRANSFER => true,
         CURLOPT_POSTFIELDS => implode("\n", $urls),
         CURLOPT_HTTPHEADER => array('Content-Type: text/plain'),
      );
      curl_setopt_array($ch, $options);
      $result = curl_exec($ch);
      $result = json_decode($result);

//        if($result->success <= 0){
//            throw new \Exception("百度SEO推送链接失败，此次推送成功0条；推送url为：".implode("\n", $urls));
//        }

   }

   // 1.2 从百度中删除资源
   static private function deleteResourcesOnBaidu($urlArray){

      $urls = $urlArray;

      $api = 'http://data.zz.baidu.com/del?site=www.sidoc.cn&token='.baidu_seo_token;
      $ch = curl_init();
      $options =  array(
         CURLOPT_URL => $api,
         CURLOPT_POST => true,
         CURLOPT_RETURNTRANSFER => true,
         CURLOPT_POSTFIELDS => implode("\n", $urls),
         CURLOPT_HTTPHEADER => array('Content-Type: text/plain'),
      );
      curl_setopt_array($ch, $options);
      $result = curl_exec($ch);
      $result = json_decode($result);

//        if($result->success <= 0){
//            throw new \Exception("百度SEO删除链接失败，此次删除成功0条；删除url为：".implode("\n", $urls));
//        }


   }

}