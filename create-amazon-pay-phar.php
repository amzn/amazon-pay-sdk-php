<?php
    $p = new Phar('amazon-pay.phar', FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME, 'amazon-pay.phar');
    $p->startBuffering();
    $p->setStub('<?php Phar::mapPhar(); require \'phar://amazon-pay.phar/AmazonPay/Client.php\'; require \'phar://amazon-pay.phar/AmazonPay/IpnHandler.php\'; __HALT_COMPILER(); ?>');
    $p->buildFromDirectory('.', '$(.*)\.php$');
    $p->stopBuffering();
    echo "Phar created: amazon-pay.phar\n";
?>
