<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>M.Esslemont Diss Project</title>
    <link rel="stylesheet" href="commentStyle.css">
</head>
<body>

<!--******** Samples for your convenience ********-->
<!--    IFRAME

<iframe id="lectVideo" width="640" height="360" src="https://www.youtube.com/embed/IjGfkbfVGw8" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen>
    This browser does not support the iFrame tag.
</iframe>

-->
<!--    VIDEO

<video id="lectVideo" width="640" height="360" src="Egg.mp4" controls>
            This browser does not support the video tag.
</video>

-->
<!--**********************************************-->


<!--*************** THE TEMPLATE IS HERE ***************-->
<div id="wrapper">
    <div id="leftSide">
        <iframe id="lectVideo" width="640" height="360" src="https://www.youtube.com/embed/IjGfkbfVGw8" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen>
            This browser does not support the iFrame tag.
        </iframe>
        <!--Empty div used if we make a YouTube iframe api player object-->
        <div id="player"></div>
    </div>

    <div id="rightSide">
        <div id="comments">
            <ul id="commList"></ul>
        </div>
        <!-- User will enter comments here. they will appear in the comments div at the correct time -->
        <form method="post" id="commForm" onsubmit="return false">
            <input type="text" id="comm" required><input type="submit" value="Comment" id="commButt" onclick="addComment()" required>
            <p>
                <input type="range" min="0" max="1" value="0" id="timeSlider">
                <b>Comment at: </b> <span id="commTime">0s</span>
            </p>
        </form>
    </div>
    <p hidden id="userName"><?php echo $_SERVER['HTTP_CIS_REMOTE_USER']?></p>
    <p hidden>Video at: <span id="videoTime">0s</span></p>
</div>

<script id="DomPure" type="text/javascript" src="DOMPurify-main/DOMPurify-main/dist/purify.min.js"></script>

