<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 7-3-18
 * Time: 8:10
 */

function mailAdmin( $errorMessage )
{
    $subject = 'fout bij ' . __FILE__;
    $body = '
        <p>Hallo,</p>
        <p>            
        Onderstaande fout heeft zich voorgedaan bij de cronjob updateBetLines: ' . $errorMessage . '.
        </p>
        <p>
        met vriendelijke groet,
        <br>
        VOBetting
        </p>';

    $from = "VOBetting";
    $fromEmail = "noreply@VOBetting.nl";
    $headers  = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8" . "\r\n";
    $headers .= "From: ".$from." <" . $fromEmail . ">" . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    $params = "-r ".$fromEmail;

    if ( !mail( 'coendunnink@gmail.com', $subject, $body, $headers, $params) ) {
        // $app->flash("error", "We're having trouble with our mail servers at the moment.  Please try again later, or contact us directly by phone.");
        error_log('Mailer Error!' );
        // $app->halt(500);
    }
}