<?php

// Inclui a classe para os relatorios r�pido
require_once("Dompdf/dompdf_config.inc.php");

/**
 * Created on 18/11/2009
 *
 * Classe Bootstrap
 *
 * Esta classe tem a fun��o de carregar qualquer coisa
 * que for utilizado no sistema, aqui que se d� o
 * pontape inicial do sistema.
 *
 * @filesource
 * @author			M�rcio / David
 * @copyright		Copyright 2009 Marca
 * @package			zendframework
 * @subpackage		zendframework.application
 * @version			1.0
 */
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {
    
    private $autenticacao = null;
    private $front        = null;
    
	public function __construct($application){

	    parent::__construct($application);

	    header('Content-type: text/html; charset=iso-8859-1');
	    
	    // Captura o controlador principal
	    $this->front = Zend_Controller_Front::getInstance();
	    
        // Define a localiza��o do usu�rio
        try {
	        $locale = new Zend_Locale(Zend_Locale::BROWSER);
	        $locale->setDefault($locale);
        } catch(Exception $e) {
        	//
        }
        
	}


	/**
     * Carrega os dados da inicaliza��o da classe
     *
     * @return void
     */
    public function run() {
        parent::run();
    }
	
	/**
     * Inicializa o auto-carregamento
     *
     * @return Object $moduleLoader
     */
    protected function _initAutoload() {

        // Adiciona no autoload a classe DOMPDF (David valente)
        Zend_Loader_Autoloader::getInstance()->pushAutoloader('DOMPDF_autoload','');
        
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->registerNamespace('Marca_');
      
    }
    
    /**
     * 
     * Seta a baseUrl
     * 
     * @return void
     */
    protected function _initEnvironment() {
	    // set up baseURL
	    $this->_burl = $this->front->getBaseUrl();
	    if (!$this->_burl) {
	      $this->_burl = rtrim(preg_replace( '/([^\/]*)$/', '', $_SERVER['PHP_SELF'] ), '/\\');
	      $this->front->setBaseUrl($this->_burl);
	    }
    }
   

    /**
     * Adiciona a rota do REST
     *
     * @return void
     */
	protected function _initRestRoute() {

		$this->bootstrap('Request');
		$restRoute = new Zend_Rest_Route($this->front, array(), array(
			'default' => array('restserver')
		));
		
	}


	/**
	 * Redireciona para a rota definida
	 *
	 * @return object
	 */
	protected function _initRequest() {
        $this->bootstrap('FrontController');
        $request = $this->front->getRequest();
    	if (null === $this->front->getRequest()) {
            $request = new Zend_Controller_Request_Http();
            $this->front->setRequest($request);
        }

    	return $request;
    }


    /**
     * Carrega os dados do banco de dados
     *
     * @return void
     */
    protected function _initDb() {
		// Captura a sess�o
		$portoweb = new Zend_Session_Namespace('portoweb');
        
        // Inicializa a classe de autentica��o
        $this->autenticacao = Marca_Auth::getInstance();
        
        if(isset($this->autenticacao) && $this->autenticacao->getIdentity() != "") {
        
            // Se usu�rio logou existir� a configura��o
            if(isset($portoweb->arrayconfig)) {

                // Se n�o existir na sess�o o objeto Zend_Db
                // Carrega-o e seta a conex�o, caso contr�rio 
                // Busca da sess�o este objeto
                if(! isset($portoweb->db)) {
                    $config = new Zend_Config($portoweb->arrayconfig);

                    $db = Zend_Db::factory($config->database);
                    $db->getConnection();

                    try {
                        // Altera algumas sess�es do ORACLE
                        $db->query("ALTER SESSION SET NLS_COMP = 'LINGUISTIC'");
                        $db->query("ALTER SESSION SET NLS_SORT = 'BINARY_AI'");
                        $db->query("ALTER SESSION SET NLS_DATE_FORMAT = 'DD/MM/YYYY HH24:MI:SS'");
                    } catch(Zend_Exception $e) {
                        echo $e->getMessage();
                    }

                    // Seta o modo de retorno das consultas
                    $db->setFetchMode(Zend_Db::FETCH_OBJ);

                    // Seta na sess�o o objeto do banco
                    $portoweb->db = $db;

                } else {

                    // Busca da sess�o o objeto do banco
                    $db = $portoweb->db;

                    try {
                        // Altera algumas sess�es do ORACLE
                        $db->query("ALTER SESSION SET NLS_COMP = 'LINGUISTIC'");
                        $db->query("ALTER SESSION SET NLS_SORT = 'BINARY_AI'");
                        $db->query("ALTER SESSION SET NLS_DATE_FORMAT = 'DD/MM/YYYY HH24:MI:SS'");
                    } catch(Zend_Exception $e) {
                        echo $e->getMessage();
                    }

                    // Busca da sess�o a autenticacao do usuario
                    $db = $portoweb->db;

                }

                Zend_Db_Table::setDefaultAdapter($db);
                Zend_Registry::set('db', $db);
            }
        }
    }

    /**
     * Carrega o layout e seta os par�metros dele
     *
     * @return void
     */
    protected function _initViewHelper() {

        $this->bootstrap('layout');
		$layout = $this->getResource('layout');
		$view   = $layout->getView();
		$view->setEncoding('ISO-8859-1');

		$view->addScriptPath('../application/layouts/scripts');
		$view->addHelperPath('../application/layouts/helpers');
		
		$view->doctype('XHTML1_TRANSITIONAL');
		$view->headMeta()->appendHttpEquiv('Content-Type','text/html; charset=iso-8859-1');
		$view->headTitle()->setSeparator(' - ');
		$view->headTitle('Porto do Rio Grande ');
	}


	/**
     * Carrega os actions helpers
     *
     * @return void
     */
	protected function _initActionHelpers() {
	    Zend_Controller_Action_HelperBroker::addPrefix('Marca_Controller_Action_Helper');
    }
	
    /**
	 *  Inicializa o plugin que ir� decriptografar os dados
	 *  passados na url pelo par�metro "crypt"
	 *
	 *  @return void
	 */
	protected function _initDecriptografa() {
		$this->front->registerPlugin(new Marca_Controller_Plugin_Decriptografia());
	}

	/**
	 *  Inicializa o plugin que Contem os dados da
	 *  requisi��o anterior
	 *
	 *  @return void
	 */
	protected function _initOperacaoAnterior() {
		$this->front->registerPlugin(new Marca_Controller_Plugin_OperacaoAnterior());
	}
	
    /**
     *  Inicializa o plugin que ir� tratar os dados da requisi��o     
     *
     *  @return void
     */
    protected function _initTrataParams() {
        $this->front->registerPlugin(new Marca_Controller_Plugin_TrataParams());
    }
    
    /**
	 *  Carrega as regras de acesso
	 *
	 * @return void
	 */
	protected function _initAcl() {
        // Inicializa a classe de autentica��o
        $this->autenticacao = Marca_Auth::getInstance();
        
        if(isset($this->autenticacao) && $this->autenticacao->getIdentity() != "") {
            $this->front->registerPlugin(new Marca_Controller_Plugin_Acl());
        }
	}
    
	/**
     *  Inicializa o plugin que ir� gravar os logs de sess�o das transa��es e terminais de acesso    
     *
     *  @return void
     */
    protected function _initLogSessao() {
        // Inicializa a classe de autentica��o
        $this->autenticacao = Marca_Auth::getInstance();
        
        if(isset($this->autenticacao) && $this->autenticacao->getIdentity() != "") {
            $this->front->registerPlugin(new Marca_Controller_Plugin_LogSessao());
        }
    }
    
    /**
    *  Carrega o plugim de mensagem do sistema
    *
    * @return void
    */
    protected function _initMensagemSistema() {
        // Cria uma fila para guardar as mensagens do sistema
        $queue = new Marca_Queue('Array', array('name'=>'mensagemSistema'));

        // Registra a fila
        Zend_Registry::set("mensagemSistema", $queue);

        $this->front->registerPlugin(new Marca_Controller_Plugin_MensagemSistema());
    }
    
}