<?php

class Template
{
    private $vars = [
        'BASEURL' => BASEURL
    ];
    private $file = null;
    private $fileContent = '';

    public function __construct($file)
    {
        $real_path = BASEPATH.'views/view.'.$file.'.php';
        if (is_file($real_path) && is_readable($real_path)) {
            $this->file = $real_path;
        } else {
            throw new Exception("View file $file ($real_path) not found.");
        }
        $this->fileContent = file_get_contents($this->file);
    }

    public function set($value)
    {
        $this->vars = array_merge($this->vars, $value);
    }

    public function render()
    {
        if (sizeof($this->vars) > 0) {
            foreach($this->vars as $k => $v) {
                $this->fileContent = str_replace('{{'.strtoupper($k).'}}', $v, $this->fileContent);
            }
        }
        return $this->fileContent;
    }
}