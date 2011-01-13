<html><body>
<?php

// Make a request to the AWIS UrlInfo operation to get information about a given url

define("ACCESS_KEY_ID", "AKIAJRANUM7WSH75N47A");
define("SECRET_ACCESS_KEY", "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");
define("SERVICE_ENDPOINT", "http://awis.amazonaws.com?");

define("ACTION", "UrlInfo");
define("RESPONSE_GROUP", "Rank,ContactInfo,LinksInCount,ContentData,UsageStats,RankByCountry");

$site_url = "nyc.gov";

echo("For site: " . $site_url."\n\n");

$awis_url = generate_url($site_url);

echo ("Request: \n". $awis_url."\n\n");

// Make request

$result = make_http_request($awis_url);

// Display resulting XML

 echo("Response:\n");
 echo($result);

// Parse XML and display results

$current_tag = "";

$xml_parser  =  xml_parser_create("");
xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, false);
xml_set_element_handler($xml_parser, "start_tag", "end_tag");
xml_set_character_data_handler($xml_parser, "contents");
xml_parse($xml_parser, $result, true);
xml_parser_free($xml_parser);




function objectsIntoArray($arrObjData, $arrSkipIndices = array())
{
    $arrData = array();
    
    // if input is object, convert into array
    if (is_object($arrObjData)) {
        $arrObjData = get_object_vars($arrObjData);
    }
    
    if (is_array($arrObjData)) {
        foreach ($arrObjData as $index => $value) {
            if (is_object($value) || is_array($value)) {
                $value = objectsIntoArray($value, $arrSkipIndices); // recursive call
            }
            if (in_array($index, $arrSkipIndices)) {
                continue;
            }
            $arrData[$index] = $value;
        }
    }
    return $arrData;
}


$xmlStr = file_get_contents($xmlUrl);
$xmlObj = simplexml_load_string($xmlStr);
$arrXml = objectsIntoArray($xmlObj);
print 'testing:'.print_r($arrXml);










// Print out the results

echo ("Results:\n");
echo ("Other sites that link to this site: ".$results['linksincount']."\n");
echo ("Rank: ".$results['rank']."\n");
echo ("Country: ".$results['country']."\n");



function contents($parser, $data) {
    global $current_tag, $results;
    echo 'my results!!!'.print_r($results).'end results';
	switch ($current_tag) {
        case "aws:PhoneNumber":
		                $results['phonenumber'] .= $data;
                break;
        case "aws:OwnerName":
		                $results['ownername'] .= $data;
                break;
        case "aws:Email":
		                $results['email'] .= $data;
                break;
        case "aws:Street":
		                $results['street'] .= $data;
                break;
        case "aws:City":
		                $results['city'] .= $data;
                break;
        case "aws:State":
		                $results['state'] .= $data;
                break;
        case "aws:PostalCode":
		                $results['postalcode'] .= $data;
                break;
        case "aws:Country":
		                $results['country'] .= $data;
                break;
        case "aws:LinksInCount":
		                $results['linksincount'] .=  $data;
                break;
        case "aws:Rank":
		                $results['rank'] .=  $data;
                break;
    
    }
}

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
