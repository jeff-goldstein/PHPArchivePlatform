<?php
// Include the SDK using the Composer autoloader
require 'vendor/autoload.php';
require 's3.php';
$config = require('config.php');
$emailAddress = $_POST["emailaddress"];
$theme = $_POST["theme"];
if (!$theme) $theme = "wide";
$sdate = $_POST["sdate"];
$edate = $_POST["edate"] . "T23:59:59";
$searchId = $_POST["searchId"];

$servername = $config['mysql']['servername'];
$username = $config['mysql']['username'];
$password = $config['mysql']['password'];
$loggingFlag = $config['mysql']['LoggingFlag'];
$displayLimit = $config['processing']['DisplayLimit'];
$top = "msys";

function secondsToTime($seconds) 
{
    $dtF = new \DateTime('@0');
    $dtT = new \DateTime("@$seconds");
    return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
}

if ($searchId == "") $sql = 'SELECT * FROM austein_archiver.events WHERE rcpt_to = "' . $emailAddress . '" and injection_time > "' . $sdate . '" and injection_time < "' . $edate . '" order by injection_time desc';
else $sql = 'SELECT * FROM austein_archiver.events WHERE UID = "' . $searchId . '" and injection_time > "' . $sdate . '" and injection_time < "' . $edate . '" order by injection_time desc';

if ($theme == "wide")
{
    $tabledetails = "<tbody>";
}
else
{
    $tabledetails = "<style>#rows td {padding-bottom: 5px;color: black;}
    table {border-collapse: collapse; border: 1px}</style>
    <table id='summary'><tr><td style='width:30%; vertical-align:top;'>
    <table id='rows' style='width:100%'>
    <tbody>";
}

$conn = mysqli_connect($servername, $username, $password);

// Check connection
if (!$conn) 
{
    if ($loggingFlag) $archive_output = sprintf("\n\n>>>>>MySQL connection failed connecting to MYSQL to log S3 entry:%-200s\nTo: %-50s\n From: %-50s\n Subject: %-200s\n InjectionTime: %-42s\n UID: %-38s\n Event Type: %-38s\n ArchiveFileName: %s>>>>>", $conn->error, $rcpt_to, $friendly_from, $subject, $injection_time, $uid, $event_type, $currentFile);
    $deleteFile = false;
}
else
{
    $result = $conn->query($sql);
    if ($result->num_rows > 0) 
    {
        if ($result->num_rows <= $displayLimit)
        {
            $notes = "Retrieval Done... ". $result->num_rows . " returned";
            // output data of each row
            $index = 0;
            while($row = $result->fetch_assoc()) 
            {
                $row["injection_time"] = substr_replace($row["injection_time"], "", -5);
                $row["injection_time"] = str_replace("T", " ", $row["injection_time"]);
                if($theme == "wide") 
                {
                    $tabledetails .= "<tr class='hoverrow'><td style='width:15px; cursor:pointer;' onclick='show_details(" . '"' . $index . '"' . ")'><center><input style='background-color: #E8F0FE;cursor:pointer;' title='Select to see the actual email and further details on this event' id='detailcheck' name='detailcheck' type='radio'>" . "</center></td><td style='padding: 5px;'>" . $row["subject"]. "</td><td style='padding: 5px;'>" . $row["injection_time"]. "</td><td style='padding: 5px;'>" . $row["event_time"]. "</td><td style='padding: 5px;'>" . $row["campaign_id"]. "</td><td style='padding: 5px;'>" . $row["event_type"]. "</td>";
                    $tabledetails .= "<td title='This indicates if this record represents an action on the original email, archive duplicate email, cc or bcc' style='padding: 5px;'>" . $row["rcpt_type"] . "</td><td style='padding: 5px;'>" . $row["rcpt_to"] . "</td>";
                    $tabledetails .= "<td><center>" .  "<input title='Select to retrieve all data on this UID' type='button' style='-webkit-border-radius: 75%; padding:5px; color: #000000; font-family: Helvetica, Arial; font-weight: bold; font-size: 12px; background-color: #fff6e6;' value='" . $row["UID"] . "' onclick=" . '"getEmailEvents(' . "'UID'," . $row["UID"] . ')"' . "></center></td><td hidden>" . $row["UID"] . "</td><td hidden>" . $row["raw"] . "</td></tr>";
                }
                else 
                {
                    $timeElapse = secondsToTime(strtotime($row["event_time"]) - strtotime($row["injection_time"]));
                    $tabledetails .= "<tr><td style='width:15px; cursor:pointer;' onclick='show_details(" . '"' . $index . '"' . ")'><center><input style='background-color: #E8F0FE;cursor:pointer;' title='Select to see the actual email and further details on this event' id='detailcheck' name='detailcheck' type='radio'>" . "</center></td>";
                    $tabledetails .= "<td>Subject: " . $row["subject"] . "<br>Sent At: " . $row["injection_time"] . "<br>Event Happened At: " . $row["event_time"] .  "<br>Time Elapse: " . $timeElapse .  "<br>Campaign Id: " . $row["campaign_id"];
                    if ($emailAddress != $row["rcpt_to"]) $tabledetails .= "<br>" . $row["rcpt_to"] . "<br>";
                    $tabledetails .= "<table id='cnt' style='background-color:#fff6e6; width:100%'><tr><td title='Event Type' style='width:33%'>" . $row["event_type"] . "</td><td title='This indicates if this record represents an action on the original email, archive duplicate email, cc or bcc' style='width:33%'>" . $row["rcpt_type"] . "</td><td style='width:33%'><input type='button' title='Select to retrieve all data on this UID' style='-webkit-border-radius: 75%; padding:5px; color: #000000; font-family: Helvetica, Arial; font-weight: bold; font-size: 12px; background-color: #fff6e6;' value='" . $row["UID"] . "' onclick=" . '"getEmailEvents(' . "'UID'," . $row["UID"] . ')"'. "></td></tr></table></td>";
                    $tabledetails .= "<td hidden>" . $row["subject"] . "</td><td hidden>" . $row["injection_time"]. "</td><td hidden>" . $row["campaign_id"] . "</td><td hidden>" . $row["event_type"] . "</td><td hidden>" . $row["rcpt_type"] . "</td><td hidden>" . $row["UID"] . "</td><td hidden>" . $row["raw"] . "</td></tr>";
                }
                $index++;
            }
            if ($theme == "wide") $tabledetails .= "</tbody>";
            else $tabledetails .= "</tbody></table></td><td id='emailbodyanddetails' style='vertical-align:top; background-color:#fff6e6'></td></tr><table>";
        }
        else
        {
            $notes = "Retrieval Done... ". $result->num_rows . " returned but is more than the configured limit of " . $displayLimit . ".  Please scope down search dates requested.";
        }
    } 
    else 
    {
        if ($edate <= $sdate) $notes = "Retrieval Done... ". $result->num_rows . " returned.  Start date must be before end date.";
        else $notes = "Retrieval Done... ". $result->num_rows . " returned.  Maybe a larger date sampling will work.";
    }
    mysqli_close($conn);
    $tabledetails .= "<input id='drilleddownUID' type=hidden value='" . $searchId . "'>";
}
$json_array=array(
    'details' => $tabledetails,
    'error' => $responses,
    'url' => $url,
    'notes' => $notes
);
$json_encoded_string = json_encode ($json_array);
echo $json_encoded_string;
?>