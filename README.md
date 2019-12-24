# websocket-Swoole php框架扩展

> ## 扩展说明

基于swoole开发的websocket实时推送、主动推送、长连接扩展，应用于php的各类框架，如thinkphp,yii2,laravel等

> ## 如何使用

## 安装

```php
composer require xiaochengfu/swoole-websocket
```

## 配置

1.将params_swoole.php.default配置文件放到你框架配置文件夹下
推荐放到config下：
```sh
laravel、thinkphp5/6放到config目录下

yii2放到common/config目录下

```
2.修改配置文件

如何修改，配置文件中有具体说明

## 启动

根据每个框架的自定义命令，设置对应的指令即可，以启动为例

```php
$setting = config('params.swoole');//框架获取配置的方法，各有不同

$swoole = new SwooleService($setting);
$swoole->serviceStart();
```
扩展支持

a.启动服务 serviceStart

b.查看服务状态 serviceStats

c.查看进程列表 serviceList

d.停止服务 serviceStop

## 场景测试

1.连接socket

访问`http://www.websocket-test.com`，输入ip:9512，进行连接测试

2.测试推送

推送通过http请求触发，在你要触发推送的action中，添加
```php
$swoole = new \xiaochengfu\swoole\Swoole(config('params.swoole'));
$swoole->pushMsg(1,['hello','world']);

//注意pushMsg($fd,$data)
//fd 这里可用刚连接的客户端1
//data 类型为数组

```
正确的话，你将在刚连接的浏览器客户端中接收到推送消息

3. 测试实时消息通信

这里采用callback回调的方式，可灵活的嵌入逻辑，只需要把`SwooleCallback.php`
文件放到vendor外的任何地方，通过修改命名空间来访问，这里仅把SwooleCallback.php文件当作测试回调文件!

客户端发送消息格式如下：
```json
{"data":{"namespace":"xiaochengfu\\swoole\\SwooleCallback","function":"test","params":{"a":1}}}
```
---敲黑板---

a.发送的消息必须为字符串

b.格式说明

`namespace` 为回调文件的名字空间

`function` 为回调文件内的方法，必须为静态方法

`params` 为方法的参数

正确的话，socket客户端将立即收到服务端返回的消息

