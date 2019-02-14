<?php
/**
 *
 * Classe responsável por executar os relatórios criados dinamicamente
 *
 *
 * @author     Bruno Teló
 * @category   Marca Sistemas
 * @copyright  Copyright (c) 1991-2012 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 */
class ConfiguracoesSistema_GeradorRelatorios2Controller extends Marca_Controller_Abstract_Operacao {

    /**
     * (non-PHPdoc)
     * @see library/Marca/Controller/Abstract/Marca_Controller_Abstract_Operacao#init()
     */
    public function init() {
        parent::init();

        // Carrega os modelos de dados
        Zend_Loader::loadClass("ConsultaModel");
		Zend_Loader::loadClass('ConsultaColunaTabelaModel');
        Zend_Loader::loadClass('ConsultaTipoFiltroModel');

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
    public function pesquisarAction() { 

        // Captura a sessão
        $sessao = new Zend_Session_Namespace('portoweb');

        // Recupera os parametros da requisição
        $params = $this->_request->getParams();

		// "Corrige" os valores dos parâmetros caso possuem caracteres acentuados
		foreach ($params as &$value) {
			$value = utf8_decode($value);
		}

        // Instancia a classe de transacoes web
        $transacoes = new ConsultaModel();

        // Retorna a query 
        $select = $transacoes->queryBuscaConsultas($params)->orderByList();

		// Executa a consulta
		$sessao->dadosPesquisa = $transacoes->fetchAll($select);
		
		// Seta o número da página corrente
		$pagina = $this->_getParam('pagina', 1);
		
		// Recebe a instância do paginator por singleton     
		$paginator = Zend_Paginator::factory($sessao->dadosPesquisa);
		
		// Define a página corrente
		$paginator->setCurrentPageNumber($pagina);
		
		// Define o total de linhas por página
		$paginator->setItemCountPerPage($sessao->perfil->QT_LINHAS);
		
		// Joga para a view a paginação
		$this->view->paginator = $paginator;
		
		// Reenvia os valores para o formulário
		$this->_helper->RePopulaFormulario->repopular($params);

		unset($transacoes);  
        
    }

    /**
     * Metodo selecionar
     * objetivo: utilizado para selecionar um registro a partir de uma listagem
     */
    public function selecionarAction() { 

	    // Captura a sessão
        $sessao = new Zend_Session_Namespace('portoweb');

        // Recupera os parametros da requisição
        $params = $this->_request->getParams();
        
        // Instancia os modelos de dados
        $consColuna	= new ConsultaColunaTabelaModel();
        $tpFiltro   = new ConsultaTipoFiltroModel();
        
        // Define os filtros para a cosulta
        $where = $consColuna->addWhere(array("C.CD_CONSULTA = ?" => $params['cd_consulta']))
                          ->getWhere();
        
        $select = $consColuna->select()
							->setIntegrityCheck(false)
                            ->from(array("TC" => "COLUNA"), array("TC.TP_COLUNA",
                                                                  "TC.TAMANHO",
                                                                  "TC.NO_COLUNA",
                                                                  "CC.CD_COND_COL",
                                                                  "CC.CD_CONSULTA",
                                                                  "SG_TABELA" => "LTRIM(RTRIM(CC.SG_TABELA))",
                                                                  "CC.CD_COLUNA",
                                                                  "CC.DS_COMPLEMENTO",
                                                                  "CC.ABRE_PAR",
                                                                  "CC.FECHA_PAR",
                                                                  "CC.TP_LIGACAO",
                                                                  "LIG_INT_PAR" => new Zend_Db_Expr("LTRIM(RTRIM(CC.LIG_INT_PAR))"),
                                                                  "LIG_EXT_PAR" => new Zend_Db_Expr("LTRIM(RTRIM(CC.LIG_EXT_PAR))"),
                                                                  "CC.ORD_COND",
                                                                  "C.TITULO",
                                                                  "CF.NO_TP_FILTRO",
                                                                  "CF.DS_TP_FILTRO"))
                            ->join(array("CC" => "CONSULTA_CONDICAO_COLUNA"), "TC.CD_COLUNA = CC.CD_COLUNA", array())
                            ->join(array("CT" => "CONSULTA_TABELA"), "TC.CD_TABELA = CT.CD_TABELA AND LTRIM(RTRIM(CC.SG_TABELA)) = LTRIM(RTRIM(CT.SG_TABELA))", array())
                            ->join(array("C"  => "CONSULTA"), "CT.CD_CONSULTA = C.CD_CONSULTA", array())
                            ->join(array("CF" => "CONSULTA_TIPO_FILTRO"), "CC.TP_LIGACAO = CF.CD_TP_FILTRO", array())
                            ->where($where)
                            ->order("CC.ORD_COND ASC");
        
        // Recupera a transação
        $res  = $consColuna->fetchAll($select);

		if (count($res) > 0) {
			// Reenvia os valores para o formulário
			$this->view->cd_consulta = $params['cd_consulta'];
			$this->view->cd_usuario = $sessao->perfil->CD_USUARIO;
			$this->view->titulo = $res[0]['TITULO'];

			$this->view->dados = $res;

		}else{

			// Retorna as transações, menus e sistemas
			$select = $consColuna->select()
								->setIntegrityCheck(false)
								->from(array("C" => "CONSULTA"), array("C.TITULO"))
								->where($where);

			// Recupera a transação
			$res  = $consColuna->fetchAll($select);

			$this->view->cd_consulta = $params['cd_consulta'];
			$this->view->dados = array();
			$this->view->cd_usuario = $sessao->perfil->CD_USUARIO;
			$this->view->titulo = $res[0]['TITULO'];

		}
        
        // Busca os filtros para ligação entre as colunas
        $selFiltros = $tpFiltro->queryBuscaFiltrosConsulta();
        
        // Executa a consulta
        $resConFiltro = $tpFiltro->fetchAll($selFiltros);
        
        // Joga para a os dados da consulta
        $this->view->filtros = $resConFiltro;
    }

    /**
     * Metodo relatorio
     * objetivo: utilizado para gerar um relatorio a partir de uma listagem
     */
    public function relatorioAction() { 
        // Recupera os parametros da requisição
        $params = $this->_request->getParams();
        
		$paramsRel = array();

		$paramsRel['_no_sistema'] 	 = 'gerador-relatorio';
        $paramsRel['_no_pasta_zend'] = 'gerador-relatorio';
		$paramsRel['sistema'] 		 = 'Gerador de Relatório';
		$paramsRel['usuario'] 		 = $params['cd_usuario'];
		$paramsRel['titulo'] 		 = $params['titulo'];
        
        //Zend_Debug::dump($params);
        
        // Se possuir parâmetros
        if(isset($params['ds_complemento'])) {
            
            $i=0;
            $andOr = false;
            
            foreach ($params['ds_complemento'] as $ds_complemento) {

                $abre_par     = $params['abre_par'][$i];
                $cd_cond_col  = $params['cd_cond_col'][$i];
                $sg_tabela    = $params['sg_tabela'][$i];
                $cd_coluna    = $params['cd_coluna'][$i];
                $lig_int_par  = $params['lig_int_par'][$i];
                $fecha_par    = $params['fecha_par'][$i];
                $lig_ext_par  = $params['lig_ext_par'][$i];
                $no_tp_filtro = $params['no_tp_filtro'][$i];
                $tp_coluna    = strtolower($params['tp_coluna'][$i]);
                
                $valorParameter = trim($abre_par) . " ";
                
                if (trim($ds_complemento) !== ""){
                    
                    if(trim(strtoupper($no_tp_filtro)) == "IS" || trim(strtoupper($no_tp_filtro)) == "IS NOT") {
                        $valorParameter .= $sg_tabela . '.' . $cd_coluna . ' ' . $no_tp_filtro . ' ' . trim($ds_complemento);
                        
                    } else if ($tp_coluna == 'd' || $tp_coluna == 'date' || $tp_coluna == 'datetime' || $tp_coluna == 'smalldatetime' || $tp_coluna == 'timestamp') {
                        if(trim(strtoupper($no_tp_filtro)) == "BETWEEN") {
							$matches = null;
							preg_match_all('/\d{2}\/\d{2}\/\d{4} \d{2}\:\d{2}/', substr($ds_complemento, 0, 16), $matches);
							if(count($matches[0]) == 2) {
								$valorParameter .= 'TO_DATE(TO_CHAR('.$sg_tabela . "." . $cd_coluna . ", 'DD/MM/YYYY HH24:MI'), 'DD/MM/YYYY HH24:MI') " . $no_tp_filtro . " TO_DATE('" . trim($matches[0][0]) . "', 'DD/MM/YYYY HH24:MI') AND  TO_DATE('" . trim($matches[0][1]) . "', 'DD/MM/YYYY HH24:MI')";
							} else {
								preg_match_all('/\d{2}\/\d{2}\/\d{4}/', $ds_complemento, $matches);
								if(count($matches[0]) == 2) {
									$valorParameter .= 'TO_DATE(TO_CHAR('.$sg_tabela . "." . $cd_coluna . ", 'DD/MM/YYYY'), 'DD/MM/YYYY') " . $no_tp_filtro . " TO_DATE('" . trim($matches[0][0]) . "', 'DD/MM/YYYY') AND  TO_DATE('" . trim($matches[0][1]) . "', 'DD/MM/YYYY')";
								}
							}
                        } else {
							$matches = null;
							preg_match_all('/\d{2}\/\d{2}\/\d{4} \d{2}\:\d{2}/', substr($ds_complemento, 0, 16), $matches);
							if(isset($matches[0][0]) && $matches[0][0] != "" && strlen($matches[0][0]) == 16) {
								$valorParameter .= 'TO_DATE(TO_CHAR('.$sg_tabela . "." . $cd_coluna . ", 'DD/MM/YYYY HH24:MI'), 'DD/MM/YYYY HH24:MI') " . $no_tp_filtro . " TO_DATE('" . trim($ds_complemento) . "', 'DD/MM/YYYY HH24:MI')";
							} else {
								$valorParameter .= 'TO_DATE(TO_CHAR('.$sg_tabela . "." . $cd_coluna . ", 'DD/MM/YYYY'), 'DD/MM/YYYY') " . $no_tp_filtro . " TO_DATE('" . trim($ds_complemento) . "', 'DD/MM/YYYY')";
							}
                        }
                        
                    } else if(strtolower($tp_coluna) == 'c' || strtolower($tp_coluna) == 'text' || strtolower($tp_coluna) == 'char' || strtolower($tp_coluna) == 'varchar') {
                        // Se for entre aspas e o filtro for igual, muda para like
                        if($no_tp_filtro == "=") {
                            $no_tp_filtro = "LIKE";
                        } else if($no_tp_filtro == "<>") {
                            $no_tp_filtro = "NOT LIKE";
                        }

                        // Converte asterisco por porcento
                        $ds_complemento = str_replace("*", "%", $ds_complemento);

                        $valorParameter .= $sg_tabela . '.' . $cd_coluna . ' ' . $no_tp_filtro . " '" . trim($ds_complemento) . "'";
                        
                    } else {
                        $valorParameter .= $sg_tabela . '.' . $cd_coluna . ' ' . $no_tp_filtro . ' ' . trim($ds_complemento);
                    }
                    
                }else{
                    $valorParameter .= '1=1';
                }

                $valorParameter .= " " . trim($lig_int_par) . " " . trim($fecha_par) . " " . trim($lig_ext_par);
                
                if($andOr === false) {
                    $valorParameter = " AND " . $valorParameter;
                }

                if(trim($lig_int_par) != "" || trim($lig_ext_par) != "") {
                    $andOr = true;
                } else {
                    $andOr = false;
                }
                
                $indice = strtolower($cd_coluna) . "_" . $cd_cond_col;
                $paramsRel[$indice] = $valorParameter;
                
                $i++;
            }
        }

        //Zend_Debug::dump($paramsRel); die;
        
		$v1 = array("(", ")", "'", "\"", "/", "\\");
		$v2 = array("__par1__", "__par2__", "__apos__", "__aspas__", "__barra__", "__cbarra__");
		foreach($paramsRel as $indice => $valor) {
			$paramsRel[$indice] = str_replace($v1, $v2, $valor);
		}

		// Chama o método relatório da classe pai
		$this->_helper->GeraRelatorioJasper->gerar('Rel' . $params['cd_consulta'], $paramsRel, $params['_rel_metodo']);
    }

}
