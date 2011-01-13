<html>
	<head>
		<title></title>
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3/jquery.min.js"></script>
        <script>
        $(document).ready(function() {
            //console.log('testing'+$('iframe .digit'));            
        });
        
        jQuery.each($('iframe .digit'), function(i, val) {
            $("#content").append(document.createTextNode(" - " + val));
        });
        
        $(document).ready(function() {
            console.log('test '+$('#frame').contents().find('.digit').html());
        });
        </script>
	</head>
	<body>
	<?php 

$url = "http://www.kylejasterstudio.com";


function get_url_contents($url){
        $crl = curl_init();
        $timeout = 5;
        curl_setopt ($crl, CURLOPT_URL,$url);
        curl_setopt ($crl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($crl, CURLOPT_CONNECTTIMEOUT, $timeout);
        $ret = curl_exec($crl);
        curl_close($crl);
        return $ret;
}
	?>
	
	
	
	<!-- 
	<iframe id="frame" src="http://www.quantcast.com/tabs/traffic-tab?wunit=wd:org.realtor&req=0.9342758407018505&country=UK" height="500" width="100%"></iframe>
	<div id="content"></div>
	 -->
	</body>
</html>
