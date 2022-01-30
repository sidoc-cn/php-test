<?php

namespace sidoc;

use think\console\Command;
use think\console\Input;
use think\console\Output;

// 自定义thinkphp命令，此命令必须先在application/command.php中配置； 详见：https://www.kancloud.cn/manual/thinkphp5/235129
class CrontabTimer extends Command{

   protected function configure(){
      // 定义一个名为叫CrontabTimer的命令
      $this->setName('CrontabTimer')->setDescription('命令描述：用于定时器调用');
   }

   protected function execute(Input $input, Output $output){
      // 控制台测试输出
      // $output->writeln("test output");

      // 发送邮件需要一些请求参数，此处直接调用没有请求参数，因此模拟一些参数
      $_SERVER['SERVER_PORT'] = 443;
      $_SERVER['HTTP_HOST'] = 'www.sidoc.cn';
      
      ExceptionService::report("[".env."]定时任务","定时任务",__FILE__, __LINE__);
   }
}