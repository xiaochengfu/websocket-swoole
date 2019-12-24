<?php
/**
 * websocket推送
 * time:2017-05-27
 * author:houpeng
 */
namespace xiaochengfu\swoole;
use Curl\Curl;

class Swoole
{

    /**
     * 配置对象
     * @var array
     */
    private $settings = [];

    function __construct($settings){
        $this->settings = $settings;
    }

    /**
     * Description:  主动推送
     * Author: hp <xcf-hp@foxmail.com>
     * @param $fd
     * @param array $data
     * @return bool
     * @throws \ErrorException
     */
    public function pushMsg($fd,array $data){
        $settings = $this->settings;
        $t1 = microtime(true);
        $logger = new Logger($settings);
        $logger->info('[ 消息发送触发开始 '.date('Y-m-d H:i:s' ,time()).']-data为'.json_encode($data));
        $curl = new Curl();
        $body = [
            'fd'=>$fd,
            'data'=>$data,
            'type'=>'socket'
        ];
        $curl->post($settings['swoole_http'],$body);
		$t2 = microtime(true);
        $logger->info('[ 消息发送触发结束 '.date('Y-m-d H:i:s', time()).']-执行耗时：'.round($t2-$t1, 3));
		return true;
    }
    
}
