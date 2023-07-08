<?php

namespace Florian\Server\Service;

use Voryx\WebSocketMiddleware\WebSocketConnection;

const offline =  0;
const online = 1;
const away = 2;

class Client
{

    private string $Username;
    private WebSocketConnection $socket;
    private int $status;

    function __construct($socket, $uname)
    {
        $this->Username = $uname;
        $this->socket = $socket;
    }

    public function getSocket(): WebSocketConnection
    {
        return $this->socket;
    }

    public function getUname(): string
    {
        return $this->Username;
    }
}