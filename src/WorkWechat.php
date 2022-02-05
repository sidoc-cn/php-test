<?php

namespace sidoc;

use think\facade\Log;
use Throwable;

// 企业微信
class WorkWechat {

    // 发送通知消息给管理员
    static public function notiMessage($title,$content){
        self::sendMessageToAdmin($title,$content,"通知消息");
    }

    // 发送异常消息给管理员
    static public function reportException($exception){
        try{
            // 不要上报404、403等HTTP请求异常
            if($exception instanceof \think\exception\HttpException){
                if($exception->getStatusCode() == 404 || $exception->getStatusCode() == 403){
                    return;
                }
            }

            $code = $exception->getCode();
            $content = $exception->getMessage();
            $file = $exception->getFile();
            $line = $exception->getLine();
            $message = "<div class=\"gray\">".date("Y-m-d H:i:s",time())."<br/></div><div class=\"normal\">异常：".$content."</div><div class=\"normal\">位置：".$file."</div><div class=\"normal\">行数：".$line."</div><div class=\"normal\">错误码：".$code."</div>";
            if(method_exists($exception,'getStatusCode')){
                $message .= ("<div class=\"normal\">HTTP状态码：".$exception->getStatusCode()."</div>");
            }
            self::sendMessageToAdmin("系统发生异常，请尽快处理！",$message,"异常上报");
        }catch (Throwable $e){
            // 捕获所有异常，避免异常循环上报
            Log::error($e->__toString());
        }
    }


    // 发送消息至管理员
    static private function sendMessageToAdmin($title,$message,$messageType){
       
        $param = [
            "touser"=>'YANGWW',   // 消息接收人
            "msgtype"=>'textcard',    // 消息类型
            "agentid"=>work_wechat_sidoc_admin_id, // 企业微信自建应用的ID
            "textcard"=>[             // 消息内容
                "title"=>$title,
                "description"=>$message,
                "url"=>"https:www.sidoc.cn",
                "btntxt"=>$messageType
            ],
        ];
        $param = json_encode($param);

        // 获取access_token,该接口不能频繁调用，必须缓存access_token
        $url = "https://qyapi.weixin.qq.com/cgi-bin/message/send?debug=1&access_token=".self::getToken();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, true);        // 请求方式
        curl_setopt($ch, CURLOPT_POSTFIELDS,$param); // post请求数据
        curl_setopt($ch, CURLOPT_URL, $url);         // 请求地址
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // 将请求结果以字符串方式返回
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,20); // 请求超时时间

        // 执行请求
        $responseContent = curl_exec($ch);  
        // 获取请求信息
        $responseInfo    = curl_getinfo($ch);
        // 关闭请求
        curl_close($ch);

        if($responseInfo['http_code'] != 200){
            Log::error("企业微信发送消息时发生错误：");
            Log::error(json_encode($responseInfo));
        }
        
        $token_jsonarr = json_decode($responseContent, true);
        if(array_key_exists('errcode',$token_jsonarr) && $token_jsonarr['errcode'] != 0){
            Log::error("企业微信发送消息时发生错误：");
            Log::error($responseContent);
        }
    }

    // 获取access_token
    private static function getToken(){

        // access_token的每日调用次数有一定限制，因此开发者必须在自己的服务全局缓存access_token，access_token有效期7200秒

        // 0.1> 从缓存中获取access_token
        $key = "work_wechat_access_token_".work_wechat_corpid;
        $redis = RedisManger::instance();
        $access_token = $redis->get($key);
        if($access_token){
            return $access_token;
        }

        // 0.2> 从微信服务器请求access_token
        $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=".work_wechat_corpid."&corpsecret=".work_wechat_corpsecret;
        $access_token_Arr =  file_get_contents($url);
        $token_jsonarr = json_decode($access_token_Arr, true);
        if(array_key_exists('errcode',$token_jsonarr) && $token_jsonarr['errcode'] != 0){
            Log::error("企业微信获取access_token发生时错误：");
            Log::error($access_token_Arr);
        }

        $expires_in = $token_jsonarr["expires_in"] - 60 * 20; // 保险期间，提前20分钟重新获取access_token，以避免时钟差时导致未能及时更新
        $access_token = $token_jsonarr["access_token"];
        $redis->setex($key,$expires_in,$access_token);
        return $access_token;
    }
}