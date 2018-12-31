<?php
{
  for ($i=1; $i<11; $i++)
  {
    $random = rand();
    $emailbody = "<html><body>Dear {{firstname}},<br><p>We are  pleased to welcome you as a privileged visitor. The username you have chosen to use to log in to your customer account is email.goldstein@gmail.com .<br><p>For security reasons, we will never send you your password. Be sure to memorize it.";
    $emailbody .= '<p>Hope to see you again soon on bbqforbikers.com!<br>Your biking bbq team!<input name="ArchiveCode" type="hidden" value="' . $random . '"></body></html>';
    file_put_contents('emailbody.html', $emailbody);

    $swakbody = file_get_contents("sendemail.orig");

    $swakbody = str_replace("<randomcode>", $random, $swakbody);

    file_put_contents('sendemail.sh', $swakbody);

    $output = shell_exec('./sendemail.sh');
  }
}
?>
