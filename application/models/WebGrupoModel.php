<?php
/**
 * Created on 03/12/2009
 *
 * Modelo da classe GrupoModel
 *
 * @filesource
 * @author          David
 * @copyright       Copyright 2009 Marca
 * @package         zendframework
 * @subpackage      zendframework.application.models
 * @version         1.0
 */

class WebGrupoModel extends Marca_Db_Table_Abstract {

    /**
     * Nome da tabela relacionada
     *
     * @var string
     */
    protected $_name = 'WEB_GRUPO';

    /**
     * Chave primбria da tabela
     *
     * @var string
     */
    protected $_primary = array('CD_GRUPO');

    /**
     * Classe para manipular a linha
     *
     * @var string
     */
    protected $_rowClass = 'WebGrupoRow';
    
    
    /**
     * Desativa a exclusгo lуgica
     *
     * @var boolean
     */
    protected $_logicalDelete = false;
    

    /**
     * Tabelas dependente
     *
     * @var array
     */
    protected $_dependentTables = array('WebGrupoUsuarioModel', 'WebGrupoTransacaoModel');

    /**
	 *  Regra de negocio do modelo
	 *
	 * @var array
	 */
	protected $_rules = array(
							array('name'         =>'CD_GRUPO',
						          'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatуrio',
						          'filter'       =>'intval'),

							array('name'         =>'NO_GRUPO',
							      'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatуrio')
			   			   );

	
   
	/**
     * Retorna todos os grupos
     *
     * @return array
     */
    public function getGrupos(){

    	try {
			// Busca todos os grupos cadastradas
			$todosGrupos = $this->fetchAll(null, "NO_GRUPO ASC");
			$grupos      = array();
	        foreach($todosGrupos as $grupo) {
	        	$grupos[$grupo->CD_GRUPO] = $grupo->NO_GRUPO;
	        }

	        return $grupos;

    	} catch (Exception $e) {
    		echo $e->getMessage();
    	}
    }
    
    /**
     * 
     * Mйtodo responsбvel por buscar os grupos
     * 
     * @param  array  $params Estes sгo os parвmetros de busca
     * @return object $select Objeto que contйm os dados da query
     */
    public function queryBuscaGrupos(&$params=array()) {
        
        // Define os filtros para a cosulta
        $where = $this->addWhere(array("WG.CD_GRUPO        = ?" => $params['cd_grupo']))
                      ->addWhere(array("WG.NO_GRUPO     LIKE ?" => $params['no_grupo']))
                      ->addWhere(array("WG.DS_GRUPO     LIKE ?" => $params['ds_grupo']))
                      ->addWhere(array("WG.FL_ATIVO     = ?" => $params['ativo']))
                      ->getWhere();
        
        // Retorna as transaзхes, menus e sistemas
        $select = $this->select()
                       ->setIntegrityCheck(false)
                       ->distinct()
                       ->from(array("WG"  => "WEB_GRUPO"), array("WG.FL_ATIVO", "WG.CD_GRUPO", 
                                                                 "WG.NO_GRUPO",
                                                                 "WG.DS_GRUPO"))
                       ->where($where)
                       ->order("WG.NO_GRUPO, WG.CD_GRUPO ASC");
        
        // Retorna a consulta
        //echo $select; die;
        return $select;
        
    }
    
