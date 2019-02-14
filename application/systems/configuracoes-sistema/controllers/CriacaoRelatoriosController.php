<?php
/**
 *
 * Classe responsáel por criar relatórios dinâmicos
 *
 *
 * @author     Bruno Teló
 * @category   Marca Sistemas
 * @copyright  Copyright (c) 1991-2012 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 */
class ConfiguracoesSistema_CriacaoRelatoriosController extends Marca_Controller_Abstract_Operacao {

    /**
     * (non-PHPdoc)
     * @see library/Marca/Controller/Abstract/Marca_Controller_Abstract_Operacao#init()
     */
    public function init() {
        parent::init();

        // Carrega os modelos de dados
        Zend_Loader::loadClass("ConsultaModel");
		Zend_Loader::loadClass('ConsultaColunaModel');
		Zend_Loader::loadClass('ConsultaColunaFiltroModel');
        Zend_Loader::loadClass('ConsultaConexaoTabelaModel');
        Zend_Loader::loadClass("WebGrupoTabelaConsultaModel");
        
		// Captura a sessão
        $sessao = new Zend_Session_Namespace('portoweb');

        // Carrega a classe de tradução
        Zend_Loader::loadClass("Marca_Controller_Action_Helper_Traduz");

        // Instancia o objeto de tradução
        $traducao = new Marca_Controller_Action_Helper_Traduz();

        // Monta a tradução dos campos.
        // Nessessário para reduzir a quantidade de acessos ao banco,  
        // Diminuindo assim o tempo de carregamento da página.
        $this->view->traducao = $traducao->traduz(array("LB_SISTEMA",
                                                        "LB_MENU_ABA",
                                                        "LB_TRANSACAO",
                                                        "LB_AMB_DESENV",
                                                        "LB_IMPRESSAO",
                                                        "LB_DISPONIVEL","LB_ORD_EXIBICAO",$sessao->perfil->CD_IDIOMA));

    }
    
    /**
     * Metodo index
     * objetivo: Método principal da classe
     */
    public function indexAction() {
        // Redireciona para a action pesquisar
        $this->_forward("pesquisar");
    }
    
     /**
     * Metodo novo
     * objetivo: utilizado para formularios de cadastro
     */
    public function novoAction() { 

		// Captura a sessão
        $sessao = new Zend_Session_Namespace("portoweb");

        // Captura os parametros passados por GET
        $params = $this->getRequest()->getParams();
        
        // Instancia os modelos de dados
/*
        $sistemas   = new WebSistemaModel();
        $menus      = new WebMenuSistemaModel();
        $transacoes = new WebTransacaoModel();
*/
        // Joga para a view o usuário logado
        $this->view->cd_usuario = $sessao->perfil->CD_USUARIO;

        // Reenvia os valores para o formulário
        $this->_helper->RePopulaFormulario->repopular($params);
    }

