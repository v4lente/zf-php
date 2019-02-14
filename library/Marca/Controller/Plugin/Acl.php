<?php
/**
 *
 * Plugin que controlará a lista de acesso
 * Verifica se o usuário tem permissão de acessar o metodo requisitado
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

		// Inicializa a classe de autenticação
        $autenticacao = Zend_Auth::getInstance();
        
        // Captura a conexão com o banco de dados
        $db = Zend_Registry::get("db");
        
        // Captura e valida a sessão do usuário
        $identidade_usuario = $autenticacao->getIdentity();
        if(! empty($identidade_usuario)) {
            
        	// Se a requisição não for feita via ajax valida
        	if (! $this->_request->isXmlHttpRequest()){

        		// Captura os dados da url
        		$no_module     = $this->getRequest()->getModuleName();
        		$no_controller = $this->getRequest()->getControllerName();
        		$privilegio    = $this->getRequest()->getActionName();
        		$recurso	   = $no_module . ':' . $no_controller;
        		
                $params = $this->getRequest()->getParams();
                
                // Busca o tipo de transação se é Mestre ou Detalhe
                $sql = $db->select()
                          ->from(array("WEB_TRANSACAO"), array("FL_TIPO_TRANSACAO"))
                          ->where("OBJ_EXECUTADO = '" . $no_controller . "'");
                
                $res = $db->fetchRow($sql);
                
        		// Caso não seja a pagina de login faz a verificação dos privilegios dos usuarios.
                // Também é verificado se a transação é MESTRE, pois não é verificado permissão para tela tipo DETALHE.
        		if ($no_controller !== 'login' && $res->FL_TIPO_TRANSACAO == "M") {
        			// Busca a sessão portoweb existente
        			$portoweb = new Zend_Session_Namespace('portoweb');
        			
        			// Define os privilegios dos usuários
        			if(! isset($portoweb->helperAcl)) {
        				$helperAcl = new Marca_Controller_Helper_Acl();
        				$helperAcl->setRoles();
        				$helperAcl->setResources();
        				$helperAcl->setPrivilegios();
        		
        				// Seta na sessão o objeto da ACL
        				$portoweb->helperAcl = $helperAcl;
                        
        			} else {
        		
        				// Busca da sessão o objeto da ACL
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
                        
        				// Se não tiver permissão dispara uma exceção
        				if(!$permissao) {
        					throw new Zend_Acl_Exception("Sem permissão de acesso para o método " . $privilegio);
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