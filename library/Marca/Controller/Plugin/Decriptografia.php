<?php
/**
 *
 * Plugin que irá decriptografar os dados que foram transmitidos
 *
 * @category   Marca Sistemas
 * @copyright  Copyright (c) 1991-2009 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 */
class Marca_Controller_Plugin_Decriptografia extends Zend_Controller_Plugin_Abstract {

    /**
     * (non-PHPdoc)
     * @see library/Zend/Controller/Plugin/Zend_Controller_Plugin_Abstract::postDispatch()
     */
    public function routeShutdown(Zend_Controller_Request_Abstract $request){

        // Recupera os parâmetros passados pela url
        $params = $this->getRequest()->getParams();
               
        // Verifica se foi passado algo criptografado
        // se sim, descriptografa e joga na requisição os
        // dados descriptografados, podendo assim manipula-los
        // posteriormente na action
        if(isset($params["crypt"]) && $params["crypt"] != "") {
            $decript  = base64_decode($params["crypt"]);
            $paramExp = explode("/", $decript);
            $conta = count($paramExp);
            for($i=0; $i < $conta; $i+=2) {
            	
            	if (trim($paramExp[$i]) !== "") {
            		$valor = str_replace("_barra_", "/", $paramExp[$i+1]);
                	$this->getRequest()->setParam($paramExp[$i], $valor);
            	}
            	
            }
            
            // Zera o parâmetro crypt
            $this->getRequest()->setParam("crypt", null);
        }
		
    }

}