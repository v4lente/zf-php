<?php
/**
 * Esta classe tem como objetivo colocar condições nas Colunas (where), na tela de criação de relatórios.
 *
 * @author     Bruno Teló
 * @category   Marca Sistemas
 * @copyright  Copyright (c) 1991-2012 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 */
class ConfiguracoesSistema_TabConsCondColunasController extends Marca_Controller_Abstract_Operacao {
    
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
        Zend_Loader::loadClass('ConsultaTipoFiltroModel');
        Zend_Loader::loadClass('ConsultaTipoTotalModel');
		
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
        $condColunas = new ConsultaCondicaoColunaModel();
        $tpFiltro    = new ConsultaTipoFiltroModel();
        
        // Captura o código da transação pai
        $pai_cd_consulta = Zend_Registry::get("pai_cd_consulta");
        
        // Busca todas as tabelas existentes 
        $dados = array("cd_consulta" => $pai_cd_consulta);
        $select = $condColunas->queryBuscaColunasTabelasConsulta($dados);
        
        // Define os parâmetros para a consulta e retorna o resultado da pesquisa
        $resConsulta = $condColunas->fetchAll($select);
                
        // Joga para a os dados da consulta
        $this->view->colTabelas = $resConsulta;
        
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
        $consCondColuna = new ConsultaCondicaoColunaModel();
		
        // Captura o código da transação pai
        $pai_cd_consulta = Zend_Registry::get("pai_cd_consulta");
        
        // Exclui as condições das colunas existentes para inserí-las novamente
        $consCondColuna->delete("CD_CONSULTA = " . $pai_cd_consulta);
        
        // Busca o total de condições passadas
		$totalCondicoes = count($params["cd_cond_col"]);
        
        for($i=0; $i < $totalCondicoes; $i++) {
            
            // Insere as colunas
            $dados = array("CD_COND_COL"    => $consCondColuna->nextVal(),
                           "CD_CONSULTA"    => $pai_cd_consulta,
                           "SG_TABELA"      => $params["sg_tabela"][$i],
                           "CD_COLUNA"      => $params["cd_coluna"][$i],
                           "DS_COMPLEMENTO" => $params["ds_complemento"][$i],
                           "ABRE_PAR"       => $params["abre_par"][$i],
                           "FECHA_PAR"      => $params["fecha_par"][$i],
                           "TP_LIGACAO"     => $params["tp_ligacao"][$i],
                           "LIG_INT_PAR"    => trim($params["lig_int_par"][$i]),
                           "LIG_EXT_PAR"    => trim($params["lig_ext_par"][$i]),
                           "ORD_COND"       => ($i + 1));
            
            // Insere ou edita
            $consCondColuna->insert($dados);
            
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
        $consCondColuna = new ConsultaCondicaoColunaModel();
        
        // Captura o código da transação pai
        $pai_cd_consulta = Zend_Registry::get("pai_cd_consulta");
        
        if($params["cd_cond_col"] != "") {
            if($consCondColuna->delete("CD_COND_COL = '" . $params["cd_cond_col"])) {
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
     * Retorna as condições das colunas
     *
     * @return void
     */
	public function retornaCondicoesColunasConsultaXAction(){
	
		// Verifica se a requisição foi passada por Ajax
		if($this->_request->isXmlHttpRequest()) {

			// Captura o código da transação pai
			$pai_cd_consulta = Zend_Registry::get("pai_cd_consulta");
		
			// Captura os parametros passados por GET
			$params = $this->getRequest()->getParams();

			// Instancia o modelo
			$consCondColunas = new ConsultaCondicaoColunaModel();

			try {

				$retornoJSON = array();
				
                // Busca as tabelas
                $dados = array("cd_consulta" => $pai_cd_consulta);
                $selCondicoes = $consCondColunas->queryBuscaCondicoesColunasSelecionadasConsulta($dados);
                
				// Define os parâmetros para a consulta e retorna o resultado da pesquisa
                $resConsultaPos = $consCondColunas->fetchAll($selCondicoes);
                
                if(count($resConsultaPos) > 0) {
                    
                    foreach($resConsultaPos as $linha) {
                        $retornoJSON[] = array(	
                                            "cd_cond_col"    => $linha->CD_COND_COL,
                                            "cd_consulta"    => $linha->CD_CONSULTA,
                                            "cd_tabela"      => $linha->CD_TABELA,
                                            "sg_tabela"      => trim($linha->SG_TABELA),
                                            "no_tabela"      => trim($linha->NO_TABELA),
                                            "cd_coluna"      => $linha->CD_COLUNA,
                                            "no_coluna"      => $linha->NO_COLUNA,
                                            "ds_complemento" => $linha->DS_COMPLEMENTO,
                                            "abre_par"       => $linha->ABRE_PAR,
                                            "fecha_par"      => $linha->FECHA_PAR,
                                            "tp_ligacao"     => $linha->TP_LIGACAO,
                                            "lig_int_par"    => trim($linha->LIG_INT_PAR),
                                            "lig_ext_par"    => trim($linha->LIG_EXT_PAR),
                                            "ord_cond"       => $linha->ORD_COND
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