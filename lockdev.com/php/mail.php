<?php
    //declare our variables
	

	$name = stripslashes(strip_tags($_POST['name']));
	$email = stripslashes(strip_tags($_POST['email']));
	$message = stripslashes(strip_tags($_POST['msg']));
ob_start();

    $headers = 'From: ' . $email . "\r\n" .
	     	   'Reply-To: ' . $email . "\r\n" .
		       'X-Mailer: PHP/' . phpversion();
	//your email goes here		   
	$myEmail = 'michael@lockdev.com';
	$todayis = date("l, F j, Y, g:i a") ;
	$subject = "A message";	
	$message = " Message: $message \r \n From: $name  \r \n Reply to: $email";
	
	//sending data
	mail($myEmail, $subject, $message, $headers);
?>
