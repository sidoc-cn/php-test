<?php

namespace sidoc;

class JsonUtil{

   /**
    * 将数据转为JSON，并附带公共参数
    *
    * @param $data 业务数据
    */
   static  function toJson($data,$errCode) {

   }

   /**
    * 将数据转为响应JSON
    *
    * @param $data             欲转换的数据
    * @param string $meeage    响应消息
    * @param string $status_code  响应状态码; 0 表示成功,-1表示错误。
    * @return \think\response\Json
    */
   static function  json_response($data,$meeage="请求成功~",$status_code=0){
      $data = ["message"=>$meeage, 'status_code' => $status_code,'data' => $data];
      return json($data);
   }
}


