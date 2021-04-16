<?php
require_once 'ws.php';

class Packet {
    public $type;
    public $data;

    public function __construct($type, $data) {
        $this->type = $type;
        $this->data = $data;
    }
}

class User {
    public $connection;
    public $user;

    public function __construct($connection, $user) {
        $this->connection = $connection;
        $this->user = $user;
    }
}

class Connection {
    public $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function send($data) {
        WebSocketServer::response($this->connection, $data);
    }
}

?>
