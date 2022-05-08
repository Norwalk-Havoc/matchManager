<?php
require_once('matchFunctions.php');

$challongeURLS = getChallongeTournaments();

?>


<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="bootstrap.min.css" >
	<link rel="stylesheet" href="next5.css" >
	<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
	<style>
		@font-face {
		  font-family: Conduit;
		  src: url('assets/ITC - ConduitITCPro-Black.otf');
		  font-weight: 800;
		}
		@font-face {
		  font-family: Conduit;
		  src: url('assets/ITC - ConduitITCPro-BlackItalic.otf');
		  font-weight: 800;
		  font-style: italic;
		}
		@font-face {
		  font-family: Conduit;
		  src: url('assets/ITC - ConduitITCPro-Bold.otf');
		  font-weight: 700;
		}
		@font-face {
		  font-family: Conduit;
		  src: url('assets/ITC - ConduitITCPro-BoldItalic.otf');
		  font-weight: 700;
		  font-style: italic;
		}
		@font-face {
		  font-family: Conduit;
		  src: url('assets/ITC - ConduitITCPro-ExtraBold.otf');
		  font-weight: 750;
		}
		@font-face {
		  font-family: Conduit;
		  src: url('assets/ITC - ConduitITCPro-ExtraBoldItalic.otf');
		  font-weight: 750;
		  font-style: italic;

		}
		@font-face {
		  font-family: Conduit;
		  src: url('assets/ITC - ConduitITCPro-Medium.otf');
		  font-weight: 600;
		}
		@font-face {
		  font-family: Conduit;
		  src: url('assets/ITC - ConduitITCPro-MediumItalic.otf');
		  font-weight: 600;
		  font-style: italic;
		}
		@font-face {
		  font-family: Conduit;
		  src: url('assets/ITC - ConduitITCPro-Light.otf');
		  font-weight: 300;
		}
		@font-face {
		  font-family: Conduit;
		  src: url('assets/ITC - ConduitITCPro-LightItalic.otf');
		  font-weight: 300;
		  font-style: italic;
		}
		@font-face {
		  font-family: Conduit;
		  src: url('assets/ITC - ConduitITCPro-ExtraLight.otf');
		  font-weight: 100;
		}
		@font-face {
		  font-family: Conduit;
		  src: url('assets/ITC - ConduitITCPro-ExtraLightItalic.otf');
		  font-weight: 100;
		  font-style: italic;
		}



		@font-face {
		  font-family: Billy;
		  src: url('assets/Billy-Bold.otf');
		  font-weight: 400;
		}
	
		.greenroom {
			font-weight: bold;
			text-shadow: 2px 2px 12px #00FF00;
		}
		.greenroomBoth {
			background-color: #70cc7c !important;
		}
	
		.glitch {
		  position: relative;
		  text-shadow: 0.05em 0 0 #00fffc, -0.03em -0.04em 0 #fc00ff,
		    0.025em 0.04em 0 #fffc00;
		  animation: glitch 725ms infinite;
		}

		.glitch span {
		  position: absolute;
		  top: 0;
		  left: 0;
		}

		.glitch span:first-child {
		  animation: glitch 500ms infinite;
		  clip-path: polygon(0 0, 100% 0, 100% 35%, 0 35%);
		  transform: translate(-0.04em, -0.03em);
		  opacity: 0.75;
		}

		.glitch span:last-child {
		  animation: glitch 375ms infinite;
		  clip-path: polygon(0 65%, 100% 65%, 100% 100%, 0 100%);
		  transform: translate(0.04em, 0.03em);
		  opacity: 0.75;
		}

		@keyframes glitch {
		  0% {
		    text-shadow: 0.05em 0 0 #00fffc, -0.03em -0.04em 0 #fc00ff,
		      0.025em 0.04em 0 #fffc00;
		  }
		  15% {
		    text-shadow: 0.05em 0 0 #00fffc, -0.03em -0.04em 0 #fc00ff,
		      0.025em 0.04em 0 #fffc00;
		  }
		  16% {
		    text-shadow: -0.05em -0.025em 0 #00fffc, 0.025em 0.035em 0 #fc00ff,
		      -0.05em -0.05em 0 #fffc00;
		  }
		  49% {
		    text-shadow: -0.05em -0.025em 0 #00fffc, 0.025em 0.035em 0 #fc00ff,
		      -0.05em -0.05em 0 #fffc00;
		  }
		  50% {
		    text-shadow: 0.05em 0.035em 0 #00fffc, 0.03em 0 0 #fc00ff,
		      0 -0.04em 0 #fffc00;
		  }
		  99% {
		    text-shadow: 0.05em 0.035em 0 #00fffc, 0.03em 0 0 #fc00ff,
		      0 -0.04em 0 #fffc00;
		  }
		  100% {
		    text-shadow: -0.05em 0 0 #00fffc, -0.025em -0.04em 0 #fc00ff,
		      -0.04em -0.025em 0 #fffc00;
		  }
		}
		
		#myVideo {
		  position: fixed;
		  right: 0;
		  bottom: 0;
		  min-width: 100%; 
		  min-height: 100%;
		}
		body::-webkit-scrollbar {
		  display: none;
		}
	
	</style>
    <title>Next Matches</title>
	
	<script type="text/javascript">
		
	var matchUpdate = setInterval(updateAvailable, 5000);
	var availableMatchHash = "";
	
	$(document).ready(function(){
	  $("#updateChallongeURLS").click(function(){
		  var data = $('#challongeTournaments').serialize();
		  $.post('matchFunctions.php', data, function( data ) { $( "#challongeResponse" ).html( data )} );
	  });
	  
	  $("input[name='matchSelect']").click(function(){
		  var data = $('#matchSelectForm').serialize();
		  var chosenMatch = data['matchSelect'];
		  $("#theMatchInfo").html(chosenMatch);
		  
	  });



	  updateAvailable();
	 
	});

	

	
	
	function updateAvailable(){
		$.get('matchFunctions.php?mode=next10&json=1', function( data ) { 
			var availableMatch = jQuery.parseJSON(data);
			if (availableMatch.hash == availableMatchHash){
				// Do nothing :) 
			} else {
				availableMatchHash = availableMatch.hash;
				$( "#availableMatches" ).html( availableMatch.html );
			}
			
		});

	}
	

	

	
	</script>
	
	
  </head>
  <body>
	  <video autoplay muted loop id="myVideo" style="opacity:70%">
	    <source src="assets/next20BG.mp4" type="video/mp4">
	  </video>
	  

	 <h1 class="next10Title glitch">Next 20 Matches</h1>
	<div class="container-flex px-4">
		<div class="row " id="availableMatches">
			<div class="d-flex justify-content-center">
			  <div class="spinner-border" role="status">
			    <span class="sr-only">Loading...</span>
			  </div>
			</div>
		
		</div>
	</div>
	 <!-- <h1 class="next10Footer">If your robot is on this list head to the greenroom!<br>You are responsible for being ready<br> Robots in green are allready in the green room</h1> -->
	

	  
	  
	  

    <!-- Optional JavaScript; choose one of the two! -->

    <!-- Option 1: jQuery and Bootstrap Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-Piv4xVNRyMGpqkS2by6br4gNJ7DXjqk09RmUpJ8jgGtD7zP9yug3goQfGII0yAns" crossorigin="anonymous"></script>

    <!-- Option 2: Separate Popper and Bootstrap JS -->
    <!--
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js" integrity="sha384-+YQ4JLhjyBLPDQt//I+STsc9iw4uQqACwlvpslubQzn4u2UU2UFM80nGisd026JF" crossorigin="anonymous"></script>
    -->
  </body>
</html>