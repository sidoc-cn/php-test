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
    static public function getToken($request,$domain="sidoc.cn") {

        // 0.1> 获取token（分别兼容从请求头、Cookie和请求参数中来获取token）
        $token = $request->header("authorization");

        // 0.2> 若从请求头无法获取token，则尝试从Cookie中获取token，以兼容未能前后分离的项目；
        if(empty($token)){ 
            $token = Cookie::get("authorization");
        }

        // 通常情况下，分别从请求头和Cookie中获取token，就能满足所有项目需求，但个别场景下仍无法满足实际使用,例如：使用三方账号单点登录成功后，用户服务系统需要
        // 通过url链接直接跳回调当前项目，但使用url链接跳转无法在请求头中添加token，更无法在用户服务系统中为当前项目设置Cookie，这种情况下就需要将token放置在
        // 请求参数中带回到当前项目；

        // 从请求参数中获取token
        // 为了尽量保护token安全，token名称尽量使用不易被发现和使用的名称，此处使用t1作为token名称
        if(empty($token)){ 
            $token   = $request->param("t1"); 
            $expires = $request->param("e1"); 
            
            // 在cookie中保存token
            // cookie域名有效性设置：http://www.cnblogs.com/fsjohnhuang/archive/2011/11/22/2258999.html
            // setcookie("cookieName","cookieValue, 0, "/", ".test.com"); // 过期时间为0，表示浏览器关闭时销毁cookie;
            
            // 作用域："abc.com" 可以在abc.com主域名之下的多级子域名有效
            //       ".abc.com" 只能在二级域名以及"www.abc.com"下有效
            // 参数secure：cookie是否只能通过https发送
            // 参数httponly：是否只能通过http协议访问cookie
            // 配置详见：https://www.php.net/manual/zh/function.setcookie.php
            setcookie("authorization",$token,$expires,'/',$domain,false,true);
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