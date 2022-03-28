<?php

namespace sidoc;

use Gregwar\Captcha\CaptchaBuilder;

// 验证码封装(包括图形验证、行为验证等)
class CaptchaTool {

    /**
     * 生成并输出图形验证码
     * 此函数同时返回验证码校验key，客户端需要提交此key以便服务端从redis中读取验证码，并进行校验
     * 
     * 官网：https://github.com/Gregwar/Captcha
     *
     * @return void
     */
    static public function image() {

        $builder = new CaptchaBuilder();
        // 启用或禁用插值（默认启用），禁用会提交效率，但图像会更丑；
        $builder->setInterpolation(false); 

        // 构建验证码
        $e = $builder->build();
        // 输出验证码图片
        $builder->output();

        // 保存验证码文本,5分钟后自动销毁
        $key = "captcha_".Guid::guidString(Tools::get_real_ip());
        $redis = RedisManger::instance(['select'=>1]);
        $redis->setex($key,300,$e->getPhrase());
        return $key;
    }

    /**
     * 图片验证码校验
     *
     * @param [type] $key  用于读取验证码的key
     * @param [type] $code 用户输入的验证码
     * @return void
     */
    static public function imageVerify($key,$code) {
        
        $redis = RedisManger::instance(['select'=>1]);
        $captchaCode = $redis->get($key);
        if(empty($captchaCode)){
            return false;
        }

        $captchaCode = strtolower($captchaCode); // 转小写
        if($captchaCode === strtolower($code)){
            return true;
        }
        return false;
    }




    /**
     * 行为验证
     *
     * @return void
     */
    static public function behavior(){
        # code...
    }

}