<?php

/**
 * 
 * @package Image Listing (No Need SQL)
 * 
 * Upload this file to your server, change config URL and folder
 * Then upload your images, Information on JSON according metaexif.
 * 
 * JSON Variable (Column In Tables):
 * --- id
 * --- url
 * --- name
 * --- filesize
 * --- image.width
 * --- image.height
 * --- image.bits
 * --- image.mime
 * --- image.make
 * --- image.model
 * --- image.software
 * --- image.datetime
 * --- image.datetime_original
 * --- image.datetime_digitized
 *  
 */

if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
  $protocol = 'http://';
} else {
  $protocol = 'https://';
}
$ROOT_URL = $protocol . $_SERVER['SERVER_NAME'] . dirname($_SERVER['PHP_SELF']) . "/";
$images_folder = $ROOT_URL . "images";
//$mp3_folder = "mp3";

$config['path'] = 'images'; //  folder
//$config['url'] = 'http://' . $_SERVER["HTTP_HOST"] . '/radio/images'; //  url

$config['url'] = $images_folder; // ROOT URL

$id = 0;
$rest_api = array();
foreach (glob($config['path'] . "/*.{jpg,jpeg,JPG,JPEG,png,PNG}", GLOB_BRACE) as $filename)
{
    $file_url = $config['url'] . '/' . basename($filename);
    $rest_api[$id]['id'] = $id;
    $rest_api[$id]['url'] = $file_url;
    $rest_api[$id]['name'] = basename($filename);
    $rest_api[$id]['filesize'] = readable_size(filesize($filename));
    if (function_exists('getimagesize'))
    {
        $imagesize = getimagesize($filename);
        $rest_api[$id]['image']['width'] = $imagesize[0];
        $rest_api[$id]['image']['height'] = $imagesize[1];
        $rest_api[$id]['image']['bits'] = $imagesize['bits'];
        $rest_api[$id]['image']['mime'] = $imagesize['mime'];
    }

    if (function_exists('exif_read_data'))
    {
        if ((pathinfo($filename, PATHINFO_EXTENSION) == 'jpeg') || (pathinfo($filename, PATHINFO_EXTENSION) == 'jpg'))
        {
            $exif = exif_read_data($filename, 0, true);
            foreach ($exif as $key => $section)
            {
                foreach ($section as $name => $val)
                {
                    if (strtolower($name) == 'copyright')
                    {
                        $rest_api[$id]['image']['copyright'] = htmlentities($val);
                    }
                    if (strtolower($name) == 'author')
                    {
                        $rest_api[$id]['image']['author'] = htmlentities($val);
                    }
                    if (strtolower($name) == 'company')
                    {
                        $rest_api[$id]['image']['company'] = htmlentities($val);
                    }
                    if (strtolower($name) == 'software')
                    {
                        $rest_api[$id]['image']['software'] = htmlentities($val);
                    }
                    if (strtolower($name) == 'make')
                    {
                        $rest_api[$id]['image']['make'] = htmlentities($val);
                    }
                    if (strtolower($name) == 'model')
                    {
                        $rest_api[$id]['image']['model'] = htmlentities($val);
                    }
                    if (strtolower($name) == 'datetime')
                    {
                        $rest_api[$id]['image']['datetime'] = htmlentities($val);
                    }
                    if (strtolower($name) == 'datetimeoriginal')
                    {
                        $rest_api[$id]['image']['datetime_original'] = htmlentities($val);
                    }
                    if (strtolower($name) == 'datetimedigitized')
                    {
                        $rest_api[$id]['image']['datetime_digitized'] = htmlentities($val);
                    }

                }
            }
        }
    }
    $rest_api[$id]['debug'] = readable_size(memory_get_usage());
    $id++;
}

function readable_size($size)
{
    $unit = array('b','kb','mb','gb','tb','pb');
    return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
}

header('Content-type: application/json');
header('Access-Control-Allow-Origin: *');

if (defined("JSON_UNESCAPED_UNICODE"))
{
    die(json_encode($rest_api, JSON_UNESCAPED_UNICODE));
} else
{
    die(json_encode($rest_api));
}

?>