<?php
/* File: s3.php
   Purpose: Store the rfc822 portion of the inbox relay obtained by SparkPost on
            all archived emails
    Called by: upload.php
*/

// Include the SDK using the Composer autoloader
require 'vendor/autoload.php';
require 's3.php';
$config = require('config.php');

function get_important_headers($headers, &$original_to, &$headerDate, &$subject, &$from)
{
// Loop through headers array and pull out what I want to store in my SQL table
// as a reference to each archived file in S3.
    foreach ($headers as $key => $value) {
        foreach ($value as $key_sub => $value_sub) {
            if ($key_sub == 'To') $original_to = $value_sub;
            if ($key_sub == 'Date') $headerDate = $value_sub;
            if ($key_sub == 'Subject') $ubject = $value_sub;
            if ($key_sub == 'From') $from = $value_sub;
        }
    }
}


function extract_data($verb, &$fileContent, $DefaultTimeZone, &$original_to, &$headerDate, &$subject, &$from, &$sendDateTime, $inputField, &$UID)
{
// Get the data needed for storing in s3 and in the SQL table
    $servertimestamp = NULL; $AuthfromServer = NULL;
    if ($verb == "POST") 
    {
        $original_to = NULL; $headerDate = NULL;

        date_default_timezone_set($DefaultTimeZone);
        $previewTimestamp = localtime(time(),true);
        $monthName = date('F', mktime(0, 0, 0, $previewTimestamp["tm_mon"] + 1, 10));
        $sendDateTime = ($previewTimestamp["tm_year"] + 1900) . "." . $monthName . "." . $previewTimestamp["tm_mday"] . "." . $previewTimestamp["tm_hour"] . "." . $previewTimestamp["tm_min"] . "." . $previewTimestamp["tm_sec"];

        $body = file_get_contents("php://input");
        file_put_contents("archivecopy.json", $body);
        $fields = json_decode($body, true);
        $rcpt_to = $fields['0']['msys']['relay_message']['rcpt_to'];
        $friendly_from = $fields['0']['msys']['relay_message']['friendly_from'];
        $subject = $fields['0']['msys']['relay_message']['content']['subject'];
        $headers = $fields['0']['msys']['relay_message']['content']['headers'];
        $html = $fields['0']['msys']['relay_message']['content']['html'];
        $fileContent = $fields['0']['msys']['relay_message']['content']['email_rfc822'];
        $start = strpos($html, $inputField);
        $start = strpos($html, "value=", $start) + 7;
        $end = strpos($html, ">", $start) - 1;
        $length = $end - $start;
        $UID = substr($html, $start, $length);

        get_important_headers($headers, $original_to, $headerDate, $subject, $from);
    }
}

function MYSQLLog ($original_to, $headerDate, $subject, $from, $ArchiveDirectory, $UID, $ArchiveTextLog, $sendDateTime, $servername, $username, $password)
{
// This not only creates the crossref row in SQL but also logs similar data into
// a text file.  Make sure you rotate or clean out the text file once in a while.

    // Create connection
    $conn = mysqli_connect($servername, $username, $password);

    // Check connection
    if (!$conn) {
        $archive_output = sprintf("\n\n>>>>>\nMySQL connection failed connecting to MYSQL to log S3 data entry:%-200s\nTo: %-50s\n From: %-50s\n Subject: %-200s\n HeaderTimeStamp: %-42s\n ArchiveTimeStamp: %-38s\n ArchiveDirectory: %s\n UID: %-60s\n>>>>>", $conn->error, $original_to, $from, $subject, $headerDate, $sendDateTime, $ArchiveDirectory, $UID);
    }
    else
    {
        $sql = "INSERT INTO austein_archiver.tracker (ToEmailAddress, Date, Subject, FromAddress, directory, UID) VALUES ('" . $original_to . "', '" . $headerDate . "', '" . $subject . "','" . $from . "', '" . $ArchiveDirectory . "', '" . $UID . "')";

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

// Initialize
$verb = $_SERVER['REQUEST_METHOD']; 
        
$ArchiveDirectory = $config['archive']['ArchiveDirectory']; 
$ArchiveTextLog = $config['archive']['ArchiveLogName'];
$DefaultTimeZone = $config['archive']['DefaultTimeZone'];
$servername = $config['mysql']['servername'];
$username = $config['mysql']['username'];
$password = $config['mysql']['password'];
$inputField = $config['hiddenID']['inputField'];


// Process workflow
extract_data($verb, $fileContent, $DefaultTimeZone, $original_to, $headerDate, $subject, $from, $sendDateTime, $inputField, $UID);
$fileName = $ArchiveDirectory . '/' . $UID . '.eml';
S3upload ($fileName, $fileContent);
MySQLlog ($original_to, $headerDate, $subject, $from, $ArchiveDirectory, $UID,  $ArchiveTextLog, $sendDateTime, $servername, $username, $password);

?>
