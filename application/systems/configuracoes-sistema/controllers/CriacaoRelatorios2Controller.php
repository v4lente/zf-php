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
class ConfiguracoesSistema_CriacaoRelatorios2Controller extends Marca_Controller_Abstract_Operacao {

    /**
     * (non-PHPdoc)
     * @see library/Marca/Controller/Abstract/Marca_Controller_Abstract_Operacao#init()
     */
    public function init() {
        parent::init();

        // Carrega os modelos de dados
        Zend_Loader::loadClass("ConsultaModel");
		Zend_Loader::loadClass('ConsultaColunaTabelaModel');
		Zend_Loader::loadClass('ConsultaCondicaoColunaModel');
        Zend_Loader::loadClass('ConsultaLigacaoTabelaModel');
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
        $tabelaConsColuna = new ConsultaColunaTabelaModel();

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
                    
                    if($params["salvar_silencioso"] == 1) {
                        $tabelaConsulta->setShowMessage(false);
                    }
                    
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
                                                    ->from(array("CON" => "CONSULTA_COLUNA_TABELA"), array("TOTAL"=> new Zend_Db_Expr("COUNT(*)")))
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
                $where = $tabelaConsulta->addWhere(array("CON.CD_CONSULTA = ?" => $params['cd_consulta']))
                                        ->getWhere();

                // Retorna as transações, menus e sistemas
                $select = $tabelaConsulta->select()
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
                $resConsulta  = $tabelaConsulta->fetchRow($select);

                // Retorna se há tabelas já cadastradas no relatório
                $selectCount = $tabelaConsulta->select()
                                              ->setIntegrityCheck(false)
                                              ->from(array("CON" => "CONSULTA_COLUNA"), array("TOTAL"=> new Zend_Db_Expr("COUNT(*)")))
                                              ->where($where);

                // Recupera a transação
                $count  = $tabelaConsulta->fetchRow($selectCount);

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
        $consColuna 	= new ConsultaColunaTabelaModel();
		$colunaFiltro	= new ConsultaCondicaoColunaModel();
        $consConTabRef  = new ConsultaLigacaoTabelaModel();
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

            $cdConsColuna .= $linha->CD_COL_TAB;
        }
        
