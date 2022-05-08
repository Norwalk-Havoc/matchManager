<?php
error_reporting(E_ALL ^ E_NOTICE);
require_once('matchFunctions.php');

?>


<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="bootstrapDark.min.css" >
	 <link rel="stylesheet" href="upNext.css" >
	<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <title>Image Upload</title>
    <meta name="viewport" content="width=580px, initial-scale=1">
	

  </head>
  <body class=" ">

	<div class="container-sm my-4" id="botDetails">
		<h1>Update robot photo</h1>
		<h4>Please upload a picture of your robot on a WHITE background. We will use this photo in our broadcast and marketing.</h4>
		<form method="post" enctype="multipart/form-data" action="matchFunctions.php?mode=picUpload">
		  <input type="hidden" value="<?=$_GET['bot_id']?>" name="bot_id">
		  <input type="hidden" value="<?=$_GET['imageString']?>" name="imageString">	
		   <input type="hidden" value="<?=$_GET['tournament']?>" name="tournament">	
		  <div class="form-group">
		    <label for="exampleFormControlFile1">Upload new photo</label>
		    <input type="file" class="form-control-file" name="botPicture" accept="image/*" capture="environment" id="exampleFormControlFile1">
		  </div>
		  <button type="submit" class="btn btn-primary">Upload</button>
		</form>
		
		
	</div>

  </body>
</html>