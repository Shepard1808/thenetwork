<?php

namespace Florian\Server\Service;


use PDO;

class DatabaseManager
{
    private PDO $PDO;

    public function __construct()
    {
        $dbValues = parse_ini_file("data.ini",false,INI_SCANNER_RAW);
        $this->PDO = new PDO(
            'mysql:host=localhost;dbname='.$dbValues['dbname'].';port=3306',$dbValues['username'],$dbValues['password'],
            [PDO::FETCH_ASSOC]);

    }

    public function getUsers(){
        $stmt =  $this->PDO->prepare("SELECT username,token FROM client");
        $stmt->execute([]);
        return $stmt->fetchAll();
    }

    public function tryLogin($uname, $password)
    {
        $stmt = $this->PDO->prepare("SELECT * FROM client WHERE username = ?;");
        $stmt->execute([$uname]);
        $rs = $stmt->fetch();
        if($rs === null){
            return null;
        }
        if(password_verify($password,$rs['password'])){
            return $rs;
        }else{
            return null;
        }

    }

    public function insertUser($username,$password): void
    {
        $password = password_hash($password, PASSWORD_DEFAULT);
        $token = $this->genToken();
        $stmt = $this->PDO->prepare("INSERT INTO client (token,username,password) VALUES (?,?,?);");
        $stmt->execute([$token,$username,$password]);
    }

    private function genToken(): string
    {
        $chars = "1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";

        $token = "";

        for($i = 0; $i < 16; $i++){
            $token = $token . substr($chars, rand(0, strlen($chars)),1);
        }
        return $token;
    }

}