<?php
/**
 * Esta classe tem como objetivo manipular os dados da Aba de Colunas das Tabelas, na tela de cria��o de relat�rios.
 *
 * @author     Bruno Tel�
 * @category   Marca Sistemas
 * @copyright  Copyright (c) 1991-2012 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 */
class ConfiguracoesSistema_TabConsColunasController extends Marca_Controller_Abstract_Operacao {
    
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
        
        // Captura a sess�o
        $sessao = new Zend_Session_Namespace('portoweb');

        // Captura e grava o c�digo da transa��o da janela pai
        if (isset($params['pai_cd_consulta'])) {
            $sessao->pai_cd_consulta = $params['pai_cd_consulta'];
        }

        Zend_Registry::set("pai_cd_consulta", $sessao->pai_cd_consulta);

        // Joga para a view o c�digo da transa��o pai
        $this->view->pai_cd_consulta = $sessao->pai_cd_consulta;

    }
    
    /**
     * M�todo inicial para carregamento de classes do controlador
     *
     * @return void
     */
    public function init() {

        // Carrega o m�todo de inicializa��o da classe pai
        parent::init();

        // Define o layout a ser utilizado
        $this->_helper->layout->setLayout("layout-aba");

        // Carrega o modelo de dados
        Zend_Loader::loadClass('TabelaRModel');
		Zend_Loader::loadClass('ColunaModel');
        Zend_Loader::loadClass('ConsultaTabelaModel');
        Zend_Loader::loadClass('ConsultaLigacaoTabelaModel');
        Zend_Loader::loadClass('ConsultaColunaTabelaModel');
        Zend_Loader::loadClass('ConsultaTipoFiltroModel');
        Zend_Loader::loadClass('ConsultaTipoTotalModel');
		
    }


    /**
     * M�todo principal da classe
     *
     * @return void
     */
    public function indexAction() {

        // Recupera a inst�ncia da base de dados
        $db = Zend_Registry::get("db");

        // Captura os parametros passados por GET
        $params = $this->getRequest()->getParams();
        
        // Captura a sess�o
        $sessao = new Zend_Session_Namespace('portoweb');

        // Instancia a classe de sistemas web
        $colTabelas = new ConsultaColunaTabelaModel();
        $tpTotal    = new ConsultaTipoTotalModel();
        
        // Captura o c�digo da transa��o pai
        $pai_cd_consulta = Zend_Registry::get("pai_cd_consulta");
        
        // Busca todas as tabelas existentes 
        $dados = array("cd_consulta" => $pai_cd_consulta);
        $select = $colTabelas->queryBuscaColunasTabelasConsulta($dados);
        
        // Define os par�metros para a consulta e retorna o resultado da pesquisa
        $resConsulta = $colTabelas->fetchAll($select);
                
        // Joga para a os dados da consulta
        $this->view->colTabelas = $resConsulta;
        
        // Busca os totalizadores das colunas
        $selTotais = $tpTotal->queryBuscaTotaisConsulta();
        
        // Executa a consulta
        $resConTotal = $tpTotal->fetchAll($selTotais);
        
        // Joga para a os dados da consulta
        $this->view->totais = $resConTotal;
    }


    /**
     * Gera um documento para cadastro de um novo aditivo
     *
     * @return void
     */
    public function novoAction(){

        

    }

    
    /**
     * Salva uma a��o a transa��o
     *
     * @return void
     */
    public function salvarAction() {

        // Recupera os parametros da requisi��o
        $params = $this->_request->getParams();
        //Zend_Debug::dump($params); die();
        
        // Instancia o modelo
        $consColTabela = new ConsultaColunaTabelaModel();
		
        // Captura o c�digo da transa��o pai
        $pai_cd_consulta = Zend_Registry::get("pai_cd_consulta");
        
        // Exclui as colunas existentes para inser�-las novamente
        //$consColTabela->delete("CD_CONSULTA = " . $pai_cd_consulta);
        
        // Busca o total de colunas passadas
		$totalColunas = count($params["cd_col_tab"]);
        
        for($i=0; $i < $totalColunas; $i++) {
                        
            // Insere as colunas
            $dados = array("CD_CONSULTA"    => $pai_cd_consulta,
                           "SG_TABELA"      => $params["sg_tabela"][$i],
                           "CD_COLUNA"      => $params["cd_coluna"][$i],
                           "DS_CABECALHO"   => $params["ds_cabecalho"][$i],
                           "TP_FORMATO"     => $params["tp_formato"][$i],
                           "TAM_COLUNA"     => $params["tam_coluna"][$i],
                           "ORD_COLUNA"     => ($i + 1),
                           "CD_TP_TOTAL"    => $params["cd_tp_total"][$i] == "" ? "0" : $params["cd_tp_total"][$i],
                           "SENTIDO_COLUNA" => $params["sentido_coluna"][$i],
                           "DISTINTO"       => $params["distinto"][$i]);
            
            // Insere ou edita
            if($params["cd_col_tab"][$i] == "") {
                $dados["CD_COL_TAB"] = $consColTabela->nextVal();
                $consColTabela->insert($dados);
                
            } else {
                $cd_col_tab = $params["cd_col_tab"][$i];
                $consColTabela->update($dados, "CD_COL_TAB = " . $cd_col_tab);
            }
            
        }
			
        // Limpa os dados da requisi��o
        $params = $this->_helper->LimpaParametrosRequisicao->limpar();

        // Redireciona para a��o novo
        $this->_forward('index');
        
    }


    /**
     * Exclui um aditivo selecionado de um contrato
     *
     * @return void
     */
    public function excluirAction(){
        
        // Recupera os parametros da requisi��o
        $params = $this->_request->getParams();
        //Zend_Debug::dump($params); die();
        
        // Instancia o modelo
        $consColTabela = new ConsultaColunaTabelaModel();
        
        // Captura o c�digo da transa��o pai
        $pai_cd_consulta = Zend_Registry::get("pai_cd_consulta");
        
        if($params["cd_col_tab"] != "") {
            if($consColTabela->delete("CD_COL_TAB = '" . $params["cd_col_tab"] . "' AND CD_CONSULTA = " . $pai_cd_consulta)) {
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
     * Gera o relat�rio de aditivos a partir de uma listagem
     *
     * @return void
     */
    public function relatorioAction() { }
    
     /**
     * Retorna as tabelas da consulta
     *
     * @return void
     */
	public function retornaColunasTabelasConsultaXAction(){
	
		// Verifica se a requisi��o foi passada por Ajax
		if($this->_request->isXmlHttpRequest()) {

			// Captura o c�digo da transa��o pai
			$pai_cd_consulta = Zend_Registry::get("pai_cd_consulta");
		
			// Captura os parametros passados por GET
			$params = $this->getRequest()->getParams();

			// Instancia o modelo
			$consColTabelas = new ConsultaColunaTabelaModel();

			try {

				$retornoJSON = array();
				
                // Busca as tabelas
                $dados = array("cd_consulta" => $pai_cd_consulta);
                $selTabelas = $consColTabelas->queryBuscaColunasSelecionadasConsulta($dados);
                
				// Define os par�metros para a consulta e retorna o resultado da pesquisa
                $resConsultaPos = $consColTabelas->fetchAll($selTabelas);
                
                if(count($resConsultaPos) > 0) {
                    
                    foreach($resConsultaPos as $linha) {
                        $retornoJSON[] = array(	
                                            "cd_col_tab"     => $linha->CD_COL_TAB,
                                            "cd_consulta"    => $linha->CD_CONSULTA,
                                            "cd_tabela"      => $linha->CD_TABELA,
                                            "sg_tabela"      => trim($linha->SG_TABELA),
                                            "no_tabela"      => trim($linha->NO_TABELA),
                                            "cd_coluna"      => $linha->CD_COLUNA,
                                            "no_coluna"      => $linha->NO_COLUNA,
                                            "ds_cabecalho"   => trim($linha->DS_CABECALHO),
                                            "tp_formato"     => $linha->TP_FORMATO,
                                            "tam_coluna"     => $linha->TAM_COLUNA,
                                            "ord_coluna"     => $linha->ORD_COLUNA,
                                            "cd_tp_total"    => $linha->CD_TP_TOTAL,
                                            "sentido_coluna" => trim($linha->SENTIDO_COLUNA),
                                            "distinto"       => $linha->DISTINTO
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