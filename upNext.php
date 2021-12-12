<?php

require_once('matchFunctions.php');

if (isset($_GET['mask'])){
	$mask = $_GET['mask'];
} else {
	$maks = 0;
}


?>

<html>
<head>
    <link rel="stylesheet" href="bootstrapPunk.min.css" >
	<?php if ($mask == 0) { ?>
	<link rel="stylesheet" href="upNext.css">
	<?php  } else { ?>
	<link rel="stylesheet" href="upNextMask.css">
	<?php } ?>
</head>
<body>
<!-- <video class="sample" no-controls autoplay loop muted playsinline src="SampleMatch.mp4"></video>	 -->
	
</div>
<div class="matchContainer" id="matchContainer">
	<?php
		
		
	$tournaments = getChallongeTournaments();
	if (isset($_GET['tournament'])){
		$key = $_GET['tournament'];
	} else {
		$key = "3lb-Bracket";
	}
	$tournament = $tournaments[$key];
	if (isset($_GET['round'])){
		$round = $_GET['round'];
	} else {
		$round = 0;
	}
	

	
	printUpNext($tournament,$round);
	
	
	?>

</div>
	<?php
	
	echo '	<script>
		
	var matchUpdate = setInterval(updateAvailable, 5000);
	function updateAvailable(){
		$.get(\'matchFunctions.php?mode=updateUpNext&tournament='.$tournament.'&round='.$round.'\', function( data ) { $( "#matchContainer" ).html( data )} );
		
	}
	
	</script>';
	
	?>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-Piv4xVNRyMGpqkS2by6br4gNJ7DXjqk09RmUpJ8jgGtD7zP9yug3goQfGII0yAns" crossorigin="anonymous"></script>
</body>