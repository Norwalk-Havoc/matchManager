<?php

$bot = preg_replace("/[^a-zA-Z0-9]+/", "", strtolower($_GET['bot']));

if (isset($_GET['thumb'])){
	if (file_exists("robots-thumb/$bot.png")){
		header("Location:robots-thumb/$bot.png");
	}
	else {
		header("Location:assets/no_bot_image_thumb.png");
	}
	exit();
}

if (file_exists("robots/$bot-removebg.png")){
	header("Location:robots/$bot-removebg.png");
} else if (file_exists("robots/$bot.jpg")){
	header("Location:robots/$bot.jpg");
}
else {
	header("Location:assets/no_bot_image.png");
}


?>