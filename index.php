<?php
session_start();

require_once "view/View.php";
require_once "controller/Main.php";
require_once "controller/Reminder.php";
require_once "models/connection/Connector.php";
require_once "models/Model.php";
require_once "models/Users.php";

if (!$_SESSION["status"]) {
    $_SESSION['status'] = "main_menu";
    $_SESSION['isLogin'] = false;
}

$main = new Main();

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
    session_unset();
    session_destroy();
}

$_SESSION['LAST_ACTIVITY'] = time();

if ($_POST) {
    if (isset($_POST['log_in'])) {
        $log = $main->login($_POST['login'], $_POST['password']);
        if ($log == 0) {
            $_SESSION['login'] = $_POST['login'];
            $file = fopen("view/templates/assets/Images/" . $_SESSION['login'] . ".png", 'c');
            fwrite($file, base64_decode($main->getAvatar($_SESSION['login'])));
            fclose($file);
            echo "<style>.avatar button{background-image: url(view/templates/assets/Images/". $_SESSION['login'] . ";}</style>";
            $_SESSION['status'] = "main_menu";
            $_SESSION['isLogin'] = true;
        } else if ($log == 1) {
            $_SESSION['status'] = "admin";
        } else if ($log == 2) {
            echo "<script>alert('Wrong password')</script>";
        } else if ($log == 3) {
            echo "<script>alert('User not found')</script>";
        }
    }

    if (isset($_POST['reg'])) {
        if ($_POST['password'] == $_POST['confirm_password']) {
            $reg = $main->reg($_POST['login'], password_hash($_POST['password'], PASSWORD_BCRYPT), $_POST['first_name'] . " " . $_POST['last_name'], $_POST['email']);
            if ($reg == 0) {
                $_SESSION['login'] = $_POST['login'];
                $_SESSION['status'] = 'main_menu';
                $file = fopen("view/templates/assets/Images/" . $_SESSION['login'] . ".png", 'c');
                fwrite($file, base64_decode($main->getAvatar($_SESSION['login'])));
                fclose($file);
                $_SESSION['isLogin'] = true;
            } else if ($reg == 1) {
                $_SESSION['status'] = "admin";
            } else if ($reg == -1) {
                echo "<script>alert('Login already taken!')</script>";
            } else if ($reg == -2) {
                echo "<script>alert('Email already used!')</script>";
            }
        } else {
            echo "<script>alert('Password don`t match')</script>";
        }
    }

    if (isset($_POST['remind'])) {
        $rem = $main->remind($_POST['login']);
        if ($rem == 0) {
            echo "<script>alert('Password sended')</script>";
            $_SESSION['status'] = "login";
        } else if ($rem == 1) {
            echo "<script>alert('Wrong login')</script>";
        }
    }

    if (isset($_POST['send_avatar'])) {
        $ava = $main->saveAvatar($_SESSION['login'], $_FILES['avatar']['tmp_name']);
        $file = fopen("view/templates/assets/Images/" . $_SESSION['login'] . ".png", 'c');
        fwrite($file, base64_decode($main->getAvatar($_SESSION['login'])));
        fclose($file);
    }

    if (isset($_POST['send_card'])) {
        $ava = $main->saveCard($_POST['id'], $_POST['name'], $_POST['attack'], $_POST['def'], $_POST['health'], $_POST['cost'], $_FILES['img']['tmp_name']);
    }

    if (isset($_POST['logout'])) {
        $_SESSION['status'] = "login";
    }

    if (isset($_POST['go_remind'])) {
        $_SESSION['status'] = "reminder";
    }

    if (isset($_POST['go_reg'])) {
        $_SESSION['status'] = "reg";
    }

    if (isset($_POST['go_log'])) {
        $_SESSION['status'] = "login";
    }

    if (isset($_POST['go_main'])) {
        $_SESSION['status'] = "main_menu";
    }

    if (isset($_POST['go_change_ava'])) {
        $_SESSION['status'] = "avatar";
    }

    if (isset($_POST['play'])) {
        if ($_SESSION['isLogin'] == true) {
            $arr = $main->getDecks($_SESSION['login']);
            $_SESSION['countdeck1'] = count($arr[1]);
            $_SESSION['countdeck2'] = count($arr[2]);
            $_SESSION['countdeck3'] = count($arr[3]);
            $_SESSION['status'] = "chooseDeck";
        } else {
            $_SESSION['status'] = "login";
        }
    }

    if (isset($_POST['go_collection'])) {
        if ($_SESSION['isLogin'] == true) {
            $arr = $main->getDecks($_SESSION['login']);
            $_SESSION['countdeck1'] = count($arr[1]);
            $_SESSION['countdeck2'] = count($arr[2]);
            $_SESSION['countdeck3'] = count($arr[3]);
            $_SESSION['cards'] = $main->getCards();
            $_SESSION['status'] = "collection";
        } else {
            $_SESSION['status'] = "login";
        }
    }

    if (isset($_POST['deck'])) {
        $_SESSION['deck'] = $_POST['deck'];
        $res = $main->getDeck($_SESSION['deck'], $_SESSION['login']);
        $_SESSION['chosen_deck'] = $res;
        $arr = $main->getDecks($_SESSION['login']);
        $_SESSION['countdeck1'] = count($arr[1]);
        $_SESSION['countdeck2'] = count($arr[2]);
        $_SESSION['countdeck3'] = count($arr[3]);
    }

    if (isset($_POST['cardAdd'])) {
        $res = $main->addtoDeck($_SESSION['login'], $_SESSION['deck'], $_POST['cardAdd']);
        if ($res == -1) {
            echo "<script>alert('Deck is full')</script>";
        } else if ($res == -2) {
            echo "<script>alert('Card already in deck')</script>";
        }
        $res = $main->getDeck($_SESSION['deck'], $_SESSION['login']);
        $_SESSION['chosen_deck'] = $res;
        $arr = $main->getDecks($_SESSION['login']);
        $_SESSION['countdeck1'] = count($arr[1]);
        $_SESSION['countdeck2'] = count($arr[2]);
        $_SESSION['countdeck3'] = count($arr[3]);
    }

    if (isset($_POST['cardDelete'])) {
        $res = $main->removeFromDeck($_SESSION['login'], $_SESSION['deck'], $_POST['cardDelete']);
        $res = $main->getDeck($_SESSION['deck'], $_SESSION['login']);
        $_SESSION['chosen_deck'] = $res;
        $arr = $main->getDecks($_SESSION['login']);
        $_SESSION['countdeck1'] = count($arr[1]);
        $_SESSION['countdeck2'] = count($arr[2]);
        $_SESSION['countdeck3'] = count($arr[3]);
    }

    if (isset($_POST['deckforbattle'])) {
        $_SESSION['deck'] = $_POST['deckforbattle'];
        $res = $main->getDeck($_POST['deckforbattle'], $_SESSION['login']);
        if (count($res) == 10) {
            $arr = $main->getCardsfromDeck($res);
            $_SESSION['battle_deck'] = $arr;
            $_SESSION['status'] = "chooseEnemy";
        } else {
            echo "<script>alert('Chosen deck is not full')</script>";
        }
    }
}

switch ($_SESSION['status']) {
    case "login":
        (new View("view/templates/login.html"))->render();
        break;
    case "reg":
        (new View("./view/templates/reg.html"))->render();
        break;
    case "reminder":
        (new View("view/templates/reminder.html"))->render();
        break;
    case "main_menu":
        (new View("./view/templates/main_menu.php"))->renderPhp(); 
        break;
    case "collection":
        (new View("./view/templates/collection.php"))->renderPhp();
        break;
    case "avatar":
        (new View("./view/templates/avatar.php"))->renderPhp();
        break;
    case "chooseDeck":
        (new View("./view/templates/chooseDeck.php"))->renderPhp();
        break;
    case "chooseEnemy":
        (new View("./view/templates/chooseEnemy.php"))->renderPhp();
        break;
    default:
        (new View("./view/templates/main_menu.php"))->renderPhp();
        break;
}
