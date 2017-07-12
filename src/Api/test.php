<?php

$username = '';
$password = '';

define('TELEGRAM_TOKEN', '');
define('TELEGRAM_CHAT_ID', '');

require __DIR__.'/vendor/autoload.php';

function notify($msg) {
    echo ' :> Sending message "'.$msg."\"\n";

    $url = 'https://api.telegram.org/bot'.TELEGRAM_TOKEN.'/sendMessage?chat_id='.TELEGRAM_CHAT_ID;
    $url .= "&text=".urlencode($msg);
    $ch = curl_init();
    $optArray = array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true
    );
    curl_setopt_array($ch, $optArray);
    $result = curl_exec($ch);
    curl_close($ch);
}

function sanitize($f) {
    $replace_chars = array(
        'Š'=>'S', 'š'=>'s', 'Ð'=>'Dj','Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A',
        'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I',
        'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U',
        'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss','à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a',
        'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i',
        'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u',
        'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', 'ƒ'=>'f'
    );
    $f = strtr($f, $replace_chars);
    $f = preg_replace(array('/[\&]/', '/[\@]/', '/[\#]/'), array('-et-', '-arobase-', '-numero-'), $f);
    $f = preg_replace('/[^(\x20-\x7F)]*/','', $f);
    $f = str_replace(' ', '-', $f);
    $f = str_replace('\'', '', $f);
    $f = preg_replace('/[^\w\-\.]+/', '', $f);
    $f = preg_replace('/[\-]+/', '-', $f);
    return strtolower($f);
}

function deleteDirectory($dir) {
    if (!file_exists($dir)) { return true; }
    if (!is_dir($dir) || is_link($dir)) {
        return unlink($dir);
    }
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') { continue; }
        if (!deleteDirectory($dir . "/" . $item, false)) {
            chmod($dir . "/" . $item, 0777);
            if (!deleteDirectory($dir . "/" . $item, false)) return false;
        };
    }
    return rmdir($dir);
}

function parseCategorie(&$client, $idStore, $nameStore, $idPublication, $namePublication, $idCategory, $nameCategory, $idParentCategory = null, $nameParentCategory = null) {

    try {
        $response = $client->get('stores/'.$idStore.'/publicationTypes/'.$idPublication.'/categories/'.$idCategory.'/catalog?sort=0&page=0&itemCount=500');
        $result = json_decode($response->getBody()->getContents(), true);
        $issues = $result['result'];
    } catch (Exception $e) {
        echo ' /!\ Error : '.$e->getMessage()."\n";
        return;
    }

    foreach ($issues['issues'] as $issue) {

        try {
            $response = $client->get('publications/'.$issue['publicationId'].'/issues/-1?indexStart=0&itemCount=30');
            $result = json_decode($response->getBody()->getContents(), true);
            $collection = $result['result'];
        } catch (Exception $e) {
            echo ' /!\ Error : '.$e->getMessage()."\n";
            continue;
        }

        foreach ($collection as $book) {

            $toDownload = array();

            try {
                $response = $client->get('reader/publications/'.$book['publicationId'].'/issues/'.$book['issueId'].'/signedurls');
                $result = json_decode($response->getBody()->getContents(), true);
                $content = $result['result'];
            } catch (Exception $e) {
                echo ' /!\ Error : '.$e->getMessage()."\n";
                continue;
            }

            if (!is_null($idParentCategory)) {
                $path = './downloads/'.sanitize($nameStore).'/'.sanitize($namePublication).'/'.sanitize($nameParentCategory).'/'.sanitize($nameCategory).'/'.sanitize($content['publication']['title']).'/'.sanitize($content['issueNumber']).'/src/';
            } else {
                $path = './downloads/'.sanitize($nameStore).'/'.sanitize($namePublication).'/'.sanitize($nameCategory).'/'.sanitize($content['publication']['title']).'/'.sanitize($content['issueNumber']).'/src/';
            }

            if (count(explode(',', $content['freePages'])) == count($content['signedUrls'])) {
                if (is_dir($path)) { deleteDirectory(dirname($path).'/'); }

                continue;
            }

            echo '       => '.$content['publication']['title'].' - '.$content['issueNumber'].' ('.count($content['signedUrls']).' pages)'."\n";

            ksort($content['signedUrls']);

            foreach ($content['signedUrls'] as $page => $content) {

                $toDownload[] = array(
                    'from'  =>  $content['hiresUrl'],
                    'to'    =>  $path.str_pad($page, 5, '0', STR_PAD_LEFT).'.jpg'
                );

            }

            try {
                downloadAndCompile($toDownload);
            } catch (Exception $e) {
                echo ' /!\ Error : '.$e->getMessage()."\n";
                deleteDirectory(dirname(dirname($toDownload[0]['to']).'/').'/');
                continue;
            }

        }

    }

}

