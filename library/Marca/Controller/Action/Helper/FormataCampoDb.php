<?php
/**
 * Helper para formatar os campos na action
 *
 * @filesource
 * @author			David Valente
 * @copyright		Copyright 2011 Marca
 * @package			zendframework
 * @subpackage		zendframework.application
 * @version			1.0
 */
class Marca_Controller_Action_Helper_FormataCampoDb extends Zend_Controller_Action_Helper_Abstract {

	/**
	* Retorna um campo data formatado para inclusão no banco
	*
	* @param string
	* @return string
	*/
	public function data($param = "", $formato = "dd/MM/yyyy") {
	
		try {
			
			$data = "";
			if (trim($param) != "") {
				$dt   = new Zend_Date($param);			
				$data = $dt->get($formato);
			}
			
			return $data;
	
		} catch(Exception $e){
			echo $e->getMessage();
		}
	}
	
	
	/**
	 * Retorna uma placa formatado para inclusão no banco
	 *  
	 * @param string
	 * @return string
	 */
	public function placa($param = "") {
		
		try {
		
			$placa = "";
			if (trim($param) != "") {
				// Remove o traço, espaços e transforma para UPPERCASE
				$placa = str_replace(array("-"," "), "", trim(strtoupper($param)));
			}
		
			return $placa;
			 
		} catch(Exception $e){
			echo $e->getMessage();
		}
	}	
	
	
	/**
	* Retorna um valor sem virgula, substitui ponto por virgula
	*
	* @param string
	* @return string
	*/
	public function peso($param) {
	
		try {
			
			$peso = "";
			if (trim($param) != "") {
				// substitui ponto por virgula
				$peso = str_replace(".", ",", trim($param));
			}

			return $peso; 
	
		} catch(Exception $e){
			echo $e->getMessage();
		}
	}	
	
	/**
	* Retorna um valor sem ponto
	*
	* @param string
	* @return string
	*/
	public function valor($param) {
	
		try {
			
			$valor = "";
			if (trim($param) != "") {
				// substitui ponto por nada
				$valor = str_replace(".", "", trim($param));
			}
			
			return $valor;
	
		} catch(Exception $e){
			echo $e->getMessage();
		}
	}
	
	/**
	* Retorna um valor sem os caracteres passados
	*
	* @param string
	* @return string
	*/
	public function removeMascara($param) {
	
		try {
	
			$mascara = "";
			if (trim($param) != "") {
				// Remove os caracteres
				$mascara = str_replace(array(".","/","-"), "", $param);
			}

			return $mascara;
	
		} catch(Exception $e){
	
			echo $e->getMessage();
		}
	}

	
	/**
	* Retorna um valor ponto
	*
	* @param string
	* @return string
	*/
	public function ajustaValorConcatenado($param = "", $indice = 0, $caracter = "-") {
	
		try {

			$campo = "";
			if (trim($param) != "") {
				
				// Explode a variavel pelo
				$ret   = explode($caracter, $param);
				$campo = $ret[$indice];
			}

			return $campo;

		} catch(Exception $e){
			echo $e->getMessage();
		}
	}
}