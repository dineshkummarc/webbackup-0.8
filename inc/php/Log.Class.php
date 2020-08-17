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

	/** 
	* Class responsible for criate o Log
	*
	* @author Marcos Timm Rossow <marcos@marcos.blog.br>
	* @version 1.0
	* @access Public
	* @package WebBackup
	*/
	class Log
	{
		/** 
		* Variable with a project name
		* @access Private
		* @name $_project_name
		*/
		private $_project_name;

		/** 
		* Variable with name of backup file
		* @access Private
		* @name $_file_name
		*/
		private $_file_name;

		/** 
		* Variable with a time that start the log
		* @access Private
		* @name $_start_time
		*/
		private $_start_time;

		/** 
		* Variable with end log
		* @access Private
		* @name $_end_time
		*/
		private $_end_time;

		/** 
		* Variable with error count
		* @access Private
		* @name $_error
		*/
		private $_error = 0;

		/** 
		* Variable with the general log file
		* @access Private
		* @name $_error
		*/
		private $_general_log = "/../../log/";

		/** 
		* Variable with e-mail for report errors
		* @access Private
		* @name $_error
		*/
		private $_email;

		/** 
		* @access Private
		* @name $_report_by_mail
		*/
		private $_report_by_mail;

		/** 
		* Array with log lines
		* @access Private
		* @name $_log
		*/
		public $_log = Array();

		/** 
		* Constructor Method.
		* Start the log file
		* @access Public
		* @param String $_project_name name of the project
		* @return void
		*/
		public function __construct($_project_name, $_id_project, $_email, $_report_by_mail)
		{
			$this->_project_name 	= 	$_project_name;
			$this->_report_by_mail	= 	$_report_by_mail;
			$this->_email		 	= 	$_email;
			$this->_file_name 		= 	$_id_project . "_" . date("YmdHis") . ".log";
			$this->_start_time 		= 	date("Y/m/d - H:i:s");

			$this->setLog("SCHEDULE ",$this->_project_name,true);
			$this->setLog("START BACKUP",$this->_start_time,true);
			$this->setSeparator();
		}

		public function setLog($_process, $_message, $_status)
		{
			$this->_log[] = Array("time"=>date("Y/m/d - H:i:s"),"process"=>$_process,"message"=>$_message,"status"=>$_status);
			if(!$_status)
				$this->_error++;
		}

		public function setSeparator()
		{
			$this->_log[] = Array("time"=>"","process"=>"-","message"=>"","status"=>"");
		}

		public function getLog()
		{
			$_str_log = "";
			foreach($this->_log AS $log)
			{
				if("-" != $log["process"])
				{
					if($log["status"])
						$log["status"] = "Success!";
					else
						$log["status"] = "Error!";
					$_str_log = $_str_log . $log["time"] . " - " . $log["process"] . " - " . $log["message"] . ": " . $log["status"] . "\n";
				}
				else
					$_str_log = $_str_log . "_________________________________________________________________\n";
			}
			return $_str_log;
		}

		public function writeLog()
		{
			// Set end time
			$this->_end_time = date("Y/m/d - H:i:s");

			// add end backup
			$this->setLog("END BACKUP", $this->_end_time, true);

			// add end line with resume
			if(0 < $this->_error)
			{
				$msg = $this->_error . " ERROR FOUND";
				$status = FALSE;
			}
			else
			{
				$msg = "SUCCESS! No error found";
				$status = TRUE;
			}
			$this->setSeparator();
			$this->_log[] = Array("time"=>date("Y/m/d - H:i:s"),"process"=>"RESUME","message"=>$msg,"status"=>$status);
			

			// write log in the file
			if (is_writable(dirname(__FILE__) . "/../../log/"))
			{
				if (!$handle = fopen(dirname(__FILE__) . "/../../log/" . $this->_file_name, 'w'))
					echo "Cannot open file ($this->_file_name)";
				if (fwrite($handle, $this->getLog()) === FALSE)
					echo "Cannot write log to file ($this->_file_name)";
				fclose($handle);

				// register a global log
				$this->writeGeneralLog();
			}
			else
				echo "The file $this->_file_name is not writable";
		}

		public function reportByMail()
		{
			$send = $this->_report_by_mail;

			// obrigaroty send report
			if(0 < $this->_error)
			{
				$send 	= true;
				$subject 	= "Backup Report (".$this->_schedule."): ERROR";
			}
			else
				$subject 	= "Backup Report (".$this->_schedule."): SUCCESS!";

			if($send AND "" != $this->_email)
			{
				$to      		= $this->_email;
				$message 	= nl2br($this->getLog());
				$headers 		= 'From: webmaster@example.com' . "\r\n" .
							   'Reply-To: webmaster@example.com' . "\r\n" .
							   'X-Mailer: PHP/' . phpversion();
				mail($to, $subject, $message, $headers);
			}
		}

		public function writeGeneralLog()
		{
			// call send mail
			$this->reportByMail();
		
			if(0 < $this->_error)
				$msg = "Found " . $this->_error . " ERROR. Please, check the specific schedule report to see what's happen.";
			else
				$msg = "No errors found - SUCCESS!";
		
			$_general_log = $this->_start_time . " - " . $this->_project_name . ": " . $msg . "\n";

			// write general log in the file
			if (is_writable(dirname(__FILE__) . $this->_general_log))
			{
				if (!$handle = fopen(dirname(__FILE__)  . $this->_general_log . "general.log", 'a'))
					echo "Cannot open file ($this->_general_log)";
				if (fwrite($handle, $_general_log) === FALSE)
					echo "Cannot write log to file ($this->_general_log)";
				fclose($handle);
			}
			else
				echo "The file $this->_general_log is not writable";
		}

	}

?>