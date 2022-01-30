<?php

namespace sidoc;

// 上传文件处理
class UploadFile{

    /**
        * 保存文件到临时目录,支持指保存
        *
        * @return 返回文件路径数组
        */
    public static function saveFileToTempDic($user_id){

        $guid = Guid::guidString();
        $tempFolder = PUBLIC_PATH.'static'.DIRECTORY_SEPARATOR.'common'.DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR;  // 文件存储的临时目录
        if (!file_exists($tempFolder.$user_id)){ // 目录不存在，则创建
            if(!(FileUtil::createDir($tempFolder.$user_id))){
                return json_encode(array("errorMsg"=>"文件上传失败，请刷新页面后重试！"));
            }
        }

        $filePathArray = array();
        foreach($_FILES as $key=>$value) {

            $fileNameArray =  explode('.',$_FILES[$key]["name"]);
            $filePostfix = $fileNameArray[count($fileNameArray)-1];  // 获取文件后缀

            if($_FILES[$key]["type"] == 'image/png'){
                $filePostfix = 'png';
            }

            $filePathName = $tempFolder . $user_id . DIRECTORY_SEPARATOR . $guid . '.' . $filePostfix; // 文件存储位置

            if($_FILES[$key]["size"] == 0){
                continue;
            }

            if(move_uploaded_file($_FILES[$key]["tmp_name"], $filePathName)){

                $filePathArray[$key] = $filePathName;

            }else{ // 文件移动失败
                throw new \think\exception\HttpException(500,"文件移动失败");
            }
        }
        return $filePathArray;
    }


    /**
        * 根据路径删除临时文件
        */
    public static function delTempFile($filePath){

    }

}
