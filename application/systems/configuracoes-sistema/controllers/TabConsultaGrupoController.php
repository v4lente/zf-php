<?php
/**
 *
 * Respons�vel pelas associa��es de grupos a consultas
 *
 * @author     Bruno Tel�
 * @category   Marca Sistemas
 * @copyright  Copyright (c) 1991-2011 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 */
class ConfiguracoesSistema_TabConsultaGrupoController extends Marca_Controller_Abstract_Operacao {

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
        
        // Recupera os parametros da requisi��o
        $params = $this->_request->getParams();
                
        // Carrega os modelos de dados
		Zend_Loader::loadClass("ConsultaModel");
		Zend_Loader::loadClass("WebGrupoTabelaConsultaModel");
		Zend_Loader::loadClass("WebGrupoModel");
		
        // Captura a sess�o
        $sessao = new Zend_Session_Namespace('portoweb');

		// Instancia as classes Models
        $consulta  = new ConsultaModel();
		
        // Recupera a inst�ncia da base de dados
        $db = Zend_Registry::get("db");

        // Carrega a classe de tradu��o
        Zend_Loader::loadClass("Marca_Controller_Action_Helper_Traduz");
		Zend_Loader::loadClass('Marca_PicklistDb');
		Zend_Loader::loadClass('Marca_ConverteCharset');

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
    public function novoAction() {
	    // Captura o c�digo da consulta pai
        $pai_cd_consulta = Zend_Registry::get("pai_cd_consulta");
		
		$this->view->pai_cd_consulta = $pai_cd_consulta;
    }
    
	/**
     * Salva, utilizado para as opera��es de INSERT/UPDATE
     *
     * @return void
     */
    public function salvarAction() {

		// Recupera os parametros da requisi��o
        $params = $this->_request->getParams();

        // Associa as vari�veis do banco
        $db = Zend_Registry::get('db');

        // Captura a sess�o
        $sessao = new Zend_Session_Namespace('portoweb');

		//captura o c�digo do grupo selecionado na MASTER
		$pai_cd_consulta = Zend_Registry::get("pai_cd_consulta");

        // Instancia as classes Models
        $grupoCons  = new WebGrupoTabelaConsultaModel();

		// Verifica se a opera��o � de NOVO
		if($params['operacao'] == "novo") {

			// Percorre todas as a��es
			foreach($params['cd_grupo'] as $cd_grupo) {
				if ( $cd_grupo != '' ) {
					// Monta os dados para salvar
					$dados = array("CD_CONSULTA"   	=> $pai_cd_consulta,
								   "CD_GRUPO" 		=> $cd_grupo);

					// Valida os dados obrigat�rios
					if($grupoCons->isValid($dados)) {
						// Insere o novo usu�rio
						$grupoCons->insert($dados);
					}
				}
			}
		}

   		//redireciona para a tela de listagem
        $this->_forward("pesquisar", null, null, $params);

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
		$pai_cd_consulta = Zend_Registry::get("pai_cd_consulta");

		// Instancia as classes Models
        $grupoCons	= new WebGrupoTabelaConsultaModel();

		// Monta a condi��o do where
		$where = "CD_GRUPO = " . $params['cd_grupo'] . " AND CD_CONSULTA = " . $params['cd_consulta'];

		// Exclui o sistema da tabela Paralisa
        $delete = $grupoCons->delete($where);

   		//redireciona para a tela de listagem
        $this->_forward("pesquisar", null, null, $params);

		unset($grupoCons);

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
		$pai_cd_consulta = Zend_Registry::get("pai_cd_consulta");

		// Instancia as classes Models
        $grupoCons  = new WebGrupoTabelaConsultaModel();

		// Monta a condi��o do where
		$where = "TC.CD_GRUPO = " . $params['cd_grupo'] . " AND TC.CD_CONSULTA = " . $pai_cd_consulta; 

        // Busca todas as transa��es ligadas as a��es para a aba tab-transacao-acao
        $select = $grupoCons->select()
                          ->setIntegrityCheck(false)
                          ->from(array("TC" => "WEB_GRUPO_TABELA_CONSULTA"), array("TC.CD_CONSULTA", "TC.CD_GRUPO", "G.NO_GRUPO", "G.DS_GRUPO" ))
						  ->join(array("G" => "WEB_GRUPO"), "G.CD_GRUPO = TC.CD_GRUPO", array())
                          ->where($where);

        // Define os par�metros para a consulta e retorna o resultado da pesquisa
        $resConsulta = $grupoCons->fetchRow($select);

		// Define os dados em tela
		$this->_helper->RePopulaFormulario->repopular($resConsulta->toArray(), "lower");

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

        // Captura o c�digo da consulta pai
        $pai_cd_consulta = Zend_Registry::get("pai_cd_consulta");
		
		// Instancia as classes Models
        $grupoCons  = new WebGrupoTabelaConsultaModel();

        // Define os filtros para a cosulta
        $where = $grupoCons->addWhere(array("WT.CD_CONSULTA = ?" => $pai_cd_consulta))
                           ->getWhere();


        // Busca todas as transa��es ligadas as a��es para a aba tab-transacao-acao
        $select = $grupoCons->select()
                          ->setIntegrityCheck(false)
                          ->from(array("WG" => "WEB_GRUPO"), array("WG.CD_GRUPO",
																   "WG.NO_GRUPO", 
																   "WG.DS_GRUPO"))
                          ->join(array("WT" => "WEB_GRUPO_TABELA_CONSULTA"), "WG.CD_GRUPO = WT.CD_GRUPO", array())
                          ->where($where)
						  ->order('WG.CD_GRUPO ASC'); // Permite ordenar a consulta pela listagem
//						  ->orderByList(); // Permite ordenar a consulta pela listagem

        // Define os par�metros para a consulta e retorna o resultado da pesquisa
        $resConsulta = $grupoCons->fetchAll($select);

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

		unset($grupoCons);
		
	}

