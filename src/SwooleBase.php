<?php
/**
 * Name: SwooleBase.php.
 * Author: hp <xcf-hp@foxmail.com>
 * Date: 2018-04-27 18:44
 * Description: SwooleBase.php.
 */

namespace xiaochengfu\swoole;


use think\facade\Config;

class SwooleBase
{
    public $settings;
    public function __construct()
    {
        //thinkphp5获取配置的方法
        $this->settings = Config::get('params.swoole');
        //yii2获取配置的方法
//        $this->settings = \Yii::$app->params['swoole'];
    }

}