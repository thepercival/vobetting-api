<?php

namespace App;

use Psr\Container\ContainerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Log\LoggerInterface;
use Selective\Config\Configuration;
use Symfony\Component\Console\Command\Command as SymCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

class Command extends SymCommand
{
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var Mailer
     */
    protected $mailer;
    /**
     * @var Configuration
     */
    protected $config;

    public function __construct(Configuration $config)
    {
        $this->config = $config;
        parent::__construct();
    }

    protected function configure()
    {
        $this->addOption('logtofile', null, InputArgument::OPTIONAL, 'logtofile?');
    }

    protected function initLogger(InputInterface $input, string $name)
    {
        $logToFile = $input->hasOption('logtofile') ? filter_var(
            $input->getOption('logtofile'),
            FILTER_VALIDATE_BOOLEAN
        ) : false;
        $loggerSettings = $this->config->getArray('logger');

        $this->logger = new Logger($name);
        $processor = new UidProcessor();
        $this->logger->pushProcessor($processor);

        $path = $logToFile ? ($loggerSettings['path'] . $name . '.log') : 'php://stdout';
        $handler = new StreamHandler($path, $loggerSettings['level']);
        $this->logger->pushHandler($handler);
    }

    protected function initMailer(LoggerInterface $logger)
    {
        $emailSettings = $this->config->getArray('email');
        $this->mailer = new Mailer(
            $logger,
            $emailSettings['from'],
            $emailSettings['fromname'],
            $emailSettings['admin']
        );
    }
}
