<?php
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;

require 'vendor/autoload.php';

$config = require('config.php');
$UIDFile = "uploads/" . $_POST["UID"] . '.html'; 

$client = S3Client::factory(
array(
    'key'    => $config['s3']['key'],
    'secret' => $config['s3']['secret'],
    'signature' => 'v4',
    'region' => 'us-east-2'
));
$tempStoreFile = "tmp/tmp" . rand() . ".html";
$FileHandle = fopen($tempStoreFile, 'w+');
try 
{
    $results = $client->getObject([
        'Bucket'=>$config['s3']['bucket'],
        'Key' =>  "{$UIDFile}"]);
    header("Content-Type: {$results['ContentType']}");
    echo $tempStoreFile;
    file_put_contents($tempStoreFile, $results['Body']);
} 

catch (S3Exception $e) 
{
    // Catch an S3 specific exception.
    echo "proof baby: ";
    echo $e->getMessage();
}
fclose($FileHandle);

?>
