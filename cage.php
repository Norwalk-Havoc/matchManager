<?php
require_once('matchFunctions.php');
if (isset($_GET['cage'])){
	$cage = $_GET['cage'];
} else {
	$cage = 1;
}

if (isset($_GET['mask'])){
	$mask = $_GET['mask'];
} else {
	$maks = 0;
}

if (isset($_GET['inCage'])){
	$inCage = $_GET['inCage'];
} else {
	$inCage = false;
}


$cageVals = getCageText($cage);
extract($cageVals);


?>

<html>
<head>
	<?php if ($inCage == '360L') { ?>
	<link rel="stylesheet" href="Cage360L.css">
	<?php } else if ($inCage == '360R') { ?>
	<link rel="stylesheet" href="Cage360R.css">
	<?php } else if ($mask == 0) { ?>
	<link rel="stylesheet" href="onScreen.css">
	<?php  } else { ?>
	<link rel="stylesheet" href="onScreenMask.css">
	
	
	<?php } ?>
	
	<script src="https://code.jquery.com/jquery-3.5.1.min.js">
		
	</script>
	
	
	<script type="text/javascript">
		
	var cages = setInterval(updateCage, 500);
	var timeTimer = setInterval(updateTime, 100);
	
	var matchEnd = 0;
	var countdownEnd = 0;
	var matchActive = false;
	var matchTime = 180;
	var matchPaused = FALSE;
	var player1ready = FALSE;
	var player2ready = FALSE;
	
	function updateTime(){
		if (matchActive){
			var clockText = "";
			
			var now = Date.now();
			var msToEnd = (matchEnd * 1000) - now;
			var msToCountdownEnd = (countdownEnd * 1000) - now;
			
			if (msToEnd > matchTime * 1000) {
				var seconds = Math.floor(Math.floor((msToEnd - matchTime * 1000) % 60000)/1000);
				if (seconds == 0){
					seconds = "Fight!";
					$(".countDown").hide();
				} else if (seconds < 10){
					seconds = seconds;
					$(".countDown").show();
				} 
				clockText = seconds;
				
			} else if (msToEnd > 10000){
				var minutes = Math.floor(msToEnd / 60000);
				var seconds = Math.floor(Math.floor(msToEnd % 60000)/1000);
				if (seconds == 0){
					seconds = "00";
				} else if (seconds < 10){
					seconds = "0"+seconds;
				} 
				clockText = minutes+":"+seconds;
				$(".countDown").hide();
			} else {
				var seconds = Math.floor(msToEnd/1000);
				var tenths = Math.floor((msToEnd%1000)/100);
				clockText = seconds+"."+tenths;
				$(".countDown").hide();
			}
			
			
			
		} else {
			clockText = "";
			$(".countDown").hide();
		}
		if (msToEnd < 0 && msToEnd > -10000){
			clockText = "END";
		}
		if (matchPaused){
			clockText = "PAUSE"
		}
		$('.countDownTime').html(clockText);
		$('.matchClock').html(clockText);
	}
	
	
	function updateCage(cage){
		  $.get('matchFunctions.php?mode=cageJson&cage=<?php echo $cage; ?>', function( data ) { 
			  var cage = jQuery.parseJSON(data);
			  $('#player1').html(cage.player1);
			  $('#player1countdown').html(cage.player1);
			  $(".leftBotImage").css("background-image", "url('getBotPic.php?thumb=1&bot=" + cage.player1.replaceAll(' ', '').toLowerCase() + "')");
			  $('#player2').html(cage.player2);
			  $('#player2countdown').html(cage.player2);
			  $(".rightBotImage").css("background-image", "url('getBotPic.php?thumb=1&bot=" + cage.player2.replaceAll(' ', '').toLowerCase() + "')");
			  
			  $('.matchNumber').html(cage.order);
			  var bracket = "";
			  if (cage.round > 0){
			  	$('.bracket').html("Winners Bracket ");
				$('.round').html(cage.round);
				bracket = "Winners Bracket Round "+cage.round;
		  	  } else {
  			  	$('.bracket').html("Losers Bracket ");
  				$('.round').html((cage.round * -1));
				bracket = "Losers Bracket Round "+ (cage.round * -1);
				
		  	  }
			  $('.weightClass').html(cage.weightClass);
			  
			  
			  var matchString = "Match: "+cage.order+" - "+bracket+" - "+cage.weightClass;
			  $('.matchMeta').html(matchString);
			  
			  matchEnd = cage.stopTime;
			  countdownEnd = cage.endCountdown;
			  matchActive = cage.matchActive;
			  matchTime = cage.matchTime;
			  matchPaused = cage.matchPaused;
			  if (cage.player1_ready == 1){
			  	 $(".leftBotName").addClass("ready");
			  } else {
			  	 $(".leftBotName").removeClass("ready");
			  }
			  if (cage.player2_ready == 1){
			  	  $(".rightBotName").addClass("ready");
			  } else {
 			  	 $(".rightBotName").removeClass("ready");
			  }
			  
			  if (cage.player1 == cage.winner){
				  $(".leftWin").show();
			  } else {
			  	 $(".leftWin").hide();
			  }
			  if (cage.player2 == cage.winner){
				  $(".rightWin").show();
			  } else {
			  	  $(".rightWin").hide();
			  }
			  			  
		  } ); 
	}
	
	</script>
	
</head>
<body>
<!-- <video class="sample" no-controls autoplay loop muted playsinline src="SampleMatch.mp4"></video> -->

<div class="matchBar">
	<video no-controls autoplay loop muted playsinline src="assets/loop.mov"></video>
	<div class="leftBotName"><div class="leftWin" id="leftWin"></div ><span id="player1" ><?=$player1;?></span></div>
	<div class="leftBotImage" style="background-image:url('robots/<?=$player1image?>.jpg')"></div>
	<div class="matchClock">Loading</div>
	<div class="rightBotName"><div class="rightWin"></div><span id="player2" ><?=$player2?></span></div>
	<div class="rightBotImage" style="background-image:url('robots/<?=$player2image?>.jpg')"></div>
</div>
<div class="matchDetails">

<div class="matchNumber"><?=$order;?></div>
<?php if($round > 0){
	echo '<div class="bracket">Winners Bracket </div> ';
} else {
	echo '<div class="bracket">Losers Bracket </div> ';
		$round = $round * -1;
}?>
<div class="round"> <?=$round; ?></div>
<div class="weightClass"><?=$weightClass;?></div>

</div>
<div class="countDown">
	<div class="leftBotCountdown"><div class="centerBotName" id="player1countdown"></div></div>
	<div class="rightBotCountdown"><div class="centerBotName" id="player2countdown"></div></div>
	
	<div class="countDownTime">
		-
	</div>
	<div class="matchMeta"></div>

</div>
</body>