    /**
     * Metodo salvar
     * objetivo: utilizado para as operações de INSERT/UPDATE
     */
    public function salvarAction() { 

		$db = Zend_Registry::get("db");

		// Recupera os parametros da requisição
        $params = $this->_request->getParams();

        // Instancia os modelos de dados
        $tabelaConsulta   = new ConsultaModel();
        $tabelaConsColuna = new ConsultaColunaModel();

        if($params["cd_consulta"] == "") {
			$params['operacao'] = "novo";
            $params["cd_consulta"] = $tabelaConsulta->nextVal();
        } else {
			$params['operacao'] = "editar";
		}

		if ($params['operacao'] == "novo") {
			$params["dthr_cadastro"] = date("d/m/Y h:i:s");
		}
		
        // Monta os dados que serão salvos
        $dados = array("CD_CONSULTA"    => (int) $params["cd_consulta"],
                       "DTHR_CADASTRO"	=> $params["dthr_cadastro"],
                       "DS_TIPO"        => "T", // TABELA OU VIEW
                       "TITULO"   		=> $params["titulo"],
                       "FL_ATIVO"    	=> $params["fl_ativo"],
                       "ORIENTACAO"    	=> $params["orientacao"],
                       "CD_USUARIO"   	=> $params["cd_usuario"]);
					   
		// Gerao o arquivo XML do Jasper
        $xmlJasper = $this->geraArquivo($dados);
		
		// Joga para a view os dados do relatório
		$this->view->cd_consulta = (int) $params["cd_consulta"];
		$this->view->nome		 = $xmlJasper['nomeRelatorio'];
		$this->view->titulo 	 = $xmlJasper['titulo'];
		$this->view->descricao   = $xmlJasper['descricao'];
		$this->view->arquivo   	 = $xmlJasper['arquivo'];
		$this->view->sistema 	 = $xmlJasper['sistema'];
		$this->view->ambiente 	 = APPLICATION_ENV;
		
		// Adiciona o xml aos dados a serem salvos
		$dados["XML"] = base64_decode($xmlJasper["xml"]);
		
		// Verifica as regras do modelo de dados
        if($tabelaConsulta->isValid($dados)) {
            
			if ($params['operacao'] == "editar") {
				$dados["DTHR_CADASTRO"] = new Zend_Db_Expr("TO_DATE('{$params["dthr_cadastro"]}:00', 'DD/MM/YYYY HH24:MI:SS')");
			}
            
            try {
                
                $insert = true;
                $update = true;
                
                // Verifica se a operação é de NOVO
                if($params['operacao'] == "novo") {
                    
                    // Insere os dados do sistema
                    $insert = $tabelaConsulta->insert($dados);

                    // Seta o código da transação
                    $this->_request->setParam("cd_consulta", $params["cd_consulta"]);

                } else {

                    // Define os filtros para a atualização
                    $where = $tabelaConsulta->addWhere(array("CD_CONSULTA = ?" => $params['cd_consulta']))
                                            ->getWhere();

                    // Atualiza os dados
                    $update = $tabelaConsulta->update($dados, $where);
                }
                
                // Redireciona para ação de selecionar
                if(!$insert || !$update) {
                    $this->_forward('pesquisar');
                    
                } else { // Se salvou
                    
                    // Define os filtros para a cosulta
                    $dados = array("cd_consulta" => $params['cd_consulta']);

                    // Retorna as transações, menus e sistemas
                    $select = $tabelaConsulta->queryBuscaConsultas($dados);

                    // Recupera a transação
                    $resConsulta  = $tabelaConsulta->fetchRow($select);

                    // Retorna as transações, menus e sistemas
                    $selectCount = $tabelaConsColuna->select()
                                                    ->from(array("CON" => "CONSULTA_COLUNA"), array("TOTAL"=> new Zend_Db_Expr("COUNT(*)")))
                                                    ->where("CON.CD_CONSULTA = " . $params['cd_consulta']);

                    // Recupera a transação
                    $count  = $tabelaConsulta->fetchRow($selectCount);

                    $this->view->possuiTabsVinculadas = $count->TOTAL >= 1 ? 1 : 0;
                    $this->view->nome				  = 'Rel' . $params['cd_consulta'];
                    $this->view->sistema 	 		  = 'gerador-relatorio';
                    $this->view->ambiente 	 		  = APPLICATION_ENV;

                    // Reenvia os valores para o formulário
                    $this->_helper->RePopulaFormulario->repopular($resConsulta->toArray(), "lower");
                    
                }
                
            } catch(Zend_Exception $e) {
                
                echo "Erro: " . $e->getMessage();
                
                // Define os filtros para a cosulta
                $where = $consulta->addWhere(array("CON.CD_CONSULTA = ?" => $params['cd_consulta']))
                                  ->getWhere();

                // Retorna as transações, menus e sistemas
                $select = $consulta->select()
                                   ->setIntegrityCheck(false)
                                   ->from(array("CON" => "CONSULTA"), array("CON.*", 
                                                                            "CON.CD_CONSULTA", 
                                                                            "DTHR_CADASTRO" => new Zend_Db_Expr("TO_CHAR(CON.DTHR_CADASTRO, 'DD/MM/YYYY HH24:MI')"), 
                                                                            "CON.TITULO", 
                                                                            "CON.FL_ATIVO", 
                                                                            "CON.CD_USUARIO", 
                                                                            "CON.ORIENTACAO"))
                                   ->where($where);

                // Recupera a transação
                $resConsulta  = $consulta->fetchRow($select);

                // Retorna se há tabelas já cadastradas no relatório
                $selectCount = $consulta->select()
                                        ->setIntegrityCheck(false)
                                        ->from(array("CON" => "CONSULTA_COLUNA"), array("TOTAL"=> new Zend_Db_Expr("COUNT(*)")))
                                        ->where($where);

                // Recupera a transação
                $count  = $consulta->fetchRow($selectCount);

                $this->view->possuiTabsVinculadas = $count->TOTAL >= 1 ? 1 : 0;

                // Reenvia os valores para o formulário
                $this->_helper->RePopulaFormulario->repopular($resConsulta->toArray(), "lower");
                
            }
            
        }
    }

    /**
     * Metodo excluir
     * objetivo: utilizado para a operação de DELETE
     */
    public function excluirAction() { 
        
        // Recupera os parametros da requisição
        $params = $this->_request->getParams();
        
        // Instancia os modelos 
        $consultas      = new ConsultaModel();
        $consColuna 	= new ConsultaColunaModel();
		$colunaFiltro	= new ConsultaColunaFiltroModel();
        $consConTabRef  = new ConsultaConexaoTabelaModel();
        $grupoCons      = new WebGrupoTabelaConsultaModel();
        
        // Monta a condição de exclusão
        $where = "CD_CONSULTA = {$params['cd_consulta']}";
        
		// Busca os filtros
		$cdColunas = $consColuna->fetchAll($where);
        
        $cdConsColuna = "";
        foreach ($cdColunas as $linha) {
            if($cdConsColuna != "") {
                $cdConsColuna .= ",";
            }

            $cdConsColuna .= $linha->CD_CONS_COLUNA;
        }
        
        // Exclui os filtros
        if($cdConsColuna != "") {
            $whereFiltro = "CD_CONS_COLUNA IN (" . $cdConsColuna . ")";
            $colunaFiltro->delete($whereFiltro);
        }
        
        
        
        // Exclui as ligações
        $consConTabRef->delete($where);
        
		// Atualiza os dados
		$consColuna->delete($where);
        
        // Exclui os grupos
        $grupoCons->delete($where);
        
        // Exclui a transação
        $delete = $consultas->delete($where);
        
        // Verifica se o registro foi excluído
        if($delete) {
            // Limpa os dados da requisição
            $params = $this->_helper->LimpaParametrosRequisicao->limpar();
			$params["titulo"] 	  = "";
			$params["orientacao"] = "";
		
            // Redireciona para o index
            $this->_forward("index", null, null, $params);
			
			//exibe mensagem de confirmação
			echo "<script>Base.montaMensagemSistema(Array('Operação realizada.'), 'SUCESSO', 2);</script>";
            
        } else {
            // Se não conseguir excluir, retorna pra seleção do registro
            $this->_forward("selecionar");
        }
        
    }

