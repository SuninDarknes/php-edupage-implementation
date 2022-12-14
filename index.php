<?php
$subdomain = "";
$username ="";
$password = "";

$data = [
	"username" => $username,
	"password" => $password,
	"csrfauth" => "",
];
//Sending a log in cURL
$ch = curl_init('https://'.$subdomain.'.edupage.org/login/edubarLogin.php');
curl_setopt_array($ch, array( CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POSTFIELDS=> $data,
			CURLOPT_FOLLOWLOCATION=> true,
			CURLOPT_COOKIEFILE=> 'PHPSESSID',
			CURLINFO_HEADER_OUT=> true));
$response = curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);

//If recieved an error while trying to log in
if(strpos($response,"skgdLoginBadMsg")){
	echo "Wrong username or password";
	return;
}

//Get PHPSESSID cookie out of header info
foreach(preg_split("/\s/", $info["request_header"]) as $line)
	if(preg_match("/PHPSESSID/i", $line))	
	$session_cookie =substr($line,10,32 );



//Get gsechash out of response
foreach(preg_split("/((\r?\n)|(\r\n?))/", $response) as $line)
    if(strpos($line, "gsechash"))
		$gsechash =substr($line,14,8 );

//JSON data for requesting information from edupage
$data = [
	"__args" =>
	[
		null,
		[
			"date" => date("Y-m-d"),
			"mode" => "classes"
		]
	],
	"__gsh" => $gsechash
];

$payload =json_encode($data);
//Sending a cURL for data
$ch = curl_init('https://'.$subdomain.'.edupage.org/substitution/server/viewer.js?__func=getSubstViewerDayDataHtml');
curl_setopt_array($ch, array(
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_POSTFIELDS =>$payload,
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json',
    'Cookie: PHPSESSID='.$session_cookie
)));
$response = curl_exec($ch);
curl_close($ch);
echo $response;

?>
