<?php
/**
 * Helper que define as regras, recursos e privilégios da ACL
 *
 * @category   Marca Sistemas
 * @copyright  Copyright (c) 1991-2009 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 */
class Marca_Controller_Helper_Acl {

	private $_acl;

	private $_usuario;

	private $_regras = array();

	private $_recursosPadrao = array();

	public function __construct() {

		Zend_Loader::loadClass("UsuarioModel");
		Zend_Loader::loadClass("WebGrupoTransacaoModel");

		$autenticacao = Zend_Auth::getInstance();

        // Captura e valida a sessão do usuário
        $this->_usuario = strtoupper($autenticacao->getIdentity());

        // Instancia a classe que controla a lista de controle de acesso
        $this->_acl     = new Zend_Acl();

        // Define os controles padrões
        $this->_recursosPadrao = array('index',
                                       'error',
                                       'captcha',
                                       'teste',
                                       'menu',
                                       'login',
                                       'pick-list',
                                       'gera-rel',
                                       'exec-rel',
                                       'rfb',
                                       'jasper');
	}
    
	/**
	 * Seta as regras (Grupos)
	 *
	 * @return void
	 */
	public function setRoles() {
		// Remove todas as regras
		$this->_acl->removeRoleAll();

		// Pega o adaptador padrão do banco
		$db = Zend_Registry::get('db');
		
		// Busca a sessão portoweb existente
		$portoweb = new Zend_Session_Namespace('portoweb');
        
        $selGrupo = $db->select()
                       ->from(array("WGT" => "WEB_GRUPO_TRANSACAO", array("WGT.CD_GRUPO")))
                       ->group(array("WGT.CD_GRUPO"));
        
		// Busca os grupos do usuário logado
		$where = "UPPER(TRIM(WGU.CD_USUARIO)) = UPPER(TRIM('{$this->_usuario}'))";
		$select = $db->select()
					 ->distinct()
					 ->from(array("WGU" => "WEB_GRUPO_USUARIO"), array("WGU.CD_GRUPO"))
					 ->where($where)
                     ->where("EXISTS (" . $selGrupo . ")");
		
		// Captura todos os grupos do usuário
		$usuarioGrupo = $db->fetchAll($select);
		
		// Seta as regras(Grupos) para o usuário logado
		foreach($usuarioGrupo as $linha) {
			
			// Verifica se ja foi registrada a regra, 
			// Não pode ter regra duplicada, gera um erro.
			if (! in_array($linha->CD_GRUPO, $this->_regras)) {
				$this->_acl->addRole(new Zend_Acl_Role($linha->CD_GRUPO));
				$this->_regras[] = $linha->CD_GRUPO;
			} 
			
		}
		
		// Joga os códigos de grupos na sessão
		$portoweb->perfil->CD_GRUPO = $this->_regras;
		
		$this->_acl->addRole(new Zend_Acl_Role('alguem'));
	}