	/**
     * 
     * Mйtodo responsбvel por buscar os grupos para web e cliente/servidor
     * 
     * @param  array  $params Estes sгo os parвmetros de busca
     * @return object $select Objeto que contйm os dados da query
     */
    public function queryBuscaGruposWebCliServ(&$params=array()) {
        
        // Define os filtros para a cosulta
        $where = $this->addWhere(array("CD_GRUPO    = ?" => $params['cd_grupo']))
                      ->addWhere(array("NO_GRUPO LIKE ?" => $params['no_grupo']))
                      ->getWhere();
        
        // Retorna a consulta dos grupos da web
        $select1 = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(array("WG" => "WEB_GRUPO"), array("CD_GRUPO" => new Zend_Db_Expr("TO_CHAR(WG.CD_GRUPO)"), 
                                                                 "WG.NO_GRUPO", 
                                                                 "AMBIENTE" => new Zend_Db_Expr("'WEB'")));
        
        // Retorna a consulta dos grupos do cliente/servidor
        $select2 = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(array("G" => "GRUPO"), array("G.CD_GRUPO", 
                                                            "G.NO_GRUPO", 
                                                            "AMBIENTE" => new Zend_Db_Expr("'CLIENTE/SERVIDOR'")));
        
        // Une as consultas
        $union = $this->select()
                      ->setIntegrityCheck(false)
                      ->union(array($select1, $select2));

        
        // Retorna a consulta podendo aplicar filtro e com ordenaзгo
        $select3 = $this->select()
                        ->setIntegrityCheck(false)
                        ->from($union, array("CD_GRUPO", "NO_GRUPO", "AMBIENTE"))
                        ->where($where)
                        ->order("NO_GRUPO ASC");
        
        // Retorna a consulta
        return $select3;
        
    }
    
    
	/**
     * 
     * Mйtodo responsбvel por buscar os grupos ligados aos usuбrios da web e cliente/servidor
     * Interface – Grupo de Acesso x Usuбrio
     * 
     * @param  array  $params Estes sгo os parвmetros de busca
     * @return object $select Objeto que contйm os dados da query
     */
    public function queryBuscaGruposUsuariosWebCliServ(&$params=array()) {
        
        // Trata as datas
        $dataAcesso = "1=1";
        if($params['dt_acesso_ini'] != "" && $params['dt_acesso_fim'] == "") {
            $dataAcesso  = "(U.DT_ACESSO_INI >= TO_DATE('{$params['dt_acesso_ini']}', 'DD/MM/YYYY'))";
            
        } else if($params['dt_acesso_ini'] != "" && $params['dt_acesso_fim'] != "") {
            $dataAcesso  = "(U.DT_ACESSO_INI BETWEEN TO_DATE('{$params['dt_acesso_ini']}', 'DD/MM/YYYY') AND TO_DATE('{$params['dt_acesso_fim']}', 'DD/MM/YYYY') OR ";
            $dataAcesso .= "U.DT_ACESSO_FIM BETWEEN TO_DATE('{$params['dt_acesso_ini']}', 'DD/MM/YYYY') AND TO_DATE('{$params['dt_acesso_fim']}', 'DD/MM/YYYY'))";
        }
        
        // Define os filtros para a cosulta
        $where = $this->addWhere(array("U.CD_GRUPO      LIKE ?" => $params['cd_grupo']))
                      ->addWhere(array("U.CD_USUARIO    LIKE ?" => $params['cd_usuario']))
                      ->addWhere(array(new Zend_Db_Expr($dataAcesso)))
                      ->getWhere();
        
        // Retorna a consulta dos grupos da web
        $select1 = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(array("WG" => "WEB_GRUPO"), array("U.CD_USUARIO",
                                                                 "U.NO_USUARIO",
                                                                 "CD_GRUPO" => new Zend_Db_Expr("TO_CHAR(WG.CD_GRUPO)"),
                                                                 "WG.NO_GRUPO",
                                                                 "WGU.DT_ACESSO_INI",
                                                                 "WGU.DT_ACESSO_FIM",
                                                                 "AMBIENTE" => new Zend_Db_Expr("'WEB'")))
                        ->joinLeft(array("WGU" => "WEB_GRUPO_USUARIO"), "WG.CD_GRUPO    = WGU.CD_GRUPO", array())
                        ->joinLeft(array("U"   => "USUARIO"),           "WGU.CD_USUARIO = U.CD_USUARIO", array());
        
        // Retorna a consulta dos grupos do cliente/servidor
        $select2 = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(array("G" => "GRUPO"), array("U.CD_USUARIO",
        													"U.NO_USUARIO", 
        													"G.CD_GRUPO", 
                                                            "G.NO_GRUPO",
                                                            "UG.DT_ACESSO_INI",
                                                            "UG.DT_ACESSO_FIM", 
                                                            "AMBIENTE" => new Zend_Db_Expr("'CLIENTE/SERVIDOR'")))
                        ->joinLeft(array("UG" => "USUARIO_GRUPO"), "G.CD_GRUPO    = UG.CD_GRUPO", array())
                        ->joinLeft(array("U"  => "USUARIO"),       "UG.CD_USUARIO = U.CD_USUARIO", array());
        
        // Une as consultas
        $union = $this->select()
                      ->setIntegrityCheck(false)
                      ->union(array($select1, $select2));

        
        // Retorna a consulta podendo aplicar filtro e com ordenaзгo
        $select3 = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(array("U" => $union), array("U.CD_USUARIO", 
                                    				 	   "U.NO_USUARIO", 
                                    					   "U.CD_GRUPO", 
                                    					   "U.NO_GRUPO", 
                                    					   "DT_ACESSO_INI" => new Zend_Db_Expr("TO_CHAR(U.DT_ACESSO_INI, 'DD/MM/YYYY')"), 
                                    					   "DT_ACESSO_FIM" => new Zend_Db_Expr("TO_CHAR(U.DT_ACESSO_FIM, 'DD/MM/YYYY')"), 
                                    					   "U.AMBIENTE"))
                        ->where($where)
                        ->order(array("U.NO_GRUPO ASC", "U.NO_USUARIO ASC", "U.DT_ACESSO_FIM DESC"));
        
        // Retorna a consulta
        return $select3;
        
    }
    
