<?php
/**
 * websocket推送
 * time:2017-05-27
 * author:houpeng
 */
namespace xiaochengfu\swoole;
use Curl\Curl;

class Swoole extends SwooleBase
{

    const NORMAL = 1;
    const TYPE_SOCKET = 'socket';//socket请求

    /**
     * @param $fd
     * @param $data
     * @return mixed
     * websocket消息推送
     * 格式为:
     *   'fd' => xx,//客户端id
     *   'data' => [],//消息体
     */
    public function pushMsg($fd,$data){
		$t1 = microtime(true);
        (new Logger())->info('[ 消息发送触发开始 '.date('Y-m-d H:i:s' ,time()).']-data为'.json_encode($data));
        $settings = $this->settings;
        $datas['type'] = self::TYPE_SOCKET;
        $datas['data'] = json_encode($data);
        $datas['fd'] = $fd;
        $curl = new Curl();
		$datas['command'] = $data['command'];
        $curl->post($settings['swoole_http'],$datas);
		$t2 = microtime(true);
        (new Logger())->info('[ 消息发送触发结束 '.date('Y-m-d H:i:s', time()).']-执行耗时：'.round($t2-$t1, 3));
		return true;
    }
    
}
