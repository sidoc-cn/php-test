<?php

namespace sidoc;

use think\facade\Env;

// 数据转义
class DataEscape {

    // HTML字符串转义 - 转义后可存储于数据库中
    static public function htmlEncode($htmlText) {

        // addslashes()函数用于对输入字符串中的某些预定义字符前添加反斜杠，这样处理是为了数据库查询语句等的需要。
        // stripslashes()函数删除由 addslashes() 函数添加的反斜杠。该函数用于清理从数据库或HTML表单中取回的数据。(若是连续二个反斜杠，则去掉一个，保留一个；若只有一个反斜杠，就直接去掉。)

        // HTML文章保存时，使用addslashes()函数，所以显示时必须使用stripslashes()反转
        // 常规转义
        $temp = addslashes($htmlText);

        // 特殊字符转义
        $temp = str_ireplace("\n","\\n",$temp);
        $temp = str_ireplace("\r","\\r",$temp);
        $temp = str_ireplace("\t","\\t",$temp);

        return $temp;
    }

    // HTML字符串反转义 - 反转后可用于前端显示
    static public function htmlDecode($htmlText) {
 
        // 特殊字符反转
        $temp = str_ireplace("\\n","\n",$htmlText);
        $temp = str_ireplace("\\r","\r",$temp);
        $temp = str_ireplace("\\t","\t",$temp);

        // 常规转义
        $temp = stripslashes($temp);

        return $temp;
    }

}