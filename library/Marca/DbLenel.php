<?php

require_once 'Zend/Config/Ini.php';

/**
 * Created on 15/05/2012
 *
 * Esta � uma interface usada para conectar no banco de dados Lenel
 *
 * @filesource
 * @author			M�rcio Souza Duarte
 * @copyright		Copyright 2009 Marca
 * @package			zendframework
 * @subpackage		zendframework.library
 * @version			1.0
 */
class Marca_DbLenel {
	
	/**
	 * DataBase
	 *
	 * @var Object
	 */
	protected $_dbLenel;
	
	/**
	 * Resultado da Consulta
	 *
	 * @var Array
	 */
	protected $_resultado;
	
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

		// Autentica a base
		$this->conectar();
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
    public function conectar() {

		// Carrega os dados do arquivo de configura��o do sistema (dados de conex�o, caminhos dos diretorios etc...)
    	$config = new Zend_Config_Ini('../application/configs/application.ini', APPLICATION_ENV);

    	$teste = "(DESCRIPTION = (ADDRESS = (PROTOCOL = TCP)(HOST = 10.76.1.13)(PORT = 1521)) (CONNECT_DATA = (SID = LENEL)))";

		try {
			// Conecta no oracle

			$dbLenel = oci_connect($this->_usuario, 
									$this->_senha, 
									$teste, 
									"WE8ISO8859P1");

		} catch(Exception $e) {
			echo "Erro de Conex�o: " . $e->getMessage();
			die();
		}

        // Registra o adaptador padr�o
        Zend_Registry::set('dbLenel', $dbLenel);

        // Grava na sess�o do usu�rio a conex�o
		$sessao = new Zend_Session_Namespace('portoweb');

		// Seta na sess�o o objeto do banco
    	$sessao->dbLenel = $dbLenel;

		// Seta o resultado da conex�o
		$this->_dbLenel = $dbLenel;

		// Altera a sess�o do oracle
//		$this->alterSession();
    }

	//Desconecta do banco de dados
	public function desconectar() {
		unset($sessao->dbLenel);
		return oci_close($this->_dbLenel);
	}
	
	// Altera a sess�o do oracle
	public function alterSession($paramSql=""){
		
		$sql = "alter session set nls_date_format = 'DD/MM/YYYY HH24:MI:SS'";
		$rs  = oci_parse($this->_dbLenel, $sql);
		oci_execute($rs, OCI_DEFAULT);

   		$sql = "alter session set NLS_NUMERIC_CHARACTERS='.,'";
		$rs  = oci_parse($this->_dbLenel, $sql);
		oci_execute($rs, OCI_DEFAULT);
		
		if ($paramSql != ""){	
			$rs = oci_parse($this->_dbLenel, $paramSql);
			oci_execute($rs, OCI_DEFAULT);
		}
		
		//Esse comando abaixo foi criado para resolver um problema do instant client para linux.
		//O problema s� existe na vers�o 10G em diante.
		$sql = "ALTER SESSION SET NLS_TERRITORY = 'AMERICA' ";
		$rs  = oci_parse($this->_dbLenel, $sql);
		if(!$rs) {
			echo 'Erro: Ocorreu algum problema durante a validacao'.OCIError($rs);
		}
		oci_execute($rs);
		
	}
	
	// Chama a consulta
	public function select($sql) {
		
		try {
		    
			//OCIParse an�lisa a 'consulta' (identificador de conex�o, meuSQL)
			$parse = @oci_parse($this->_dbLenel, $sql);
			if(@oci_execute($parse)) {
				
				// Seta os objetos da consulta
				while($linha = @oci_fetch_object($parse)) {
					// Seta o resultado da consulta
					$this->_resultado[] = $linha;
				}
				
				// Retorna o resultado da consulta
				return $this->_resultado;
				
			} else {
				$erro_select = ("<p>Erro Oracle: " . OCIError() . "</p>");
				throw new Exception($erro_select); //Msg de Erro
			}
			
		} catch(Exception $excecao) {
			//Exibe a msg de erro
			echo "Aconteceu um erro no resultado da consulta: " . $excecao->getMessage();
		}
		
	}
	
	// Retorna o resultado
	public function getResultado() {
		return $this->_resultado;
	}
	
}