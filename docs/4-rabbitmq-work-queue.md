# 工作队列

> 轮循分配或者工人分配任务(竞争消费者模式)

![avatar](data:image/png;base64,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)


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

![avatar](data:image/png;base64,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)


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