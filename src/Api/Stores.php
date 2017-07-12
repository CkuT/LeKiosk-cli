<?php

namespace Colfej\LeKioskCLI\Api;

abstract class Stores {

    public static function getList() {

        $response = Request::get('stores');
        $content = $response['result'];

        return $content;

    }

}