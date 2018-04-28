<?php
/**
 * Swoole 实现的 http server,用来处理异步多进程任务
 * author:houpeng
 * time:2017-05-29
 */

namespace xiaochengfu\swoole;

class SwooleSetWebSocket{

    private $server = null;

    /**
     * swoole 配置
     * @var array
     */
    private $settings = [];

    /**
     * Yii::$app 对象
     * @var array
     */
    private $app = null;

    /**
     * SwooleSetWebSocket constructor.
     * @param $settings
     * @param $app
     */
    public function __construct($settings,$app){
        $this->settings = $settings;
        $this->app = $app;
    }

    /**
     * 设置swoole进程名称
     * @param string $name swoole进程名称
     */
    private function setProcessName($name){
        if (function_exists('cli_set_process_title')) {
            cli_set_process_title($name);
        } else {
            if (function_exists('swoole_set_process_name')) {
                swoole_set_process_name($name);
            } else {
                trigger_error(__METHOD__. " failed.require cli_set_process_title or swoole_set_process_name.");
            }
        }
    }

    /**
     * 运行服务
     * @return [type] [description]
     */
    public function run(){
        $this->server = new \swoole_websocket_server($this->settings['host'], $this->settings['port']);
        $this->server->set($this->settings);
        //回调函数
        $call = [
            'start',
            'workerStart',
            'managerStart',
            'open',
            'task',
            'finish',
            'close',
            'message',
            'receive',
            'request',
            'workerStop',
            'shutdown',
        ];
        //事件回调函数绑定
        foreach ($call as $v) {
            $m = 'on' . ucfirst($v);
            if (method_exists($this, $m)) {
                $this->server->on($v, [$this, $m]);
            }
        }

        echo "服务成功启动" . PHP_EOL;
        echo "服务运行名称:{$this->settings['process_name']}" . PHP_EOL;
        echo "服务运行端口:{$this->settings['host']}:{$this->settings['port']}" . PHP_EOL;

        return $this->server->start();
    }

    /**
     * Description:  onStart
     * Author: hp <xcf-hp@foxmail.com>
     * Updater:
     * @param $server
     * @return bool
     */
    public function onStart($server){
        echo '[' . date('Y-m-d H:i:s') . "]\t swoole_websocket_server master worker start\n";
        $this->setProcessName($server->settings['process_name'] . '-master');
        //记录进程id,脚本实现自动重启
        $pid = "{$this->server->master_pid}\n{$this->server->manager_pid}";
        file_put_contents($this->settings['pidfile'], $pid);
        return true;
    }

    /**
     * Description:  onManagerStart
     * Author: hp <xcf-hp@foxmail.com>
     * Updater:
     * @param $server
     */
    public function onManagerStart($server){
        echo '[' . date('Y-m-d H:i:s') . "]\t swoole_http_server manager worker start\n";
        $this->setProcessName($server->settings['process_name'] . '-manager');
    }

    /**
     * Description:  onWorkerStart
     * Author: hp <xcf-hp@foxmail.com>
     * Updater:
     * @param $server
     * @param $workerId
     */
    public function onWorkerStart($server, $workerId){
        if ($workerId >= $this->settings['worker_num']) {
            $this->setProcessName($server->settings['process_name'] . '-task');
        } else {
            $this->setProcessName($server->settings['process_name'] . '-event');
        }
    }

    /**
     * Description:  onWorkerStop
     * Author: hp <xcf-hp@foxmail.com>
     * Updater:
     * @param $server
     * @param $workerId
     */
    public function onWorkerStop($server, $workerId){
        echo '['. date('Y-m-d H:i:s') ."]\t swoole_http_server[{$server->settings['process_name']}  worker:{$workerId} shutdown\n";
    }

    /**
     * Description:  接收websocket客户端信息，并实时返回
     * Author: hp <xcf-hp@foxmail.com>
     * Updater:
     * @param $server
     * @param $frame
     * @return mixed
     */
    public function onMessage($server, $frame){
        echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";

        /**
         * 功能一:获取websocket的连接信息
         */
        if($frame->data == 'stats'){
            $websocket_number['websocket_number'] = count($this->server->connection_list(0,100));
            array_push($websocket_number,$this->server->stats());
            return $this->server->push($frame->fd,json_encode($websocket_number));
        }else{
            $requestData = json_decode($frame->data,true);
            /**
             * 功能二:
             * 这里分别以thinkphp5、yii2为例
             * 扩展部分,根据客户端发来的命令{$frame->data}来做出相应的处理,这里根据自己的需求来写不做处理...
             * 推荐使用框架的全局调用方法来做扩展处理,来实现解耦,主要通过client发来的socket指令data来自定义区分逻辑控制器
             * thinkphp5推荐data协议指令：data=>['n'=>'app\common\logic\SwooleCallback','f'=>'test','p'=>['a'=>1]],n为名字空间,f为方法,p为参数
             * yii2推荐data协议指令：data=>['a'=>'test/test','p'=>['a'=>1]],a为控制器,p为参数
             *
             * thinkphp5你的回调类可能是这样的：
             * class SwooleCallback
             *   {
             *       public $server;
             *      public $fid;
             *      public $data;
             *      public function __construct($args)
             *      {
             *          $this->server = $args['server'];
             *          $this->fid = $args['fid'];
             *          $this->data = $args['data'];
             *      }
             *
             *     public function test(){
             *         //实时回应
             *         $this->server->push($this->fid,json_encode($this->data));
             *      }
             *  }
             *
             * yii2你的控制器console/controllers可能是这样的:
             *  public function actionTest($param){
             *      $info = $param['data'];
             *      $param['server']->push($param['fid'],json_encode($info));
             *  }
             *
             */
            if (isset($requestData['data']['n']) && $requestData['data']['n']) {
//                thinkphp5的应用示例
                app($requestData['data']['n'],[
                    ['server'=>$server,'fid'=> $frame->fd,'data'=>$requestData['data']['p']]
                ])->{$requestData['data']['f']}();
//                yii2的应用示例
//                \Yii::$app->runAction($requestData['data']['a'], [['server'=>$server,'fid'=> $frame->fd,'data'=>$requestData['data']['p']]]);
            }else{
                $server->push($frame->fd, "终于等到你啦!");
            }
        }
    }

