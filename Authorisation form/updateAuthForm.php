<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
    <link rel="stylesheet" href="authFormStyle.css">
</head>
<body>

<div id="authForm">
        <label for="newUser">Please enter the email address of the user:</label>
        <input type="email" id="newUser">
    <br>
        <label for="options"> Select authorisation level</label>
        <select id="options">
            <option value="STUD">Student</option>
            <option value="MODE">Moderator</option>
        </select>
        <button id="formButt" onClick="postForm()">SUBMIT</button>

    <p hidden id="userName"><?php echo $_SERVER['HTTP_CIS_REMOTE_USER']?></p>
</div>

<script id="sendForm">

    function postForm(){
        var userName , updateName , authLevel , xhttp , param;
        userName = document.getElementById('userName').innerText;
        updateName = document.getElementById('newUser').innerText;
        authLevel = document.getElementById('options').value;

        //console.log("USERNAME: " + userName);
        //console.log("UPDATE NAME: " + updateName);
        //console.log("AUTHLEVEL: " + authLevel);

        param = "uName=" + userName + "&toUpdate=" + updateName + "&authLevel=" + authLevel;


        xhttp = new XMLHttpRequest();

        xhttp.open("POST" , "/~rgb18185/H3D0R4H/Dissertation/dissertation/PHP/updateAuth",true);
        //Specifies what kind of data we'll be sending
        xhttp.setRequestHeader("content-type" , "application/x-www-form-urlencoded");
        //This runs after the request has been sent.
        xhttp.onload = function(){
            //This will either tell us that the user's authorisation level has been updated, or that it has not been.
            alert( this.responseText );

        };
        xhttp.send(param);

    }


</script>

</body>
</html>
