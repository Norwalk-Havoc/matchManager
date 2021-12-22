<?php
require_once('matchFunctions.php');


?>


<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
	<link rel="stylesheet" href="judgeDisplayMask.css">
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
    <title>Judge Results</title>
	
	<script type="text/javascript">
	
		
		//var matchInterval = setInterval(updateMatchData, 1000);
		
		$(document).ready(function(){

			// updateMatchData();
			// $('video').prop('muted',true).play()
		});
		
		function isset (accessor) {
		  try {
		    // Note we're seeing if the returned value of our function is not
		    // undefined or null
		    return accessor() !== undefined && accessor() !== null
		  } catch (e) {
		    // And we're able to catch the Error it would normally throw for
		    // referencing a property of undefined
		    return false
		  }
		}
		
		function resetJudges(){
			$("#j1b1a").html("-");
			$("#j1b1d").html("-");
			$("#j1b1c").html("-");
			$("#j1b2a").html("-");
			$("#j1b2d").html("-");
			$("#j1b2c").html("-");
			
			$("#j2b1a").html("-");
			$("#j2b1d").html("-");
			$("#j2b1c").html("-");
			$("#j2b2a").html("-");
			$("#j2b2d").html("-");
			$("#j2b2c").html("-");
			
			$("#j3b1a").html("-");
			$("#j3b1d").html("-");
			$("#j3b1c").html("-");
			$("#j3b2a").html("-");
			$("#j3b2d").html("-");
			$("#j3b2c").html("-");
			
		 	 $("#player1").html("-");
		 	 $("#player2").html("-");
			
			
		}
		
		
		function updateMatchData() {
  		  $.get('matchFunctions.php?mode=getJudgeDisplayData', function( data ) { 
  			  var match = jQuery.parseJSON(data);
			  if (!isset(() => match.player1)){
				  resetJudges();
				  return;
			  }
			  
			  $("#player1").html(match.player1);
			  $("#player2").html(match.player2);
			  
			  
			  //Judge 1 Player 1
			  if (isset(() => match.judge_scores.player1["1"].dammage)) {
				  $("#j1b1d").html(match.judge_scores.player1["1"].damage)  ;	  
			  } else {
				  $("#j1b1d").html("-");
			  }
			  if (isset(() => match.judge_scores.player1["1"].aggression)) {
				  $("#j1b1a").html(match.judge_scores.player1["1"].aggression)  ;	  
			  } else {
				  $("#j1b1a").html("-");
			  }
			  if (isset(() => match.judge_scores.player1["1"].control)) {
				  $("#j1b1c").html(match.judge_scores.player1["1"].control)  ;	  
			  } else {
				  $("#j1b1c").html("-");
			  }
			  
			  //Judge 1 Player 2
			  if (isset(() => match.judge_scores.player2["1"].damage)) {
				  $("#j1b2d").html(match.judge_scores.player2["1"].damage)  ;	  
			  } else {
				  $("#j1b2d").html("-");
			  }
			  if (isset(() => match.judge_scores.player2["1"].aggression)) {
				  $("#j1b2a").html(match.judge_scores.player2["1"].aggression)  ;	  
			  } else {
				  $("#j1b2a").html("-");
			  }
			  if (isset(() => match.judge_scores.player2["1"].control)) {
				  $("#j1b2c").html(match.judge_scores.player2["1"].control)  ;	  
			  } else {
				  $("#j1b2c").html("-");
			  }
			  
			  //Judge 2 Player 1
			  if (isset(() => match.judge_scores.player1["2"].damage)) {
				  $("#j2b1d").html(match.judge_scores.player1["2"].damage)  ;	  
			  } else {
				  $("#j2b1d").html("-");
			  }
			  if (isset(() => match.judge_scores.player1["2"].aggression)) {
				  $("#j2b1a").html(match.judge_scores.player1["2"].aggression)  ;	  
			  } else {
				  $("#j2b1a").html("-");
			  }
			  if (isset(() => match.judge_scores.player1["2"].control)) {
				  $("#j2b1c").html(match.judge_scores.player1["2"].control)  ;	  
			  } else {
				  $("#j2b1c").html("-");
			  }
			  
			  //Judge 2 Player 2
			  if (isset(() => match.judge_scores.player2["2"].damage)) {
				  $("#j2b2d").html(match.judge_scores.player2["2"].damage)  ;	  
			  } else {
				  $("#j2b2d").html("-");
			  }
			  if (isset(() => match.judge_scores.player2["2"].aggression)) {
				  $("#j2b2a").html(match.judge_scores.player2["2"].aggression)  ;	  
			  } else {
				  $("#j2b2a").html("-");
			  }
			  if (isset(() => match.judge_scores.player2["2"].control)) {
				  $("#j2b2c").html(match.judge_scores.player2["2"].control)  ;	  
			  } else {
				  $("#j2b2c").html("-");
			  }
			  
			  //Judge 3 Player 1
			  if (isset(() => match.judge_scores.player1["3"].damage)) {
				  $("#j3b1d").html(match.judge_scores.player1["3"].damage)  ;	  
			  } else {
				  $("#j3b1d").html("A");
			  }
			  if (isset(() => match.judge_scores.player1["3"].aggression)) {
				  $("#j3b1a").html(match.judge_scores.player1["3"].aggression)  ;	  
			  } else {
				  $("#j3b1a").html("-");
			  }
			  if (isset(() => match.judge_scores.player1["3"].control)) {
				  $("#j3b1c").html(match.judge_scores.player1["3"].control)  ;	  
			  } else {
				  $("#j3b1c").html("-");
			  }
			  
			  //Judge 3 Player 2
			  if (isset(() => match.judge_scores.player2["3"].damage)) {
				  $("#j3b2d").html(match.judge_scores.player2["3"].damage)  ;	  
			  } else {
				  $("#j3b2d").html("-");
			  }
			  if (isset(() => match.judge_scores.player2["3"].aggression)) {
				  $("#j3b2a").html(match.judge_scores.player2["3"].aggression)  ;	  
			  } else {
				  $("#j3b2a").html("-");
			  }
			  if (isset(() => match.judge_scores.player2["3"].control)) {
				  $("#j3b2c").html(match.judge_scores.player2["3"].control)  ;	  
			  } else {
				  $("#j3b2c").html("-");
			  }
			  
			  
		  });
		}
		

		
		
	
	

	
	</script>
	
	
  </head>
  <body>
	<div class="container-flex px-4 py-4">

			<div class="row">
				<div class="announcersBox " >
					<div class="announcersVideo videoBox"></div>
					<div class="names">
						<div class="playerName" id="player1">Corwallace</div>
						<div class="playerName" id="player2">Multiple Bots</div>
					</div>
				</div>
				<div class="judgeBox " >
					<div class="judgeName" id="judge1name"><?php echo $judgeNames[1]; ?></div>
					<div class="judgeVideo videoBox"></div>
					<div class="scores">
						<div class="labelRow judgeRow">
							<div class="score">Damage</div>
							<div class="score">Aggression</div>
							<div class="score">Control</div>
						</div>
						<div class="scoreRow judgeRow">
							<div class="score"><span class="damageScore" id="j1b1d">-</span></div>
							<div class="score"><span class="aggressionScore" id="j1b1a">-</span></div>
							<div class="score"><span class="controlScore" id="j1b1c">-</span></div>
						</div>
						<div class="scoreRow judgeRow">
							<div class="score"><span class="damageScore" id="j1b2d">-</span></div>
							<div class="score"><span class="aggressionScore" id="j1b2a">-</span></div>
							<div class="score"><span class="controlScore" id="j1b2c">-</span></div>
						</div>
					</div>
				</div>
				<div class="judgeBox " >
					<div class="judgeName" id="judge2name"><?php echo $judgeNames[2]; ?></div>
					<div class="judgeVideo videoBox"></div>
					<div class="scores">
						<div class="labelRow judgeRow">
							<div class="score">Damage</div>
							<div class="score">Aggression</div>
							<div class="score">Control</div>
						</div>
						<div class="scoreRow judgeRow">
							<div class="score"><span class="damageScore" id="j2b1d">-</span></div>
							<div class="score"><span class="aggressionScore" id="j2b1a">-</span></div>
							<div class="score"><span class="controlScore" id="j2b1c">-</span></div>
						</div>
						<div class="scoreRow judgeRow">
							<div class="score"><span class="damageScore" id="j2b2d">-</span></div>
							<div class="score"><span class="aggressionScore" id="j2b2a">-</span></div>
							<div class="score"><span class="controlScore" id="j2b2c">-</span></div>
						</div>
					</div>
				</div>
				<div class="judgeBox" >
					<div class="judgeName " id="judge3name"><?php echo $judgeNames[3]; ?></div>
					<div class="judgeVideo videoBox"></div>
					<div class="scores">
						<div class="labelRow judgeRow">
							<div class="score">Damage</div>
							<div class="score">Aggression</div>
							<div class="score">Control</div>
						</div>
						<div class="scoreRow judgeRow">
							<div class="score"><span class="damageScore" id="j3b1d">-</span></div>
							<div class="score"><span class="aggressionScore" id="j3b1a">-</span></div>
							<div class="score"><span class="controlScore" id="j3b1c">-</span></div>
						</div>
						<div class="scoreRow judgeRow">
							<div class="score"><span class="damageScore" id="j3b2d">-</span></div>
							<div class="score"><span class="aggressionScore" id="j3b2a">-</span></div>
							<div class="score"><span class="controlScore" id="j3b2c">-</span></div>
						</div>
					</div>
				</div>
			</div>
	</div>
	

	
	  
	  
	  

    <!-- Option 1: jQuery and Bootstrap Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-Piv4xVNRyMGpqkS2by6br4gNJ7DXjqk09RmUpJ8jgGtD7zP9yug3goQfGII0yAns" crossorigin="anonymous"></script>
  </body>
</html>