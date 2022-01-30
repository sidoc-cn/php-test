<?php

namespace sidoc;

// 腾迅防水墙 - 滑动验证码
class TCaptcha{

   /*
   * 检验行为验证是否通过,
   *
   * 返回：1:验证成功，0:验证失败
   *
   * */
   static public function verify($ticket,$randstr){
   
      $AppSecretKey = tencen_captchao_key;     // $_GET["AppSecretKey"]
      $appid        = tencen_captchao_appid;   // $_GET["appid"]
      $Ticket       = $ticket;                 // $_GET["Ticket"]
      $Randstr      = $randstr;                // $_GET["Randstr"]
      $UserIP       = $_SERVER['REMOTE_ADDR']; // $_GET["UserIP"]

      $url = "https://ssl.captcha.qq.com/ticket/verify";
      $params = array(
         "aid" => $appid,
         "AppSecretKey" => $AppSecretKey,
         "Ticket" => $Ticket,
         "Randstr" => $Randstr,
         "UserIP" => $UserIP
      );
      $paramstring = http_build_query($params);
      $content = self::txcurl($url,$paramstring);
      $result = json_decode($content,true);
      if($result){
         // response	1:验证成功，0:验证失败，100:AppSecretKey参数校验错误[required]
         if($result['response'] == 1){
            return 1;
         }else{
            // echo $result['response'].":".$result['err_msg'];
            return 0;
         }
      }else{
         $title = "[".env."]"."腾迅防水墙 - 验证码发生错误";
         $content = "错误信息:没有具体的错误信息，可能是请求失败";
         ExceptionService::report($title,$content,__FILE__, __LINE__);
      }
   }

   /**
    * 请求接口返回内容
    * @param  string $url [请求的URL地址]
    * @param  string $params [请求的参数]
    * @param  int $ipost [是否采用POST形式]
    * @return  string
    */
   static private function txcurl($url,$params=false,$ispost=0){
      $httpInfo = array();

      $ch = curl_init();

      // 以下定制HTTP请求相关参数
      curl_setopt( $ch, CURLOPT_HTTP_VERSION , CURL_HTTP_VERSION_1_1 );
      curl_setopt( $ch, CURLOPT_USERAGENT , 'JuheData' );
      curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT , 60 );
      curl_setopt( $ch, CURLOPT_TIMEOUT , 60);
      curl_setopt( $ch, CURLOPT_RETURNTRANSFER , true );
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
      if( $ispost ) {

         curl_setopt( $ch , CURLOPT_POST , true );
         curl_setopt( $ch , CURLOPT_POSTFIELDS , $params );
         curl_setopt( $ch , CURLOPT_URL , $url );

      } else {
         if($params){
               curl_setopt( $ch , CURLOPT_URL , $url.'?'.$params );
         }else{
               curl_setopt( $ch , CURLOPT_URL , $url);
         }
      }
      $response = curl_exec( $ch );
      if ($response === FALSE) {
         //echo "cURL Error: " . curl_error($ch);
         return false;
      }
      $httpCode = curl_getinfo( $ch , CURLINFO_HTTP_CODE );
      $httpInfo = array_merge( $httpInfo , curl_getinfo( $ch ) );
      curl_close( $ch );
      return $response;
   }
   
}