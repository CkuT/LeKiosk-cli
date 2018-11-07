<?php

namespace Colfej\LeKioskCLI\Api;

use Colfej\LeKioskCLI\Helper;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Reader {

    public static function isReaderLink($link) {

        if (preg_match("/https:\/\/www2\.lekiosk\.com\/([a-z]+)\/reader\/(?P<publication>[0-9]+)\/(?P<issue>[0-9]+)/", $link, $match)) {
            return array(
                'id_publication' => intval($match['publication']),
                'id_issue' => intval($match['issue'])
            );
        }

        return false;

    }

    public static function get($idPublication, $idIssue) {

        $response = Request::get('reader/publications/'.$idPublication.'/issues/'.$idIssue.'/signedurls/');
        $content = $response['result'];

        return $content;

    }

    public static function download($idPublication, $idIssue, $output = null) {

        $data = self::get($idPublication, $idIssue);

        ksort($data['signedUrls']);

        var_dump($data);

        // Die ... for the moment ...
        die();

        // Cover : coverUrl
        // Pages : signedUrls









        

        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid().'_lekiosk-cli_'.$idPublication.'_'.$idIssue.DIRECTORY_SEPARATOR;

        if (!is_null($output)) {
            $output->writeln('Create tmp folder ...', OutputInterface::VERBOSITY_VERBOSE);
        }

        mkdir($path);

        if (!is_null($output)) {
            
            $output->writeln('Tmp folder is '.$path, OutputInterface::VERBOSITY_DEBUG);

            $progress = new ProgressBar($output, count($data['signedUrls']));
            $progress->start();

        }

        foreach ($data['signedUrls'] as $page => $content) {

            $steps = array('hiresUrl', 'bigUrl');

            foreach ($steps as $step) {

                try {

                    if (!isset($content[$step])) {
                        throw new \Exception('Url not found ...');
                    }

                    $from = $content[$step];
                    $to = $path.str_pad($page, 3, '0', STR_PAD_LEFT).'.jpg';

                    Request::download($to, $from);

                    break;
                    
                } catch (\Exception $e) {

                    if ($step == end($steps)) {
                        throw new \Exception('Can\'t download page '.$page.' ...');
                    }

                    continue;

                }

            }

            if (!is_null($output)) {
                $progress->advance();
            }

        }

        if (!is_null($output)) {
            
            $progress->finish();

            $output->writeln('');

        }

        return array(
            'path'  =>  $path,
            'sanitize'  =>  Helper::sanitize($data['publication']['title'].'_'.$data['issueNumber'])
        );

    }

}