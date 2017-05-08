<?php
require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
// 创建好服务器连接后
$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');

// 我们必须创建"channel"通道和声明队列名和发送消息到队列中
$channel = $connection->channel();
$channel->queue_declare('hello', false, false, false, false);

$msg = new AMQPMessage('Hello World!'.date('Y-m-d H:i:s'));
$channel->basic_publish($msg, '', 'hello');
echo " [x] Sent '{$msg->body}'\n";
// 最后关闭通道和连接
$channel->close();
$connection->close();