<?php
/**
 * Esta classe tem como objetivo manipular os dados da Aba de Tabelas, da tela de cria��o de relat�rios.
 *
 * @author     Bruno Tel�
 * @category   Marca Sistemas
 * @copyright  Copyright (c) 1991-2012 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 */
class ConfiguracoesSistema_TabConsultaTabelasController extends Marca_Controller_Abstract_Operacao {
    
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
		Zend_Loader::loadClass('ConsultaColunaModel');
		Zend_Loader::loadClass('ConsultaColunaFiltroModel');
        Zend_Loader::loadClass('ConsultaConexaoTabelaModel');
        Zend_Loader::loadClass('AllConstraintsModel');

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
        $tabelas = new TabelaRModel();
        
        // Captura o c�digo da transa��o pai
        $pai_cd_consulta = Zend_Registry::get("pai_cd_consulta");

        // Define os filtros para a cosulta
        $where = $tabelas->addWhere(array("CC.CD_CONSULTA = ?" => $pai_cd_consulta))
                         ->getWhere();

        // Busca todas as transa��es ligadas as a��es para a aba tab-transacao-acao
        $select = $tabelas->select()
                          ->setIntegrityCheck(false)
                          ->from(array("TB" => "TABELA_R"), array("CC.CD_CONSULTA",
                                                                  "CC.ALIAS_TABELA",
																  "TB.CD_TABELA", 
															      "TB.NO_TABELA",
																  "TB.DS_TABELA"))
                          ->join(array("CC" => "CONSULTA_COLUNA"), "CC.CD_TABELA = TB.CD_TABELA", array())
                          ->where($where)
						  ->group(array("CC.CD_CONSULTA", "CC.ALIAS_TABELA", "TB.CD_TABELA", "TB.NO_TABELA", "TB.DS_TABELA"))
                          ->order(array("CC.ALIAS_TABELA ASC"))
						  ->orderByList(); // Permite ordenar a consulta pela listagem

        // Define os par�metros para a consulta e retorna o resultado da pesquisa
        $resConsulta = $tabelas->fetchAll($select);

        // Seta o n�mero da p�gina corrente
        $pagina = $this->_getParam('pagina', 1);

        // Recebe a inst�ncia do paginator por singleton
        $paginator = Zend_Paginator::factory($resConsulta);

        // Define a p�gina corrente
        $paginator->setCurrentPageNumber($pagina);

        // Define o total de linhas por p�gina
        $paginator->setItemCountPerPage($sessao->perfil->QT_LINHAS);

