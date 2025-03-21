<?php

require 'vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$queue = 'default';
$channel->queue_declare($queue, false, true, false, false);

$message = new AMQPMessage(json_encode(['test' => 'message']));
$channel->basic_publish($message, '', $queue);

echo "Message published to RabbitMQ.\n";

$channel->close();
$connection->close();