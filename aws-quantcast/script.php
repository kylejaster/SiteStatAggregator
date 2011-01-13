<?php 


//$site_url = "realtor.org";


$list = explode("\n",file_get_contents('url-list.txt'));


foreach($list as $k =>$site_url){
    renderSiteData($site_url);
    }
    
function renderSiteData($site_url){
    $traffic = getTrafficData($site_url);
    $rank = getRankData($site_url);
    
    $data = $traffic.", ".$rank."\n";
    
    storeSiteData($site_url, $data);
    }


function getTrafficData($site_url) {
    
    $site_url_mod = explode('.',$site_url);
    $site_url_mod = $site_url_mod[1].'.'.$site_url_mod[0];
         
    $target_url = "http://www.quantcast.com/tabs/traffic-tab?wunit=wd:".$site_url_mod."&req=0.9342758407018505&country=US";
    
    $xpath = getDOM($target_url);
    
    $xpath_results = $xpath->evaluate("/html/body//td");

    $split_list = domElements($xpath_results);
    
    $final_data = $split_list[2].', '.$split_list[4];
    
//    storeLink($final_data,$target_url);
	echo "Traffic Data: $final_data ";
    return $final_data;
}


function getRankData($site_url) {
    
    $target_url = "http://www.quantcast.com/".$site_url;
    
    $xpath = getDOM($target_url);
    
    $xpath_results = $xpath->evaluate("/html/body//ul/li/h4/a/span/strong");

    $split_list = domElements($xpath_results);
    
    $final_data = $split_list[1];
    
//    storeLink($final_data,$target_url);
	echo "Rank Data: $final_data \n";
    return $final_data;
}



function domElements($xpath_results){
    
    $current_list = '';

    for ($i = 0; $i < $xpath_results->length; $i++) {	
        $xpath_result = $xpath_results->item($i);
    	$value = $xpath_result->nodeValue;
    	$current_list .= '| '.$value;
    	}
    
//    echo $current_list;
    
    $split_list = explode("|", str_replace(",","", $current_list));
    
    return $split_list;
    
    }    



function getDOM($target_url) {
    // get domXPath of $url

    $userAgent = "Firefox (WindowsXP) Ð Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6";
    // make the cURL request to $target_url
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
    curl_setopt($ch, CURLOPT_URL,$target_url);
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $html= curl_exec($ch);
    if (!$html) {	echo "cURL error number:" .curl_errno($ch);
    	echo "cURL error:" . curl_error($ch);
    	exit;
    }
    
    // parse the html into a DOMDocument
    
    $dom = new DOMDocument();
    // echo $html;
    
    @$dom->loadHTML($html);
    
    
    // grab all the on the page
    $xpath = new DOMXPath($dom);
    
    return $xpath;    
}



function storeSiteData($gathered_from, $data) {	
    //$query = "INSERT INTO links (url, gathered_from) VALUES ('$url', '$gathered_from')";
	//mysql_query($query) or die('Error, insert query failed');
	$myFile = "results.csv";
    $fh = fopen($myFile, 'a') or die("can't open file");
    $stringData = $gathered_from.', '.$data;
    fwrite($fh, $stringData);
    fclose($fh);	
}




?>






