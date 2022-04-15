REQUIREMENTS:
	-Download the DOMPurify JavaScript framework
		(It can be found at: https://github.com/cure53/DOMPurif)
	-Either place the framework in the same folder as the rest of the project 
	  or 	change the src value of the DomPure script found in 'mainPage' to the file path of your copy of DOMPurify

	-Now simply load 'mainPage' page in your browser.


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

