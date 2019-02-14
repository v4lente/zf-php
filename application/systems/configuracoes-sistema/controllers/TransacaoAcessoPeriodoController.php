<?php
/**
 *
 * Classe que tem como fun��o listar os grupos, usu�rios e per�odos de 
 * acesso as funcionalidades do Sistema Porto RG.
 *
 * @author     M�rcio Souza Duarte
 * @category   Marca Sistemas
 * @copyright  Copyright (c) 1991-2010 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 */
class ConfiguracoesSistema_TransacaoAcessoPeriodoController extends Marca_Controller_Abstract_Operacao {

    /**
     * (non-PHPdoc)
     * @see library/Marca/Controller/Abstract/Marca_Controller_Abstract_Operacao#init()
     */
    public function init() {
        
    	parent::init();
        
        // Carrega os modelos de dados
        Zend_Loader::loadClass("WebSistemaModel");
        Zend_Loader::loadClass("WebTransacaoModel");
        Zend_Loader::loadClass("TransacaoModel");
        Zend_Loader::loadClass("UsuarioModel");
    }
    
    /**
     * Metodo index
     * objetivo: M�todo principal da classe
     */
    public function indexAction() { 
        
        // Instancia os modelos de dados
        $webSistemas          = new WebSistemaModel();
        
        // Busca todos os grupos da web e cliente/servidor
        $this->view->sistemas = $webSistemas->listaSistemas();
        
    }
    
     /**
     * Metodo novo
     * objetivo: utilizado para formularios de cadastro
     */
    public function novoAction() { 
        
    }

    /**
     * Metodo salvar
     * objetivo: utilizado para as opera��es de INSERT/UPDATE
     */
    public function salvarAction() { 
        
    }

    /**
     * Metodo excluir
     * objetivo: utilizado para a opera��o de DELETE
     */
    public function excluirAction() { 
        
    }

    /**
     * Metodo pesquisar
     * objetivo: utilizado para executar pesquisas
     */
    public function pesquisarAction() { 
        
    }

    /**
     * Metodo selecionar
     * objetivo: utilizado para selecionar um registro a partir de uma listagem
     */
    public function selecionarAction() { 
        
    }

    /**
     * Metodo relatorio
     * objetivo: utilizado para gerar um relatorio a partir de uma listagem
     */
    public function relatorioAction() { 
        
        // Chama o m�todo relat�rio da classe pai
        parent::relatorio();
                
        // Captura os parametros passados por GET
        $params = $this->getRequest()->getParams();

    	// Instancia os modelos de dados
        $webTransacao = new WebTransacaoModel();
        
        // Verifica o tipo de agrupamento para fazer a consulta
        // Se for do tipo Grupo entra na primeira condi��o, se 
        // for do tipo Usu�rio entra na segunda op��o
        $this->view->agrupamento = $params["agrupamento"]; 
        if($params["agrupamento"] == "T") {
        	
        	$extraWhere = "WT.FL_VISIVEL                      = 1
						   AND WA.FL_MENU                     = 1
						   AND UPPER(WT.OBJ_EXECUTADO) NOT LIKE 'TAB-%'
						   AND WT.AMB_DESENV                  = 'ZEND'
						   AND UPPER(WT.OBJ_EXECUTADO) NOT LIKE  'EXEC-REL%'  
						   AND TRUNC(SYSDATE) >= WGU.DT_ACESSO_INI AND (WGU.DT_ACESSO_FIM IS NULL OR TRUNC(SYSDATE) <= WGU.DT_ACESSO_FIM)
						   AND TRUNC(SYSDATE) >= WGT.DT_ACESSO_INI AND (WGT.DT_ACESSO_FIM IS NULL OR TRUNC(SYSDATE) <= WGT.DT_ACESSO_FIM)";
        	
            $query = $webTransacao->queryBuscaSitemaTransacoesUsuarios($params, $extraWhere, "1=1");
            
        } else {
        	
        	$extraWhere = " TRUNC(SYSDATE)    >= UG.DT_ACESSO_INI AND (UG.DT_ACESSO_FIM IS NULL OR TRUNC(SYSDATE) <= UG.DT_ACESSO_FIM)
   						   AND TRUNC(SYSDATE) >= AT.DT_ACESSO_INI AND (AT.DT_ACESSO_FIM IS NULL OR TRUNC(SYSDATE) <= AT.DT_ACESSO_FIM)";
        	
            $query = $webTransacao->queryBuscaSitemaTransacoesUsuarios($params, "1=1", $extraWhere);
        }
        
        // Retorna para a view os dados da consulta
        $this->view->resConsulta = $webTransacao->fetchAll($query);
    }
    