	/**
     * 
     * Mйtodo responsбvel por buscar os usuбrio ligados aos grupos da web e cliente/servidor
     * Interface – Usuбrio x Grupo de Acesso
     * 
     * @param  array  $params Estes sгo os parвmetros de busca
     * @return object $select Objeto que contйm os dados da query
     */
    public function queryBuscaUsuariosGruposWebCliServ(&$params=array()) {
        
        // Trata as datas
        $dataAcesso = "1=1";
        if($params['dt_acesso_ini'] != "" && $params['dt_acesso_fim'] == "") {
            $dataAcesso  = "(U.DT_ACESSO_INI >= TO_DATE('{$params['dt_acesso_ini']}', 'DD/MM/YYYY'))";
            
        } else if($params['dt_acesso_ini'] != "" && $params['dt_acesso_fim'] != "") {
            $dataAcesso  = "(U.DT_ACESSO_INI BETWEEN TO_DATE('{$params['dt_acesso_ini']}', 'DD/MM/YYYY') AND TO_DATE('{$params['dt_acesso_fim']}', 'DD/MM/YYYY') OR ";
            $dataAcesso .= "U.DT_ACESSO_FIM BETWEEN TO_DATE('{$params['dt_acesso_ini']}', 'DD/MM/YYYY') AND TO_DATE('{$params['dt_acesso_fim']}', 'DD/MM/YYYY'))";
        }
        
        // Define os filtros para a cosulta
        $where = $this->addWhere(array("U.CD_GRUPO      LIKE ?" => $params['cd_grupo']))
                      ->addWhere(array("U.CD_USUARIO    LIKE ?" => $params['cd_usuario']))
                      ->addWhere(array(new Zend_Db_Expr($dataAcesso)))
                      ->getWhere();
        
        // Retorna a consulta dos grupos da web
        $select1 = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(array("WG" => "WEB_GRUPO"), array("U.CD_USUARIO",
                                                                 "U.NO_USUARIO",
                                                                 "CD_GRUPO" => new Zend_Db_Expr("TO_CHAR(WG.CD_GRUPO)"),
                                                                 "WG.NO_GRUPO",
                                                                 "WGU.DT_ACESSO_INI",
                                                                 "WGU.DT_ACESSO_FIM",
                                                                 "AMBIENTE" => new Zend_Db_Expr("'WEB'")))
                        ->joinLeft(array("WGU" => "WEB_GRUPO_USUARIO"), "WG.CD_GRUPO    = WGU.CD_GRUPO", array())
                        ->joinLeft(array("U"   => "USUARIO"),           "WGU.CD_USUARIO = U.CD_USUARIO", array());
        
        // Retorna a consulta dos grupos do cliente/servidor
        $select2 = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(array("G" => "GRUPO"), array("U.CD_USUARIO",
        													"U.NO_USUARIO", 
        													"G.CD_GRUPO", 
                                                            "G.NO_GRUPO",
                                                            "UG.DT_ACESSO_INI",
                                                            "UG.DT_ACESSO_FIM", 
                                                            "AMBIENTE" => new Zend_Db_Expr("'CLIENTE/SERVIDOR'")))
                        ->joinLeft(array("UG" => "USUARIO_GRUPO"), "G.CD_GRUPO    = UG.CD_GRUPO", array())
                        ->joinLeft(array("U"  => "USUARIO"),       "UG.CD_USUARIO = U.CD_USUARIO", array());
        
        // Une as consultas
        $union = $this->select()
                      ->setIntegrityCheck(false)
                      ->union(array($select1, $select2));

        
        // Retorna a consulta podendo aplicar filtro e com ordenaзгo
        $select3 = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(array("U" => $union), array("U.CD_USUARIO", 
                                    				 	   "U.NO_USUARIO", 
                                    					   "U.CD_GRUPO", 
                                    					   "U.NO_GRUPO", 
                                    					   "DT_ACESSO_INI" => new Zend_Db_Expr("TO_CHAR(U.DT_ACESSO_INI, 'DD/MM/YYYY')"), 
                                    					   "DT_ACESSO_FIM" => new Zend_Db_Expr("TO_CHAR(U.DT_ACESSO_FIM, 'DD/MM/YYYY')"),
                                    					   "U.AMBIENTE"))
                        ->where($where)
                        ->order(array("U.NO_USUARIO ASC", "U.NO_GRUPO ASC", "U.DT_ACESSO_FIM DESC"));
        
        // Retorna a consulta
        return $select3;
        
    }
        
}
?>