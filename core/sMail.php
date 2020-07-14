<?php
require_once('Mail/class.phpmailer.php');

function sMail($to,$fromname, $sub, $msg="", $replytoname, $replyto, $filepath = ''){
    $mail = new PHPMailer(true);
    $mail->IsSMTP(); // telling the class to use SMTP
    //$mail->Host       = "mail.yourdomain.com"; // SMTP server
     //$mail->SMTPDebug  = 4;                     // enables SMTP debug information (for testing)
    // 1 = errors and messages
    // 2 = messages only
    $mail->SMTPAuth   = true;                  // enable SMTP authentication
    $mail->SMTPSecure = "tls";                 // sets the prefix to the servier
    $mail->Host       = "smtp.sendgrid.net";      // sets GMAIL as the SMTP server
    //$mail->Host       = "smtp.gmail.com";      // sets GMAIL as the SMTP server
    $mail->Port       =  587;                   // set the SMTP port for the GMAIL server
    $mail->Username   = USER_NAME;  // GMAIL username
    $mail->Password   =  PASSWORD;            // GMAIL password

    //$mail->Username   = USER_NAME;  // GMAIL username
    // $mail->Password   =  PASSWORD;            // GMAIL password

    $mail->SetFrom("no-reply@psbloansin59minutes.com", "PSB Loans in 59 Minutes");

    $mail->AddReplyTo($replyto,$replytoname);

    $mail->Subject  = $sub;

    //$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
    $mail->IsHTML(true);
    $mail->MsgHTML($msg);

    $mail->FromName   = $fromname;
    foreach($to as $key => $value){
        $mail->AddAddress($value,$key);
    }
    //  $mail->AddAttachment($filepath);      // attachment
    //$mail->AddAttachment(""); // attachment
//    $mail->SMTPDebug = 1;
    if(!$mail->Send()) {
        echo "Mailer Error: " . $mail->ErrorInfo;
        return false;
    } else {
        return true;
    }
}
