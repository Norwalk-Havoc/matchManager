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
    <link rel="stylesheet" href="bootstrapDark.min.css" >
	<link rel="stylesheet" href="systemState.css" >
	
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
		  font-weight: 400;
		}
		@font-face {
		  font-family: Conduit;
		  src: url('assets/ITC - ConduitITCPro-MediumItalic.otf');
		  font-weight: 400;
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
		
		body{
			background: #000000;
		}
		.badge-pill::before {
			content: "";
		}
		
		.badge-pill {
			font-size: 36px;
		}
		
		.lower-message-text {

			font-size: 36px;
		}
		
		.form-control {

			font-size: 42px;
		}
	
	</style>
    <title>Event Status</title>
	
	<script type="text/javascript">
		
	var matchUpdate = setInterval(updateAvailable, 5000);
	var availableMatchHash = "";
	var cageDataHash = "";
	
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

	function updateCages(){
		$.get('matchFunctions.php?mode=updateCages&json=1', function( data ) { 
			var cageData = jQuery.parseJSON(data);
			if (cageData.hash == cageDataHash){
				// Do nothing :) 
			} else {
				cageDataHash = cageData.hash;
				$( "#cageRow" ).html( cageData.html );
			}
		});
	}

	
	
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
		updateCages();

	}
	

	

	
	</script>
	
	
  </head>
  <body>
	<div class="container-flex px-4">
		<input  type="hidden" name="mode" value="updateCages" >
		<div class="row" id="cageRow">
			<?php  
				populateCageFields(1);
				populateCageFields(2);
				populateCageFields(3);
			?>
		</div>
	</div>
	<h1 class="next10Footer"></h1>
	<div class="container-flex px-4">
		<div class="row " id="availableMatches">
			<div class="d-flex justify-content-center">
			  <div class="spinner-border" role="status">
			    <span class="sr-only">Loading...</span>
			  </div>
			</div>
		
		</div>
	</div>
	<BR>
		<BR>
			<BR>


	  
	  
	  

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