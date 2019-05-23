<?php

class Logger
{
    static $log_file = "log/log.log";

    static function log($log, $level='log') {
        if($log != '') {
            $fh = fopen(self::$log_file, "a");
            fwrite($fh,date('Y-m-d H:i:s')."\t[$level] $log\n\n");
            fclose($fh);
        }
    }
}