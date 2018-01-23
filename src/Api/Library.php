<?php

namespace Colfej\LeKioskCLI\Api;

use Colfej\LeKioskCLI\Helper;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Library {

    public static function getAllPublications($archived = false, $deleted = false) {

        $publications = array();

        $page = 1;
        $count = 100000;

        while ($page >= 1) {

            $return = self::getPublications($archived, $deleted, $page, $count);

            if (count($return) != $count) {
                $page = null;
            } else {
                $page++;
            }

            $publications = array_merge($publications, $return);

        }

        return $publications;

    }

    public static function getPublications($archived = false, $deleted = false, $page = 1, $count = 100000) {

        $query = array(
            'archived'  =>  ($archived ? 'true' : 'false'),
            'deleted'   =>  ($deleted ? 'true' : 'false'),
            'page'      =>  intval($page),
            'itemCount' =>  intval($count)
        );

        $response = Request::get('users/me/library/publications?'.http_build_query($query));
        $content = $response['result'];

        return $content;

    }

    public static function getAllIssuesForPublication($id, $archived = false, $deleted = false) {

        $issues = array();

        $page = 1;
        $count = 100000000;

        while ($page >= 1) {

            $return = self::getIssuesForPublication($id, $archived, $deleted, $page, $count);

            if (count($return) != $count) {
                $page = null;
            } else {
                $page++;
            }

            $issues = array_merge($issues, $return);

        }

        return $issues;

    }

    public static function getIssuesForPublication($id, $archived = false, $deleted = false, $page = 1, $count = 100000000) {

        $query = array(
            'archived'  =>  ($archived ? 'true' : 'false'),
            'deleted'   =>  ($deleted ? 'true' : 'false'),
            'page'      =>  intval($page),
            'itemCount' =>  intval($count)
        );

        $response = Request::get('users/me/library/publications/'.intval($id).'/issues?'.http_build_query($query));
        $content = $response['result'];

        return $content;

    }

}