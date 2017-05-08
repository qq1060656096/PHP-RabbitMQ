<?php
require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';


use PhpAmqpLib\Connection\AMQPStreamConnection;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->exchange_declare('direct_logs', 'direct', false, false, false);

list($queue_name, ,) = $channel->queue_declare("info_warning", false, true, false, false);




$channel->queue_bind($queue_name, 'direct_logs', 'info');
$channel->queue_bind($queue_name, 'direct_logs', 'warning');


echo ' [*] info and warning Waiting for logs. To exit press CTRL+C', "\n";
$i = 1;
$callback = function($msg){
    sleep(3);
    global $i;
    echo ' [x] ',$i,' ',$msg->delivery_info['routing_key'], ':', $msg->body, "\n";
    $i++;
    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
};

$channel->basic_consume($queue_name, '', false, false, false, false, $callback);

while(count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();