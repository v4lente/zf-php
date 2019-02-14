<?php
/**
 * Action Helper para adicionar um valor no inicio de um combo
 *
 * @uses Zend_Controller_Action_Helper_Abstract
 *
 * @author David Valente, Marcio Duarte
 *
 * @param array  $combo
 * @param array $valorPadrao
 *
 * @return array
 */

class Zend_View_Helper_AdicionaValorPadraoCombo extends Zend_View_Helper_Abstract
{
	public function adicionaValorPadraoCombo($combo = array(), $valorPadrao = array("_null_" => " -- Selecione -- ")) {
		// Concatena o valor padrão com o combo
        
		$retorno = array();
		if(count($combo) > 0) {
    		
			if(is_array($valorPadrao)) {    			
    			$retorno = $valorPadrao + $combo;    		
    		} else {
    			$retorno = array("" => $valorPadrao) + $combo;
    		}
    		   		
		} else {
		    $retorno = $valorPadrao;
		}
		
		
		return $retorno;
    }
        
}