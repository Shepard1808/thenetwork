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
include_once "Manager.php";
include_once "Client.php";

class Server
{


    private array $managers = [];
    private array $clients = [];
    private JSONWorker $JSONWorker;

    private array $responselist = [];

    function __construct($port){
        $this->JSONWorker = new JSONWorker();
        $loop = Loop::get();


        $loop->addPeriodicTimer(10.0,function () {
            foreach (array_merge($this->managers, $this->clients) as $item){
                echo $item->getUname() . PHP_EOL;
            }

            ClientToManager($this->clients,$this->managers,$this->JSONWorker);
        });


        $loop->addPeriodicTimer(45.0, function () {

            foreach (array_merge($this->clients, $this->managers) as $item){
                if($item instanceof Node){
                    $item->getSocket()->send($this->JSONWorker->createJSON("server",$item->getUname(),"heartbeat",[]));
                }
            }

            echo "heartbeat sent" . PHP_EOL;
            for($i = 0; $i < 1000; $i++) {
                sleep(0.01);
            }
            echo "checking activity..." . PHP_EOL;
            verifyExistence($this->clients,$this->managers,$this->responselist);
            $this->responselist = [];
        });

        $websocket = new WebSocketMiddleware([], function (WebSocketConnection $connection) {
            echo "new Connection\n";

            $connection->on('message', function (Message $message) use ($connection) {
                $msg = $this->JSONWorker->decodeJSON($message);
                if(isset($msg['type'])) {
                    echo $msg['from']. " sent: " . $msg['type']. PHP_EOL;
                    switch ($msg['type']){
                        case "introduction":
                            $connection->send($this->JSONWorker->createJSON('server', 'client', 'verifyName', []));
                            break;
                        case "verify":
                            if(verify($msg['from'], array_merge($this->clients, $this->managers))){
                                if(strpos($msg['from'],"client") !== false){
                                    $this->clients[] = new Client($msg['from'], $connection);
                                    ClientToManager($this->clients, $this->managers, $this->JSONWorker);
                                }else if (strpos($msg['from'],"manager") !== false){
                                    $this->managers[] = new Manager($msg['from'], $connection);
                                }else{
                                    $connection->close();
                                }
                            }else{
                                $last = verifyInactive($msg['from'],$this->clients);
                                if($last >= 0){
                                    $this->clients[$last]->updateSocket($connection);
                                    $this->clients[$last]->setStatus(true);
                                }else {
                                    $connection->close();
                                }
                            }
                            break;
                        case "refresh":
                            if(strpos($msg['from'],"manager") !== false) {
                                $key = sendRefresh($msg['to'],$this->clients,$this->JSONWorker,$msg['from']);
                                if($key >= 0) {
                                    $this->clients[$key]->setStatus(false);
                                }
                            }
                            break;
                        case "refreshAll":
                            refreshAll($this->clients, $this->JSONWorker,$msg['from']);
                            foreach ($this->clients as $client){
                                $client->setStatus(false);
                            }
                            break;
                        case "heartbeat":
                            $this->responselist[] = $msg['from'];
                            break;
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