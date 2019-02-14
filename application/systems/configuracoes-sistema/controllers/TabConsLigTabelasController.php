<?php
/**
 * Esta classe tem como objetivo manipular os dados da Aba de Ligações entre Tabelas, da tela de criação de relatórios.
 *
 * @author     Bruno Teló
 * @category   Marca Sistemas
 * @copyright  Copyright (c) 1991-2012 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 */
class ConfiguracoesSistema_TabConsLigTabelasController extends Marca_Controller_Abstract_Operacao {
    
    /**
     * 
     * Construtor da classe
     * 
     * @return void
     */
    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array()) {
        
        // Chama o construtor pai
        parent::__construct($request, $response, $invokeArgs);
        
        // Captura os parametros passados por GET
        $params = $this->getRequest()->getParams();
        
        // Captura a sessão
        $sessao = new Zend_Session_Namespace('portoweb');

        // Captura e grava o código da transação da janela pai
        if (isset($params['pai_cd_consulta'])) {
            $sessao->pai_cd_consulta = $params['pai_cd_consulta'];
        }

        Zend_Registry::set("pai_cd_consulta", $sessao->pai_cd_consulta);

        // Joga para a view o código da transação pai
        $this->view->pai_cd_consulta = $sessao->pai_cd_consulta;

    }
    
    /**
     * Método inicial para carregamento de classes do controlador
     *
     * @return void
     */
    public function init() {

        // Carrega o método de inicialização da classe pai
        parent::init();

        // Define o layout a ser utilizado
        $this->_helper->layout->setLayout("layout-aba");

        // Carrega o modelo de dados
        Zend_Loader::loadClass('TabelaRModel');
		Zend_Loader::loadClass('ColunaModel');
        Zend_Loader::loadClass('ConsultaTabelaModel');
        Zend_Loader::loadClass('ConsultaLigacaoTabelaModel');
        Zend_Loader::loadClass('ConsultaTipoFiltroModel');
		
    }


    /**
     * Método principal da classe
     *
     * @return void
     */
    public function indexAction() {

        // Recupera a instância da base de dados
        $db = Zend_Registry::get("db");

        // Captura os parametros passados por GET
        $params = $this->getRequest()->getParams();
        
        // Captura a sessão
        $sessao = new Zend_Session_Namespace('portoweb');

        // Instancia a classe de sistemas web
        $ligTabelas = new ConsultaLigacaoTabelaModel();
        $tpFiltro   = new ConsultaTipoFiltroModel();
        
        // Captura o código da transação pai
        $pai_cd_consulta = Zend_Registry::get("pai_cd_consulta");
        
        // Busca todas as tabelas existentes 
        $dados = array("cd_consulta" => $pai_cd_consulta);
        $select = $ligTabelas->queryBuscaColunasTabelasConsulta($dados);
        
        // Define os parâmetros para a consulta e retorna o resultado da pesquisa
        $resConsulta = $ligTabelas->fetchAll($select);
                
        // Joga para a os dados da consulta
        $this->view->ligTabelas = $resConsulta;
        
        // Busca os filtros para ligação entre as colunas
        $selFiltros = $tpFiltro->queryBuscaFiltrosConsulta();
        
        // Executa a consulta
        $resConFiltro = $tpFiltro->fetchAll($selFiltros);
        
        // Joga para a os dados da consulta
        $this->view->filtros = $resConFiltro;
    }


    /**
     * Gera um documento para cadastro de um novo aditivo
     *
     * @return void
     */
    public function novoAction(){

        

    }

    
    /**
     * Salva uma ação a transação
     *
     * @return void
     */
    public function salvarAction() {

        // Recupera os parametros da requisição
        $params = $this->_request->getParams();
        //Zend_Debug::dump($params); die();
        
        // Instancia o modelo
        $consLigTabela = new ConsultaLigacaoTabelaModel();
		
        // Captura o código da transação pai
        $pai_cd_consulta = Zend_Registry::get("pai_cd_consulta");
        
        // Exclui as ligações existentes para inserí-las novamente
        $consLigTabela->delete("CD_CONSULTA = " . $pai_cd_consulta);
        
        // Busca o total de ligações passadas
		$totalLigacoes = count($params["cd_ligacao"]);
        
        for($i=0; $i < $totalLigacoes; $i++) {
            
            // Insere as ligações
            $dados = array("CD_LIGACAO"  => $consLigTabela->nextVal(),
                           "CD_CONSULTA" => $pai_cd_consulta,
                           "SG_TABELA_1" => $params["sg_tabela_1"][$i],
                           "CD_COLUNA_1" => $params["cd_coluna_1"][$i],
                           "SG_TABELA_2" => $params["sg_tabela_2"][$i],
                           "CD_COLUNA_2" => $params["cd_coluna_2"][$i],
                           "TP_LIGACAO"  => $params["tp_ligacao"][$i],
                           "OUTER_JOIN"  => $params["outer_join"][$i]);
            
            $consLigTabela->insert($dados);
            
        }
			
        // Limpa os dados da requisição
        $params = $this->_helper->LimpaParametrosRequisicao->limpar();

        // Redireciona para ação novo
        $this->_forward('index');

		 

    }


    /**
     * Exclui um aditivo selecionado de um contrato
     *
     * @return void
     */
    public function excluirAction(){
        
        // Recupera os parametros da requisição
        $params = $this->_request->getParams();
        //Zend_Debug::dump($params); die();
        
        // Instancia o modelo
        $consLigTabela = new ConsultaLigacaoTabelaModel();
        
        // Captura o código da transação pai
        $pai_cd_consulta = Zend_Registry::get("pai_cd_consulta");
        
        if($params["cd_ligacao"] != "") {
            if($consLigTabela->delete("CD_LIGACAO = '" . $params["cd_ligacao"] . "' AND CD_CONSULTA = " . $pai_cd_consulta)) {
                $this->_helper->json('1', true);
            } else {
                $this->_helper->json('0', true);
            }
            
        } else {
            $this->_helper->json('0', true);
        }
        
    }


    /**
     * Seleciona um aditivo referente a um contrato
     * @return void
     */
    public function selecionarAction() {

        

    }


    /**
     * Pesquisa os aditivos
     *
     * @return void
     */
    public function pesquisarAction() { }
    
    
    /**
     * Gera o relatório de aditivos a partir de uma listagem
     *
     * @return void
     */
    public function relatorioAction() { }
    
    /**
     * Retorna as ligações das tabelas já feitas
     *
     * @return void
     */
	public function retornaLigacoesTabelasConsultaXAction(){
	
		// Verifica se a requisição foi passada por Ajax
		if($this->_request->isXmlHttpRequest()) {

			// Captura o código da transação pai
			$pai_cd_consulta = Zend_Registry::get("pai_cd_consulta");
		
			// Captura os parametros passados por GET
			$params = $this->getRequest()->getParams();

			// Associa as variáveis do banco
			$db = Zend_Registry::get('db');

			// Instancia o modelo
			$consLigTabelas = new ConsultaLigacaoTabelaModel();

			try {

				$retornoJSON = array();
				
                // Busca as tabelas
                $dados = array("cd_consulta" => $pai_cd_consulta);
                $selTabelas = $consLigTabelas->queryBuscaLigacoesTabelasConsulta($dados);
                
				// Define os parâmetros para a consulta e retorna o resultado da pesquisa
                $resConsultaPos = $consLigTabelas->fetchAll($selTabelas);
                
                if(count($resConsultaPos) > 0) {
                    
                    foreach($resConsultaPos as $linha) {
                        $retornoJSON[] = array(	
                                            "cd_consulta" => $linha->CD_CONSULTA,
                                            "cd_ligacao"  => $linha->CD_LIGACAO,
                                            "sg_tabela_1" => trim($linha->SG_TABELA_1),
                                            "cd_coluna_1" => $linha->CD_COLUNA_1,
                                            "no_coluna_1" => $linha->NO_COLUNA_1,
                                            "sg_tabela_2" => trim($linha->SG_TABELA_2),
                                            "cd_coluna_2" => $linha->CD_COLUNA_2,
                                            "no_coluna_2" => $linha->NO_COLUNA_2,
                                            "tp_ligacao"  => $linha->TP_LIGACAO,
                                            "outer_join"  => $linha->OUTER_JOIN
                                        );
                    }
                    
                }
                
				$this->_helper->json(Marca_ConverteCharset::converter($retornoJSON), true);

			} catch(Exception $e) {
				//echo $e->getMessage(); die;
				echo false;
			}

		}
	}
    
    /**
     * Busca as ligações entre as tabelas selecionadas
     *
     * @return void
     */
	public function buscaLigacoesAutomaticasXAction(){
	
		// Verifica se a requisição foi passada por Ajax
		if($this->_request->isXmlHttpRequest()) {

			// Captura o código da transação pai
			$pai_cd_consulta = Zend_Registry::get("pai_cd_consulta");
		
			// Captura os parametros passados por GET
			$params = $this->getRequest()->getParams();

			// Associa as variáveis do banco
			$db = Zend_Registry::get('db');

			// Instancia o modelo
			$consLigTabelas = new ConsultaLigacaoTabelaModel();

			try {

				$retornoJSON = array();
				
                // Busca as tabelas
                $dados = array("cd_consulta" => $pai_cd_consulta);
                $selTabelas = $consLigTabelas->queryBuscaLigacoesAutomaticasConsulta($dados);
                
				// Define os parâmetros para a consulta e retorna o resultado da pesquisa
                $resConsultaPos = $consLigTabelas->fetchAll($selTabelas);
                
                if(count($resConsultaPos) > 0) {
                    
                    foreach($resConsultaPos as $linha) {
                        $retornoJSON[] = array(	
                                            "cd_consulta" => $pai_cd_consulta,
                                            "sg_tabela_1" => trim($linha->SG_TABELA_1),
                                            "cd_coluna_1" => $linha->CD_COLUNA_1,
                                            "sg_tabela_2" => trim($linha->SG_TABELA_2),
                                            "cd_coluna_2" => $linha->CD_COLUNA_2
                                        );
                    }
                    
                }
                
				$this->_helper->json(Marca_ConverteCharset::converter($retornoJSON), true);

			} catch(Exception $e) {
				//echo $e->getMessage(); die;
				echo false;
			}

		}
	}

}