<?php

class Cache
{
    private $cache_path = BASEPATH.'cache/';
    private $cache_ttl = 3600 * 24;
    private $key = null;

    private $active = false;


    public function __construct($key)
    {
        if ($key === false) {
            $this->active = false;
            Logger::log("Caching deactivated");
            return false;
        } else {
            $this->key = $key;
            $this->retrieve(); //try to retrieve the cache for this key
            ob_start(); //starts buffering
        }
    }

    private function check()
    {
        $full_filename = $this->getFullPath($this->key);
        return is_file($full_filename) && is_readable($full_filename);
    }

    public function store()
    {
        if ($this->active) {
            $full_path = $this->getFullPath($this->key);
            $content = ob_get_contents();

            Logger::log("Caching key $this->key");

            $fh = fopen($full_path, "w");
            fwrite($fh, $content);
            fclose($fh);

            ob_end_flush();
        }
    }

    public function retrieve()
    {

        $full_path = $this->getFullPath($this->key);
        if ($this->check()) {
            if (time() - filemtime($full_path) < $this->cache_ttl) {
                Logger::log("Cache hit for template $this->key");
                $content = file_get_contents($full_path);
                echo $content;
                exit();
            } else {
                Logger::log("Cache stale for template $this->key");
                return false;
            }
        } else {
            Logger::log("Cache miss for template $this->key");
            return false;
        }
    }

    public function deactivate()
    {
        $this->active = false;
    }

    private function getFullPath($key)
    {
        return $this->cache_path.$key.'.cache.html';
    }
}