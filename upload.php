<?php

// Include the SDK using the Composer autoloader
require 'vendor/autoload.php';
require 's3.php';
$config = require('config.php');

function get_important_headers($headers, &$original_to, &$headerDate, &$subject, &$from)
{
    foreach ($headers as $key => $value) {
        foreach ($value as $key_sub => $value_sub) {
            if ($key_sub == 'To') $original_to = $value_sub;
            if ($key_sub == 'Date') $headerDate = $value_sub;
            if ($key_sub == 'Subject') $ubject = $value_sub;
            if ($key_sub == 'From') $from = $value_sub;
        }
    }
}


function extract_data($verb, &$fileContent, $DefaultTimeZone, &$original_to, &$headerDate, &$subject, &$from, &$sendDateTime, &$transmissionID)
{
    $servertimestamp = NULL; $AuthfromServer = NULL;
    if ($verb == "POST") 
    {
        $original_to = NULL; $headerDate = NULL;

        date_default_timezone_set($DefaultTimeZone);
        $previewTimestamp = localtime(time(),true);
        $monthName = date('F', mktime(0, 0, 0, $previewTimestamp["tm_mon"] + 1, 10));
        $sendDateTime = ($previewTimestamp["tm_year"] + 1900) . "." . $monthName . "." . $previewTimestamp["tm_mday"] . "." . $previewTimestamp["tm_hour"] . "." . $previewTimestamp["tm_min"] . "." . $previewTimestamp["tm_sec"];

        $body = file_get_contents("php://input");
        $fields = json_decode($body, true);
        $rcpt_to = $fields['0']['msys']['relay_message']['rcpt_to'];
        $friendly_from = $fields['0']['msys']['relay_message']['friendly_from'];
        $subject = $fields['0']['msys']['relay_message']['content']['subject'];
        $headers = $fields['0']['msys']['relay_message']['content']['headers'];
        $transmissionID = $fields['0']['msys']['relay_message']['content']['transmissionID'];
        $fileContent = $fields['0']['msys']['relay_message']['content']['email_rfc822'];

        get_important_headers($headers, $original_to, $headerDate, $subject, $from);
    }
}

function MYSQLLog ($original_to, $headerDate, $subject, $from, $fileName, $ArchiveTextLog, $sendDateTime, $servername, $username, $password)
{
    // Create connection
    $conn = mysqli_connect($servername, $username, $password);

    // Check connection
    if (!$conn) {
        $archive_output = sprintf("\n\n>>>>>MySQL connection failed connecting to MYSQL to log S3 entry:%-200s\nTo: %-50s\n From: %-50s\n Subject: %-200s\n HeaderTimeStamp: %-42s\n ArchiveTimeStamp: %-38s\n ArchiveFileName: %s>>>>>", $conn->error, $original_to, $from, $subject, $headerDate, $sendDateTime, $fileName);
    }
    else
    {
        $sql = "INSERT INTO austein_archiver.tracker (ToEmailAddress, Date, Subject, FromAddress, fileName) VALUES ('" . $original_to . "', '" . $headerDate . "', '" . $subject . "','" . $from . "', '" . $fileName . "')";

        if ($conn->query($sql) === TRUE) 
        {
            $archive_output = sprintf("\n\n>>>>>To: %-50s\n From: %-50s\n Subject: %-200s\n HeaderTimeStamp: %-42s\n ArchiveTimeStamp: %-38s\n ArchiveFileName: %s>>>>>", $original_to, $from, $subject, $headerDate, $sendDateTime, $fileName);
        } 
        else 
        {
            $archive_output = sprintf("\n\n>>>>>MySQL insert failure to MYSQL to log S3 entry:%-200s\nTo: %-50s\n From: %-50s\n Subject: %-200s\n HeaderTimeStamp: %-42s\n ArchiveTimeStamp: %-38s\n ArchiveFileName: %s>>>>>", $conn->error, $original_to, $from, $subject, $headerDate, $sendDateTime, $fileName);

        }
        mysqli_close($conn);
    }
    file_put_contents($ArchiveTextLog, $archive_output, LOCK_EX | FILE_APPEND);
}

    
// Main Body - Sort of
$verb = $_SERVER['REQUEST_METHOD']; 
        
$ArchiveDirectory = $config['archive']['ArchiveDirectory']; 
$ArchiveTextLog = $config['archive']['ArchiveLogName'];
$DefaultTimeZone = $config['archive']['DefaultTimeZone'];
$servername = $config['mysql']['servername'];
$username = $config['mysql']['username'];
$password = $config['mysql']['password'];

extract_data($verb, $fileContent, $DefaultTimeZone, $original_to, $headerDate, $subject, $from, $sendDateTime, $transmissionID);
$fileName = $ArchiveDirectory . '/' . $transmissionID . '.eml';
S3upload ($fileName, $fileContent);
MySQLlog ($original_to, $headerDate, $subject, $from, $fileName, $ArchiveTextLog, $sendDateTime, $servername, $username, $password);

?>
