<?php

namespace sidoc;

use Qcloud\Cos\Client;
use think\facade\Env;
use Throwable;

/*
 * 腾迅云COS
 * */
class TencenyunCos{

    // 实例化COS
    static private function instanceClient(){

        if(Env::get('APP_DEBUG')){
            $bucket = 'static-test-1251340588';
            $region = 'ap-nanjing';
        }else{
            $bucket = 'static-10051824';
            $region = 'ap-shanghai';
        }

        $cosClient = new Client(array(
            'region' => $region, #地域，如ap-guangzhou,ap-beijing-1
            'schema' => 'https', //协议头部，默认为http
            'credentials' => array(
                'secretId' => tencenyun_cos_id,
                'secretKey' => tencenyun_cos_key,
            ),
        ));
        return [ 'bucket'=>$bucket, 'client'=>$cosClient ];
    }


    /**
     * 上传文件
     *
     * @param [type] $path
     * @param [type] $file
     * @return void
     */
    static public function pushObject($path,$filePath){

        $cos = self::instanceClient();
        $bucket = $cos['bucket'];
        $client = $cos['client'];
        
        try {
            $result = $client->upload(
                $bucket,  // 存储桶名称，由BucketName-Appid 组成，可以在COS控制台查看 https://console.cloud.tencent.com/cos5/bucket
                $path,    // 此处的 key 为对象键
                fopen($filePath, 'rb')
            );
            // 请求成功
            print_r($result);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * 删除文件或目录
     *
     * @param [type] $path
     * @return void
     */
    static public function deleteObject($path){

        $cos = self::instanceClient();
        $bucket = $cos['bucket'];
        $client = $cos['client'];

        // 对象键（Key）是对象在存储桶中的唯一标识。例如，对象的访问域名user.sidoc.cn/doc1/pic1.jpg 中，对象键为 doc1/pic1.jpg；即也是文件在cos中的文件路径
        try {

            // 根据文件夹路径，获取其下所有文件，包括文件夹本身
            // 例如，参数Prefix(前缀)为 doc/ ,则会获取到：doc/、doc/1.png、doc/1.png、...
            $result = $client->listObjects(array('Bucket' => $bucket,'Prefix' => $path));

            if(!empty($result['Contents'])){

                $arr = array();
                foreach ($result['Contents'] as $value){
                    array_push($arr,["Key"=>$value["Key"]]); // 添加元素
                }

                // 删除多个文件
                $result = $client->deleteObjects(array(
                    'Bucket' => $bucket, // 格式：BucketName-APPID
                    'Objects' =>$arr
                ));
                // print_r($result);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }


    // 保存图片 - 通过链接
    static public function saveFileByLink($link,$toPath){

        $cos = self::instanceClient();
        $bucket = $cos['bucket'];
        $client = $cos['client'];

        // 对象键（Key）是对象在存储桶中的唯一标识。例如，对象的访问域名user.sidoc.cn/doc1/pic1.jpg 中，对象键为 doc1/pic1.jpg；即也是文件在cos中的文件路径
        try {

            // 下载文件
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $link);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            $file = curl_exec($ch);
            curl_close($ch);

            // 保存至对象存储
            // self::clien
            
            // 请求成功,print_r打印的内容会被添加http请求结果后，因此此处必须注释
            // print_r($result);
        }catch(Throwable $e){
            throw $e;
        }

    }
    
}