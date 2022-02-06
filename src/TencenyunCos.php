<?php

namespace sidoc;

use Qcloud\Cos\Client;

/*
 * 腾迅云COS
 * */
class TencenyunCos{

    /**
    * 从COS的user bucket中删除文件
    *
    * @param $user_id  用户ID
    * @param $filePath 文件路径
    * @return bool|\Exception
    * @throws \Exception
    */
    static function deleteFileFromCosUser($user_id,$filePath){

        if(empty($user_id)){
            throw new \Exception("参数错误");
        }

        // 验证删除路径，防止误删；目前只允许程序删除 {user_id}/avatar 路径下的文件
        // 判断路径前缀
        if(!Tools::startsWith($filePath,"user/".$user_id."/")){
            return false;
        }

        $cosClient = new Client(array(
            'region' => 'ap-shanghai', #地域，如ap-guangzhou,ap-beijing-1
            'credentials' => array(
                'secretId' => tencenyun_cos_id,
                'secretKey' => tencenyun_cos_key,
            ),
        ));

        // 对象键（Key）是对象在存储桶中的唯一标识。例如，对象的访问域名user.sidoc.cn/doc1/pic1.jpg 中，对象键为 doc1/pic1.jpg；即也是文件在cos中的文件路径
        $key = $filePath;
        try {
            // 删除 COS 对象
            $result = $cosClient->deleteObject(array(
                'Bucket' => 'user-10051824', // bucket 的命名规则为{bucketName}-{appid}
                'Key' => $key
            ));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
        * 从COS的sidoc bucket中删除文件或目录
        *
        * @param $business_type 业务类型：doc、article
        * @param $business_id   业务ID，文档或文章的ID
        * @param $filePath      欲删除的文件路径，仅在业务类型为doc时有效
        * @return bool|\Exception
        * @throws \Exception
        */
    static function deleteFileFromCosBlog($business_type,$business_id,$filePath){

        if(empty($business_id)){
            throw new \Exception("参数错误");
        }
        switch ($business_type){
            case "doc":{

                // 验证删除路径，防止误删
                // 判断路径前缀
                if(!Tools::startsWith($filePath,"doc/".$business_id."/")){
                    return false;
                }

                // 如果$filePath是目录，则先删除该目录下的所有文件，然后再删除目录
                if($filePath == "doc/".$business_id."/"){
                    // 删除目录下的所有文件,包含目录本身，目录必须以"/"结尾
                    self::batchDeleteFile($filePath);
                    return false;
                }

                break;
            }
            case "article":{
                // 彻底删除文章时，此函数才会被调用，用于删除文章中引用的所有资源

                // 如果$filePath是目录，则先删除该目录下的所有文件，然后再删除目录
                if($filePath == "article/".$business_id."/"){
                    // 删除目录下的所有文件,包含目录本身，目录必须以"/"结尾
                    self::batchDeleteFile($filePath);
                    return false;
                }

                break;
            }
            default:{
                throw new \Exception("参数错误");
            }
        }

        $cosClient = new Client(array(
            'region' => 'ap-shanghai', #地域，如ap-guangzhou,ap-beijing-1
            'credentials' => array(
                'secretId' => tencenyun_cos_id,
                'secretKey' => tencenyun_cos_key,
            ),
        ));

        // 对象键（Key）是对象在存储桶中的唯一标识。例如，对象的访问域名user.sidoc.cn/doc1/pic1.jpg 中，对象键为 doc1/pic1.jpg；即也是文件在cos中的文件路径
        $key = $filePath;
        try {
            // 删除 COS 对象
            $result = $cosClient->deleteObject(array(
                'Bucket' => 'sidoc-10051824', // bucket 的命名规则为{bucketName}-{appid}
                'Key' => $key
            ));

        } catch (\Exception $e) {
            throw $e;
        }
    }

    // 批量删除文件
    static private function batchDeleteFile($folderPath){

        $cosClient = new Client(array(
            'region' => 'ap-shanghai', #地域，如ap-guangzhou,ap-beijing-1
            'credentials' => array(
                'secretId' => tencenyun_cos_id,
                'secretKey' => tencenyun_cos_key,
            ),
        ));

        // 对象键（Key）是对象在存储桶中的唯一标识。例如，对象的访问域名user.sidoc.cn/doc1/pic1.jpg 中，对象键为 doc1/pic1.jpg；即也是文件在cos中的文件路径
        try {

            // 根据文件夹路径，获取其下所有文件，包括文件夹本身
            // 例如，参数Prefix(前缀)为 doc/ ,则会获取到：doc/、doc/1.png、doc/1.png、...
            $result = $cosClient->listObjects(array(
                'Bucket' => 'sidoc-10051824', // bucket 的命名规则为{bucketName}-{appid}
                'Prefix' => $folderPath
            ));
            // print_r($result);

            if(!empty($result['Contents'] )){

                $arr = array();
                foreach ($result['Contents'] as $value){
                    array_push($arr,["Key"=>$value["Key"]]); // 添加元素
                }

                // 删除多个文件
                $result = $cosClient->deleteObjects(array(
                    'Bucket' => 'sidoc-10051824', //格式：BucketName-APPID
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

        $cosClient = new Client(array(
            'region' => 'ap-shanghai', #地域，如ap-guangzhou,ap-beijing-1
            'credentials' => array(
                'secretId' => tencenyun_cos_id,
                'secretKey' => tencenyun_cos_key,
            ),
        ));

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
            $result = $cosClient->putObject(array(
                'Bucket' => 'user-10051824', // bucket 的命名规则为{bucketName}-{appid}
                'Key' => $toPath,
                'Body' => $file,
            ));
            // 请求成功,print_r打印的内容会被添加http请求结果后，因此此处必须注释
            // print_r($result);

        } catch (\Exception $e) {
            throw $e;
        }

    }
    
}