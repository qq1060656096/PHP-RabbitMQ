RabbitMQ 是一个消息中间件: 它接受和转发消息。你可以看看邮局，当你要发邮件时，你会想到把邮件放到邮箱里面。可以肯定的是，快递员最终会把邮件给收件人。在这个比喻中。RabbitMQ 是邮箱、邮政局、快递员。

RabbitMQ和邮局之间的主要区别是,它不处理,相反,它接受数据,消息的存储和转发的二进制文件。

RabbitMQ和消息传递在一般情况下,使用一些术语。

生产方式发送。一个程序发送消息是一个生产者

一个队列的名称在RabbitMQ中是一个邮箱.尽管通过RabbitMQ消息流和应用程序,他们只能存储在一个队列.队列只受主机的内存和磁盘限制,它本质是一个大消息缓冲区。很多生成者可以蒋消息发送到一个队列,和许多消费者可以从一个队列接受数据。这就是我们如何描述一个队列。

消费也有类似的意义。消费者大多是一个程序,等待接收消息。

在这部分的教程中我们将用PHP编写两个程序,发送一个消息的生产者,消费者接收信息并打印出来。我们会掩盖一些细节的php-amqplib API,把精力集中在这个非常简单的事情开始。这是一个“Hello World”的消息。

在下面的图中,“P”是我们的生产和“C”是我们消费者。中间的框是一个队列,消息缓冲RabbitMQ代表消费者。

![img](https://qq1060656096.github.io/images/rabbitmq/3-1.png)

### 1步
使用"composer"引入"php-amqplib/php-amqplib"库

```php
{
    "require": {
        "php-amqplib/php-amqplib": ">=2.6.1"
    }
}
```

安装好"php-amqplib",我们可以编写一些代码

### 2 步
>生产者

![img](https://qq1060656096.github.io/images/rabbitmq/3-2.png)


我们叫消息发布者(sender)"send.php",消息接受者叫"receive.php".生产者蒋连接到RabbitMQ发送一条消息并退出

在"send.php"，我们必须包含必须的库和必要的类

```php
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
// 创建好服务器连接后
$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');

// 我们必须创建"channel"通道和声明队列名和发送消息到队列中 
$channel = $connection->channel();
$channel->queue_declare('hello', false, false, false, false);

$msg = new AMQPMessage('Hello World!');
$channel->basic_publish($msg, '', 'hello');
echo " [x] Sent 'Hello World!'\n";
// 最后关闭通道和连接
$channel->close();
$connection->close();
```

### 3步
> 接受者

![img](https://qq1060656096.github.io/images/rabbitmq/3-3.png)



我们从RabbitMQ接受消息,不同的生成者发送消息,
我们保持运行监听消息并打印出来。

在"receive.php"，我们必须包含必须的库和必要的类


```php
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->queue_declare('hello', false, false, false, false);

echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

$callback = function($msg) {
  echo " [x] Received ", $msg->body, "\n";
};

$channel->basic_consume('hello', '', false, true, false, false, $callback);

while(count($channel->callbacks)) {
    $channel->wait();
}
```

现在我们在命令行(终端)运行2个脚本

```php
php receive.php
```

```php
php send.php
```
消费者将打印消息从发送方通过RabbitMQ。接收者将继续运行,等待消息(使用ctrl - c来阻止它),所以尝试发送方从另一个终端上运行。



您可能希望看到队列RabbitMQ和有多少消息。你可以使用rabbitmqctl工具(如特权用户):

```php
# linux使用命令
sudo rabbitmqctl list_queues

# windows使用命令
rabbitmqctl.bat list_queues
```