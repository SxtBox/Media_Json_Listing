<?php

/**
 * @author: TRC4 <trc4@usa.com>
 * @copyright: TRC4.COM
 * @created date: Tuesday, March 10, 2020 (GMT+1)
 * @package: RSS To JSON Converter
 * 
 */

error_reporting(0);
date_default_timezone_set("Europe/Tirane");
// HTTP/HTTPS
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
  $protocol = 'http://';
} else {
  $protocol = 'https://';
}
$ROOT_URL = $protocol . $_SERVER['SERVER_NAME'] . dirname($_SERVER['PHP_SELF']) . "/";
$VAR1 = $ROOT_URL . "";
$VAR2 = "";

// CONFIG
$append_url = "?ref=albdroid"; // REPLACE
$url_feeds["albdroid"] = "http://www.radiorecord.ru/rss.xml"; // RSS LINK
$date_format = "l, jS \of F Y h:i:s A";
// END CONFIG

if(!ini_get("allow_url_fopen")){
	die("<strong>Error!</strong> The PHP allow_url_fopen setting is disabled, please edit your <a target=\"_blank\" href=\"http://php.net/allow-url-fopen\">php.ini</a>");
}

$rest_api = array();
// MAKE ACTIVE error_reporting OR FIX THIS PART
function get_images($content){
	$images = array();
	libxml_use_internal_errors(true);
	$doc = new DOMDocument();
	$doc->loadHTML($content);
	libxml_clear_errors();
	$imageTags = $doc->getElementsByTagName("img"); // FIX IMAGES ATTRIBUTES
	foreach ($imageTags as $tag)
	{
		$images[] = $tag->getAttribute("src"); // FIX SRC ATTRIBUTES
	}
	return $images;
}

function get_rss($url,$date_format) {
	global $append_url;
	$rss_content = file_get_contents($url);
	$obj = simplexml_load_string($rss_content,"SimpleXMLElement",LIBXML_NOCDATA);
	$arr = json_decode(json_encode($obj), true);
	$item = 0;
	$new_entry = array();
	if(!isset($arr["entry"])){
		$arr["entry"]=$arr["channel"]["item"] ;
	}

	foreach ($arr['entry'] as $entry){
		$new_entry[$item] = $entry;
		//FIX ID IF IS WRONG
		$new_entry[$item]['id'] = $item;
		//FIX LINK IF IS WRONG
		if (isset($entry['link'])){
			if (isset($entry['link']['@attributes'])){
				$new_entry[$item]['x_link']['attributes'] = $entry['link']['@attributes'];
				if (isset($new_entry[$item]['x_link']['attributes']['href'])){
					$new_entry[$item]['x_link']['attributes']['href'] = $new_entry[$item]['x_link']['attributes']['href'].$append_url ;
				}
			}

			if (count($entry['link']) > 1){
				$y = 0;
					foreach ($entry['link'] as $link){
						$new_entry[$item]['x_link'][$y]['attributes'] = $link['@attributes'] ;
						if (isset($link['@attributes']['href'])){
							$new_entry[$item]['x_link'][$y]['attributes']['href'] = $link['@attributes']['href'].$append_url ;
						}
					$y++;
				}
			}
		}

		if (isset($entry['category']['@attributes'])){
			$new_entry[$item]['x_category']['attributes'] = $entry['category']['@attributes'];
		}
		if (isset($entry['updated'])){
			$new_entry[$item]['x_updated'] = date($date_format, strtotime($entry['updated']));
		}
		if (isset($entry['published'])){
			$new_entry[$item]['published_date'] = date($date_format, strtotime($entry['published']));
		}
		if (isset($entry['description'])){
			$new_entry[$item]['content'] = $entry['description'];
			unset($new_entry[$item]['description']);
			$images = get_images($new_entry[$item]['content']);
			if (isset($images[0]))
			{
			$new_entry[$item]['thumbnail'] = $images[0];
			}
			$new_entry[$item]['images'] = $images;
		}
		if (isset($entry['content'])){
			$images = get_images($new_entry[$item]['content']);
			if (isset($images[0]))
			{
				$new_entry[$item]['thumbnail'] = $images[0];
			}
			$new_entry[$item]['images'] = $images;
		}
		if (isset($entry['pubDate'])){
			if (!is_array($entry['pubDate'])){
				$new_entry[$item]['published_date'] = date($date_format, strtotime($entry['pubDate']));
				unset($new_entry[$item]['pubDate']);
			}
		}
		if (isset($entry['link'])){
			$new_entry[$item]['x_link']['attributes']['href'] = $entry['link'];
		}
		if (isset($entry['enclosure']['@attributes'])){
			$new_entry[$item]['enclosure']['attributes'] = $entry['enclosure']['@attributes'];
		}
		if (isset($entry['link']['@attributes']['href'])){
			$new_entry[$item]['x_link']['attributes']['href'] = $entry['link']['@attributes']['href'];
		}
		$item++;
	}
	return $new_entry;
}

if(!isset($_GET["json"])){
	$_GET["json"]= "route";
}

switch($_GET["json"]){
	case "albdroid": 
		$rest_api = get_rss($url_feeds["albdroid"],$date_format);
		break;

	case "route":                                                                       
$rest_api["routes"][0] = array("namespace"=>"albdroid","methods"=>"GET","link"=>"http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']."?json=albdroid");
//$rest_api["routes"][0] = array("namespace"=>"albdroid","methods"=>"GET","link"=>$_SERVER['HTTP_HOST'].$_SERVER["PHP_SELF"]."?json=albdroid"); // NO HTTP
//$rest_api["routes"][0] = array("namespace"=>"albdroid","methods"=>"GET","link"=>$_SERVER["PHP_SELF"]."?json=albdroid"); // FOLDER & PHP File only
	break;
}

header('Content-type: application/json');
header('Access-Control-Allow-Origin: *');
if(defined("VARI_KARIN"))
{
    $output = str_replace('\\/', '/', json_encode($rest_api)); // REMOVE \/
	echo $output;
}else{
	echo str_replace('\\/', '/', json_encode($rest_api,JSON_UNESCAPED_UNICODE));// ELSE \/
	//echo json_encode($rest_api,JSON_UNESCAPED_UNICODE); // WITH \/
}
?>
