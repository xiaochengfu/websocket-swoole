<?php
/**
 * 日志类
 * author:houpeng
 * time:2018-01-26
 */
namespace xiaochengfu\swoole\src;


class Logger extends SwooleBase
{

    public  function info($msg,$logfile='') {
        $settings = $this->settings;
        if (empty($msg)) {
            return;
        }
        if (!is_string($msg)) {
            $msg = var_dump($msg);
        }
        //日志内容
        $msg = '['. date('Y-m-d H:i:s') .'] '. $msg . PHP_EOL;
        //日志文件大小
        $maxSize = $settings['log_size'];
        //日志文件位置
        $file = $logfile ?: $settings['log_dir']."/".date('Y-m').".log";
		if(!is_dir($settings['log_dir'])) {
            mkdir($settings['log_dir'], 0777, true);
		}
        //切割日志
        if (file_exists($file)) {
			if(filesize($file) >= $maxSize) {
				$bak = $file.'-'.time();
				if (!rename($file, $bak)) {
					error_log("rename file:{$file} to {$bak} failed", 3, $file);
				}
			}
        } else {
			touch($file);
			chmod($file, 0666);
		}
        error_log($msg, 3, $file);
    }
 
}