<?php

namespace sidoc;

use Gregwar\Captcha\CaptchaBuilder;

// 验证码封装(包括图形验证、行为验证等)
class CaptchaTool {

    /**
     * 生成并输出图形验证码，同时返回验证码文本
     * 
     * 官网：https://github.com/Gregwar/Captcha
     *
     * @return void
     */
    static  function image() {

        // 创建并配置图形验证码对象
        $builder = new CaptchaBuilder();
        $builder->setInterpolation(false); // 启用或禁用插值（默认启用），禁用会提交效率，但图像会更丑；

        // 构建验证码
        $e = $builder->build();
        // 输出验证码图片
        $builder->output();

        return $e->getPhrase(); // 获取验证码
    }

}