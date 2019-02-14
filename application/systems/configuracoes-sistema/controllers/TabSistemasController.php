<?php
/**
 * Esta classe tem como objetivo manipular os dados da Aba de TabSistemas.
 *
 * @author     David Valente
 * @category   Marca Sistemas
 * @copyright  Copyright (c) 1991-2011 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 */
class ConfiguracoesSistema_TabSistemasController extends Marca_Controller_Abstract_Operacao {
    
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
        Zend_Loader::loadClass('SistemaModel');
        Zend_Loader::loadClass('TabelaSistemaModel');       
        
        
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
        $tabelaSistemas = new TabelaSistemaModel();
        
        // Captura o c�digo da transa��o pai
        $pai_cd_tabela = Zend_Registry::get("pai_cd_tabela");
        
        // Define os filtros para a cosulta
        $where = $tabelaSistemas->addWhere(array("CD_TABELA = ?" => $pai_cd_tabela))
                                ->getWhere();
        
        // Define os par�metros para a consulta e retorna o resultado da pesquisa
        $select = $tabelaSistemas->querySistemas($where);                
       
        // Recebe a inst�ncia do paginator por singleton
        $paginator = Zend_Paginator::factory($select);
        
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
        $tabelaSistema = new TabelaSistemaModel(); 

        // Captura o c�digo da transa��o pai
        $pai_cd_tabela = Zend_Registry::get("pai_cd_tabela");
        
        // Monta os dados que ser�o salvos
		$dados = array("CD_TABELA"  => $pai_cd_tabela,
                       "CD_SISTEMA" => $params["cd_sistema"]
        			  );
		
        // Valida os dados obrigat�rios
        if($tabelaSistema->isValid($dados)) {
        
        	// Se o registro n�o existir insere, caso contr�rio edita
        	if($params["operacao"] == "novo") {
            	
        		// Verifica se j� n�o foi inserido este sistema
        		$tsRow = $tabelaSistema->fetchRow("CD_SISTEMA = '{$params["cd_sistema"]}' AND CD_TABELA = '{$pai_cd_tabela}'");
        		
        		if($tsRow->CD_SISTEMA != "") {
        			
        			// Pega do registro a fila
        			$mensagemSistema = Zend_Registry::get("mensagemSistema");
        			
        			// Parametro para ser gerada a mensagem
	    			$msg = array("msg"    => array("Sistema j� inserido"), 
							     "titulo" => "Erro",
								 "tipo"   => 3);
        			// Gera a mensagem
        			$mensagemSistema->send(serialize($msg));
        			
        			// Redireciona para a��o novo
        			$this->_forward('novo');
        			
        		} else {
	        		// Insere o novo aditivo
	                $tabelaSistema->insert($dados);
	                
	                // Redireciona para a��o novo
	                $this->_forward('index');
        		}
                            	
            } else {
        	
            	// Monta a condi��o do UPDATE
				$where = "CD_TABELA  = '{$pai_cd_tabela}' AND ".
	                     "CD_SISTEMA = '{$params["cd_sistema"]}'";					
	          
				// Executa o UPDATE
				$tabelaSistema->update($dados, $where);
	                
				// Redireciona para a��o de selecionar		                
				$this->_forward('selecionar');
		            
				// Reenvia os dados para popular o FORMUL�RIO
	        	$this->_helper->RePopulaFormulario->repopular($params, "lower");	        	               
        	}         	
        }
        
