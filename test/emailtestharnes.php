<?php
{ 
for ($i=1; $i<20; $i++)
{
  $randomuid = rand();
  $randomselect = rand(1,6);
  switch ($randomselect)
  {
    case 1: 
      $emailbody = "<html><body>Dear Jeff,<br><p>We are  pleased to welcome you as a privileged visitor. The username you have chosen to use to log in to your customer account is email.goldstein@gmail.com .<br><p>For security reasons, we will never send you your password. Be sure to memorize it.";
      $emailbody .= '<p>Hope to see you again soon on bbqforbikers.com!<br>Your biking bbq team!<input name="ArchiveCode" type="hidden" value="' . $randomuid . '"></body></html>';
      $subject = "Welcome to BBQing for Bikers";
      $campaignID = "Welcome";
      break;
    case 2: 
      $emailbody = file_get_contents("sparkpostnewsletter.html");
      $emailbody = str_replace("<randomcode>", $randomuid, $emailbody);
      $subject = "SparkPost Monthly Newletter";
      $campaignID = "Newletter";
      break;
    case 3: 
      $emailbody = file_get_contents("InsuranceIDCardNotice.html");
      $emailbody = str_replace("<randomcode>", $randomuid, $emailbody);
      $subject = "Policy No. " . $randomuid . " - Amica Mutual Insurance - ID Cards - secured";
      $campaignID = "ID Card Request";
      break;
    case 4: 
      $emailbody = file_get_contents("amexpaymentdue.html");
      $emailbody = str_replace("<randomcode>", $randomuid, $emailbody);
      $subject = "American Express Online Services";
      $campaignID = "Payment Reminder";
      break;
    case 5: 
      $emailbody = file_get_contents("hargreavesMonthly.html");
      $emailbody = str_replace("<randomcode>", $randomuid, $emailbody);
      $subject = "Your investment report";
      $campaignID = "Monthly Investment Report";
      break;
    case 6: 
      $emailbody = file_get_contents("virginbill.html");
      $emailbody = str_replace("<randomcode>", $randomuid, $emailbody);
      $subject = "Your Virgin Media bill is ready";
      $campaignID = "Bill Announcement";
      break;
  }

  file_put_contents('emailbody.html', $emailbody);
  
  $swakbody = file_get_contents("sendemail.orig");
  $swakbody = str_replace("<randomcode>", $randomuid, $swakbody);
  $swakbody = str_replace("<subject>", $subject, $swakbody);
  $swakbody = str_replace("<campaign_id>", $campaignID, $swakbody);

  file_put_contents('sendemail.sh', $swakbody);

  $output = shell_exec('./sendemail.sh');
}
}
?>
