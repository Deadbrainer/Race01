<?php
error_reporting(E_ALL);
set_time_limit(0);
ob_implicit_flush();

require_once 'user.php';
require_once 'ws.php';

$server = new WebSocketServer("10.11.6.9", 2346);

$GLOBALS['server'] = $server;
// максимальное время работы 100 секунд, выводить сообщения в консоль
$server->settings(0, true);

$GLOBALS["connections"] = array();

$server->onMessage = function ($connect, $data) {
    $connection = new Connection($connect); //TODO remove, if we migrate to the lib

    $d = json_decode($data);
    print_r($d);
    switch ($d->type) {
        case 'init':  // first connect of user
            array_push($GLOBALS["connections"], new User($connect, $d->user)); //
            echo "Client connected, there are " . count($GLOBALS["connections"]) . " clients online\n";
            $connection->send(json_encode(new Packet('auth', null)));

            foreach ($GLOBALS["connections"] as $key => $value) {
                if ($value->user->login != $d->user->login) {
                    WebSocketServer::response($value->connection, json_encode(new Packet('update_userlist', $GLOBALS["connections"])));
                }
            }
            break;
        case 'main_load':
            $connection->send(json_encode(new Packet('main_load', $GLOBALS["connections"])));
            break;
        case 'invite':  // first connect of user
            echo "Client sending invite to " . $d->data->login . '\n';
            foreach ($GLOBALS["connections"] as $key => $value) {
                if ($value->user->login == $d->data->login) {
                    WebSocketServer::response($value->connection, json_encode(new Packet('invite_recieve', $d->user)));
                }
            }
            // $connection->send('hello ' . $data);
            break;
        case 'accept_invite':
            $array = array($d->data->target, $d->data->source);
            shuffle($array);
            $first = $array[0];
            foreach ($GLOBALS["connections"] as $key => $value) {


                if ($value->user->login == $d->data->target) {
                    $answer1 = [
                        "first" => $first,
                        "enemy" => $d->data->source
                    ];

                    WebSocketServer::response($value->connection, json_encode(new Packet('start_battle', $answer1)));
                } else if ($value->user->login == $d->data->source) {
                    $answer2 = [
                        "first" => $first,
                        "enemy" => $d->data->target
                    ];

                    WebSocketServer::response($value->connection, json_encode(new Packet('start_battle', $answer2)));
                }
            }
            break;
        case 'sync_out':
            foreach ($GLOBALS["connections"] as $key => $value) {
                if ($value->user->login == $d->data->target->login) {
                    WebSocketServer::response($value->connection, json_encode(new Packet('sync_in', $d->data)));
                }
            }
            break;
        default:
            echo ("Recieved data from client " . $d->type . "\n");
            break;
    }
};

$server->onClose = function ($connection) {

    foreach ($GLOBALS["connections"] as $key => $value) {
        if ($value->connection === $connection) {
            unset($GLOBALS["connections"][$key]);
        }
    }
    echo "Connection closed\n";
};

function sig_handler($sig)
{
    $GLOBALS['server']->stopServer();
}

pcntl_signal(SIGINT,  "sig_handler");
pcntl_signal(SIGTERM, "sig_handler");
pcntl_signal(SIGHUP,  "sig_handler");

$server->startServer();
