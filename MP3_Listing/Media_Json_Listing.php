<?php

/**
 * 
 * @package Media JSON Listing (No Need SQL)
 * 
 * Upload this file to your server, change config folder
 * Then upload your mp3,mp4, etc Information on JSON according id3 tag.
 * 
 * JSON Variable (Column In Tables):
 * --- id
 * --- url
 * --- name
 * --- filesize
 * --- id3.Version
 * --- id3.Album
 * --- id3.Author
 * --- id3.AlbumAuthor
 * --- id3.Track
 * --- id3.Year
 * --- id3.Genre
 * --- id3.Encoded
 * --- id3.Copyright
 * --- id3.Publisher
 * --- id3.OriginalArtist
 * --- id3.URL
 * --- id3.Comments
 * --- id3.Rating
 * --- id3.Composer
 *  
 */

if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
  $protocol = 'http://';
} else {
  $protocol = 'https://';
}
$ROOT_URL = $protocol . $_SERVER['SERVER_NAME'] . dirname($_SERVER['PHP_SELF']) . "/";
$media_folder = $ROOT_URL . "mp3";

$config['path'] = 'mp3'; // MP3 FOLDER
//$config['url'] = 'http://' . $_SERVER["HTTP_HOST"] . '/mp3'; // MP3 URL LOCALHOST ONLY EX: http://localhost/mp3

// $config['url'] = $ROOT_URL . $mp3_folder; // AUTO URL
$config['url'] = $media_folder; // ROOT URL

$id = 0;

$rest_api = array();

foreach (glob($config['path'] . "/*.mp3") as $filename)
{
    $file_url = $config['url'] . '/' . basename($filename);
    $rest_api[$id]['id'] = $id;
	$rest_api[$id]['title'] = basename($filename);
    $rest_api[$id]['url'] = $file_url;
    $rest_api[$id]['size'] = readable_size(filesize($filename));
    $rest_api[$id]['id3'] = get_id3($filename);
    // $rest_api[$id]['debug'] = readable_size(memory_get_usage()); // debug
    $id++;
}

function get_id3($filename)
{
    $tags[] = array("prefix" => "TALB", "text" => "Album");
    $tags[] = array("prefix" => "TPE1", "text" => "Author");
    $tags[] = array("prefix" => "TPE2", "text" => "AlbumAuthor");
    $tags[] = array("prefix" => "TRCK", "text" => "Track");
    $tags[] = array("prefix" => "TYER", "text" => "Year");
    $tags[] = array("prefix" => "TLEN", "text" => "Lenght");
    $tags[] = array("prefix" => "USLT", "text" => "Lyric");
    $tags[] = array("prefix" => "TPOS", "text" => "Desc");
    $tags[] = array("prefix" => "TCON", "text" => "Genre");
    $tags[] = array("prefix" => "TENC", "text" => "Encoded");
    $tags[] = array("prefix" => "TCOP", "text" => "Copyright");
    $tags[] = array("prefix" => "TPUB", "text" => "Publisher");
    $tags[] = array("prefix" => "TOPE", "text" => "OriginalArtist");
    $tags[] = array("prefix" => "WXXX", "text" => "URL");
    $tags[] = array("prefix" => "COMM", "text" => "Comments");
    $tags[] = array("prefix" => "POPM", "text" => "Rating");
    $tags[] = array("prefix" => "TCOM", "text" => "Composer");
    $info['Version'] = '0.0';
    $fp = fopen($filename, 'r');
    $metadata = fread($fp, 1024);
    fclose($fp);

    if (substr($metadata, 0, 3) == 'ID3')
    {
        $info_version = hexdec(bin2hex(substr($metadata, 3, 1))) . '.' . hexdec(bin2hex(substr($metadata, 4, 1)));
        if ($info_version == '4.0' || $info_version == '3.0')
        {
            $info['Version'] = $info_version;
            for ($i = 0; $i < count($tags); $i++)
            {
                if (strpos($metadata, $tags[$i]['prefix'] . chr(0)) != false)
                {
                    $varname = ($tags[$i]['text']);
                    $iPos = strpos($metadata, $tags[$i]['prefix'] . chr(0));
                    $iLen = hexdec(bin2hex(substr($metadata, ($iPos + 5), 3)));
                    $data = substr($metadata, $iPos + 10 + 1, $iLen - 1);

                    if ($tags[$i]['prefix'] == 'COMM')
                    {
                        $data = '[' . substr($metadata, $iPos + 10 + 1, 3) . '] ' . substr($metadata, $iPos + 10 + 1 + 4, $iLen - (1 + 4));
                    }
                    if ($tags[$i]['prefix'] == 'WXXX')
                    {
                        $data = substr($metadata, $iPos + 10 + 1 + 1, $iLen - (1 + 1));
                    }
                    $info[$varname] = htmlentities($data);
                }
            }

        }
    }
    return $info;
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