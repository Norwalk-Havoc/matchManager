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
	<style>
		.form-control {
			color: #ffffff;
			background-color: #000000;
		}
		.blue-bg {
			background-color: #018fb0;
		}
		.red-bg {
			background-color: #dc00c8;
		}
		.greenroom {
			font-weight: bold;
			text-shadow: 2px 2px 12px #00FF00;
		}
		.greenroomBoth {
			background-color: #0a571a !important;
		}
	</style>
	<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <title>Match Manager</title>
	
	<script type="text/javascript">
		
	var cages = setInterval(updateCages, 1000);
	var cageDataHash = "";
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


  	  $(document).on("click","input[name='matchSelect']", function(){
	
	
		  var chosenMatch = this.value;
		  chosenMatch = chosenMatch.replace('-',' ');
		  chosenMatch = chosenMatch.replace('~',' ');
  		  $("#theMatchInfo").html('"'+chosenMatch+'"');
  	  });

	  $(document).on("click","#updateCage", function(){
		  $("#updateCage").html('  <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
		  
  		  var data = $('#matchSelectForm').serialize();
		  
		  $.post('matchFunctions.php', data, function( data ) { 
			  $("#updateCage").html("Submit");
			  $(':input','#matchSelectForm')
			  .prop('selected', false);
			  
			  updateCages();
			  startMatchUpdates();
		  } ); 
		  console.log('set');
	  })
	  
	  $(document).on("click","input[group='cage']", function(){
		  stopMatchUpdates();
	  });
	  
	  $(document).on("click","#cageUpdates", function(){
		  if(cages){
			  stopMatchUpdates();

		  } else {
			  startMatchUpdates();
		  }
	  });
	  updateAvailable();
	 
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
	
	
	function updateAvailable(){
		$.get('matchFunctions.php?mode=updateMatches&json=1', function( data ) { 
			var availableMatch = jQuery.parseJSON(data);
			if (availableMatch.hash == availableMatchHash){
				// Do nothing :) 
			} else {
				availableMatchHash = availableMatch.hash;
				$( "#availableMatches" ).html( availableMatch.html );
			}
			
		});

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
		<h1><h4 class="float-right"><span class="btn btn-secondary" onClick="window.location.reload();">Reload Webpage</span></h4><p class="h1">Cages</p></h1>
		<div class="row" id="cageRow">
			<?php  
				populateCageFields(1);
				populateCageFields(2);
				populateCageFields(3);
				populateCageFields(4);
			?>
		</div>
		
		
	</div>


	<div class="container-flex px-4">
		<form id="matchSelectForm">
			<input  type="hidden" name="mode" value="assignMatch" >
		<h1>
				<h3 class="float-right">Assign Match <span id="theMatchInfo"></span> to Cage: 
					<div class="btn-group btn-group-toggle" data-toggle="buttons">					 
					  <label class="btn btn-outline-secondary" for="inlineRadio1"> <input class="btn-check" type="radio" name="matchSpot" id="inlineRadio1" value="1">- 1 -</label>
					  <label class="btn btn-outline-secondary" for="inlineRadio2"><input class="btn-check" type="radio" name="matchSpot" id="inlineRadio2" value="2">- 2 -</label>
					  <label class="btn btn-outline-secondary" for="inlineRadio3"> <input class="btn-check" type="radio" name="matchSpot" id="inlineRadio3" value="3" >- 3 <span class="text-info h6">(30lb) -</span></label>
					  <label class="btn btn-outline-secondary" for="inlineRadio4"> <input class="btn-check" type="radio" name="matchSpot" id="inlineRadio4" value="4" >- 4 -</span></label>

					</div>
					<button type="button" class="btn btn-primary" id="updateCage">Submit</button>
				</h3>
			<p class="h1">Possible Matches</p>					
		</h1>
			<div class="row border" id="availableMatches">
			
				<div class="d-flex justify-content-center">
				  <div class="spinner-border" role="status">
				    <span class="sr-only">Loading...</span>
				  </div>
				</div>
			
			</div>
			<hr>
			<div class="row">

			</div>
		</form>
	</div>
	
	
	<div class="container-flex px-4">
			<h1>Challonge Tournaments</h1>
			<hr />
			<div class="container">
			<div class="row">
				<div class="col-md-6">
					<form id="challongeTournaments">
						<input type="hidden" name="mode" value="challongeURL" class="form-control" id="basic-url" aria-describedby="basic-addon3">
					<label for="basic-url">3lb Bracket URL</label>
					<div class="input-group mb-3">
					  <div class="input-group-prepend">
					    <span class="input-group-text" id="challonge3">https://challonge.com/</span>
					  </div>
					  <input type="text" name="3lb-Bracket" class="form-control" value="<?=$challongeURLS['3lb-Bracket']?>" id="basic-url" aria-describedby="basic-addon3">
					</div>
					<label for="basic-url">12lb Bracket URL</label>
					<div class="input-group mb-3">
					  <div class="input-group-prepend">
					    <span class="input-group-text" id="challonge12">https://challonge.com/</span>
					  </div>
					  <input type="text"  name="12lb-Bracket" value="<?=$challongeURLS['12lb-Bracket']?>"  class="form-control" id="basic-url" aria-describedby="basic-addon3">
					</div>
					<label for="basic-url">12lb Sportsman Bracket URL</label>
					<div class="input-group mb-3">
					  <div class="input-group-prepend">
					    <span class="input-group-text" id="challonge12s">https://challonge.com/</span>
					  </div>
					  <input type="text"  name="12lb-Sportsman-Bracket" value="<?=$challongeURLS['12lb-Sportsman-Bracket']?>"  class="form-control" id="basic-url" aria-describedby="basic-addon3">
					</div>
					<label for="basic-url">30lb Bracket URL</label>
					<div class="input-group mb-3">
					  <div class="input-group-prepend">
					    <span class="input-group-text" id="challonge30">https://challonge.com/</span>
					  </div>
					  <input type="text"   name="30lb-Bracket" value="<?=$challongeURLS['30lb-Bracket']?>"  class="form-control" id="basic-url" aria-describedby="basic-addon3">
					</div>
					</form>
				</div>
				<div class="col-md-6">
					<div class="row text-center">
						<div class="btn-group mx-auto " role="group" aria-label="Basic example">
						  <button type="button" id="updateChallongeURLS" class="btn btn-primary ">Update Bracket URLS</button>
						  <button type="button" id="refreshChallonge" class="btn btn-primary">Refresh Challonge</button>
						</div>
					</div>
					<div class="card m-3 p-3">
						<h5>Response:</h5>
						<div class="well" id="challongeResponse"></div>
					</div>
				</div>	
			</div>
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