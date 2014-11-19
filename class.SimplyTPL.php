<?php
/* ---------------------------------------------------------------------+
| SimplyTPL:                                              build 0001    |
| Easy and usefull template manager class	                            |
+-----------------------------------------------------------------------+
| Copyright (C) 2014  Javier Pulido Hernández                           |
|                                                                       |
| This program is free software: you can redistribute it and/or modify  |
| it under the terms of the GNU General Public License as published by  |
| the Free Software Foundation, either version 3 of the License, or	    |
| (at your option) any later version.                                   |
|                                                                       |
| This program is distributed in the hope that it will be useful,       |
| but WITHOUT ANY WARRANTY; without even the implied warranty of        |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         |
| GNU General Public License for more details.                          |
|                                                                       |
| You should have received a copy of the GNU General Public License	    |
| along with this program.  If not, see <http://www.gnu.org/licenses/>. |
+--------------------------------------------------------------------- */

// SPANISH LANGUAGE
define('SPA_ERROR_STRING',		'Debe ingresar un parametro.');
define('SPA_ERROR_WHERE',		'Error en la funcion ');
define('SPA_WRONG_CHARACTERS',	'El parametro contiene caracteres no permitidos.');

// ENGLISH LANGUAGE
define('ENG_ERROR_STRING', 		'You have to enter parameter');
define('ENG_ERROR_WHERE',		'Error in the function ');
define('ENG_WRONG_CHARACTERS',	'The parameter contains illegal chracters.');

class SimplyTPL {
	private $template;
	private $html_tags = array('id');
	
    function __construct($file, $language = NULL) {
		$language = strtolower($language);
		if ($language == 'spanish')
		{
			define('ERROR_STRING',		ENG_ERROR_STRING);
			define('ERROR_WHERE',		ENG_ERROR_WHERE);
			define('WRONG_CHARACTERS',	ENG_WRONG_CHARACTERS);
		} elseif ($language == "english") {
			define('ERROR_STRING',		SPA_ERROR_STRING);
			define('ERROR_WHERE',		SPA_ERROR_WHERE);
			define('WRONG_CHARACTERS',	SPA_WRONG_CHARACTERS);
		} else {
			$this->debug('The selected language no exists.<br>We applicated at default <b>English</b>.');
		}

        $this->template = file_get_contents('./root/templates/'. $file .'.tpl');
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
		if ($id)
		{
			if(is_string($id))
			{
				if(preg_match('/^([a-z0-9_-]+)$/i', $id)) {
					if($data) {
						if(is_string($data)) {
							$replace = '<$1 $2>'.$data.'</$1>';
							$this->template = preg_replace($this->search_string($id), $replace, $this->template);
							return True;
						} else {
							$this->debug(ERROR_STRING, ERROR_WHERE . 'HTML');
						}
					} else {
						$matches = '';
						preg_match($this->search_string($id), $this->template, $matches);
						return ($matches) ? $matches[0] : false;
					}
				} else {
					$this->debug(WRONG_CHARACTERS);
				}
			} else {
				$this->debug(ERROR_STRING, ERROR_WHERE . 'HTML');
			}
		} else {
			$this->template = $data . $this->template;
		}
	}

	public function after($id, $data) {
		if(is_string($id) and is_string($data))
		{
			$this->template = preg_replace($this->search_string($id), '$0'.$data, $this->template);
			return true;
		} else {
			$this->error(ERROR_STRING . ERROR_WHERE . 'AFTER');
		}
	}

	public function before($id, $data) {
		if(is_string($id) and is_string($data))
		{
			$this->template = preg_replace($this->search_string($id), $data.'$0', $this->template);
			return true;
		} else {
			$this->error(ERROR_STRING . ERROR_WHERE . 'BEFORE');
		}
	}

	public function append($id, $data) {
		if(is_string($id) and is_string($data)) {
			$this->template = preg_replace($this->search_string($id, '<$1 $2>$5'.$data.'</$1>', $this->template);
			return true;
		} else {
			$this->error(ERROR_STRING . ERROR_WHERE . 'APPEND');
		}
	}

	public function prepend($id, $data) {
		if(is_string($id) and is_string($data)) {
			$this->template = preg_replace($this->search_string($id, '<$1 $2>'.$data.'$5</$1>', $this->template);
			return true;
		} else {
			$this->error(ERROR_STRING . ERROR_WHERE . 'PREPEND');
		}
	}

	public function replace($id, $data) {
		if(is_string($id) and is_string($data)) {
			$this->template = preg_replace($this->search_string($id), $data, $this->template);
			return true;
		} else {
			$this->error(ERROR_STRING . ERROR_WHERE . 'REPLACE');
		}
	}

	public function printTo() {
		if($this->template) return $this->template;
	}

	public function printToScreen() {
		print $this->template;
	}

	private function search_string($id) {
		return '/\<([a-z0-9]+)(?:[^\>]*)(id=(?:(\'|"))(?:'. $id .')(?:(\'|"))(?:[^\>]*))\>\s*(.*)\s*\<\/\1\>/i';
	}

	private function debug($error, $func = NULL) {
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
?>
