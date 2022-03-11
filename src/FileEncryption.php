<?php

namespace sidoc;

// 参考自：https://riptutorial.com/php/example/25499/symmetric-encryption-and-decryption-of-large-files-with-openssl

/**
 * 定义每次从源文件中读取的块数，对于“AES-128-CBC”，每个块由 16 个字节组成，
 * 因此，如果我们读取 10000 个块，我们会将 160kb 加载到内存中，你可以调整这个值，以读/写更短或更长的块。
 */
define('FILE_ENCRYPTION_BLOCKS', 10000);

/**
 * 文件加密
 * 
 * 因为对越长的字符串进行加密，代价越大，所以通常将明文进行分段，然后对每段明文进行加密，最后再拼成一个字符串，这些分段的明文，通常称为块。
 * 块加密要面临的问题就是如何填满最后一块？所以这就是PADDING的作用，使用各种方式填满最后一块字符串，所以对于解密端，也需要用同样的PADDING来找到最后一块中的真实数据的长度。
 * 此处将使用OPENSSL_RAW_DATA选项，它会自动填充(PADDING)最后一块。
 * 
 * 对分段后的每块后明文进行加密时，其使用的密钥是相同的；为了提高安全性，加密过程又引入了“初始化向量”，"初始化向量"和密钥的作用类似，区别在于应用于所有
 * 分块上的密钥是相同的，但“初始化向量”却是不同的，这就相当于为每个分块又重新定义了一个密钥。
 * 
 */
class FileEncryption{

    /**
     * 加密文件
     *
     * @param string $source 需要加密的文件路径
     * @param string $key 用于加密的密钥
     * @param string $dest 加密后文件路径
     * @return string|false 返回已创建的文件名，如果发生错误则返回 FALSE
     */
    static public function encryptFile($source, $key, $dest){

        $key = substr(sha1($key, true), 0, 16); // 计算字符串 $key 的 sha1 散列值，并取前16位
        $iv  = openssl_random_pseudo_bytes(16); // 生成一个伪随机字节串,作为块加密的初始化向量

        $error = false;
        if ($fpOut = fopen($dest, 'w')) {
            // 将随机字符串作为初始化向量写入文件开头
            fwrite($fpOut, $iv);
            if ($fpIn = fopen($source, 'rb')) { // 以可读写方式打开文件
                while (!feof($fpIn)) { // feof用于判断文件指针是否到了文件结束位置
                    // 从文件指针 $fpIn 处开始读取最多 length 个字节的数据，参数二用于指定最多读取的字节个数
                    $plaintext = fread($fpIn, 16 * FILE_ENCRYPTION_BLOCKS);
                    // 加密数据
                    // 参数1：欲加密的数据
                    // 参数2：加密方式
                    // 参数3：加密密钥
                    // 参数4：加密格式
                    // 参数5：初始化向量：类似于加密密钥，加密和解密都需要相同初始化向量，区别在于每个块的加密密钥是相同的，但初始化向量是不同的，可进一步提高安全性
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
     * @param string $dest 解密后文件路径
     * @return string|false 返回已创建的文件名，如果发生错误则返回 FALSE
     */
    static public function decryptFile($source, $key, $dest){

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