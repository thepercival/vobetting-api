<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 30-1-17
 * Time: 12:48
 */

namespace App;

use Psr\Log\LoggerInterface;

final class Mailer
{
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var string
     */
    private $fromEmailaddress;
    /**
     * @var string
     */
    private $fromName;
    /**
     * @var string
     */
    protected $adminEmailaddress;

    public function __construct(
        LoggerInterface $logger,
        string $fromEmailaddress,
        string $fromName,
        string $adminEmailaddress
    ) {
        $this->logger = $logger;
        $this->fromEmailaddress = $fromEmailaddress;
        $this->fromName = $fromName;
        $this->adminEmailaddress = $adminEmailaddress;
    }

    public function send(string $subject, string $body, string $toEmailaddress)
    {
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8" . "\r\n";
        $headers .= "From: " . $this->fromName . " <" . $this->fromEmailaddress . ">" . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        $params = "-r " . $this->fromEmailaddress;

        if (!mail($toEmailaddress, $subject, $body, $headers, $params)) {
            // $app->flash("error", "We're having trouble with our mail servers at the moment.  Please try again later, or contact us directly by phone.");
            $this->logger->error('Mailer Error for ' . $toEmailaddress);
            // $app->halt(500);
        }
    }

    public function sendToAdmin(string $subject, string $body)
    {
        $this->send($subject, $body, $this->adminEmailaddress);
    }
}
