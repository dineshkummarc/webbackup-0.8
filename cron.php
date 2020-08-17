<?php

/**----------------------------------------------------------------------------------
 | WebBackup v 0.8 -  by Marcos Timm Rossow
 | Copyright (c) 2010 Marcos Timm Rossow
 | Email: marcos@marcos.blog.br
 | Site: http://webbackup.marcos.blog.br
 +-----------------------------------------------------------------------------------
 | WebBackup is free software: you can redistribute it and/or modify
 | it under the terms of the GNU General Public License as published by
 | the Free Software Foundation, either version 3 of the License, or
 | (at your option) any later version.
 | WebBackup is distributed in the hope that it will be useful,
 | but WITHOUT ANY WARRANTY; without even the implied warranty of
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 | See the GNU General Public License for more details on http://www.gnu.org/licenses/
 +------------------------------------------------------------------------------- **/

	set_time_limit(0);
	//ini_set('memory_limit','32M');

	require_once(dirname(__FILE__) . '/inc/php/Xml.Class.php');
	require_once(dirname(__FILE__) . '/inc/php/Compress.Class.php');
	require_once(dirname(__FILE__) . '/inc/php/Mysql.Class.php');
	require_once(dirname(__FILE__) . '/inc/php/Log.Class.php');
	require_once(dirname(__FILE__) . '/inc/php/FTP.class.php');

	$filename 	=	dirname(__FILE__) . "/inc/schedules.xml";
	$schedules 	= 	Xml::readXml($filename,"schedule");

	// manual backup
	if(0 < $_GET["backup"])
	{
		for($i=0; $i < count($schedules); $i++)
		{
			if($_GET["backup"] == $schedules[$i]["id"])
				$newSchedule[0] = $schedules[$i];
		}
		$schedules = $newSchedule;
	}

	foreach($schedules AS $schedule)
	{
		echo "Start Schedule: " . $schedule["name"] . "<br/>";

		if("" != $schedule["email"])
		{
			$email		= 	$schedule["email"];
			$AlwaysSend 	= 	true;
		}
		else
		{
			$user 		= 	Xml::readXml(dirname(__FILE__) . "/inc/user.xml","user");
			$email 		=	$user[0]["email"];
			$AlwaysSend 	= 	false;
		}

		// Start Log Object
		$Log = new Log($schedule["name"],$schedule["id"], $email, $send);
		
		// Verify the backup types
		$types = explode("/",$schedule["backup"]);

		// Verify repeat 
		$repeat	= $schedule["repeat"];
		$last		= $schedule["last"];
		$lastDate	= substr($schedule["last"],0,10);
		$isDay = false;

		if(("never" == $last) OR (0 < $_GET["backup"]))
		{
			$isDay = true;
		}
		else
		{
			switch($repeat)
			{
				case "Daily":
					$arrLastDate 	= explode("/", $lastDate);
					$nextDate 	= date("Ymd", mktime(0, 0, 0, $arrLastDate[1], $arrLastDate[2]+1, $arrLastDate[0]));
					if($nextDate <= date("Ymd"))
						$isDay = true;
					break;
				case "Weekly":
					$arrLastDate 	= explode("/", $lastDate);
					$nextDate 	= date("Ymd", mktime(0, 0, 0, $arrLastDate[1], $arrLastDate[2]+7, $arrLastDate[0]));
					if($nextDate <= date("Ymd"))
						$isDay = true;
					break;
				case "15 days":
					$arrLastDate 	= explode("/", $lastDate);
					$nextDate 	= date("Ymd", mktime(0, 0, 0, $arrLastDate[1], $arrLastDate[2]+15, $arrLastDate[0]));
					if($nextDate <= date("Ymd"))
						$isDay = true;
					break;
				case "Month":
					$arrLastDate 	= explode("/", $lastDate);
					$nextDate 	= date("Ymd", mktime(0, 0, 0, $arrLastDate[1]+1, $arrLastDate[2], $arrLastDate[0]));
					if($nextDate <= str_replace("/","",$lastDate))
						$isDay = true;
					break;
				case "Never":
					break;
			}
		}

		if($isDay)
		{
			// FILES BACKUP
			if (in_array("files", $types))
			{
				// Backup File Name
				$file_name = dirname(__FILE__) . "/temp/".$schedule["id"]."-backup-" . date("Ymd") .".". $schedule["fileformat"];
				// Object Compress
				$File = new Compress($file_name);

				if (!(is_object($File)))
				{
					$Log->setLog("File","Problem to create a " . $schedule["fileformat"] . " file.", false);
					$Log->setLog("Backup","Abort schedule '" . $schedule["name"] . "'.", false);
					$Log->writeLog();
				}
				else
				{
					$Log->setLog("Backup Files","Initiate",true);

					if(is_readable($schedule["folder"]))
					{
						$Log->setLog("Reading Folder","Directory ('" . $schedule["folder"] . "') is readable.", true);
						$Log->setLog("Reading file list","Directory ('" . $schedule["folder"] . "') is directory.", true);

						// Make a Compress File
						$File->AddDir($schedule["folder"]);
						$ret = $File->GetStatus();
						$Log->setLog("File Status",$ret, TRUE);
						$Log->setSeparator();
						$ret = $File->GetNumFiles();
						$Log->setLog("Num Files Add",$ret, TRUE);
						$Log->setSeparator();
						$ret = $File->Close();
						$Log->setLog("File Compress",$ret[1], $ret[0]);
					}
					else
					{
						$Log->setLog("Reading Folder","Directory ('" . $schedule["folder"] . "') is NOT readable.", false);
						$Log->setLog("Backup Files","Skip Backup Files!",false);
						$Log->setSeparator();
					}
				}
			}

			// MYSQL BACKUP
			if (in_array("db", $types))
			{
				// file name
				$file_mysql = dirname(__FILE__) . "/temp/".$schedule["id"]."-mysql-" . date("Ymd") .".zip";

				// set the parameters in objetct
				$MyBackup = new MyBackUp($schedule["host"], 3306, $schedule["user"], $schedule["pass"], $schedule["dbname"]);

				// create a connection
				$ret = $MyBackup->Connect();

				if(!$ret)
				{
					$Log->setLog("Backup Mysql","Skip Backup Mysql!",FALSE);
					$Log->setLog("Backup Mysql",$ret[1],$ret[0]);
					$Log->setSeparator();
				}
				else
				{
					$ret = $MyBackup->BackUp($file_mysql);
					$Log->setLog("Backup Mysql",$ret[1],$ret[0]);
				}
			}

			// ENVIA PARA O FTP
			$FTP = new FTP($schedule["ftphost"], $schedule["ftpuser"], $schedule["ftppass"]);

			if($FTP ->Conecta())
			{
				if("" != $schedule["ftpfolder"])
					$FTP->Dir($schedule["ftpfolder"]);

				if (in_array("files", $types))
				{
					// send Files Backup
					$ret = $FTP->Envia($file_name, $schedule["id"]."-backup-" . date("Ymd") .".". $schedule["fileformat"]);
					$Log->setLog("FTP",$ret[1],$ret[0]);
					
					// DELETE A LOCAL COPY BACKUP FILE
					if($ret[0])
						unlink($file_name);
				}
				if (in_array("db", $types))
				{
					// send Mysql Backup
					$ret = $FTP->Envia($file_mysql, $schedule["id"]."-mysql-" . date("Ymd") .".zip");
					$Log->setLog("FTP",$ret[1],$ret[0]);

					// DELETE A LOCAL COPY BACKUP FILE
					if($ret[0])
						unlink($file_mysql);
				}
				// Close FTP Connection
				$FTP->Fecha();
			}
			else
			{
				$Log->setLog("FTP", "ERROR Trying to connect to FTP", FALSE);
				$Log->setLog("FTP", "Skip send backup files", FALSE);
				$Log->setLog("WebBackup", "A local copy of the backup files will be kept", TRUE);
			}


			if(!$_GET["backup"] OR ($_GET["backup"] AND $schedule["repeat"] == "Never"))
			{
				// Write a date of backup in to schedule file
				$OldSchedules 	= 	Xml::readXml(dirname(__FILE__) . "/inc/schedules.xml","schedule");
				$xml = new Xml("webbackup");
				foreach($OldSchedules AS $OldSchedule)
				{
					if($schedule["id"] == $OldSchedule["id"])
						$OldSchedule["last"] = date("Y/m/d H:i:s");
					$xml->addContent(array("schedules"=>$OldSchedule),"schedule");
				}
				writeXML($xml, $filename);
			}

			// End Log File
			$Log->setSeparator();
			$Log->writeLog();
			echo "<hr/>";
		}
	}

	function writeXML($_obj_xml, $_filename)
	{
		// write schedules in xml file
		if (is_writable($_filename))
		{
			if (!$handle = fopen($_filename, 'w+'))
				echo "Cannot open file ($filename)";
			if (fwrite($handle, $_obj_xml->showXML()) === FALSE)
				echo "Cannot write to file ($filename)";
			fclose($handle);
		}
		else
			echo "The file $filename is not writable";
	}

	echo "Cron Finish!";

?>