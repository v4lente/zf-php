<?php
/**
 * View Helper para formatar o VALOR na tela
 *
 * @uses Zend_View_Helper_Abstract
 *
 * @author David Valente/Marcio Souza
 * 
 * @param float|string  $param
 * @return float 
 *
 */

class Zend_View_Helper_FormataValor extends Zend_View_Helper_Abstract
{
	public function formataValor($param = 0, $precision = 2) {

		try {
			
			$valor = "";
			
			// Verifica se foi passado algum valor
			if (trim($param) != "") {
				$valor = number_format(str_replace(",", ".", $param), $precision, ',', '.');
			}
			
			return $valor;
			 
		} catch(Exception $e){

			echo $e->getMessage();
		}
	}	
}