<?php
/**
 * Esta classe tem como objetivo manipular os dados da Aba de TabColunas.
 *
 * @author     David Valente
 * @category   Marca Sistemas
 * @copyright  Copyright (c) 1991-2011 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 */
class ConfiguracoesSistema_TabColunasController extends Marca_Controller_Abstract_Operacao {
    
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
        if (isset($params['pai_cd_tabela'])) {
            $sessao->pai_cd_tabela = $params['pai_cd_tabela'];
        }
        
        Zend_Registry::set("pai_cd_tabela", $sessao->pai_cd_tabela);
        
        // Joga para a view o c�digo da transa��o pai
        $this->view->pai_cd_tabela = $sessao->pai_cd_tabela;
        
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
        Zend_Loader::loadClass('ColunaModel');       
        
        
    }


    /**
     * M�todo principal da classe
     *
     * @return void
     */
    public function indexAction() {
        
        // Captura os parametros passados por GET
        $params = $this->getRequest()->getParams();
        
        // Instancia a classe de sistemas web
        $colunas = new ColunaModel();
        
        // Captura o c�digo da transa��o pai
        $pai_cd_tabela = Zend_Registry::get("pai_cd_tabela");
        
        // Define os filtros para a cosulta
        $where = $colunas->addWhere(array("CD_TABELA = ?" => $pai_cd_tabela))
                         ->getWhere();
                         
        // Define os par�metros para a consulta
        $query = $colunas->queryBuscaColunas($where)->orderByList();
        
        // Recebe a inst�ncia do paginator por singleton
        $paginator = Zend_Paginator::factory($query);
        
        // Seta o n�mero da p�gina corrente
        $pagina = $this->_getParam('pagina', 1);
        
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
    public function novoAction(){}
    
    
    /**
     * Salva uma a��o a transa��o
     *
     * @return void
     */
    public function salvarAction() {

        // Recupera os parametros da requisi��o
        $params = $this->_request->getParams();

        // Instancia o modelo
        $colunas = new ColunaModel(); 

        // Captura o c�digo da transa��o pai
        $pai_cd_tabela = Zend_Registry::get("pai_cd_tabela");

        // Remove os pontos do valor caso tenha sido informado
        if (trim($params["tamanho"]) != "") {
        	$params["tamanho"] = str_replace(".", "", $params["tamanho"]);	
        }

        // Monta os dados que ser�o salvos
		$dados = array("CD_TABELA"        => $pai_cd_tabela,
                       "CD_COLUNA"        => $params["cd_coluna"],				       
        			   "NO_COLUNA"        => $params["no_coluna"],
        			   "CD_COLUNA_REF"    => $params["cd_coluna_ref"],
					   "DS_COLUNA"        => $params["ds_coluna"],
					   "TP_COLUNA"        => $params["tp_coluna"],
					   "TAMANHO"          => $params["tamanho"],
					   "FL_PK"            => $params["fl_pk"],
					   "FL_NULL"          => $params["fl_null"],
					   "DEFAULT_VALUE"    => $params["default_value"],
					   "CHECK_CONSTRAINT" => $params["check_constraint"]);
		
        // Valida os dados obrigat�rios
        if($colunas->isValid($dados)) {
        
        	// Se o registro n�o existir insere, caso contr�rio edita
        	if($params["operacao"] == "novo") {
            	
        		// Insere o novo aditivo
                $colunas->insert($dados);
                
                // Redireciona para a��o novo
            	$this->_forward('index');
            	
            } else {
        	
            	// Monta a condi��o do UPDATE
				$where = "CD_TABELA = '{$pai_cd_tabela}' AND ".
	                     "CD_COLUNA = '{$params["cd_coluna"]}'";					
	          
				// Executa o UPDATE
				$colunas->update($dados, $where);
	                
				// Redireciona para a��o de selecionar		                
				$this->_forward('selecionar');		                       
		            
				// Reenvia os dados para popular o FORMUL�RIO
	        	$this->_helper->RePopulaFormulario->repopular($params, "lower");  
	        	               
        	}         	
        }
        
        // Libera da memoria
        unset($colunas);
    }


    /**
     * Exclui um aditivo selecionado de um contrato
     *
     * @return void
     */
    public function excluirAction(){
        
        // Recupera os parametros da requisi��o
        $params = $this->_request->getParams();
        
        // Instancia a classe de sistemas web
        $colunas = new ColunaModel();
        
        // Define os filtros para a cosulta
        $where = $colunas->addWhere(array("CD_TABELA = ?" => $params['cd_tabela']))
        				 ->addWhere(array("CD_COLUNA = ?" => $params['cd_coluna']))
                         ->getWhere();
        
        // Define os par�metros para a consulta e retorna o resultado da pesquisa
        $delete = $colunas->delete($where);
        
        // Verifica se o registro foi exclu�do
        if($delete) {
			
        	// Limpa os dados da requisi��o
            $params = $this->_helper->LimpaParametrosRequisicao->limpar();
                
            // Redireciona para o index
            $this->_forward("index", null, null, $params);
                
		} else {
            
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
        $colunas = new ColunaModel();
        
        // Captura o c�digo da transa��o pai
        $pai_cd_tabela = Zend_Registry::get("pai_cd_tabela");
        
        // Define os filtros para a cosulta
        $where = $colunas->addWhere(array("CD_TABELA = ?" => $pai_cd_tabela))
        				 ->addWhere(array("CD_COLUNA = ?" => $params['cd_coluna']))
                         ->getWhere();
        
        // Define os par�metros para a consulta e retorna o resultado da pesquisa
        $resConsulta = $colunas->fetchRow($where);
             
        // Reenvia os valores para o formul�rio
        $this->_helper->RePopulaFormulario->repopular($resConsulta, "lower");
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
	 * Busca os dados via ajax
	 *
	 * @return JSON
	 */
	public function retornaDadosXAction() {

		// Verifica se arrequisi��o foi passada por Ajax
		if($this->_request->isXmlHttpRequest()) {
			 
			// Captura os parametros passados por GET
			$params = $this->getRequest()->getParams();

			// Pega o c�digo da tabela
			$pai_cd_tabela = Zend_Registry::get("pai_cd_tabela");
			
			// Instancia a classe model
			$colunas = new ColunaModel();
						
			$where = "CD_COLUNA = '{$params['cd_coluna']}' AND CD_TABELA = '{$pai_cd_tabela}'";
			
			// Pega os tipos de opera��es da pesagem
			$retorno = $colunas->fetchAll($where);
			
			if (count($retorno) > 0) {			
				$resultado['registro'] = $retorno->toArray();
			}
			
			// Retorna os dados por json
			$this->_helper->json(Marca_ConverteCharset::converter($resultado), true);

			// Limpa os objetos da memoria
			unset($colunas);
		}

	}
}