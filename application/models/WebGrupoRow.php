<?php
/**
 * Created on 02/08/2010
 *
 * Manipula a linha da classe GrupoModel
 *
 * @filesource
 * @author			David Valente, Márcio Souza Duarte
 * @copyright		Copyright 2010 Marca
 * @package			zendframework
 * @subpackage		zendframework.application.models
 * @version			1.0
 */

class WebGrupoRow extends Zend_Db_Table_Row_Abstract {

	/**
	 * (non-PHPdoc)
	 * @see library/Zend/Db/Table/Row/Zend_Db_Table_Row_Abstract#init()
	 */
	public function init(){

	}

    /**
     * Retorna os sistemas do grupo
     *
     * @return array
     */
    public function getSistemas(){

    	try {
    		// Pega o adaptador padrão do banco
			$db = Zend_Registry::get('db');

			// Busca todas as transações cadastradas para o usuário logado
			$select = $db->select()->from(array("grutran"=>"WEB_GRUPO_TRANSACAO"), array("DISTINCT sis.CD_SISTEMA, sis.NO_SISTEMA"))
								   ->join(array("tran"=>"WEB_TRANSACAO"), "grutran.CD_TRANSACAO = tran.CD_TRANSACAO", array())
								   ->join(array("menusis"=>"WEB_MENU_SISTEMA"), "menusis.CD_MENU = tran.CD_MENU", array())
								   ->join(array("sis"=>"WEB_SISTEMA"), "sis.CD_SISTEMA = menusis.CD_SISTEMA", array())
								   ->where("grutran.CD_GRUPO = {$this->CD_GRUPO}")
								   ->where("tran.OBJ_EXECUTADO IS NOT NULL");

			$sistemasGrupo = $db->fetchAll($select);
			$sistemas      = array();
	        foreach($sistemasGrupo as $sistema) {
	        	$sistemas[$sistema->CD_SISTEMA] = $sistema->NO_SISTEMA;
	        }

	        return $sistemas;

    	} catch (Exception $e) {
    		echo $e->getMessage();
    	}
    }
}
?>