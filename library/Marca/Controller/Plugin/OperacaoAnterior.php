<?php
/**
 *
 * Plugin que ir� armazenar na sess�o os par�metros da requisi��o
 *
 * @filesource
 * @author			David Valente, M�rcio Souza Duarte
 * @copyright		Copyright 2010 Marca
 * @package			zendframework
 * @subpackage		zendframework.application
 * @version			1.0
 */
class Marca_Controller_Plugin_OperacaoAnterior extends Zend_Controller_Plugin_Abstract {


    /**
     * (non-PHPdoc)
     * @see library/Zend/Controller/Plugin/Zend_Controller_Plugin_Abstract::postDispatch()
     */
    public function routeShutdown(Zend_Controller_Request_Abstract $request){

    	// Grava na sess�o do usu�rio a conex�o
		$sessao = new Zend_Session_Namespace('portoweb');
		
		// Recupera o controlador do frontend
        $front  = Zend_Controller_Front::getInstance();

    	// Captura os parametros passados por GET
		$params = $this->getRequest()->getParams();
		
		$modulo     = trim($params["module"]);
		$controller = trim($params["controller"]); 
        $action     = trim($params["action"]);
		
		// Verfica se o systema n�o � o padr�o e 
		// aplica uma excess�o para o controlador "exec-rel"
		// ao qual permite setar a p�gina anterior pois neste 
		// controlador � poss�vel navegar
		if ((($modulo != "default" && 
		    ($front->getRequest()->isXmlHttpRequest() === false) && 
		    (strpos($controller, "tab-") === false)) || 
		    ($modulo == "default" && $controller == "exec-rel")) && 
		    ($action == "index" || $action == "pesquisar")) {
				
	        	// Grava a �ltima requisi��o da action index ou pesquisar
		    	$sessao->voltar["paginaAnterior"] = $params;

		}

    }
}