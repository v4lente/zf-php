<?php
/**
 *
 * Classe respons�vel por cadastrar as tabelas dos sistemas (Dicion�rio de dados)
 *
 * @author     David Valente
 * @category   Marca Sistemas
 * @copyright  Copyright (c) 1991-2011 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 */
class ConfiguracoesSistema_TabelaController extends Marca_Controller_Abstract_Operacao {

	/**
	 * (non-PHPdoc)
	 * @see library/Marca/Controller/Abstract/Marca_Controller_Abstract_Operacao#init()
	 */
	public function init() {
		 
		parent::init();

		// Carrega os modelos de dados
		Zend_Loader::loadClass("TabelaRModel");
		
	}

	/**
	 * Metodo index
	 * objetivo: M�todo principal da classe
	 */
	public function indexAction() {
				
		// Recupera os parametros da requisi��o
		$params = $this->_request->getParams();
		
		// Instancia a classe de sistemas web
		$tabelaR   = new TabelaRModel();
		
		// Captura a sess�o
		$sessao    = new Zend_Session_Namespace('portoweb');
											
		// Define os par�metros para a consulta e retorna o resultado da pesquisa
		$query     = $tabelaR->queryBuscaTabelas($params)->orderByList();
		
		// Recebe a inst�ncia do paginator por singleton
		$paginator = Zend_Paginator::factory($query);
		
		// Seta o n�mero da p�gina corrente
		$pagina    = $this->_getParam('pagina', 1);
		
		// Define a p�gina corrente
		$paginator->setCurrentPageNumber($pagina);

		// Define o total de linhas por p�gina
		$paginator->setItemCountPerPage($sessao->perfil->QT_LINHAS);
		
		// Joga para a view a pagina��o
		$this->view->paginator = $paginator;
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
	public function salvarAction() {

		// Recupera os parametros da requisi��o
		$params = $this->_request->getParams();

		// Captura a sess�o
		$sessao = new Zend_Session_Namespace('portoweb');
		
		// Instancia a classe de sistemas web
		$tabelaR = new TabelaRModel();

		// Monta os dados que ser�o salvos
		$dados = array("CD_TABELA"          => $params["cd_tabela"],
                       "SG_TABELA"          => $params["sg_tabela"],				       
        			   "NO_TABELA"          => $params["no_tabela"],
        			   "DS_TABELA"          => $params["ds_tabela"],
					   "FL_COMPLETA"        => $params["fl_completa"],
					   "FL_REDUZIDA"        => $params["fl_reduzida"],
					   "FL_RELATORIO"       => $params["fl_relatorio"],
					   "FL_REDUZIDA"        => $params["fl_reduzida"],
					   "FL_CONS_NAO_ESTRUT" => $params["fl_cons_nao_estrut"]);
		
		// Verifica as regras do modelo de dados
		if($tabelaR->isValid($dados)) {
			
			// Se o registro n�o existir insere(Novo), caso contr�rio edita
			if($params["operacao"] == "novo") {

				// Insere o novo aditivo
				$tabelaR->insert($dados);

			} else {
			 
				// Monta a condi��o do where
				$where = "CD_TABELA  = '{$params["cd_tabela"]}'";
				 
				// Atualiza os dados
				$tabelaR->update($dados, $where);

			}
			
			// Redireciona para a��o novo
			$this->_forward('selecionar');
		}
	}

	
	/**
	 * Metodo excluir
	 * objetivo: utilizado para a opera��o de DELETE
	 */
	public function excluirAction() {
		
		// Recupera os parametros da requisi��o
		$params = $this->_request->getParams();

		// Instancia a classe de sistemas web
		$tabelaR = new TabelaRModel();

		// Define os filtros para a exclus�o
		$where = $tabelaR->addWhere(array("CD_TABELA = ?" => $params['cd_tabela']))
						 ->getWhere();

		// Exclui o sistema
		$delete = $tabelaR->delete($where);

		// Verifica se o registro foi exclu�do
		if($delete) {

			// Limpa os dados da requisi��o
			$params = $this->_helper->LimpaParametrosRequisicao->limpar();

			// Redireciona para o index
			$this->_forward("index", null, null, $params);

		} else {
			// Se n�o conseguir excluir, retorna pra sele��o do registro
			$this->_forward("selecionar");
		}
	}
	

	/**
	 * Metodo pesquisar
	 * objetivo: utilizado para executar pesquisas
	 */
	public function pesquisarAction() {
		 
		// Recupera os parametros da requisi��o
		$params  = $this->_request->getParams();
		
		// Captura a sess�o
		$sessao  = new Zend_Session_Namespace('portoweb');
		
		// Instancia a classe de sistemas web
		$tabelaR = new TabelaRModel();
		
		// Joga os par�metros para outra vari�vel para serem tratados
		$params2 = $params;
		
		// Trata a busca para desconsiderar a busca em branco ao qual vem o valor 2 do combo
		$params2["fl_relatorio"] 	   = trim($params["fl_relatorio"])       == "2" ? "" : $params["fl_relatorio"];
		$params2["fl_cons_nao_estrut"] = trim($params["fl_cons_nao_estrut"]) == "2" ? "" : $params["fl_cons_nao_estrut"];
		
		// Define os par�metros para a consulta e retorna o resultado da pesquisa
		$query     = $tabelaR->queryBuscaTabelas($params2)->orderByList();
		
		// Recebe a inst�ncia do paginator por singleton
		$paginator = Zend_Paginator::factory($query);
		
		// Seta o n�mero da p�gina corrente
		$pagina    = $this->_getParam('pagina', 1);
		
		// Define a p�gina corrente
		$paginator->setCurrentPageNumber($pagina);

		// Define o total de linhas por p�gina
		$paginator->setItemCountPerPage($sessao->perfil->QT_LINHAS);

		// Reenvia os valores para o formul�rio
		$this->_helper->RePopulaFormulario->repopular($params, 'lower');
		
		// Joga para a view a pagina��o
		$this->view->paginator = $paginator;	
	}

	/**
	 * Metodo selecionar
	 * objetivo: utilizado para selecionar um registro a partir de uma listagem
	 */
	public function selecionarAction() {
		 
		// Recupera os parametros da requisi��o
		$params = $this->_request->getParams();

		// Instancia a classe de sistemas web
		$tabelaR = new TabelaRModel();

		// Define os filtros para a cosulta
		$where = $tabelaR->addWhere(array("CD_TABELA = ?" => $params['cd_tabela']))->getWhere();

		// Recupera o sistema selecionado
		$sistema  = $tabelaR->fetchRow($where);
		
		// Define os dados em tela
		$this->defineValoresPadrao($sistema->toArray());		
	}

	/**
	 * Metodo relatorio
	 * objetivo: utilizado para gerar um relatorio a partir de uma listagem
	 */
	public function relatorioAction() {
        
        // Captura os parametros passados por GET
    	$params = $this->getRequest()->getParams();

    	// Associa as vari�veis do banco
    	$db = Zend_Registry::get('db');
    	 
    	// Carrega a sess�o do usu�rio
    	$sessao = new Zend_Session_Namespace('portoweb');    	
    	
    	// Instancia a classe
    	$usuario = new UsuarioModel();
    	
    	// Captura a sess�o
    	$sessao = new Zend_Session_Namespace('portoweb');
        
        if($params['cd_tabela'] != ""){
			$clausulas = " AND upper(cd_tabela) like '%".strtoupper($params['cd_tabela'])."%' "; 	
        }
        if($params['no_tabela'] != ""){
			$clausulas = " AND upper(no_tabela) like '%upper(".strtoupper($params['no_tabela']).")%' "; 	
        }
				
                    
    	//Defini��o de parametros para o relat�rio
    	$params['usuario']   = "Usu�rio: " . $sessao->perfil->CD_USUARIO;
    	$params['sistema']   = 'Configura��es sistema';
    	$params['titulo']    = 'Dicion�rio de Dados - Tabelas';
    	$params['clausulas'] = $clausulas;

    	
        
    	 
    	// Helper para gerar o relat�rio no jasper. Nome do relat�rio, Par�metros da query, Formato de Impress�o
    	$this->_helper->GeraRelatorioJasper->gerar("tabela", $params, $params['_rel_metodo']); 

    	
    	// Limpa os objetos da memoria
    	unset($usuario);    
    
        /*
         * Gerar pdf antigo com fpdf

		// Chama o m�todo relat�rio da classe pai
        parent::relatorio();

		// Captura os parametros passados por GET
		$params = $this->getRequest()->getParams();

		// inicializa a variavel como um array
		$resConsulta = array();

		// Instancia a classe de sistemas web
		$tabelaR = new TabelaRModel();

		// Monta a condi��o de filtro
		$where   = $tabelaR->addWhere(array("CD_TABELA  = ?"   => $params['cd_tabela']))
				           ->addWhere(array("NO_TABELA LIKE ?" => $params['no_tabela']))
				 	       ->getWhere();
		
		// Ordena��o da consulta
		$order       = $this->_helper->OrdenaConsulta->ordenar();
											
		// Define os par�metros para a consulta e retorna o resultado da pesquisa
		$resConsulta = $tabelaR->fetchAll($where, $order);

		// Joga para a view o resultado da consulta
		$this->view->resConsulta = $resConsulta;
		*/
	}


	/**
	 * Busca os dados via ajax
	 *
	 * @return JSON
	 */
	public function retornaDadosXAction() {

		// Verifica se arrequisi��o foi passada por Ajax
		if($this->_request->isXmlHttpRequest()) {
			 
			// Captura os parametros passados por GET
			$params = $this->getRequest()->getParams();

			// Instancia a classe model
			$tabelaR = new TabelaRModel();
						
			$where = "CD_TABELA = '{$params['cd_tabela']}'";
			
			// Pega os tipos de opera��es da pesagem
			$retorno = $tabelaR->fetchAll($where);
			
			if (count($retorno) > 0) {			
				$resultado['registro'] = $retorno->toArray();
			}
			
			// Retorna os dados por json
			$this->_helper->json(Marca_ConverteCharset::converter($resultado), true);

			// Limpa os objetos da memoria
			unset($tabelaR);
		}

	}
	
	/**
	 * Define alguns valores padr�o que ser�o carregados e
	 * mostrados em mais de uma p�gina
	 *
	 * @param array $params Parametros da requisi��o
	 */
	private function defineValoresPadrao($params = array()) {
				
		// Reenvia os valores para o formul�rio
		$this->_helper->RePopulaFormulario->repopular($params, 'lower');

	}
	
}