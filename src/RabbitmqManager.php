<?php

namespace sidoc;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;
use Exception;
use think\facade\Log;

// RabbitMQ消息队列
class RabbitmqManager extends Command {


   // 0.0 配置命令行
   protected function configure(){
      // 定义一个名为叫RabbitmqManager的thinkphp命令
      $this->setName('startListening')
         ->addArgument('listeningType', Argument::OPTIONAL, '监听类型') // 配置参数
         ->setDescription('命令描述：用于开始监听消息队列');
   }

   // 0.1 在终端通过命令执行thinkphp命令时，会执行此函数
   //     执行此命令：进入目录执行 sudo php think startListening
   protected function execute(Input $input, Output $output){

      $listeningType  = $input->getArgument('listeningType');
      switch ($listeningType){
         case "test":{
               self::testListeningMessage();
               $output->writeln("测试队列监听已启动！");
               break;
         }
         default:{
               $output->writeln("监听类型错误");
               $output->writeln("你应该这样执行此命令：php think startListening [监听类型]");
               $output->writeln("");
               $output->writeln("常见监听类型如下：");
               $output->writeln("test - 监听消息测试任务");
               // ...

               $output->writeln("");
               return;
         }
      }

      // 控制台测试输出
      $output->writeln("已经开始监听消息队列");
   }

   // 0.2.0 测试 - 监听测试队列
   static public function testListeningMessage(){

      // 消息处理的逻辑回调函数
      $callback = function($msg) {

         // 注意：
         // 1>. 回调中必须捕获异常，否则会导致消息队列监听程序崩溃，从而无法断续监听消息
         // 2>. 重启消息队列客户端，回调中修改才能生效
         // 3>. 不要在回调访问数据库，因为极易发生异常：PDO::prepare(): MySQL server has gone away

         try{
               Log::info("消息队列消费者 - 配置Frp - 开始请求FrpServer...");

               $url = "http://test.com/admin/message_queue/receivedMessage";
               $result = file_get_contents($url);
               Log::info($result);

         }catch (Exception $e){
               // 注：不要在此处抛出异常，或访问数据库
               Log::error("消息队列发生错误：");
               Log::error($e->getMessage());

               // 尝试上报异常
               ExceptionService::report("消息队列发生错误",$e->getMessage(),__FILE__, __LINE__);
         }

         Log::info("");
         Log::info("--------------------------------------------------------------------------------------------");
         Log::info("");

         // 手动确认ack，确保消息已经处理
         $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
         Log::info("消息队列 - 消息准备确认完成！");

         // 发送带有“quit”字符串的消息，可以取消消费者。
         if ($msg->body === 'quit') {
               $msg->delivery_info['channel']->basic_cancel($msg->delivery_info['consumer_tag']);
         }
      };

      Log::info("消息队列 - 消费者准备监听Frp队列");
      self::listeningMessage("[".env."]-"."test_exchange","[".env."]-"."test_queue","[".env."]-"."test_queue_routing_key",$callback);
   }

   // 0.2.1 测试 - 发送测试消息
   static public function testSendMessage($messageBody){
      RabbitmqManager::produceMessage("[".env."]-"."test_exchange","[".env."]-"."test_queue","[".env."]-"."test_queue_routing_key",$messageBody);
   }


   static private $config = array(
      'host' => '49.233.93.111',
      'port' => 5672,
      'login' => '',
      'password' => ''
   );

   /**
    * 监听指定队列
    *
    * @param string $exchangeName  交换机名称
    * @param $queueName            队列名称
    * @param $routing_key          路由key
    * @param $callback             收到消息时的回调
    * @throws \ErrorException
    */
   static public function listeningMessage($exchangeName,$queueName,$routing_key,$callback){

      // 详见：https://learnku.com/articles/9117/rabbitmq-entry-work-queue
      //      https://www.vckai.com/xiao-xi-dui-lie-rabbitmq-san-phpde-shi-yong-shi-li

      // 创建RabbitMQ连接和chanel通道
      $connection = new AMQPStreamConnection(self::$config['host'], self::$config['port'], self::$config['login'], self::$config['password'],
         $vhost = '/',
         $insist = false,
         $login_method = 'AMQPLAIN',
         $login_response = null,
         $locale = 'en_US',
         $connection_timeout = 10.0, // 连接超时时间,如果在设定时间内还没有完成连接操作，则抛出异常
         $read_write_timeout = 10.0, // 读写超时,如果在设定的时间内还没有完成读写操作,则抛出异常，读写超时时间必须小于心跳的2倍
         $context = null,
         $keepalive = true,   // 是否长连接
         $heartbeat = 60,      // 心跳时间间隔，为0表示禁用心跳
         $channel_rpc_timeout = 0.0);
      $channel = $connection->channel();


      // 设置消费者（Consumer）客户端同时只处理一条队列
      // 这样是告诉RabbitMQ，再同一时刻，不要发送超过1条消息给一个消费者（Consumer），直到它已经处理了上一条消息并且作出了响应。这样，RabbitMQ就会把消息分发给下一个空闲的消费者（Consumer）。
      $channel->basic_qos(0, 1, false);

      // 创建路由和队列，以及绑定路由队列，注意要跟publisher的一致
      // 这里其实可以不用创建，但是为了防止队列没有被创建所以做的容错处理
      self::createAndBindProcess($channel,$exchangeName,$queueName,$routing_key);

      /**
      * queue: hello               // 被消费的队列名称
      * consumer_tag: consumer_tag // 消费者客户端身份标识，用于区分多个客户端
      * no_local: false            // 这个功能属于AMQP的标准，但是RabbitMQ并没有做实现
      * no_ack: true               // 收到消息后，是否不需要回复确认；为true是不需要回复确认，为false时则需要回复确认
      * exclusive: false           // 是否排他，即这个队列只能由一个消费者消费。适用于任务不允许进行并发处理的情况下；若此消费者已被启用，再次启动时，会报错：ACCESS_REFUSED - queue 'frp_queue' in vhost '/' in exclusive use
      * nowait: false              // 不返回执行结果，但是如果排他开启的话，则必须需要等待结果的，如果两个一起开就会报错
      * callback: $callback        // 回调逻辑处理函数
      */
      $channel->basic_consume($queueName, env, false, false, true, false, $callback);

      // 注册一个会在php中止时执行的函数
      register_shutdown_function(array('sidoc\RabbitmqManager','shutdown'), $channel, $connection);

      // 阻塞队列监听事件
      while(count($channel->callbacks)) {
         $channel->wait();
      }
   }