        // Joga para a view a pagina��o
        $this->view->paginator = $paginator;
    }


    /**
     * Gera um documento para cadastro de um novo aditivo
     *
     * @return void
     */
    public function novoAction(){

        // Recupera os parametros da requisi��o
        $params = $this->_request->getParams();

        // Instancia a classe das tabelas
        $consColuna = new ConsultaColunaModel();

        // Captura o c�digo da transa��o pai
        $pai_cd_consulta = Zend_Registry::get("pai_cd_consulta");

       // Busca porcentagem das colunas j� cadastradas
        $where = $consColuna->addWhere(array("CC.CD_CONSULTA = ?" => $pai_cd_consulta))
                            ->getWhere();

        // Busca o somat�rio total de colunas e o pr�ximo alias da nova tabela que ser� refer�nciada
        $select = $consColuna->select()
                             ->setIntegrityCheck(false)
                             ->from(array("CC" => "CONSULTA_COLUNA"), array("TAM_LARGURA"       => new Zend_Db_Expr("NVL(SUM(CC.TAM_LARGURA), 0)"),
                                                                            "ALIAS_TABELA_NOVA" => new Zend_Db_Expr("'T' || TO_CHAR(NVL(REGEXP_SUBSTR(MAX(ALIAS_TABELA), '\d+'), 0) + 1)")))
                             ->where($where);

        // Define os par�metros para a consulta e retorna o resultado da pesquisa
        $resConsulta = $consColuna->fetchRow($select);

        // Joga para a view novo
		$this->view->fl_possui_tabs    = $params['fl_possui_tabs'];
		$this->view->perc_colunas      = $resConsulta['TAM_LARGURA'];
        $this->view->alias_tabela_nova = $resConsulta['ALIAS_TABELA_NOVA'];

    }

    
    /**
     * Salva uma a��o a transa��o
     *
     * @return void
     */
    public function salvarAction() {

        // Recupera os parametros da requisi��o
        $params = $this->_request->getParams();
        //Zend_Debug::dump($params); die;
        
        // Instancia o modelo
        $consColuna 	= new ConsultaColunaModel();
		$colunaFiltro	= new ConsultaColunaFiltroModel();
        $consConTabRef  = new ConsultaConexaoTabelaModel();
		
        // Captura o c�digo da transa��o pai
        $pai_cd_consulta = Zend_Registry::get("pai_cd_consulta");

		// Monta a condi��o do where para excluir os filtros das colunas dessa consulta
		$where = "CD_CONSULTA      =  " . $pai_cd_consulta                       . "  AND " .
				 "UPPER(CD_TABELA) = '" . strtoupper(trim($params["cd_tabela"])) . "' AND " . 
                 "ALIAS_TABELA     = '" . trim($params["alias_tabela_nova"])     . "'";

		$cdColunas = $consColuna->fetchAll($where)->toArray();
        
        try {
            // Exclui os filtros
            $cdConsColuna = "";
            foreach ($cdColunas as $k => $v) {
                if($cdConsColuna != "") {
                    $cdConsColuna .= ",";
                }
                
                $cdConsColuna .= $v['CD_CONS_COLUNA'];
            }
            
            if($cdConsColuna != "") {
                $whereFiltro = "CD_CONS_COLUNA IN (" . $cdConsColuna . ")";
                $colunaFiltro->delete($whereFiltro);
            }
            
        } catch(Zend_Exception $e) {
            echo "Erro: " . $e->getMessage(); die;
        }

        try {
            // Exclui as colunas para serem re-inseridas
            $consColuna->delete($where);
            
        } catch(Zend_Exception $e) {
            echo "Erro: " . $e->getMessage(); die;
        }
        
        try {
            // Remove a refer�ncia da conex�o entre as tabelas
            $whereConexao = "CD_CONSULTA  =  " . $pai_cd_consulta                   . "  AND " .
                            "ALIAS_TABELA = '" . trim($params["alias_tabela_nova"]) . "'";

            $consConTabRef->delete($whereConexao);
            
        } catch(Zend_Exception $e) {
            echo "Erro: " . $e->getMessage(); die;
        }
        
        // Se retornar um array � porque foi editado uma coluna da tabela, portanto deve gravar.
        if(is_array($params["fl_mostra"])) {
                
			// Conta quantas colunas foram acrescentadas ao relat�rio
			$totIndice = count($params["fl_mostra"]);

			// Percorre todas as a��es
			for($i=0; $i < $totIndice; $i++) {

				// Verifica se o c�digo � v�lido
				if($params["fl_mostra"][$i] == 1) {

					// Incrementa a sequence da tabela
					$cd_cons_coluna = $consColuna->nextVal();
                    
					// Monta os dados para salvar
					$dados = array("CD_CONS_COLUNA"	=> (int) $cd_cons_coluna,
								   "CD_CONSULTA" 	=> $pai_cd_consulta,
								   "CD_TABELA" 		=> $params["cd_tabela"],
								   "CD_COLUNA" 		=> $params["cd_coluna"][$i],
                                   "DS_CABECALHO"   => $params["no_coluna"][$i],
								   "FL_MOSTRA"   	=> 1,
								   "DS_CABECALHO"	=> $params["ds_cabecalho"][$i],
								   "NR_POSICAO"		=> $params["nr_posicao"][$i],
								   "TAM_LARGURA"	=> $params["tam_largura"][$i],
								   "NR_ORDEM"		=> $params["nr_ordem"][$i],
								   "FL_SOMA"		=> $params["fl_soma"][$i],
                                   "FL_VISIVEL"  	=> $params["fl_visivel"][$i],
                                   "ALIAS_TABELA"   => $params["alias_tabela_nova"]);
                    
					// Valida os dados obrigat�rios
					if($consColuna->isValid($dados)) {
                        
						// Insere a nova a��o
						$retorno = $consColuna->insert($dados);
                        
                        if($retorno) {
                            
                            // Verifica se o filtro foi selecionado
                            if($params["fl_filtro"][$i] == 1) {

                                for ($i_filtro=0; $i_filtro < count($params["cd_tp_filtro_".$i]); $i_filtro++) {
									echo trim($params["ds_filtro_".$i][$i_filtro]) .'</br>';
									if (trim($params["ds_filtro_".$i][$i_filtro]) == ''){
										$params["ds_filtro_".$i][$i_filtro] = ' ';
									}

                                    // Monta os dados para salvar o filtro
                                    $dados3 = array("CD_CONS_FILTRO" => (int) $colunaFiltro->nextVal(),
                                                    "CD_CONS_COLUNA" => $cd_cons_coluna,
                                                    "CD_TP_FILTRO"   => $params["cd_tp_filtro_".$i][$i_filtro],
                                                    "DS_FILTRO"      => $params["ds_filtro_".$i][$i_filtro]);

													
                                    // Valida os dados obrigat�rios
                                    if($colunaFiltro->isValid($dados3)) {
                                        // Insere a nova a��o
                                        $colunaFiltro->insert($dados3);
                                    }

                                }

                            }
                            
                        }
					} else {
                        echo "<br />- Dados Inv�lidos.";
                    }
                    
				}

			}
            
            // Insere as conex�es
            if($retorno) {
                // A primeira tabela n�o possui conex�o com outra.
                if($params["alias_tabela_nova"] != "T1") {
                    // Insere a conex�o da tabela
                    $dados2 = array("CD_CONSULTA" 	   => $pai_cd_consulta,
                                    "ALIAS_TABELA" 	   => $params["alias_tabela_nova"],
                                    "ALIAS_TABELA_REF" => $params["alias_tabela_ref"],
                                    "FK_TABELA"        => $params["fk_tabela"]);
                    
                    $consConTabRef->insert($dados2);
                }
            }
			
			// Define que foi salvo o documento para chamar o 
			// evento da janela pai
			$this->view->controleSalvar = 1;
			
			// Limpa os dados da requisi��o
			$params = $this->_helper->LimpaParametrosRequisicao->limpar();
			// Redireciona para a��o novo
			$this->_forward('index');

		} 

    }


    /**
     * Exclui um aditivo selecionado de um contrato
     *
     * @return void
     */
    public function excluirAction(){
        
        // Captura a classe de mensagens do sistema
        $mensagemSistema = Zend_Registry::get("mensagemSistema");
        
        // Recupera os parametros da requisi��o
        $params = $this->_request->getParams();

        // Instancia os modelos
        $consColuna    = new ConsultaColunaModel();
		$colunaFiltro  = new ConsultaColunaFiltroModel();
        $consConTabRef = new ConsultaConexaoTabelaModel();

        // Captura o c�digo da transa��o pai
		$pai_cd_consulta = Zend_Registry::get("pai_cd_consulta");
        
        // Monta a condi��o do where para verificar se nenhuma outra tabela depende desta
        $whereConexoes = "CD_CONSULTA      =  " . $pai_cd_consulta                   . "  AND " .
                         "ALIAS_TABELA_REF = '" . trim($params["alias_tabela_nova"]) . "'";

        $conexoes = $consConTabRef->fetchAll($whereConexoes);

        // Se existirem tabelas dependentes n�o deixa excluir
        if(count($conexoes) > 0) {
            
            // Monta a mensagem de retorno
			$msg = array("msg"    => array("N�o � poss�vel excluir a tabela pois existem outras tabelas dependentes."),
                         "titulo" => "ATEN��O",
                         "tipo"   => 4);
			$mensagemSistema->send(serialize($msg));
            
            // Se n�o conseguir excluir, retorna pra sele��o do registro
            $this->_forward("selecionar");
            
        } else if($pai_cd_consulta != "" && $params['cd_tabela'] != "" && $params["alias_tabela_nova"] != "") { // Valida os dados obrigat�rios

			// Monta a condi��o do where para exlcuir os filtros das colunas dessa consulta
			$where = "CD_CONSULTA      =  " . $pai_cd_consulta                       . "  AND " .
                     "UPPER(CD_TABELA) = '" . strtoupper(trim($params["cd_tabela"])) . "' AND " . 
                     "ALIAS_TABELA     = '" . trim($params["alias_tabela_nova"])     . "'";
            
            $cdColunas = $consColuna->fetchAll($where)->toArray();

            $whereFiltro = "";
			foreach ($cdColunas as $k=>$v) {
                if($whereFiltro != "") {
                    $whereFiltro .= ",";
                }
                
				$whereFiltro .= $v['CD_CONS_COLUNA'];
			}
            
            // Exclui os filtros das colunas
            if($whereFiltro != "") {
                $colunaFiltro->delete("CD_CONS_COLUNA IN (" . $whereFiltro . ")");
            }

            // Deleta as conex�es
            $consConTabRef->delete("ALIAS_TABELA = '".strtoupper(trim($params["alias_tabela_nova"]))."' AND CD_CONSULTA = {$pai_cd_consulta}");

			// Deleta as colunas
			$delete = $consColuna->delete("UPPER(CD_TABELA) = '".strtoupper(trim($params["cd_tabela"]))."' AND CD_CONSULTA = {$pai_cd_consulta} AND ALIAS_TABELA = '".strtoupper(trim($params["alias_tabela_nova"]))."'");

            // Verifica se o registro foi exclu�do
            if($delete) {

                // Define que foi excluido o documento para chamar o 
                // evento da janela pai
                $this->view->controleSalvar = 1;

                // Limpa os dados da requisi��o
                $params = $this->_helper->LimpaParametrosRequisicao->limpar();

                // Redireciona para o index
                $this->_forward("index", null, null, $params);

            } else {

                // Monta a mensagem de retorno
                $msg = array("msg"    => array("Erro ao excluir."),
                             "titulo" => "ATEN��O",
                             "tipo"   => 4);
                $mensagemSistema->send(serialize($msg));
                
                // Se n�o conseguir excluir, retorna pra sele��o do registro
                $this->_forward("selecionar");
            }
            
        } else {
            
            // Monta a mensagem de retorno
            $msg = array("msg"    => array("Erro ao excluir."),
                         "titulo" => "ATEN��O",
                         "tipo"   => 4);
            $mensagemSistema->send(serialize($msg));
            
            // Se n�o conseguir excluir, retorna pra sele��o do registro
            $this->_forward("selecionar");
        }

    }


    /**
     * Seleciona um aditivo referente a um contrato
     * @return void
     */
    public function selecionarAction() {

        // Recupera os parametros da requisi��o
        $params = $this->_request->getParams();
                
        // Instancia a classe de sistemas web
        $tabelas    = new TabelaRModel();
		$consColuna = new ConsultaColunaModel();

        // Captura o c�digo da transa��o pai
        $pai_cd_consulta = Zend_Registry::get("pai_cd_consulta");

        // Define os filtros para a cosulta
        $where1 = $tabelas->addWhere(array("CC.CD_CONSULTA = ?" => $pai_cd_consulta))
                         ->getWhere();

        // Busca os dados para serem mostrados na view
        $select1 = $tabelas->select()
						   ->distinct()
                           ->setIntegrityCheck(false)
                           ->from(array("TB" => "TABELA_R"), array("CC.CD_CONSULTA",
                                                                   "CC.ALIAS_TABELA",
													  		       "TB.CD_TABELA", 
															       "TB.NO_TABELA",
															       "TB.DS_TABELA"))
                           ->join(array("CC" => "CONSULTA_COLUNA"), "CC.CD_TABELA = TB.CD_TABELA", array())
                           ->where($where1);

        // Joga em um array todas as a��es
        $arrayTabelas = $tabelas->fetchAll($select1);

		// Joga para a view as tabelas j� vinculadas � consulta
        $this->view->tabsVinculadas = $arrayTabelas; 

        // Define os filtros para a cosulta
        $where2 = $tabelas->addWhere(array("CC.CD_CONSULTA  = ?" => $pai_cd_consulta))
						  ->addWhere(array("CC.CD_TABELA    = ?" => $params['cd_tabela']))
                          ->addWhere(array("CC.ALIAS_TABELA = ?" => $params['alias_tabela']))
                          ->getWhere();

        $select2 = $tabelas->select()
                           ->distinct()
                           ->setIntegrityCheck(false)
                           ->from(array("TB" => "TABELA_R"), array("CC.CD_CONSULTA",
                                                                   "CC.ALIAS_TABELA",
																   "TB.CD_TABELA", 
															       "TB.NO_TABELA",
																   "TB.DS_TABELA"))
                           ->join(array("CC" => "CONSULTA_COLUNA"), "CC.CD_TABELA = TB.CD_TABELA", array())
                           ->where($where2);

        // Define os par�metros para a consulta e retorna o resultado da pesquisa
        $linhaConsulta = $tabelas->fetchRow($select2)->toArray();
        $linhaConsulta["ALIAS_TABELA_NOVA"] = $linhaConsulta["ALIAS_TABELA"];
        
        // Busca a consulta pai
        $where3 = $consColuna->addWhere(array("CC.CD_CONSULTA = ?" => $pai_cd_consulta))
                             ->getWhere();

        // Busca porcentagem das colunas j� cadastradas
        $select3 = $consColuna->select()
                             ->setIntegrityCheck(false)
                             ->from(array("CC" => "CONSULTA_COLUNA"), array("SUM(CC.TAM_LARGURA) AS TAM_LARGURA"))
                             ->where($where3);

        // Define os par�metros para a consulta e retorna o resultado da pesquisa
        $resConsulta = $consColuna->fetchRow($select3);

		$this->view->perc_colunas     = $resConsulta['TAM_LARGURA'];

        // Reenvia os valores para o formul�rio
        $this->_helper->RePopulaFormulario->repopular($linhaConsulta, "lower");

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
    

	public function autoCompleteTabelaXAction(){
	
		// Verifica se a requisi��o foi passada por Ajax
		if($this->_request->isXmlHttpRequest()) {
			
			// Captura os parametros passados por GET
			$params = $this->getRequest()->getParams();
			 
			// Instancia o modelo
            $tabelas        = new TabelaRModel();
            $allConstraints = new AllConstraintsModel();
			
			try {

				$retornoJSON = array();

				// Se N�O existe tabela j� cadastrada
				if ($params["fl_possui_tabs"] == 0) {
					// Define os filtros para a consulta
					//captura a linha
					$where = $tabelas->addWhere(array("UPPER(TB.CD_TABELA || TB.NO_TABELA || TB.DS_TABELA) LIKE ?" => '%'.trim(strtoupper($params["term"])).'%'))
									 ->getWhere();

					// Monta a busca
					$select = $tabelas->select()
                                      ->setIntegrityCheck(false)
                                      ->from(array("TB" => "TABELA_R"), array("TB.CD_TABELA", 
                                                                              "TB.NO_TABELA", 
                                                                              "TB.DS_TABELA"))        				 
                                      ->where($where)
                                      ->order("TB.NO_TABELA ASC");

					$linhas = $tabelas->fetchAll($select);
							
				// Se J� existe tabela cadastrada
				} else {
                    
                    // Monta a condi��o de busca
                    $dadosConstraints = array("cd_consulta" => $params["cd_consulta"],
                                              "no_tabela"   => $params["term"]);
                    
                    // Busca as tabelas que possuem liga��o com a tabela passada
                    $queryConstraints  = $allConstraints->queryBuscaLigacaoTabelas($dadosConstraints);
                    
                    // Executa a busca
                    $linhas = $allConstraints->fetchAll($queryConstraints);
                    
				}
                
				if(count($linhas) > 0) {
					foreach($linhas as $linha) {
						$value     = $linha->NO_TABELA; //preenche o campo do autocomplete somente com O NO_TABELA ao selecionar uma op��o da lista 
						$label     = $linha->NO_TABELA; // valor exibido na listagem do autocomplete
						$cd_tabela = trim($linha->CD_TABELA); //dado para preenchimento do outro campo
						$ds_tabela = trim($linha->DS_TABELA); //dado para preenchimento do outro campo
						 
						$retornoJSON[] = array("value"     => $value,
											   "label"     => $label,
											   "cd_tabela" => $cd_tabela,
											   "ds_tabela" => $ds_tabela
						);
					}
				}
								
				$this->_helper->json(Marca_ConverteCharset::converter($retornoJSON), true);

			} catch(Exception $e) {
				echo $e->getMessage(); die;
				//echo false;
			}

			// Limpa os objetos da memoria
			unset($tabelas);
		}
	}


	public function retornaColunasTabelaXAction(){
	
		// Verifica se a requisi��o foi passada por Ajax
		if($this->_request->isXmlHttpRequest()) {

			// Captura o c�digo da transa��o pai
			$pai_cd_consulta = Zend_Registry::get("pai_cd_consulta");
		
			// Captura os parametros passados por GET
			$params = $this->getRequest()->getParams();

			// Associa as vari�veis do banco
			$db = Zend_Registry::get('db');

			// Instancia o modelo
            $tabColunas = new ColunaModel();
			$consColuna = new ConsultaColunaModel();

			try {

				$retornoJSON = array();


				// Monta o where da query abaixo
				$wherePos = $consColuna->addWhere(array("CC.CD_CONSULTA   = ?" => $pai_cd_consulta))
									   ->addWhere(array("CC.ALIAS_TABELA != ?" => $params["alias_tabela"]))
									   ->getWhere();

				// Busca posi��es j� cadastradas para montar array
				$selectPos = $consColuna->select()
									 ->setIntegrityCheck(false)
									 ->from(array("CC" => "CONSULTA_COLUNA"), array("NR_POSICAO AS NR_POSICAO"))
									 ->where($wherePos);
									 
				// Define os par�metros para a consulta e retorna o resultado da pesquisa
				$resConsultaPos = $consColuna->fetchAll($selectPos)->toArray();

				// Define os filtros para a consulta
				//captura a linha
				$where = $tabColunas->addWhere(array("UPPER(TC.CD_TABELA) = ?"	=> trim(strtoupper($params["cd_tabela"]))))
									->getWhere();

				// Monta a busca
				$select = $tabColunas->select()
						->setIntegrityCheck(false)
						->from ( array ("TC" => "COLUNA"), array("CC.FL_MOSTRA", "CC.DS_CABECALHO", "CC.NR_POSICAO",
																 "CC.TAM_LARGURA", "CC.NR_ORDEM", "CC.FL_SOMA", 
																 "TC.NO_COLUNA", "CC.CD_CONS_COLUNA", "TC.CD_COLUNA", 
																 "CCF.FL_FILTRO", "TC.TP_COLUNA", "CC.FL_VISIVEL"))
						->joinleft(array("CC" => "CONSULTA_COLUNA"), "CC.CD_TABELA = TC.CD_TABELA AND CC.CD_COLUNA = TC.CD_COLUNA 
                                                                  AND CC.CD_CONSULTA = {$pai_cd_consulta} AND CC.ALIAS_TABELA = '{$params["alias_tabela"]}'", array())
						->joinleft(array("CCF" => $db->select()
													->from(array(" CCF2" => "CONSULTA_COLUNA_FILTRO"), array("CCF2.CD_CONS_COLUNA", "COUNT(*) AS FL_FILTRO"))
													->group ("CCF2.CD_CONS_COLUNA")), "CCF.CD_CONS_COLUNA = CC.CD_CONS_COLUNA", array())
						->where($where);

				$linhas = $tabColunas->fetchAll($select);

				if(count($linhas) > 0) {
					foreach($linhas as $linha) {
						$retornoJSON[] = array(	
											"cd_cons_coluna" 	=> $linha->CD_CONS_COLUNA,
											"posicoes_ja_cads"	=> $resConsultaPos,
											"fl_mostra"      	=> $linha->FL_MOSTRA,
											"cd_coluna"      	=> $linha->CD_COLUNA,
                                            "no_coluna"      	=> $linha->NO_COLUNA,
											"tp_coluna"     	=> $linha->TP_COLUNA,
											"ds_cabecalho"   	=> $linha->DS_CABECALHO,
											"nr_posicao"     	=> $linha->NR_POSICAO,
											"tam_largura"    	=> $linha->TAM_LARGURA,
											"nr_ordem"       	=> $linha->NR_ORDEM,
											"fl_soma"        	=> $linha->FL_SOMA,
											"fl_filtro"      	=> $linha->FL_FILTRO,
                                            "fl_visivel"     	=> $linha->FL_VISIVEL
										);
					}
				}

				$this->_helper->json(Marca_ConverteCharset::converter($retornoJSON), true);

			} catch(Exception $e) {
				//echo $e->getMessage(); die;
				echo false;
			}

			// Limpa os objetos da memoria
			unset($tabColunas);
		}
	}

	public function retornaColunasFiltroXAction(){
	
		// Verifica se a requisi��o foi passada por Ajax
		if($this->_request->isXmlHttpRequest()) {
			
			// Captura os parametros passados por GET
			$params = $this->getRequest()->getParams();

			// Associa as vari�veis do banco
			$db = Zend_Registry::get('db');

			// Instancia o modelo
            $tabColunas = new ColunaModel();

			try {

				$retornoJSON = array();

				// Define os filtros para a consulta
				//captura a linha
				$where = $tabColunas->addWhere(array("CCF.CD_CONS_COLUNA = ?" => trim($params["cd_cons_coluna"])))
									->getWhere();

				// Monta a busca
				$select = $tabColunas->select()
						->setIntegrityCheck(false)
						->from ( array ("CCF" => "CONSULTA_COLUNA_FILTRO"), array("CCF.CD_CONS_COLUNA", "CCF.CD_CONS_FILTRO", 
																				  "CCF.CD_TP_FILTRO", "DS_FILTRO"))
						->where ($where);

				$linhas = $tabColunas->fetchAll($select);

				if(count($linhas) > 0) {
					foreach($linhas as $linha) {
						$retornoJSON[] = array(	
											"cd_cons_coluna" => $linha->CD_CONS_COLUNA,
											"cd_cons_filtro" => $linha->CD_CONS_FILTRO,
											"cd_tp_filtro" 	 => $linha->CD_TP_FILTRO,
											"ds_filtro" 	 => $linha->DS_FILTRO
										);
					}
				}

				$this->_helper->json(Marca_ConverteCharset::converter($retornoJSON), true);

			} catch(Exception $e) {
				//echo $e->getMessage(); die;
				echo false;
			}

			// Limpa os objetos da memoria
			unset($tabColunas);
		}
	}
    
    /**
     * Retorna as conex�es entre as tabelas "FK" do banco e compara
     * com as FK que j� foram cadastradas.
     */
    public function retornaConexoesTabelasXAction(){
	
		// Verifica se a requisi��o foi passada por Ajax
		if($this->_request->isXmlHttpRequest()) {
			
			// Captura os parametros passados por GET
			$params = $this->getRequest()->getParams();

			// Associa as vari�veis do banco
			$db = Zend_Registry::get('db');

			// Instancia o modelo
            $consColuna    = new ConsultaColunaModel();
            $consConTabRef = new ConsultaConexaoTabelaModel();

			try {

                // Se for a partir da segunda tabela selecionada mostra as conex�es
                $conexoesTabelas = array();
                if($params["alias_tabela"] != "T1") {

                    // Busca as conex�es entre as tabelas
                    $dados = array("cd_consulta"       => $params["cd_consulta"],
                                   "cd_tabela_nova"    => $params["cd_tabela_nova"],
                                   "alias_tabela_nova" => $params["alias_tabela_nova"]);
                    $queryConexao = $consColuna->queryBuscaConexoesTabelasBanco($dados);

                    // Executa a conexao
                    $retConexoes = $db->query($queryConexao);
                    
                    // Busca as conex�es existentes para a tabela referenciada
                    $dados2 = array("cd_consulta"  => $params["cd_consulta"],
                                    "alias_tabela" => $params["alias_tabela_nova"]);
                    $queryTabelaRef = $consConTabRef->queryBuscaConexoesTabelaReferenciada($dados2);
                    
                    // Executa a conex�o
                    $retTabelaRef   = $consConTabRef->fetchAll($queryTabelaRef);

                    // Percorre as conexoes e monta o combo
                    foreach($retConexoes->fetchAll() as $conexaoBanco) {
                        // Verifica se j� existe a tabela referenciada no banco e seta como 1
                        foreach($retTabelaRef as $conexaoTabela) {
                            $existe = 0;
                            if($conexaoBanco->CHAVE_TABELAS == $conexaoTabela->FK_TABELA) {
                                $existe = 1; // Utilizado para marcar no combo por qual FK foi referenciada a tabela
                            }
                        }
                        
                        $valor = $conexaoBanco->TABELA_REFERENCIADA . "(" . $conexaoBanco->ALIAS_TABELA_REF  . ") = " . 
                                 $conexaoBanco->TABELA_NOVA         . "(" . $conexaoBanco->ALIAS_TABELA_NOVA . ")";
                        $conexoesTabelas[] =  array("CHAVE"             => $conexaoBanco->CHAVE_TABELAS,
                                                    "VALOR"             => $valor,
                                                    "ALIAS_TABELA_REF"  => $conexaoBanco->ALIAS_TABELA_REF,
                                                    "ALIAS_TABELA_NOVA" => $conexaoBanco->ALIAS_TABELA_NOVA,
                                                    "EXISTE"            => $existe);
                    }

                }

				$this->_helper->json(Marca_ConverteCharset::converter($conexoesTabelas), true);

			} catch(Exception $e) {
				echo "Erro: " . $e->getMessage(); die;
			}

			// Limpa os objetos da memoria
			unset($consColuna);
		}
	}

}