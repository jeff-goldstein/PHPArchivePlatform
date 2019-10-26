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
            if ($key_sub == 'Subject') $subject = $value_sub;
            if ($key_sub == 'From') $from = $value_sub;
        }
    }
}


function extract_data($verb, &$fileContent, $DefaultTimeZone, &$original_to, &$headerDate, &$subject, &$from, &$sendDateTime, $inputField, &$UID, &$html, &$headerstring, $simplecheck, &$skip)
{
    $servertimestamp = NULL; $AuthfromServer = NULL; $skip="YES";
    if ($verb == "POST") 
    {
        $original_to = NULL; $headerDate = NULL;

        date_default_timezone_set($DefaultTimeZone);
        $previewTimestamp = localtime(time(),true);
        $monthName = date('F', mktime(0, 0, 0, $previewTimestamp["tm_mon"] + 1, 10));
        $sendDateTime = ($previewTimestamp["tm_year"] + 1900) . "." . $monthName . "." . $previewTimestamp["tm_mday"] . "." . $previewTimestamp["tm_hour"] . "." . $previewTimestamp["tm_min"] . "." . $previewTimestamp["tm_sec"];

        $body = file_get_contents("php://input");
        $headerStringValue = $_SERVER['HTTP_X_API_KEY'];
        // Very Very simple check
        if ($headerStringValue == $simplecheck)
        {
            $skip="NO";
            $fields = json_decode($body, true);
            $rcpt_to = $fields['0']['msys']['relay_message']['rcpt_to'];
            $friendly_from = $fields['0']['msys']['relay_message']['friendly_from'];
            $subject = $fields['0']['msys']['relay_message']['content']['subject'];
            $headers = $fields['0']['msys']['relay_message']['content']['headers'];
            $headerstring = json_encode($headers);
            $html = $fields['0']['msys']['relay_message']['content']['html'];
            $fileContent = $fields['0']['msys']['relay_message']['content']['email_rfc822'];
            $start = strpos($html, $inputField);
            if ($start!=0)
            {
                $start = strpos($html, "value=", $start) + 7;
                $end = strpos($html, ">", $start) - 1;
                $length = $end - $start;
                $UID = substr($html, $start, $length);
                file_put_contents("uploadheaders.txt", $headerstring);
                get_important_headers($headers, $original_to, $headerDate, $subject, $from);
            }
            else
            {
                $errorText = "\n*******\n\tRcpt To: " . $rcpt_to;
                $errorText .= "\n\tSubject: " . $subject;
                $errorText .= "\n\tHeaders: " . $headerstring;
                $errorText .= "\n\tSend Date Time: " . $sendDateTime;
                file_put_contents("SkipThisEmailBecauseNoArchiveValueFound.txt", $errorText, FILE_APPEND);
                $skip="YES";
            }
        }
        else
        {
            $rcpt_to = $fields['0']['msys']['relay_message']['rcpt_to'];
            $subject = $fields['0']['msys']['relay_message']['content']['subject'];
            $headers = $fields['0']['msys']['relay_message']['content']['headers'];
            $errorText = "\n*******\n\tRcpt To: " . $rcpt_to;
            $errorText .= "\n\tSubject: " . $subject;
            $errorText .= "\n\tHeaders: " . $headerstring;
            $errorText .= "\n\tSend Date Time: " . $sendDateTime;
            file_put_contents("SkipThisEmailBecauseMissingSecurityHeader.txt", $errorText, FILE_APPEND);
        }
    }
}

function MYSQLLog ($original_to, $headerDate, $subject, $from, $ArchiveDirectory, $UID, $ArchiveTextLog, $sendDateTime, $servername, $username, $password, $headerstring)
{
    // Create connection
    $conn = mysqli_connect($servername, $username, $password);

    // Check connection
    if (!$conn) {
        $archive_output = sprintf("\n\n>>>>>\nMySQL connection failed connecting to MYSQL to log S3 data entry:%-200s\nTo: %-50s\n From: %-50s\n Subject: %-200s\n HeaderTimeStamp: %-42s\n ArchiveTimeStamp: %-38s\n ArchiveDirectory: %s\n UID: %-60s\n>>>>>", $conn->error, $original_to, $from, $subject, $headerDate, $sendDateTime, $ArchiveDirectory, $UID);
    }
    else
    {
        $sql = "INSERT INTO austein_archiver.tracker (ToEmailAddress, Date, Subject, FromAddress, directory, UID, headerstring) VALUES ('" . $original_to . "', '" . $headerDate . "', '" . $subject . "','" . $from . "', '" . $ArchiveDirectory . "', '" . $UID . "', '" . $headerstring ."')";

        if ($conn->query($sql) === TRUE) 
        {
            $archive_output = sprintf("\n\n>>>>>\nTo: %-50s\n From: %-50s\n Subject: %-200s\n HeaderTimeStamp: %-42s\n ArchiveTimeStamp: %-38s\n ArchiveDirectory: %s\n UID: %-60s\n>>>>>", $original_to, $from, $subject, $headerDate, $sendDateTime, $ArchiveDirectory, $UID);
        } 
        else 
        {
            $archive_output = sprintf("\n\n>>>>>\nMySQL insert failure to MYSQL to log S3 entry:%-200s\nTo: %-50s\n From: %-50s\n Subject: %-200s\n HeaderTimeStamp: %-42s\n ArchiveTimeStamp: %-38s\n ArchiveDirectory: %s\n UID: %-60s\n>>>>>", $conn->error, $original_to, $from, $subject, $headerDate, $sendDateTime, $ArchiveDirectory, $UID);

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
$inputField = $config['hiddenID']['inputField'];
$simplecheck = $config['secret']['secret'];

extract_data($verb, $fileContent, $DefaultTimeZone, $original_to, $headerDate, $subject, $from, $sendDateTime, $inputField, $UID, $html, $headerstring, $simplecheck, $skip);
if ($skip == "NO")
{
    $fileName = $ArchiveDirectory . '/' . $UID . '.eml';
    S3upload ($fileName, $fileContent, $html);
    MySQLlog ($original_to, $headerDate, $subject, $from, $ArchiveDirectory, $UID,  $ArchiveTextLog, $sendDateTime, $servername, $username, $password, $headerstring);
}

?>
