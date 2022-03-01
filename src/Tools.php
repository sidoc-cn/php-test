<?php

namespace sidoc;

class Tools{

    // 自定义密钥
    static $paramEncryptionKey = encryptionKey; 

    /**
        * 解密请求参数
        *
        * @param $p
        * @return string
        */
    static public function verifyParam($p){

        // 加密时使用urlencode()对数据编码，但解密时无须使用urldecode()解码，因为thinkphp自动对请求参数进行urldecode()

        $p = Tools::symmetryDecrypt($p,self::$paramEncryptionKey);
        $pContent = json_decode($p, true);
        return $pContent;
    }

    /**
        * 加密请求参数
        *
        * @param $p
        * @return string
        */
    static public function encryptionParam($p){
        return urlencode(Tools::symmetryEncrypt(json_encode($p,JSON_UNESCAPED_UNICODE),self::$paramEncryptionKey));
    }


    /**
        * 手机号：部分位数显示为*号
        *
        * @param $phone
        * @return string|string[]|null
        */
    static public function tel_hidden($phone){
        
        $IsWhat = preg_match('/(0[0-9]{2,3}[-]?[2-9][0-9]{6,7}[-]?[0-9]?)/i', $phone); //座机
        if ($IsWhat == 1) {
            return preg_replace('/(0[0-9]{2,3}[-]?[2-9])[0-9]{3,4}([0-9]{3}[-]?[0-9]?)/i', '$1****$2', $phone);
        } else {
            return preg_replace('/(1[35478]{1}[0-9])[0-9]{4}([0-9]{4})/i', '$1****$2', $phone);
        }
    }


    /**
        * 邮箱：部分位数显示为*号
        *
        * @param $phone
        * @return string
        */
    static public function mail_hidden($phone){

        $email_array = explode("@", $phone);
        $prevfix = (strlen($email_array[0]) < 4) ? "" : substr($phone, 0, 3); //邮箱前缀
        $count = 0;
        $str = preg_replace('/([\d\w+_-]{0,100})@/', '***@', $phone, -1, $count);
        $rs = $prevfix . $str;
        return $rs;
    }

    /**
        * 获取用户真实IP
        *
        * @return bool
        */
    static public function get_real_ip(){

        $ip=FALSE;

        //客户端IP 或 NONE
        if(!empty($_SERVER["HTTP_CLIENT_IP"])){
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        }

        // 多重代理服务器下的客户端真实IP地址（可能伪造）,如果没有使用代理，此字段为空

        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {

            $ips = explode (", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
            if ($ip) { array_unshift($ips, $ip); $ip = FALSE; }

            for ($i = 0; $i < count($ips); $i++) {

                if (!preg_match ("/^(10│172.16│192.168)./i", $ips[$i])) {
                    $ip = $ips[$i];
                    break;
                }
            }
        }
        // 客户端IP 或 (最后一个)代理服务器 IP
        return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
    }

    /**
        * 获取指定主机或域名的CNAME指向
        *
        * @param $hostname 主机名
        * @return string
        */
    static public function getHostnameCname($hostname){

        // 验证自定义域名是否已通过cname指向指定域名
        $dnsList = dns_get_record("sidoc.cn");  // 获取指定主机的DNS记录
        $target = "";                                     // 通过CNAME指向的域名
        foreach ($dnsList as $value){
            if($value['type'] == "CNAME"){
                $target = $value['target'];
            };
        }
        return $target;
    }


    /**
        * 对称加密：加密
        * @param $str    要加密的数据
        * @param string $key 密钥；使用该密钥对数据进行加密，解密时要使用相同密钥才能解密；
                            密钥长度根据加密方式有所不同，可以是128位、192位、256位；详见：https://www.cnblogs.com/fxyy/p/8868351.html
                            AES	          密钥长度 ( 位 )
                            AES-128	      128
                            AES-192	      192
                            AES-256	      256
    * @return string 加密后的数据，失败时返回false
    */
    static public function symmetryEncrypt($str,$key=null) {

        if($key == null){
            $key = self::$paramEncryptionKey;
        }

        // data    : 明文
        // method  : 加密算法
        // key     : 密钥
        // options : 0 : 自动对明文进行 padding, 返回的数据经过 base64 编码.
        //           1 : OPENSSL_RAW_DATA, 自动对明文进行 padding, 但返回的结果未经过 base64 编码.
        //           2 : OPENSSL_ZERO_PADDING, 自动对明文进行 0 填充, 返回的结果经过 base64 编码. 但是, openssl 不推荐 0 填充的方式, 即使选择此项也不会自动进行 padding, 仍需手动 padding.
        $data = openssl_encrypt($str, 'AES-128-ECB',$key, OPENSSL_RAW_DATA);
        $data = base64_encode($data);

        return $data;
    }

    /**
        * 对称加密：解密
        * @param string $str    要解密的数据
        * @param string $key    密钥，使用该密钥对数据进行解密
        * @return string        解密后的数据，失败时返回false
        */
    static public function symmetryDecrypt($str,$key=null) {

        if($key == null){
            $key = self::$paramEncryptionKey;
        }
        $decrypted = openssl_decrypt(base64_decode($str), 'AES-128-ECB',$key, OPENSSL_RAW_DATA);
        return $decrypted;
    }


    /**
        * 判断字符串是否以指定前缀开始
        *
        * @param $string
        * @param $startString
        * @return bool
        */
    static public function startsWith ($string, $startString){
        $len = strlen($startString);
        return (substr($string, 0, $len) === $startString);
    }

    /**
        * 根据key删除数组中指定元素
        *
        * @param $arr
        * @param $key
        * @return mixed
        */
    static public function array_remove_by_key($arr, $key){

        // 如果数组中不存在指定key
        if(!array_key_exists($key, $arr)){
            return $arr;
        }

        $keys = array_keys($arr);
        $index = array_search($key, $keys);
        if($index !== FALSE){
            array_splice($arr, $index, 1);
        }
        return $arr;
    }

}

