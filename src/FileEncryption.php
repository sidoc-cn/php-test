<?php

namespace sidoc;

/**
 * 定义每个块应该从源文件中读取的块数
 * 对于“AES-128-CBC”，每个块由 16 个字节组成。
 * 因此，如果我们读取 10,000 个块，我们会将 160kb 加载到内存中；你可以调整这个值，以读/写更短或更长的块。
 */
define('FILE_ENCRYPTION_BLOCKS', 10000);

// 文件加密
class FileEncryption{

    /**
     * 加密文件，并将结果保存在一个以“.enc”为后缀的新文件中
     *
     * @param string $source 需要加密的文件路径
     * @param string $key 用于加密的密钥
     * @param string $dest 加密后文件应该写入的文件名
     * @return string|false 返回已创建的文件名，如果发生错误则返回 FALSE
     */
    function encryptFile($source, $key, $dest){

        $key = substr(sha1($key, true), 0, 16);
        $iv  = openssl_random_pseudo_bytes(16);

        $error = false;
        if ($fpOut = fopen($dest, 'w')) {
            // 将初始化向量放在文件的开头
            fwrite($fpOut, $iv);
            if ($fpIn = fopen($source, 'rb')) {
                while (!feof($fpIn)) {
                    $plaintext = fread($fpIn, 16 * FILE_ENCRYPTION_BLOCKS);
                    $ciphertext = openssl_encrypt($plaintext, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
                    // 使用密文的前 16 个字节作为下一个初始化向量
                    $iv = substr($ciphertext, 0, 16);
                    fwrite($fpOut, $ciphertext);
                }
                fclose($fpIn);
            } else {
                $error = true;
            }
            fclose($fpOut);
        } else {
            $error = true;
        }
        return $error ? false : $dest;
    }

    /**
     * 解密文件
     * 
     * 对传递的文件进行解密并将结果保存在一个新文件中，删除文件名的最后4个字符
     *
     * @param string $source 需要解密的文件路径
     * @param string $key 用于解密的密钥（必须与加密相同）
     * @param string $dest 解密后文件文件名。
     * @return string|false 返回已创建的文件名，如果发生错误则返回 FALSE
     */
    function decryptFile($source, $key, $dest)
    {
        $key = substr(sha1($key, true), 0, 16);

        $error = false;
        if ($fpOut = fopen($dest, 'w')) {
            if ($fpIn = fopen($source, 'rb')) {
                // 从文件开头获取初始化向量
                $iv = fread($fpIn, 16);
                while (!feof($fpIn)) {
                    $ciphertext = fread($fpIn, 16 * (FILE_ENCRYPTION_BLOCKS + 1)); // 我们必须多读一个块来解密而不是加密
                    $plaintext = openssl_decrypt($ciphertext, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
                    // 使用密文的前 16 个字节作为下一个初始化向量
                    $iv = substr($ciphertext, 0, 16);
                    fwrite($fpOut, $plaintext);
                }
                fclose($fpIn);
            } else {
                $error = true;
            }
            fclose($fpOut);
        } else {
            $error = true;
        }

        return $error ? false : $dest;
    }


}