        // Libera da memoria
        unset($tabelaSistema);
    }


    /**
     * Exclui um aditivo selecionado de um contrato
     *
     * @return void
     */
    public function excluirAction(){
        
    	// Verifica se arrequisi��o foi passada por Ajax
		if($this->_request->isXmlHttpRequest()) {
    	
	    	// Recupera os parametros da requisi��o
	        $params = $this->_request->getParams();
	       
	        // Captura o c�digo da transa��o pai
	        $pai_cd_tabela = Zend_Registry::get("pai_cd_tabela");
	        
	        // Instancia a classe de sistemas web
	        $tabelaSistema = new TabelaSistemaModel();
	        
	        // Define os filtros para a cosulta
	        $where = "CD_TABELA = '{$pai_cd_tabela}' AND CD_SISTEMA = '{$params['cd_sistema']}'";
	                               
			// Desativa a mensagem do sistema
			$tabelaSistema->setShowMessage(false);
				                               
	        // Define os par�metros para a consulta e retorna o resultado da pesquisa
	        $delete = $tabelaSistema->delete($where);
	        
	        $retornoJSON = array("retorno" => $delete);
			$this->_helper->json(Marca_ConverteCharset::converter($retornoJSON), true);	        
		}
    }


    /**
     * Seleciona um aditivo referente a um contrato
     * @return void
     */
    public function selecionarAction() {
        
       // Recupera os parametros da requisi��o
        $params = $this->_request->getParams();
        
        // Captura o c�digo da transa��o pai
        $pai_cd_tabela = Zend_Registry::get("pai_cd_tabela");
        
        // Instancia a classe de sistemas web
        $tabelaSistema = new TabelaSistemaModel();
        
        // Define os filtros para a cosulta
        $where = $tabelaSistema->addWhere(array("CD_TABELA = ?"  => $pai_cd_tabela))
        				  	   ->addWhere(array("CD_SISTEMA = ?" => $params['cd_sistema']))
                               ->getWhere();
        
        // Define os par�metros para a consulta e retorna o resultado da pesquisa
        $resConsulta = $tabelaSistema->fetchAll($where)->toArray();          
             
        // Reenvia os valores para o formul�rio
        $this->_helper->RePopulaFormulario->repopular($resConsulta[0], "lower");        
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
    public function relatorioAction() {}
    
    
	/**
	 * 
	 * Busca informa��es do motorista via AJAX
	 */
    public function autoCompleteXAction(){
		
    	// Verifica se arrequisi��o foi passada por Ajax
		if($this->_request->isXmlHttpRequest()) {
			
			// Captura a sess�o
			$sessao = new Zend_Session_Namespace('portoweb');
			
			// Captura os parametros passados por GET
			$params = $this->getRequest()->getParams();

			// Instancia a classe model 
			$sistemas = new SistemaModel();
						
			$where = "UPPER(NO_SISTEMA) LIKE UPPER('{$params["term"]}%')";
			if ($params['campo'] == "cd_sistema") {
				// TERM � o parametro default que vem da function autocomplete via ajax
				$where = "UPPER(CD_SISTEMA) LIKE UPPER('{$params["term"]}%')";				
			}
			
			$where .= " AND CD_SISTEMA NOT IN (SELECT TS.CD_SISTEMA 
											     FROM TABELA_SISTEMA TS
											    WHERE TS.CD_TABELA = '{$sessao->pai_cd_tabela}')";
			
			// Pega as cegonheiras sem chassis para o agendamento			
			$retorno = $sistemas->fetchAll($sistemas->select()
													->setIntegrityCheck(false)
												    ->from($sistemas, array("CD_SISTEMA", "NO_SISTEMA"))
													->where($where));

			// Se existir registros
			$retornoJSON = array();
			if(count($retorno) > 0) {
				// Verifica o campo
				if ($params['campo'] == "cd_sistema") {
	           		foreach($retorno as $linha) {
	           				// Devolve pela c�digo do sistema
	                    	$retornoJSON[] = array("label"     => strtoupper($linha->CD_SISTEMA),
	                    						   "descricao" => strtoupper($linha->NO_SISTEMA)                    						   
	                                               );
	           		}                    
	           	}
           		// Verifica o campo
           		if ($params['campo'] == "no_sistema") {
					foreach($retorno as $linha) {
						// Devolve pela c�digo do sistema
	                    $retornoJSON[] = array("label"  => strtoupper($linha->NO_SISTEMA),
	                    					   "codigo" => strtoupper($linha->CD_SISTEMA)                    						   
	                                           );
	           		}
				}
           }
			
			// Retorna os dados por json
			$this->_helper->json(Marca_ConverteCharset::converter($retornoJSON), true);
			
			// Limpa os objetos da memoria
			unset($sistemas);
		}    	
	}
	
	/**
	 * Busca o sistema vinculado a tabela pai
	 *
	 * @return JSON
	 */             
	public function retornaSistemaVinculadoTabelaXAction() {
	
		// Verifica se arrequisi��o foi passada por Ajax
		if($this->_request->isXmlHttpRequest()) {
			 
			// Captura os parametros passados por GET
			$params = $this->getRequest()->getParams();

			 // Captura o c�digo da transa��o pai
        	$pai_cd_tabela = Zend_Registry::get("pai_cd_tabela");
        
			// Instancia a classe model
			$sistema       = new SistemaModel();
			$tabelaSistema = new TabelaSistemaModel();
			
			// Verifica se existe o sistema na tabela sistema
			$retorno = $sistema->fetchAll("CD_SISTEMA = '{$params['cd_sistema']}'");
			
			if(count($retorno) == 0) {
				$retorno = -1;
				
			} else {
				
				// Verifica se o sistema j� n�o est� cadastrado
				$retorno = $tabelaSistema->fetchAll("CD_TABELA = '{$pai_cd_tabela}' AND CD_SISTEMA = '{$params['cd_sistema']}'");
				$retorno = count($retorno);
			}
			
			// Retorna os dados por json
			$this->_helper->json(Marca_ConverteCharset::converter($retorno), true);

			// Limpa os objetos da memoria
			unset($sistema);
			unset($tabelaSistema);
		}

	}
    
}