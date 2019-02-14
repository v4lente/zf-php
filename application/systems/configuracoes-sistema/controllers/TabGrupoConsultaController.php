<?php
/**
 *
 * Respons�vel pelas associa��es de grupos com consultas
 *
 * @author     Maur�cio pesenti Spolavori
 * @category   Marca Sistemas
 * @copyright  Copyright (c) 1991-2011 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 *
 * Alterado em 09/11/2012 por Guilherme Padilha: adicionado "utf8_decode" no autocomplete e comentado "Marca_PicklistDb"
 */
class ConfiguracoesSistema_TabGrupoConsultaController extends Marca_Controller_Abstract_Operacao {

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
        if (isset($params['pai_cd_grupo'])) {
            $sessao->pai_cd_grupo = $params['pai_cd_grupo'];
        }

        Zend_Registry::set("pai_cd_grupo", $sessao->pai_cd_grupo);

        // Joga para a view o c�digo da transa��o pai
        $this->view->pai_cd_grupo = $sessao->pai_cd_grupo;
    }
    
	/**
     * M�todo inicial para carregamento de classes do controlador
     *
	 * (non-PHPdoc)
     * @see library/Marca/Controller/Abstract/Marca_Controller_Abstract_Operacao#init()
	 *
     * @return void
     */
    public function init() {

		// Carrega o m�todo de inicializa��o da classe pai
        parent::init();
        
        // Define o layout a ser utilizado
        $this->_helper->layout->setLayout("layout-aba");

        // Carrega os modelos de dados
		Zend_Loader::loadClass("ConsultaModel");
		Zend_Loader::loadClass("WebGrupoTabelaConsultaModel");
		
        // Captura a sess�o
        $sessao = new Zend_Session_Namespace('portoweb');

		// Instancia as classes Models
        $consulta  = new ConsultaModel();
		
        // Recupera a inst�ncia da base de dados
        $db = Zend_Registry::get("db");

        // Carrega a classe de tradu��o
        Zend_Loader::loadClass("Marca_Controller_Action_Helper_Traduz");
		Zend_Loader::loadClass("Marca_ConverteCharset");
		//Zend_Loader::loadClass('Marca_PicklistDb');

        // Instancia o objeto de tradu��o
        $traducao = new Marca_Controller_Action_Helper_Traduz();

        // Monta a tradu��o dos campos. Nessess�rio para reduzir a quantidade de acessos ao banco, diminuindo assim o tempo de carregamento da p�gina.
        $this->view->traducao = $traducao->traduz(array("LB_CODIGO", 
                                                        "LB_TITULO_CONSULTA", 
                                                        "LB_DATAHORA_CADASTRO", 
                                                        "LB_RESPONSAVEL", 
                                                        "LB_CD_CONSULTA", 
                                                        "LB_ATIVO",
														"LB_COD_CONSULTA", 
                                                        "LB_TITULO_DA_CONSULTA", 
                                                        $sessao->perfil->CD_IDIOMA));

	}

	/**
     * M�todo principal da classe
     *
     * @return void
     */
    public function indexAction() {
	
		// Redireciona para a��o de pesquisar
		$this->_forward('pesquisar', null, null, $params);
	}

	/**
     * Gera um documento para cadastro de um novo registro
     *
     * @return void
     */
    public function novoAction() { }
    
	/**
     * Salva, utilizado para as opera��es de INSERT/UPDATE
     *
     * @return void
     */
    public function salvarAction() {
		
		// Recupera os parametros da requisi��o
        $params = $this->_request->getParams();
		
		//captura o c�digo do grupo selecionado na MASTER
		$params["cd_grupo"] = Zend_Registry::get("pai_cd_grupo");

        // Instancia as classes Models
        $grupo_consulta  = new WebGrupoTabelaConsultaModel();
	
		// Verifica se a opera��o � de NOVO
		if ($params['operacao'] == "novo") {
			
			// Percorre todas as a��es
			for($i=0; $i < 10; $i++) {
				
				// Verifica se o c�digo � v�lido
				if ($params["cd_consulta"][$i] != "") {

					// Monta os dados para salvar
					$dados = array("CD_GRUPO"    => $params["cd_grupo"],
								   "CD_CONSULTA" => $params["cd_consulta"][$i]);

					// Valida os dados obrigat�rios
					if ($grupo_consulta->isValid($dados)) {

						// Insere o novo usu�rio
						$grupo_consulta->insert($dados);
					}
				}
			}
		}

		//limpa vari�vel para n�o ocorrer erro ao redirecionar para a listagem
		$params['cd_consulta'] = "";

   		//redireciona para a tela de listagem
        $this->_forward("pesquisar", null, null, $params);

		// Libera a mem�ria
		unset($grupo_consulta);
	}

	/**
     * Exclui um registro selecionado, utilizado para a opera��o de DELETE
     *
     * @return void
     */
    public function excluirAction() {
	
		// Recupera os parametros da requisi��o
        $params = $this->_request->getParams();

        // Captura a sess�o
		$sessao = new Zend_Session_Namespace('portoweb');    	
		
		//captura o c�digo do grupo selecionado na MASTER
		$params["cd_grupo"] = Zend_Registry::get("pai_cd_grupo");

		// Instancia as classes Models
        $grupo_consulta  = new WebGrupoTabelaConsultaModel();

		// Monta a condi��o do where
		$where = "CD_GRUPO = " . $params['cd_grupo'] . " AND CD_CONSULTA = " . $params['cd_consulta']; 

		// Exclui o sistema da tabela Paralisa
        $delete = $grupo_consulta->delete($where);
		
		//limpa vari�vel para n�o ocorrer erro ao redirecionar para a listagem
		$params['cd_consulta'] = "";
		
   		//redireciona para a tela de listagem
        $this->_forward("pesquisar", null, null, $params);
	
		// Libera a mem�ria
		unset($grupo_consulta);
	}

	/**
     * Retorna os dados de um objeto selecionado a partir de uma listagem
	 *
     * @return void
     */
	public function selecionarAction() {

		// Recupera os parametros da requisi��o
		$params = $this->_request->getParams();
		
		//captura o c�digo do grupo selecionado na MASTER
		$params["cd_grupo"] = Zend_Registry::get("pai_cd_grupo");
		
		// Instancia as classes Models
        $grupo_consulta  = new WebGrupoTabelaConsultaModel();

		//Chama pesquisa passando where como par�metro
		$select = $grupo_consulta->buscaConsultas($params);	

		// Recupera o sistema selecionado
		$sistema = $grupo_consulta->fetchRow($select);

		// Define os dados em tela
		$this->_helper->RePopulaFormulario->repopular($sistema->toArray(), "lower");

		// Libera a mem�ria
		unset($grupo_consulta);
	}

	/**
     * Pesquisa os processos
     *
     * @return void
     */
    public function pesquisarAction() {

        // Captura a sess�o
        $sessao = new Zend_Session_Namespace('portoweb');

		// Recupera os parametros da requisi��o
        $params = $this->_request->getParams();
        
		//captura o c�digo do grupo selecionado na MASTER
		$params["cd_grupo"] = Zend_Registry::get("pai_cd_grupo");
		
		// Instancia as classes Models
        $grupo_consulta = new WebGrupoTabelaConsultaModel();

		//Chama pesquisa passando where como par�metro
		$select = $grupo_consulta->buscaConsultas($params)->orderByList();

		//echo $select;die;
		//$sessao->dadosPesquisa = $grupo_consulta->fetchAll($select);
		$resConsulta = $grupo_consulta->fetchAll($select);
		
		// Seta o n�mero da p�gina corrente
		$pagina = $this->_getParam('pagina', 1);

		// Recebe a inst�ncia do paginator por singleton     
		//$paginator = Zend_Paginator::factory($sessao->dadosPesquisa);
		$paginator = Zend_Paginator::factory($resConsulta);

		// Define a p�gina corrente
		$paginator->setCurrentPageNumber($pagina);
		
		// Define o total de linhas por p�gina
		$paginator->setItemCountPerPage($sessao->perfil->QT_LINHAS);
		
		// Joga para a view a pagina��o
		$this->view->paginator = $paginator;
		
		// Reenvia os valores para o formul�rio
		$this->_helper->RePopulaFormulario->repopular($params);
		
		// Libera a mem�ria
		unset($grupo_consulta);
	}

	/**
	 * Gera o relat�rio a partir de uma listagem
	 *
	 * @return void
	 */
	public function relatorioAction() { }

	
	public function autoCompleteTituloXAction(){
        
		// Verifica se a requisi��o foi passada por Ajax
		if ($this->_request->isXmlHttpRequest()) {

			// Captura os parametros passados por GET
			$params = $this->getRequest()->getParams();

			//captura o c�digo do grupo selecionado na MASTER
			$params["cd_grupo"] = Zend_Registry::get("pai_cd_grupo");

			// Instancia a classe model
			$consulta = new ConsultaModel();

			try {

				$retornoJSON = array();

				// Define os filtros para a consulta
				$where = $consulta->addWhere(array("UPPER(TO_CHAR(C.CD_CONSULTA) || ' - ' || C.TITULO) LIKE  ?"	=> '%'.$params["term"].'%'))
								  //para n�o trazer consultas j� vinculadas ao grupo
								  ->addWhere(array("C.CD_CONSULTA NOT IN (SELECT MS.CD_CONSULTA FROM WEB_GRUPO_TABELA_CONSULTA AS MS WHERE MS.CD_GRUPO = ".$params["cd_grupo"].")"))
								  ->getWhere();

				// Monta a busca
				$select = $consulta->select()
									->setIntegrityCheck(false)
									->from ( array ("C" => "CONSULTA"), array("DS_TITULO" => new Zend_Db_Expr("UPPER(TO_CHAR(C.CD_CONSULTA) + ' - ' + C.TITULO)"),
																			  "DADOS"     => new Zend_Db_Expr("TO_CHAR(C.CD_CONSULTA) + ' - ' +" .
																			  "UPPER(TRIM(C.TITULO)) || ' - ' ||" .
																			  "TO_CHAR(C.DTHR_CADASTRO) || ' ' || TO_CHAR(C.DTHR_CADASTRO) + ' - ' || " .
																			  "UPPER(TRIM(C.CD_USUARIO)) || ' - ' || " .
																			  "CASE WHEN FL_ATIVO = 1 THEN 'SIM' ELSE 'N�O' END"), 
                                                                              "C.TITULO" ))
									->where($where)
									->order("1 ASC");

				$linhas = $consulta->fetchAll($select);

				if (count($linhas) > 0) {
					foreach($linhas as $linha) {
						$id     = trim($linha->DS_TITULO);
						$value  = trim($linha->TITULO);	//preenche o campo do autocomplete somente com o T�TULO ao selecionar uma op��o da lista 
						$label  = trim($linha->DS_TITULO);	// valor exibido na listagem do autocomplete
						$target = trim($linha->DADOS); //dados que ser�o separados para preenchimento dos outros campos

						$retornoJSON[] = array("id"     => $id,
											   "value"  => $value,
											   "label"  => $label,
											   "target" => $target
						);
					}
				}

				$this->_helper->json(Marca_ConverteCharset::converter($retornoJSON), true);

			} catch(Exception $e) {
				//echo $e->getMessage(); die;
				echo false;
			}

			// Limpa os objetos da memoria
			unset($consulta);
		}
	}

	/**
     * Valida o codigo da consulta informado e retorna os demais dados para preenchimento dos campos
     *
     * @return JSON
     */
	public function validaCodigoXAction() {
		
        // Verifica se arrequisi��o foi passada por Ajax
        if ($this->_request->isXmlHttpRequest()) {
            
            // Captura os parametros passados por GET
            $params = $this->getRequest()->getParams();
            
			//captura o c�digo do grupo selecionado na MASTER
			$params["cd_grupo"] = Zend_Registry::get("pai_cd_grupo");

            // Instancia o model
            $grupo_consulta = new WebGrupoTabelaConsultaModel();
            $consulta       = new ConsultaModel();

			//verifica se c�digo de consulta j� est� vinculado ao grupo
			$where = $grupo_consulta->addWhere(array("MS.CD_CONSULTA = ?"	=> $params['cd_consulta']))
									->addWhere(array("MS.CD_GRUPO = ?"		=> $params['cd_grupo']))
									->getWhere();

			$select = $grupo_consulta->select()
									  ->setIntegrityCheck(false)
									  ->from (array("MS" => "WEB_GRUPO_TABELA_CONSULTA"), array("MS.CD_CONSULTA", "UPPER(LTRIM(RTRIM(MG.NO_GRUPO))) as NO_GRUPO"))
									  ->join (array("MC" => "CONSULTA"), "MS.CD_CONSULTA = MC.CD_CONSULTA", array())
									  ->join (array("MG" => "WEB_GRUPO"), "MS.CD_GRUPO = MG.CD_GRUPO", array())
									  ->where($where);

			//echo $select;die;
			$linha = $grupo_consulta->fetchRow($select);

           //se n�o estiver vinculado, procura na tabela CONSULTA para verificar se existe e retornar os dados
           if ($linha->CD_CONSULTA == "") {
				
				//verifica se c�digo informado existe 
				$where = $consulta->addWhere(array("MC.CD_CONSULTA = ?"	=> $params['cd_consulta']))
								 ->getWhere();

				$select = $consulta->select()
								  ->setIntegrityCheck(false)
								  ->from (array("MC" => "CONSULTA"), array("MC.CD_CONSULTA", 
                                                                           "TITULO"        => new Zend_Db_Expr("UPPER(TRIM(MC.TITULO))"),
																		   "DTHR_CADASTRO" => new Zend_Db_Expr("TO_CHAR(MC.DTHR_CADASTRO) || ' ' || TO_CHAR(MC.DTHR_CADASTRO)"),
																		   "CD_USUARIO"    => new Zend_Db_Expr("UPPER(TRIM(MC.CD_USUARIO))"),
																		   "FL_ATIVO"      => new Zend_Db_Expr("CASE WHEN MC.FL_ATIVO = 1 THEN 'SIM' ELSE 'N�O' END")))
								  ->where($where);

				//echo $select;die;			  
				$linha = $consulta->fetchRow($select);

				//se existir, retorna os dados
				if ($linha->CD_CONSULTA != ""){
					$linha = $linha->toArray();
					$linha["MSG"] = "";
					//print_r($linha);die;
				}
				//caso n�o exista monta mensagem de erro
				else{
					//$linha = $linha->toArray();
					$linha["MSG"] = "C�digo de consulta inexistente, por favor, informe um c�digo v�lido.";
				}
            }
		    //caso j� esteja vinculado monta mensagem de erro
		    else {
				$linha = $linha->toArray();
				$linha["MSG"]= "Esta consulta j� est� cadastrada para o grupo ".$linha["NO_GRUPO"].".";
				//print_r($linha);die;
			}

            // Retorna os dados por json
            $this->_helper->json(Marca_ConverteCharset::converter($linha), true);

            // Limpa os objetos da memoria
            unset($consulta);
            unset($grupo_consulta);
        }
    }
}
?>