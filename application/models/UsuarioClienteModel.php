<?php
/**
 * Created on 27/04/2011
 *
 * Modelo da classe UsuarioInternetModel
 *
 * @filesource
 * @author			Márcio Souza Duarte
 * @copyright		Copyright 2009 Marca
 * @package			zendframework
 * @subpackage		zendframework.application.models
 * @version			1.0
 */

class UsuarioClienteModel extends Marca_Db_Table_Abstract {

    /**
     * Nome da tabela relacionada
     *
     * @var string
     */
    protected $_name = 'USUARIO_CLIENTE';

    /**
     * Chave primária da tabela
     *
     * @var string
     */
    protected $_primary = array('CD_CLIENTE', 'CD_USUARIO');

    /**
     * Desativa a exclusão lógica
     *
     * @var boolean
     */
    protected $_logicalDelete = false;
    

    /**
	 *  Regra de negocio do modelo
	 *
	 * @var array
	 */
	protected $_rules = array(
							array('name'         =>'CD_CLIENTE',
						          'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório',
						          'filter'       =>'intval'),

							array('name'         =>'CD_USUARIO',
						          'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório',
						          'filter'       =>'Zend_Filter_StringToUpper'),
							
							array('name'         =>'FL_PRINCIPAL',
						          'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório',
						          'filter'       =>'intval')
			   			   );
			   			   
			   			   
			   			   
    /**
     * Retorna todos os usuarios clientes do usuário
     *
     * @return array
     */
    public function getClientesUsuario($cd_usuario=""){

        try {
            
            // Verifica se o código do usuário foi passado
            $where = null;
            if($cd_usuario != "") {
                $where = "UC.CD_USUARIO LIKE '{$cd_usuario}'";
            }
            
            // Busca os clientes do usuário
            $select = $this->select()
                           ->distinct()
                           ->setIntegrityCheck(false)
                           ->from(array("UC" => "USUARIO_CLIENTE"), array("C.CD_CLIENTE",
                                                                          "C.RAZAO_SOCIAL"))
                           ->join(array("C" => "CLIENTE"), "UC.CD_CLIENTE = C.CD_CLIENTE", array())
                           ->where($where)
                           ->order("C.CD_CLIENTE ASC");
            
            // Busca todos os grupos cadastradas
            $todosClientes = $this->fetchAll($select);
            $clientes      = array();
            foreach($todosClientes as $cliente) {
                $clientes[$cliente->CD_CLIENTE] = $cliente->RAZAO_SOCIAL;
            }

            return $clientes;

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
    
    /**
     * Retorna a consulta com os dados dos cliente
     *
     * @return array
     */
    public function queryBuscaClientesUsuario(&$params = array()){

        try {
            
            // Define os filtros para a cosulta
            $where = $this->addWhere(array("UC.CD_USUARIO 	= ?" => $params['cd_usuario']))
                          ->addWhere(array("UC.FL_PRINCIPAL = ?" => $params['fl_principal']))
                          ->getWhere();
            
            // Busca os clientes do usuário
            $select = $this->select()
                           ->distinct()
                           ->setIntegrityCheck(false)
                           ->from(array("UC" => "USUARIO_CLIENTE"), array("C.CD_CLIENTE",
                                                                          "NR_DOC" => "F_FORMATA_CNPJCPF(C.NR_DOC)",
                                                                          "C.TP_DOC",
                                                                          "C.FL_FIS_JUR_EST",
                                                                          "C.NO_FANTASIA",
                                                                          "C.RAZAO_SOCIAL",
                                                                          "C.ENDERECO",
                                                                          "C.BAIRRO",
                                                                          "C.CD_MUNIC",
                                                                          "C.CEP",
                                                                          "C.FONE",
                                                                          "C.FONE2",
                                                                          "C.FAX",
                                                                          "C.EMAIL",
                                                                          "C.NO_CONTATO1",
                                                                          "C.NO_CONTATO2",
                                                                          "C.FL_AG_MAR",
                                                                          "C.FL_ARMADOR",
                                                                          "C.FL_OP_PORT",
                                                                          "C.FL_IMP",
                                                                          "C.FL_EXP",
                                                                          "C.FL_DESP",
                                                                          "C.FL_PROC",
                                                                          "C.FL_FORNEC",
                                                                          "C.INF_COMPL",
                                                                          "C.NR_INSCRICAO",
                                                                          "C.FL_OUTROS",
                                                                          "C.FL_DOCUM",
                                                                          "C.CD_PESSOA_AFE",
                                                                          "C.DS_SITE",
                                                                          "C.CD_UF",
                                                                          "C.DS_MOTIVO",
                                                                          "C.FL_TRANSP",
                                                                          "C.NR_RG",
                                                                          "C.DT_EMISS_RG",
                                                                          "C.ORG_EMISS_RG",
                                                                          "C.CD_PAIS",
                                                                          "C.FL_REALIZA_PESAGEM",
                                                                          "C.FL_ATIVO",
                                                                          "C.EMAIL_AM",
                                                                          "C.EMAIL_OP",
                                                                          "C.EMAIL_EX",
                                                                          "C.EMAIL_IM",
                                                                          "C.EMAIL_DE",
                                                                          "C.FL_EXIGE_RNTRC",
                                                                          "C.RNTRC",
                                                                          "C.DT_VALIDADE_RNTRC"))
                           ->join(array("C" => "CLIENTE"), "UC.CD_CLIENTE = C.CD_CLIENTE", array())
                           ->where($where)
                           ->order("C.CD_CLIENTE ASC");
            
            // Retorna a consulta
            return $select;

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
    
    
}
?>