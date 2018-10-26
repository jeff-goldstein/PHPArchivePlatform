<?php
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;

function S3upload ($fileName, $fileContent)
{

    require 'vendor/autoload.php';

    $config = require('config.php');

    $client = S3Client::factory(
    array(
        'key'    => $config['s3']['key'],
        'secret' => $config['s3']['secret'],
        'signature' => 'v4',
        'region' => 'us-east-2'
    ));

    try 
    {
	    $client->putObject(array(
        'Bucket'=>$config['s3']['bucket'],
        'Key' =>  "{$fileName}",
        'Body' => $fileContent,
        'StorageClass' => 'REDUCED_REDUNDANCY',
        'ACL' => 'public-read'
        ));
    } 
    
    catch (S3Exception $e) 
    {
    // Catch an S3 specific exception.
    echo $e->getMessage();
    }
}
?>