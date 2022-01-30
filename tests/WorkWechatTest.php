<?php


require_once "../vendor/autoload.php";
require_once "./accountConfig_v1.0.php";

use sidoc\WorkWechat;

WorkWechat::reportException(new Exception("fdsaf"));
