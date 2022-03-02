<?php

require_once "../vendor/autoload.php";
// require_once "./AccountConfig.php";

use sidoc\DatabaseBackup;

DatabaseBackup::backup("fds","/fs");

// AliyuncOss::