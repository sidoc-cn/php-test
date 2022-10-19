<?php

namespace sidoc;

// 服务器性能监控
class PerformanceMonitoring{

    /**
     * Nginx性能监控
     *
     * @return void
     */
    static public function nginx(){
        /*

        1.0> 在 nginx.conf 添加如下配置即可开始性能监控
        location = /basic_status {
            stub_status;
        }

        2.0> 获取性能信息
        curl 127.0.0.1/basic_status

        3.0> 性能结果数据解释
        Active connections 当前活动的连接数量（包括等待的）
        accepts 已接收的连接总数
        handled 已处理的连接总数
        requests 当前的请求总数
        Reading nginx正在读取的连接数量
        Writing nginx正在响应的连接数量
        Waiting 当前空闲的连接数量
        */

        $result = shell_exec('curl 127.0.0.1/basic_status'); 
        return $result;
    }


    /**
     * php-fpm性能监控
     *
     * @return void
     */
    static public function phpFpm(){

        // 获取php-fpm性能状态信息
        $status = shell_exec('systemctl status php-fpm'); 
        return $status;
    }

    /**
     * php-fpm性能监控 - 读取请求慢日志
     *
     * @return void
     */
    static public function phpFpmSlowLog($date){
        // 读取指定日期的慢请求日志
        $result = file_get_contents('/var/log/php-fpm/www-slow.log'.$date); 
        return $result;
    }

    /**
     * 数据库性能监控
     *
     * @return void
     */
    static public function mysql(){
        // 查询慢查询情况
        // SHOW VARIABLES LIKE '%slow%';
        // SHOW GLOBAL STATUS LIKE '%slow%';
    }

    /**
     * 系统性能监控
     *
     * @return void
     */
    static public function sys(){

        $result = getrusage();
        echo $result;
    }
}