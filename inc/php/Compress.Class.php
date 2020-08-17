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
	* Class responsable for Compress files and folder
	*
	* @author Marcos Timm Rossow <marcos@marcos.blog.br>
	* @version 0.3
	* @access Public
	* @package Compress
	*/
	class Compress
	{
		/** 
		* Variable with a file name
		* File to save.
		* @access Private
		* @name $_file_name
		*/
		private $_file_name;

		/** 
		* Path source that will be compress
		* @access Private
		* @name $_source
		*/
		private $_source;

		/** 
		* Compress Type
		* @access Private
		* @name $_type
		*/
		private $_type;

		/** 
		* Object Compress
		* @access Private
		* @name $_obj_com
		*/
		private $_obj_com;

		/** 
		* Constructor.
		* Identify file type and call the rigth method for compress
		* @access Public
		* @param String $_file_name path and file name that will save a compress file
		* @param String $_source path to directoty that will be compress
		* @return bool
		*/
		public function __construct($_file_name)
		{
			$this->_file_name 	= $_file_name;
			//$this->_source	= $_source;

			// array extension
			$_arr_fomart = array (
				'zip'		=>'zip',
				'tar'		=>'tar',
				'gz'		=>'gz',
				'gzip'	=>'gz',
				'bzip'	=>'bz',
				'bz'		=>'bz',
				'bzip2'	=>'bz',
				'bz2'		=>'bz',
				'tgz'		=>'gz',
				'tgzip'	=>'gz',
				'tbzip'	=>'bz',
				'tbz'		=>'bz',
				'tbzip2'	=>'bz',
				'tbz2'	=>'bz'
			);

			// get extension os file
			$ext = pathinfo ($_file_name);
			$ext = $ext['extension'];

			// Identify extension format
			foreach ($_arr_fomart AS $key => $val)
				if (stripos($ext, $key) !== FALSE) 
					$comp=$val;

			// set compress type
			switch($comp) {
				case "zip":
					$this->_type = $comp;
					$this->Zip();
					break;
				case "tar":
					$this->_type = $comp;
					break;
				case "gz":
					$this->_type = $comp;
					break;
				case "bz":
					$this->_type = $comp;
					break;
				default:
					return array(FALSE,"Could not identify file format");
			}
			return array(TRUE,"Format File identify (" . $this->_type . ")");
		}

		private function Zip()
		{
			$this->_obj_com = new ZipArchive();

			if ($this->_obj_com->open($this->_file_name, ZIPARCHIVE::CREATE)!==TRUE)
				return array(FALSE,"Cannot create file '". $this->_file_name ."'");
			else
				return array(TRUE,"File create '". $this->_file_name ."'");
		}

		public function AddDir($_path)
		{
			$this->_obj_com->addEmptyDir($_path);
			$nodes = glob($_path . '/*'); 

			foreach ($nodes as $node)
			{
				if (is_dir($node))
					$this->AddDir($node); 
				else if (is_file($node)) 
					$this->_obj_com->addFile($node); 
			}
		}

		public function AddFromString($_file, $_str)
		{
			if($this->_obj_com->addFromString($_file, $_str))
				return array(TRUE, "File String success created");
			else
				return array(FALSE, "Error creating a file string");
		}

		public function GetStatus()
		{
			return $this->_obj_com->getStatusString();
		}

		public function GetNumFiles()
		{
			return $this->_obj_com->numFiles;
		}

		public function Close()
		{	
			$_num_files = $this->GetNumFiles();

			if($this->_obj_com->close())
				return array(TRUE,"Compress file success created. " . $_num_files . " files added");
			else
				return array(FALSE,"Fail to create a compress file");
		}
	}

?>