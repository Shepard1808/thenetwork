<?php
class JSONWorker{

    public function createJSON(string $from, $to, string $type, array $payload)
    {
        return json_encode(["from" => $from, "to" => $to, "type" => $type, "payload" => $payload]);
    }

    public function decodeJSON($message)
    {
        return json_decode($message,true);
    }
}