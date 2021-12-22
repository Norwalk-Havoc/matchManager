<?php
require_once('matchFunctions.php');
if (isset($_GET['judgeID'])){
	$judgeID = $_GET['judgeID'];
	$judgeName = $judgeNames[$judgeID];
} else {
	$judgeID = 0;
}

?>


<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="bootstrap.min.css" >
	<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
	<style>
		.greenroom {
			font-weight: bold;
			text-shadow: 2px 2px 12px #00FF00;
		}
		.greenroomBoth {
			background-color: #70cc7c !important;
		}
		
	</style>
    <title>Judge Manager</title>
	
	<script type="text/javascript">
		var LAgression = 2.5;
		var RAgression = 2.5;
		var LControl = 2.5;
		var RControl = 2.5;
		var LDammage = 2.5;
		var RDammage = 2.5;
		var LTotal = 2.5;
		var RTotal = 2.5;
		var matchID = -1;
		
		var matchInterval = setInterval(updateMatchData, 3000);
		
		$(document).ready(function(){
			$("#agression").change(function () {
				RAgression = parseInt($("#agression").val());
				updateScores();
			});
			$("#control").change(function () {
				RControl = parseInt($("#control").val());
				updateScores();
			});
			$("#dammage").change(function () {
				RDammage = parseInt($("#dammage").val());
				updateScores();
			});
			updateMatchData();
		});
		
		function resetMatch(){
			$("#agression").val(2.5);
			$("#control").val(2.5);
			$("#dammage").val(2.5);
			LAgression = 2.5;
			RAgression = 2.5;
			LControl = 2.5;
			RControl = 2.5;
			LDammage = 2.5;
			RDammage = 2.5;
			LTotal = 2.5;
			RTotal = 2.5;
			updateScores();
		}
		
		
		function updateMatchData() {
  		  $.get('matchFunctions.php?mode=judgeMatchData', function( data ) { 
  			  var match = jQuery.parseJSON(data);
			  $("#leftBotName").html(match.player1);
			  $("#rightBotName").html(match.player2);
			  if (matchID != match.id){
				  resetMatch();
			  }
			  matchID = match.id;
			  if (match.round > 0){
			  	//Winners Bracket
				  $("#matchTitle").html("Winners Bracket "+match.tournament+" Round "+match.round);
			  } else if (match.round < 0){
			  	//Winners Bracket
				  $("#matchTitle").html("Losers Bracket "+match.tournament+" Round "+(match.round * -1));
			  }
			  else {
				  $("#matchTitle").html("No match underway");
				  resetMatch();
			  }
		  });
		}
		
		function submitScores() {
  		  $.get('matchFunctions.php?mode=judgeScoreUpload&judgeID=<?php echo $judgeID ?>&LAgression='+LAgression+'&LControl='+LControl+'&LDammage='+LDammage, function( data ) { 
			  $("#winnerName").html('Submitted!');
		  });
		}
		
		
		function updateScores(){
			LAgression = 5 - RAgression;
			LControl = 5 - RControl;
			LDammage = 5 - RDammage;
			LTotal = LAgression + LControl + LDammage;
			RTotal = RAgression + RControl + RDammage;
			

			$("#leftBotAgression").html(LAgression);
			$("#rightBotAgression").html(RAgression);
			$("#leftBotDammage").html(LDammage);
			$("#rightBotDammage").html(RDammage);
			$("#leftBotControl").html(LControl);
			$("#rightBotControl").html(RControl);
			$("#leftTotal").html(LTotal);
			$("#rightTotal").html(RTotal);
			if (LTotal > RTotal){
				$("#leftBotName").css("font-weight","Bold");
				$("#rightBotName").css("font-weight","Normal");
				$("#winnerName").html($("#leftBotName").html());
				
			} else if (RTotal > LTotal){
				$("#rightBotName").css("font-weight","Bold");
				$("#leftBotName").css("font-weight","Normal");
				$("#winnerName").html($("#rightBotName").html());
			} else {
				$("#rightBotName").css("font-weight","Normal");
				$("#leftBotName").css("font-weight","Normal");
				$("#winnerName").html('-');
			}

		}
	

	
	</script>
	
	
  </head>
  <body>
	<div class="container-flex px-4 py-4">
		<?php if ($judgeID == 0 || $judgeName == "") {  ?>
			<h5 class="text-center alert alert-danger">CHECK URL - UNKONWN JUDGE ID</h3>	
		<?php } else { ?>
		<h5 class="text-center">Judge: <span id="JudgeName" class="font-weight-bold"><?php echo $judgeName; ?></span> Control Pannel</h3>
		<?php }  ?>	
		<h4 class="text-center" id="matchTitle">Loading Bracket Loading Round</h4>
		<br>
		  <h4 class="alert-heading"><span id="leftBotName">Loading...</span> <span id="leftTotal" class="font-weight-bold">4</span> <span  class="float-right"><span id="rightBotName">Loading...</span> <span class="font-weight-bold" id="rightTotal" >1</span></span></h4>
		  <hr>
		  
		  <form>
		    <div class="form-group">
			  <label for="agression" class="text-center">Aggression</label>
			  <div class="numbers"><span id="leftBotAgression" class="font-weight-bold">-</span> <span class="float-right"><span id="rightBotAgression" class="font-weight-bold">-</span></span></div>
			  <input type="range" class="custom-range" min="0" max="5" value="2.5" id="agression">
		    </div>
		    <div class="form-group">
			  <label for="control" class="text-center">Control</label>
			  <div class="numbers"><span id="leftBotControl" class="font-weight-bold">-</span> <span class="float-right"><span id="rightBotControl" class="font-weight-bold">-</span></span></div>
			  
			  <input type="range" class="custom-range" min="0" max="5" value="2.5" id="control">
		    </div>
		    <div class="form-group">
			  <label for="dammage" class="text-center">Damage</label>
			  <div class="numbers"><span id="leftBotDammage" class="font-weight-bold">-</span> <span class="float-right"><span id="rightBotDammage" class="font-weight-bold">-</span></span></div>
			  
			  <input type="range" class="custom-range" min="0" max="5" value="2.5" id="dammage">
		    </div>
		    <br>
		   <div class="mx-auto text-center"> <div class="btn btn-primary" onClick="submitScores()">Submit Score: <span id="winnerName">-</span> Wins</div></div>
		  </form>
		  

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