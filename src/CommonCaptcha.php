<?php

namespace sidoc;

// 公共行为验证
class CommonCaptcha{

   /**
    * 行为验证，此函数仅由Controller层调用
    *
    * @param string $verify_type   验证码类型：graphic(图片验证)、tCaptcha(腾迅云行为验证)
    * @return \think\response\Json
    */
   static public function verify($verify_type="tCaptcha"){

      switch ($verify_type){
         case "graphic":{ // 图形验证码
            $verify = request()->param("captcha");
            if(!captcha_check($verify)){
               return JsonUtil::json_response("","验证码错误", STATUS_FAILED);
            };
            break;
         }
         case "tCaptcha":{ // 腾迅云行为验证
            $ticket = request()->param("ticket");
            $randstr = request()->param("randstr");
            $verifyResult = TCaptcha::verify($ticket,$randstr);
            if($verifyResult != 1){
               return JsonUtil::json_response("","行为验证错误，重新验证", STATUS_FAILED);
            }
            break;
         }
         default:{
            return JsonUtil::json_response("","验证码类型错误", STATUS_FAILED);
         }
      }
   }

}