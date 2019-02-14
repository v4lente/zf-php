<?php
/**
 *
 * Plugin que controlar� o log de acesso as transa��es marcadas para tal finalidade.
 * Se a transa��o permitir gravar log, ser� inserido um registro na tabela LOG_SESSAO_TRANSACAO 
 * e tamb�m na LOG_SESSAO_TERMINAL_ACESSO.
 *
 * @category   Marca Sistemas
 * @copyright  Copyright (c) 1991-2009 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 */
class Marca_Controller_Plugin_LogSessao extends Zend_Controller_Plugin_Abstract {
    	
	public function routeStartup(Zend_Controller_Request_Abstract $request)
	{		
		try {		
			// Inicializa a classe de autentica��o
			$autenticacao = Zend_Auth::getInstance();
			
			// Pega a instancia do controlador principal
			$front        = Zend_Controller_Front::getInstance();
			
			// Captura e valida a sess�o do usu�rio
			$identidade_usuario = $autenticacao->getIdentity();
			if(! empty($identidade_usuario)) {

				// Instancia o objeto do banco
				$db = Zend_Registry::get("db");

				// Busca a sess�o portoweb existente
				$portoweb = new Zend_Session_Namespace('portoweb');

				// Pega o ID da SESSAO
				$id_sessao = (int)$portoweb->perfil->ID_SESSAO;
				
				// Cria as sess�es da transa��o/relat�rio e do terminal de acesso
				if(strtoupper(trim($identidade_usuario)) != "PUBLICO") {
                    $db->query("BEGIN PORTOLOG.P_VERIFICA_LOG_SESSAO_ABERTO({$id_sessao}); END;");				
                }
			}

		} catch (Zend_Db_Exception $e) {
			
			// Caso o usu�rio ja tenha uma sess�o em aberto
			if ($e->getCode() == 20199) {
				
				// Pega a identifica��o					
				$autenticacao = Marca_Auth::getInstance();
				
				// Captura a sess�o
				$sessao = new Zend_Session_Namespace('portoweb');
				
				unset($sessao->_autenticacao);
				
				// Remove o usu�rio da sess�o
				$autenticacao->clearIdentity();
				
				// Seta uma parametro na sessao para mostra uma mensagem para o usu�rio do sistema
				$sessao->msgSessao = "msg-sessao/saida-forcada";										
			}
			
		}		
		
	}
	
	
	/**
	 * (non-PHPdoc)
	 * @see library/Zend/Controller/Plugin/Zend_Controller_Plugin_Abstract#dispatchLoopShutdown()
	 */
	public function dispatchLoopShutdown() {
		
		// Inicializa a classe de autentica��o
        $autenticacao = Zend_Auth::getInstance();

        // Captura e valida a sess�o do usu�rio
        $identidade_usuario = $autenticacao->getIdentity();
        if(! empty($identidade_usuario)) {

            // Recupera os par�metros passados pela url
            $params = $this->getRequest()->getParams();
            
            // Busca a sess�o portoweb existente
            $portoweb = new Zend_Session_Namespace('portoweb');
            
            // Recupera o controlador do frontend
            $front     = Zend_Controller_Front::getInstance();
            $ipEstacao = $front->getRequest()->getServer('REMOTE_ADDR');
            
            // Instancia o objeto do banco
            $db = Zend_Registry::get("db");
			
            // Seta o valor do c�digo da transa��o e do relat�rio
            $_cd_transacao = isset($params["_cd_transacao"]) ? $params["_cd_transacao"] : 0;  
            $_cd_relatorio = isset($params["_cd_relatorio"]) ? $params["_cd_relatorio"] : 0;
            
            // Controla se a transa��o n�o � a mesma, no caso de
            // uma atualiza��o de tela por f5
            $valida = true;
            if(!isset($portoweb->_cd_transacao_old) || $portoweb->_cd_transacao_old != $_cd_transacao) {
            	$portoweb->_cd_transacao_old = $_cd_transacao;
            		
            } elseif($portoweb->_cd_transacao_old == $_cd_transacao) {
            		
            	if($_cd_relatorio != "") {
            
            		if(!isset($portoweb->_cd_relatorio_old) || $portoweb->_cd_relatorio_old != $_cd_relatorio) {
            			$portoweb->_cd_relatorio_old = $_cd_relatorio;
            			$valida = true;
            				
            		} elseif($portoweb->_cd_relatorio_old == $_cd_relatorio) {
            			$valida = false;
            		}
            
            	} else {
            
            		$valida = false;
            	}
            }
         
			try {
								
				// Se n�o validar zera os c�digos da transa��o e do relat�rio
				if(! $valida) {
					$_cd_transacao = 0;
					$_cd_relatorio = 0;
				}
				
				// Cria as sess�es da transa��o/relat�rio e do terminal de acesso
				$db->query("BEGIN PORTOLOG.P_GERA_LOG_SESSAO_WEB('{$identidade_usuario}', {$portoweb->perfil->ID_SESSAO}, {$_cd_transacao}, {$_cd_relatorio}, '{$ipEstacao}'); END;");
				
			} catch(Exception $e) {				
				// echo $e->Message();
			}
			
		} // if -> $identidade_usuario
		
	} // dispatchLoopShutdown	
}