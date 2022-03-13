<?php

namespace sidoc;

use think\facade\Cookie;
use think\facade\Env;

// 中间件常用工具封装
class MiddlewareTool {


    /**
     * 获取Token
     *
     * @param [type] $request
     * @return void
     */
    static public function getToken($request) {

        // 0.1> 获取token（分别兼容从请求头、请求参数和Cookie中来获取token）
        $token = $request->header("authorization");

        // 0.2> 若从请求头中无法获取token，则尝试从请求参数中获取；此举可兼容部分重要场景，例如：三方授权回调后，会直接通过链接跳回本项目，
        // 因为直接使用连接跳转无法在请求头中添加token，因此需要在请求参数中放置token；为了尽量保护token安全，token名称尽量使用不易
        // 被发现的简写，此处设为t1;
        if(empty($token)){ 
            $token = $request->param("t1"); 
        }

        // 0.3> 若从请求头和请求参数中均无法获取token，则尝试从Cookie中获取token，以便兼容未能前后分离的项目；
        if(empty($token)){ 
            $token = Cookie::get("authorization");
        }
        return $token;
    }

    /**
     * 验证Token
     *
     * @param [type] $token
     * @return void
     */
    static public function verifyToken($token) {

        $authInfo = null;
        if($token){
            // 被远程请求的服务必须删除Trace，否则将无法输出纯字符串，以致加解密错误，详见：https://www.sidoc.cn/doc/1124.html
            $authInfo = RemoteCallTools::request(Env::get('SIDOC_USER_SERVICE')."/auth",['token'=>$token]);
        }
        return $authInfo;
    }


}