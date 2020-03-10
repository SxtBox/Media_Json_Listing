<?php

/**
 * 
 * Shoutcast & icecast2 v2.x
 *
 */

if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
  $protocol = 'http://';
} else {
  $protocol = 'https://';
}
$ROOT_URL = $protocol . $_SERVER['SERVER_NAME'] . dirname($_SERVER['PHP_SELF']) . "";

$server_stream_url = 'http://stream.dancewave.online:8080';
$json_api = array();
$opts = array('http' => array('method' => "GET", 'header' => "Accept-language: en\r\n" . "Referer: http://kodi.al/\r\n" . "Content-type: application/x-www-form-urlencoded\r\n" . "User-agent: Vari Karin\r\n" . "Origin: null" . "\r\n"));
$context = stream_context_create($opts);

if (!isset($_GET['json']))
{
    $_GET['json'] = 'route';
}

switch ($_GET['json'])
{

case 'route':

$json_api["routes"]['/main_server'] = array(
    "namespace" => "",
    "methods" => "GET",
    "link" =>  $server_stream_url . "");

$json_api["routes"]['/stats'] = array(
    "namespace" => "/stats",
    "methods" => "GET",
    "link" =>  $server_stream_url . "/stats");

$json_api["routes"]['/route'] = array(
    "namespace" => "/listeners",
    "methods" => "GET",
    "link" =>  $server_stream_url . "/index.html?sid=1");

$json_api["routes"]['/mount'] = array(
    "namespace" => "/mount",
    "methods" => "GET",
    "link" => $server_stream_url . "?status-json.xsl?mount=/dance.mp3");

$json_api["routes"]['/status_json'] = array(
    "namespace" => "/status_json",
    "methods" => "GET",
    "link" =>  $server_stream_url . "/status-json.xsl");
break;

// EXTRA FUNCTIONS FOR SHOUTCAST
case 'status':
$content = @file_get_contents($server_stream_url . '/index.html?sid=2', false, $context);
$content = explode('<table cellpadding="2" cellspacing="0" border="0" align="center">', $content);
$content = explode('</table>', $content[1]);
$content = '{' . $content[0] . '}';
$content = str_replace('<td width="120" valign="top">', '<td>', $content);
$content = str_replace('<td valign="top">', '<td>', $content);
$content = str_replace('<tr><td>', '"', $content);
$content = str_replace('</td></tr>', '",', $content);
$content = str_replace(': </td><td>', '":"', $content);
$content = strip_tags($content);
$content = str_replace(',}', '}', $content);
$content = str_replace('Server Status', 'ServerStatus', $content);
$content = str_replace('Stream Status', 'StreamStatus', $content);
$content = str_replace('Stream Name', 'StreamName', $content);
$content = str_replace('Content Type', 'ContentType', $content);
$content = str_replace('Stream Genre(s)', 'StreamGenres', $content);
$content = str_replace('Current Song', 'CurrentSong', $content);
$json_api = json_decode($content, true);
break;

case 'history':
$content = @file_get_contents($server_stream_url . '/played.html?sid=2', false, $context);
$content = explode('<tr><td><b>Played @</b></td><td><b>Song Title</b></td></tr>', $content);
$content = explode('</table>', $content[1]);
$content = "[" . str_replace('</tr>', '"},', $content[0]) . ']';
$content = str_replace('<td style="padding: 0 10px;"><b>Current Song</b></td>', ' (Current Song)', $content);
$content = str_replace('<tr><td>', '{"time":"', $content);
$content = str_replace('</td><td>', '","title":"', $content);
$content = str_replace('},]', '}]', $content);
$json_api = json_decode($content, true);
break;
}

header('Content-type: application/json');
header('Access-Control-Allow-Origin: *');
echo json_encode($json_api, JSON_UNESCAPED_UNICODE);

?>