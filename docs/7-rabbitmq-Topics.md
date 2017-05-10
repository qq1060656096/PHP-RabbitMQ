# 主题模式
> 接收消息基于(正则)匹配模式

在前面的教程中我们改进的日志系统。我们使用一个direct exchange,而不是只使用fanout exchange能力的虚拟广播,,有选择地接收日志的可能性。

虽然使用的direct exchange改进我们的系统,但它仍然有一定的局限性,它不能做路由基于多个标准。

日志系统中我们不仅要订阅日志根据严重程度,但也基于源发出的日志。你可能知道这个概念的syslog unix工具,这路线日志根据严重程度(info/warn/crit…)和设备(auth/cron/kern……)。

这将给我们一个很大的灵活性——我们可能只想要来自cron错误,但也从“kern”的所有日志。

实现在我们的日志系统我们需要了解更复杂的topic exchange。

### Topic exchange

消息发送到topic exchange不可能任意routing_key——它必须是一个单词列表,使用点"."分隔。可以是任何单词,但通常他们指定一些功能连接到消息。一些有效routing_key例子:"stock.usd.nyse", "nyse.vmw", "quick.orange.rabbit".routing key可以有尽可能多的单词,但是长度不能超过255字节。

binding_key必须在相同的形式。topic exchange背后的逻辑是类似于直接一个消息发送与特定routing_key交付的所有队列与匹配的binding_key绑定。

### binding_keys有两个重要的特殊情况:
> 1. 星号"*"完全可以代表任意一个单词.
> 2. #(hash)可以代表0个或者多个单词

请看下面一个例子:
![img](https://qq1060656096.github.io/images/rabbitmq/6-1.png)

在这个例子中,我们将发送消息,所有描述动物.消息将发送到routing_key,由3个单词和2个点组成.第1个routing_key单词celerity(快速、敏捷),第2个color(颜色),第3个species(物种): "<celerity>.<colour>.<species>".

### 我们创建了2个队列3个绑定: 
> 1. Q1 binding_key = "*.orange.*"
> - 队列Q1绑定了(橙色)
> 2. Q2 binding_key = "*.*.rabbit" and ,
> - 队列Q2绑定了(兔子)和(懒惰)

##### 绑定可以概括
> 1. 一个消息routing_key设置为"quick.orange.rabbit",它将被放到2个队列.
> 2. 消息 "lazy.orange.elephant"也会被放到2个队列,
> 3. 另一方面消息"quick.orange.fox" 会被放到Q1队列. 
> 4. 消息"lazy.brown.fox"会被放到Q2队列
> 5. 消息"lazy.pink.rabbit"虽然匹配了2个绑定,但是2个绑定都在Q2队列,只会放一次到Q2队列
> 6. 消息"quick.brown.fox" 不匹配任何绑定会被丢弃
> 7. 如果我们打破我们的合同,发送一条消息,该消息带有一个或四个单词,如"orange"或"quick.orange.male.rabbit"? 这些消息不会匹配任何绑定会被丢弃。
> 8. 消息"lazy.orange.male.rabbit" 尽管它有四个单词,它能匹配最后一个绑定("lazy.#").它会被放到Q2队列

# Topic exchange 特点
> 1. Topic exchange 最强大,它可以像其他的exchange
> 2. 当binding_key使用"#",队列就会接受所有的消息,就行fanout exchange
> 3. 当binding_key不使用特殊字符("*"和 "#"),它就像direct exchange

# 示例(demo):
我们的日志系统使用 topic exchange.我们将开始一个工作假设的routing keys日志将会有两个单词: "<facility>.<severity>".

emit_log_topic.php文件代码:

```php
<?php
require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->exchange_declare('topic_logs', 'topic', false, false, false);

$routing_key = isset($argv[1]) && !empty($argv[1]) ? $argv[1] : 'anonymous.info';
$data = implode(' ', array_slice($argv, 2));
if(empty($data)) $data = "Hello World!";

$msg = new AMQPMessage($data);

$channel->basic_publish($msg, 'topic_logs', $routing_key);

echo " [x] Sent ",$routing_key,':',$data," \n";

$channel->close();
$connection->close();
```

receive_logs_topic.php文件代码

```php
<?php
require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';


use PhpAmqpLib\Connection\AMQPStreamConnection;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->exchange_declare('topic_logs', 'topic', false, false, false);

list($queue_name, ,) = $channel->queue_declare("", false, false, true, false);

$binding_keys = array_slice($argv, 1);
if( empty($binding_keys )) {
    file_put_contents('php://stderr', "Usage: $argv[0] [binding_key]\n");
    exit(1);
}

foreach($binding_keys as $binding_key) {
    $channel->queue_bind($queue_name, 'topic_logs', $binding_key);
}

echo ' [*] Waiting for logs. To exit press CTRL+C', "\n";

$callback = function($msg){
    echo ' [x] ',$msg->delivery_info['routing_key'], ':', $msg->body, "\n";
};

$channel->basic_consume($queue_name, '', false, true, false, false, $callback);

while(count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();
```

接收所有消息

```php
php receive_logs_topic.php "#"
```

接收"kern"消息

```php
php receive_logs_topic.php "kern.*"
```


接收"critical"消息

```php
php receive_logs_topic.php "*.critical"
```

创建多绑定
```php
php receive_logs_topic.php "kern.*" "*.critical"
```

创建routing key="kern.critical"类型绑定
```php
php emit_log_topic.php "kern.critical" "A critical kernel error"
```
