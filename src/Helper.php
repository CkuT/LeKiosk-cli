<?php

namespace Colfej\LeKioskCLI;

abstract class Helper {

    public static function sanitize($str) {

        $replace_chars = array(
            'Š'=>'S', 'š'=>'s', 'Ð'=>'Dj','Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A',
            'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I',
            'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U',
            'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss','à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a',
            'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i',
            'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u',
            'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', 'ƒ'=>'f'
        );

        $str = strtr($str, $replace_chars);

        $str = preg_replace('/[^(\x20-\x7F)]*/','', $str);

        $str = str_replace(' ', '-', $str);
        $str = str_replace('\'', '', $str);

        $str = preg_replace('/[^\w\-\.]+/', '', $str);
        $str = preg_replace('/[\-]+/', '-', $str);

        return strtolower($str);

    }

    public static function deleteDirectory($dir) {

        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir) || is_link($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
        
            if ($item == '.' || $item == '..') {
                continue;
            }
        
            if (!self::deleteDirectory($dir.DIRECTORY_SEPARATOR.$item)) {
        
                chmod($dir.DIRECTORY_SEPARATOR.$item, 0777);
        
                if (!self::deleteDirectory($dir.DIRECTORY_SEPARATOR.$item)) {
                    return false;
                }
        
            };
        
        }
        
        return rmdir($dir);

    }

}