    /**
     * http请求
     * @param $request
     * @param $response
     * @return mixed
     * 用于处理推送消息(websocket的推送)
     */
    public function onRequest($request, $response){
        if(!empty($request->post) && is_array($request->post)){
            $requestData = $request->post;
           if(isset($requestData['type']) && $requestData['type'] == 'socket'){
                //websocket推送消息到客户端
                $allFd = $this->server->connection_list(0,100);
                if($allFd){
                    if(in_array($requestData['fd'],$this->server->connection_list(0,100))){
                        $status = $this->server->connection_info($requestData['fd']);
                        if($status['websocket_status'] == 3){
							$t1 = microtime(true);
							(new Logger())->info('[ 消息发送开始 '.date('Y-m-d H:i:s', time()).']-id为'.$requestData['fd']);
                            $result = $this->server->push($requestData['fd'],$requestData['data']);
                            echo $result. PHP_EOL;
                            echo time(). PHP_EOL;
                            echo date('Y-m-d H:i:s'). PHP_EOL;
							$t2 = microtime(true);
							(new Logger())->info('[ 消息发送结束 '.date('Y-m-d H:i:s', time()).']-id为'.$requestData['fd'].' 执行耗时：'.round($t2-$t1, 3));
                        } else
							$this->onClose($this->server, $requestData['fd']);
                    }
                }
            }
        }
        $response->end(json_encode($this->server->stats()));
    }


    /**
     * Description:  解析data对象
     * Author: hp <xcf-hp@foxmail.com>
     * Updater:
     * @param $data
     * @return array|bool|mixed
     */
    private function parseData($data){
        $data = json_decode($data,true);
        $data = $data ?: [];
        if(!isset($data["data"]) || empty($data["data"])){
            return false;
        }
        return $data;
    }


    /**
     * Description:  任务处理
     * Author: hp <xcf-hp@foxmail.com>
     * Updater:
     * @param $serv
     * @param $task_id
     * @param $from_id
     * @param $data
     * @return array|bool|mixed
     */
    public function onTask($serv, $task_id, $from_id, $data){
        (new Logger())->info('[task data] '.$data);
        $data = $this->parseData($data);
        if($data === false){
            return false;
        }
        return $data;
    }


    protected function httpGet($url,$data){
        if ($data) {
            $url .='?'.http_build_query($data) ;
        }
        $curlObj = curl_init();    //初始化curl，
        curl_setopt($curlObj, CURLOPT_URL, $url);   //设置网址
        curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);  //将curl_exec的结果返回
        curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curlObj, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curlObj, CURLOPT_HEADER, 0);         //是否输出返回头信息
        $response = curl_exec($curlObj);   //执行
        curl_close($curlObj);          //关闭会话
        return $response;
    }

    /**
     * Description:  解析onfinish数据
     * Author: hp <xcf-hp@foxmail.com>
     * Updater:
     * @param $data
     * @return bool|string
     */
    private function genFinishData($data){
        if(!isset($data['finish']) || !is_array($data['finish'])){
            return false;
        }
        return json_encode(['data'=>$data['finish']]);
    }

    /**
     * Description:  任务结束回调函数
     * Author: hp <xcf-hp@foxmail.com>
     * Updater:
     * @param $server
     * @param $taskId
     * @param $data
     * @return bool
     */
    public function onFinish($server, $taskId, $data){

        $data = $this->genFinishData($data);

        if($data !== false ){
            return $this->server->task($data);
        }
        return true;
    }

    /**
     * @param $server
     * @param $request
     * websocket连接的回调函数
     */
    public function onOpen($server, $request){
        echo "server: websocketclient success with fd{$request->fd}\n";
	    $info = ['code' => 200, 'status' => 1, 'command' => 'whoAreU'];
        $server->push($request->fd, json_encode($info));
    }

    /**
     * Description:  客户端关闭后,服务端的消息回调
     * Author: hp <xcf-hp@foxmail.com>
     * Updater:
     * @param $ser
     * @param $fd
     */
    public function onClose($ser, $fd){
        echo "client {$fd} closed\n";
        /**
         * 这里可以写你的回调处理
         */
    }

    /**
     * @param $server
     * @param $fd
     * @param $from_id
     * @param $data
     * @return bool
     * 已废弃
     * 处理tcp客户端请求,由于开启的服务为websocket,tcp客户端无法与其通信,全部功能转到request回调中
     */
    public function onReceive($server, $fd, $from_id, $data){
        if($data == 'stats'){
            return $this->server->send($fd,var_export($this->server->stats(),true),$from_id);
        }
        $this->server->task($data);//非阻塞的，将任务扔到任务池，并及时返还
        return true;

    }

}

