<?php

use sidoc\RedisManger;
require_once "../vendor/autoload.php";
require_once "./AccountConfig.php";


$redis = RedisManger::instance(['select'=>0]);
$f = $redis->set('user_dddd',111);
$f = $redis->get('user_dddd');
echo $f;
