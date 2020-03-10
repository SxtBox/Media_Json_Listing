<?php
/**
 * @PHP Mailer Json
 */

//change with your email
$recipient = "your@mail.com";

$rest_api["routes"]["methods"] = "POST";
$rest_api["routes"]["args"]["feedback_email"] = array(
"required" => "true",
"description" => "Enter Your Description");

$rest_api["routes"]["args"]["feedback_name"] = array(
"required" => "true", "description" => "Enter Your Description");

$rest_api["routes"]["args"]["feedback_message"] = array(
"required" => "true", "description" => "Enter Your Description");

if (isset($_POST['feedback_email']))
{
    $rest_api = array();
    if (!isset($_POST["feedback_email"]))
    {
        $_POST["feedback_email"] = 'your@mail.com';
    }
    if (!isset($_POST["feedback_name"]))
    {
        $_POST["feedback_name"] = 'your name';
    }
    if (!isset($_POST["feedback_message"]))
    {
        $_POST["feedback_message"] = '';
    }
    $email = filter_var(trim($_POST["feedback_email"]), FILTER_SANITIZE_EMAIL);
    $message = trim($_POST["feedback_message"]);
    $name = strip_tags(trim($_POST["feedback_name"]));
    $name = str_replace(array("\r", "\n"), array(" ", " "), $name);
    if (empty($name) or empty($message) or !filter_var($email, FILTER_VALIDATE_EMAIL))
    {
        $rest_api["message"] = "Something went wrong and we couldn't send your message";
        $rest_api["title"] = "Error!";
    } else
    {
        $subject = "New email from $name";
        $email_content = "Name: $name\n";
        $email_content .= "Email: $email\n\n";
        $email_content .= "Message:\n$message\n";
        $email_headers = 
        "From: $name <$email>" . "\r\n" . 
        "Reply-To: " . $email . "\r\n" . 
        "X-Mailer: PHP/" . phpversion();
        if (mail($recipient, $subject, $email_content, $email_headers))
        {
            $rest_api["message"] = "Thank You! Your message has been sent";
            $rest_api["title"] = "Thank You!";
        }
    }
} else
{
    $rest_api["message"] = "Method not Allowed";
    $rest_api["title"] = "Error";
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