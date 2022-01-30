<?php

use sidoc\SyncLock;
require_once "../vendor/autoload.php";
require_once "./AccountConfig.php";



SyncLock::lock(1,"1111");

// SyncLock::unlock(1,"1111");