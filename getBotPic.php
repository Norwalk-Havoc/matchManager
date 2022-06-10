<?php

$bot = preg_replace("/[^a-zA-Z0-9]+/", "", strtolower($_GET['bot']));

if (isset($_GET['people'])){
	if ($_GET['people'] == 1){
		$base = 'people';
	} else {
		$base = 'robots';
	}
} else {
	$base = 'robots';
}


if (isset($_GET['thumb'])){
	if (file_exists("$base-thumb/$bot.png")){
		header("Location:$base-thumb/$bot.png");
	}
	else {
		header("Location:assets/no_bot_image_thumb.png");
	}
	exit();
}

if (file_exists("$base/$bot-removebg.png")){
	header("Location:$base/$bot-removebg.png");
} else if (file_exists("$base/$bot.jpg")){
	header("Location:$base/$bot.jpg");
}
else {
	header("Location:assets/no_bot_image.png");
}


?>