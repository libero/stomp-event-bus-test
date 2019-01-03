<?php

use Stomp\Client;
use Stomp\Network\Connection;
use Stomp\SimpleStomp;
use Stomp\Transport\Message;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

require_once 'vendor/autoload.php';

$consume = function (InputInterface $input, OutputInterface $output) : void {
    $client = new Client(new Connection($_ENV['BROKER']));
    $client->setVhostname($_ENV['VHOST']);
    $client->setLogin($_ENV['LOGIN'], $_ENV['PASSCODE']);
    $client->setClientId($_ENV['CLIENT_ID'] ?? null);
    $stomp = new SimpleStomp($client);

    $destination = $_ENV['TOPIC_ROOT'].$input->getArgument('destination');

    for ($i = 0; $i < 10; $i++) {
        $stomp->send($destination, new Message($message = $input->getArgument('destination').' message '.md5(uniqid
            ('', true))));
        $output->writeln("Sent {$message}");
    }
};

(new Application())
    ->register('send')
    ->addArgument('destination', InputArgument::REQUIRED)
    ->setCode($consume)
    ->getApplication()
    ->setDefaultCommand('send', true)
    ->run();
