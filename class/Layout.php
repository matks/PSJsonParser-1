<?php

class Layout
{
    private $cssFiles = [
        'https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css',
        'https://fonts.googleapis.com/css?family=Roboto',
        'https://fonts.googleapis.com/css?family=Roboto+Mono',
        'https://fonts.googleapis.com/icon?family=Material+Icons',
        'http://localhost/json/assets/style.css'
    ];
    private $JSFiles = [];
    private $title = 'Nightlies report';
    private $layoutFile = BASEPATH.'views/layout.php';
    private $layoutFileContent = '';

    private $viewTemplate = null;

    private static $instance = null;

    public function __construct()
    {
    }

    public static function get()
    {
        if (self::$instance == null)
        {
            self::$instance = new Layout();
        }

        return self::$instance;
    }

    public function addCSSFile($src) {
        $this->cssFiles[] = $src;
    }

    public function addJSFile($src) {
        $this->JSFiles[] = $src;
    }

    public function setFile($file = null) {
        $real_path = BASEPATH.'views/'.$file;
        if ($file) {
            if (is_file($real_path) && is_readable($real_path)) {
                $this->layoutFile = $real_path;
            }
        } else {
            throw new Exception("Layout file $file ($real_path) not found.");
        }
    }

    private function setFileContent() {
        $this->layoutFileContent = file_get_contents($this->layoutFile);
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function setView(Template $template) {
        $this->viewTemplate = $template;
    }

    public function render() {

        //get the content of the layout file
        $this->setFileContent();

        //replace title
        $this->layoutFileContent = str_replace('{{TITLE}}', $this->title, $this->layoutFileContent);

        //add css and JS files
        $cssIncludes = '';
        foreach ($this->cssFiles as $src) {
            $cssIncludes .= '
                <link rel="stylesheet" type="text/css" href="' . $src . '">
            ';
        }

        $JSIncludes = '';
        foreach($this->JSFiles as $src) {
            $JSIncludes .= '
                <script src="'.$src.'"></script>
            ';
        }

        //insert CSS and JS calls
        $this->layoutFileContent = str_replace('{{CSS}}', $cssIncludes, $this->layoutFileContent);
        $this->layoutFileContent = str_replace('{{JS}}', $JSIncludes, $this->layoutFileContent);

        //start rendering the view itself
        $view_render = $this->viewTemplate->render();

        //place the content of the rendered view into the layout
        $this->layoutFileContent = str_replace('{{CONTENT}}', $view_render, $this->layoutFileContent);

        //buffers content
        ob_start();
        echo $this->layoutFileContent;
        //renders content
        ob_end_flush();

    }
}