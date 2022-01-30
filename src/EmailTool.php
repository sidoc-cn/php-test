<?php

namespace sidoc;

use Exception;
use think\facade\Log;

// 邮箱发送
class EmailTool {

    /**
     * 发送异常邮件
     * 
     * 示例：EmailTool::sendExceptionEmail("业务模块名称","异常标题","异常内容",__FILE__, __LINE__);
     *
     * @param [type] $subject 业务模块
     * @param [type] $title   异常名称
     * @param [type] $content 异常内容描述
     * @param [type] $file    异常文件名称
     * @param [type] $line    异常发生行数
     * @return void
     */
    static public function sendExceptionEmail($title,$content,$file,$line) {

        $param['template'] = "exceptionEmail";    // 邮件模板
        $param['business'] = "exception";         // 业务模块，如：frp、emialVerify、retrievePassword、exception
        $param['email']    = "512113110@qq.com";  // 邮箱地址
        $param['subject']  = "系统发生异常，请尽快处理！";  // 邮件名称
        $param['data']     = [    // 邮件数据
            'title'   => $title,
            'content' => $content,
            'file'    => $file,
            'line'    => $line
        ];      

        try{
            RemoteCallTools::request("https://www.sidoc.cn/common_web/email_web/sendEmailService",$param);
        }catch (Exception $e){
            Log::error($e->__toString());
        }
    }

    /**
     * 发送Frp内网穿透过期提醒邮箱
     *
     * @param [type] $username
     * @param [type] $userEmail
     * @param [type] $protocolNumber
     * @param [type] $expirationTime
     * @return void
     */
    static public function sendFrpNotiEmail($username,$userEmail,$protocolNumber,$expirationTime) {

        $param['template'] = "frpProtocolExpiredNoti"; // 邮件模板
        $param['business'] = "frp";               // 业务模块，如：frp、emialVerify、retrievePassword、exception
        $param['email']    = $userEmail;          // 邮箱地址
        $param['subject']  = "内网穿透连接过期提醒"; // 邮件名称
        $param['data']     = [    // 邮件数据
            'username'        => $username,
            'protocol_number' => $protocolNumber,
            'expiration_time' => $expirationTime
        ];

        try{
            RemoteCallTools::request("https://www.sidoc.cn/common_web/email_web/sendEmailService",$param);
        }catch (Exception $e){
            Log::error($e->__toString());
        }
    }

}