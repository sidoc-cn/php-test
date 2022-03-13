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

        // 0.1> 从请求头中获取token,此方式仅适用于前后分离的项目
        $token = $request->header("authorization");
        if(!empty($token)){
            return $token;
        }

        // 0.2> 若从请求头无法获取token，则从Cookie中获取token，以兼容未能前后分离的项目；
        if(empty($token)){ 
            // 前后端未分离的项目必须使用Cookie,否则页面跳转将没有权限，请求头中token仅用于为接口授权
            $token = Cookie::get("authorization");
        }

        // 通常情况下，分别从请求头和Cookie中获取token，就能满足所有项目需求，但个别场景下仍无法满足实际使用,例如：使用三方账号单点登录成功后，用户服务系统需要
        // 通过url链接直接跳回到当前业务系统，但使用url链接跳转无法在请求头中添加token，更无法在用户服务系统中为当前项目设置Cookie(因此Cookie无法跨域设置)，这
        // 种情况下就需要将token放置在请求参数中带回到当前业务系统；将token放置在请求参数中回调业务系统有如下两种情况：
        // 情况一：被回调的业务系统不是前后分离的项目，这种情况下就要在服务端获取token，并保存到Cookie中；
        // 情况二：被回调的业务系统是前后分享的项目，这种情况下就要在前端获取token，并保存至localStorage中；

        // 从请求参数中获取token,并保存到Cookie中
        // 为了尽量保护token安全，token名称尽量使用不易被发现和使用的名称，此处使用t1作为token名称
        if(empty($token)){ 
            $token   = $request->param("t1"); 
            self::configCookie($token);
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


    /**
     * 配置Cookie
     * Cookie无法跨域设置，所以业务系统只能为自身的域名设置Cookie；
     * 也是因为Cookie无法跨域设置，所以用户服务系统授权完成后，无法为业务系统设置Cookie
     *
     * @param [type] $token
     * @param [type] $domain
     * @param [type] $expires
     * @return void
     */
    static private function configCookie($token) {

        // 默认设为36周有效期,按理说此处应设为永久有效，因此token的过期时间本身由用户服务系统决定
        // 微调token过期时间，使其在凌晨4点左右失效；避免在白天用户使用时突然失效，影响用户体验
        $hour = date('G',time()); // 获取当天时间(小时,24小时制)
        $expires = time() + 604800*36 + ((24 - $hour + 4) * 3600); // 有效时长(秒)，此处为一周(604800秒)，一周后凌晨4点过期
        
        // 在cookie中保存token
        // cookie域名有效性设置：http://www.cnblogs.com/fsjohnhuang/archive/2011/11/22/2258999.html
        // setcookie("cookieName","cookieValue, 0, "/", ".test.com"); // 过期时间为0，表示浏览器关闭时销毁cookie;
        
        // 作用域："abc.com" 可以在abc.com主域名之下的多级子域名有效
        //       ".abc.com" 只能在二级域名以及"www.abc.com"下有效
        // 参数secure：cookie是否只能通过https发送
        // 参数httponly：是否只能通过http协议访问cookie
        // 配置详见：https://www.php.net/manual/zh/function.setcookie.php
        setcookie("authorization","",$expires,'/',self::firstLevelDomain(),false,true);
    }

    /**
     * 删除cookie
     * 对于未前后分离的项目，当token过期或失效时，应立即清除Cookie
     *
     * @return void
     */
    static public function deleteCookie() {

        // 名称相同的cokie,只要域名不同，就可以同时存在，例如：域名sidoc.cn和www.sidoc.cn下可以有名称相同的cookie,两个cookie都起作用，为了确保安全，此处遍历清除所有cookie;
        // 清空当前域名下所有cookie
        foreach($_COOKIE as $key=>$value){
            setcookie($key,"",time()-3600,'/',self::firstLevelDomain());
            // setcookie("authorization","",time()-3600);
        }
    }

    // 获取一级域名
    static private function firstLevelDomain(){

        $httpHost = $_SERVER['HTTP_HOST'];
        if(filter_var($httpHost, FILTER_VALIDATE_IP)){ // 判断是否为ip
            // 如果是ip
            $domain = $httpHost;
        }else{
            // 如果是域名，则取顶级域名，以便兼容其下多级域名
            $arr = explode(".",$httpHost);
            $domain = $arr[count($arr)-2].".".$arr[count($arr)-1];
        }
        return $domain;
    }

}