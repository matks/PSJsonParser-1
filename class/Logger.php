<?php

class Logger
{
    static $log_file = "log/log.log";

    static function log($log, $level='log') {

        $dirname = dirname(self::$log_file);
        if (!is_dir($dirname))
        {
            mkdir($dirname, 0755, true);
        }

        if($log != '') {
            $fh = fopen(self::$log_file, "a");
            fwrite($fh,date('Y-m-d H:i:s')."\t[$level] $log\n");
            fclose($fh);
        }
    }
}