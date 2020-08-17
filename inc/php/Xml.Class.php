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
	* Class responsible for formatting content in XML format
	*
	* @author Marcos Timm Rossow <marcos@marcos.blog.br>
	* @version 1.0
	* @copyright Interwise
	* @access Public
	* @package ADM
	*/
	class Xml
	{
		/** 
		* Variable with a root name
		* by default, the name is root.
		* @access Private
		* @name $_root_name
		*/
		private $_root_name = "root";

		/** 
		* Variable with a code type
		* @access Private
		* @name $_root_name
		*/
		private $_encode = 'UTF-8';

		/** 
		* Version of output file
		* @access Private
		* @name $_version_xml
		*/
		private $_version_xml = '1.0';

		/** 
		* Object XML
		* @access Private
		* @name $_obj_xml
		*/
		private $_obj_xml;

		/** 
		* Integer to skip attribute when this was empty
		* @access Private
		* @name $_skip_empty_value
		*/
		public $_skip_empty_value;

		/** 
		* Método construtor.
		* Inicia o objeto e cria o root com o nome do sistema especificado no arquivo de configuração
		* @access Public
		* @param String $_root_name possibilita definir outro nome ao root do documento diferente do informado no arquivo de configuração do sistema
		* @param String $_encode possibilita definir outro tipo de codificação. O padrão é UTF-8
		* @param String $_version_xml possibilita alterar a versão de saída do arquivo XML. Por padrão, versão 1.0
		* @return bool
		*/
		public function __construct($_root_name = FALSE, $_encode = FALSE, $_version_xml = FALSE, $skip_empty_value = FALSE)
		{
			// caso  tenha sido passado um nome para o sistema, define o mesmo
			if(isset($_root_name) AND "" != $_root_name)
				$this->_root_name = $_root_name;

			// caso  tenha sido passado a codificação do sistema, define o mesmo
			if(isset($_encode) AND "" != $_encode)
				$this->_encode = $_encode;

			// caso  tenha sido passado versão de saída do arquivo xml, define o mesmo
			if(isset($_version_xml) AND "" != $_version_xml)
				$this->_version_xml = $_version_xml;

			// inicia o objeto XmlWriter
			$this->_obj_xml = new XmlWriter();
			// inicia a memória 
			$this->_obj_xml->openMemory();
			// inicia o documento passando como parâmetro a versão do documento xml e o tipo de codificação que será usada
			$this->_obj_xml->startDocument($this->_version_xml, $this->_encode);
			// cria a raiz do documento com o nome do sistema
			$this->_obj_xml->startElement($this->_root_name);
			// habilita a identação do documento
			$this->_obj_xml->setIndent(TRUE);
			// define 3 espaços para serem utilizados como identação afim de ficar mais claro no debug
			$this->_obj_xml->setIndentString("   ");

			if($skip_empty_value)
				$this->_skip_empty_value = true;

			return true;
		}

		/** 
		* Método para adicionar conteúdo ao XML
		* Inicia o objeto e cria o root com o nome do sistema especificado no arquivo de configuração
		* @access Public
		* @param String $_array_dados Array de forma associativa com dados a serem inseridos no objeto XML
		* @return bool
		*/
		public function addContent($_arr_dados = FALSE, $_str_indice = FALSE, $_arr_atributo = FALSE, $_int_finalizar = TRUE)
		{
			// verifica se foi passado um array válido
			if(is_array($_arr_dados) AND 0 < count($_arr_dados))
			{
				// percorre os elementos do array
				foreach($_arr_dados as $chave => $valor)
				{
					// verifica se existem filhos para este array
					if(is_array($valor))
					{
						// abre o elemento
						$this->_obj_xml->startElement($_str_indice);

						// verifica se foi passado algum atributo
						if(is_array($_arr_atributo) AND 0 < count($_arr_atributo))
						{
							foreach($_arr_atributo AS $chave => $valor) 
							{
								if(true == $this->_skip_empty_value && "" == $valor) continue;
									$this->_obj_xml->writeAttribute($chave,$valor);
							}
						}

						// recursividade para inserir um elemento interno
						$this->addContent($valor);

						// finaliza o elemento
						if($_int_finalizar)
							$this->_obj_xml->endElement();

						// continua percorrendo o array
						continue;
					}

					// insere elementos que não contenham mais filhos
					if(true == $this->_skip_empty_value && "" == $valor) continue;
						@$this->_obj_xml->writeElement($chave,$valor);
				}
				return true;
			}
			// Como não foi passado um Array com valores, verifica se foi passado um índice
			// para criação de um elemento aberto
			elseif("" != $_str_indice)
			{
				// Caso não tenha passado um array vazio e o Skip estiver ativo, não cria elemento vazio
				if($_int_finalizar AND $this->_skip_empty_value AND !(is_array($_arr_atributo) AND 0 < count($_arr_atributo)))
					return true;

				// abre o elemento
				$this->_obj_xml->startElement($_str_indice);

				// verifica se foi passado algum atributo
				if(is_array($_arr_atributo) AND 0 < count($_arr_atributo))
				{
					foreach($_arr_atributo AS $chave => $valor)
						$this->_obj_xml->writeAttribute($chave,$valor);
					
				}
				// Finaliza o Elemento
				if($_int_finalizar)
					$this->_obj_xml->endElement();
				return true;
			}
			else
			{
				return false;
			}
		}

		/** 
		* Método para retornar o arquivo XML gerado
		* @access Public
		* @return bool
		*/
		public function CloseEl()
		{
			if($this->_obj_xml->endElement())
				return true;
			else
				return false;
		}

		/** 
		* Insert a text in to a opened node
		* @access Public
		* @return bool
		* @param String $text Is a text to insert
		*/
		public function Text($text)
		{
			if("" != $text AND is_string($text))
				if($this->_obj_xml->text($text))
					return true;
				else
					return false;
		}

		/** 
		* Método para retornar o arquivo XML gerado
		* @access Public
		* @return string
		*/
		public function showXML()
		{
			// finaliza o objeto
			$this->_obj_xml->setIndent(true);
			$this->_obj_xml->endElement();

			// retorna o XML gerado
			return $this->_obj_xml->outputMemory(true);
		}

		/** 
		* Método para retornar um array associativo com os filhos de um arquivo XML
		* @access Public
		* @param String $_xml_file nome do arquivo XML a ser lido
		* @param String $_children nome dos filhos a serem lidos
		* @return array
		*/
		public function readXml($_xml_file, $_children)
		{
			$xmlFile = simplexml_load_file($_xml_file);
			$arr = Array();
			$i=0;
			foreach($xmlFile->$_children AS $children)
			{
				foreach($children AS $key => $value)
					$arr[$i][$key] = (string)$value;
				$i++;
			}
			return $arr;
		}

	}
?>