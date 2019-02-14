<?php

/**
 * @see Marca_Db_Adapter_Oracle
 */
require_once 'Marca/Db/Adapter/Oracle.php';

/**
 * Created on 18/11/2009
 *
 * Modelo da classe AutenticacaoDBOracle
 *
 * Esta � uma interface usada para conectar no banco de dados
 *
 * @filesource
 * @author			M�rcio Souza Duarte
 * @copyright		Copyright 2009 Marca
 * @package			zendframework
 * @subpackage		zendframework.library
 * @version			1.0
 */
class Marca_Auth_Adapter_Autentica implements Zend_Auth_Adapter_Interface {

    /**
     * usuario
     *
     * @var string
     */
    protected $_usuario;

    /**
     * senha
     *
     * @var string
     */
    protected $_senha;

    /**
     * Construtor
     *
     * Seta o usu�rio e a senha para autentica��o
     *
     * @return void
     */
    public function __construct($usuario, $senha) {
        $this->_usuario = $usuario;
        $this->_senha   = $senha;
    }

    /**
     * Faz a autentica��o do usu�rio
     *
     * Retorna o resultado da autentica��o utilizado por este adaptador
     *
     * @throws Zend_Auth_Adapter_Exception Quando n�o conseguir validar
     *
     * @return Zend_Auth_Result
     */
    public function authenticate() {

    	// Carrega os dados do arquivo de configura��o do sistema (dados de conex�o, caminhos dos diretorios etc...)
    	$config      = new Zend_Config_Ini('../application/configs/application.ini', APPLICATION_ENV);

    	// Remove as aspas em volta das tabelas
    	$options     = array(Zend_Db::AUTO_QUOTE_IDENTIFIERS => FALSE);
		$arrayconfig = array(
					        'database' => array(
					            'adapter' => $config->resources->db->adapter,
					            'params'  => array(
					                'charset'          => $config->resources->db->params->charset,
					                'dbname'           => $config->resources->db->params->dbname,
					                'username'         => $this->_usuario,
					                'password'         => $this->_senha,
									'options'          => $options,
            		                'persistent'       => $config->resources->db->params->persistent,
		                            'adapterNamespace' => 'Marca_Db_Adapter'
					            )
					        )
					    );



		$config = new Zend_Config($arrayconfig);
		
		
		try {

			$db = Zend_Db::factory($config->database);
			$db->getConnection();

		} catch(Zend_Db_Adapter_Oracle_Exception $e) {

			switch ($e->getCode()) {

				case 1017: // Usu�rio ou senha inv�lido

					return new Marca_Auth_Result(Marca_Auth_Result::FAILURE_CREDENTIAL_INVALID, $this->_usuario, 
																  array("Usu�rio ou senha inv�lido."));

					break; 

				case 28000 : // Usu�rio bloqueado no banco

					return new Marca_Auth_Result(Marca_Auth_Result::FAILURE_ACCOUNT_USER_LOCKED, $this->_usuario, 
																  array("Conta do usu�rio est� bloqueada. Contate o CPD."), 3);

				default:

					// Retorna qualquer outro tipo de erro do banco
					return new Marca_Auth_Result(Marca_Auth_Result::FAILURE_UNCATEGORIZED, $this->_usuario, 
																  array("Conex�o n�o estabelecida. Contate o CPD"), 3);
			}
		            	 			        	
        }		    
		
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
        
        // Seta o adaptador principal
        Zend_Db_Table::setDefaultAdapter($db);
        
        // Registra o adaptador padr�o
        Zend_Registry::set('db', $db);
        
        // Grava na sess�o do usu�rio a conex�o
		$sessao = new Zend_Session_Namespace('portoweb');

		// Inicializa as Variaveis de sess�o
		$sessao->arrayconfig = $arrayconfig;

		// Seta na sess�o o objeto do banco
    	$sessao->db = $db;
    	
		// Retorna o resultado da conex�o
		return new Marca_Auth_Result(Marca_Auth_Result::SUCCESS, $this->_usuario);
    }
}