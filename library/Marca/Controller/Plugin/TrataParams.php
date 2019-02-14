<?php
/**
 *
 * Plugin que irá tratar os prametros da requisição
 *
 * @category   Marca Sistemas
 * @copyright  Copyright (c) 1991-2011 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 */
class Marca_Controller_Plugin_TrataParams extends Zend_Controller_Plugin_Abstract {

    /**
	 * (non-PHPdoc)
	 * @see library/Zend/Controller/Plugin/Zend_Controller_Plugin_Abstract#preDispatch()
	 */
    public function preDispatch(Zend_Controller_Request_Abstract $request) {

        // Recupera os parâmetros passados pela url
        $params = $this->getRequest()->getParams();
        
        // Percorre os parâmetros da requisição
        foreach($params as $indice => $valor) {
        	
            // Verifica se o valor é um texto
            if(is_string($valor)) {
                // Verifica se tem o valor "_null_" e substitue por NADA
                if(strpos($valor, "_null_") !== false || strpos($valor, "_barra_") !== false) {
                    $v = $this->substituiValorCaracterEspecial($valor);
                    // Seta os parametros novamente
                    $this->getRequest()->setParam($indice, $v);
                    
                } else {
                	// Verifica se o parametro esta vindo via AJAX e se é do plugin autocomplete do jQuery
                	if ($indice == 'term' && $this->_request->isXmlHttpRequest()) {
                		// Decodifica a string
                		$valor = utf8_decode($valor);
                	}

                	// Seta os parametros novamente
                    $this->getRequest()->setParam($indice, urldecode($valor));
                }
            } else if(is_array($valor)) {
            	
            	foreach($valor as $indice2 => $valor2) {
            		// Verifica se o valor2 é um texto
            		if(is_string($valor2)) {
	            		// Verifica se tem o valor "_null_" e substitue por NADA
	        			if(strpos($valor2, "_null_") !== false || strpos($valor2, "_barra_") !== false) {
	            			$v = $this->substituiValorCaracterEspecial($valor2);
	            			// Seta os parametros novamente
	            			$valor[$indice2] = $v;
	            			
	        			} else {
		                	// Seta os parametros novamente
		                    $valor[$indice2] = urldecode($valor2);
		                }
            		}
            	}
            	
            	$this->getRequest()->setParam($indice, $valor);
            }
            
        }
        
    }
    
    private function substituiValorCaracterEspecial($v) {
		$valor = $v;
   		$valor = str_replace("_null_",  "",  $valor);
		$valor = str_replace("_barra_", "/", $valor);
		
		return $valor;
    }
	
}