# 发布/订阅

>发送一条消息,多个消费者处理

在前面的教程中,我们创建了一个工作队列。工作队列背后的假设是,每个任务是交付给一个工人。在本部分,我们将做一些完全不同的事情,我们会提供一个消息到多个消费者。这种模式被称为“发布/订阅”。

为了说明这个模式,我们将构建一个简单的日志系统。它将包括两个项目,第一个将发出日志消息,第二个将接收并打印它们。

在我们的日志系统每运行接收者项目将得到消息的副本。这样我们能够运行一个接收器和直接记录到磁盘,同时我们可以运行另一个接收器,看到屏幕上的日志。

本质上,发表日志消息将被广播给所有的接收器。

### 交换器
在前面的部分教程中我们与队列发送和接收消息。现在是时候在Rabbit介绍完整的消息传递模型。

> 让我们快速回顾我们在前面的教程:
> 1. 生产者(producer)是一个用户应用程序发送消息。
> 2. 队列是一个缓冲区,它存储信息。
> 3. 消费者(consumer)是一个用户应用程序接收消息。

RabbitMQ的消息模型的核心思想是,生产者没有直接向队列发送任何消息。实际上,经常生产者甚至不知道一个消息将传递给任何队列。

Producer发送的Message实际上是发到了Exchange中。它的功能也很简单：从Producer接收Message，然后投递到queue中。Exchange需要知道如何处理Message，是把Message放到特定queue中，还是放到多个queue中？或者丢弃.这个rule是通过Exchange 的type定义的。

![img](https://qq1060656096.github.io/images/rabbitmq/5-1.png)



有一些可用的交换类型: "direct", "topic", "headers"和"fanout"。我们将关注最后一个——"fanout"。让我们创建一个该类型的交换,称之为logs

```php
channel.exchange_declare(exchange='logs', type='fanout')
```

fanout exchange非常简单。您可能会猜测的名字,fanout就是广播模式,广播所有的消息到它知道所有队列。而这正是我们需要为我们的logger。

> ### exchange列表
> 列出服务器上的交流有用rabbitmqctl您可以运行:

```php
sudo rabbitmqctl list_exchanges
```

这个列表中会有一些amq。*交流与默认(匿名)交换。这些都是默认创建,但不太可能你需要使用它们。

### 默认交换器
在前面的部分教程中我们一无所知交往,但仍然能够将消息发送到队列。这是可能的因为我们是使用一个默认的交换,我们确定的空字符串(" ")。
记得之前我们发布一个消息:

```php
channel.basic_publish(exchange='', routing_key='hello', body=message)
```

> exchange参数是交换器的名称。空字符串表示默认或无名的交换:消息路由到指定的队列的名称routing_key,如果它存在。

现在,我们可以发表我们命名为交换而不是:

```php
channel.basic_publish(exchange='logs', routing_key='', body=message)
```

### 临时队列

你可能还记得以前我们使用队列有一个指定的名称(还记得hello和task_queue吗?)。能够说出一个队列是至关重要的,我们需要点工人相同的队列。给一个队列的名字是很重要的,当你想在生产者和消费者之间共享队列。

但这并不是我们的记录器。我们想要听到的所有日志消息,不仅仅是其中的一个子集。我们也只对当前交换器感兴趣而不是旧的消息。解决,我们需要两件事情。

首先,每当我们连接到Rabbit我们需要一个新鲜的、空的队列。去做我们可以创建一个队列和一个随机的名字,或者,甚至更好,让服务器选择一个随机队列名称。我们不可以通过提供queue_declare队列参数:

```php
result = channel.queue_declare()
```

在这一点上result.method。队列包含随机队列名称。例如它可能看起来像amq.gen-JzTY20BRgKO-HjmUJj0wLg。

其次,一旦我们消费者应该删除队列断开。有一个专属标志:

```php 
result = channel.queue_declare(exclusive=True)
```

# 捆绑

![img](https://qq1060656096.github.io/images/rabbitmq/5-2.png)

我们已经创建了一个展开交流和一个队列。现在我们需要告诉交换将消息发送到队列。交易所之间的关系和一个队列称为绑定。

```php
channel.queue_bind(exchange='logs', queue=result.method.queue)
```

从现在起日志交易所将附加消息队列。


> ### 绑定列表
> 你能列出现有的绑定使用,你猜对了.

```php
rabbitmqctl list_bindings
```

### 把它放在一起
![img](https://qq1060656096.github.io/images/rabbitmq/5-3.png)

生产者程序发出日志消息,看起来不不同于前面的教程。最重要的变化是,现在我们想发布消息日志交流而不是无名。这里是emit_log的代码emit_log.php脚本:

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->exchange_declare('logs', 'fanout', false, false, false);

$data = implode(' ', array_slice($argv, 1));
if(empty($data)) $data = "info: Hello World!";
$msg = new AMQPMessage($data);

$channel->basic_publish($msg, 'logs');

echo " [x] Sent ", $data, "\n";

$channel->close();
$connection->close();

```

如你所见,建立连接后,我们宣布交换。这一步是必要的发布到一个不存在的交易是被禁止的。
信息将丢失如果没有队列绑定到交易所,但那是为我们好,如果没有消费者听我们可以安全地丢弃消息。
receive_logs.php的代码:

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->exchange_declare('logs', 'fanout', false, false, false);

list($queue_name, ,) = $channel->queue_declare("", false, false, true, false);

$channel->queue_bind($queue_name, 'logs');

echo ' [*] Waiting for logs. To exit press CTRL+C', "\n";

$callback = function($msg){
  echo ' [x] ', $msg->body, "\n";
};

$channel->basic_consume($queue_name, '', false, true, false, false, $callback);

while(count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();
```

如果你想将日志保存到一个文件,打开一个控制台和类型:

```php
php receive_logs.php > logs_from_rabbit.log
```

如果你想看到登录屏幕,产生一个新的终端并运行:

```php
php receive_logs.php
```

当然,发出日志类型:

```php
php emit_log.php
```

使用rabbitmqctl list_bindings实际上您可以验证代码创建绑定和队列,因为我们想要的。有两个receive_logs。您应当会看到类似运行php程序

```php
sudo rabbitmqctl list_bindings
```

结果的解释很简单:数据与服务器交换日志去两个队列的名称。而这正是我们的目的。