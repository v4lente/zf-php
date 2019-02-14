<?php
/**
 * Action Helper para criptografar parametros passados por array
 *
 * @uses Zend_View_Helper_Abstract
 *
 * @author M�rcio Duarte
 *
 * @param  array    $parametros  Parametros da criptografia
 * @param  boolean  $url         Define se � para retornar a string pronta para ser pasada na url ou n�o
 * 
 * @return string   Retorna uma linha com os par�metros criptografados na base 64
 *
 */

class Marca_Controller_Action_Helper_Criptografia extends Zend_Controller_Action_Helper_Abstract {
	
	/*
	 * Metodo principal do helper
	 */
	public function criptografar($parametros = array(), $url = true) {
        
		// Criptografa a url passada
        if(is_array($parametros) && count($parametros) > 0) {
           	$paramStr = "";
           	foreach($parametros as $indice => $valor) {
           	    if($valor != "") {            	    	
           	    	$valor = str_replace("/","_barra_", $valor);
           	    	$paramStr .= $indice."/".$valor."/";
           	    }
           	}
            
	      	if($paramStr != "") {
	           	$parametros = base64_encode($paramStr);
		     }
        } else {
           	$parametros = "";
        }
        
        // Se for para url, retorna a string pronta, 
        // caso contr�rio retorna somente os par�metros criptografados
        if($url) {
        	$retorno = "crypt/" . $parametros;
        } else {
        	$retorno = $parametros;
        }
        
        // Retorna a string criptografada
        return $retorno;
    }
    
}
