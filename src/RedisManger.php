<?php

namespace sidoc;

use think\cache\driver\Redis;

// Redis封装类
class RedisManger{

   static protected $options = [
      'host'     => redis_host,
      'password' => redis_password,
      'port'     => 6379,
      'select'   => 0,      // 选择数据库
      'timeout'  => 0,      // 关闭时间 0:代表不关闭
      'expire'   => 0,
      'persistent' => false,
      'prefix'   => '',
   ];

   static public function instance($options = []){

      if (!extension_loaded('redis')) {   // 判断是否有扩展(如果apache没reids扩展就会抛出这个异常)
         throw new \BadFunctionCallException('not support: redis');
      }
      if (!empty($options)) {
         self::$options = array_merge(self::$options, $options);
      }
      $redis = new Redis(self::$options);

      // thinkphp对redis进行了封装，并暴露了一些简单的操作；此处不使用thinkphp封装的Redis,而是通过handler()获取原生Redis对象供外部操作
      return $redis->handler();
   }

}