<?php

use Voryx\WebSocketMiddleware\WebSocketConnection;

class Client
{

    private string $Username;
    private WebSocketConnection $socket;

    function __construct($socket,$uname){
        $this->Username = $uname;
        $this->socket = $socket;
    }

    public function getSocket():WebSocketConnection
    {
        return $this->socket;
    }

    public function getUname():string
    {
        return $this->Username;
    }
}