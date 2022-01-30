<?php

namespace sidoc;

use Exception;
use think\facade\Log;

// 短信发送
class SmsTool {

    /**
     * 
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

    /**
     * 发送异常短信
     *
     * @param [type] $excptionNumber 异常数量
     * @return void
     */
    static public function sendException($excptionNumber) {

        $param['businessType'] = "exception_noti";  // 业务类型
        $param['data']     = [    // 邮件数据
            'excptionNumber' => $excptionNumber,
        ];      

        try{
            // RemoteCallTools::request("http://127.0.0.1:8001/common_web/tencenyun_sms_web/sendSmsService",$param);
            RemoteCallTools::request("https://www.sidoc.cn/common_web/tencenyun_sms_web/sendSmsService",$param);
        }catch (Exception $e){
            Log::error($e->__toString());
        }
    }

    /**
     * 发送Frp内网穿透过期提醒短信
     *
     * @param [type] $username
     * @param [type] $userEmail
     * @param [type] $protocolNumber
     * @param [type] $expirationTime
     * @return void
     */
    static public function sendFrpProtocolExpiredNoti($userId,$protocolNumber,$expirationTime) {
        
        $param['businessType'] = "frp_protocolExpiredNoti";  // 业务类型
        $param['data'] = [    // 邮件数据
            'userId'=>$userId,
            'protocolNumber'=>$protocolNumber,
            'expirationTime'=>$expirationTime
        ];      

        try{
            // RemoteCallTools::request("http://127.0.0.1:8001/common_web/tencenyun_sms_web/sendSmsService",$param);
            RemoteCallTools::request("https://www.sidoc.cn/common_web/tencenyun_sms_web/sendSmsService",$param);
        }catch (Exception $e){
            Log::error($e->__toString());
        }
    }

}