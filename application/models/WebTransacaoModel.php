<?php
/**
 * Created on 03/12/2009
 *
 * Modelo da classe TransacaoModel
 *
 * @filesource
 * @author          David
 * @copyright       Copyright 2009 Marca
 * @package         zendframework
 * @subpackage      zendframework.application.models
 * @version         1.0
 */

class WebTransacaoModel extends Marca_Db_Table_Abstract {

    /**
     * Nome da tabela relacionada
     *
     * @var string
     */
    protected $_name = 'WEB_TRANSACAO';

    /**
     * Chave primária da tabela
     *
     * @var string
     */
    protected $_primary = array('CD_TRANSACAO');

     /**
     * Classe para manipular a linha
     *
     * @var string
     */
    protected $_rowClass = 'WebTransacaoRow';
    
    
    /**
     * Desativa a exclusão lógica
     *
     * @var boolean
     */
    protected $_logicalDelete = false;
    

    /**
     * Tabelas dependente
     *
     * @var array
     */
    protected $_dependentTables = array('WebTransacaoAcaoModel');

    /**
	 *  Tabelas que a classe faz referencia
	 *
	 * @var array
	 */

    protected $_referenceMap = array ('Menu' => array ('columns'       => array ('CD_MENU'),
									 				   'refTableClass' => 'WebMenuSistemaModel',
													   'refColumns'    => array ('CD_MENU'))
									 );

    /**
	 *  Regra de negocio do modelo
	 *
	 * @var array
	 */
	protected $_rules = array(
							array('name'         =>'CD_TRANSACAO',
						          'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório',
						          'filter'       =>'intval'),

							array('name'         =>'NO_TRANSACAO',
							      'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório'),

							array('name'         =>'AMB_DESENV',
							      'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório',
							      'filter'       =>'Zend_Filter_StringToUpper'),

							array('name'         =>'ORD_TRANSACAO',
							      'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório',
							      'filter'       =>'intval'),
							
							array('name'         =>'CD_MENU',
							      'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório',
							      'filter'       =>'intval'),
							
							array('name'         =>'FORMAT_REL',
                                  'class'        =>'NotEmpty',
                                  'errorMessage' =>'Campo obrigatório',
                                  'filter'       =>'Zend_Filter_StringToUpper')
			   			   );

    /**
     * Retorna todas transações
     *
     * @return array
     */
    public function getTransacoes($cd_menu=""){

    	try {
    	    $where = null;
            if($cd_menu != "") {
                $where = "CD_MENU = {$cd_menu}";
            }
    	    
			// Busca todos as transacões cadastradas
			$todosTransacoes = $this->fetchAll($where, "NO_TRANSACAO ASC");
			$arrayRetorno    = array();
	        foreach($todosTransacoes as $linha) {
	        	$arrayRetorno[$linha->CD_TRANSACAO] = $linha->NO_TRANSACAO;
	        }

	        return $arrayRetorno;

    	} catch (Exception $e) {
    		echo $e->getMessage();
    	}
    }
    
