<?php
/**
 * Esta classe tem como objetivo manipular os dados da Aba de Tabelas, da tela de criação de relatórios.
 *
 * @author     Bruno Teló
 * @category   Marca Sistemas
 * @copyright  Copyright (c) 1991-2012 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 */
class ConfiguracoesSistema_TabConsTabelasController extends Marca_Controller_Abstract_Operacao {
    
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
        Zend_Loader::loadClass('ConsultaColunaTabelaModel');
        Zend_Loader::loadClass('ConsultaCondicaoColunaModel');

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
        $tabelas = new TabelaRModel();
        
        // Captura o código da transação pai
        $pai_cd_consulta = Zend_Registry::get("pai_cd_consulta");
        
        // Busca todas as tabelas existentes 
        $select = $tabelas->select()
                          ->setIntegrityCheck(false)
                          ->from(array("TB" => "TABELA_R"), array("TB.CD_TABELA", 
															      "TB.NO_TABELA",
																  "TB.SG_TABELA"));

        // Define os parâmetros para a consulta e retorna o resultado da pesquisa
        $resConsulta = $tabelas->fetchAll($select);
                
        // Joga para a os dados da consulta
        $this->view->tabelas = $resConsulta;
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
        $consTabela = new ConsultaTabelaModel();
		
        // Captura o código da transação pai
        $pai_cd_consulta = Zend_Registry::get("pai_cd_consulta");
        
        // Busca as tabelas já inseridas
        $selTabelas = $consTabela->select()
                                 ->setIntegrityCheck(false)
                                 ->from(array("CT" => "CONSULTA_TABELA"), array("CT.CD_TABELA, CT.SG_TABELA"))
                                 ->where("CT.CD_CONSULTA = " . $pai_cd_consulta);
        
        $resTabelas = $consTabela->fetchAll($selTabelas);
        
        $sgTabelasBanco = array();
        
        foreach($resTabelas as $tabela) {
            $sgTabelasBanco[] = trim($tabela->SG_TABELA);
        }
        
        // Busca o total de tabelas passadas
		$totalTabelas = count($params["tabelas"]);
        
        try {
        
            for($i=0; $i < $totalTabelas; $i++) {

                // Se a sigla não existir para a consulta insere a nova tabela, caso contrário edita a mesma
                if(! in_array(trim($params["sg_tabelas"][$i]), $sgTabelasBanco)) {

                    $dados = array("CD_CONSULTA" => $pai_cd_consulta,
                                   "CD_TABELA"   => $params["tabelas"][$i],
                                   "SG_TABELA"   => $params["sg_tabelas"][$i]);

                    $consTabela->insert($dados);

                }

            }
            
            $this->view->atualizaTelaPrincipal = "1";
            
        } catch(Zend_Exception $e) {
            echo "Erro: " . $e->getMessage();
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
        $consTabela = new ConsultaTabelaModel();
        $consLiga   = new ConsultaLigacaoTabelaModel();
        $consCol    = new ConsultaColunaTabelaModel();
        $consCond   = new ConsultaCondicaoColunaModel();
        
        // Captura o código da transação pai
        $pai_cd_consulta = Zend_Registry::get("pai_cd_consulta");
        
        if($params["cd_tabela"] != "" && $params["sg_tabela"] != "") {
            
            $params["cd_tabela"] = trim($params["cd_tabela"]);
            $params["sg_tabela"] = trim($params["sg_tabela"]);
                        
            $resTab = $consTabela->fetchRow($select);
            
            try {
                $consLiga->setShowMessage(false);
                $consLiga->delete("(LTRIM(RTRIM(SG_TABELA_1)) = '" . $params["sg_tabela"] . "' OR LTRIM(RTRIM(SG_TABELA_2)) = '" . $params["sg_tabela"] . "') AND CD_CONSULTA = " . $pai_cd_consulta);
            } catch(Zend_Exception $e) {}
            
            try {
                $consCol->setShowMessage(false);
                $consCol->delete ("LTRIM(RTRIM(SG_TABELA)) = '" . $params["sg_tabela"] . "' AND CD_CONSULTA = " . $pai_cd_consulta);
            } catch(Zend_Exception $e) {}
            
            try {
                $consCond->setShowMessage(false);
                $consCond->delete("LTRIM(RTRIM(SG_TABELA)) = '" . $params["sg_tabela"] . "' AND CD_CONSULTA = " . $pai_cd_consulta);
            } catch(Zend_Exception $e) {}
            
            if($consTabela->delete("CD_TABELA = '" . $params["cd_tabela"] . "' AND LTRIM(RTRIM(SG_TABELA)) = '" . $params["sg_tabela"] . "' AND CD_CONSULTA = " . $pai_cd_consulta)) {
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
     * Retorna as tabelas da consulta
     *
     * @return void
     */
	public function retornaTabelasConsultaXAction(){
	
		// Verifica se a requisição foi passada por Ajax
		if($this->_request->isXmlHttpRequest()) {

			// Captura o código da transação pai
			$pai_cd_consulta = Zend_Registry::get("pai_cd_consulta");
		
			// Captura os parametros passados por GET
			$params = $this->getRequest()->getParams();

			// Associa as variáveis do banco
			$db = Zend_Registry::get('db');

			// Instancia o modelo
			$consTabelas = new ConsultaTabelaModel();

			try {

				$retornoJSON = array();
				
                // Busca as tabelas
                $dados = array("cd_consulta" => $params["cd_consulta"]);
                $selTabelas = $consTabelas->queryBuscaTabelasConsulta($dados);
                
				// Define os parâmetros para a consulta e retorna o resultado da pesquisa
				$resConsultaPos = $consTabelas->fetchAll($selTabelas);

                foreach($resConsultaPos as $linha) {
                    $retornoJSON[] = array(	
                                        "cd_consulta" => $linha->CD_CONSULTA,
                                        "cd_tabela"	  => $linha->CD_TABELA,
                                        "no_tabela"   => $linha->NO_TABELA,
                                        "sg_tabela"   => $linha->SG_TABELA
                                    );
                }

				$this->_helper->json(Marca_ConverteCharset::converter($retornoJSON), true);

			} catch(Exception $e) {
				//echo $e->getMessage(); die;
				echo false;
			}

		}
	}


}