    /**
     * Metodo pesquisar
     * objetivo: utilizado para executar pesquisas
     */
    public function pesquisarAction() { 

        // Captura a sessão
        $sessao = new Zend_Session_Namespace('portoweb');

        // Recupera os parametros da requisição
        $params = $this->_request->getParams();

		// "Corrige" os valores dos parâmetros caso possuem caracteres acentuados
		foreach ($params as &$value) {
			$value = utf8_decode($value);
		}

        // Instancia a classe de consulta web
        $consulta = new ConsultaModel();
        
        // Retorna a query 
        $select = $consulta->queryBuscaConsultas($params)->orderByList();

		// Recebe a instância do paginator por singleton     
		$paginator = Zend_Paginator::factory($select);
		
		// Seta o número da página corrente
		$pagina = $this->_getParam('pagina', 1);
		
		// Define a página corrente
		$paginator->setCurrentPageNumber($pagina);
		
		// Define o total de linhas por página
		$paginator->setItemCountPerPage($sessao->perfil->QT_LINHAS);
		
		// Joga para a view a paginação
		$this->view->paginator = $paginator;

		// Reenvia os valores para o formulário
		$this->_helper->RePopulaFormulario->repopular($params);

		unset($consulta);  
        
    }

    /**
     * Metodo selecionar
     * objetivo: utilizado para selecionar um registro a partir de uma listagem
     */
    public function selecionarAction() { 
        
        // Recupera os parametros da requisição
        $params = $this->_request->getParams();
        
        // Instancia os modelos de dados
        $tabelaConsulta   = new ConsultaModel();
        $tabelaConsColuna = new ConsultaColunaModel();
        
        // Define os filtros para a cosulta
        $dados = array("cd_consulta" => $params['cd_consulta']);
        
        // Retorna as transações, menus e sistemas
        $select = $tabelaConsulta->queryBuscaConsultas($dados);

        // Recupera a transação
        $resConsulta  = $tabelaConsulta->fetchRow($select);

        // Retorna as transações, menus e sistemas
        $selectCount = $tabelaConsColuna->select()
                                        ->from(array("CON" => "CONSULTA_COLUNA"), array("TOTAL"=> new Zend_Db_Expr("COUNT(*)")))
                                        ->where("CON.CD_CONSULTA = " . $params['cd_consulta']);

        // Recupera a transação
        $count  = $tabelaConsulta->fetchRow($selectCount);

		$this->view->possuiTabsVinculadas = $count->TOTAL >= 1 ? 1 : 0;
		$this->view->nome				  = 'Rel' . $params['cd_consulta'];
		$this->view->sistema 	 		  = 'gerador-relatorio';
		$this->view->ambiente 	 		  = APPLICATION_ENV;
		
		// Reenvia os valores para o formulário
        $this->_helper->RePopulaFormulario->repopular($resConsulta->toArray(), "lower");
    }

    /**
     * Metodo relatorio
     * objetivo: utilizado para gerar um relatorio a partir de uma listagem
     */
    public function relatorioAction() { 
        
        // Chama o método relatório da classe pai
        parent::relatorio();
        
        // Captura a sessão
        $sessao = new Zend_Session_Namespace('portoweb');

        // Recupera os parametros da requisição
        $params = $this->_request->getParams();

        // Instancia a classe de transacoes web
        $transacoes = new ConsultaModel();
        
        // Retorna a query 
        $select = $transacoes->queryBuscaConsultas($params)->orderByList();
		
		// Executa a query, retorna o resultado da pesquisa
		$resConsulta = $transacoes->fetchAll($select);
        
        // Joga para a view os resultados
        $this->view->resConsulta = $resConsulta;
    }
   
    /**
     * Verifica a ordem de exibição da transação
     *
     * @return JSON
     */
    public function verificaOrdemExibicaoXAction() {

        // Verifica se arrequisição foi passada por Ajax
        if($this->_request->isXmlHttpRequest()) {
            // Captura os parametros passados por GET
            $params = $this->getRequest()->getParams();
            
            // Instancia o modelo
            $transacoes = new WebTransacaoModel();
            
            // Define os filtros para a cosulta
            $where = $transacoes->addWhere(array("CD_MENU       = ?" => $params['cd_menu']))
                                ->addWhere(array("ORD_TRANSACAO = ?" => $params['ord_transacao']))
                                ->getWhere();

            // Captura o registro
            $linha = $transacoes->fetchRow($where);
            
            // Converte a linha para array
            $retLinha = array();
            if($linha->CD_TRANSACAO != "") {
                $retLinha = $linha->toArray();
            }
            
            // Retorna os dados por json
            $this->_helper->json(Marca_ConverteCharset::converter($retLinha), true);

            // Limpa os objetos da memoria
            unset($transacoes);
        }

    }

