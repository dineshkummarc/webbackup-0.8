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
	$_SESSION = array();
	session_destroy();
	session_start();

	if("" != @$_POST['username'] AND "" != @$_POST['password'])
	{
		require_once(dirname(__FILE__) . '/inc/php/Xml.Class.php');

		$filename 	= 	"inc/user.xml";
		$user	 	= 	Xml::readXml($filename,"user");

		if($user[0]["login"] == $_POST['username'] AND $user[0]["pass"] == MD5($_POST['password']))
		{
			$_SESSION['name'] 	=	$user[0]["name"];
			$_SESSION['email']	=	$user[0]["email"];
			$_SESSION['login']	=	$user[0]["login"];
			$msg = "Pass ok.";
			header("Location: home.php");
		}
		else
			$msg = "Invalid Login or Password! Please, try again.";
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
			<div id="login">
				<div id="logo-login"><h1>WebBackup - Package and Save</h1></div>
				<span class="login-error"><?php echo @$msg; ?></span>
				<div id="login-box">
					<form action="index.php" method="post">
						<p><label>Username: </label><input type="text" name="username" /></p>
						<p><label>Password: </label><input type="password" name="password" /></p>
						<p><button type="submit">Login</button></p>
					</form>
				</div>
			</div>

			<div id="foot">
				<p>powered by <a href="http://sourceforge.net/projects/webbackup/">WebBackup</a> | <a href="http://sourceforge.net/donate/index.php?group_id=337229">Buy me a beer (donate)</a></p>
			</div>
		</div>
	</body>
</html>
