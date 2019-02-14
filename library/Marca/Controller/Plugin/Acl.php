<?php
/**
 *
 * Plugin que controlar� a lista de acesso
 * Verifica se o usu�rio tem permiss�o de acessar o metodo requisitado
 *
 * @category   Marca Sistemas
 * @copyright  Copyright (c) 1991-2009 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 */
class Marca_Controller_Plugin_Acl extends Zend_Controller_Plugin_Abstract {
    
	/**
	 * (non-PHPdoc)
	 * @see library/Zend/Controller/Plugin/Zend_Controller_Plugin_Abstract#preDispatch()
	 */
	public function routeShutdown(Zend_Controller_Request_Abstract $request) {

		// Inicializa a classe de autentica��o
        $autenticacao = Zend_Auth::getInstance();
        
        // Captura a conex�o com o banco de dados
        $db = Zend_Registry::get("db");
        
        // Captura e valida a sess�o do usu�rio
        $identidade_usuario = $autenticacao->getIdentity();
        if(! empty($identidade_usuario)) {
            
        	// Se a requisi��o n�o for feita via ajax valida
        	if (! $this->_request->isXmlHttpRequest()){

        		// Captura os dados da url
        		$no_module     = $this->getRequest()->getModuleName();
        		$no_controller = $this->getRequest()->getControllerName();
        		$privilegio    = $this->getRequest()->getActionName();
        		$recurso	   = $no_module . ':' . $no_controller;
        		
                $params = $this->getRequest()->getParams();
                
                // Busca o tipo de transa��o se � Mestre ou Detalhe
                $sql = $db->select()
                          ->from(array("WEB_TRANSACAO"), array("FL_TIPO_TRANSACAO"))
                          ->where("OBJ_EXECUTADO = '" . $no_controller . "'");
                
                $res = $db->fetchRow($sql);
                
        		// Caso n�o seja a pagina de login faz a verifica��o dos privilegios dos usuarios.
                // Tamb�m � verificado se a transa��o � MESTRE, pois n�o � verificado permiss�o para tela tipo DETALHE.
        		if ($no_controller !== 'login' && $res->FL_TIPO_TRANSACAO == "M") {
        			// Busca a sess�o portoweb existente
        			$portoweb = new Zend_Session_Namespace('portoweb');
        			
        			// Define os privilegios dos usu�rios
        			if(! isset($portoweb->helperAcl)) {
        				$helperAcl = new Marca_Controller_Helper_Acl();
        				$helperAcl->setRoles();
        				$helperAcl->setResources();
        				$helperAcl->setPrivilegios();
        		
        				// Seta na sess�o o objeto da ACL
        				$portoweb->helperAcl = $helperAcl;
                        
        			} else {
        		
        				// Busca da sess�o o objeto da ACL
        				$helperAcl = $portoweb->helperAcl;
        			}
                    
        			$acl           = $helperAcl->getAcl();
        			$regras        = $helperAcl->getRegras();
        			$totalRegras   = count($regras);
                    
        			try {
        				$permissao = false;
        					
        				// Verifica se o usuario tem privilegio para acessar o controlador/action
        				for($i=0; $i < $totalRegras; $i++) {
        		
        					// caso seja modulo default desconsidera as actions
        					if ($no_module == 'default' && $acl->isAllowed ( $regra, $recurso, null )) {
        						$permissao = true;
        						break;
        		
        					} elseif ($acl->isAllowed ( $regras[$i], $recurso, $privilegio )) {
        						$permissao = true;
        						break;
        					}
                            
        				}
                        
        				// Se n�o tiver permiss�o dispara uma exce��o
        				if(!$permissao) {
        					throw new Zend_Acl_Exception("Sem permiss�o de acesso para o m�todo " . $privilegio);
        				}
        		
        			} catch(Exception $e) {

        				if ($no_module !== 'default') {
        					echo "<script type='text/javascript'>alert('{$e->getMessage()}');</script>";
        				}

        				$this->getRequest()->setModuleName('default');
        				$this->getRequest()->setControllerName('menu');
        				$this->getRequest()->setActionName('index');
        			}        		
        		}        		
        	}            
		}
	}
}