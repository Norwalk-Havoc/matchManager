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
	<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <title>Match Manager</title>
	
	<script type="text/javascript">
		
	var cages = setInterval(updateCages, 1000);
	var cageDataHash = "";
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




	 
	});

	
	function startMatchUpdates(){
	  $('#cageUpdates').html("Pause Cage Updates");
	  $('#cageUpdates').addClass("btn-secondary").removeClass("btn-primary");
	  cages = setInterval(updateCages, 2000);
		
	}
	function stopMatchUpdates(){
	  $('#cageUpdates').html("Resume Cage Updates");
	  $('#cageUpdates').removeClass("btn-secondary").addClass("btn-primary");
	  clearInterval(cages);
      cages = null;
	}

	function updateCage(cage){
		var data = $('#cage'+cage+'form').serialize();
		  $.post('matchFunctions.php', data, function( data ) { 
			  updateCages();
			  
		  } ); 
		
	}
	function cageCommand( cage,  command, button){
		$(button).html('<div class="spinner-border spinner-border-sm" role="status"> <span class="sr-only">Loading...</span></div>');
		if (command == "player1Wins"){
			$.get('matchFunctions.php?mode=winner&cage='+cage+'&player=1', function( data ) { 
				 updateCages();	
				  updateAvailable();			 
			});
		}
		else if (command == "player2Wins"){
			$.get('matchFunctions.php?mode=winner&cage='+cage+'&player=2', function( data ) { 
				 updateCages();
				 updateAvailable();
			});
		}
		else if (command == "startMatch"){
			$.get('matchFunctions.php?mode=startMatch&cage='+cage, function( data ) { 
				 updateCages();
				 updateAvailable();
			});
		}
		else if (command == "stopMatch"){
			$.get('matchFunctions.php?mode=stopMatch&cage='+cage, function( data ) { 
				 updateCages();
				 updateAvailable();
			});
		}
		else if (command == "clearMatch"){
			$.get('matchFunctions.php?mode=clearMatch&cage='+cage, function( data ) { 
				 updateCages();
				 updateAvailable();
			});
		}
		else if (command == "encore"){
			$.get('matchFunctions.php?mode=encore&cage='+cage, function( data ) { 
				 updateCages();
				 updateAvailable();
			});
		}
		else if (command == "pause"){
			$.get('matchFunctions.php?mode=pause&cage='+cage, function( data ) { 
				 updateCages();
				 updateAvailable();
			});
		}
	}
	

	
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