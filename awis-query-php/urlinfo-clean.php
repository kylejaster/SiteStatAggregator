<html><body>
<?php

// Make a request to the AWIS UrlInfo operation to get information about a given url

define("SERVICE_ENDPOINT", "http://awis.amazonaws.com?");

define("ACTION", "UrlInfo");
define("RESPONSE_GROUP", "Rank,ContactInfo,LinksInCount,ContentData,UsageStats,RankByCountry");

$site_url = "nyc.gov";

echo("For site: " . $site_url."<br />");

$awis_url = generate_url($site_url);

// Make request

$result = make_http_request($awis_url);

// Display resulting XML

// echo("Response:\n");
// echo($result);

// Parse XML and display results

// $current_tag = "";









function start_tag($parser, $name) {
    global $current_tag, $results;
	$current_tag = $name;
}

function end_tag() {
	global $current_tag;
	$current_tag = '';

}

// Returns the AWS url to get AWIS information for the given site

function generate_url($site_url) {

        $timestamp =  generate_timestamp();
        $site_enc = urlencode($site_url);
        $timestamp_enc = urlencode($timestamp);
        $signature_enc = urlencode(calculate_RFC2104HMAC(ACTION . $timestamp, SECRET_ACCESS_KEY));

   	return SERVICE_ENDPOINT
        . "AWSAccessKeyId=".ACCESS_KEY_ID
        . "&Action=".ACTION
        . "&ResponseGroup=".RESPONSE_GROUP
        . "&Timestamp=$timestamp_enc"
        . "&Signature=$signature_enc"
        . "&Url=$site_enc";

}


// Calculate signature using HMAC: http://www.faqs.org/rfcs/rfc2104.html

function calculate_RFC2104HMAC ($data, $key) {
    return base64_encode (
        pack("H*", sha1((str_pad($key, 64, chr(0x00))
        ^(str_repeat(chr(0x5c), 64))) .
        pack("H*", sha1((str_pad($key, 64, chr(0x00))
        ^(str_repeat(chr(0x36), 64))) . $data))))
     );
}

// Timestamp format: yyyy-MM-dd'T'HH:mm:ss.SSS'Z'

function generate_timestamp () {
    return gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", time());
}

// Make an http request to the specified URL and return the result

function make_http_request($url){
       $ch = curl_init($url);
       curl_setopt($ch, CURLOPT_TIMEOUT, 4);
	   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
       $result = curl_exec($ch);
       curl_close($ch);
       return $result;
}


?>
</body></html>
