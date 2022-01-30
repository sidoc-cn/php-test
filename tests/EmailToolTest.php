<?php


require_once "../vendor/autoload.php";
require_once "./AccountConfig.php";

use sidoc\EmailTool;

EmailTool::sendExceptionEmail("标题","内容",__FILE__, __LINE__);
// EmailTool::sendFrpNotiEmail("土木工程","512113110@qq.com",1,"2022年11月11日");