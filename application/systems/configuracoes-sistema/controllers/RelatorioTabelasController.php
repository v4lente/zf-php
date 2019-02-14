<?php

/**
 *
 * Classe responsável por listar as tabelas.
 *
 * @author     David Valente @v4lente
 * @category   Marca Sistemas
 * @copyright  Copyright (c) 1991-2011 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 */
class ConfiguracoesSistema_RelatorioTabelasController extends Marca_Controller_Abstract_Operacao {

    /**
     * (non-PHPdoc)
     * @see library/Marca/Controller/Abstract/Marca_Controller_Abstract_Operacao#init()
     */
    public function init() {

    	parent::init();
        
        // Carrega os modelos de dados
        Zend_Loader::loadClass("TabelaRModel"); 
        Zend_Loader::loadClass("SistemaModel");
    }
    
    /**
     * Metodo index
     * objetivo: Método principal da classe
     */
    public function indexAction() {}
    
     /**
     * Metodo novo
     * objetivo: utilizado para formularios de cadastro
     */
    public function novoAction() {}

    /**
     * Metodo salvar
     * objetivo: utilizado para as operações de INSERT/UPDATE
     */
    public function salvarAction() {}

    /**
     * Metodo excluir
     * objetivo: utilizado para a operação de DELETE
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
        
        // Chama o método relatório da classe pai
        parent::relatorio();
        
        // Recupera os parametros da requisição
        $params = $this->_request->getParams();
        
        // Instancia as classes Models
        $tabelas = new TabelaRModel();
                
        // Retorna a query de consulta dos logs de sessão do usuário
        $select = $tabelas->queryBuscaTabelasColunas($params);
        
        // Define os parâmetros para a consulta e retorna o resultado da pesquisa
        $resConsulta = $tabelas->fetchAll($select);
        
        // Joga para a view o resultado da consulta
        $this->view->resConsulta = $resConsulta;
        
    }
    
    
	/**
	 * 
	 * Busca os dados via ajax confome os parametros passado.
	 */
    public function autoCompleteXAction(){
		
    	// Verifica se arrequisição foi passada por Ajax
		if($this->_request->isXmlHttpRequest()) {
			 
			// Captura os parametros passados por GET
			$params = $this->getRequest()->getParams();
		
			// Bloco de código para controlar o auto-complete-x da TABELA
			if ($params['campo'] == "cd_tabela" || $params['campo'] == "no_tabela") {

				// Instancia a classe model 
				$tabelaR = new TabelaRModel();

				// TERM é o parametro default que vem da function autocomplete via ajax
				// CAMPO é o indici do array
				$dados[$params['campo']] = "%".$params["term"]."%";

				// Define os parâmetros para a consulta e retorna o resultado da pesquisa
				$query  = $tabelaR->queryBuscaTabelas($dados);

				// Busca os dados baseado no filtro da query 			
				$retorno = $tabelaR->fetchAll($query);			

				// Se existir registros
				if(count($retorno) > 0) {
					// Verifica o campo
		           	foreach($retorno as $linha) {
		           		// O LABEL recebe o valor do CAMPO montado DINAMICAMENTE conforme variavel campo passada na requisição
		                $retornoJSON[] = array("label"     => ($linha->{strtoupper($params['campo'])}),
		                					   "codigo"    => ($linha->CD_TABELA),
		                					   "descricao" => ($linha->NO_TABELA)
		                                       );
		           		}
		        }
	           	
				// Retorna os dados por json
				$this->_helper->json(Marca_ConverteCharset::converter($retornoJSON), true);
				
				// Limpa os objetos da memoria
				unset($tabelaR);
			}
		
			// Bloco de código para controlar o auto-complete-x do SISTEMA
			if ($params['campo'] == "cd_sistema" || $params['campo'] == "no_sistema") {

				// Instancia a classe model 
				$sistemas = new SistemaModel();

				// TERM é o parametro default que vem da function autocomplete via ajax
				// CAMPO é o indici do array
				$dados[$params['campo']] = "%".$params["term"]."%";

				// Pega as cegonheiras sem chassis para o agendamento			
				$query = $sistemas->queryBuscaSistemas($dados);

				// Busca os dados baseado no filtro da query
				$retorno = $sistemas->fetchAll($query);

				// Se existir registros
				if(count($retorno) > 0) {
					// Verifica o campo					
	           		foreach($retorno as $linha) {
	           				// O LABEL recebe o valor do CAMPO montado DINAMICAMENTE conforme variavel campo passada na requisição    
	                    	$retornoJSON[] = array("label"     => strtoupper($linha->{strtoupper($params['campo'])}),
						                    	   "codigo"    => strtoupper($linha->CD_SISTEMA),
						                    	   "descricao" => strtoupper($linha->NO_SISTEMA)
	                                               );
	           		} 
	            }

				// Retorna os dados por json
				$this->_helper->json(Marca_ConverteCharset::converter($retornoJSON), true);

				// Limpa os objetos da memoria
				unset($sistemas);
			}
		}
	}
}