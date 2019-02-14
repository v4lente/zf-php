<?php
/**
 * Created on 01/09/2010
 *
 * Manipula a linha da classe SistemaModel
 *
 * @filesource
 * @author			David Valente
 * @copyright		Copyright 2010 Marca
 * @package			zendframework
 * @subpackage		zendframework.application.models
 * @version			1.0
 */

class WebMenuSistemaRow extends Zend_Db_Table_Row_Abstract {


    /**
     * Retorna todas as transações
     *
     * @return array
     */
    public function getWebTransacao($cd_usuario = ""){

    	try {

    		if ($cd_usuario == "") {

    			throw new Exception("Atenção: Código do usuário em branco.");
    		}

	    	// Pega o adaptador padrão do banco
			$db = Zend_Registry::get('db');
			
			// Busca todas as transações cadastradas para o usuário logado
			$select	= $db->select()->from(array("SIST"  => "WEB_SISTEMA"), array("SIST.NO_SISTEMA", 
																				 "MENU.CD_MENU", 
																				 "MENU.NO_MENU", 
																				 "TRAN.CD_TRANSACAO", 
																				 "TRAN.NO_TRANSACAO", 
																				 "OBJ_EXECUTADO" => new Zend_Db_Expr("CASE WHEN TRAN.AMB_DESENV = 'POWR' THEN REPLACE(TRAN.OBJ_EXECUTADO, '/','-') ELSE TRAN.OBJ_EXECUTADO END"), 
																				 "TRAN.ORD_TRANSACAO", 
																				 "TRAN.AMB_DESENV"))
								   ->join(array("MENU"  => "WEB_MENU_SISTEMA"),    "SIST.CD_SISTEMA         = MENU.CD_SISTEMA" ,    array())
								   ->join(array("TRAN"  => "WEB_TRANSACAO"),       "MENU.CD_MENU            = TRAN.CD_MENU" ,       array())
								   ->join(array("GRTRA" => "WEB_GRUPO_TRANSACAO"), "TRAN.CD_TRANSACAO       = GRTRA.CD_TRANSACAO" , array())
                                   ->join(array("ACAO"	=> "WEB_ACAO"), 	       "(TRAN.FL_TIPO_TRANSACAO  = ACAO.FL_TIPO_ACAO OR acao.FL_TIPO_ACAO = 'C')
                                                                               AND (GRTRA.FL_PERMISSAO      = ACAO.FL_PERMISSAO
                                                                                 OR ACAO.FL_PERMISSAO       = 'C')",                array())
								   ->join(array("GRUSU" => "WEB_GRUPO_USUARIO"),   "GRTRA.CD_GRUPO          = GRUSU.CD_GRUPO" ,     array())
								   ->where("TRAN.CD_MENU     = {$this->CD_MENU}")
								   ->where("GRUSU.CD_USUARIO = '{$cd_usuario}'")
								   ->where("TRAN.OBJ_EXECUTADO IS NOT NULL")
								   ->group("SIST.NO_SISTEMA, MENU.CD_MENU, MENU.NO_MENU, TRAN.CD_TRANSACAO, TRAN.NO_TRANSACAO, OBJ_EXECUTADO, TRAN.ORD_TRANSACAO, TRAN.AMB_DESENV")
								   ->order("TRAN.ORD_TRANSACAO ASC");

			return $db->fetchAll($select);

    	} catch (Exception $e) {
    		echo $e->getMessage();
    	}
    }
    
    
	/**
     * Retorna todas as ABAS do sistema para o usuário passado
     *
     * @return array
     */
    public function getAbasMenuSistema($cd_usuario = "") {

    	try {

    		if ($cd_usuario == "") {
    			throw new Exception("Atenção: Código do usuário em branco.");
    		}

	    	// Pega o adaptador padrão do banco
			$db = Zend_Registry::get('db');			
    	  
			// Busca todos os menus/transações cadastradas referentes ao sistema passado para o usuário logado
			$select	= $db->select()->from(array("SIST"  => "WEB_SISTEMA"), array("SIST.NO_SISTEMA", 
																				 "MENU.CD_MENU", 
																				 "MENU.NO_MENU", 
																				 "MENU.ORD_MENU",
																			     "TRAN.CD_TRANSACAO", 
																				 "TRAN.NO_TRANSACAO", 
																				 "TRAN.FL_NOVA_JANELA",  
																				 "OBJ_EXECUTADO" => new Zend_Db_Expr("CASE WHEN TRAN.AMB_DESENV = 'POWR' THEN REPLACE(TRAN.OBJ_EXECUTADO, '/','-') ELSE TRAN.OBJ_EXECUTADO END"), 
																				 "TRAN.ORD_TRANSACAO", 
                                                                                 "GRTRA.FL_PERMISSAO",
																				 "TRAN.AMB_DESENV",
																				 "SIST.NO_PASTA_ZEND",
																				 "NO_PASTA_ZEND2" => "SIST2.NO_PASTA_ZEND"))
								   ->join(array("MENU"  => "WEB_MENU_SISTEMA"),    "SIST.CD_SISTEMA         = MENU.CD_SISTEMA",     array())
								   ->join(array("TRAN"  => "WEB_TRANSACAO"),       "MENU.CD_MENU            = TRAN.CD_MENU",        array())
                                   ->join(array("GRTRA" => "WEB_GRUPO_TRANSACAO"), "TRAN.CD_TRANSACAO       = GRTRA.CD_TRANSACAO",  array())
								   ->join(array("ACAO"	=> "WEB_ACAO"), 	       "TRAN.FL_TIPO_TRANSACAO  = ACAO.FL_TIPO_ACAO 
                                                                               AND (GRTRA.FL_PERMISSAO      = ACAO.FL_PERMISSAO
                                                                                 OR ACAO.FL_PERMISSAO       = 'C')",                array())
								   ->join(array("GRUSU" => "WEB_GRUPO_USUARIO"),   "GRTRA.CD_GRUPO          = GRUSU.CD_GRUPO",      array())
								   ->joinLeft(array("SIST2" => "WEB_SISTEMA"),     "TRAN.CD_SISTEMA_ORIGEM  = SIST2.CD_SISTEMA",    array())
								   ->where("GRUSU.CD_USUARIO = '{$cd_usuario}'")
								   ->where("TRAN.FL_VISIVEL  = 1")
								   ->where("SIST.CD_SISTEMA = {$this->CD_SISTEMA}")
								   ->where("TRAN.OBJ_EXECUTADO IS NOT NULL")
								   ->where("TRUNC(SYSDATE) >= TRUNC(GRUSU.DT_ACESSO_INI)")
								   ->where("(GRUSU.DT_ACESSO_FIM  IS NULL OR TRUNC(SYSDATE) <= TRUNC(GRUSU.DT_ACESSO_FIM))")
                                   ->where("(GRTRA.DT_ACESSO_FIM  IS NULL OR TRUNC(SYSDATE) <= TRUNC(GRTRA.DT_ACESSO_FIM))")
								   ->group(array("SIST.NO_SISTEMA", 
										   		 "MENU.CD_MENU", 
										   		 "MENU.NO_MENU", 
										   		 "MENU.ORD_MENU",
									       	  	 "TRAN.CD_TRANSACAO", 
										   	     "TRAN.NO_TRANSACAO", 
										   	     "TRAN.FL_NOVA_JANELA",  
										   		 new Zend_Db_Expr("CASE WHEN TRAN.AMB_DESENV = 'POWR' THEN REPLACE(TRAN.OBJ_EXECUTADO, '/','-') ELSE TRAN.OBJ_EXECUTADO END"), 
										   		 "TRAN.ORD_TRANSACAO",
                                                 "GRTRA.FL_PERMISSAO",
										   		 "TRAN.AMB_DESENV",
										   		 "SIST.NO_PASTA_ZEND",
										   		 "SIST2.NO_PASTA_ZEND"))
								   ->order("MENU.ORD_MENU ASC, TRAN.ORD_TRANSACAO ASC");
            
			// Retorna os menus do usuário
			$res = $db->fetchAll($select);
            
            if(count($res) > 0) {
                
                // Percorre o objeto para ver se tem mais de uma permissão para a mesma transação.
                // Caso exista uma de consulta e manutenção, remove a de consulta.
                $cont = 0;
                $cd_transacao = "";
                $cd_transacao_ant = "";
                $ret = array();
                foreach($res as $linha) {
                    
                    if($cont == 0) {
                        $cd_transacao_ant = $linha->CD_TRANSACAO;
                        $ret[$cont] = $linha;
                        $cont++;
                    } else {
                        
                        // Se a transação for igual a anterior salva a permissão na anterior e não inclui a nova
                        if($cd_transacao_ant == $linha->CD_TRANSACAO) {
                            $linha->FL_PERMISSAO = 'M';
                            $ret[$cont - 1] = $linha;
                        } else {
                            $ret[$cont] = $linha;
                            $cont++;
                        }
                        
                    }
                    
                    // Grava a transação corrente para ser comparada com a próxima
                    $cd_transacao_ant = $linha->CD_TRANSACAO;
                    
                }
                
                return $ret;
            } else {
                return array();
            }

    	} catch (Exception $e) {
    		echo $e->getMessage();
    	}
    }  
}
?>