    /**
     * Gera o relatório em XML
     *
     * @return boolean
     */
	 function geraArquivo($dadosRel=array()) {

        // Recupera a instância da base de dados
        $db = Zend_Registry::get("db");
        
        // Define o nome do arquivo
		$noArquivo = 'Rel'.$dadosRel['CD_CONSULTA'];

		// Abre o arquivo (se nÃ£o existir, cria)
		@unlink('jasper/'.$noArquivo.".jrxml");

		$arq = fopen('jasper/'.$noArquivo.".jrxml", "w");

		// DefiniÃ§Ã£o das margens laterais
		$leftMargin = 20;
		$rightMargin = 20;
		$margem = $leftMargin + $rightMargin;

		// OrientaÃ§Ã£o do rel 
		if ($dadosRel['ORIENTACAO'] == 'R'){ // Retrato
			$pageWidth = 595;
			$pageHeight = 842;
			$orientation = 'Portrait';
		}else{ // Paisagem
			$pageWidth = 842;
			$pageHeight = 595;
			$orientation = 'Landscape';
		}

		//Escreve no arquivo xml
		fwrite($arq, utf8_encode('<?xml version="1.0" encoding="UTF-8" ?>
						<jasperReport xmlns="http://jasperreports.sourceforge.net/jasperreports" 
							xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
							xsi:schemaLocation="http://jasperreports.sourceforge.net/jasperreports http://jasperreports.sourceforge.net/xsd/jasperreport.xsd" 
							name="' . $noArquivo . '" 
							language="groovy" 
							pageWidth="'.$pageWidth.'" 
							pageHeight="'.$pageHeight.'" 
							orientation="'.$orientation.'"
							whenNoDataType="NoDataSection" 
							columnWidth="555" 
							leftMargin="'.$leftMargin.'" 
							rightMargin="'.$rightMargin.'" 
							topMargin="20" 
							bottomMargin="20"
						>
					'));
        
        // Define o caminho do relatório
		$reportUnit = '/reports/producao/gerador_relatorio/' . $noArquivo;

        // Define o caminho do Jasper
		$url = JASPER_URL . '/jasperserver/services/repository';
        
		fwrite($arq, utf8_encode('<property name="ireport.zoom" value="1.5"/>
                                  <property name="ireport.x" value="0"/>
                                  <property name="ireport.y" value="0"/>
                                  <property name="ireport.jasperserver.reportUnit" value="'.$reportUnit.'"/>
                                  <property name="ireport.jasperserver.url" value="'.$url.'"/>'));

/**************************************
****** Montagem do PARAMETER **********
***************************************/
        
        // Busca os dados da consulta para montagem dos parâmetros
        $select = $db->select()
					->from(array("TC" => "COLUNA"), array("CF.CD_CONS_FILTRO",
                                                          "CC.CD_CONS_COLUNA",
														  "TC.CD_TABELA",
                                                          "CC.ALIAS_TABELA",
														  "TC.CD_COLUNA",
														  "TC.TP_COLUNA", 
														  "CT.NO_TP_FILTRO",
														  "CF.DS_FILTRO"))
					->join(array("CC" => "CONSULTA_COLUNA"), "TC.CD_TABELA = CC.CD_TABELA AND TC.CD_COLUNA = CC.CD_COLUNA", array())
					->join(array("CF" => "CONSULTA_COLUNA_FILTRO"), "CC.CD_CONS_COLUNA = CF.CD_CONS_COLUNA", array())
					->join(array("CT" => "CONSULTA_TIPO_FILTRO"), "CF.CD_TP_FILTRO = CT.CD_TP_FILTRO", array())
					->where("CC.CD_CONSULTA = {$dadosRel['CD_CONSULTA']}")
                    ->order("CC.NR_ORDEM ASC");
		
        // Joga em um array o resultado
        $res = $db->fetchAll($select);
		
		$parameter    = '';
		$arrFiltro    = array();
		$arrParameter = array();

		fwrite($arq, utf8_encode('<parameter name="sistema" class="java.lang.String"/>
                                  <parameter name="titulo" class="java.lang.String"/>
                                  <parameter name="usuario" class="java.lang.String"/>'));
		$aux=0;

		foreach ($res as $k=>$v) {
            
			$aux += 1;
			$entreAspas = false;
			$tpColuna   = strtolower($v->TP_COLUNA);
			$nomeParametro = strtolower($v->CD_COLUNA) . '_' . $v->CD_CONS_COLUNA . '_' . $v->CD_CONS_FILTRO;
			
			if ($tpColuna == 'd' || $tpColuna == 'c' || $tpColuna == 'date' || $tpColuna == 'datetime' || $tpColuna == 'smalldatetime' || $tpColuna == 'timestamp' || $tpColuna == 'string' || $tpColuna == 'char' || $tpColuna == 'varchar') {
				$entreAspas = true;
			}
			
			$tp_coluna = 'lang.String';
			$valorParameter = "";

			$testeValor = trim($v->DS_FILTRO);
			if ($testeValor !== ""){
				if ($entreAspas === true){
					if ($tpColuna == 'd' || $tpColuna == 'date' || $tpColuna == 'datetime' || $tpColuna == 'smalldatetime' || $tpColuna == 'timestamp') {
						$valorParameter .= 'TO_DATE('.$v->ALIAS_TABELA . "." . $v->CD_COLUNA . ", 'DD/MM/YYYY') " . $v->NO_TP_FILTRO . " TO_DATE('" . trim($v->DS_FILTRO) . "', 'DD/MM/YYYY')";
					}else{
                        // Se for entre aspas e o filtro for igual, muda para like
                        if($v->NO_TP_FILTRO == "=") {
                            $v->NO_TP_FILTRO = "LIKE";
                        }
                        
                        // Converte asterisco por porcento
                        $v->DS_FILTRO = str_replace("*", "%", $v->DS_FILTRO);
                        
						$valorParameter .= $v->ALIAS_TABELA . '.' . $v->CD_COLUNA . ' ' . $v->NO_TP_FILTRO . " '" . trim($v->DS_FILTRO) . "'";
					}
					
				} else {
					$valorParameter = $v->ALIAS_TABELA . '.' . $v->CD_COLUNA . ' ' . $v->NO_TP_FILTRO . ' ' . trim($v->DS_FILTRO);
				}
			}else{
				$valorParameter .= '1=1';
			}

			$arrFiltro[]    = $valorParameter;
			$arrParameter[] = $nomeParametro;
			fwrite($arq, utf8_encode('<parameter name="'.$nomeParametro.'" class="java.'.$tp_coluna.'">
                                          <defaultValueExpression><![CDATA[" AND '.$valorParameter.' "]]></defaultValueExpression>
                                      </parameter>'));

		}

		// Valor default das variaveis e arrays usados na montagem do QueryString
		$queryString= '';
		$selectQuery= '';
		$fromQuery	= '';
		$whereQuery	= '';
		$groupQuery	= '';
		$orderQuery	= '';

		$flag = 0;

		//$arrGroupBy = array();
		$arrVariables	= array();
		$arrCampos	= array();

		$existeSoma = false;

/***********************************
****** Montagem do SELECT **********
************************************/
        $select = $db->select()
					->from(array("TC" => "COLUNA"), array("TC.CD_TABELA",
                                                          "CC.CD_CONS_COLUNA",
                                                          "CC.ALIAS_TABELA",
														  "TC.CD_COLUNA", 
														  "CC.FL_SOMA"))
					->join(array("CC" => "CONSULTA_COLUNA"), "TC.CD_TABELA = CC.CD_TABELA AND TC.CD_COLUNA = CC.CD_COLUNA", array())
					->where("CC.CD_CONSULTA = {$dadosRel['CD_CONSULTA']}")
                    ->where("CC.FL_VISIVEL  = 1");

        // Joga em um array o resultado
        $res = $db->fetchAll($select);

		if (count($res) > 0){
			$selectQuery= 'SELECT ';

			foreach ($res as $k=>$v){
				$flag++;
				if ($v->FL_SOMA == 1) {
					$selectQuery .= $v->ALIAS_TABELA . '.' . $v->CD_COLUNA . ' AS ' . $v->CD_COLUNA . '_' . $v->CD_CONS_COLUNA;
					$arrVariables[] = $v->CD_COLUNA . '_' . $v->CD_CONS_COLUNA;
					$existeSoma = true;
				}else{
					$selectQuery .= $v->ALIAS_TABELA . '.' . $v->CD_COLUNA . ' AS ' . $v->CD_COLUNA . '_' . $v->CD_CONS_COLUNA;
				}
				if ($flag < count($res)) {
					$selectQuery .= ', ';
				}

			}
		}

/*********************************
****** Montagem do FROM **********
**********************************/
        $select = $db->select()
					 ->distinct()
					 ->from(array("CC" => "CONSULTA_COLUNA"), array("CC.CD_TABELA", "CC.ALIAS_TABELA"))
					 ->where("CC.CD_CONSULTA = {$dadosRel['CD_CONSULTA']}")
                     ->order("CC.ALIAS_TABELA");

        $res = $db->fetchAll($select);

		$flag = 0;
		$qtdeTabelas = count($res);
		$arrAliasTabelas = array();
		if ($qtdeTabelas > 0){
			$fromQuery	= '  FROM ';

			foreach ($res as $k=>$v){
				$flag++;
				$fromQuery .= $v->CD_TABELA . " " . $v->ALIAS_TABELA;

				if ($flag < count($res)) {
					$fromQuery .= ', ';
				}
			}
		}

/**********************************
****** Montagem do WHERE **********
***********************************/

        $sql =  "select cct.cd_consulta,
                        cct.alias_tabela,
                        cct.alias_tabela_ref,
                        cct.fk_tabela,
                        tp1.tabela_principal,
                        tp1.tabela_relacionada,
                        tp1.foreign_key,
                        tp1.coluna_tabela_principal,
                        tp1.coluna_tabela_relacionada
                 from consulta_conexao_tabela cct,
                 (select r.table_name as tabela_principal, 
                         c.table_name as tabela_relacionada,
                         c.constraint_name as foreign_key,
                         rc.column_name as coluna_tabela_principal,
                         cc.column_name as coluna_tabela_relacionada
                 from    all_constraints c,
                         all_constraints r,
                         all_cons_columns cc,
                         all_cons_columns rc
                 where   c.constraint_type = 'R'
                 and     c.owner not in ('SYS','SYSTEM')
                 and     c.r_owner = r.owner
                 and     c.owner = cc.owner
                 and     r.owner = rc.owner
                 and     c.constraint_name = cc.constraint_name
                 and     r.constraint_name = rc.constraint_name
                 and     c.r_constraint_name = r.constraint_name
                 and     cc.position = rc.position
                 and     c.constraint_name in (
                         select distinct cct.fk_tabela
                           from consulta_conexao_tabela cct 
                          where cct.cd_consulta  = {$dadosRel['CD_CONSULTA']})
                 union
                 select  c.table_name as tabela_principal, 
                         r.table_name as tabela_relacionada,
                         c.constraint_name as foreign_key,
                         cc.column_name as coluna_tabela_principal,
                         rc.column_name as coluna_tabela_relacionada
                 from    all_constraints c,
                         all_constraints r,
                         all_cons_columns cc,
                         all_cons_columns rc
                 where   c.constraint_type = 'R'
                 and     c.owner not in ('SYS','SYSTEM')
                 and     c.r_owner = r.owner
                 and     c.owner = cc.owner
                 and     r.owner = rc.owner
                 and     c.constraint_name = cc.constraint_name
                 and     r.constraint_name = rc.constraint_name
                 and     c.r_constraint_name = r.constraint_name
                 and     cc.position = rc.position
                 and     c.constraint_name in (
                         select distinct cct.fk_tabela
                           from consulta_conexao_tabela cct 
                          where cct.cd_consulta  = {$dadosRel['CD_CONSULTA']})
                 ) tp1,
                 (select distinct
                         ccl.cd_tabela,
                         ccl.alias_tabela
                    from consulta_coluna ccl
                   where ccl.cd_consulta = {$dadosRel['CD_CONSULTA']}
                 ) tp2
                 where cct.fk_tabela    = tp1.foreign_key
                   and cct.alias_tabela = tp2.alias_tabela
                   and tp2.cd_tabela    = tp1.tabela_principal
                 order by cct.alias_tabela asc";

        $resWhere = $db->query($sql);

        $whereQuery = ' WHERE 1=1';

        foreach($resWhere->fetchAll() as $whereLin) {
            // Monta a condição
            $whereQuery .= ' AND '. $whereLin->ALIAS_TABELA_REF . '.' . $whereLin->COLUNA_TABELA_RELACIONADA . ' = ' 
                                  . $whereLin->ALIAS_TABELA     . '.' . $whereLin->COLUNA_TABELA_PRINCIPAL;
        }


	// Complementação do WHERE com os filtros cadastrados
		foreach ($arrParameter as $v){
			$whereQuery .= ' $P!{'.$v.'} ';
		}

/*************************************
****** Montagem do GROUP BY **********
**************************************/
/*
		if ($existeGroupBy == true){
			$groupQuery	= ' GROUP BY ';
			$flag=0;
			foreach ($arrGroupBy as $v){
				$flag++;
				$groupQuery .= $v;
				if ($flag < count($arrGroupBy)) {
					$groupQuery	.= ', ';
				}
			}
		}
*/
/*************************************
****** Montagem do ORDER BY **********
**************************************/
		$select = $db->select()
					->from(array("TC" => "COLUNA"), array("TC.CD_TABELA",
                                                          "CC.CD_CONS_COLUNA",
                                                          "CC.ALIAS_TABELA",
														  "TC.CD_COLUNA", 
														  "CC.NR_ORDEM"))
					->join(array("CC" => "CONSULTA_COLUNA"), "TC.CD_TABELA = CC.CD_TABELA AND TC.CD_COLUNA = CC.CD_COLUNA", array())
					->where("CC.CD_CONSULTA = {$dadosRel['CD_CONSULTA']} AND CC.NR_ORDEM != 0")
                    ->where("CC.FL_VISIVEL = 1")
					->order("CC.NR_ORDEM ASC");

        $resOrder = $db->fetchAll($select);

		$flag = 0;
		if (count($resOrder) > 0){
			$orderQuery	= ' ORDER BY ';

			foreach ($resOrder as $k=>$v){
				$flag++;
				$orderQuery .= $v->CD_COLUNA . '_' . $v->CD_CONS_COLUNA .' ASC';

				if ($flag < count($resOrder)) {
					$orderQuery .= ', ';
				}
			}
		}
        
        // Junta todos as variáveis da query para montar a consulta
		$queryString = 	$selectQuery.
						$fromQuery.
						$whereQuery.
						$groupQuery.
						$orderQuery;

		fwrite($arq, utf8_encode('<queryString><![CDATA['.$queryString.']]></queryString>'));

/***********************************
****** Montagem do TAGFIELDS *******
************************************/
        $select = $db->select()
					->from(array("TC" => "COLUNA"), array("CC.CD_CONS_COLUNA",
                                                          "TC.CD_COLUNA",
														  "TC.TP_COLUNA"))
					->join(array("CC" => "CONSULTA_COLUNA"), "TC.CD_TABELA = CC.CD_TABELA AND TC.CD_COLUNA = CC.CD_COLUNA", array())
					->where("CC.CD_CONSULTA = {$dadosRel['CD_CONSULTA']}")
                    ->where("CC.FL_VISIVEL  = 1");

        $resField = $db->fetchAll($select);

		if (count($resField) > 0) {

			foreach ($resField as $k => $v) {
                
				if (strtolower($v->TP_COLUNA) == 'n' || strtolower($v->TP_COLUNA) == 'numeric' || strtolower($v->TP_COLUNA) == 'money' || strtolower($v->TP_COLUNA) == 'smallint' || strtolower($v->TP_COLUNA) == 'int' || strtolower($v->TP_COLUNA) == 'bigint' || strtolower($v->TP_COLUNA) == 'tinyint') {
					$tp_coluna = 'math.BigDecimal';
				}else if (strtolower($v->TP_COLUNA) == 'd' || strtolower($v->TP_COLUNA) == 'date' || strtolower($v->TP_COLUNA) == 'datetime' || strtolower($v->TP_COLUNA) == 'smalldatetime' || strtolower($v->TP_COLUNA) == 'timestamp') {
					$tp_coluna = 'sql.Timestamp';
				}else if (strtolower($v->TP_COLUNA) == 'c' || strtolower($v->TP_COLUNA) == 'text' || strtolower($v->TP_COLUNA) == 'char' || strtolower($v->TP_COLUNA) == 'varchar')  {
					$tp_coluna = 'lang.String';
				} else {
                    $tp_coluna = 'lang.String';
                }

				fwrite($arq, utf8_encode('<field name="' . $v->CD_COLUNA . '_' . $v->CD_CONS_COLUNA . '" class="java.' . $tp_coluna . '"/>'));

			}
		}

/***********************************
****** Montagem do VARIABLES *******
************************************/

		if ($existeSoma){

			for ($i=0; $i < count($arrVariables); $i++){

				$campo = $arrVariables[$i].'_'.$i;

				fwrite($arq, utf8_encode('<variable name="'.$campo.'" class="java.math.BigDecimal" resetType="Column" calculation="Sum">
											<variableExpression><![CDATA[$F{'.$arrVariables[$i].'}]]></variableExpression>
										</variable>'));
			}
		}

		fwrite($arq, utf8_encode('<background><band splitType="Stretch"/></background>'));

/********************************
****** Montagem do TÍTULO *******
*********************************/

		fwrite($arq, utf8_encode('<title>
                        <band height="70" splitType="Stretch">
                            <staticText>
                                <reportElement positionType="Float" stretchType="RelativeToTallestObject" x="75" y="3" width="'.($pageWidth-$margem-85).'" height="15"/>
                                <textElement textAlignment="Center" markup="none">
                                    <font fontName="SansSerif" size="11" isBold="false"/>
                                </textElement>
                                <text><![CDATA[SUPERINTENDÊNCIA DO PORTO DO RIO GRANDE]]></text>
                            </staticText>
                            <textField>
                                <reportElement x="75" y="24" width="'.($pageWidth-$margem-85).'" height="20"/>
                                <textElement textAlignment="Center">
                                    <font size="9"/>
                                </textElement>
                                <textFieldExpression><![CDATA[$P{sistema}]]></textFieldExpression>
                            </textField>
                            <textField>
                                <reportElement x="75" y="44" width="'.($pageWidth-$margem-85).'" height="16"/>
                                <textElement textAlignment="Center">
                                    <font size="9" isBold="true"/>
                                </textElement>
                                <textFieldExpression><![CDATA[$P{titulo}]]></textFieldExpression>
                            </textField>
                            <line>
                                <reportElement x="0" y="63" width="'.($pageWidth-$margem).'" height="1"/>
                            </line>
                            <image>
                                <reportElement x="3" y="1" width="62" height="60"/>
                                <imageExpression><![CDATA["repo:/images/logo_porto"]]></imageExpression>
                            </image>
                        </band>
                    </title>'));

		fwrite($arq, utf8_encode('<pageHeader><band height="11" splitType="Stretch"/></pageHeader>'));

/*********************************
*** Montagem da COLUMNHEADER *****
**********************************/

		if (strtolower($v->TP_COLUNA) == 'n' || strtolower($v->TP_COLUNA) == 'numeric' || strtolower($v->TP_COLUNA) == 'money' || strtolower($v->TP_COLUNA) == 'smallint' || strtolower($v->TP_COLUNA) == 'int' || strtolower($v->TP_COLUNA) == 'bigint' || strtolower($v->TP_COLUNA) == 'tinyint') {
			$tp_coluna = 'math.BigDecimal';
		}else if (strtolower($v->TP_COLUNA) == 'd' || strtolower($v->TP_COLUNA) == 'date' || strtolower($v->TP_COLUNA) == 'datetime' || strtolower($v->TP_COLUNA) == 'smalldatetime' || strtolower($v->TP_COLUNA) == 'timestamp') {
			$tp_coluna = 'sql.Timestamp';
		}else if (strtolower($v->TP_COLUNA) == 'c' || strtolower($v->TP_COLUNA) == 'text' || strtolower($v->TP_COLUNA) == 'char' || strtolower($v->TP_COLUNA) == 'varchar')  {
			$tp_coluna = 'lang.String';
		}

        $sql = "select 	case when cc.ds_cabecalho is not null then 
							cc.ds_cabecalho
						else 
							tc.no_coluna
						end as titulo,
						cc.tam_largura *".($pageWidth-$margem-1)."/ 100 as tam_largura,
						case when tc.tp_coluna in('N', 'numeric','smallint','bigint','tinyint','int') then  
							'Right'
						else
							'Left'
						end as alinhamento
				  from 	coluna   tc
			inner join 	consulta_coluna cc on tc.cd_tabela = cc.cd_tabela
						 			   and tc.cd_coluna = cc.cd_coluna
				 where 	cc.cd_consulta = {$dadosRel['CD_CONSULTA']}
                   and cc.fl_visivel = 1 
			  order by 	cc.nr_posicao";

        // Joga em um array o retorno da query
        $res = $db->query($sql)->fetchAll();

		if (count($res) > 0){

			fwrite($arq, utf8_encode('<columnHeader><band height="22" splitType="Stretch">'));

			$x = 0;
			$y = 2;
			foreach ($res as $k=>$v){
				fwrite($arq, utf8_encode('<staticText>
								<reportElement x="'.$x.'" y="2" width="'.($v->TAM_LARGURA - 5).'" height="12"/>
								<textElement textAlignment="'.$v->ALINHAMENTO.'">
									<font size="9" isBold="true"/>
								</textElement>
								<text><![CDATA['.($v->TITULO).']]></text>
							</staticText>'));
                
				$x = $x + $v->TAM_LARGURA;
			}

			fwrite($arq, utf8_encode('<line>
                                        <reportElement x="0" y="16" width="'.($pageWidth-$margem).'" height="1"/>
                                    </line>
                                </band>
                            </columnHeader>'));

		}

/***************************
*** Montagem da DETAIL *****
****************************/

			fwrite($arq, utf8_encode('<detail>
						<band height="18" splitType="Stretch">
							<rectangle>
								<reportElement x="1" y="4" width="'.($pageWidth-$margem).'" height="11" forecolor="#DEDDDD" backcolor="#DEDDDD">
									<printWhenExpression><![CDATA[new Boolean(($V{COLUMN_COUNT}.intValue() % 2) != 0)]]></printWhenExpression>
								</reportElement>
							</rectangle>'));

/******************************
*** Montagem da TEXTFIELD *****
*******************************/

        $select = $db->select()
					->from(array("CC" => "CONSULTA_COLUNA"), array("CC.CD_CONS_COLUNA",
                                                                    "CC.CD_COLUNA",
                                                                    "TC.TP_COLUNA",
                                                                    "CC.TAM_LARGURA AS TAM_ORIG",
                                                                    "(CC.TAM_LARGURA * ".($pageWidth-$margem-1)."/100) AS TAM_LARGURA",
                                                                    "CASE WHEN TC.TP_COLUNA IN('N','NUMERIC','SMALLINT','BIGINT','TINYINT','INT') THEN  
                                                                          'Right'
                                                                      ELSE
                                                                          'Left'
                                                                      END AS ALINHAMENTO"))
					->where("CC.CD_CONSULTA = {$dadosRel['CD_CONSULTA']}")
                    ->where("CC.FL_VISIVEL = 1")
					->join(array("TC" => "COLUNA"), "TC.CD_TABELA = CC.CD_TABELA AND TC.CD_COLUNA = CC.CD_COLUNA", array())
					->order("CC.NR_POSICAO ASC");

        $res = $db->fetchAll($select);

		if (count($res) > 0){
			$x = 0;

			$arrPosicoes = array();

			foreach ($res as $k=>$v){
				
				$arrPosicoes[$v->CD_COLUNA . '_' . $v->CD_CONS_COLUNA] = array('x'=>$x, 'width'=>$v->TAM_LARGURA);

				if (strtolower($v->TP_COLUNA) == 'd' || strtolower($v->TP_COLUNA) == 'date' || strtolower($v->TP_COLUNA) == 'datetime') {
					$campoFormat = 'new SimpleDateFormat("dd/MM/yyyy").format($F{'.$v->CD_COLUNA . '_' . $v->CD_CONS_COLUNA .'})';
				}else{
					$campoFormat = '$F{' . $v->CD_COLUNA . '_' . $v->CD_CONS_COLUNA . '}';
				}				

				if (strtolower($v->ALINHAMENTO) == 'right'){
					$tipo = "new Float(0.0)";
				}else{
					$tipo = "new String('')";
				}
                
				fwrite($arq, utf8_encode('<textField>
								<reportElement x="'.$x.'" y="3" width="'.($v->TAM_LARGURA - 5).'" height="12"/>
								<textElement textAlignment="'.$v->ALINHAMENTO.'">
									<font size="9"/>
								</textElement>
								<textFieldExpression><![CDATA[$F{'.$v->CD_COLUNA . '_' . $v->CD_CONS_COLUNA .'} == null ? '.$tipo.' : '.$campoFormat.']]></textFieldExpression>
							</textField>'));
                
				$x = ($x + $v->TAM_LARGURA);
			}

			fwrite($arq, utf8_encode('</band></detail>'));
		}

		/***************************************
		*** Montagem do COLUMNFOOTER *****
		****************************************/

		fwrite($arq, utf8_encode('<columnFooter><band height="45" splitType="Stretch">'));

		if ($existeSoma) {

			for ($i=0; $i < count($arrVariables); $i++){
				
				$campo = $arrVariables[$i].'_'.$i;
				
				$x     = 0;
				$width = 100;
				if(isset($arrPosicoes[$arrVariables[$i]])) {
					$x     = $arrPosicoes[$arrVariables[$i]]["x"];
					$width = $arrPosicoes[$arrVariables[$i]]["width"];
				}

				fwrite($arq, utf8_encode('<textField>
											<reportElement x="' . $x . '" y="14" width="' . ($width - 5) . '" height="20"/>
											<textElement textAlignment="Right"/>
											<textFieldExpression><![CDATA[$V{'.$campo.'}]]></textFieldExpression>
										 </textField>'));
			}
		}

		fwrite($arq, utf8_encode('</band></columnFooter>'));

		/***************************************
		*** Montagem da parta final do SML *****
		****************************************/
		fwrite($arq, utf8_encode('<pageFooter>
						<band height="16" splitType="Stretch">
							<textField pattern="dd/MM/yyyy HH:mm:ss">
								<reportElement stretchType="RelativeToTallestObject" x="45" y="4" width="71" height="10"/>
								<textElement>
									<font fontName="SansSerif" size="7"/>
								</textElement>
								<textFieldExpression><![CDATA[new java.util.Date()]]></textFieldExpression>
							</textField>
							<textField evaluationTime="Report">
								<reportElement x="'.($pageWidth-$margem-45).'" y="4" width="40" height="10"/>
								<textElement>
									<font fontName="SansSerif" size="7"/>
								</textElement>
								<textFieldExpression><![CDATA[" " + $V{PAGE_NUMBER}]]></textFieldExpression>
							</textField>
							<staticText>
								<reportElement x="10" y="4" width="33" height="10"/>
								<textElement>
									<font fontName="SansSerif" size="7"/>
								</textElement>
								<text><![CDATA[Emissão]]></text>
							</staticText>
							<staticText>
								<reportElement x="116" y="4" width="13" height="10"/>
								<textElement>
									<font fontName="SansSerif" size="7"/>
								</textElement>
								<text><![CDATA[   -  ]]></text>
							</staticText>
							<textField>
								<reportElement x="'.($pageWidth-$margem-130).'" y="4" width="80" height="10"/>
								<textElement textAlignment="Right">
									<font fontName="SansSerif" size="7"/>
								</textElement>
								<textFieldExpression><![CDATA["Página "+$V{PAGE_NUMBER}+" de"]]></textFieldExpression>
							</textField>
							<textField pattern="dd/MM/yyyy HH:mm:ss" isBlankWhenNull="false">
								<reportElement mode="Transparent" x="132" y="4" width="100" height="10" forecolor="#000000" backcolor="#FFFFFF"/>
								<textElement textAlignment="Left" verticalAlignment="Top" rotation="None" markup="none">
									<font fontName="SansSerif" size="7" isBold="false" isItalic="false" isUnderline="false" isStrikeThrough="false" pdfEncoding="Cp1252" isPdfEmbedded="false"/>
									<paragraph lineSpacing="Single"/>
								</textElement>
								<textFieldExpression><![CDATA[$P{usuario}]]></textFieldExpression>
							</textField>
							<line>
								<reportElement x="0" y="2" width="'.($pageWidth-$margem).'" height="1"/>
							</line>
						</band>
					</pageFooter>'));

		/****************************
		*** Montagem da SUMMARY *****
		*****************************/
		fwrite($arq, utf8_encode('<summary><band height="25" splitType="Stretch" /></summary>'));
					
		fwrite($arq, utf8_encode('<noData>
						<band height="18">
							<staticText>
								<reportElement x="0" y="2" width="'.($pageWidth-$margem).'" height="14"/>
								<textElement textAlignment="Center"/>
								<text><![CDATA[Nenhuma informação foi encontrada para a sua pesquisa.]]></text>
							</staticText>
						</band>
					</noData>
				</jasperReport>'));

		// Fecha o arquivo
		fclose($arq);

		// Abre o arquivo para gravar no banco
		$arq2 = fopen('jasper/'.$noArquivo.".jrxml", "r");
		$xml  = "";
		while (!feof($arq2)) {
			$xml .= fgetc($arq2);
		}
		fclose($arq2);

		// Remove caracteres não funcionais
		$xml = preg_replace("/[\t\r\n]+/", " ", trim($xml));

		// Retorna os dados do relatório
		$dadosRel = array('nomeRelatorio' => $noArquivo,
						  'titulo' 		  => $dadosRel['TITULO'],
						  'descricao' 	  => $dadosRel['TITULO'],
						  'arquivo' 	  => $noArquivo,
						  'sistema' 	  => 'gerador-relatorio',
						  'xml' 		  => base64_encode($xml));
		
		return $dadosRel;

	}

}
