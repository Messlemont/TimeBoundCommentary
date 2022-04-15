
This was my dissertation project, developed duirng my fourth year at the University of Strathclyde. It is a system to allow users to create commentary for video by means of leaving a comment associated with a particular point in the video's runtime. Users are able to reply to and delete comments as well, there is a moderation system to allow for the curation of this commentary.

This system is compatible with embeded MP4 files and embeded YouTube videos.
I have included mitigation against XSS and SQLI attacks.


REQUIREMENTS:
	-Download the DOMPurify JavaScript framework
		(It can be found at: https://github.com/cure53/DOMPurif)
	-Either place the framework in the same folder as the rest of the project 
	  or 	change the src value of the DomPure script found in 'mainPage' to the file path of your copy of DOMPurify

	-Now simply load 'mainPage' page in your browser.

MAKING A COMMENT:
	-Select a time using the range type HTML tag
	-Write your comment in the text field provided
	-Click on the ‘Comment’ button
	-You will receive either a confirmation that your comment has been saved, or told that you’re comment was unable to be saved
	-To make sure you can see your new comment, try reloading the page

DELETE A COMMENT:
	-On the comment you wish to delete, click on the ‘D’ button
	-Confirm that you wish to delete your comment
	-Unless you are a moderator or lecturer, you will only be able to delete your own comments

REPLY TO A COMMENT:
	-Comment chains can have a maximum of 10 comments (including the original comment)
	-Click the ‘R’ button on the comment you would like to reply to
	-Write your comment 
	-Click on the ‘Comment’ button
	-Do not interact with the time slider after clicking on the ‘R’ button, if you do you will have to click on the ‘R’ button again or else your comment will not be treated as a reply

TO CHANGE VIDEO:
	-Replace the mainPage templates iFrame tag with either:
		*A video tag that contains the file path of an MP4 file as its source - remember to include 'controls'
			(The video source should have a unique file name relative to what it 
			 is EG: Naming the 3rd lecture video of week 2 for CS101: 'CS101W3L2')
		
		*An iFrame tag containing an embedded YouTube video source
			(To easily acquire this, go to the video you wish to embed.
			 Right-click on the video and select 'Copy Embed Code'.
			 This will copy the entire iFrame code to the clipboard.)
			 
		NOTE: -Make sure the 'id' for your video/iFrame tag is 'lectVideo'
		      -I have provided in mainPage an example of both a video and iFrame tag
		      

