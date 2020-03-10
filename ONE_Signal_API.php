<?PHP

/**
 * OneSignal API
 * sendMessage()
 * 
 * @param mixed $app_id
 * @param mixed $api_key
 * @param mixed $message
 * @return
 */

// DOC https://documentation.onesignal.com/v6.0/reference#section-example-code-create-notification
function sendMessage($app_id, $api_key, $message)
{
    $content = array("en" => $message);
    $fields = array(
        'app_id' => $app_id,
        'included_segments' => array('All'),
        'data' => array("foo" => "bar"),
        'contents' => $content);

    $fields = json_encode($fields);
    $ch = curl_init();
	                            // https://documentation.onesignal.com/docs
    curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8', 'Authorization: Basic ' . $api_key));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}

$app_id = 'ENTER_APP_ID_HERE';
$api_key = 'ENTER_API_KEY_HERE';
$message = 'Enter Success Message Here';

$response = sendMessage($app_id,$api_key,$message);
echo "<pre>";
print_r($response);
echo "</pre>";

?>