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

// ================ My Acount Settings ===================

	if("" != @$_POST["user-name"])
	{
		$xmlUser = new Xml("webbackup");
		$userData["name"] = $_POST["user-name"];
		$userData["email"] = $_POST["user-email"];
		$userData["login"] = $_POST["user-login"];
		if("" != $_POST["user-pass"])
			$userData["pass"] = MD5($_POST["user-pass"]);
		else
			$userData["pass"] = $user[0]["pass"];
		$xmlUser->addContent(array("user"=>$userData),"user");

		writeXML($xmlUser,$userfile);

		if("" != $_POST["user-pass"])
			header("Location: login.php");
		else
		{
			$_SESSION["name"] = $userData["name"];
			$_SESSION["email"] = $userData["email"];
		}
	}

// ================ Manipulate Schedules ===================

	$filename 	=	"inc/schedules.xml";
	$schedules 	= 	Xml::readXml($filename,"schedule");

	if(0 < @$_GET['del'])
	{
		// Initiate xml object
		$xml = new Xml("webbackup");

		// add all schedules to file. Except a del schedule
		foreach($schedules AS $schedule)
			if($_GET["del"] != $schedule["id"])
				$xml->addContent(array("schedules"=>$schedule),"schedule");
		// write a file without a del schedule
		writeXML($xml, $filename);
		// read a new xml file to show in table
		$schedules = Xml::readXml($filename,"schedule");
	}

	if(@$_POST['name'])
	{
		// Initiate xml object
		$xml = new Xml("webbackup");

		// add all schedules to file
		$id = 0;
		foreach($schedules AS $schedule)
		{
			// get the max id schedule
			if($schedule["id"] > $id)
				$id = $schedule["id"];
			// add schedule to xml
			$xml->addContent(array("schedules"=>$schedule),"schedule");
		}

		// add a schedule id
		$_POST["id"] = $id+1;
		$_POST["last"] = "never";

		// Hack for XML assume some properties for backup obtions
		$_POST["backup"] = implode("/",$_POST["backup"]);

		// add a new schedule to xml object
		$xml->addContent(array("schedule"=>$_POST),"schedule");

		// call writeXml function
		writeXML($xml,$filename);

		// read a new xml file to show in table
		$schedules = Xml::readXml($filename,"schedule");
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

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"> 
	<head> 
		<title>WebBackup - Package and Save</title> 
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
		<link rel="shortcut icon" href="inc/img/ico.png" /> 
		<link rel="stylesheet" title="default" href="inc/css/style.css" type="text/css" media="all" /> 
		<link rel="stylesheet" title="default" href="inc/js/pixelmatrix-uniform/css/uniform.default.css" type="text/css" media="all" /> 
		<script src="inc/js/jquery-1.4.2.min.js" type="text/javascript" charset="utf-8"></script> 
		<script src="inc/js/pixelmatrix-uniform/jquery.uniform.min.js" type="text/javascript" charset="utf-8"></script> 
		<script src="inc/js/jquery-validate/jquery.validate.pack.js" type="text/javascript" charset="utf-8"></script> 
		<script src="inc/js/dom.js" type="text/javascript" charset="utf-8"></script> 
	</head>  
 	<body>
		<div id="conteiner">
			<div id="head">
				<div id="logo"><h1>WebBackup - Package and Save</h1></div>
				<div id="user"><b><?php echo $_SESSION["name"]; ?></b> | <a href="index.php">Logout</a></div>
			</div>
			
			<div id="body">
				<div class="top"></div>
				<div class="content">
					<div class="menu">
						<ul>
							<li><img src="inc/img/user_edit.png" /><a class="my-acount-button" href="#">My Acount</a></li>
							<li><img src="inc/img/report.png" /><a class="report-button" href="#">View Log</a></li>
							<li><img src="inc/img/calendar_add.png" /><a href="#" class="new-schedule-button">New Schedule</a></li>
						</ul>
					</div>

					<div id="my-acount" class="hide">
						<form action="home.php" method="post" id="my-acount-form">
							<h2>My Acount</h2>
							<p><label for="user-name">Name</label><input type="text" id="user-name" name="user-name" value="<?php echo $_SESSION["name"]; ?>" /></p>
							<p><label for="user-email">E-mail</label><input type="text" id="user-email" name="user-email" value="<?php echo $_SESSION["email"]; ?>" /></p>
							<p><span class="tip-left">(In case of error, you will be reported)</span></p>
							<p><label for="user-login">Login</label><input type="text" id="user-login" name="user-login" value="<?php echo $_SESSION["login"]; ?>" /></p>
							<p><label for="user-pass">New Password</label><input type="text" id="user-pass" name="user-pass" value="" /></p>
							<p><span class="tip-left">(Just if want to change it)</span></p>
							<p><button type="submit">Save</button></p>
							<p><br/></p>
							<hr/>
						</form>
					</div>

					<div id="new-schedule" class="hide">
						<form action="home.php" method="post" id="new-schedule-form">
							<h2>New Schedule</h2>
							<fieldset id="schedule-options">
								<p><label for="name">Name</label><input type="text" id="name" name="name" value="" /></p>
								<p><label for="email">Report me by e-mail</label><input type="text" id="email" name="email" value="" /></p>
								<span class="tip-left">You always will receive a mail in error case by "My Acount" mail. If you put a mail here, you receive in success case too.</span>
								<p>
									<label for="repeat">Repeat</label>
									<select name="repeat" id="repeat">
										<option value="Never">Never</option>
										<option value="Daily">Daily</option>
										<option value="Weekly">Weekly</option>
										<option value="15 days">15 days</option>
										<option value="Month">Month</option>
									</select>
								</p>
								<p>
									<label for="fileformat">File Format</label>
									<select name="fileformat" id="fileformat">
										<option value="zip">zip</option>
										<!--<option value="tar">tar (require zlib)</option>
										<option value="gzip">gzip (require zlib)</option>
										<option value="bzip2">bzip2 (require zlib)</option>-->
									</select>
								</p>
							</fieldset>

							<p id="backupchoice">
								<br/>
								<label for="backup" class="label-bigger">What do you want save?</label><input type="checkbox" name="backup[]" id="files" value="files" />Files <input type="checkbox" name="backup[]" id="db" value="db" />MySQL
								<label for="backup[]" class="error">Please select at least one topic you'd like to backup.</label>
								<br/>
							</p>

							<fieldset id="field-files" class="hide">
								 <legend for="folder">Folder</legend>
								<p><label>Path</label><input type="text" id="folder" name="folder" value="" /></p>
								<span class="tip-left"><?php echo "e.g. " . $_SERVER['DOCUMENT_ROOT']; ?></span>
							</fieldset>
							<br/>
							<fieldset id="field-db" class="hide">
								 <legend>Database</legend>
								<p><label for="host">Host/IP</label><input type="text" id="host" name="host" value="" /></p>
								<p><label for="dbuser">User</label><input type="text" id="dbuser" name="user" value="" /></p>
								<p><label for="pass">Pass</label><input type="password" id="pass" name="pass" value="" /></p>
								<p><label for="dbname">DB Name</label><input type="text" id="dbname" name="dbname" value="" /></p>
							</fieldset>
							<p><br/></p>
							<fieldset class="ftp">
								 <legend>Backup To (FTP Acess)</legend>
								<p><label for="ftphost">Host/IP</label><input type="text" id="ftphost" name="ftphost" value="" /></p>
								<p><label for="ftpuser">User</label><input type="text" id="ftpuser" name="ftpuser" value="" /></p>
								<p><label for="ftppass">Pass</label><input type="password" id="ftppass" name="ftppass" value="" /></p>
								<p><label for="ftpfolder">Folder</label><input type="text" id="ftpfolder" name="ftpfolder" value="" /></p>
							</fieldset>
							<p><br/></p>
							<p><button id="create" type="submit" >Create</button> <button id="cancel">Cancel</button></p>
							<p><br/></p>
						</form>
						<hr/>
					</div>

					<h2>Schedules</h2>
					<div id="schedules">
						<table>
							<tr>
								<th>Name</th>
								<th>Repeat</th>
								<th>Last</th>
								<th>Options</th>
							</tr>
							<?php
								if($schedules)
								{
									foreach($schedules AS $schedule)
									{
										echo "<tr>";
										echo "<td>". $schedule["name"] ." (".$schedule["backup"].")</td>";
										echo "<td class='center'>". $schedule["repeat"] ."</td>";
										echo "<td class='center'>". $schedule["last"] ."</td>";
										echo '<td class="center"><a target="_blank" href="cron.php?backup=' . $schedule["id"] . '"><img src="inc/img/package_go.png" alt="Backup Now" title="Backup Now" /></a><a href="Javascript: window.open(\'log.php?schedule=' . $schedule["id"] . '\', \'report\', \'width=800,height=600\')"><img src="inc/img/page.png" alt="View Schedule Report" title="View Schedule Report" /></a><a href="?del=' . $schedule["id"] . '"><img src="inc/img/delete.png" alt="Delete Schedule" title="Delete Schedule" /></a></td>';
										echo "</tr>";
									}
								}
							?>
						</table>
					</div>
				</div>
				<div class="bottom"></div>
			</div>

			<div id="foot">
				<p>powered by <a href="http://sourceforge.net/projects/webbackup/">WebBackup</a> | <a href="http://sourceforge.net/donate/index.php?group_id=337229">Buy me a beer (donate)</a></p>
			</div>
		</div>
 	</body>
</html>