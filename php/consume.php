<?php

use Stomp\Client;
use Stomp\Network\Connection;
use Stomp\SimpleStomp;
use Stomp\Transport\Frame;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

require_once 'vendor/autoload.php';

$consume = function (InputInterface $input, OutputInterface $output) : void {
    $connection = new Connection($_ENV['BROKER']);
    $connection->setReadTimeout(1);
    $client = new Client($connection);
    $client->setVhostname($_ENV['VHOST']);
    $client->setLogin($_ENV['LOGIN'], $_ENV['PASSCODE']);
    $stomp = new SimpleStomp($client);

    $ack = 0;
    $nack = 0;
    $unack = 0;

    if (!$client->isConnected()) {
        $client->connect();
        $output->writeln("Connected");
    }

    subscribe:

    $stomp->subscribe($_ENV['DESTINATION'], $_ENV['SUBSCRIPTION'], 'client-individual');

    $output->writeln("Subscribed");

    while (true) {
        $frame = $stomp->read();

        if ($frame instanceof Frame) {
            if (random_int(1, 10) <= 1) {
                $unack++;
                $output->write("Did not acknowledge {$frame->body}");
            } elseif (random_int(1, 10) <= 5) {
                $stomp->ack($frame);
                $ack++;
                $output->write("Consumed {$frame->body}");
            } else {
                $stomp->nack($frame);
                $nack++;
                $output->write("Failed on {$frame->body}");
            }
            $output->writeln(" (ack {$ack}, nack {$nack}, unack {$unack})");
        }

        if (random_int(1, 10) <= 1) {
            if (random_int(1, 10) <= 1) {
                $client->disconnect(true);
                $output->writeln("Disconnected");
            } else {
                $stomp->unsubscribe($_ENV['DESTINATION'], $_ENV['SUBSCRIPTION']);
                $output->writeln("Unsubscribed");
            }
            sleep(3);
            goto subscribe; // Here be dinosaurs.
        }
    }
};

(new Application())
    ->register('consume')
    ->setCode($consume)
    ->getApplication()
    ->setDefaultCommand('consume', true)
    ->run();
