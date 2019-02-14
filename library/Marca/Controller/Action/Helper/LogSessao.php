<?php
/**
 * Action Helper para registra os logs de acesso
 *
 * @uses Zend_View_Helper_Abstract
 *
 * @author David Valente
 *   
 * @return void
 *
 */

class Marca_Controller_Action_Helper_LogSessao extends Zend_Controller_Action_Helper_Abstract
{
	
	/*
	 *  Contrutor da classe
	 */
	public function __construct() {
        
        // Carrega o modelo
        Zend_Loader::loadClass("LogSessaoModel");
		Zend_Loader::loadClass("LogSessaoTerminalAcessoModel");        
    }

    /**
     * 
     * Busca a sessão atual gerada no banco pela trigger de login
     * e insere na tabela de LOG_SESSAO_TERMINAL_ACESSO
     * 
     */
    public function registraLogin(){
    	
    	// Verifica o login do usuário, se não tem alguma sessão em aberto para o mesmo CD_USUARIO
    	try {
    		
    		$db = Zend_Registry::get("db");
    		 
    		// Cria as sessões da transação/relatório e do terminal de acesso
    		$db->query("BEGIN PORTOLOG.P_LOG_SESSAO_LOGON; END;");
    		
    		// Seta o ID da sessão e atualiza o IP do usuário
    		$this->setLogSessao();
    		
    	} catch(Zend_Db_Exception $e) {	            		
			// echo $e->getMessage(); die;
		}
    }
    
    
	/**
     * 
     * Busca a sessão atual gerada no banco pela trigger de login
     * e insere seta a data de DTHR_LOGOFF na tabela de LOG_SESSAO_TERMINAL_ACESSO
     * para o terminal que esta deslogando do sistema
     * 
     */
    public function registraLogoff(){
    	
    	try {
    		// Classe do modelo de dados da log sessao
    		$logSessao = new LogSessaoModel();

    		// Se a sessão estiver ativa, efetua o logoff da mesma
    		if ($logSessao->verificaSessaoAtiva() === true) {
    			
	    		// Captura a sessão
	    		$sessao = new Zend_Session_Namespace('portoweb');
	
	    		// Pega a referencia da conexão
				$db = Zend_Registry::get("db");
	    		 
	    		// Cria as sessões da transação/relatório e do terminal de acesso
	    		$db->query("BEGIN PORTOLOG.P_LOG_SESSAO_LOGOFF('{$sessao->perfil->ID_SESSAO}'); END;");
    		}
    	
    	} catch(Zend_Db_Exception $e) {
			//echo $e->getMessage(); die;
    	}

    }

    /*
    * Pega o log sessão aberto para o usuário logado
    */
    private function setLogSessao() {

    	// Recupera o controlador do frontend
    	$front             = Zend_Controller_Front::getInstance();

    	// Captura a sessão
    	$sessao            = new Zend_Session_Namespace('portoweb');

    	// Classe do modelo de dados da log sessao
    	$logSessao         = new LogSessaoModel();
    	
    	// Pega a sessão corrente
    	$logSessaoCorrente = $logSessao->getLogSessaoAtiva();

    	// Atualiza o IP do cliente
    	$logSessaoCorrente->IP_ESTACAO = $front->getRequest()->getServer('REMOTE_ADDR');

    	// Salva o registro
    	$logSessaoCorrente->save();

    	// Pega o ID da SESSAO aberta
    	$sessao->perfil->ID_SESSAO = $logSessaoCorrente->ID_SESSAO;
    }
}