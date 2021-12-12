<?php
error_reporting(E_ALL ^ E_NOTICE);
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
	<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <title>Wranlger View</title>
	
	<script type="text/javascript">
	$(document).ready(function(){

	  updateAvailable();
	 
	});

	
	var matchUpdate = setInterval(updateAvailable, 15000);
	
	function updateAvailable(){
		$.get('matchFunctions.php?mode=updateMatches', function( data ) { $( "#availableMatches" ).html( data )} );
		
	}
	
	</script>
	<style>
		input {
			display:none;
		}
		.greenroom {
			font-weight: bold;
			text-shadow: 2px 2px 12px #00FF00;
		}
		.greenroomBoth {
			background-color: #70cc7c !important;
		}
	</style>
	
  </head>
  <body>


	<div class="container-fluid">
		<div class="row" id="availableMatches">
			
			<div class="d-flex justify-content-center">
			  <div class="spinner-border" role="status">
			    <span class="sr-only">Loading...</span>
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