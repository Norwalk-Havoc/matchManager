<?php
error_reporting(E_ALL ^ E_NOTICE);
require_once('matchFunctions.php');

$challongeURLS = getChallongeTournaments();
// if (isset($_GET['bot_id']) && isset($_GET['bracket'])){
// 	$myBot = $_GET['bot_id'];
// 	$bracket = $_GET['bracket'];
// } else {
// 	echo "Missing Bot Info. - Check your link";
// 	exit(1);
// }


?>


<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="bootstrapDark.min.css" >
	 <link rel="stylesheet" href="upNextMobile.css" >
	<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <title>Bot View</title>
    <meta name="viewport" content="width=580px, initial-scale=1">
	
	<script type="text/javascript">
	$(document).ready(function(){
	  updateAvailable();
	});


	var matchUpdate = setInterval(updateAvailable, 5000);
	var myBotMd5 = ""

	function updateAvailable(){
		$.get('matchFunctions.php?mode=allBots', function( data ) {
			 var myBot = jQuery.parseJSON(data);

	 			if (myBot.md5 == myBotMd5){
	 				// Do nothing :)
	 			} else {
	 				myBotMd5 = myBot.md5;
	 				$( "#botList" ).html( myBot.html );
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
	<div class="" id="botList">

		<div class="aBotBlock">
			<div class="aBot">
				<div class="aBotWeight">30<span class="weightCaption">lb</span></div>
				<div class="aBotName">Herp Derper</div>
				<div class="aBotReady">12<span class="readyCaption">min</span></div>
			</div>
			<div class="aBotNext">
				<div class="aBotNextMatch">22</div>
				<div class="aBotNextCompetitor">Derpy Von Derp</div>
				<div class="aBotNextCompetitorTime">18</div>
			</div>
		</div>
		
		<div class="aBotBlock">
			<div class="aBot">
				<div class="aBotWeight">30<span class="weightCaption">lb</span></div>
				<div class="aBotName">Herp Derper</div>
				<div class="aBotReady">12<span class="readyCaption">min</span></div>
			</div>
			<div class="aBotNext">
				<div class="aBotNextMatch">22</div>
				<div class="aBotNextCompetitor">Derpy Von Derp</div>
				<div class="aBotNextCompetitorTime">18</div>
			</div>
		</div>
		

	
	</div>

  </body>
</html>