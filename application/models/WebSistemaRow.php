<?php
/**
 * Created on 01/09/2010
 *
 * Manipula a linha da classe SistemaModel
 *
 * @filesource
 * @author			David Valente
 * @copyright		Copyright 2009 Marca
 * @package			zendframework
 * @subpackage		zendframework.application.models
 * @version			1.0
 */

class WebSistemaRow extends Zend_Db_Table_Row_Abstract {


    /**
     * Retorna todas as ABAS do sistema
     *
     * @return array
     */
    public function getWebMenuSistema($cd_usuario = ""){

    	try {

    		if ($cd_usuario == "") {

    			throw new Exception("Atenção: Código do usuário em branco.");
    		}

	    	// Pega o adaptador padrão do banco
			$db = Zend_Registry::get('db');

			// Busca todas as transações cadastradas para o usuário logado
			$select	= $db->select()->from(array("SIST"  => "WEB_SISTEMA"), array("SIST.NO_SISTEMA, MENU.CD_MENU, MENU.NO_MENU, MENU.ORD_MENU"))
								   ->join(array("MENU"  => "WEB_MENU_SISTEMA"),    "SIST.CD_SISTEMA   = MENU.CD_SISTEMA" , array())
								   ->join(array("TRAN"  => "WEB_TRANSACAO"),       "MENU.CD_MENU      = TRAN.CD_MENU" , array())
								   ->join(array("TACAO" => "WEB_TRANSACAO_ACAO"),  "TRAN.CD_TRANSACAO = TACAO.CD_TRANSACAO" , array())
								   ->join(array("ACAO"  => "WEB_ACAO"),            "TACAO.CD_ACAO     = ACAO.CD_ACAO" , array())
								   ->join(array("GRTRA" => "WEB_GRUPO_TRANSACAO"), "TRAN.CD_TRANSACAO = GRTRA.CD_TRANSACAO" , array())
								   ->join(array("GRUSU" => "WEB_GRUPO_USUARIO"),   "GRTRA.CD_GRUPO    = GRUSU.CD_GRUPO" , array())
								   ->where("GRUSU.CD_USUARIO = '{$cd_usuario}'")
								   ->where("TRAN.FL_VISIVEL  = 1")
								   ->where("SIST.CD_SISTEMA = {$this->CD_SISTEMA}")
								   ->where("TRAN.OBJ_EXECUTADO IS NOT NULL")
								   ->group("SIST.NO_SISTEMA, MENU.CD_MENU, MENU.NO_MENU, MENU.ORD_MENU")
								   ->order("MENU.ORD_MENU ASC");
            
			return $db->fetchAll($select);

    	} catch (Exception $e) {
    		echo $e->getMessage();
    	}
    }

}
?>