<script id="commScript">

    var lectVideo = document.getElementById('lectVideo');

    //src will be a boolean representing the video source-
    //TRUE means our video is from youtube
    //FALSE means our video is an embedded mp4 file
    var source = false;

    //This stores the comments we get from the local storage
    var commentList =[];
    //This stores the replies to comments, which we also get from local storage
    var replies = [];

    //Stores the potential time for the users comment (value of timeSlider)
    var boundTime;
    var timeSlider = document.getElementById("timeSlider");

    //Updates the comment time / the displayed time when the user moves the slider
    timeSlider.oninput = function(){
        document.getElementById("commTime").innerText = this.value + "s";
        boundTime = this.value;
    };


    function init(lectVideo){

        if ( (lectVideo.src).includes("https://www.youtube") ){
            console.log("TRUE");
            //if the video is from youtube we need to set up the YouTube iframe API
            source = true;
            setUpYouTube();
        }else{
            console.log("FALSE");
            source = false;
            timeSlider.setAttribute("max" , getVidDur(source , lectVideo));
            getComments();
            commentLoop();
        }
    }

    init(lectVideo);

    //This function will begin setting up the YouTube iFrame API
    function setUpYouTube(){
        var tag = document.createElement('script');
        tag.src = "https://www.youtube.com/iframe_api";
        var firstScriptTag = document.getElementsByTagName('script')[0];
        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
    }

    //The YouTube iframe API will call this method itself
    function onYouTubeIframeAPIReady(){
        //Here out lectVideo variable will now be a YouTube API Player object, instead of the previous iframe.
        var id = getVideoID();
        console.log("vidID: " + id);

        var embedPlayer = document.getElementById("lectVideo");

        //Use old player attributes for the new player, which takes over the lectVideo object
        lectVideo = new YT.Player('player',{
            height: embedPlayer.getAttribute("height"),
            width: embedPlayer.getAttribute("width"),
            videoId: id,
            origin: window.location.origin,
            events:{
                'onReady': onPlayerReady,
                'onStateChange': commentLoop
            }
        });
    }

    //The API will call this function when the video player is ready. If the video isn't
    function onPlayerReady(event) {
        //If we create an iframe tag using the API, we no longer need the original iframe tag
        document.getElementById("lectVideo").remove();

        //make sure the timer starts working once the video begins to play
        lectVideo.addEventListener("play" , displayTime());

        //Now our slider ranges from 0s to the final second of the video
        document.getElementById("timeSlider").setAttribute("max",getVidDur(source , lectVideo));

        //This will SELECT all the comments for this video from our comments table and format them before then storing
        // them in the localstorage to be displayed at the appropriate time
        getComments();

        //Set to true since this is a YouTube video
        source = true;

    }

    function getVideoID(){
        //Function takes the source of the embedded video and gets the video ID from it
        var PREFIX = "https://www.youtube.com/embed/";

        //Cant use getVidSrc because the iframe has not been created yet
        var url = document.getElementById("lectVideo").src;
        //console.log("SRC: " + url);

        var videoID = url.slice(PREFIX.length);
        //console.log("videoID: " + videoID);

        return videoID
    }

    //When the player is ready, get the comments for this video.
    function getComments(){
        var xhttp , vid , param;
        vid = getVidSrc(source , lectVideo);
        param = "vid=" + vid;
        xhttp = new XMLHttpRequest();

        xhttp.open("POST" , "/~rgb18185/H3D0R4H/M.Esslemont_fourth_year_project/PHP/getComm.php",true);
        //Specifies what kind of data we'll be sending
        xhttp.setRequestHeader("content-type" , "application/x-www-form-urlencoded");
        //This runs after the request has been sent.
        xhttp.onload = function(){
            console.log("Response text: " + this.responseText);
            var commList = this.responseText;
            prepComments(commList);
        };
        xhttp.send(param);
    }

    function displayTime(){
        setInterval(function() {
            document.getElementById("videoTime").innerText = getVidTime(source , lectVideo) + "s";
            //console.log("VIDEO TIME: " + getVidTime(source , lectVideo) + "s");
        },1000);
    }

    function deleteComment(delButtInfo){
        var confirmDelete , xhttp, param, uName, commInfo;

        //First lets check that this wasnt a mistake, and ask for confirmation!
        confirmDelete = confirm("Are you sure you want to delete this comment?");
        //If the user has NOT confirmed they wish to delete the comment, just exit the function
        if (confirmDelete != true){
            return;
        }

        uName = (document.getElementById('userName').innerText);

        commInfo = delButtInfo.value;
        console.log("Button value: "+commInfo);
        commInfo = commInfo.split("#@#");

        param = "name=" + uName + "&commName=" + commInfo[0] + "&time=" + commInfo[1] + "&vidSrc=" + getVidSrc(source , lectVideo);
        xhttp = new XMLHttpRequest();

        xhttp.open("POST", "/~rgb18185/H3D0R4H/M.Esslemont_fourth_year_project/PHP/delComm.php", true);
        //Specifies what kind of data we'll be sending
        xhttp.setRequestHeader("content-type", "application/x-www-form-urlencoded");
        //This runs after the request has been sent.
        xhttp.onload = function () {
            if (this.responseText == "Comment has been deleted.") {
                alert("This comment has been successfully deleted!");
            } else {
                alert("Uh oh! This comment has NOT been deleted. Are you sure you have permission to do this? Please reload the page and try again.");
                console.log("Response text: " + this.responseText);
            }
        };
        xhttp.send(param);
    }

    function addComment(){
        var xhttp , param, uName, comBod, valid , cleanComm;

        //Once they click the button, disable it for a second so they dont spam submit a comment
        document.getElementById('commButt').disabled = true;
        setTimeout(function () {
            document.getElementById('commButt').disabled = false;
        }, 1000);

        //Gets the users username
        uName = (document.getElementById('userName').innerText);

        //If the user is somehow able to access the page but bypasses the authorisation (meaning we have no username)
        //do not allow them to make a comment.
        if (uName == ""){
            alert("You do not have the authorisation to make a comment!");
            return;
        }


        comBod = document.getElementById('comm').value;

        //This helps configure how DOMPurify will sanitise the user input.
        DOMPurify.addHook('afterSanitizeAttributes', function (node) {
            //THIS HOOK MEANS ONLY HTTPS LINKS WILL BE WHITELISTED
            // build an anchor to map URLs to
            var anchor = document.createElement('a');

            // check all href attributes for validity
            if (node.hasAttribute('href')) {
                anchor.href = node.getAttribute('href');
                if (anchor.protocol && !anchor.protocol.match('https')) {
                    node.removeAttribute('href');
                }
            }
            // check all action attributes for validity
            if (node.hasAttribute('action')) {
                anchor.href = node.getAttribute('action');
                if (anchor.protocol && !anchor.protocol.match('https')) {
                    node.removeAttribute('action');
                }
            }
            // check all xlink:href attributes for validity
            if (node.hasAttribute('xlink:href')) {
                anchor.href = node.getAttribute('xlink:href');
                if (anchor.protocol && !anchor.protocol.match('https')) {
                    node.removeAttribute('xlink:href');
                }
            }

            //THIS HOOK MEANS LINKS WILL OPEN IN A NEW TAB/WINDOW
            // set all elements owning target to target=_blank
            if ('target' in node) {
                node.setAttribute('target', '_blank');
            }
            // set non-HTML/MathML links to xlink:show=new
            if (
                !node.hasAttribute('target') &&
                (node.hasAttribute('xlink:href') || node.hasAttribute('href'))
            ) {
                node.setAttribute('xlink:show', 'new');
            }
        });

        cleanComm = DOMPurify.sanitize(comBod); //Cleaning input might mean out input is now a null value
        if(cleanComm.length == 0){
            alert("Uh oh! Your comment has NOT been saved. You didnt write anything!");
        } else {

            if(cleanComm.length > 500){
                alert("Your comment is longer than 500 characters!\n Perhaps try leaving a link to an external learning resource instead?");
                return;
            }


            //comment time stored as a number to 2 decimal place (10 comments for every second seems more than reasonable - 2nd dp is for replies)
            let commTime = (parseFloat(boundTime)).toFixed(2);

            //If the user tries to make a comment AFTER the video is finished or BEFORE it even starts: stop them.
            if (commTime > getVidDur(source , lectVideo) || commTime < 0){
                alert("You can not create a comment at that time.");
                return;
            }

            if (commTime == 0.00){
                commTime = commTime + 0.10;
            }


            do {

                //If there is already a comment made at this time for this video
                if (localStorage.getItem(commTime.toString()) != null) {

                    let checkRep = ((parseFloat(commTime)).toFixed(2)).toString()

                    if(checkRep.charAt(checkRep.length - 1) != '0'){
                        //At this point there must be 9 replies already, the maximum amount for a single comment thread.
                        if(commTime % 0.1 != 9) {
                            //Increase time by 0.01 and loop (because this is a reply)
                            commTime = (parseFloat(commTime) + parseFloat(0.01)).toFixed(2)
                        }
                    }else{
                        //Increase the time by 0.1s and loop (this is an original comment)
                        commTime = (parseFloat(commTime) + parseFloat(0.10)).toFixed(2)
                    }

                } else {
                    valid = true;
                }
            } while (valid === false);

            console.log("USERNAME: " + uName);
            console.log("COMMENT: " + cleanComm);
            console.log("TIME: " + commTime);
            console.log("VIDEO: " + String(getVidSrc(source, lectVideo)));

            //List of parameters to be sent to postComm.php
            param = "name=" + uName + "&comBod=" + cleanComm + "&time=" + commTime + "&vidSrc=" + getVidSrc(source, lectVideo);

            xhttp = new XMLHttpRequest();

            xhttp.open("POST", "/~rgb18185/H3D0R4H/M.Esslemont_fourth_year_project/PHP/postComm.php", true);
            //Specifies what kind of data we'll be sending
            xhttp.setRequestHeader("content-type", "application/x-www-form-urlencoded");
            //This runs after the request has been sent.
            xhttp.onload = function () {
                if (this.responseText == "Comment has been saved.") {
                    alert("Your comment has been successfully saved!");

                    localStorage[commTime] = uName + "#@#" + cleanComm + "#@#" + commTime ;

                    document.getElementById('comm').value = "";
                    boundTime = 0.00;
                    timeSlider.value = 0;
                    document.getElementById("commTime").innerText = timeSlider.value + "s";

                } else {
                    alert("Uh oh! Your comment has NOT been saved. Please reload the page and try again.");
                    //console.log("Response text: " + this.responseText);
                }
            };
            xhttp.send(param);
        }
    }

    //Every second this function will check to see what comments need to be displayed and will then display them
    //This function is either called when the YouTube iFrame API is ready or by the init function if out video is an MP4
    function commentLoop(){
        let originalTime;
        //every second, loop and check for comments to display
        let commLoop = setInterval(function () {
            originalTime = getVidTime(source , lectVideo);
            //console.log("OG time:" + originalTime);
            checkComments(originalTime);
            displayComments();
        },1000);
    }

    //This function checks if any comments need to be displayed, if they do then it adds them to the list of comments
    //to display and then will every 1.5 seconds check to see if it is time to stop displaying the comment.
    function checkComments(commentTime){
        //console.log("Commentlist: " + commentList.toString());
        //round the time to 2dp but makes sure the 2nd dp is 0
        let time = commentTime.toFixed(1);
        time = parseFloat(time).toFixed(2);
        //For that time we then loop 10 times to check from i.0seconds to i.9seconds for a comment, 2nd dp is for replies to comments.
        for(let x = 0; x < 10; x++) {
            let comm = localStorage[time];
            //If there is a comment stored at this time -And it is not already in the comment list
            if (comm && !commentList.includes(comm)) {
                //Add that comment to list of comments we're currently displaying - then check if this comment has any replies
                commentList.push(comm);
                checkReplies(time);
                //then every 1.5s we check to see if the video has progressed 10~ seconds. when it has, remove the comment.
                //and if the user rewinds the video, stop displaying it, otherwise it could end up being displayed twice
                let y = setInterval(function () {
                    let timeDiff = getVidTime(source , lectVideo) - commentTime
                    if (timeDiff >= 10 || timeDiff <= -1) {
                        //Since this loop happens for every comment, by the time its time to remove a comment it should be
                        //at the top of the array. Once the comments removed, we stop our loop and clear the interval.
                        commentList.shift();
                        clearInterval(y);
                    }
                }, 1500);
            }
            time = (parseFloat(time) + parseFloat(0.1)).toFixed(2);
        }
    }

    //This function sets up that a comment is meant to reply to another one and checks to see if the comment chain is
    //already at its maximum length of 10 comments (including the original comment the others are replying to). It does
    //not submit the comment itself.
    function prepReply(repButtInfo){
        let time , repNum;
        time = repButtInfo.value;
        //console.log("Time: " + time);

        //Given a time of x.yzS this gives us x.y0s, meaning we can check how many replies the original comment has.
        time = parseFloat(time).toFixed(1);
        time = parseFloat(time).toFixed(2);
        //console.log("Format time: " + time);

        //If this comment chain is already 10 comments long, tell the user it would be more productive to bring whatever
        //they are discussing to the lecturer instead
        if( replies[time].length == 9 ) {
            alert("This comment chain already has 10 comments!\nTry to bring this topic up in your next tutorial instead.");
            document.getElementById('comm').innerText = "";
        }else{
            repNum = (replies[time].length + 1) / 100;
            //console.log("Reply number: " + repNum);

            //timeSlider can only have integer values, so its better for us to update boundTime with the precise value
            //and give timeSlider an approx value so the user can see that the boundTime has changed.
            boundTime = parseFloat(time) + parseFloat(repNum);
            boundTime = parseFloat(boundTime).toFixed(2);
            //console.log("bT: " + boundTime);

            //Updating timeSliders value manually doesnt call tS.onInput, so we need to update commTime manually
            timeSlider.value = boundTime;
            document.getElementById("commTime").innerText = timeSlider.value + "s";
        }
    }

    //This function checks to see if a comment has any replies, if it does it will add them as a list of comments to our
    //replies array with the original comments time acting as the index
    function checkReplies(time){
        let reply, commReplies , repTime;
        //Just to make sure our float keeps the correct amount of DP
        repTime = parseFloat(time).toFixed(2);
        repTime = parseFloat(repTime) + parseFloat(0.01);
        repTime = parseFloat(repTime).toFixed(2);
        console.log("rT: " + repTime);
        commReplies = [];

        //Start at 0.01 because a.b0s is the original comment - a.b(1-9)s is for the replies
        for(let x = 0.01; x <= 0.09; x += 0.01){
            //If there is a reply for this
            reply = localStorage[repTime];
            if(reply && !replies.includes(reply)){
                commReplies.push(reply);
                //console.log("cR: " + commReplies);
            }
            //Trying to use x to do this just meant that only a.b1s was ever checked for some reason
            repTime = parseFloat(repTime) + parseFloat(0.01);
            repTime = parseFloat(repTime).toFixed(2);
            console.log("rT: " + repTime)
        }
        replies[time] = commReplies;
    }

    //This function takes the time of a comment that is going to be displayed, and will check how many replies it has
    //and sets them up to be added as HTML elements, similar to displayComments
    function displayReplies(time){
        let reply , topRep , botRep , wholeRep, repDiv = "" , reps;
        //We already know this time has replies, so we simply loop through them
        reps = replies[time];
        reps.forEach(function(currRep){
            reply = currRep.split("#@#");

            topRep = "<div class='topCom'><b>" + reply[0] + ":</b>" +
                "<div class='comButts'>"+
                "<button class='delButt' value='" + reply[0] + "#@#" + reply[2]  +"' onclick=deleteComment(this)>D</button>"
                + "</div></div>";


            botRep = "<p class='commBody'>" + reply[1] + "</p>";

            wholeRep = "<div class='reply'>" + topRep + botRep + "</div>"

            //This will simply return all of the replies, ready to be added on to the end of the comment
            repDiv = repDiv + wholeRep
        })
        return repDiv
    }

    //If the video is playing, commentLoop will call this function every second to update what the comment list is
    //currently displaying
    function displayComments(){
        let currentComment , topCom , botCom, wholeCom , time , commReplies = [];
        //Loops through the list of current comments and adds them all as an unordered list to displayComm.
        let commList = document.getElementById('commList');
        commList.innerHTML = '';
        commentList.forEach(function (currComm) {

            currentComment = currComm.split("#@#");

            topCom = "<div class='topCom'><b>" + currentComment[0] + ":</b>" +
                "<div class='comButts'>"+
                "<button class='replyButt' value='" + currentComment[2] + "' onclick=prepReply(this)>R</button>" +
                "<button class='delButt' value='" + currentComment[0] + "#@#" + currentComment[2]  +"' onclick=deleteComment(this)>D</button>"
                + "</div></div>";


            botCom = "<p class='commBody'>" + currentComment[1] + "</p>";

            time = parseFloat(currentComment[2]).toFixed(2);
            //console.log("dC time: " + time);
            //console.log("rep[t]: " + replies[time]);

            //if we have replies to this comment, call displayReplies
            if(replies[time].length != 0){
                //console.log("DISPLAY REPLIES FOR " + time + " : ");
                commReplies = displayReplies(time);
                //console.log(commReplies);
            }

            wholeCom = topCom + botCom + commReplies;
            commReplies = [];

            //Create a list item element, add it to unordered list, change it to be the current comment were adding from the array
            let li = document.createElement('li');
            commList.appendChild(li);
            li.className = "comment";
            li.innerHTML += wholeCom;
        });
    }

    //This function will properly format the comments weve selected from the SQL table before storing them in the local
    //cache so they can be displayed at the proper times during the video. doing this means we only query the server
    //once, which is more efficient.
    function prepComments(commList){
        var comments, cTime, cBody, cName;

        //Since we store video comments in local storage, this means we should clear the comments within range of our
        //video, in case there are comments left over from a previous one.
        for(let i = 0.00; i <= getVidDur(source , lectVideo) +1; i=i+0.01){
            localStorage.removeItem( (parseFloat(i)).toFixed(2));
        }


        //Loop through comments, and parses the JSON before updating the array

        comments = JSON.parse(commList);
        comments.forEach(function(comment){
            console.log(comment['uName'].toString() + " SAID " + comment['comm'].toString() + " AT " + comment['disTime'].toString());

            cTime = comment['disTime'].toString();
            cBody = comment['comm'];
            cName = comment['uName'];

            //The #@# means we can easily 'split' this string later into name , comment & time
            localStorage[cTime] = cName + "#@#" + cBody + "#@#" + cTime;
        })

    }


    //Gets us the videos duration
    function getVidDur(source , vid){
        if (source) {
            return vid.getDuration();
        } else {
            return vid.duration;
        }
    }

    //Gets the videos current time
    function getVidTime(source , vid){
        if (source) {
            return vid.getCurrentTime();
        } else{
            return vid.currentTime;
        }
    }

    //Gets the source of the video. URL if YT and the source file name if not.
    function getVidSrc(source , vid){
        if (source) {
            //Part of the URL will be a timestamp for the users current position in the video
            vidURL = vid.getVideoUrl();

            //This gets us the unique 11 character video ID
            vidCode = vidURL.slice(-11);

            return vidCode;
        } else {
            //The src gives us the entire filepath so we split it and take the text after the last
            // '/' character since that will be the file name
            let wholePath = (vid.src).split('/');
            let fileName = wholePath.pop();
            return fileName;
        }
    }

</script>
<!-- *************************************************** -->

</body>
</html>
