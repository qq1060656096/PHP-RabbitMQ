#rabbitmq-web管理插件


### 安装web管理RabbitMQ插件
```sql
rabbitmq-plugins enable rabbitmq_management
```
### 设置用户管理权限
> 注意用户有管理权限才能登陆
```sql
#添加用户
rabbitmqctl add_user admin admin

#设置admin用户为管理员
rabbitmqctl set_user_tags  admin administrator
```

Web UI位于: http://server-name:15672/
> http://localhost:15672/

Web API位于: http://server-name:15672/api
> http://localhost:15672/api

Web cli位于: http://server-name:15672/cli
> http://localhost:15672/cli

>