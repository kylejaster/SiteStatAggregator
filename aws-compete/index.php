<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd"><html>
	<head>
		<title></title>
	</head>
	<body>



<?php

//print file_get_contents("http://apps.compete.com/sites/compete.com/trended/uv/?apikey=c19978531da2592c81c2a79938be841c&format=json");

$list = explode("\n",file_get_contents('url-list.txt'));

foreach($list as $k => $site_url){

$call_url = "http://apps.compete.com/sites/".$site_url."/trended/search/?apikey=c19978531da2592c81c2a79938be841c&metrics=uv,vis,rank&latest=1";

$json_result = json_decode(file_get_contents($call_url), true);

$myResult = $site_url.', ';
$myResult .= $json_result['data']['trends']['vis'][0]['value'];
$myResult .= ', , , '.$json_result['data']['trends']['uv'][0]['value'];

print $myResult.'<br />';

}
//print print_r($json_result);

?>



	</body>
</html>