    /**
     * 
     * Método responsável por buscar as transações
     * 
     * @param  array  $params Estes são os parâmetros de busca
     * @return object $select Objeto que contém os dados da query
     */
    public function queryBuscaTransacoes(&$params=array(), $extraWhere = array("1=1")) {
        
        // Monta a condição
        $where = $this->addWhere(array("WS.NO_SISTEMA     LIKE ?" => $params['no_sistema']))
                      ->addWhere(array("WT.AMB_DESENV     LIKE ?" => $params['amb_desenv']))
                      ->addWhere(array("WM.NO_MENU        LIKE ?" => $params['no_menu']))
                      ->addWhere(array("WT.FORMAT_REL     LIKE ?" => $params['format_rel']))
                      ->addWhere(array("WT.NO_TRANSACAO   LIKE ?" => $params['no_transacao']))
                      ->addWhere(array("WT.FL_VISIVEL        = ?" => $params['fl_visivel']))
                      ->addWhere(array("WT.DS_TRANSACAO   LIKE ?" => $params['ds_transacao']))
                      ->addWhere(array("WT.FL_PUBLICO        = ?" => $params['fl_publico']))
                      ->addWhere(array("WT.ORD_TRANSACAO     = ?" => $params['ord_transacao']))
                      ->addWhere(array("WT.FL_NOVA_JANELA    = ?" => $params['fl_nova_janela']))
                      ->addWhere(array("WT.FL_TIPO_TRANSACAO = ?" => $params['fl_tipo_transacao']))
                      ->addWhere(array("WS.CD_SISTEMA        = ?" => $params['cd_sistema']))     
        			  ->addWhere(array("WT.CD_TRANSACAO      = ?" => $params['cd_transacao']))
                      ->addWhere(array("WT.OBJ_EXECUTADO     = ?" => $params['obj_executado']))
                      ->addWhere(array("WS.NO_PASTA_ZEND     = ?" => $params['no_pasta_zend']))
                      ->addWhere($extraWhere)
                      ->getWhere();
        
        // Retorna as transações, menus e sistemas
        $select = $this->select()
                       ->setIntegrityCheck(false)
                       ->from(array("WT" => "WEB_TRANSACAO"), array("WT.CD_TRANSACAO",
                                                                    "WT.NO_TRANSACAO",
                                                                    "WT.DS_TRANSACAO",
                                                                    "WT.OBJ_EXECUTADO",
                                                                    "WT.AMB_DESENV",
                                                                    "WT.ORD_TRANSACAO",
                                                                    "WT.FORMAT_REL",
                                                                    "WT.FL_VISIVEL",
                                                                    "WT.FL_PUBLICO",
                       												"WT.FL_LOG_SESSAO",
                       												"WT.FL_RELATORIO",
                                                                    "WT.FL_NOVA_JANELA",
                                                                    "WT.FL_TIPO_TRANSACAO",
                                                                    "WM.CD_MENU",
                                                                    "WM.no_MENU",
                                                                    "WM.DS_MENU",
                                                                    "WM.ORD_MENU",
                                                                    "WS.CD_SISTEMA", 
                                                                    "WS.NO_SISTEMA", 
                                                                    "WS.DS_SISTEMA", 
                                                                    "WS.ORD_SISTEMA",
																	"WT.NO_ARQ_AJUDA"))
                       ->join(array("WM" => "WEB_MENU_SISTEMA"), "WT.CD_MENU = WM.CD_MENU", array())
                       ->join(array("WS" => "WEB_SISTEMA"), "WM.CD_SISTEMA = WS.CD_SISTEMA", array())
                       ->where($where)
                       ->order("WS.NO_SISTEMA ASC, WM.NO_MENU ASC, WT.NO_TRANSACAO ASC");
        
        // Retorna a consulta
        return $select;
        
    }
    
    
	/**
     * 
     * Método responsável por buscar os usuário ligados as transações da web e cliente/servidor
     * Interface – transações x Usuário
     * 
     * @param  array  $params Estes são os parâmetros de busca
     * @return object $select Objeto que contém os dados da query
     */
    public function queryBuscaTransacoesUsuariosWebCliServ(&$params=array()) {
        
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

        
        // Retorna a consulta podendo aplicar filtro e com ordenação
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
    
    
	/**
     * 
     * Método responsável por buscar os usuário ligados as transações da web e cliente/servidor vinculados no sistema
     * Interface – transações x Usuário
     * 
     * @param  array  $params Estes são os parâmetros de busca
     * @return object $select Objeto que contém os dados da query
     */
    public function queryBuscaSitemaTransacoesUsuarios(&$params=array(), $extraWhere1 = array("1=1"), $extraWhere2 = array("1=1")) {
        
        // Trata as datas
        $dataAcesso = "1=1";
        if($params['dt_acesso_ini'] != "" && $params['dt_acesso_fim'] == "") {
            $dataAcesso  = "(DT_ACESSO_INI >= TO_DATE('{$params['dt_acesso_ini']}', 'DD/MM/YYYY'))";
            
        } else if($params['dt_acesso_ini'] != "" && $params['dt_acesso_fim'] != "") {
            $dataAcesso  = "(DT_ACESSO_INI BETWEEN TO_DATE('{$params['dt_acesso_ini']}', 'DD/MM/YYYY') AND TO_DATE('{$params['dt_acesso_fim']}', 'DD/MM/YYYY') OR ";
            $dataAcesso .= "DT_ACESSO_FIM BETWEEN TO_DATE('{$params['dt_acesso_ini']}', 'DD/MM/YYYY') AND TO_DATE('{$params['dt_acesso_fim']}', 'DD/MM/YYYY'))";
        }
        
        // Define os filtros para a cosulta
        $where1 = $this->addWhere(array("WS.CD_SISTEMA    LIKE ?" => $params['cd_sistema']))
        			   ->addWhere(array("WT.CD_TRANSACAO  LIKE ?" => $params['cd_transacao']))
                       ->addWhere(array("U.NO_USUARIO     LIKE ?" => $params['no_usuario']))
                       ->addWhere(array(new Zend_Db_Expr($dataAcesso)))                      
                       ->getWhere();

        // Retorna a consulta dos sistemas, transações x usuários da web
        $select1 = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(array("WS" => "WEB_SISTEMA"), array("WS.NO_SISTEMA",
                        										   "CD_SISTEMA" => new Zend_Db_Expr("TO_CHAR(WS.CD_SISTEMA)"),
											                       "AMBIENTE" => new Zend_Db_Expr("'WEB'"),
															       "WT.NO_TRANSACAO",
                        										   "CD_TRANSACAO" => new Zend_Db_Expr("TO_CHAR(WT.CD_TRANSACAO)"),         
															       "U.NO_USUARIO",
                        										   "U.CD_USUARIO", 
															       "WA.NO_ACAO",
															       "WGT.DT_ACESSO_INI",
															       "WGT.DT_ACESSO_FIM"))
                        ->join(array("WMS" => "WEB_MENU_SISTEMA"),   "WS.CD_SISTEMA   = WMS.CD_SISTEMA",   array())
                        ->join(array("WT"  => "WEB_TRANSACAO"),      "WMS.CD_MENU     = WT.CD_MENU",       array())
                        ->join(array("WGT" => "WEB_GRUPO_TRANSACAO"),"WT.CD_TRANSACAO = WGT.CD_TRANSACAO", array())
                        ->join(array("WGU" => "WEB_GRUPO_USUARIO"),  "WGT.CD_GRUPO    = WGU.CD_GRUPO",     array())
                        ->join(array("WA"  => "WEB_ACAO"),           "WGT.CD_ACAO     = WA.CD_ACAO",       array())
                        ->join(array("U"   => "USUARIO"),            "WGU.CD_USUARIO  = U.CD_USUARIO",     array())
                        ->where($where1)
                        ->where($extraWhere1);

                        
		 // Define os filtros para a cosulta
        $where2 = $this->addWhere(array("S.CD_SISTEMA    LIKE ?" => $params['cd_sistema']))
        		   	   ->addWhere(array("T.CD_TRANSACAO  LIKE ?" => $params['cd_transacao']))
                       ->addWhere(array("U.NO_USUARIO    LIKE ?" => $params['no_usuario']))
                       ->addWhere(array(new Zend_Db_Expr($dataAcesso)))                      
                       ->getWhere();
                                              
        // Retorna a consulta dos sistemas, trasações x usuários do cliente/servidor
        $select2 = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(array("S" => "SISTEMA"), array("S.NO_SISTEMA", 
                        									  "S.CD_SISTEMA",
                        									  "AMBIENTE" => new Zend_Db_Expr("'CLIENTE/SERVIDOR'"),
        													  "T.NO_TRANSACAO", 
                        									  "T.CD_TRANSACAO",
        													  "U.NO_USUARIO",
                        									  "U.CD_USUARIO", 
                                                              "NO_ACAO" => new Zend_Db_Expr("DECODE(AT.FL_TIPO_OPER, 'C', 'CONSULTA', 'MANUTENÇÃO')"),
                                                              "AT.DT_ACESSO_INI",
                                                              "AT.DT_ACESSO_FIM"))
                        ->join(array("T"  => "TRANSACAO"),        "S.CD_SISTEMA  = T.CD_SISTEMA", array())
                        ->join(array("AT" => "ACESSO_TRANSACAO"), "T.CD_SISTEMA  = AT.CD_SISTEMA AND T.CD_TRANSACAO = AT.CD_TRANSACAO", array())
                        ->join(array("UG" => "USUARIO_GRUPO"),    "AT.CD_GRUPO   = UG.CD_GRUPO",  array())
                        ->join(array("U"  => "USUARIO"),          "UG.CD_USUARIO = U.CD_USUARIO", array())
                        ->where($where2)
                        ->where($extraWhere2);

        // Une as consultas
        $union = $this->select()
                      ->setIntegrityCheck(false)
                      ->union(array($select1, $select2));
        
        // Retorna a consulta podendo aplicar filtro e com ordenação
        $select3 = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(array("U" => $union), array("U.NO_SISTEMA",
                        								   "U.CD_SISTEMA", 
                                    				 	   "U.AMBIENTE", 
                                    					   "U.NO_TRANSACAO", 
                        								   "U.CD_TRANSACAO",
                                    					   "U.NO_USUARIO",
                        								   "U.NO_ACAO",
                        								   "U.CD_USUARIO",  
                                    					   "DT_ACESSO_INI" => new Zend_Db_Expr("TO_CHAR(DT_ACESSO_INI, 'DD/MM/YYYY')"), 
                                    					   "DT_ACESSO_FIM" => new Zend_Db_Expr("TO_CHAR(DT_ACESSO_FIM, 'DD/MM/YYYY')")));

        // Se for para agrupar por transação                
		if ($params['agrupamento'] == "T") {
        	$select3->order(array(new Zend_Db_Expr("NO_SISTEMA ASC, NO_TRANSACAO ASC, NO_USUARIO ASC, DT_ACESSO_INI ASC, DT_ACESSO_FIM NULLS FIRST, NO_ACAO ASC")));	
        } else {
        	$select3->order(array(new Zend_Db_Expr("NO_USUARIO ASC, NO_SISTEMA ASC, NO_TRANSACAO ASC, DT_ACESSO_INI ASC, DT_ACESSO_FIM NULLS FIRST, NO_ACAO ASC")));	
        }
        // Retorna a consulta
        return $select3;        
    }
}
?>