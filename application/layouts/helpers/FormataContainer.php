<?php
/**
 * View Helper para formatar o CONTAINER na tela
 *
 * @uses Zend_View_Helper_Abstract
 *
 * @author David Valente
 * 
 * @param string  $param
 * @return string 
 *
 */

class Zend_View_Helper_FormataContainer extends Zend_View_Helper_Abstract
{
	public function formataContainer($param) {

		try {
			
			// Verifica se foi passado algum valor
			if (trim($param) == "") {
				return "";
			}
			
			$partLetras   = substr($param, 0, 4);
			$partNumeros  = substr($param, 4, 3);
			$partNumeros2 = substr($param, 7, 3);
			$partDigito   = substr($param, 10, 1);
			
			return  $partLetras."-".$partNumeros.".".$partNumeros2."-".$partDigito;
			 
		} catch(Exception $e){

			echo $e->getMessage();
		}
	}
}