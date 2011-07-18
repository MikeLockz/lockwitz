<?php
    
	$myData = $foursquare->GetPrivate("users/self");
	$user = json_decode($myData);
	
	$city = $user->response->user->checkins->items->venue->city;
	
	echo $city; //returns users city

?>