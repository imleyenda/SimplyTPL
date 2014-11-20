<?php
/* ---------------------------------------------------------------------+
| SimplyTPL:                                              build 0003    |
| Easy and usefull template manager class                               |
+-----------------------------------------------------------------------+
| Copyright (C) 2014  Javier Pulido Hernández                           |
|                                                                       |
| This program is free software: you can redistribute it and/or modify  |
| it under the terms of the GNU General Public License as published by  |
| the Free Software Foundation, either version 3 of the License, or     |
| (at your option) any later version.                                   |
|                                                                       |
| This program is distributed in the hope that it will be useful,       |
| but WITHOUT ANY WARRANTY; without even the implied warranty of        |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         |
| GNU General Public License for more details.                          |
|                                                                       |
| You should have received a copy of the GNU General Public License     |
| along with this program.  If not, see <http://www.gnu.org/licenses/>. |
+--------------------------------------------------------------------- */

// SPANISH LANGUAGE
define('SPA_ERROR_STRING',		'Debe ingresar un parametro.');
define('SPA_ERROR_TPL', 		'La plantilla no esta en el directorio.');
define('SPA_ERROR_WHERE',		'Error en la funcion ');
define('SPA_WRONG_CHARACTERS',	'El parametro contiene caracteres no permitidos.');

// ENGLISH LANGUAGE
define('ENG_ERROR_STRING', 		'You have to enter parameter');
define('ENG_ERROR_TPL', 		'The template is not in the directory.');
define('ENG_ERROR_WHERE',		'Error in the function ');
define('ENG_WRONG_CHARACTERS',	'The parameter contains illegal chracters.');

class SimplyTPL {
	private $template;
	private $dir;
	
    function __construct($directory, $language = NULL) {
		$language = strtolower($language);
		if ($language == 'spanish') {
			define('ERROR_STRING',		ENG_ERROR_STRING);
			define('ERROR_TPL',			ENG_ERROR_TPL);
			define('ERROR_WHERE',		ENG_ERROR_WHERE);
			define('WRONG_CHARACTERS',	ENG_WRONG_CHARACTERS);
		} elseif ($language == "english") {
			define('ERROR_STRING',		SPA_ERROR_STRING);
			define('ERROR_TPL',			SPA_ERROR_TPL);
			define('ERROR_WHERE',		SPA_ERROR_WHERE);
			define('WRONG_CHARACTERS',	SPA_WRONG_CHARACTERS);
		} else {
			$this->debug('The selected language no exists.<br>We applicated at default <b>English</b>.');
		}
		$this->dir = $directory;
    }
	
	public function template($file) {
		if (file_exists($this->dir. $file .'.tpl')) {
			$this->template = file_get_contents($this->dir. $file .'.tpl');
			$this->template = preg_replace(array('/\>[^\S ]+/s','/[^\S ]+\</s','/(\s)+/s'), array('>','<','\\1'), $this->template);
			$this->template = str_replace("> <", "><", $this->template);
		} else {
			$this->debug(ERROR_TPL . ' -> ' . $this->dir. $file .'.tpl');
		}
	}

	public function assign($data) {
		if(is_array($data)) {
			$this->data = $data;
			$this->template = str_replace("'", "\'", $this->template);
			if(!empty($this->data)){
				$this->template = preg_replace('/\{([a-z0-9\-_]+)\}/is', "'.$$1.'", $this->template);
				reset($this->data);
				foreach($this->data as $key => $value) $$key = $value;
				eval("\$this->template = '$this->template';");
				reset($this->data);
				foreach($this->data as $key => $value) unset($$key);
			}
			$this->template = str_replace("\'", "'", $this->template );
			return true;
		} else {
			$this->error(ERROR_STRING . ERROR_WHERE);
		}
	}
	
	public function html($id = NULL, $data = NULL) {
		if ($id) {
			if(is_string($id))
			{
				$matches = ''; preg_match($this->search_string($id), $this->template, $matches);
				$result 	= $matches[1];
				if($data) {
					if(is_string($data)) {
						$this->template = str_replace($result, $data, $this->template);
						return true;
					} else {
						$this->debug(ERROR_STRING, ERROR_WHERE . 'HTML');
					}
				} else {
					return $result;
				}
			} else {
				$this->debug(ERROR_STRING, ERROR_WHERE . 'HTML');
			}
		}else{
			$this->template = ($data) ? $data : "";
		}
	}

	public function after($id, $data) {
		if(is_string($id) and is_string($data))
		{
			$matches = ''; preg_match($this->search_string($id), $this->template, $matches);
			$result 	= $matches[0];
			$this->template = str_replace($result, $data . $result, $this->template);
			return true;
		} else {
			$this->error(ERROR_STRING . ERROR_WHERE . 'AFTER');
		}
	}

	public function before($id, $data) {
		if(is_string($id) and is_string($data))
		{
			$matches = ''; preg_match($this->search_string($id), $this->template, $matches);
			$result = $matches[0];
			$this->template = str_replace($result, $result . $data, $this->template);
			return true;
		} else {
			$this->error(ERROR_STRING . ERROR_WHERE . 'BEFORE');
		}
	}

	public function append($id, $data) {
		if(is_string($id) and is_string($data)) {
			$matches = ''; preg_match($this->search_string($id), $this->template, $matches);
			$this->template = str_replace($matches[1], $data . $matches[1], $this->template);
			return true;
		} else {
			$this->error(ERROR_STRING . ERROR_WHERE . 'APPEND');
		}
	}

	public function prepend($id, $data) {
		if(is_string($id) and is_string($data)) {
			$matches = ''; preg_match($this->search_string($id), $this->template, $matches);
			$this->template = str_replace($matches[1], $matches[1] . $data, $this->template);
			return true;
		} else {
			$this->error(ERROR_STRING . ERROR_WHERE . 'PREPEND');
		}
	}

	public function printTo() {
		if($this->template) return $this->template;
	}

	public function printToScreen() {
		print $this->template;
	}

	private function search_string($id) {
		$matches = ''; preg_match('{(.*):(.*)=(.*)}is', $id, $matches);
		$tag = $matches[1]; $attr = $matches[2]; $id = $matches[3];
		return '{<'.$tag.'\s+'.$attr.'=(?:"|\')(?:'.$id.')(?:"|\')\s*>((?:(?: (?!<'.$tag.'[^>]*>|</'.$tag.'>). )++|<'.$tag.'[^>]*>(?1)</'.$tag.'>)*)</div>}six';
	}

	private function debug($error, $func = NULL) {
		$this->template = false;
		$alert = "<div style='font-family:verdana,arial;font-size:12px;border:1px dashed red;margin:20px;padding:3px;'>";
		$alert .= "<div style='color:red;font-size:13px;'><b>WARNING</b></div>";
		if(is_string($error)) {
			$alert .= "$error";
		} else {
			$alert .= "$error_string";
		}

		if ($func) {
			$alert .= "<br>$func.";
		}
		$alert .= "</div> \n";

		echo $alert;
		return false;
	}
}

print '<!-- GENERATED USING SIMPLYTPL BUILD 003 --!>';
?>
