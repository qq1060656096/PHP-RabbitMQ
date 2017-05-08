# rabbitmqctl-管理工具

# 应用和集群管理

### 停止节点

```sql
rabbitmqctl stop
```

### 停止应用
> 该命令指示RabbitMQ节点停止RabbitMQ应用程序
```sql
rabbitmqctl stop_app
```

### 启动应用
> 此命令指示RabbitMQ节点启动RabbitMQ应用程序
```sql
rabbitmqctl start_app
```

### 重置节点
> 此命令重置RabbitMQ节点,RabbitMQ应用程序必须已经停止
```sql
rabbitmqctl reset
```

### 强制重置节点
> 此命令重置RabbitMQ节点,所述force_reset命令不同于reset位，它无条件地复位节点，不管当前管理数据库状态和群集配置的。如果数据库或群集配置已损坏，则只能作为最后手段使用必须已经停止
```sql
rabbitmqctl force_reset
```

# 1 用户管理

### 1.0 列出所有用户
```sql
rabbitmqctl list_users
```

### 1.1 新增用户

```sql
#rabbitmqctl add_user {username} {password}
rabbitmqctl add_user admin admin
```

### 1.2 删除用户

```sql
#rabbitmqctl delete_user {username}
rabbitmqctl delete_user admin
```

### 1.3 更改密码

```sql
#rabbitmqctl change_password {username} {newpassword}
rabbitmqctl change_password admin admin123
```

### 1.4 清除密码
> 注意清除密码后用户不能登陆

```sql
#rabbitmqctl clear_password {username} 
rabbitmqctl clear_password admin
```

### 1.5 设置认证用户

```sql
#rabbitmqctl authenticate_user {username} {password}
rabbitmqctl authenticate_user admin admin
```

# 2 角色管理

> 角色类型有:none、management、policymaker、monitoring、administrator

### 2.1 设置用户角色(设置用户RabbitMQ管理权限)

```sql
#rabbitmqctl set_user_tags {username} {tag ...}
#设置admin用户为管理员
rabbitmqctl set_user_tags  admin administrator
```
### 2.2 角色描述
> 也可以理解为权限，但它只是针对管理RabbitMQ的权限，包括vhost、用户、exchange、queue、链接等。举个实际的例子：假设开启了rabbitmq_manage 的插件，通过set_user_tags 就可以赋予某个用户使用rabbitmq_manage的权限。

系统默认有5种角色：none、management、policymaker、monitoring、administrator

##### none
> 不能访问 management plugin

##### management
> 用户可以通过AMQP做的任何事外加：
列出自己可以通过AMQP登入的virtual hosts
查看自己的virtual hosts中的queues, exchanges 和 bindings
查看和关闭自己的channels 和 connections
查看有关自己的virtual hosts的“全局”的统计信息，包含其他用户在这些virtual hosts中的活动。
policymaker

##### management可以做的任何事外加：
> 查看、创建和删除自己的virtual hosts所属的policies和parameters
monitoring

##### management可以做的任何事外加：
> 列出所有virtual hosts，包括他们不能登录的virtual hosts
查看其他用户的connections和channels
查看节点级别的数据如clustering和memory使用情况
查看真正的关于所有virtual hosts的全局的统计信息
administrator

##### policymaker和monitoring可以做的任何事外加:
> 创建和删除virtual hosts
查看、创建和删除users
查看创建和删除permissions
关闭其他用户的connections



# 3 权限控制
> 权限控制是以vhost为单位,当你在Rabbit里创建一个用户时,用户通常会被指派知道一个vhost,并且只能访问被指派的vhost内的队列、交换器和绑定.vhost之间是绝对隔离的.

### 3.1 权限如何工作
> 当RabbitMQ客户端建立与服务器的连接时，它指定了要在其中运行的虚拟主机。此时执行第一级访问控制，服务器检查用户是否有访问虚拟主机的权限，否则拒绝连接尝试。

### 3.2 创建虚拟主机
```sql
#rabbitmqctl add_vhost {vhost}
rabbitmqctl add_vhost test_vhost
```

### 3.3 删除虚拟主机

```sql
#rabbitmqctl delete_vhost {vhost}
rabbitmqctl delete_vhost test_vhost
```

### 3.4 虚拟主机列表 

```sql
#rabbitmqctl list_vhosts [vhostinfoitem ...]

rabbitmqctl list_vhosts name tracing

rabbitmqctl list_vhosts
```

### 3.5 设置权限

```sql
#rabbitmqctl set_permissions [-p vhost] {user} {conf} {write} {read}

#设置用户拥有所有权限
rabbitmqctl set_permissions -p / myvhost tonyg ".*" ".*" ".*"

rabbitmqctl set_permissions -p / myvhost tonyg "^tonyg-.*" ".*" ".*"
```
> 该命令指示RabbitMQ代理授予名为tonyg的用户名为/ myvhost的虚拟主机的用户，其名称以“tonyg-”开头的所有资源都具有配置权限，并对所有资源进行写入和读取权限。


### 3.6 清除用户vhost权限
> 拒绝用户访问vhost
```sql
#rabbitmqctl clear_permissions [-p vhost ] { username }
rabbitmqctl clear_permissions -p test_vhost admin
```

### 3.7 列出vhost的权限

```sql
#rabbitmqctl list_permission [-p vhost ]
rabbitmqctl list_permissions -p test_vhost

rabbitmqctl list_permissions
```

### 3.8 列出用户权限

```sql
#rabbitmqctl list_user_permissions { username }

rabbitmqctl list_user_permissions guest
```

### 列出vhost交换器

```sql
#rabbitmqctl list_exchanges [-p vhost ]
rabbitmqctl list_exchanges -p /
```

### 列出vhost队列

```sql
#rabbitmqctl list_queues list_queues [-p vhost] [[--offline] | [--online] | [--local]] [queueinfoitem ...]

rabbitmqctl list_queues -p /
```

### 列出vhost绑定信息

```sql
#rabbitmqctl list_bindings [-p vhost ] [ bindinginfoitem ...]
rabbitmqctl list_bindings -p /
```

### 列出TCP/IP连接统计信息

```sql
#rabbitmqctl list_connections [ connectioninfoitem ...]

rabbitmqctl list_connections
```

###  

```sql

```

