<?php

use Psr\Http\Message\ServerRequestInterface;
use Ratchet\RFC6455\Messaging\Message;
use React\EventLoop\Loop;
use React\Http\HttpServer;
use React\Http\Message\Response;
use React\Socket\ConnectionInterface;
use React\Socket\SocketServer;
use Voryx\WebSocketMiddleware\WebSocketConnection;
use Voryx\WebSocketMiddleware\WebSocketMiddleware;

require_once __DIR__ . '/vendor/autoload.php';

include_once "JSONWorker.php";
include_once "tasks.php";

class Server
{


    private array $clients = [];
    private JSONWorker $JSONWorker;

    private array $responselist = [];

    function __construct($port){
        $this->JSONWorker = new JSONWorker();
        $loop = Loop::get();


        $loop->addPeriodicTimer(45.0, function () {

            foreach ($this->clients as $item){
                if($item instanceof Node){
                    $item->getSocket()->send($this->JSONWorker->createJSON("server",$item->getUname(),"heartbeat",[]));
                }
            }

            echo "heartbeat sent" . PHP_EOL;
            for($i = 0; $i < 1000; $i++) {
                sleep(0.01);
            }
            echo "checking activity..." . PHP_EOL;
            verifyExistence($this->clients,$this->responselist);
            $this->responselist = [];
        });

        $websocket = new WebSocketMiddleware([], function (WebSocketConnection $connection) {
            echo "new Connection\n";

            $connection->on('message', function (Message $message) use ($connection) {
                $msg = $this->JSONWorker->decodeJSON($message);
                if(isset($msg['type'])) {
                    echo $msg['from']. " sent: " . $msg['type']. PHP_EOL;
                    switch ($msg['type']){
                        default:
                            break;
                    }
                }
                //echo "Received message from client: " . $message . PHP_EOL;
            });

            $connection->on('close', function (){
                //function to remove item from array
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