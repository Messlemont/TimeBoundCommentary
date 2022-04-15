<?php

    function connOrDie(){
        include('config.php');

        $conn = new mysqli($sName , $user , $pWord , $db);
        if ($conn -> connect_error){
            die(" CLIENT SIDE ERROR.");
        }
        return $conn;
    }

    function addNewComm($conn , $vid , $uName , $time , $comm){

        $stmt = $conn->prepare( "INSERT INTO `Comments` VALUES (? , ? , ? , ?)" );
        $stmt->bind_param("ssds" , $vid , $uName , $time , $comm);  //SSDS because its string,string,double,string

        //source , name , time , comment
        if($stmt->execute()){
            echo "Comment has been saved.";
            return "Comment has been saved.";
        }else{
            die("Error.");
        }

    }

    $conn = connOrDie();
    //Gets the authenticated users username - Should work since these are also in the folder?
    //$uName = $_SERVER['HTTP_CIS_REMOTE_USER'];

    $uName = $conn -> real_escape_string(urldecode($_POST['name']));
    $vid = $conn -> real_escape_string(urldecode($_POST['vidSrc']));
    $time = $conn -> real_escape_string(urldecode($_POST['time']));
    $comm = $conn -> real_escape_string(urldecode($_POST['comBod']));
    $result = addNewComm($conn , $vid, $uName , $time , $comm);

