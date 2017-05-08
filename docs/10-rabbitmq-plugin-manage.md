#rabbitmq-插件管理

### 安装web管理RabbitMQ插件
```sql
rabbitmq-plugins enable rabbitmq_management
```

### 列出插件
```sql
#列出所有插件
rabbitmq-plugins list

#列出插件所有插件描述信息
rabbitmq-plugins list -v

#列出插件名包含"management"的插件
rabbitmq-plugins list -v management

#此命令列出所有隐式或明确启用的RabbitMQ插件
rabbitmq-plugins list -e rabbit
```

### 启用插件
```sql
rabbitmq-plugins enable rabbitmq_management
```

### 启用插件
```sql
rabbitmq-plugins disable rabbitmq_management
```