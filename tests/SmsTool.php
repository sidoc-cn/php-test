<?php

use sidoc\SmsTool;

require_once "../vendor/autoload.php";
require_once "./AccountConfig.php";

// SmsTool::sendException(11);
SmsTool::sendFrpProtocolExpiredNoti(1,11,'2022年11月11日');

