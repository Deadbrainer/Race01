<?php
include "connection/Connector.php";
abstract class Model
{
    protected $db, $table;

    protected function setTable($table)
    {
        $this->table = $table;
        $this->db->connection->query("CREATE TABLE IF NOT EXISTS $this->table (`id` int NOT NULL AUTO_INCREMENT PRIMARY KEY, `login` varchar(30) NOT NULL UNIQUE, `password` varchar(255) NOT NULL, `full_name` varchar(255) NOT NULL, `email` varchar(255) NOT NULL UNIQUE, `status` varchar(255) NOT NULL DEFAULT 'user', `avatar` LONGBLOB, `decks` text);");
    }
    protected function setConnection($host, $port, $username, $password, $database)
    {
        $this->db = new Connection($host, $port, $username, $password, $database);
    }
    public function __construct($host, $port, $username, $password, $database, $table)
    {
        $this->setConnection($host, $port, $username, $password, $database);
        $this->setTable($table);
    }
    abstract public function find($id);
    abstract public function delete();
    abstract public function last();
    abstract public function save($login, $password, $full_name, $email);
    abstract public function saveAvatar($img);
    abstract public function addtoDeck($id, $card);
    abstract public function getDeck($id);
    abstract public function removeFromDeck($id, $card);
    abstract public function getDecks();
}

abstract class cardModel{
    protected $db, $table;

    protected function setTable($table)
    {
        $this->table = $table;
        $this->db->connection->query("CREATE TABLE IF NOT EXISTS `cards` (`id` int NOT NULL PRIMARY KEY, `card_name` varchar(30) NOT NULL UNIQUE, `attack` int NOT NULL, `def` int NOT NULL, `health` int NOT NULL, `cost` int NOT NULL, `img` LONGBLOB)");
    }
    protected function setConnection($host, $port, $username, $password, $database)
    {
        $this->db = new Connection($host, $port, $username, $password, $database);
    }
    public function __construct($host, $port, $username, $password, $database, $table)
    {
        $this->setConnection($host, $port, $username, $password, $database);
        $this->setTable($table);
    }
    abstract public function find($id);
    abstract public function getAll();
    abstract public function saveCard($id, $name, $attack, $def, $health, $cost, $img);
}