function downloadAndCompile($toDownload) {

    $path = dirname(dirname($toDownload[0]['to']).'/').'/';

    if (!is_dir($path)) {
        $new = true;
    } else {
        $new = false;
    }

    $client = new GuzzleHttp\Client();

    $requests = function ($toDownload) use ($client) {
        foreach ($toDownload as $dl) {

            if (is_file($dl['to'])) {
                continue;
            }

            if (!is_dir(dirname($dl['to']).'/')) {
                mkdir(dirname($dl['to']).'/', 0777, true);
            }

            yield function($poolOpts) use ($client, $dl) {

                $reqOpts = ['sink' => $dl['to']];

                if (is_array($poolOpts) && count($poolOpts) > 0) {
                    $reqOpts = array_merge($poolOpts, $reqOpts);
                }

                return $client->getAsync($dl['from'], $reqOpts);

            };

        }
    };

    $pool = new GuzzleHttp\Pool($client, $requests($toDownload), [
        'concurrency' => 10,
        'fulfilled' => function ($response, $index) {
        },
        'rejected' => function ($reason, $index) {
            echo '         => Error on index '.$index.' : '.$reason."\n";
        },
    ]);

    $promise = $pool->promise();
    $promise->wait();

    $filename = basename(dirname(dirname(dirname($toDownload[0]['to']).'/').'/')).'_'.basename(dirname(dirname($toDownload[0]['to']).'/'));

    if (is_file($path.'ebook.zip')) { unlink($path.'ebook.zip'); }
    if (is_file($path.'ebook.pdf')) { unlink($path.'ebook.pdf'); }

    if (!is_file($path.$filename.'.zip')) {

        echo '         => Create zip ...'."\n";

        $zip = new ZipArchive();
        $zip->open($path.$filename.'.zip', ZIPARCHIVE::CREATE);

        foreach ($toDownload as $file) {
            if (is_file($file['to'])) {
                $zip->addFile($file['to'], basename($file['to']));
            }
        }

        $zip->close();

    }

    if (!is_file($path.$filename.'.pdf')) {

        echo '         => Create pdf ...'."\n";

        $files = array();

        foreach ($toDownload as $file) {
            if (is_file($file['to'])) {
                $files[] = $file['to'];
            }
        }

        $pdf = new Imagick($files);
        $pdf->setImageFormat('pdf');
        $pdf->writeImages($path.$filename.'.pdf', true);

    }

    if ($new) {
       notify('Now available : '.$filename);
    }

}

$client = new GuzzleHttp\Client([
    'base_uri'  =>  'https://api.lekiosk.com/api/v1/',
    'auth'      =>  [$username, $password],
    'headers'   =>  [
        'User-Agent'    =>  'lekioskworld/475 CFNetwork/811.5.4 Darwin/16.6.0',
        'Accept'        =>  'application/json',
        'Content-type'  =>  'application/json'
    ]
]);

$response = $client->get('stores');
$result = json_decode($response->getBody()->getContents(), true);
$stores = $result['result'];

foreach ($stores as $store) {

    $response = $client->get('stores/'.$store['storeId']);
    $result = json_decode($response->getBody()->getContents(), true);
    $store = $result['result'];

    echo ' => Store '.$store['name'].' ('.$store['cultureInfo'].')'."\n";

    foreach ($store['publicationTypes'] as $publication) {

        echo '   => '.$publication['name']."\n";

        foreach ($publication['categories'] as $categorie) {

            echo '     => '.$categorie['name']."\n";

            if (isset($categorie['subCategories'])) {

                foreach ($categorie['subCategories'] as $subCategorie) {

                    echo '     -> '.$subCategorie['name']."\n";

                    parseCategorie(
                        $client,
                        $store['storeId'], $store['name'],
                        $publication['publicationTypeId'], $publication['name'],
                        $subCategorie['categoryId'], $subCategorie['name'],
                        $categorie['categoryId'], $categorie['name']
                    );

                }

            } else {

                parseCategorie(
                    $client,
                    $store['storeId'], $store['name'],
                    $publication['publicationTypeId'], $publication['name'],
                    $categorie['categoryId'], $categorie['name']
                );

            }

        }

    }

}