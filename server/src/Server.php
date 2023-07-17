<?php

namespace Florian\Server;

use Florian\Server\Service\Client;
use Florian\Server\Service\JSONWorker;
use Psr\Http\Message\ServerRequestInterface;
use Ratchet\RFC6455\Messaging\Message;
use React\EventLoop\Loop;
use React\Http\HttpServer;
use React\Http\Message\Response;
use React\Socket\SocketServer;
use Voryx\WebSocketMiddleware\WebSocketConnection;
use Voryx\WebSocketMiddleware\WebSocketMiddleware;

require_once __DIR__ . '/../vendor/autoload.php';

include_once "Service/JSONWorker.php";
include_once "Controller/tasks.php";
include_once "Service/Client.php";

class Server
{

    private array $clients = [];
    private JsonWorker $JSONWorker;
        function __construct($port)
    {
        $this->JSONWorker = new JSONWorker();
        $loop = Loop::get();

        $websocket = new WebSocketMiddleware([], function (WebSocketConnection $connection) {
            echo "new Connection\n";
            $this->clients[] = new Client($connection, "user");
            $connection->on('message', function (Message $message) use ($connection) {
                $msg = $this->JSONWorker->decodeJSON($message);
                if (isset($msg['type'])) {
                    echo $msg['from'] . " sent: " . $msg['type'] . PHP_EOL;
                    switch ($msg['type']) {
                        case "msg":
                            sendmsg($this->clients, $msg['from'], $msg['payload']['msg'], $this->JSONWorker);
                            echo $msg['type'] . " => " . $msg['payload']['msg'] . PHP_EOL;
                            break;
                        default:
                            var_dump($msg);
                            break;
                    }
                }
            });

            $connection->on('close', function () {
                echo "da hat sich jemand verabschiedet";
            });

        });

        $httpRequestHandler = function (ServerRequestInterface $request) {
            var_dump("Received browser request", $request);
            return Response::html("");
        };

        $server = new HttpServer($loop, $websocket, $httpRequestHandler);
        $server->listen(new SocketServer('0.0.0.0:' . $port));
        $loop->run();
    }


}