<?php
    function connOrDie(){

        include('config.php');

        $conn = new mysqli($sName , $uName , $pWord , $db);
        if ($conn -> connect_error){
            echo($conn -> error);
            die("CLIENT SIDE ERROR.");
        }
        return $conn;
    }

    function getComments($conn , $vidSource){

        $stmt = $conn->prepare("SELECT `uName`, `disTime`, `comm` FROM `Comments` WHERE `vidSrc` = (?)");
        $stmt->bind_param("s", $vidSource);

        //Gets all of the comments for a single video
        if($stmt->execute()){
            $result = $stmt->get_result();
            $comments = array();
            //Loop through all the comments for this video and store them separately in out comments array
            while ($row = $result -> fetch_array(MYSQLI_ASSOC)){
                $comments[] = $row;
            }
            $result -> close();
            return $comments;
        }else{
            echo($conn -> error);
            die("CLIENT SIDE ERROR.");
        }
    }



    $conn = connOrDie();
    $vidSource = $conn -> real_escape_string(urldecode($_POST['vid']));
    $commList = getComments($conn , $vidSource);

    echo json_encode($commList);
