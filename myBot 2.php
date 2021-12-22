<?php
error_reporting(E_ALL ^ E_NOTICE);
require_once('matchFunctions.php');

$challongeURLS = getChallongeTournaments();
if (isset($_GET['bot_id']) && isset($_GET['bracket'])){
	$myBot = $_GET['bot_id'];
	$bracket = $_GET['bracket'];
} else {
	echo "Missing Bot Info. - Check your link";
	exit(1);
}


?>


<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=320, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="bootstrapDark.min.css" >
	 <link rel="stylesheet" href="upNext.css" >
	<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <title>Bot View</title>
	
	<script type="text/javascript">
	$(document).ready(function(){
	  updateAvailable();
	});


	var matchUpdate = setInterval(updateAvailable, 5000);
	var myBotMd5 = ""

	function updateAvailable(){
		$.get('matchFunctions.php?mode=botDetails&bot=<?=$myBot?>&tournament=<?=$bracket?>', function( data ) { 
			 var myBot = jQuery.parseJSON(data);
 			
 			if (myBot.md5 == myBotMd5){
 				// Do nothing :) 
 			} else {
 				myBotMd5 = myBot.md5;
 				$( "#botDetails" ).html( myBot.html );
 			}	
		});

	}
	
	</script>

	
  </head>
  <body class=" ">

	<!-- <nav class="navbar navbar-expand navbar-light bg-light">
	  <a class="navbar-brand" href="#">Bot Manager</a>
	  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
	    <span class="navbar-toggler-icon"></span>
	  </button>
	  <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
	    <div class="navbar-nav">
	      <a class="nav-link active" href="#">Status <span class="sr-only">(current)</span></a>
	      <a class="nav-link" href="#">Picture</a>
	    </div>
	  </div>
	</nav> -->
	<div class="container-sm " id="botDetails">

		
		
	</div>

  </body>
</html>