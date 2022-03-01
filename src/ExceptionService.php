<?php

namespace sidoc;

use Exception;
use think\facade\Env;
use think\facade\Log;

class ExceptionService{

    /**
    * 上报异常
    *
    * 示例：ExceptionService::report($title,$content.":".json_encode($e->getMessage(),JSON_UNESCAPED_UNICODE),__FILE__, __LINE__); 
    * @param [type] $title
    * @param [type] $content
    * @param [type] $file
    * @param [type] $line
    * @return void
    */
    // static public function report($title,$content,$file,$line){

    //     $arr['title']         = $title;
    //     $arr['content']       = $content;
    //     $arr['exceptionFile'] = $file;
    //     $arr['exceptionNum']  = $line;
    //     try{
    //         RemoteCallTools::request(Env::get('SIDOC_ADMIN_SERVICE')."/exception/report",$arr);
    //     }catch (Exception $e){
    //         Log::error($e->__toString());
    //     }
    // }

    /**
    * 上报异常
    *
    * 示例1：ExceptionService::record($exception); 
    * 示例2：ExceptionService::record($exception,"其它信息"); 
    * 
    * @param [type] $exception
    * @param string $otherMessage
    * @return void
    */
    // static public function report($exception){
        
    //     $arr['exception']    = $exception->__toString();
    //     $arr['code']         = $exception->getCode();
    //     $arr['message']      = $exception->getMessage();
    //     if(method_exists($exception,'getStatusCode')){
    //         $arr['http_code']    = $exception->getStatusCode();
    //     }
    //     try{
    //         RemoteCallTools::request(Env::get('SIDOC_ADMIN_SERVICE')."/exception/record",$arr);
    //     }catch (Exception $e){
    //         Log::error($e->__toString());
    //     }
    // }
}