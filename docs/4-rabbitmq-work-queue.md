# 工作队列

> 轮循分配或者工人分配任务(竞争消费者模式)

![img](https://qq1060656096.github.io/images/rabbitmq/4-1.png)


在第一教程中我们写程序从一个命名队列中发送和接受消息,在这里我们将创建一个工作队列,用于分发耗时的任务,在多个工人.

背后的主要思想工作队列(又名:任务队列)是为了避免立即做一个资源密集型任务,不得不等待它完成。相反,我们安排以后的任务要做。我们封装任务作为消息并将其发送到一个队列。一个工作进程在后台运行将流行的任务和最终执行这项工作。当您运行许多工人的任务将在他们之间共享。

这个概念是特别有用的web应用程序中处理复杂的任务是不可能在一个短的HTTP请求窗口。

在本教程的前一部分我们发送一个包含“Hello World !”消息。现在我们将发送字符串代表复杂的任务。我们没有一个真实的任务,如图片的大小或pdf文件呈现,我们假的,只是假装很忙——通过使用睡眠()函数。点的数量我们将字符串作为其复杂性;每点将占一秒钟的“工作”。例如,假的任务描述你好……需要三秒钟。

我们会稍微修改发送。php代码从我们之前的例子,允许任意从命令行发送消息。这个程序将在我们的工作计划任务队列,所以我们的名字new_task.php:


### 循环调度

使用一个任务队列的优点之一是能够轻易并行化"parallelise"工作。如果我们建立一个积压的工作,我们可以添加更多的工人,这样的规模很容易。
首先,让我们尝试运行两个work.php脚本在同一时间。他们都将从队列中获取消息,但如何?让我们来看看。
你需要打开3个控制台。两个运行work.php脚本。这些控制台将运行我们两个消费者- C1和C2。


默认情况下,RabbitMQ将发送每个消息到下一个消费者,在序列。平均每个消费者将获得相同数量的信息。这种方式分发消息称为循环。试试这三个或更多的工人。

### 消息答复

做一个任务可能需要几秒钟。你可能想知道如果一个消费者开始漫长的任务和死亡只有部分完成。与我们当前的代码,一旦RabbitMQ传递消息到客户立即删除它从内存。在这种情况下,如果你杀了一个工人,我们将失去消息只是处理。我们也会失去所有的消息被派往这个工人但尚未处理。

但是我们不想失去任何任务。如果一个工人死亡,我们想要交付的任务到另一个工人。

为了确保消息不会丢失,RabbitMQ支持消息应答。发送ack(nowledgement)从消费者告诉RabbitMQ特定的消息已经收到,处理和RabbitMQ自由删除它。

如果消费者死亡(其通道关闭,连接关闭,或TCP连接丢失)没有发送ack,RabbitMQ会理解,信息不完全,re-queue处理。如果有其他消费者同时在线,它会很快传递到另一个消费者。这样你可以确保不丢失信息,即使工人们偶尔也会死。

没有任何消息超时;RabbitMQ将再投递消息当消费者死亡。很好即使处理一条消息,很长一段时间。

消息确认默认是关闭的。是时候把他们的第四个参数设置为basic_consume为false(真意味着没有ack)从工人和发送适当的承认,一旦我们完成一个任务。

使用这个代码我们可以肯定,即使你杀了一个工人使用CTRL + C处理消息时,没有丢失。工人死亡后不久所有未确认的消息将被发送。


### 消息的耐久性
我们已经学会了如何确保即使消费者死亡,任务也不会丢失。但是我们的任务仍将失去如果RabbitMQ服务器停止。
当RabbitMQ辞职或崩溃,它将忘记,除非你告诉它不要队列和消息。两件事必须确保消息不会丢失:我们需要两个队列和消息标记为耐用。

```php 
$channel->queue_declare('hello', false, true, false, false);
```
尽管这个命令本身是正确的,它不会在我们目前的设置工作。这是因为我们已经定义了一个名为hello的队列不耐用。RabbitMQ不允许您重新定义现有的队列具有不同参数并返回一个错误的任何程序,试图这样做。但有一个快速解决方案——让我们声明一个队列使用不同的名称,例如task_queue:


```php
$channel->queue_declare('task_queue', false, true, false, false);
```
这个标志设置为true需要应用于生产者和消费者的代码。
在这一点上我们确信task_queue队列不会丢失,即使RabbitMQ重启。现在我们需要我们的消息标记为持久性——通过设置delivery_mode = 2消息属性AMQPMessage作为属性数组的一部分

```php
$msg = new AMQPMessage($data,
    array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
);
```
> ### 注意消息的持久性
> 将消息标记为持久性并不能完全保证信息不会丢失。虽然它告诉RabbitMQ将消息保存到磁盘,仍然有一个短的时间窗口当RabbitMQ已经接受消息和尚未保存它。RabbitMQ也不做fsync(2)每条消息——这可能只是保存到缓存和不写入磁盘。持久性保证不强,但它是足够为我们简单的任务队列。如果你需要一个更强的保证,那么你可以使用发布者证实。

### 公平的分配

您可能已经注意到,调度仍然没有完全按照我们想要的工作。例如在两名工人的情况,当所有奇怪的消息是沉重的,甚至消息是光,一名工人将会不停地忙,另一个几乎不做任何工作。RabbitMQ并不了解,仍将分派消息均匀。
这仅仅是因为RabbitMQ分派消息当进入队列的消息。它看起来不为消费者不被承认的消息的数量。它只是盲目地分派每n个消息第n个消费者。

![img](https://qq1060656096.github.io/images/rabbitmq/4-2.png)



为了打败,我们可以使用basic_qos方法 prefetch_count = 1设置。这告诉RabbitMQ不给多个消息到一个工人。或者,换句话说,不派遣一个新的消息给一个工人,直到工人处理完成并确认。否则,它会分派到下一个不忙的工人

```php
$channel->basic_qos(null, 1, null);
```
> ### 注意队列大小
> 如果所有的工人很忙,您的队列可以填满。你要留意,或者添加更多的工人,或者有其他策略。
new_task.php

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->queue_declare('task_queue', false, true, false, false);

$data = implode(' ', array_slice($argv, 1));
if(empty($data)) $data = "Hello World!";
$msg = new AMQPMessage($data,
                        array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
                      );

$channel->basic_publish($msg, '', 'task_queue');

echo " [x] Sent ", $data, "\n";

$channel->close();
$connection->close();
```

orker.php

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->queue_declare('task_queue', false, true, false, false);

echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

$callback = function($msg){
  echo " [x] Received ", $msg->body, "\n";
  sleep(substr_count($msg->body, '.'));
  echo " [x] Done", "\n";
  $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
};

$channel->basic_qos(null, 1, null);
$channel->basic_consume('task_queue', '', false, false, false, false, $callback);

while(count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();

```

> 使用消息确认和预取您可以建立一个工作队列。持久性选项让任务生存即使RabbitMQ重新启动。
现在我们可以继续教程3和学习如何向许多消费者提供相同的信息。