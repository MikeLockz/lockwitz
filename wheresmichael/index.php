<?php
	ob_start();
	require_once 'EpiCurl.php';
	require_once 'EpiFoursquare.php';
	$clientId = 'JN4PIAP4WJITGEI15Z4SYPRAOVJXNHIREPQ5ECZKU1RXJD3N';
	$clientSecret = 'A4Y442LMLYDHPSQ3LHMNV3CXHQRWIOEWAFKCLJI5M34Z4AQU';
	//$code = 'BFVH1JK5404ZUCI4GUTHGPWO3BUIUTEG3V3TKQ0IHVRVGVHS';
	$accessToken = 'W0K3EQN2ZWUI0MU2LH5XBQU0GUVYATU31I0P5GM4WMF3JNTK';
	$redirectUri = 'http://www.lockwitz.com/wheresmichael/index.php';
	$userId = '7125705';
	$fsObj = new EpiFoursquare($clientId, $clientSecret, $accessToken);
	$fsObjUnAuth = new EpiFoursquare($clientId, $clientSecret);
?>
<!doctype html>
<!--[if lt IE 7 ]> <html lang="en" class="no-js ie6"> <![endif]--><!--[if IE 7 ]>    <html lang="en" class="no-js ie7"> <![endif]--><!--[if IE 8 ]>    <html lang="en" class="no-js ie8"> <![endif]--><!--[if IE 9 ]>    <html lang="en" class="no-js ie9"> <![endif]--><!--[if (gt IE 9)|!(IE)]><!--> <html lang="en" class="no-js"> <!--<![endif]--><head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	
	<title>Where's Michael</title>
	<meta name="description" content="The exact where abouts of Michael">
	<meta name="author" content="Michael">
	
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	
	<link rel="shortcut icon" href="/favicon.ico">
	<link rel="apple-touch-icon" href="/apple-touch-icon.png">
	<link rel="stylesheet" href="css/style.css?v=2">
	<script src="js/libs/modernizr-1.7.min.js"></script></head>

	
<body>
	<div id="main" class="wrapper">
		<div id="wood">
			<table>
				<tr>
					<th colspan="4"><h1>Where's Michael</h1></th>
				</tr>
				<tr></tr>
				<tr>
					<th colspan="2"><h2>Departing From</h2></th>
					<th colspan="2"><h2>Arriving In</h2></th>
				</tr>
				<tr>
					<th><h3>Location</h3></th>
					<th><h3>Date</h3></th>
					<th><h3>Location</h3></th>
					<th><h3>Date</h3></th>
				</tr>
				<tr>
					<th class="location_col">
						<ul>
							<li class="city">Indianapolis</li>
							<li class="state">Indiana</li>
						</ul>
					</th>
					<th class="date_col">
						<ul>
							<li class="date">5/17</li>
							<li class="year">2011</li>
							<li class="day">Tue</li>
						</ul>	
					</th>
					<th class="location_col">
						<ul>
							<li class="city">Pittsburgh</li>
							<li class="state">Pennsylvania</li>
						</ul>
					</th>
					<th class="date_col">
						<ul>
							<li class="date">5/20</li>
							<li class="year">2011</li>
							<li class="day">Fri</li>
						</ul>
					</th>
				</tr>
				<tr>
					<th class="location_col">
						<ul>
							<li class="city">South Bend</li>
							<li class="state">Indiana</li>
						</ul>
					</th>
					<th class="date_col">
						<ul>
							<li class="date">5/7</li>
							<li class="year">2011</li>
							<li class="day">Sat</li>
						</ul>
					</th>
					<th class="location_col">
						<ul>
							<li class="city">Denver</li>
							<li class="state">Colorado</li>
						</ul>
					</th>
					<th class="date_col">
						<ul>
							<li class="date">6/2</li>
							<li class="year">2011</li>
							<li class="day">Thur</li>
						</ul>
					</th>
				</tr>
				<tr>
					<th class="location_col">
						<ul>
							<li class="city">Cleveland</li>
							<li class="state">Tennessee</li>
						</ul>
					</th>
					<th class="date_col">
						<ul>
							<li class="date">5/1</li>
							<li class="year">2011</li>
							<li class="day">Sun</li>
						</ul>
					</th>
					<th class="location_col">
						<ul>
							<li class="city">San Diego</li>
							<li class="state">California</li>
						</ul>
					</th>
					<th class="date_col">
						<ul>
							<li class="date">6/6</li>
							<li class="year">2011</li>
							<li class="day">Mon</li>
						</ul>
					</th>
				</tr>
<?php
	$creds = $fsObj->get("/users/{$userId}/checkins");
	//echo $creds->response;
	
	// Load all HISTORY locations
	$locations = array();
	
	$i=0;
	$j=0;
	while($i<10){
	    $city = $creds->response->checkins->items[$i]->venue->location->city;
		$state = $creds->response->checkins->items[$i]->venue->location->state;
		$date = $creds->response->checkins->items[$i]->createdAt;
		
		$nextCity = $creds->response->checkins->items[$i+1]->venue->location->city;
		if ($city != $nextCity) {
			//echo $city.", ".$state." ".$date."<br />";
			$locations[$j]["city"] = $city;
			$locations[$j]["state"] = $state;
			$locations[$j]["date"] = $date;
			
			$j++;
		}
		
		$i++;
	}
	
	
?>
				<tr>
					<th class="location_col">
						<ul>
							<li class="city"><?php echo $locations[1]["city"]; ?></li>
							<li class="state"><?php echo $locations[1]["state"]; ?></li>
						</ul>
					</th>
					<th class="date_col">
						<ul>
							<li class="date"><?php echo date('n/j',$locations[1]["date"]); ?></li>
							<li class="year"><?php echo date('D',$locations[1]["date"]); ?></li>
							<li class="day">Tue</li>
						</ul>
					</th>
					<th class="location_col">
						<ul>
							<li class="city">Chicago</li>
							<li class="state">Illinois</li>
						</ul>
					</th>
					<th class="date_col">
						<ul>
							<li class="date">6/24</li>
							<li class="year">2011</li>
							<li class="day">Tue</li>
						</ul>
					</th>
				</tr>
			</table>
		</div>
	</div>
	
	<div id="console">

	</div>

	
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>
	<script>!window.jQuery && document.write(unescape('%3Cscript src="js/libs/jquery-1.5.1.min.js"%3E%3C/script%3E'))</script>
	<script src="js/script.js"></script>
	<!--[if lt IE 7 ]>
	<script src="js/libs/dd_belatedpng.js"></script>
	<script> DD_belatedPNG.fix('img, .png_bg');</script>
	<![endif]-->
	<script>
		var _gaq=[['_setAccount','UA-XXXXX-X'],['_trackPageview']]; // Change UA-XXXXX-X to be your site's ID
		(function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];g.async=1;
		g.src=('https:'==location.protocol?'//ssl':'//www')+'.google-analytics.com/ga.js';
		s.parentNode.insertBefore(g,s)}(document,'script'));
	</script></body>
</html>