	/**
	 * Gera o relat�rio a partir de uma listagem
	 *
	 * @return void
	 */
	public function relatorioAction() {
	}
	
	public function autoCompleteGrupoXAction(){

		// Verifica se a requisi��o foi passada por Ajax
		if($this->_request->isXmlHttpRequest()) {

			// Captura o c�digo da consulta pai
			$pai_cd_consulta = Zend_Registry::get("pai_cd_consulta");

			// Captura os parametros passados por GET
			$params = $this->getRequest()->getParams();
			
			// Instancia o modelo
            $grupos = new WebGrupoModel();
			
			try {

				$retornoJSON = array();

				// Define os filtros para a consulta
				//captura a linha
				if (isset($params['cd_grupo'])){
					$where = $grupos->addWhere(array("UPPER(G.CD_GRUPO) = ?" => $params["cd_grupo"]))
									 ->getWhere();
				}else{
					$where = $grupos->addWhere(array("UPPER(TO_CHAR(G.CD_GRUPO) || ' - ' || G.NO_GRUPO) LIKE  ?" => '%'.trim(strtoupper($params["term"])).'%'))
									 ->getWhere();
				}

				$select = $grupos->select()
                          ->setIntegrityCheck(false)
                          ->from(array("G" => "WEB_GRUPO"), array("G.CD_GRUPO", 
																  "G.NO_GRUPO", 
																  "LISTA" => new Zend_Db_Expr("UPPER(TO_CHAR(G.CD_GRUPO) || ' - ' || G.NO_GRUPO)"), 
																  "G.DS_GRUPO"))
						  ->where($where)
                          ->where(" NOT EXISTS (" . (
								  $grupos->select()
										->setIntegrityCheck(false)
										->from(array("TC" => "WEB_GRUPO_TABELA_CONSULTA"), array("TC.CD_GRUPO"))
										->where("TC.CD_GRUPO = G.CD_GRUPO AND TC.CD_CONSULTA = {$pai_cd_consulta}") ) . ")");
                                        
				$linhas = $grupos->fetchAll($select);

				if(count($linhas) > 0) {
					foreach($linhas as $linha) {
						$value  	= trim($linha->NO_GRUPO);	//preenche o campo do autocomplete somente com o NO_GRUPO ao selecionar uma op��o da lista 
						$label  	= trim($linha->LISTA);	// valor exibido na listagem do autocomplete
						$cd_grupo 	= trim($linha->CD_GRUPO); //dado para preenchimento do outro campo
						$ds_grupo	= trim($linha->DS_GRUPO); //dado para preenchimento do outro campo
						 
						$retornoJSON[] = array("value"     	=> $value,                       
											   "label"     	=> $label,
											   "cd_grupo" 	=> $cd_grupo,
											   "ds_grupo"	=> $ds_grupo
						);
					}
				}

				$this->_helper->json(Marca_ConverteCharset::converter($retornoJSON), true);

			} catch(Exception $e) {
				//echo $e->getMessage(); die;
				echo false;
			}

			// Limpa os objetos da memoria
			unset($grupos);
		}

	}
	
