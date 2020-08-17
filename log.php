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

	session_start();
	require_once(dirname(__FILE__) . '/inc/php/Xml.Class.php');

	// ================ User Authentication ===================

	// Test user authentication
	$userfile	 	= 	"inc/user.xml";
	$user	 	= 	Xml::readXml($userfile,"user");
	if(!($_SESSION["name"] == $user[0]["name"]))
		header("Location: login.php");

	// ================ Read Report Log ===================

	$schedule = $_GET["schedule"];
	$report = "";
	
	// take a general report log
	if(!$schedule)
	{
		$title 	= "General Log";
		$report 	= nl2br(file_get_contents(dirname(__FILE__) . "/log/general.log", FILE_USE_INCLUDE_PATH));
	}
	// take a specific schedule report
	else
	{
		if ($dh = opendir(dirname(__FILE__) . "/log"))
		{
			$filename 	=	"inc/schedules.xml";
			$schedules 	= 	Xml::readXml($filename,"schedule");

			foreach($schedules AS $task)
				if($task["id"] == $schedule)
					$title = $task["name"];

			$arrLog= array();
			while (($file = readdir($dh)) !== false) 
				if("general.log" != $file AND "." != $file AND ".." != $file AND ".svn" != $file)
					$arrLog[] = $file;
			closedir($dh);
			$arrFiles = "";
			$i=0;
			foreach($arrLog AS $arquivo)
			{
				$IdSchedule 	= substr($arquivo,0,strpos($arquivo, '_'));
				$ScheduleDate = substr($arquivo,strpos($arquivo, '_')+1);

				$year 	= substr($ScheduleDate,0,4);
				$month 	= substr($ScheduleDate,4,2);
				$day	= substr($ScheduleDate,6,2);
				$hour	= substr($ScheduleDate,8,2);
				$minuts	= substr($ScheduleDate,10,2);
				$seconds	= substr($ScheduleDate,12,2);

				$arrFiles[$i]["value"] = $arquivo;
				$arrFiles[$i]["option"] = $year . "/" . $month . "/" . $day . " " . $hour . ":" . $minuts . ":" . $seconds;
				$i++;

			}

			if("" != $_GET['file'])
				$report 	= nl2br(file_get_contents(dirname(__FILE__) . "/log/" . $_GET['file'], FILE_USE_INCLUDE_PATH));
			else
				$report 	= nl2br(file_get_contents(dirname(__FILE__) . "/log/" . $arrFiles[0]["value"], FILE_USE_INCLUDE_PATH));
		}
		
	}

	$report = str_replace("SUCCESS!","<span class='green'>SUCCESS!</span>",$report);
	$report = str_replace("Success!","<span class='green'>Success!</span>",$report);
	$report = str_replace("ERROR FOUND","<span class='red'>ERROR FOUND</span>",$report);
	$report = str_replace("Error!","<span class='red'>Error!</span>",$report);
	$report = str_replace("ERROR","<span class='red'>ERROR</span>",$report);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"> 
	<head> 
		<title>WebBackup - Package and Save</title> 
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
		<link rel="shortcut icon" href="inc/img/ico.png" /> 
		<link rel="stylesheet" title="default" href="inc/css/style.css" type="text/css" media="all" /> 
		<link rel="stylesheet" title="default" href="inc/css/print.css" type="text/css" media="print" /> 
		<link rel="stylesheet" title="default" href="inc/js/pixelmatrix-uniform/css/uniform.default.css" type="text/css" media="all" /> 
		<script src="inc/js/jquery-1.4.2.min.js" type="text/javascript" charset="utf-8"></script> 
		<script src="inc/js/pixelmatrix-uniform/jquery.uniform.min.js" type="text/javascript" charset="utf-8"></script> 
		<script src="inc/js/dom_report.js" type="text/javascript" charset="utf-8"></script> 
	</head>  
 	<body>
		<div id="conteiner-log">
			<div id="head-log">
				<div id="logo"><h1>WebBackup - Package and Save</h1></div>
				<hr id="hr" class="hide" />
				<div id="print"><a href="javascript: JavaScript:window.print();"><img src="inc/img/printer.png" alt="Print" title="Print" /></a></div>
			</div>
<?php
	if(0 < $schedule)
	{
		echo "<p class='choice-report'><label>Report: </label><select id='select-report'>";
		foreach($arrFiles AS $file)
			if($file["value"] == $_GET["file"])
				echo "<option selected='selected' value='schedule=" . $schedule . "&file=" . $file["value"] . "'>" . $file['option'] . "</option>";
			else
				echo "<option value='schedule=" . $schedule . "&file=" . $file["value"] . "'>" . $file['option'] . "</option>";			
		echo "</select></p>";
	}
?>
			<div id="log">
				<h3><?php echo $title; ?></h3>
				<?php 
				
					echo $report; 
				
				?>
			</div>

		</div>
 	</body>
</html>