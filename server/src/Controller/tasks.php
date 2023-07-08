<?php

use Florian\Server\Service\Client;
use Florian\Server\Service\JSONWorker;

function sendmsg($clients, $user, $text, $JSONWorker){
    foreach($clients as $client){
        if($client instanceof Client && $JSONWorker instanceof JSONWorker){
            $client->getSocket()->send($JSONWorker->createJSON($user, "you", "msg", ["msg" => $text]));
        }
    }
}