<?php

namespace Colfej\LeKioskCLI;

use Windwalker\Crypt\Cipher\BlowfishCipher;
use Windwalker\Crypt\Crypt;

abstract class Configuration {

    public static function load() {

        $config = array(
            'username' => '',
            'password' => ''
        );

        $crypt = new Crypt(new BlowfishCipher);

        $file = fopen(self::getConfigPath(), 'r');
        $content = fread($file, filesize(self::getConfigPath()));
        fclose($file);

        $content = $crypt->decrypt($content, 'lekiosk-cli');
        $content = explode(PHP_EOL, $content);

        if (isset($content[0]) && isset($content[1])) {
            $config['username'] = $crypt->decrypt($content[1], $content[0]);
        }

        if (isset($content[2]) && isset($content[3])) {
            $config['password'] = $crypt->decrypt($content[3], $content[2]);
        }

        return $config;
        
    }

    public static function write($username, $password) {

        $crypt = new Crypt(new BlowfishCipher);

        $usernamePrivateKey = str_shuffle(mt_rand().uniqid().mt_rand().uniqid());
        $username = $crypt->encrypt($username, $usernamePrivateKey);

        $passwordPrivateKey = str_shuffle(mt_rand().uniqid().mt_rand().uniqid());
        $password = $crypt->encrypt($password, $passwordPrivateKey);

        $content = $usernamePrivateKey.PHP_EOL;
        $content .= $username.PHP_EOL;
        $content .= $passwordPrivateKey.PHP_EOL;
        $content .= $password.PHP_EOL;

        $content = $crypt->encrypt($content, 'lekiosk-cli');

        $file = fopen(self::getConfigPath(), 'w');
        fwrite($file, $content);
        fclose($file);

    }

    protected static function getConfigPath() {

        $home = getenv('HOME');

        if (!empty($home)) {

            $home = rtrim($home, DIRECTORY_SEPARATOR);

        } elseif (!empty($_SERVER['HOMEDRIVE']) && !empty($_SERVER['HOMEPATH'])) {

            $home = $_SERVER['HOMEDRIVE'] . $_SERVER['HOMEPATH'];
            $home = rtrim($home, DIRECTORY_SEPARATOR);

        }

        return (empty($home) ? '.' : $home).DIRECTORY_SEPARATOR.'.lekiosk-cli';

    }

}