<?php
namespace sidoc;

class HttpRequest{

   /**
    * 发送GET请求
    *
    * @param $url
    * @param $param
    * @param $headers
    * @return bool|string
    */
   static public function get($url,$param=[],$headers=[]){
      return self::request("GET",$url,$param,$headers);
   }

   /**
    * 发送POST请求
    *
    * @param $url
    * @param $param
    * @param $headers
    * @return bool|string
    */
   static public function post($url,$param=[],$headers=[]){
      return self::request("POST",$url,$param,$headers);
   }

   /**
    * 发送请求封装
    *
    * @param $requestMethod
    * @param $url
    * @param $param
    * @param $headers
    * @return bool|string
    */
   static private function request($requestMethod,$url,$param,$headers){

      $oCurl = curl_init ();

      curl_setopt ( $oCurl, CURLOPT_URL, $url );
      curl_setopt ( $oCurl, CURLOPT_RETURNTRANSFER, 1 );
      curl_setopt ( $oCurl, CURLOPT_POST, true );
      curl_setopt ( $oCurl, CURLOPT_POSTFIELDS,$param);
      curl_setopt($oCurl, CURLOPT_HTTPHEADER, $headers); //设置header
      curl_setopt ( $oCurl, CURLOPT_CUSTOMREQUEST, $requestMethod);  // 请求方式,POST/GET/...
      curl_setopt ( $oCurl, CURLOPT_HEADER, false);

      // 采用https协议，一定要加入以下两句
      curl_setopt( $oCurl, CURLOPT_SSL_VERIFYPEER, false); // 不验证证书,下同
      curl_setopt( $oCurl, CURLOPT_SSL_VERIFYHOST, false); //

      $sContent = curl_exec ( $oCurl );
      $aStatus = curl_getinfo ( $oCurl );
      curl_close ( $oCurl );

      return $sContent;
   }

   
}