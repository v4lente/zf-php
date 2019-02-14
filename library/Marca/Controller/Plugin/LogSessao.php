<?php
/**
 *
 * Plugin que controlará o log de acesso as transações marcadas para tal finalidade.
 * Se a transação permitir gravar log, será inserido um registro na tabela LOG_SESSAO_TRANSACAO 
 * e também na LOG_SESSAO_TERMINAL_ACESSO.
 *
 * @category   Marca Sistemas
 * @copyright  Copyright (c) 1991-2009 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 */
class Marca_Controller_Plugin_LogSessao extends Zend_Controller_Plugin_Abstract {
    	
	public function routeStartup(Zend_Controller_Request_Abstract $request)
	{		
		try {		
			// Inicializa a classe de autenticação
			$autenticacao = Zend_Auth::getInstance();
			
			// Pega a instancia do controlador principal
			$front        = Zend_Controller_Front::getInstance();
			
			// Captura e valida a sessão do usuário
			$identidade_usuario = $autenticacao->getIdentity();
			if(! empty($identidade_usuario)) {

				// Instancia o objeto do banco
				$db = Zend_Registry::get("db");

				// Busca a sessão portoweb existente
				$portoweb = new Zend_Session_Namespace('portoweb');

				// Pega o ID da SESSAO
				$id_sessao = (int)$portoweb->perfil->ID_SESSAO;
				
				// Cria as sessões da transação/relatório e do terminal de acesso
				if(strtoupper(trim($identidade_usuario)) != "PUBLICO") {
                    $db->query("BEGIN PORTOLOG.P_VERIFICA_LOG_SESSAO_ABERTO({$id_sessao}); END;");				
                }
			}

		} catch (Zend_Db_Exception $e) {
			
			// Caso o usuário ja tenha uma sessão em aberto
			if ($e->getCode() == 20199) {
				
				// Pega a identificação					
				$autenticacao = Marca_Auth::getInstance();
				
				// Captura a sessão
				$sessao = new Zend_Session_Namespace('portoweb');
				
				unset($sessao->_autenticacao);
				
				// Remove o usuário da sessão
				$autenticacao->clearIdentity();
				
				// Seta uma parametro na sessao para mostra uma mensagem para o usuário do sistema
				$sessao->msgSessao = "msg-sessao/saida-forcada";										
			}
			
		}		
		
	}
	
	
	/**
	 * (non-PHPdoc)
	 * @see library/Zend/Controller/Plugin/Zend_Controller_Plugin_Abstract#dispatchLoopShutdown()
	 */
	public function dispatchLoopShutdown() {
		
		// Inicializa a classe de autenticação
        $autenticacao = Zend_Auth::getInstance();

        // Captura e valida a sessão do usuário
        $identidade_usuario = $autenticacao->getIdentity();
        if(! empty($identidade_usuario)) {

            // Recupera os parâmetros passados pela url
            $params = $this->getRequest()->getParams();
            
            // Busca a sessão portoweb existente
            $portoweb = new Zend_Session_Namespace('portoweb');
            
            // Recupera o controlador do frontend
            $front     = Zend_Controller_Front::getInstance();
            $ipEstacao = $front->getRequest()->getServer('REMOTE_ADDR');
            
            // Instancia o objeto do banco
            $db = Zend_Registry::get("db");
			
            // Seta o valor do código da transação e do relatório
            $_cd_transacao = isset($params["_cd_transacao"]) ? $params["_cd_transacao"] : 0;  
            $_cd_relatorio = isset($params["_cd_relatorio"]) ? $params["_cd_relatorio"] : 0;
            
            // Controla se a transação não é a mesma, no caso de
            // uma atualização de tela por f5
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
								
				// Se não validar zera os códigos da transação e do relatório
				if(! $valida) {
					$_cd_transacao = 0;
					$_cd_relatorio = 0;
				}
				
				// Cria as sessões da transação/relatório e do terminal de acesso
				$db->query("BEGIN PORTOLOG.P_GERA_LOG_SESSAO_WEB('{$identidade_usuario}', {$portoweb->perfil->ID_SESSAO}, {$_cd_transacao}, {$_cd_relatorio}, '{$ipEstacao}'); END;");
				
			} catch(Exception $e) {				
				// echo $e->Message();
			}
			
		} // if -> $identidade_usuario
		
	} // dispatchLoopShutdown	
}