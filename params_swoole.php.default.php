<?php

/**
 * 配置文件示例，放到框架的配置文件里
 */
return [
    'swoole' => [
        'host' => '127.0.0.1',        //服务启动IP,本地不要写localhost
        'port' => 9512,            //服务启动端口
        'swoole_http' => '127.0.0.1:9512',//推送触发连接地址
        'process_name' => 'swooleWebSocket',        //服务进程名
        'open_websocket_close_frame' => true,    //开启后，可在WebSocketServer中的onMessage回调中接收到客户端或服务端发送的关闭帧
        'open_tcp_nodelay' => 1,                //启用open_tcp_nodelay
        'daemonize' => false,                //守护进程化
        'heartbeat_idle_time' => 70,               //连接最大的空闲时间 （如果最后一个心跳包的时间与当前时间之差超过这个值，则认为该连接失效,建议 heartbeat_idle_time 为 heartbeat_check_interval 的两倍多一点）
        'heartbeat_check_interval' => 30,          //服务端向客户端发送心跳包的间隔时间，两参数要配合使用
        'dispatch_mode' => 4,                        //抢占模式
        'reactor_num' => 4,               //master进程创建的线程,mac中通过htop查看不到reactor线程
        'worker_num' => 8,                //work进程数目，cpu核数的1-4倍
        'task_worker_num' => 1,                //task进程的数量
        'task_max_request' => 10000,            //work进程最大处理的请求数
        'max_connection' => 10000,              //服务器程序，最大允许的连接数
        'pidfile' => \think\facade\Env::get('runtime_path') . 'swoole/swoole.pid',//
        'log_dir' => \think\facade\Env::get('runtime_path') . 'swoole',
        'task_tmpdir' => \think\facade\Env::get('runtime_path') . 'swoole',
        'log_file' => \think\facade\Env::get('runtime_path') . 'swoole/swoole.log',
        'log_size' => 204800000,       //运行时日志 单个文件大小
    ],
];