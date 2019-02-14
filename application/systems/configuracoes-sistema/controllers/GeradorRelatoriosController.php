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
class ConfiguracoesSistema_GeradorRelatoriosController extends Marca_Controller_Abstract_Operacao {

    /**
     * (non-PHPdoc)
     * @see library/Marca/Controller/Abstract/Marca_Controller_Abstract_Operacao#init()
     */
    public function init() {
        parent::init();

        // Carrega os modelos de dados
        Zend_Loader::loadClass("ConsultaModel");
		Zend_Loader::loadClass('ConsultaColunaModel');

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
        $consColuna	= new ConsultaColunaModel();
        
        // Define os filtros para a cosulta
        $where = $consColuna->addWhere(array("CCL.CD_CONSULTA = ?" => $params['cd_consulta']))
                          ->getWhere();
        
        // Retorna as transações, menus e sistemas
        $select = $consColuna->select()
							->setIntegrityCheck(false)
							->from(array("CCL" => "CONSULTA_COLUNA"), array(	"CCF.CD_CONS_FILTRO", "TCL.DS_COLUNA", "CCL.DS_CABECALHO", 
																				"TCL.TP_COLUNA", "TCL.TAMANHO", "CTF.NO_TP_FILTRO", "CCL.CD_COLUNA",
																				"CTF.DS_TP_FILTRO", "CCL.CD_TABELA", "CCL.ALIAS_TABELA",
																				"CCF.DS_FILTRO", "C.TITULO", "CCL.CD_CONS_COLUNA"))
							->join(array("C"   => "CONSULTA"), "CCL.CD_CONSULTA = C.CD_CONSULTA", array())
							->join(array("CCF" => "CONSULTA_COLUNA_FILTRO"), "CCL.CD_CONS_COLUNA = CCF.CD_CONS_COLUNA", array())
							->join(array("CTF" => "CONSULTA_TIPO_FILTRO"), "CCF.CD_TP_FILTRO = CTF.CD_TP_FILTRO", array())
							->join(array("TCL" => "COLUNA"), "CCL.CD_TABELA = TCL.CD_TABELA AND CCL.CD_COLUNA = TCL.CD_COLUNA", array())
							->where($where);

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
								->from(array("CCL" => "CONSULTA"), array("CCL.TITULO"))
								->where($where);

			// Recupera a transação
			$res  = $consColuna->fetchAll($select);

			$this->view->cd_consulta = $params['cd_consulta'];
			$this->view->dados = array();
			$this->view->cd_usuario = $sessao->perfil->CD_USUARIO;
			$this->view->titulo = $res[0]['TITULO'];

		}
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

        // Se possuir parâmetros
        if(isset($params['ds_filtro'])) {
            $i=0;

            foreach ($params['ds_filtro'] as $v) {

                $tp_coluna 		= strtolower(trim($params['tp_coluna'][$i]));
                $parametro 		= $params['parametro'][$i];
                $operador		= $params['operador'][$i];
                $tabela_coluna	= $params['tabela_coluna'][$i];

                if (trim($v) == ''){
					/*
					$v = 'NULL';
					if ($operador == '=') {
						$operador = 'IS';
					}elseif ($operador == '<>'){
						$v = 'IS NOT';
					}
					*/
                    $paramsRel[$parametro] = ' ';
                }else{
                    if ($tp_coluna == 'd' || $tp_coluna == 'date' || $tp_coluna == 'datetime' || $tp_coluna == 'smalldatetime' || $tp_coluna == 'timestamp') {
                        $paramsRel[$parametro] = " AND TO_DATE(" . $tabela_coluna . ") ". $operador  ." TO_DATE('" . trim($v) . "', 'DD/MM/YYYY')";
                    }else if ($tp_coluna == 'n' || $tp_coluna == 'numeric' || $tp_coluna == 'tinyint' || $tp_coluna == 'smallint' || $tp_coluna == 'bigint') {
                        $paramsRel[$parametro] = ' AND '. $tabela_coluna.' '.$operador.' '.trim($v);
                    }else {
                        $paramsRel[$parametro] = ' AND '. $tabela_coluna.' '.$operador." '".trim($v)."'";
                    }
                }

                $i++;
            }
        }

		$v1 = array("(", ")", "'", "\"", "/", "\\");
		$v2 = array("__par1__", "__par2__", "__apos__", "__aspas__", "__barra__", "__cbarra__");
		foreach($paramsRel as $indice => $valor) {
			$paramsRel[$indice] = str_replace($v1, $v2, $valor);
		}

		// Chama o método relatório da classe pai
		$this->_helper->GeraRelatorioJasper->gerar('Rel' . $params['cd_consulta'], $paramsRel, $params['_rel_metodo']);
    }

}
