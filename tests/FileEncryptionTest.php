<?php



require_once "../vendor/autoload.php";
// require_once "./AccountConfig.php";


use sidoc\FileEncryption;

// FileEncryption::encryptFile("FileEncryptionTest_data1.sql","111","FileEncryptionTest_data1");
FileEncryption::decryptFile("FileEncryptionTest_data1","111","FileEncryptionTest_data1_1");