	/**
     * Valida o codigo da consulta informado e retorna os demais dados para preenchimento dos campos
     *
     * @return JSON
     */
	public function validaCodigoXAction() {
		
        // Verifica se arrequisi��o foi passada por Ajax
        if($this->_request->isXmlHttpRequest()) {
            
            // Captura os parametros passados por GET
            $params = $this->getRequest()->getParams();
            
			//captura o c�digo do grupo selecionado na MASTER
			$params["cd_grupo"] = Zend_Registry::get("pai_cd_grupo");
			
            // Instancia o model
            $grupo_consulta = new WebGrupoTabelaConsultaModel();
            $consulta       = new ConsultaModel();
			
			//verifica se c�digo de consulta j� est� vinculado ao grupo
			$where = $grupo_consulta->addWhere(array("MS.CD_CONSULTA = ?" => $params['cd_consulta']))
									->addWhere(array("MS.CD_GRUPO    = ?" => $params['cd_grupo']))
									->getWhere();
			
			
			$select = $grupo_consulta->select()
							  ->setIntegrityCheck(false)
							  ->from (array("MS" => "WEB_GRUPO_TABELA_CONSULTA"), array("MS.CD_CONSULTA", 
                                                                                        "NO_GRUPO" => new Zend_Db_Expr("UPPER(TRIM(MG.NO_GRUPO))")))
							  ->join (array("MC" => "CONSULTA"),  "MS.CD_CONSULTA = MC.CD_CONSULTA", array())
							  ->join (array("MG" => "WEB_GRUPO"), "MS.CD_GRUPO    = MG.CD_GRUPO",    array())
							  ->where($where);	
			
			$linha = $consulta->fetchRow($select);
            					
			
           //se n�o estiver vinculado, procura na tabela CONSULTA para verificar se existe e retornar os dados
           if($linha->CD_CONSULTA == "") {
				
				//verifica se c�digo informado existe 
				$where = $consulta->addWhere(array("MC.CD_CONSULTA = ?"	=> $params['cd_consulta']))
								 ->getWhere();
				
				
				$select = $consulta->select()
								  ->setIntegrityCheck(false)
								  ->from (array("MC" => "CONSULTA"),  array("MC.CD_CONSULTA",
																		 	"TITULO"        => new Zend_Db_Expr("UPPER(TRIM(MC.TITULO))"),
																			"MC.DTHR_CADASTRO",
																			"CD_USUARIO"    => new Zend_Db_Expr("UPPER(TRIM(MC.CD_USUARIO))"),
																			"FL_ATIVO"      => new Zend_Db_Expr("CASE WHEN MC.FL_ATIVO = 1 THEN 'SIM' ELSE 'N�O' END")))
								  ->where($where);
				//echo $select;die;			  
				$linha = $consulta->fetchRow($select);
				
				//se existir, retorna os dados
				if($linha->CD_CONSULTA != ""){
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
		    else{
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