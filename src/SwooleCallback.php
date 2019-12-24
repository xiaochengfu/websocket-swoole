<?php
/**
 * Name: SwooleCallback.php.
 * Author: hp <xcf-hp@foxmail.com>
 * Date: 2019-12-03 18:15
 * Description: SwooleCallback.php.
 */

namespace xiaochengfu\swoole;


class SwooleCallback
{
    /**
     * Description:  实时推送回调测试
     * Author: hp <xcf-hp@foxmail.com>
     * @param $server
     * @param $fid
     * @param $data
     */
    public static function test($server,$fid,$data){
         //实时回应
         $server->push($fid,json_encode($data));
    }
}