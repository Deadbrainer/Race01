<?php
include "models/Users.php";
include "Reminder.php";
interface ControllerInterface
{
    public function __construct();
    public function execute();
    public function login($login, $password);
    public function reg($login, $password, $fullname, $email);
    public function remind($login);
    public function saveAvatar($login, $img);
    public function getAvatar($login);
    public function saveCard($id, $name, $attack, $def, $health, $cost, $img);
    public function getCards();
    public function addtoDeck($login, $id_deck, $id_card);
    public function getDeck($deck_id, $login);
    public function removeFromDeck($login, $id_deck, $id_card);
    public function getCardsfromDeck($deck);
    public function getDecks($login);
}

class Main implements ControllerInterface
{
    public function __construct()
    {
    }
    public function execute()
    {
    }
    public function login($login, $password)
    {
        $user = new Users('127.0.0.1', null, "ezuienko", "securepass", "sword");
        $user->find($_POST['login']);
        if ($user->id != null) {
            if (password_verify($password, $user->password)) {
                if ($user->status == "user") {
                    return 0;
                } else if ($user->status == "admin") {
                    return 1;
                }
            } else {
                return 2;
            }
        } else {
            return 3;
        }
    }
    public function remind($login)
    {
        $user = new Users('127.0.0.1', null, "ezuienko", "securepass", "sword");
        $user->find($login);
        if ($user->id != null) {
            remind($user->email, $user->password);
            return 0;
        } else {
            return 1;
        }
    }
    public function reg($login, $password, $fullname, $email)
    {
        $user = new Users('127.0.0.1', null, "ezuienko", "securepass", "sword");
        $res = $user->save($login, $password, $fullname, $email);
        if ($res == 1) {
            return -1;
        } else if ($res == 2) {
            return -2;
        } else {
            $user->find($login);
            if ($user->status == 'user') {
                return 0;
            } else if ($user->status == 'admin') {
                return 1;
            }
        }
    }

    public function saveAvatar($login, $img)
    {
        $user = new Users('127.0.0.1', null, "ezuienko", "securepass", "sword");
        $res  = $user->find($login);
        if($user->id != null){
            $user->saveAvatar($img);
        }
    }

    public function getAvatar($login){
        $user = new Users('127.0.0.1', null, "ezuienko", "securepass", "sword");
        $user->find($login);
        return $user->avatar;
    }

    public function saveCard($id, $name, $attack, $def, $health, $cost, $img){
        $card = new Card('127.0.0.1', null, "ezuienko", "securepass", "sword");
        $card->saveCard($id, $name, $attack, $def, $health, $cost, base64_encode(file_get_contents($img)));
    }

    public function getCards()
    {
        $card = new Card('127.0.0.1', null, "ezuienko", "securepass", "sword");
        return $card->getAll();
    }

    public function addtoDeck($login, $id_deck, $id_card)
    {
        $user = new Users('127.0.0.1', null, "ezuienko", "securepass", "sword");
        $user->find($login);
        return $user->addtoDeck($id_deck, $id_card);
    }

    public function getDeck($deck_id, $login)
    {
        $user = new Users('127.0.0.1', null, "ezuienko", "securepass", "sword");
        $user->find($login);
        return $user->getDeck($deck_id);
    }

    public function removeFromDeck($login, $id_deck, $id_card){
        $user = new Users('127.0.0.1', null, "ezuienko", "securepass", "sword");
        $user->find($login);
        return $user->removeFromDeck($id_deck, $id_card);
    }

    public function getCardsfromDeck($deck){
        $json = array();
        foreach($deck as $key=>$value){
            $temp = array();
            $card = new Card('127.0.0.1', null, "ezuienko", "securepass", "sword");
            $card->find($value+1);
            $file = fopen("view/templates/assets/Images/" . $card->id . ".png", 'c');
            fwrite($file, base64_decode($card->img));
            fclose($file);
            array_push($temp, $card->id, $card->name, $card->attack, $card->def, $card->health, $card->cost, "view/templates/assets/Images/" . $card->id . ".png");
            array_push($json, $temp);
        }
        $json = json_encode($json);
        return $json;
    }

    public function getDecks($login){
        $user = new Users('127.0.0.1', null, "ezuienko", "securepass", "sword");
        $user->find($login);
        return $user->getDecks();
    }
}
