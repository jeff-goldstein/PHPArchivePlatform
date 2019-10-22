<?php
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;

require 'vendor/autoload.php';

$config = require('config.php');
$archiveDir = $config['archive']['ArchiveDirectory'];
$tempHTMLDir = $config['reporting']['BuiltHTMLTempStore'];
$UID = $_POST["UID"];
$servername = $config['mysql']['servername'];
$username = $config['mysql']['username'];
$password = $config['mysql']['password'];

if (!is_dir($tempHTMLDir)) 
{
    // dir doesn't exist, make it
    mkdir($tempHTMLDir);
}

$UIDFile = $archiveDir . "/" . $UID . '.html'; 

$client = S3Client::factory(
array(
    'key'    => $config['s3']['key'],
    'secret' => $config['s3']['secret'],
    'signature' => 'v4',
    'region' => 'us-east-2'
));
$tempStoreFile = $tempHTMLDir . "/tmp" . rand() . ".html";
$FileHandle = fopen($tempStoreFile, 'w+');
try 
{
    $results = $client->getObject([
        'Bucket'=>$config['s3']['bucket'],
        'Key' =>  "{$UIDFile}"]);
    header("Content-Type: {$results['ContentType']}");
    // echo $tempStoreFile; //need to create a structure to send back
    file_put_contents($tempStoreFile, $results['Body']);
} 

catch (S3Exception $e) 
{
    // Catch an S3 specific exception.
    echo "error: ";
    echo $e->getMessage();
}
fclose($FileHandle);

$sql = 'SELECT * FROM austein_archiver.tracker WHERE UID = ' . $UID;

$conn = mysqli_connect($servername, $username, $password);

// Check connection
if (!$conn) 
{
    if ($loggingFlag) $archive_output = sprintf("\n\n>>>>>MySQL connection failed connecting to MYSQL to obtain headers :%-200s\nUID: %-50s", $conn->error, $UID);
}
else
{
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $headers = $row["headerstring"];
    file_put_contents("headers.txt", $headers);
}

$returnArray = array('tempStorefile' => $tempStoreFile, 'headers' => $headers);
echo json_encode($returnArray);

?>