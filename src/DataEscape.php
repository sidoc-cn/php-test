<?php

namespace sidoc;

// 数据转义
class DataEscape {

    // HTML字符串转义 - 转义后可存储于数据库中
    static public function htmlEncode($htmlText) {
        return base64_encode($htmlText);
    }

    // HTML字符串反转义 - 反转后可用于前端显示
    static public function htmlDecode($htmlText) {
        return base64_decode($htmlText);
    }

}