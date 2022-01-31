<?php

namespace sidoc;

class LogTools{

    /**
        * 获取当天日志列表
        *
        * @return string
        */
    static public function logList(){
        
        $logPath = runtime_path()."/log/";

        // 日志目录不存在
        if(!file_exists($logPath)){
            return null;
        }

        // 获取所有月份的日志
        // scandir用于列出指定路径中的文件和目录
        $allLogList = scandir($logPath);
        if(count($allLogList) <= 0){
            return null;
        }

        // 获取最后一天的日志
        $lastMonthLog = end($allLogList);
        $dayLogList = scandir($logPath.$lastMonthLog,SCANDIR_SORT_NONE);
        if(count($dayLogList) <= 0){
            return null;
        }

        $arr = array();
        foreach ($dayLogList as $value){
            // 仅返回命令行和错误日志
            if(strpos($value,'error') !== false || strpos($value,'cli') !== false){
                array_push($arr,$value);
            }
        }
        // 过滤后的日志列表为空
        if(count($arr) <= 0){
            return null;
        }

        $result['logList'] = $arr;
        $result['logFolder'] = $logPath.$lastMonthLog;
        return $result;
    }


    /**
        * 获取日志或文件内容
        * @param $path
        * @return bool|string
        */
    static public function logContent($path){

        // 路径不存在
        if(!file_exists($path)){
            return "";
        }

        // 文件内容为空
        if(filesize ($path) <= 0){
            return "";
        }

        $handle = fopen($path, "r");// 读取二进制文件时，需要将第二个参数设置成'rb'

        // 通过filesize获得文件大小，将整个文件一下子读到一个字符串中
        $contents = fread($handle, filesize ($path));
        fclose($handle);
        return $contents;
    }
}
