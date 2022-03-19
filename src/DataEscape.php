<?php

namespace sidoc;

// 数据转义
class DataEscape {

    // HTML字符串转义 - 转义后可存储于数据库中
    static public function htmlEncode($htmlText) {

        // addslashes()函数用于对输入字符串中的某些预定义字符前添加反斜杠，这样处理是为了数据库查询语句等的需要。
        // stripslashes()函数删除由 addslashes() 函数添加的反斜杠。该函数用于清理从数据库或HTML表单中取回的数据。(若是连续二个反斜杠，则去掉一个，保留一个；若只有一个反斜杠，就直接去掉。)

        // HTML文章保存时，使用addslashes()函数，所以显示时必须使用stripslashes()反转
        // 常规转义
        $temp = addslashes($htmlText);

        return base64_encode($$temp);
    }

    // HTML字符串反转义 - 反转后可用于前端显示
    static public function htmlDecode($htmlText) {
        $temp =  base64_decode($htmlText);

        // 常规转义
        return stripslashes($temp);
    }

}