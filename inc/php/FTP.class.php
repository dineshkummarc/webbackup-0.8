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

	/**
	* Classe FTP responsável por sincronizar arquivos com o servidor
	*
	* @author Marcos Timm Rossow <marcos@marcos.blog.br>
	* @version 0.1
	* @copyright Interwise
	* @access Public
	* @package ADM
	*/
	class FTP
	{
		/** 
		* Variável com o endereço host a ser conectado
		* @access Private
		* @name $_host
		*/
		private $_host;

		/** 
		* Variável para armazenar o login
		* @access Private
		* @name $_login
		*/
		private $_login;

		/** 
		* Variável para armazenar a senha
		* @access Private
		* @name $_senha
		*/
		private $_senha;

		/** 
		* Variável para  armazenar o número da porta
		* Por padrão, porta 21
		* @access Private
		* @name $_obj_xml
		*/
		private $_porta = 21;

		/** 
		* Objeto com a conexão
		* @access Public
		* @name $conn
		*/
		public $conn;

		/** 
		* Método construtor.
		* Inicia o objeto
		* @access Public
		* @param String $_host endereço host a ser conectado
		* @param String $_login login do usuário ftp
		* @param String $_senha senha do usuário
		* @param Int 	$_porta porta a ser usada. Padrão: 21
		* @return bool
		*/
		public function __construct($_host, $_login, $_senha, $_porta = FALSE)
		{
			// Define as credenciais de conexão
			if($_porta)
				$this->_porta = $_porta;
			$this->_host 	= $_host;
			$this->_login 	= $_login;
			$this->_senha 	= $_senha;
			return array(TRUE, "Trying to connect to FTP!");
		}


		public function Conecta()
		{
			// Inicia uma conexão
			$this->conn = ftp_connect($this->_host, $this->_porta);
			if(!$this->conn)
				return false;
			// Loga com o usuário e senha informados
			if(!ftp_login($this->conn, $this->_login, $this->_senha))
				return false;
			if(!ftp_pasv($this->conn, true))
				return false;
			return true;
		}

		public function Dir($dir)
		{
			if(ftp_chdir($this->conn, "/$dir/"))
				return true;
			else
				return false;
		}

		/** 
		* Método para enviar arquivo
		* Envia o arquivo para o servidor FTP
		* @access Public
		* @param String $arquivo arquivo a ser enviado
		* @param String $destino nome do arquivo no servidor
		* @return bool
		*/
		public function Envia($arquivo, $destino)
		{
			$upload = ftp_put($this->conn, $destino, $arquivo, FTP_BINARY);
			if(!$upload)
				return array(FALSE, "Failed to send $arquivo ($destino)");
			else
				return array(TRUE, "Successful send file '$arquivo'");
		}

		public function Fecha()
		{
			if(ftp_close($this->conn))
				return true;
			else
				return false;
		}
	}
?>