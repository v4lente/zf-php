<?php
/**
 * View Helper para formatar o PESO na tela
 *
 * @uses Zend_View_Helper_Abstract
 *
 * @author David Valente/Marcio Souza
 * 
 * @param float|string  $param
 * @return float 
 *
 */

class Zend_View_Helper_FormataPeso extends Zend_View_Helper_Abstract
{
	public function formataPeso($param = 0) {

		try {
			
			if (trim($param) == "") {
				$param = 0;
			}
			
			$param = number_format(str_replace(",", ".", $param), 3, '.', '');
			
			return $param;
			 
		} catch(Exception $e){

			echo $e->getMessage();
		}
	}	
}