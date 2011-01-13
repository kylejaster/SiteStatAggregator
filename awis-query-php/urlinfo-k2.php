<html><body>
<?php

// Make a request to the AWIS UrlInfo operation to get information about a given url

define("ACCESS_KEY_ID", "AKIAJRANUM7WSH75N47A");
define("SECRET_ACCESS_KEY", "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");
define("SERVICE_ENDPOINT", "http://awis.amazonaws.com?");

define("ACTION", "UrlInfo");
define("RESPONSE_GROUP", "Rank,ContactInfo,LinksInCount,ContentData,UsageStats,RankByCountry");

$site_url = "nyc.gov";

// echo("For site: " . $site_url."<br />");


// Make request

$result = make_http_request($awis_url);

// Display resulting XML

// echo("Response:\n");
// echo($result);

// Parse XML and display results

// $current_tag = "";

$list = explode("\n",file_get_contents('url-list.txt'));


foreach($list as $k =>$v){
        $awis_url = generate_url($v);

        $myArray = xml2array($awis_url, $get_attributes = 1, $priority = 'tag');
        $myResult = '';
        $myResult .= $myArray['aws:UrlInfoResponse']['aws:Response']['aws:UrlInfoResult']['aws:Alexa']['aws:ContactInfo']['aws:DataUrl']; 
        $myResult .= ', '.$myArray['aws:UrlInfoResponse']['aws:Response']['aws:UrlInfoResult']['aws:Alexa']['aws:TrafficData']['aws:Rank'];
        $myResult .= ', '.$myArray['aws:UrlInfoResponse']['aws:Response']['aws:UrlInfoResult']['aws:Alexa']['aws:TrafficData']['aws:RankByCountry']['aws:Country'][0]['aws:Rank'];
        $myResult .= ', '.$myArray['aws:UrlInfoResponse']['aws:Response']['aws:UrlInfoResult']['aws:Alexa']['aws:ContentData']['aws:LinksInCount'];
        $myResult .= ', '.$myArray['aws:UrlInfoResponse']['aws:Response']['aws:UrlInfoResult']['aws:Alexa']['aws:TrafficData']['aws:UsageStatistics']['aws:UsageStatistic'][0]['aws:PageViews']['aws:PerUser']['aws:Value'];

    print $myResult."<br />";
    

}





function xml2array($url, $get_attributes = 1, $priority = 'tag')
{
    $contents = "";
    if (!function_exists('xml_parser_create'))
    {
        return array ();
    }
    $parser = xml_parser_create('');
    if (!($fp = @ fopen($url, 'rb')))
    {
        return array ();
    }
    while (!feof($fp))
    {
        $contents .= fread($fp, 8192);
    }
    fclose($fp);
    xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
    xml_parse_into_struct($parser, trim($contents), $xml_values);
    xml_parser_free($parser);
    if (!$xml_values)
        return; //Hmm...
    $xml_array = array ();
    $parents = array ();
    $opened_tags = array ();
    $arr = array ();
    $current = & $xml_array;
    $repeated_tag_index = array (); 
    foreach ($xml_values as $data)
    {
        unset ($attributes, $value);
        extract($data);
        $result = array ();
        $attributes_data = array ();
        if (isset ($value))
        {
            if ($priority == 'tag')
                $result = $value;
            else
                $result['value'] = $value;
        }
        if (isset ($attributes) and $get_attributes)
        {
            foreach ($attributes as $attr => $val)
            {
                if ($priority == 'tag')
                    $attributes_data[$attr] = $val;
                else
                    $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
            }
        }
        if ($type == "open")
        { 
            $parent[$level -1] = & $current;
            if (!is_array($current) or (!in_array($tag, array_keys($current))))
            {
                $current[$tag] = $result;
                if ($attributes_data)
                    $current[$tag . '_attr'] = $attributes_data;
                $repeated_tag_index[$tag . '_' . $level] = 1;
                $current = & $current[$tag];
            }
            else
            {
                if (isset ($current[$tag][0]))
                {
                    $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                    $repeated_tag_index[$tag . '_' . $level]++;
                }
                else
                { 
                    $current[$tag] = array (
                        $current[$tag],
                        $result
                    ); 
                    $repeated_tag_index[$tag . '_' . $level] = 2;
                    if (isset ($current[$tag . '_attr']))
                    {
                        $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                        unset ($current[$tag . '_attr']);
                    }
                }
                $last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
                $current = & $current[$tag][$last_item_index];
            }
        }
        elseif ($type == "complete")
        {
            if (!isset ($current[$tag]))
            {
                $current[$tag] = $result;
                $repeated_tag_index[$tag . '_' . $level] = 1;
                if ($priority == 'tag' and $attributes_data)
                    $current[$tag . '_attr'] = $attributes_data;
            }
            else
            {
                if (isset ($current[$tag][0]) and is_array($current[$tag]))
                {
                    $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                    if ($priority == 'tag' and $get_attributes and $attributes_data)
                    {
                        $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                    }
                    $repeated_tag_index[$tag . '_' . $level]++;
                }
                else
                {
                    $current[$tag] = array (
                        $current[$tag],
                        $result
                    ); 
                    $repeated_tag_index[$tag . '_' . $level] = 1;
                    if ($priority == 'tag' and $get_attributes)
                    {
                        if (isset ($current[$tag . '_attr']))
                        { 
                            $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                            unset ($current[$tag . '_attr']);
                        }
                        if ($attributes_data)
                        {
                            $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                        }
                    }
                    $repeated_tag_index[$tag . '_' . $level]++; //0 and 1 index is already taken
                }
            }
        }
        elseif ($type == 'close')
        {
            $current = & $parent[$level -1];
        }
    }
    return ($xml_array);
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