	/**
	 * 
	 * Busca informa��es dos usu�rios
	 */
    public function retornaUsuarioXAction(){
		
    	// Verifica se arrequisi��o foi passada por Ajax
		if($this->_request->isXmlHttpRequest()) {
			 
			// Captura os parametros passados por GET
			$params = $this->getRequest()->getParams();

			// Instancia a classe model 
			$usuarios = new UsuarioModel();
            
			// Monta a condi��o
			$where = "UPPER(NO_USUARIO) LIKE UPPER('%{$params["term"]}%')";
			
			// Pega os usu�rios			
			$retorno = $usuarios->fetchAll($where);
			
			if(count($retorno) > 0) {
				
           		foreach($retorno as $linha) {
                	
           			// Se o evento vem do campo do nome do motorista
                	$retornoJSON[] = array("value"      => $linha->NO_USUARIO,
                	                       "label"      => $linha->CD_USUARIO . " - " . $linha->NO_USUARIO,
                	                       "cd_usuario" => $linha->CD_USUARIO);
                    
           			
           		}
           }
			
			// Retorna os dados por json
			$this->_helper->json(Marca_ConverteCharset::converter($retornoJSON), true);

			// Limpa os objetos da memoria
			unset($motoristas);
		}
	}
	
	/**
	 * 
	 * Busca as transa��es
	 */
    public function retornaTransacoesXAction(){
		
    	// Verifica se arrequisi��o foi passada por Ajax
		if($this->_request->isXmlHttpRequest()) {
			 
			// Captura os parametros passados por GET
			$params = $this->getRequest()->getParams();

			// Se for para buscar as transa��es da web
			// sen�o as do cliente/servidor
			if ($params['ambiente'] == 'WEB') {
				
				// Instancia a classe model 
				$transacoes           = new WebTransacaoModel();
				
				// Para para o model parametros fixos para serem adicionados ao WHERE
				$extraWhere           = " UPPER(WT.OBJ_EXECUTADO) NOT LIKE ('TAB-%') AND 
								          UPPER(WT.OBJ_EXECUTADO) NOT LIKE ( 'EXEC-REL%') ";
				
				$params['fl_visivel'] = 1;				
				$query                = $transacoes->queryBuscaTransacoes($params, $extraWhere);
				
			} else {
			
				// Instancia a classe model 
				$transacoes = new TransacaoModel();
				
				$extraWhere = "tp_transacao <> 'P'";
				
				$query      = $transacoes->queryBuscaTransacoes($params, $extraWhere);
			}
			
			// Pega os usu�rios			
			$retorno = $transacoes->fetchAll($query);
						
			if(count($retorno) > 0) {
				
           		foreach($retorno as $linha) {
                	
           			// Se o evento vem do campo do nome do motorista
                	$retornoJSON[] = array("CD_TRANSACAO"  => $linha->CD_TRANSACAO,
                	                       "NO_TRANSACAO"  => $linha->NO_TRANSACAO);           			
           		}
           }
			
			// Retorna os dados por json
			$this->_helper->json(Marca_ConverteCharset::converter($retornoJSON), true);

			// Limpa os objetos da memoria
			unset($transacoes);
		}
	}
	
    
}