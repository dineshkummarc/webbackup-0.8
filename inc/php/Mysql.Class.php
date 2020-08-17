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

	require_once(dirname(__FILE__) . '/Compress.Class.php');

	/** 
	* Class responsable for make mysql backup
	*
	* @author Marcos Timm Rossow <marcos@marcos.blog.br>
	* @version 0.3
	* @access Public
	* @package WebBackup
	*/
	class MyBackUp
	{

		/** 
		* Variable with a server host
		* Host/IP
		* @access Private
		* @name $_server
		*/
		private $_host = "localhost";

		/** 
		* Port to connect
		* Port
		* @access Private
		* @name $_port
		*/
		private $_port = 3306;

		/** 
		* Mysql user
		* Username
		* @access Private
		* @name $_user
		*/
		private $_user = "root";

		/** 
		* Password for mysql
		* Password
		* @access Private
		* @name $_pass
		*/
		private $_pass	= "";

		/** 
		* Data base name
		* Database
		* @access Private
		* @name $_dbase
		*/
		private $_dbase = "";

		/** 
		* Obj connection
		* Connection
		* @access Private
		* @name $_link
		*/
		private $_link = -1;

		/** 
		* Connection status
		* Status
		* @access Private
		* @name $_connected
		*/
		private $_connected = false;

		/** 
		* Array with error log
		* Error
		* @access Private
		* @name $_error
		*/
		private $_error = "";

		/** 
		* File to save
		* File
		* @access Private
		* @name $_filename
		*/
		private $_filename = "mysql-backup.sql";

		/** 
		* Constructor.
		* Set the parameters for connection
		* @access Public
		* @param String $_host connection host/ip
		* @param Integer $_port connection port
		* @param String $_user connection username
		* @param String $_pass connection password
		* @param String $_db connection database
		* @return bool
		*/
		public function __construct($_host, $_port, $_user, $_pass, $_db)
		{
			$this->_host 	= $_host;
			$this->_port 	= $_port;
			$this->_user 	= $_user;
			$this->_pass 	= $_pass;
			$this->_dbase 	= $_db;
			return true;
		}

		function Connect()
		{
			if($this->_connected = $this->_connect())
				return array(TRUE, "Mysql Success Connection");
			else
				return array(FALSE, "Mysql Connection Failed");
		}

		function BackUp($_file)
		{
			$this->_filename = $_file;
			return $this->_saveFile();
		}

		function _connect()
		{
			$value	= false;
			if(!$this->_connected)
			{
				$host		= $this->_host.":".$this->_port;
				$this->_link	= mysql_connect($host,$this->_user,$this->_pass);
			}
			if($this->_link !==-1)
			{
				$value	= mysql_select_db($this->_dbase,$this->_link);
			}
			else
			{
				$value	= mysql_select_db($this->_dbase);
			}
			if(!$value)
			{
				$this->_error = mysql_error();
				return false;
			}
			else
				return $value;
		}

		function _query($sql)
		{
			if ($this->_link !== -1)
				$result = mysql_query($sql, $this->_link);
			else
				$result = mysql_query($sql);

			if (!$result)
				$this->_error = mysql_error();

			return $result;
		}

		function _getTables()
		{
			$value	= array();
			if(!($result = $this->_query("SHOW TABLES")))
				return false;

			while($row = mysql_fetch_row($result))
				$value[] = $row[0];

			if (!sizeof($value))
			{
				$this->_error = 'No tables found in Database.';
				return false;
			}
			return $value;
		}

		function _dumpData($table)
		{
			$value="";
			$this->_query("LOCK TABLES $table WRITE");
			$value .= "-- \n";
			$value .= "--  Table structure for table `$table`\n";
			$value .= "-- \n\n";
			if(!($result = $this->_query("SHOW CREATE TABLE $table")))
				return false;
			$row	= mysql_fetch_assoc($result);
			$value .= $row['Create Table'].";\n\n";
			$value .= "\n-- Dumping data for table `$table`\n\n";
			$value .= $this->_getInserts($table);
			$this->_query("UNLOCK TABLES");
			return $value;
		}
		
		function _getInserts($table)
		{
			$value = '';
			if(!($result = $this->_query("SELECT * FROM $table")))
				return false;

			while($row = mysql_fetch_row($result))
			{
				$datum	= "";
				foreach($row as $data)
					$datum .= "'".addslashes($data)."', ";

				$datum	= substr($datum,0,-2);
				$value.= "INSERT INTO $table VALUES ($datum);\n";
			}
			return $value;
		}

		function _retrieve()
		{
			if(!$this->_connect())
				return false;

			$value  = "-- WebBackup Database\n";
			$value .="-- Site: http://sourceforge.net/projects/webbackup/\n\n";
			$value .="-- Host: $this->_host\n";
			$value .="-- Generation Time: ".date('M d, Y')." at ".date('H:i')." \n";
			$value .="-- \n\n";

			if(!empty($this->database))
			{
				$value .="-- \n";
				$value .="-- Database: `$this->database`\n";
				$value .="-- \n";
			}
			if(!($tables = $this->_getTables()))
				return false;
			foreach($tables as $table)
			{
				if(!($dumpTable = $this->_dumpData($table)))
					return false;
				$value .=$dumpTable;
			}
			return $value;
		}

		function _saveFile()
		{

			// Object Compress
			$File = new Compress($this->_filename);
			
			if($File->AddFromString($this->_dbase . ".sql", $this->_retrieve()))
			{
				$ret = $File->Close();
				return array(TRUE, "Mysql backup success file create");
			}
			else
				return array(FALSE, "Error creating a nysql file backup");
			/*
			if(!($fp = fopen($this->_filename,"w")))
				return array(FALSE, "Unable to Open File");

			$data = $this->_retrieve();
			fwrite($fp,$data);
			fclose($fp);
			@chmod($this->_filename,0744);
			return array(TRUE, "Mysql backup success file create");
			*/
		}
	}
?>