<?php

/**
 *
 * Classe respons�vel por listar os acessos as transa��es do Sistema de determinado usu�rio.
 *
 * @author     M�rcio Souza Duarte
 * @category   Marca Sistemas
 * @copyright  Copyright (c) 1991-2010 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 */
class ConfiguracoesSistema_RelatorioEventoController extends Marca_Controller_Abstract_Operacao {

    /**
     * (non-PHPdoc)
     * @see library/Marca/Controller/Abstract/Marca_Controller_Abstract_Operacao#init()
     */
    public function init() {
        parent::init();
        
        // Carrega os modelos de dados
        Zend_Loader::loadClass("UsuarioModel");
        Zend_Loader::loadClass("LogSessaoModel");
        Zend_Loader::loadClass("LogSessaoTransacaoModel");
        
    }
    
    /**
     * Metodo index
     * objetivo: M�todo principal da classe
     */
    public function indexAction() {
        
        // Captura a sess�o
        $sessao = new Zend_Session_Namespace("portoweb");
        
        // Instancia a classe que gerencia as datas
        $data = new Zend_Date();
        
        // Monta os dados da vis�o
        $dados = array();
        
        // Retorna a data
        $dados["dthr_acesso"] = $data->toString("dd/MM/YYYY");
        
        // Retorna o usu�rio logado
        $dados["usuario"]    = $sessao->perfil->NO_EMPR . " - " . $sessao->perfil->CD_USUARIO;
        $dados["cd_usuario"] = $sessao->perfil->CD_USUARIO;
        
        // Reenvia os valores para o formul�rio
        $this->_helper->RePopulaFormulario->repopular($dados);
    }
    
     /**
     * Metodo novo
     * objetivo: utilizado para formularios de cadastro
     */
    public function novoAction() {}

    /**
     * Metodo salvar
     * objetivo: utilizado para as opera��es de INSERT/UPDATE
     */
    public function salvarAction() {}

    /**
     * Metodo excluir
     * objetivo: utilizado para a opera��o de DELETE
     */
    public function excluirAction() {}

    /**
     * Metodo pesquisar
     * objetivo: utilizado para executar pesquisas
     */
    public function pesquisarAction() {}

    /**
     * Metodo selecionar
     * objetivo: utilizado para selecionar um registro a partir de uma listagem
     */
    public function selecionarAction() {}

    /**
     * Metodo relatorio
     * objetivo: utilizado para gerar um relatorio a partir de uma listagem
     */
    public function relatorioAction() { 
        
        // Chama o m�todo relat�rio da classe pai
        parent::relatorio();
        
        // Recupera os parametros da requisi��o
        $params = $this->_request->getParams();
        
        // Instancia as classes Models
        $logSesTran = new LogSessaoTransacaoModel();
        
        // Captura a sess�o
        $sessao = new Zend_Session_Namespace('portoweb');
                
        // Retorna a query de consulta dos logs de sess�o do usu�rio
        $select = $logSesTran->queryBuscaLogsTransacoesSessaoUsuario($params);
        
        // Define os par�metros para a consulta e retorna o resultado da pesquisa
        $resConsulta = $logSesTran->fetchAll($select);
        
        // Joga para a view o resultado da consulta
        $this->view->resConsulta = $resConsulta;
        
    }
    
	/**
	 * 
	 * Busca informa��es do motorista via AJAX
	 */
    public function autoCompleteXAction(){
		
    	// Verifica se arrequisi��o foi passada por Ajax
		if($this->_request->isXmlHttpRequest()) {
			 
			// Captura os parametros passados por GET
			$params = $this->getRequest()->getParams();

			// Instancia a classe model 
			$usuarios = new UsuarioModel();
            
			// Monta a condi��o
			$where = "UPPER(NO_USUARIO) LIKE UPPER('{$params["term"]}%')";
			
			// Pega os usu�rios			
			$retorno = $usuarios->fetchAll($where);
			
			if(count($retorno) > 0) {
				
           		foreach($retorno as $linha) {
                	
           			// Se o evento vem do campo do nome do motorista
                	$retornoJSON[] = array("id"         => $linha->CD_USUARIO,
                                       	   "value"      => $linha->NO_USUARIO . " - " . $linha->CD_USUARIO
                                       );
                    
           			
           		}
           }
			
			// Retorna os dados por json
			$this->_helper->json(Marca_ConverteCharset::converter($retornoJSON), true);

			// Limpa os objetos da memoria
			unset($motoristas);
		}    	
	}
    
}