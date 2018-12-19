<?php

return [
's3' => [
	'key' => '<s3 Access key id>',
	'secret' => '<Secret access key>',
	'bucket' => '<bucket name>'],
'archive' => [
	'ArchiveDirectory' => 'uploads',
	'ArchiveLogName' => 'Archivelog.txt',
	'MaxArchiveLogSize' => '15000000',
	'DefaultTimeZone' => 'America/Los_Angeles'],
'mysql' => [
	'servername' => 'localhost',
	'username' => '<mysql username>',
	'password' => '<mysql password>'],
'processing' => [
	'Directory' => 'tempStorage',
	'field' => 'uid',
	'CCFlag' => TRUE,
	'BCCFlag' => TRUE,
	'LoggingFlag' => TRUE]
];
?>
