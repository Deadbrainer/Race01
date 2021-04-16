<?php

class View
{
    public $html;

    public function __construct($html)
    {
        $this->html = $html;
    }

    public function render()
    {
        // header("Location: localhost:3000/index.php" . $this->html);
        // exit;
        $file = file_get_contents($this->html);
        if (!is_null($file)) {
            echo $file;
        }
    }

    public function renderPhp(){
        require $this->html;
    }
}