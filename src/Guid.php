<?php

namespace sidoc;


// 三段
// 一段是微秒 一段是地址 一段是随机数
class Guid {

    /**
     *  生成UUID
     *
     * @param string $prefix 自定义前缀
     * @return string
     */
    static  function guidString($prefix = "") {

        // uniqid(prefix,more_entropy): 基于以微秒计的当前时间，生成一个唯一的 ID
        //        prefix	    可选。为ID规定前缀。如果两个脚本恰好在相同的微秒生成 ID，该参数很有用。
        //        more_entropy	可选。规定位于返回值末尾的更多的熵。

        // strtoupper()：将字符串转换为大写。

        $charid = md5(uniqid(mt_rand(), true));
        $uuid = $prefix.substr($charid, 0, 8) .substr($charid, 8, 4) .substr($charid,12, 4) .substr($charid,16, 4) .substr($charid,20,12);
        return $uuid;
    }


}