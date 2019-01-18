<?php

$config = require('config.php');

$processingDirectory = $config['processing']['Directory']; 
$metaField = $config['processing']['field'];
$logCC = $config['processing']['CCFlag'];
$logBCC = $config['processing']['BCCFlag'];
$servername = $config['mysql']['servername'];
$username = $config['mysql']['username'];
$password = $config['mysql']['password'];
$loggingFlag = $config['mysql']['LoggingFlag'];
$top = "msys";

$fileList = scandir ($processingDirectory);
$fileCount = count($fileList);

for ($i=2; $i<$fileCount; $i++)
{
	$currentFile = $fileList[$i];
	$currentFile = $processingDirectory . "/" . $currentFile;
	if (!strpos($currentFile, ".lck"))
	{
		$lockFile = $currentFile . ".lck";
		$success = rename($currentFile, $lockFile);
		if ($success)
		{
			$deleteFile = true;
			$array = file_get_contents($lockFile);
			$arrayContent = json_decode($array, TRUE);
			foreach ($arrayContent as $item)
			{
				$store = false;
				$event_array = $item[$top];
				reset($event_array);
				$event_key = key($event_array);
				$uid = $item[$top][$event_key]["rcpt_meta"][$metaField];
				$rcpt_type = $item[$top][$event_key]["rcpt_type"];
				$campaign_id = $item[$top][$event_key]["campaign_id"];
				$friendly_from = $item[$top][$event_key]["friendly_from"];
				$rcpt_to = $item[$top][$event_key]["rcpt_to"];
				$subject = $item[$top][$event_key]["subject"];
				$event_type = $item[$top][$event_key]["type"];
				$injection_time = $item[$top][$event_key]["injection_time"];
				$metadata_string = json_encode($item[$top][$event_key]["rcpt_meta"]);
				$raw = json_encode($item, JSON_HEX_APOS );
				if (!$rcpt_type) $rcpt_type = "original";
				if ($uid)
				{
					switch (true)
					{
						case ($rcpt_type == "original" | $rcpt_type == "archive"):
							$store = true;
							break;
						case ($rcpt_type == "cc" && $logCC):
							$store = true;
							break;
						case ($rcpt_type == "bcc" && $logBCC):
							$store = true;
							break;
					}
				}
				if ($store)
				{
    				// Create connection
    				$conn = mysqli_connect($servername, $username, $password);

    				// Check connection
    				if (!$conn) 
    				{
        				if ($loggingFlag) $archive_output = sprintf("\n\n>>>>>MySQL connection failed connecting to MYSQL to log S3 entry:%-200s\nTo: %-50s\n From: %-50s\n Subject: %-200s\n InjectionTime: %-42s\n UID: %-38s\n Event Type: %-38s\n ArchiveFileName: %s>>>>>", $conn->error, $rcpt_to, $friendly_from, $subject, $injection_time, $uid, $event_type, $currentFile);
    				    $deleteFile = false;
    				}
    				else
    				{
        				$sql = "INSERT INTO austein_archiver.events (campaign_id, friendly_from, injection_time, rcpt_to, rcpt_type, subject, UID, event_type, raw) VALUES ('" . $campaign_id . "', '" . $friendly_from . "', '" . $injection_time . "', '" . $rcpt_to . "', '" . $rcpt_type . "', '" . $subject . "', '" . $uid . "', '" . $event_type . "', '" . $raw . "')";
        				if ($conn->query($sql) === TRUE) 
        				{
            				if ($loggingFlag) $archive_output = sprintf("\n\n>>>>>To: %-50s\n From: %-50s\n Subject: %-200s\n InjectionTime: %-38s\n UID: %-38s\n Event Type: %-38s\n ArchiveFileName: %s>>>>>", $rcpt_to, $friendly_from, $subject, $injection_time, $uid, $event_type, $currentFile);
        				} 
        				else 
        				{
            				if ($loggingFlag) $archive_output = sprintf("\n\n>>>>>MySQL insert failure to MYSQL Event Table:%-200s\nTo: %-50s\n From: %-50s\n Subject: %-200s\n InjectionTime: %-38s\n UID: %-38s\n Event Type: %-38s\n ArchiveFileName: %s>>>>>", $conn->error, $rcpt_to, $friendly_from, $subject, $injection_time, $uid, $event_type, $currentFile);
            				$deleteFile = false;
        				}
        				mysqli_close($conn);
    				}
    				file_put_contents("eventLog.txt", $archive_output, LOCK_EX | FILE_APPEND);
				}
			}
			if ($deleteFile) unlink($lockFile);
			//if ($deleteFile) rename($lockFile, $currentFile);  //Use for testing if you want to keep the file for further tests
		}
	}
}
?>
