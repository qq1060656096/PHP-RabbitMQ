<?php
require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->exchange_declare('logs', 'fanout', false, true, false);

list($queue_name, ,) = $channel->queue_declare("tmp", false, true, false, false);

//绑定队列到交换器
$channel->queue_bind($queue_name, 'logs');

echo ' [*] '.__FILE__.' Waiting for logs2. To exit press CTRL+C', "\n";

$callback = function($msg){
    global $argv;
    echo ' [x] ', $msg->body, "\n";
    sleep(2);

    echo "ack\n";
    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);


};

$channel->basic_consume($queue_name, '', false, false, false, false, $callback);

while(count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();