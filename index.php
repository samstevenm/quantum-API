<?php
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//
// 	API for Lutron Integration Commands
//
//	PHP script to send Lutron Integration commands to Proc or NWK over Telnet
//
//	Version 1.1 now with JSON output
//	Copyright (c) 2017 Sam Myers
//  Influenced by Naikel Aparicio's X10 Project
//
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//
// 	Lutron HANDLER
//
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function sendLutron($area, $level) {

	global $ini_Lutron;

	// Replace spaces with underscores in the name of the appliance
	$appliance = str_replace(' ','_',$area);

	// Now we need to know the interation id (code) of the appliance
	//If it exists in the config ini
	if (isset($ini_Lutron[$appliance]['code'])) {

		$hostname = $ini_Lutron['general']['hostname'];
		$port = $ini_Lutron['general']['port'];
		$username = $ini_Lutron['general']['username']."\r\n";
		$password = $ini_Lutron['general']['password']."\r\n";

		$myObj->config->telnetPort = $port; //recommend hiding after debuging
		$myObj->config->localIP = $hostname; //recommend hiding after debuging
		$myObj->config->userName = $username; //recommend hiding after debuging
		$myObj->area->name = $area;
		$myObj->level = $level;

		//open a connection to the proc with the setting above
		$fp = fsockopen($hostname, $port, $errno, $errstr);
		//$resCon=fread($fp,3000000);
		//return $resconn;

		//pause
		sleep(1);

		//enter the user name and wait
		fwrite($fp, $username);
		sleep(.25);

		//enter the password and wait
		fwrite($fp, $password);
		sleep(.75);

		//if the connection fails, try to tell the user why
		if (!$fp) {

			//build json response
			$myObj->error->type = "HOST_CONNECTION_ERROR";
			$myObj->error->info = $errstr;
			$myJSON = json_encode($myObj, JSON_PRETTY_PRINT);

			//return "There was an error connecting to the Quantum Proc: " . $errstr;

		//if the connection suceeds
		} else {

			//build the integration command to set area to level
			$commandWrite = "#AREA,". $ini_Lutron[$appliance]['code'].",1,".$level.",3". "\r\n";

			//send the integration command and wait
			fwrite($fp, $commandWrite);
			sleep(1);

			//build the integration command to read area level
			$commandRead = "?AREA,". $ini_Lutron[$appliance]['code'].",6\r\n";

			//send the integration command and wait
			fwrite($fp, $commandRead);
			sleep(1);
			$result=fread($fp,3000000);

			//close the connection
			fclose($fp);

			//build json response
			$myObj->area->exists = true;
			$myObj->area->id = $ini_Lutron[$appliance]['code'];
			$myObj->proc->commandWrite = $commandWrite; //recommend hiding after debuging
			$myObj->proc->commandRead = $commandRead; //recommend hiding after debuging
			$myObj->proc->cleartext = $result; //recommend hiding after debuging
			$myJSON = json_encode($myObj, JSON_PRETTY_PRINT);

		}

	//if the area id doesn't exist in the config ini tell the user.
	} else {

			//build json response
			$myObj->area->exists = false;
			$myObj->error->type = "AREA_DNE";
			$myObj->error->info = "The selected area does not exisit, or IDs may have changed.  Check your integration configuration.";
			$myJSON = json_encode($myObj, JSON_PRETTY_PRINT);
		}

		//show json output
		print "<pre>";
		print $myJSON;
		print "</pre>";
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//
// 	MAIN
//
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$Lutron_config = 'lutron.ini';
$ini_Lutron = parse_ini_file($Lutron_config, true);
$level = isset($_GET["level"]) ? $_GET["level"] : "none";
$area = isset($_GET["area"]) ? $_GET["area"] : "none";

//if both area and level are specified
if ($level != "none" && $area != "none") {

	//execute the function
	sendLutron($area, $level);

	//if area and/or level are not specified
	} else {

	//tell user they suck
	$myObj->error->type = "INVALID_REQUEST";
	$myObj->error->info = "One or more required arguments was not specified.";
	$myObj->area->name = $area;
	$myObj->level = $level;
	$myJSON = json_encode($myObj, JSON_PRETTY_PRINT);
	print "<pre>";
	print $myJSON;
	print "</pre>";
	}
?>
