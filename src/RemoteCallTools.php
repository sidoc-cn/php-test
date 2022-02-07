<?php

namespace sidoc;


// 远程调用
class RemoteCallTools{

    /**
    * 数据打包
    *
    * @param $data
    * @return array|string
    */
    static public function dataPackaging($data){

        // 加密参数
        $aData = ["t"=>time(), "data"=>$data];
        $aData = Tools::encryptionParam($aData);
        return $aData;
    }

    /**
    * 数据解析
    *
    * 远程请求时，必须开启数据有效时间验证，否则请求被若被拦截，将对服务器安全造成威胁；
    * 消息队列接收数据时，请关闭数据有效时间验证；否则队列中被阻塞的消息将无法通过时间验证；
    *
    * @param $data
    * @param bool $isVerificationTime 解析数据时，是否验证数据有效时间
    * @return mixed
    * @throws \Exception
    */
    static public function dataParse($data=null,$isVerificationTime=true){
        
        // 默认获取请求参数中的p
        if($data == null){
            $data = request()->param("p");    
        }

        if(empty($data)){
            // 此处不能抛出异常，以便外部能够兼容处理其它业务逻辑
            // throw new \Exception('参数不能为空');  
            return null;
        }

        // 解密并获取参数内容
        // PHP会判断请求中的某些头，并自动对参数进行urldecode；此处手动进行urldecode，是因为通过消息队列传递的参数不会被自动urldecode；
        // 并且当前类中通过PHP发送的远程请求，PHP也不会被自动urldecode；
        $pContent = Tools::verifyParam(urldecode($data));
        if(empty($pContent)){
            throw new \Exception("参数解析失败",IGNORED_EXCEPTION_CODE_100000);
        }

        // 数据有效性验证
        // 若数据包与当前时间的时差超过60秒，则认为数据已失效(此处非常容易报错，因为复杂业务在执行时可会使数据超时)
        if($isVerificationTime && abs(time() - $pContent['t']) > 60){
            throw new \Exception('数据无效');
        }

        return $pContent['data'];
    }


    /**
    * 远程请求
    *
    * @param $uri             请求URI
    * @param string $param    请求参数
    * @param string $headers  请求头
    * @param string $requestMethod 请求方式，推荐post请求；若是get请求请自行拼接参数
    * @return mixed
    * @throws \Exception
    */
    static public function request($path,$param=[],$headers=[],$requestMethod="post"){

        // curl详见：https://learnku.com/articles/30327

        // 初始化 curl
        $ch = curl_init();

        // 是否输出响应头信息；此处不输出响应头，否则响应头会和返回数据混在一起，导致返回数据很难被取出
        curl_setopt($ch, CURLOPT_HEADER, false);

        // 将请求结果以字符串方式返回，而非返回true或false
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // 超时时间
        curl_setopt($ch, CURLOPT_TIMEOUT,14);

        // 设置请求头
        $headers = array_merge(array(
            "request-source:'remote-call-tool'"
        ),$headers);
        curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);

        // 采用https协议，一定要加入以下两句
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 是否对认证证书来源进行检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在

        // 若给定url自动跳转到新的url,有了下面参数可自动获取新url内容：302跳转
        // curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

        // 打包、加密参数
        $param = self::dataPackaging($param);

        // 构建请求URL
        $path = self::baseUrl($path).$path;

        if($requestMethod == 'post'){
            curl_setopt($ch, CURLOPT_POST, true);         // 请求方式
            curl_setopt($ch, CURLOPT_POSTFIELDS,array('p'=>$param)); // post请求数据

            // 设置请求地址
            curl_setopt($ch, CURLOPT_URL,$path);

        }else{
            // 设置请求地址
            curl_setopt($ch, CURLOPT_URL,$path."?p=".$param);
        }

        // 执行请求
        $responseContent = curl_exec($ch);

        // 关闭URL请求
        curl_close($ch);

        return Tools::verifyParam(urldecode($responseContent))['data'];
    }


    // 获取环境信息
    private static function baseUrl($path){

        // 若路径本身就有基地址，则直接返回
        if(substr($path,0,4) == "http"){
            return "";
        }

        if(env === 'online') {               // 线上生产环境 ++++++++++++++++++++++++++++++++++++++++++++++++++
            return "https://www.sidoc.cn/";
        }else{
            return "http://localhost/";
        }
    }


    // 上传文件
    public static function uploadFile($url,$param){

        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $url );          // 设置请求地址
        curl_setopt ( $ch, CURLOPT_POST, 1 );            // post方式
        curl_setopt ( $ch, CURLOPT_HEADER, 0 );          // 是否输出响应头信息；
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );  // 将请求结果以字符串方式返回
        curl_setopt ( $ch, CURLOPT_POSTFIELDS,$param ); // 设置参数
        
        // 执行请求
        $responseContent = curl_exec($ch);

        // 关闭URL请求
        curl_close($ch);

        return $responseContent;
    }


}