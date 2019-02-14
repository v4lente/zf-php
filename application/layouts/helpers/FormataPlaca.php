<?php
/**
 * View Helper para formatar a PLACA na tela
 *
 * @uses Zend_View_Helper_Abstract
 *
 * @author David Valente/Marcio Souza
 * 
 * @param string  $param
 * @return string 
 *
 */

class Zend_View_Helper_FormataPlaca extends Zend_View_Helper_Abstract
{
	public function formataPlaca($param = 0) {

		try {
			
			$placa = str_replace(array("-", " "), "", trim(strtoupper($param)));
			
			if (strlen($placa) == 7) {
				$partLetras  = substr($placa, 0, 3);
				$partNumeros = substr($placa, 3);			
				return  $partLetras."-".$partNumeros;				
			}
			
			if (strlen($placa) == 6) {
				$partLetras  = substr($placa, 0, 3);
				$partNumeros = substr($placa, 3);
				return  $partLetras."-".$partNumeros;
			}
			
			 
		} catch(Exception $e){

			echo $e->getMessage();
		}
	}
}