        // Exclui os filtros
        if($cdConsColuna != "") {
            $whereFiltro = "CD_COL_TAB IN (" . $cdConsColuna . ")";
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
        $tabelaConsColuna = new ConsultaColunaTabelaModel();
        
        // Define os filtros para a cosulta
        $dados = array("cd_consulta" => $params['cd_consulta']);
        
        // Retorna as transações, menus e sistemas
        $select = $tabelaConsulta->queryBuscaConsultas($dados);

        // Recupera a transação
        $resConsulta  = $tabelaConsulta->fetchRow($select);

        // Retorna as transações, menus e sistemas
        $selectCount = $tabelaConsColuna->select()
                                        ->from(array("CON" => "CONSULTA_COLUNA_TABELA"), array("TOTAL"=> new Zend_Db_Expr("COUNT(*)")))
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
		$reportUnit = '/reports/' . strtoupper(APPLICATION_ENV) . '/gerador-relatorio/' . $noArquivo;

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
					->from(array("TC" => "COLUNA"), array("TC.TP_COLUNA", 
                                                          "CC.CD_COND_COL",
                                                          "CC.CD_CONSULTA",
                                                          "SG_TABELA" => "LTRIM(RTRIM(CC.SG_TABELA))",
                                                          "CC.CD_COLUNA",
                                                          "CC.DS_COMPLEMENTO",
                                                          "CC.ABRE_PAR",
                                                          "CC.FECHA_PAR",
                                                          "CC.TP_LIGACAO",
                                                          "CC.LIG_INT_PAR",
                                                          "CC.LIG_EXT_PAR",
                                                          "CC.ORD_COND",
														  "CF.NO_TP_FILTRO"))
					->join(array("CC" => "CONSULTA_CONDICAO_COLUNA"), "TC.CD_COLUNA = CC.CD_COLUNA", array())
                    ->join(array("CT" => "CONSULTA_TABELA"), "TC.CD_TABELA = CT.CD_TABELA AND LTRIM(RTRIM(CC.SG_TABELA)) = LTRIM(RTRIM(CT.SG_TABELA))", array())
					->join(array("CF" => "CONSULTA_TIPO_FILTRO"), "CC.TP_LIGACAO = CF.CD_TP_FILTRO", array())
					->where("CC.CD_CONSULTA = {$dadosRel['CD_CONSULTA']}")
                    ->order("CC.ORD_COND ASC");
		
        // Joga em um array o resultado
        $res = $db->fetchAll($select);
		
		$parameter    = '';
		$arrFiltro    = array();
		$arrParameter = array();

		fwrite($arq, utf8_encode('<parameter name="sistema" class="java.lang.String"/>
                                  <parameter name="titulo" class="java.lang.String"/>
                                  <parameter name="usuario" class="java.lang.String"/>'));
		$aux=0;
        
        $andOr = false;
		foreach ($res as $k=>$v) {
            
			$aux += 1;
			$entreAspas    = false;
			$tpColuna      = strtolower($v->TP_COLUNA);
			$nomeParametro = strtolower($v->CD_COLUNA) . '_' . $v->CD_COND_COL;
			
			if ($tpColuna == 'd' || $tpColuna == 'c' || $tpColuna == 'date' || $tpColuna == 'datetime' || $tpColuna == 'smalldatetime' || $tpColuna == 'timestamp' || $tpColuna == 'string' || $tpColuna == 'char' || $tpColuna == 'varchar') {
				$entreAspas = true;
			}
            
            if(trim(strtoupper($v->NO_TP_FILTRO)) == "IS" || trim(strtoupper($v->NO_TP_FILTRO)) == "IS NOT") {
                $entreAspas = false;
            }
			
			$tp_coluna = 'lang.String';
			$valorParameter = trim($v->ABRE_PAR) . " ";

			$testeValor = trim($v->DS_COMPLEMENTO);
			if ($testeValor !== ""){
				if ($entreAspas === true){
					if ($tpColuna == 'd' || $tpColuna == 'date' || $tpColuna == 'datetime' || $tpColuna == 'smalldatetime' || $tpColuna == 'timestamp') {
                        if(trim(strtoupper($v->NO_TP_FILTRO)) == "BETWEEN") {
							$matches = null;
							preg_match_all('/\d{2}\/\d{2}\/\d{4} \d{2}\:\d{2}/', substr($v->DS_COMPLEMENTO, 0, 16), $matches);
							if(count($matches[0]) == 2) {
								$valorParameter .= 'TO_DATE(TO_CHAR('.$v->SG_TABELA . "." . $v->CD_COLUNA . ", 'DD/MM/YYYY HH24:MI'), 'DD/MM/YYYY HH24:MI') " . $v->NO_TP_FILTRO . " TO_DATE('" . trim($matches[0][0]) . "', 'DD/MM/YYYY HH24:MI') AND TO_DATE('" . trim($matches[0][1]) . "', 'DD/MM/YYYY HH24:MI')";
							} else {
								$matches = null;
								preg_match_all('/\d{2}\/\d{2}\/\d{4}/', $v->DS_COMPLEMENTO, $matches);
								if(count($matches[0]) == 2) {
									$valorParameter .= 'TO_DATE(TO_CHAR('.$v->SG_TABELA . "." . $v->CD_COLUNA . ", 'DD/MM/YYYY'), 'DD/MM/YYYY') " . $v->NO_TP_FILTRO . " TO_DATE('" . trim($matches[0][0]) . "', 'DD/MM/YYYY') AND TO_DATE('" . trim($matches[0][1]) . "', 'DD/MM/YYYY')";
								}
							}
                            
                        } else {
							$matches = null;
							preg_match_all('/\d{2}\/\d{2}\/\d{4} \d{2}\:\d{2}/', substr($v->DS_COMPLEMENTO, 0, 16), $matches);
							if(isset($matches[0][0]) && $matches[0][0] != "" && strlen($matches[0][0]) == 16) {
								$valorParameter .= 'TO_DATE(TO_CHAR('.$v->SG_TABELA . "." . $v->CD_COLUNA . ", 'DD/MM/YYYY HH24:MI'), 'DD/MM/YYYY HH24:MI') " . $v->NO_TP_FILTRO . " TO_DATE('" . trim($matches[0][0]) . "', 'DD/MM/YYYY HH24:MI')";
							} else {
								$valorParameter .= 'TO_DATE(TO_CHAR('.$v->SG_TABELA . "." . $v->CD_COLUNA . ", 'DD/MM/YYYY'), 'DD/MM/YYYY') " . $v->NO_TP_FILTRO . " TO_DATE('" . trim($v->DS_COMPLEMENTO) . "', 'DD/MM/YYYY')";
							}
                        }
					}else{
                        // Se for entre aspas e o filtro for igual, muda para like
                        if($v->NO_TP_FILTRO == "=") {
                            $v->NO_TP_FILTRO = "LIKE";
                        }
                        
                        // Converte asterisco por porcento
                        $v->DS_COMPLEMENTO = str_replace("*", "%", $v->DS_COMPLEMENTO);
                        
						$valorParameter .= $v->SG_TABELA . '.' . $v->CD_COLUNA . ' ' . $v->NO_TP_FILTRO . " '" . trim($v->DS_COMPLEMENTO) . "'";
					}
					
				} else {
					$valorParameter .= $v->SG_TABELA . '.' . $v->CD_COLUNA . ' ' . $v->NO_TP_FILTRO . ' ' . trim($v->DS_COMPLEMENTO);
				}
			}else{
				$valorParameter .= '1=1';
			}
            
            $valorParameter .= " " . trim($v->LIG_INT_PAR) . " " . trim($v->FECHA_PAR) . " " . trim($v->LIG_EXT_PAR);
            
            if($andOr === false) {
                $valorParameter = " AND " . $valorParameter;
            }
            
            if(trim($v->LIG_INT_PAR) != "" || trim($v->LIG_EXT_PAR) != "") {
                $andOr = true;
            } else {
                $andOr = false;
            }
            
			$arrFiltro[]    = $valorParameter;
			$arrParameter[] = $nomeParametro;
			fwrite($arq, utf8_encode('<parameter name="'.$nomeParametro.'" class="java.'.$tp_coluna.'">
                                          <defaultValueExpression><![CDATA[" '.$valorParameter.' "]]></defaultValueExpression>
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
		$arrCampos	    = array();
        $arrTipoCampos  = array();
        $arrCalculation = array();

/***********************************
****** Montagem do SELECT **********
************************************/
        
        $select = $db->select()
					->from(array("TC" => "COLUNA"), array("TC.TP_COLUNA", 
                                                          "CC.CD_COL_TAB",
                                                          "CC.CD_CONSULTA",
                                                          "SG_TABELA" => "LTRIM(RTRIM(CC.SG_TABELA))",
                                                          "CC.CD_COLUNA",
                                                          "CC.DS_CABECALHO",
                                                          "CC.TP_FORMATO",
                                                          "CC.TAM_COLUNA",
                                                          "CC.ORD_COLUNA",
                                                          "CC.CD_TP_TOTAL",
                                                          "CP.NO_TP_TOTAL",
                                                          "CC.SENTIDO_COLUNA",
                                                          "CC.DISTINTO"))
					->join(array("CC" => "CONSULTA_COLUNA_TABELA"), "TC.CD_COLUNA = CC.CD_COLUNA", array())
                    ->join(array("CT" => "CONSULTA_TABELA"), "TC.CD_TABELA = CT.CD_TABELA AND LTRIM(RTRIM(CC.SG_TABELA)) = LTRIM(RTRIM(CT.SG_TABELA))", array())
                    ->joinLeft(array("CP" => "CONSULTA_TIPO_TOTAL"), "CC.CD_TP_TOTAL = CP.CD_TP_TOTAL", array())
					->where("CC.CD_CONSULTA = {$dadosRel['CD_CONSULTA']}")
                    ->order("CC.ORD_COLUNA ASC");
                    
        // Joga em um array o resultado
        $res = $db->fetchAll($select);

		if (count($res) > 0){
			$selectQuery= 'SELECT ';

			foreach ($res as $k=>$v){
				$flag++;
                
                if($v->DISTINTO == "1") {
                    $selectQuery .= 'DISTINCT ';
                }
                
				if ($v->CD_TP_TOTAL != "0") {
					$selectQuery     .= $v->SG_TABELA . '.' . $v->CD_COLUNA . ' AS ' . $v->CD_COLUNA . '_' . $v->CD_COL_TAB;
					$arrVariables[]   = $v->CD_COLUNA . '_' . $v->CD_COL_TAB;
                    $arrTipoCampos[]  = $v->TP_COLUNA;
                    $arrCalculation[] = $v->NO_TP_TOTAL;
				}else{
					$selectQuery .= $v->SG_TABELA . '.' . $v->CD_COLUNA . ' AS ' . $v->CD_COLUNA . '_' . $v->CD_COL_TAB;
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
					 ->from(array("CC" => "CONSULTA_TABELA"), array("CC.CD_CONSULTA",
                                                                    "CC.CD_TABELA",
                                                                    "SG_TABELA" => "LTRIM(RTRIM(CC.SG_TABELA))"))
					 ->where("CC.CD_CONSULTA = {$dadosRel['CD_CONSULTA']}");

        $res = $db->fetchAll($select);

		$flag = 0;
		$qtdeTabelas = count($res);
		$arrAliasTabelas = array();
		if ($qtdeTabelas > 0){
			$fromQuery	= '  FROM ';

			foreach ($res as $k=>$v) {
				$flag++;
				$fromQuery .= $v->CD_TABELA . " " . $v->SG_TABELA;

				if ($flag < count($res)) {
					$fromQuery .= ', ';
				}
			}
		}

/**********************************
****** Montagem do WHERE **********
***********************************/
        
        // Monta as ligações das colunas
        $select = $db->select()
					 ->distinct()
					 ->from(array("CL" => "CONSULTA_LIGACAO_TABELA"), array("CL.CD_LIGACAO",
                                                                            "CL.CD_CONSULTA",
                                                                            "SG_TABELA_1" => "LTRIM(RTRIM(CL.SG_TABELA_1))",
                                                                            "CL.CD_COLUNA_1",
                                                                            "SG_TABELA_2" => "LTRIM(RTRIM(CL.SG_TABELA_2))",
                                                                            "CL.CD_COLUNA_2",
                                                                            "CL.TP_LIGACAO",
                                                                            "CL.OUTER_JOIN"))
					 ->where("CL.CD_CONSULTA = {$dadosRel['CD_CONSULTA']}");

        $resWhere = $db->fetchAll($select);

        $whereQuery = ' WHERE 1=1';

        foreach($resWhere as $whereLin) {
            // Monta a condição
            $whereQuery .= ' AND '. $whereLin->SG_TABELA_1 . '.' . $whereLin->CD_COLUNA_1 . ' = ' 
                                  . $whereLin->SG_TABELA_2 . '.' . $whereLin->CD_COLUNA_2;
            
            if($whereLin->OUTER_JOIN == "1") {
                $whereQuery .= ' (+) ';
            }
        }


	// Complementação do WHERE com os filtros cadastrados
		foreach ($arrParameter as $v) {
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
					->from(array("CC" => "CONSULTA_COLUNA_TABELA"), array("CC.CD_COL_TAB",
                                                                          "SG_TABELA" => "LTRIM(RTRIM(CC.SG_TABELA))",
                                                                          "CC.CD_COLUNA",
                                                                          "CC.SENTIDO_COLUNA"))
					->where("CC.CD_CONSULTA = {$dadosRel['CD_CONSULTA']} AND CC.ORD_COLUNA != 0")
					->order("CC.ORD_COLUNA ASC");

        $resOrder = $db->fetchAll($select);
        
		if (count($resOrder) > 0){
			
			foreach ($resOrder as $k=>$v){
				
                if(trim($v->SENTIDO_COLUNA) != "") {
                    if ($orderQuery != "") {
                        $orderQuery .= ', ';
                    }
                    
                    $orderQuery .= $v->SG_TABELA . '.' . $v->CD_COLUNA . ' ' . trim($v->SENTIDO_COLUNA);
                }
                
			}
            
            if($orderQuery != "") {
                $orderQuery	= ' ORDER BY ' . $orderQuery;
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
                     ->from(array("CT" => "CONSULTA_TABELA"), array("CC.CD_COL_TAB",
                                                                    "CC.CD_CONSULTA",
                                                                    "SG_TABELA" => "LTRIM(RTRIM(CC.SG_TABELA))",
                                                                    "CT.CD_TABELA",
                                                                    "TR.NO_TABELA",
                                                                    "CC.CD_COLUNA",
                                                                    "CO.NO_COLUNA",
                                                                    "CO.TP_COLUNA",
                                                                    "CC.DS_CABECALHO",
                                                                    "CC.TP_FORMATO",
                                                                    "CC.TAM_COLUNA",
                                                                    "CC.ORD_COLUNA",
                                                                    "CC.CD_TP_TOTAL"))
                     ->join(array("CC" => "CONSULTA_COLUNA_TABELA"), "CT.CD_CONSULTA = CC.CD_CONSULTA 
                                                      AND LTRIM(RTRIM(CT.SG_TABELA)) = LTRIM(RTRIM(CC.SG_TABELA))", array())
                     ->join(array("TR" => "TABELA_R"), "CT.CD_TABELA = TR.CD_TABELA", array())
                     ->join(array("CO" => "COLUNA"),   "CT.CD_TABELA = CO.CD_TABELA
                                                    AND CC.CD_COLUNA = CO.CD_COLUNA", array())
                     ->where("CC.CD_CONSULTA = {$dadosRel['CD_CONSULTA']}")
                     ->order("CC.ORD_COLUNA ASC");
                    
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

				fwrite($arq, utf8_encode('<field name="' . $v->CD_COLUNA . '_' . $v->CD_COL_TAB . '" class="java.' . $tp_coluna . '"/>'));

			}
		}

/***********************************
****** Montagem do VARIABLES *******
************************************/

		if (count($arrCalculation) > 0){

			for ($i=0; $i < count($arrVariables); $i++){

				$campo = $arrVariables[$i].'_'.$i;
                
                if($arrTipoCampos[$i] == "D") {
                    $classType = "java.util.Date";
                } else if($arrTipoCampos[$i] == "C" && $arrCalculation[$i] != "Count") {
                    $classType = "java.lang.String";
                } else {
                    $classType = "java.math.BigDecimal";
                }
                
				fwrite($arq, utf8_encode('<variable name="'.$campo.'" class="' . $classType . '" resetType="Report" calculation="' . $arrCalculation[$i] . '">
											<variableExpression><![CDATA[$F{' . $arrVariables[$i] . '}]]></variableExpression>
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
        
        $select = $db->select()
                     ->from(array("CT" => "CONSULTA_TABELA"), array("TITULO"      => new Zend_Db_Expr("CASE WHEN CC.DS_CABECALHO IS NOT NULL THEN CC.DS_CABECALHO ELSE CO.NO_COLUNA END"),
                                                                    "TAM_LARGURA" => new Zend_Db_Expr("CC.TAM_COLUNA * ".($pageWidth-$margem-1)." / 100"),
                                                                    "ALINHAMENTO" => new Zend_Db_Expr("CASE WHEN CO.TP_COLUNA IN('N','NUMERIC','SMALLINT','BIGINT','TINYINT','INT') THEN 'Right' ELSE 'Left' END")))
                     ->join(array("CC" => "CONSULTA_COLUNA_TABELA"), "CT.CD_CONSULTA = CC.CD_CONSULTA 
                                                      AND LTRIM(RTRIM(CT.SG_TABELA)) = LTRIM(RTRIM(CC.SG_TABELA))", array())
                     ->join(array("TR" => "TABELA_R"), "CT.CD_TABELA = TR.CD_TABELA", array())
                     ->join(array("CO" => "COLUNA"),   "CT.CD_TABELA = CO.CD_TABELA
                                                    AND CC.CD_COLUNA = CO.CD_COLUNA", array())
                     ->where("CC.CD_CONSULTA = {$dadosRel['CD_CONSULTA']}")
                     ->order("CC.ORD_COLUNA ASC");

        // Joga em um array o retorno da query
        $res = $db->query($select)->fetchAll();

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
					 ->from(array("CC" => "CONSULTA_COLUNA_TABELA"), array("CC.CD_COL_TAB",
                                                                           "CC.CD_COLUNA",
                                                                           "TC.TP_COLUNA",
                                                                           "TAM_ORIG"    => "CC.TAM_COLUNA",
                                                                           "TAM_LARGURA" =>  new Zend_Db_Expr("(CC.TAM_COLUNA * ".($pageWidth-$margem-1)." / 100)"),
                                                                           "ALINHAMENTO" => new Zend_Db_Expr("CASE WHEN TC.TP_COLUNA IN('N','NUMERIC','SMALLINT','BIGINT','TINYINT','INT') THEN 'Right' ELSE 'Left' END")))
					->join(array("CT" => "CONSULTA_TABELA"), "CC.CD_CONSULTA = CT.CD_CONSULTA 
                                              AND LTRIM(RTRIM(CC.SG_TABELA)) = LTRIM(RTRIM(CT.SG_TABELA))", array())
					->join(array("TC" => "COLUNA"), "CT.CD_TABELA = TC.CD_TABELA 
                                                 AND CC.CD_COLUNA = TC.CD_COLUNA", array())
                    ->where("CC.CD_CONSULTA = {$dadosRel['CD_CONSULTA']}")
					->order("CC.ORD_COLUNA ASC");

        $res = $db->fetchAll($select);

		if (count($res) > 0){
			$x = 0;

			$arrPosicoes = array();

			foreach ($res as $k=>$v){
				
				$arrPosicoes[$v->CD_COLUNA . '_' . $v->CD_COL_TAB] = array('x'=>$x, 'width'=>$v->TAM_LARGURA);
                
				if (strtolower($v->TP_COLUNA) == 'd' || strtolower($v->TP_COLUNA) == 'date' || strtolower($v->TP_COLUNA) == 'datetime') {
					$campoFormat = 'new SimpleDateFormat("dd/MM/yyyy").format($F{'.$v->CD_COLUNA . '_' . $v->CD_COL_TAB .'})';
				}else{
					$campoFormat = '$F{' . $v->CD_COLUNA . '_' . $v->CD_COL_TAB . '}';
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
								<textFieldExpression><![CDATA[$F{'.$v->CD_COLUNA . '_' . $v->CD_COL_TAB .'} == null ? '.$tipo.' : '.$campoFormat.']]></textFieldExpression>
							</textField>'));
                
				$x = ($x + $v->TAM_LARGURA);
			}

			fwrite($arq, utf8_encode('</band></detail>'));
		}

		/***************************************
		*** Montagem do COLUMNFOOTER *****
		****************************************/

		fwrite($arq, utf8_encode('<columnFooter><band height="45" splitType="Stretch">'));

		if (count($arrCalculation) > 0) {

			for ($i=0; $i < count($arrVariables); $i++){
				
				$campo = $arrVariables[$i].'_'.$i;
				
				$x     = 0;
				$width = 100;
				if(isset($arrPosicoes[$arrVariables[$i]])) {
					$x     = $arrPosicoes[$arrVariables[$i]]["x"];
					$width = $arrPosicoes[$arrVariables[$i]]["width"];
				}
                
                $labelCalculation = "";
                if($arrCalculation[$i] == "Sum") {
                    $labelCalculation = "Soma: ";
                } else if($labelCalculation = "Count") {
                    $labelCalculation = "Contagem: ";
                } else if($labelCalculation = "Highest") {
                    $labelCalculation = "Máximo: ";
                } else if($labelCalculation = "Lowest") {
                    $labelCalculation = "Mínimo: ";
                } else if($labelCalculation = "Average") {
                    $labelCalculation = "Média: ";
                }

				fwrite($arq, utf8_encode('<textField>
											<reportElement x="' . $x . '" y="14" width="' . ($width - 5) . '" height="20"/>
											<textElement textAlignment="Right"/>
											<textFieldExpression><![CDATA["' . $labelCalculation . '" + $V{'.$campo.'}]]></textFieldExpression>
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
