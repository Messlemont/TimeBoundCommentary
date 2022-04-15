<?php

    function connOrDie(){

        include('config.php');

        $conn = new mysqli($sName , $user , $pWord , $db);
        if ($conn -> connect_error){
            die(" CLIENT SIDE ERROR.");
        }
        return $conn;
    }

    function checkPerms($uName , $toUpdate , $conn){
        $permission = false;

        $stmt = $conn->prepare( "SELECT `authLevel` FROM `Authorisation` WHERE `uname` = (?)" );
        $stmt->bind_param("s" , $uName);

        //If our query executes
        if($stmt->execute()){

            //and the user is found to be a lecturer
            if( $stmt->get_result() == "LECT") {

                $stmt->bind_param("s" , $toUpdate);
                if($stmt->execute()){
                    //and they are not trying to alter another lecturers auth level
                    if( $stmt->get_result() != "LECT") {
                        //then they have permission
                        $permission = true;
                    }
                }
            }

        }
        return $permission;
    }

    function changeAuth($toUpdate , $authLevel , $conn){
        //insert ignore means it will not add to the table if this user is already in the table
        $stmt = $conn->prepare( "INSERT IGNORE INTO `Authorisation`(`uName`, `authLevel`) VALUES ((?),(?))" );
        $stmt->bind_param("ss" , $toUpdate , $authLevel);

        //If our statement executes then we update the table incase the IGNORE came into effect
        if($stmt->execute()) {
            $stmt = $conn->prepare("UPDATE `Authorisation` SET `authLevel`=(?) WHERE `uName`=(?)");
            $stmt->bind_param("ss", $authLevel, $toUpdate);
            if($stmt->execute()){
                echo "User has been updated.";
                return "User has been updated.";

            }else{
                echo "Could not update user";
                return "Could not update user.";

            }
        } else{
            echo "Could not update user.";
            return "Could not update user.";

        }
    }



    $conn = connOrDie();
    $uName = $conn -> real_escape_string(urldecode($_POST['uName']));
    $toUpdate = $conn -> real_escape_string(urldecode($_POST['toUpdate']));
    $authLevel = $conn -> real_escape_string(urldecode($_POST['authLevel']));

    if (checkPerms($uName , $toUpdate , $conn) == true){
        changeAuth($toUpdate , $authLevel , $conn);
    } else{
        echo "Could not update user.";
        return "Could not update user.";
    }


