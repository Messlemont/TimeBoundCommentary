<?php

    function connOrDie(){

        include('config.php');

        $conn = new mysqli($sName , $user , $pWord , $db);
        if ($conn -> connect_error){
            die(" CLIENT SIDE ERROR.");
        }
        return $conn;
    }

    //checks if the user has the correct authorisation level to delete this comment
    function checkPerm($conn , $uName , $commName){
        $permission = false;
        $auth = "";


        //A user is allowed to delete there own comment
        if ($uName == $commName){
            $permission = true;
        }else{
            $stmt = $conn->prepare( "SELECT `authLevel` FROM `Authorisation` WHERE `uname` = (?)" );
            $stmt->bind_param("s" , $uName);

            if($stmt->execute()){
                $auth = $stmt->get_result();
            }

            //Only a lecturer or moderator can delete a comment that they themselves did not make
            if ($auth == "LECT" or $auth == "MODE"){
                $permission = true;
            }
        }
        return $permission;
    }

    function deleteComment($conn , $uName , $vid , $time){
        $stmt = $conn->prepare( "DELETE FROM `Comments` WHERE `uName` = (?) AND `vidSrc` = (?) AND `disTime` = (?)" );
        $stmt->bind_param("ssd" , $uName , $vid , $time);

        if($stmt->execute()){
            echo "Comment has been deleted.";
            return "Comment has been deleted.";
        }else{
            return $conn->error;
            die("Error.");
        }
    }


    $conn = connOrDie();

    //Name of the user attempting to delete the comment
    $uName = $conn -> real_escape_string(urldecode($_POST['name']));
    //Name of user that made the comment
    $commName = $conn -> real_escape_string(urldecode($_POST['commName']));

    $vid = $conn -> real_escape_string(urldecode($_POST['vidSrc']));
    $time = (double) $conn -> real_escape_string(urldecode($_POST['time']));

    if (checkPerm($conn , $uName , $commName) == true){
        deleteComment($conn , $uName , $vid , $time);
    } else {
        die("Error.");
    }


?>
