<?php

namespace sidoc;

class SyncLock {

   /**
    * 同步阻塞
    * 此函数用于阻塞程序继续向下执行，直至同步锁解除
    */
   static public function lock($dataBaseIndex=1,$token="default-token"){

      $redis = RedisManger::instance(['select'=>$dataBaseIndex]);

      // 此处使用Redis提供的原生原子命令setNX实现加锁功能
      // setNX命令：若key不存在才能设置值成功，并返回true；若key已存在，则设置失败并返回false;
      // 使用原子命令setNx向Redis中保存一个key/value作为同步锁标志，若锁标志已存在表明已上锁，若不存在表明锁已解除。

      // 若setnx返回false,说明同步锁标志已存在，则代码继续循环阻塞，否则代码跳出while循环继续向下执行；
      while ($redis->setnx('sysLock-'.$token,1) === false){
         sleep(0.5);  // 暂时执行0.5秒，以控制循环频次
      }
   }

   /**
    * 删除同步锁
    */
   static public function unlock($dataBaseIndex=1,$token="default-token"){
      $redis = RedisManger::instance(['select'=>$dataBaseIndex]);
      $redis->del('sysLock-'.$token);
   }

}