	/**
	 * Seta os recursos (Sistemas/Controladores)
	 * [recurso] -> [sistema/modulo :controlador] - [herança]
	 *
	 * @return void
	 */
	public function setResources() {
		// Pega o adaptador padrão do banco
		$db = Zend_Registry::get('db');

		// Busca todas as transações cadastradas para o usuário logado
		$select = $db->select()
		             ->distinct()
		             ->from(array("grutrans" => "WEB_GRUPO_TRANSACAO"), array("sist.NO_SISTEMA", 
		             														  "sist.NO_PASTA_ZEND", 
		             														  "trans.NO_TRANSACAO", 
		             														  "trans.OBJ_EXECUTADO",
		             														  "TEMP.NO_PASTA_ZEND2"))
					 ->join(array("trans"    => "WEB_TRANSACAO"), 	  "grutrans.CD_TRANSACAO = trans.CD_TRANSACAO", array())
					 ->join(array("menusis"  => "WEB_MENU_SISTEMA"),  "menusis.CD_MENU 		 = trans.CD_MENU",      array())
					 ->join(array("sist"     => "WEB_SISTEMA"), 	  "sist.CD_SISTEMA 		 = menusis.CD_SISTEMA", array())
					 ->join(array("grupusu"  => "WEB_GRUPO_USUARIO"), "grupusu.CD_GRUPO 	 = grutrans.CD_GRUPO",  array())
					 ->joinLeft(array("TEMP" => new Zend_Db_Expr("(SELECT CD_SISTEMA, NO_PASTA_ZEND AS NO_PASTA_ZEND2 FROM WEB_SISTEMA)")), "trans.CD_SISTEMA_ORIGEM = TEMP.CD_SISTEMA", array())
					 ->where("UPPER(TRIM(grupusu.CD_USUARIO)) = '{$this->_usuario}'")
					 ->where("trans.AMB_DESENV = 'ZEND'")
					 ->where("trans.OBJ_EXECUTADO IS NOT NULL")
					 ->order("sist.NO_SISTEMA");
        
		$transacoes_rows = $db->fetchAll($select);
        
		// Pega todas as transações do sistema
		$transacoes = array();
		foreach($transacoes_rows as $transacao){
			
		    // Controla se o objeto executado tem o módulo incluso
			if(strpos(strtolower($transacao->OBJ_EXECUTADO), "default") !== false) {
			    $divObjExec = explode("/", $transacao->OBJ_EXECUTADO);
			    $transacao->OBJ_EXECUTADO = $divObjExec[1]; 
			}
			
			// Se a transação estiver apontando para outro sistema, pega a pasta do segundo 
			// sistema referenciado em vez da pasta do sistema original vinculado
			if($transacao->NO_PASTA_ZEND2 != "") {
				$transacao->NO_PASTA_ZEND = $transacao->NO_PASTA_ZEND2;
			}
			
			// Verifica se o recurso não está no array ainda
			$obj_executado = $transacao->NO_PASTA_ZEND . "/" . $transacao->OBJ_EXECUTADO;
			if(! in_array($obj_executado, $transacoes)) {
    			$transacoes[] = $obj_executado;
			}
		}
		
	   // Pega todas as transações do sistema
        foreach($transacoes as $indice => $valor){
            // Separa o modulo e controlador
            list($modulo, $controlador) = explode("/", $valor);

            // Verifica se o MÓDULO/SISTEMA for diferente do padrão (DEFAULT)
            if(strtolower($modulo) != "default") {
                // [recurso] - [sistema/modulo - :controlador] - [herança]
                $this->_acl->addResource(new Zend_Acl_Resource(trim($modulo) . ':' . trim($controlador)));
            }
        }

		$totRecursoPadrao = count($this->_recursosPadrao);
		for($i=0; $i < $totRecursoPadrao; $i++) {
			// Cria os recursos padrões
			$this->_acl->addResource(new Zend_Acl_Resource('default:' . $this->_recursosPadrao[$i]));
		}
        
	}

	/**
	 * Seta os privilégios dos grupos para cada sistema/Controlador
	 * define as ações dentro dos controladores que o grupo tem permissão
	 *
	 * @return void
	 */
	public function setPrivilegios() {
		// Tira todas as permissões
		$this->_acl->deny();

		$acessoTransacoes = new WebGrupoTransacaoModel();
		$usuarios         = new UsuarioModel();
		$usuario          = $usuarios->fetchRow("UPPER(TRIM(CD_USUARIO)) = UPPER(TRIM('{$this->_usuario}'))");
		$transacoes_rows  = $usuario->getTransacoes();
        
		foreach($transacoes_rows as $linha_transacao) {
		    
		    // Controla se o objeto executado tem o módulo incluso
			if(strpos(strtolower($linha_transacao->OBJ_EXECUTADO), "default") !== false) {
			    $divObjExec = explode("/", $linha_transacao->OBJ_EXECUTADO);
			    $linha_transacao->OBJ_EXECUTADO = $divObjExec[1]; 
			}
			
			// Se a transação estiver apontando para outro sistema, pega a pasta do segundo
			// sistema referenciado em vez da pasta do sistema original vinculado
			if($linha_transacao->NO_PASTA_ZEND2 != "") {
				$linha_transacao->NO_PASTA_ZEND = $linha_transacao->NO_PASTA_ZEND2;
			}
		    
			// [Usuário/Perfil] - [sistema/modulo - :controlador] - [action]
        	$this->_acl->allow($linha_transacao->CD_GRUPO, trim($linha_transacao->NO_PASTA_ZEND) . ':' . trim($linha_transacao->OBJ_EXECUTADO), $linha_transacao->NO_ACAO);
		}
        
		$totRecursoPadrao = count($this->_recursosPadrao);
		$gruposUsuario    = $usuario->getGrupos();

		// Seta os recursos padrões para os grupos do usuario
		foreach ($gruposUsuario as $grupo){
			for($i=0; $i < $totRecursoPadrao; $i++) {
				// Cria os recursos padrões
				$this->_acl->allow($grupo->CD_GRUPO, 'default:' . $this->_recursosPadrao[$i], null);
			}
		}

	}

	/**
	 * Retorno o objeto da ACL
	 *
	 * @return object
	 */
	public function getAcl() {
		return $this->_acl;
	}

	/**
	 * Retorna as regras do usuário logado
	 *
	 * @return array
	 */
	public function getRegras() {
		return $this->_regras;
	}

	/**
	 * Retorna os recursos fixos padrão
	 *
	 * @return array
	 */
	public function getRecursosPadrao() {
		return $this->_recursosPadrao;
	}

}