   /**
    * 发布消息
    *
    * @param string $exchangeName  交换机名称
    * @param $queueName            队列名称
    * @param $routing_key          路由key
    * @throws \Exception
    */
   static public function produceMessage($exchangeName,$queueName,$routing_key,$messageBody){

      // 详见：https://learnku.com/articles/9117/rabbitmq-entry-work-queue

      // 创建RabbitMQ连接和chanel通道
      $connection = new AMQPStreamConnection(self::$config['host'], self::$config['port'], self::$config['login'], self::$config['password']);
      $channel = $connection->channel();

      /*
      如果将Queue的持久化标识durable设置为true，则表示Queue是一个持久化队列；Queue会被存放在硬盘上，当服务重启、关机等情况发生时，Queue都不会丢失。
      如果Queue的durable设为false，则表示Queue是内存队列，当发生服务重启等事件时，Queue会丢失。

      队列可以被持久化，但队列中的消息是否为持久化，还要看消息本身的持久化设置。也就是说，服务重启后，之前那个Queue里面未发出去的消息是否还存在，就取
      决于发送消息时对消息的设置了。

      上面阐述了队列和消息的持久化；如果不设置Exchange的持久化，对消息的可靠性来说没有什么影响；但Exchange不设置持久化，当Broker服务重启之后，
      Exchange将不复存在，此时发送方RabbitMQ Producer就无法正常发送消息了。
      */

      // 创建路由和队列，以及绑定路由队列
      self::createAndBindProcess($channel,$exchangeName,$queueName,$routing_key);

      /**
      * 创建AMQP消息类型
      * delivery_mode 消息是否持久化
      * AMQPMessage::DELIVERY_MODE_NON_PERSISTENT  不持久化
      * AMQPMessage::DELIVERY_MODE_PERSISTENT      持久化
      */
      $msg = new AMQPMessage($messageBody, ['delivery_mode' => AMQPMessage:: DELIVERY_MODE_PERSISTENT]);

      /**
      * 发送消息
      * msg: $msg                // AMQP消息内容
      * exchange: vckai_exchange // 交换机名称
      * routing_key
      */
      $channel->basic_publish($msg,$exchangeName,$routing_key);

      // Close（关闭链接）
      $channel->close();
      $connection->close();
   }

   // Private- 创建路由和队列，以及绑定路由队列
   static private function createAndBindProcess($channel,$exchangeName,$queueName,$routing_key){

      /**
      * 创建队列(Queue)
      * name: hello         // 队列名称
      * passive: false      // 如果队列不存在时，应该如何处理；为true时，存在则返回OK，否则就抛异常；为false时，存在返回OK，不存在则自动创建；
      * durable: true       // 是否持久化，为false时，消息存放到内存中的，RabbitMQ重启后消息会丢失
      * exclusive: false    // 是否排他，指定该选项为true则队列只对当前连接有效，连接断开后自动删除
      * auto_delete: false  // 是否自动删除，当已经没有消费者时是否自动被删除队列
      */
      $channel->queue_declare($queueName, false, true, false, false);

      /**
      * 创建交换机(Exchange)
      * name: vckai_exchange// 交换机名称
      * type: direct        // 交换机类型，分别为direct/fanout/topic，参考另外文章的Exchange Type说明。
      * passive: false      // 如果交换机不存在时，应该如何处理；为true时，存在则返回OK，否则就抛异常；为false时，存在返回OK，不存在则自动创建；
      * durable: false      // 是否持久化，设置false是存放到内存中的，RabbitMQ重启后会丢失
      * auto_delete: false  // 是否自动删除，当最后一个消费者断开连接之后交换机是否被自动删除
      */
      $channel->exchange_declare($exchangeName, AMQPExchangeType::DIRECT, false, true, false);

      // 绑定消息交换机和队列
      $channel->queue_bind($queueName,$exchangeName,$routing_key);

   }

   // Private - 关闭连接
   static private function shutdown($channel, $connection){
      $channel->close();
      $connection->close();
   }


}