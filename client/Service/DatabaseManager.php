<?php

namespace Service;


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
        return $this->PDO->exec("SELECT username,token FROM client");
    }

    public function tryLogin($uname, $password)
    {
        $password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->PDO->query("SELECT token,username FROM client WHERE username = ? AND `password` = ?;");
        $stmt->execute([$uname,$password]);
        return $stmt->fetch();
    }

    public function insertUser($username,$password){
        $password = password_hash($password, PASSWORD_DEFAULT);
        $token = $this->genToken();
        echo "INSERT INTO client (token,username,password) VALUES ($token,$username,$password);";
        $this->PDO->exec("INSERT INTO client (token,username,password) VALUES ($token,$username,`$password`);");
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