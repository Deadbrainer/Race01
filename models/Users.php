<?php
include "Model.php";
class Users extends Model
{
    public $id = null;
    public $name;
    public $description;
    public $race;
    public $class_role;

    public function __construct($host, $port, $username, $password, $database)
    {
        parent::__construct($host, $port, $username, $password, $database, "users");
    }

    public function find($login)
    {
        if ($this->db->getConnectionStatus()) {
            $result = $this->db->connection->query("SELECT * FROM $this->table WHERE `login`='$login'");
            $result = $result->fetchall()[0];
            $this->id = $result[0];
            $this->login = $result[1];
            $this->password = $result[2];
            $this->full_name = $result[3];
            $this->email = $result[4];
            $this->status = $result[5];
            $this->avatar = $result[6];
            $this->decks = $result[7];
        }
    }

    public function last()
    {
        if ($this->db->getConnectionStatus()) {
            $result = $this->db->connection->query("SELECT * FROM $this->table ORDER BY `id` DESC LIMIT 1");
            $result = $result->fetchall()[0];
            $this->id = $result[0];
            $this->login = $result[1];
            $this->password = $result[2];
            $this->full_name = $result[3];
            $this->email = $result[4];
            $this->status = $result[5];
            $this->avatar = $result[6];
            $this->decks = $result[7];
        }
    }

    public function delete()
    {
        if ($this->db->getConnectionStatus()) {
            $this->db->connection->query("DELETE FROM $this->table WHERE `id`=$this->id");
            $this->db->connection->commit();
        }
    }

    public function save($login, $password, $full_name, $email)
    {
        if ($this->db->getConnectionStatus()) {
            //$stmt = $this->db->connection->query("SELECT * FROM $this->table WHERE name='$this->name'");
            try {
                $decks = array("1" => array(), "2" => array(), "3" => array());
                $decks = json_encode($decks);
                $stmt = $this->db->connection->query("INSERT INTO $this->table (`login`, `password`, `full_name`, `email`, `decks`) VALUES
                ('$login', '$password', '$full_name', '$email', '$decks')");
                return 0;
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    if (strpos($e->getMessage(), "users.login") != false) {
                        return 1;
                    } else if (strpos($e->getMessage(), "users.email") != false) {
                        return 2;
                    }
                }
            }
        }
    }

    public function saveAvatar($img)
    {
        if ($this->db->getConnectionStatus()) {
            try {
                $stmt = $this->db->connection->query("UPDATE $this->table SET `avatar` = '" . base64_encode(file_get_contents($img)) . "' WHERE `login` = '$this->login'");
                return 0;
            } catch (PDOException $e) {
                echo $e;
            }
        }
    }

    public function addtoDeck($id, $card)
    {
        if ($this->db->getConnectionStatus()) {
            $result = json_decode($this->decks, true);
            if (count($result[$id]) < 10) {
                if (!in_array($card, $result[$id])) {
                    try {
                        array_push($result[$id], $card);
                        $result = json_encode($result);
                        $stmt = $this->db->connection->query("UPDATE $this->table SET `decks` = '$result' WHERE `id` = '$this->id'");
                        return 0;
                    } catch (PDOException $e) {
                        echo $e;
                    }
                } else {
                    return -2;
                }
            } else {
                return -1;
            }
        }
    }

    public function getDeck($deck_id)
    {
        $result = json_decode($this->decks, true);
        return $result[$deck_id];
    }

    public function removeFromDeck($id, $card)
    {
        if ($this->db->getConnectionStatus()) {
            $result = json_decode($this->decks, true);
            $deck = $result[$id];
            try {
                if ($deck[0] == $card) {
                    unset($deck[0]);
                } else if (($key = array_search($card, $deck)) !=  false) {
                    unset($deck[$key]);
                }
                $result[$id] = $deck;
                $result = json_encode($result);
                $stmt = $this->db->connection->query("UPDATE $this->table SET `decks` = '$result' WHERE `id` = '$this->id'");
                return 0;
            } catch (PDOException $e) {
                echo $e;
            }
        }
    }

    public function getDecks()
    {
        $result = json_decode($this->decks, true);
        return $result;
    }
}

class Card extends cardModel
{
    public function __construct($host, $port, $username, $password, $database)
    {
        parent::__construct($host, $port, $username, $password, $database, "cards");
    }
    public function find($id)
    {
        if ($this->db->getConnectionStatus()) {
            $result = $this->db->connection->query("SELECT * FROM $this->table WHERE `id`='$id'");
            $result = $result->fetchall()[0];
            $this->id = $result[0];
            $this->name = $result[1];
            $this->attack = $result[2];
            $this->def = $result[3];
            $this->health = $result[4];
            $this->cost = $result[5];
            $this->img = $result[6];
        }
    }
    public function saveCard($id, $name, $attack, $def, $health, $cost, $img)
    {
        if ($this->db->getConnectionStatus()) {
            try {
                $stmt = $this->db->connection->query("INSERT INTO $this->table (`id`, `card_name`, `attack`, `def`, `health`, `cost`, `img`) VALUES
                ('$id', '$name', '$attack', '$def', '$health', '$cost', '$img')");
                return 0;
            } catch (PDOException $e) {
                echo $e;
            }
        }
    }

    public function getAll()
    {
        if ($this->db->getConnectionStatus()) {
            $result = $this->db->connection->query("SELECT * FROM $this->table");
            $result = $result->fetchall();
            return $result;
        }
    }
}
