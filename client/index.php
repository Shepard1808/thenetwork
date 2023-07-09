<?php

use Service\DatabaseManager;

    if(isset($_GET['token'])){
        include_once "chat.html";
    }else{
        if(isset($_GET['action'])){
            switch ($_GET['action']){
                case "login":
                    include_once "Service/DatabaseManager.php";
                    $database = new DatabaseManager();
                    echo $_POST['username'] . "  :  " . $_POST['password'];
                    $result = $database->tryLogin($_POST['username'],$_POST['password']);
                    if($result == null){
                        echo "<script>alert('Falsches Passwort oder ungültiger Benutzername')</script>";
                    }
                    break;
                default:
                    break;
            }
        }else {
            include_once "home.html";
        }
    }