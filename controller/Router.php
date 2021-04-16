<?php
class Router
{
    public $params = array();
    public function setParams()
    {
        if ($_GET) {
            foreach ($_GET as $key => $value) {
                $this->params[$key] = $value;
            }
        }
    }
    public function print()
    {
        $toPrint = "{";
        if ($this->params) {
            foreach ($this->params as $key => $value) {
                $toPrint .= "'$key' : '$value', ";
            }
        }
        $toPrint = substr($toPrint, 0, -2);
        $toPrint .= "}";
        echo $toPrint;
    }
}
