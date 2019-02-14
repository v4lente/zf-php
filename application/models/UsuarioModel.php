<?php

/**
 * Created on 26/10/2009
 *
 * Modelo da classe UsuarioModel
 *
 * @filesource
 * @author			M�rcio Souza Duarte
 * @copyright		Copyright 2009 Marca
 * @package			zendframework
 * @subpackage		zendframework.application.models
 * @version			1.0
 */
class UsuarioModel extends Marca_Db_Table_Abstract {

    /**
     * Nome da tabela relacionada
     *
     * @var string
     */
    protected $_name = 'USUARIO';

    /**
     * Chave prim�ria da tabela
     *
     * @var string
     */
    protected $_primary = array('CD_USUARIO');

    /**
     * Classe para manipular a linha
     *
     * @var string
     */
    protected $_rowClass = 'UsuarioRow';

    /**
     * Desativa a exclus�o l�gica
     *
     * @var boolean
     */
    protected $_logicalDelete = false;

    /**
     * Tabelas dependente
     *
     * @var array
     */
    protected $_dependentTables = array('WebGrupoUsuarioModel');

    /**
     *  Regra de negocio do modelo
     *
     * @var array
     */
    protected $_rules = array(
        array('name' => 'CD_USUARIO',
            'class' => 'NotEmpty',
            'errorMessage' => 'Campo obrigat�rio',
            'filter' => 'Zend_Filter_StringToUpper'),
        array('name' => 'NO_USUARIO',
            'class' => 'NotEmpty',
            'errorMessage' => 'Campo obrigat�rio',
            'filter' => 'Zend_Filter_StringToUpper'),
        array('name' => 'FL_SUPER',
            'class' => 'NotEmpty',
            'errorMessage' => 'Campo obrigat�rio',
            'filter' => 'intval')
    );

    /**
     * 
     * Cria um usu�rio do banco de dados
     * 
     * @param  string $cd_usuario C�digo do usu�rio a ser inserido
     * @return bool
     * 
     */
    public function createUser($cd_usuario) {

        // Verifica se o usu�rio foi passado
        if ($cd_usuario != "") {

            // Converte para mai�scula o c�digo do usu�rio
            $cd_usuario = strtoupper($cd_usuario);

            // Instancia a base de dados
            $db = Zend_Registry::get("db");

            // Verifica se existe a tablespace
            $select1 = $db->select()
                    ->from(array("DTS" => "DBA_TABLESPACES"), array("DTS.TABLESPACE_NAME"))
                    ->where("DTS.TABLESPACE_NAME = 'TEMP'");

            // Executa a consulta
            $linha1 = $db->fetchRow($select1);

            // Captura a tablespace
            $temp1 = $linha1->TABLESPACE_NAME;

            // Verifica se existe a tablespace
            $select2 = $db->select()
                    ->from(array("DTS" => "DBA_TABLESPACES"), array("DTS.TABLESPACE_NAME"))
                    ->where("DTS.TABLESPACE_NAME = 'PORTO_DAD_AUTO'");

            // Executa a consulta
            $linha2 = $db->fetchRow($select2);

            // Captura a tablespace
            $temp2 = $linha2->TABLESPACE_NAME;

            // Gera a query para cria��o do usu�rio
            $query = "CREATE USER {$cd_usuario} 
    				  IDENTIFIED BY {$cd_usuario} 
    				  DEFAULT TABLESPACE {$temp2} 
    				  TEMPORARY TABLESPACE {$temp1}";

            // Cria o usu�rio
            $ret = $db->query($query);

            // Seta a permiss�o
            $temp3 = 'PORTO_USR';

            // Verifica se existe a regra
            $select3 = $db->select()
                    ->from(array("DR" => "DBA_ROLES"), array("DR.ROLE"))
                    ->where("DR.ROLE = 'PORTO_USR'");

            // Executa a consulta
            $linha3 = $db->fetchRow($select3);

            // Captura a tablespace
            if ($linha3->ROLE != "") {
                $temp3 = $linha3->ROLE;
            }

            // D� permiss�o ao usu�rio
            $query2 = "GRANT {$temp3} TO {$cd_usuario}";

            // Cria o usu�rio
            $ret2 = $db->query($query);

            // Retorna o resultado
            if ($ret) {
                return true;
            } else {
                return false;
            }
        } else {

            return false;
        }
    }

    /**
     * 
     * Remove um usu�rio do banco de dados
     * 
     * @param  string $cd_usuario C�digo do usu�rio a ser inserido
     * @return bool
     * 
     */
    public function dropUser($cd_usuario) {

        // Verifica se o usu�rio foi passado
        if ($cd_usuario != "") {

            // Converte para mai�scula o c�digo do usu�rio
            $cd_usuario = strtoupper($cd_usuario);

            // Instancia a base de dados
            $db = Zend_Registry::get("db");

            // Gera a query para remo��o do usu�rio
            $query = "DROP USER {$cd_usuario}";

            // Cria o usu�rio
            $ret = $db->query($query);

            // Retorna o resultado
            if ($ret) {
                return true;
            } else {
                return false;
            }
        } else {

            return false;
        }
    }

    /**
     * Retorna todos os usuarios
     *
     * @param  string $where
     * @return array  $usuarios
     */
    public function getUsuarios($where = null) {

        try {
            // Busca todos os grupos cadastradas
            $todosUsuarios = $this->fetchAll($where, "NO_USUARIO ASC");
            $usuarios = array();
            foreach ($todosUsuarios as $usuario) {
                $usuarios[$usuario->CD_USUARIO] = $usuario->NO_USUARIO;
            }

            return $usuarios;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function buscaUsuariosSemArray($where = null) {

        try {
            // Busca todos os grupos cadastradas
            $todosUsuarios = $this->fetchAll($where, "NO_USUARIO ASC");

            return $todosUsuarios;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * 
     * Troca a senha do usu�rio que est� logado
     * 
     * @param  string  $senha
     * @return boolean
     */
    public function trocarSenhaUsuarioLogado($senha = "") {

        // Associa as vari�veis do banco
        $db = Zend_Registry::get('db');

        // Carrega a classe de autentica��o
        Zend_Loader::loadClass('Marca_Auth_Adapter_Autentica');

        // Inicializa a classe de autentica��o
        $autenticacao = Zend_Auth::getInstance();

        // Captura e valida a sess�o do usu�rio
        $identidade_usuario = $autenticacao->getIdentity();

        // Altera a senha do usu�rio
        $sql = "ALTER USER $identidade_usuario IDENTIFIED BY \"{$senha}\"";

        $db->query($sql);

        // Inicializa a classe de autentica��o
        $autenticacao = Zend_Auth::getInstance();

        // Captura a autentica��o
        $autenticacao->setStorage(new Zend_Auth_Storage_Session('login_usuario'));

        // Passa o login e a senha para logar no banco
        $resultado = $autenticacao->authenticate(
                new Marca_Auth_Adapter_Autentica($identidade_usuario, $senha)
        );
    }

    /**
     * 
     * M�todo respons�vel por buscar os dados dos usu�rios
     * 
     * @param  array  $params Estes s�o os par�metros de busca
     * @return object $select Objeto que cont�m os dados da query
     */
    public function queryBuscaUsuarios(&$params = array()) {

        // Se for passado o cpf, trata ele antes da busca
        if (isset($params['cpf']) && $params['cpf'] != "") {
            $repDados = array("/", ".", ",", "-", "_");
            $params['cpf'] = str_replace($repDados, "", trim($params['cpf']));
            $params['cpf_nome'] = str_replace($repDados, "", trim($params['cpf_nome']));
        }

        // Define os filtros para a cosulta
        $where = $this->addWhere(array("U.NR_MATRIC 		                                      = ?" => $params['nr_matric']))
                ->addWhere(array("U.CD_USUARIO 		                                      = ?" => $params['cd_usuario']))
                ->addWhere(array("U.NO_USUARIO 	                                       LIKE ?" => $params['no_usuario']))
                ->addWhere(array("U.TP_USUARIO 		                                      = ?" => $params['tp_usuario']))
                ->addWhere(array("U.EMAIL 		                                       LIKE ?" => $params['email']))
                ->addWhere(array("U.FL_SUPER 		                                       LIKE ?" => $params['fl_super']))
                ->addWhere(array("U.FL_ATIVO 		                                       LIKE ?" => $params['fl_ativo']))
                ->addWhere(array("U.FL_ACESSO_INTRANET                                      = ?" => $params['fl_acesso_intranet']))
                ->addWhere(array("UPPER(U.CPF         || U.NO_USUARIO)                   LIKE ?" => strtoupper($params['cpf_nome'])))
                ->addWhere(array("UPPER(U.CD_USUARIO  || U.NO_USUARIO)                   LIKE ?" => strtoupper($params['cod_nome'])))
                ->addWhere(array("UPPER(U.NO_USUARIO) || ' - MATR�CULA: ' || U.NR_MATRIC LIKE ?" => strtoupper($params['autocomplete'])))
                ->getWhere();

        // Retorna as transa��es, menus e sistemas
        $select = $this->select()
                ->setIntegrityCheck(false)
                ->distinct()
                ->from(array("U" => "USUARIO"), array("U.NR_MATRIC",
                    "U.CD_USUARIO",
                    "U.NO_USUARIO",
                    "U.TP_USUARIO",
                    "U.EMAIL",
                    "U.FL_SUPER",
                    "U.FL_ACESSO_INTRANET",
                    "CPF" => new Zend_Db_Expr("F_FORMATA_CNPJCPF(U.CPF)"),
                    "AUTOCOMPLETE" => new Zend_Db_Expr("UPPER(U.NO_USUARIO) || ' - MATR�CULA: ' || U.NR_MATRIC")))
                ->joinLeft(array("WGU" => "WEB_GRUPO_USUARIO"), "U.CD_USUARIO = WGU.CD_USUARIO", array())
                ->joinLeft(array("WG" => "WEB_GRUPO"), "WGU.CD_GRUPO = WG.CD_GRUPO", array())
                ->where($where)
                ->order("U.NO_USUARIO ASC, U.CD_USUARIO ASC");

        // Retorna a consulta

        return $select;
    }

    /**
     * 
     * M�todo respons�vel por buscar os dados dos usu�rios matriculados, via autcomplete P.S
     * 
     * @param  array  $params Estes s�o os par�metros de busca
     * @return object $select Objeto que cont�m os dados da query
     */
    public function queryUsuariosMatriculados(&$params = array()) {
        // Define os filtros para a cosulta
        $where = $this->addWhere(array("U.TP_USUARIO = 'P'"), 'AND', false, '(')
                ->addWhere(array("U.NR_MATRIC  is not null"), 'AND', false, ')')
                ->addWhere(array("U.NR_MATRIC  LIKE ?" => $params['term']), 'AND', false, '(')
                ->addWhere(array("U.CD_USUARIO LIKE ?" => $params['term']), "OR")
                ->addWhere(array("U.NO_USUARIO LIKE ?" => $params['term']), "OR", false, ')')
                ->getWhere();

        $select = $this->select()
                ->from(array("U" => "USUARIO"), array("DESCRICAO" => new Zend_Db_Expr("UPPER(U.NO_USUARIO) || ' - MATR�CULA: ' || U.NR_MATRIC"),
                    "U.NR_MATRIC",
                    "U.CD_USUARIO"))
                ->where($where);

        return $select;
    }

    /**
     * 
     * M�todo respons�vel por verificar se o usu�rio tem ou n�o permiss�o para gerar a pr�via do manifesto
     * 
     * @param  array  $params Estes s�o os par�metros de busca
     * @return object $select Objeto que cont�m os dados da query
     */
    public function queryVerificaPermissaoGerarPrevia(&$params = array()) {

        // Define os filtros para a cosulta
        $where = $this->addWhere(array("U.CD_USUARIO = ?" => $params['cd_usuario']))
                ->getWhere();

        $select = $this->select()
                ->from(array("U" => "USUARIO"), array("COUNT" => new Zend_Db_Expr("COUNT(*)")))
                ->join(array("F" => "FUNCIONARIO"), "U.CD_EMPR = F.CD_EMPR AND U.NR_MATRIC = F.NR_MATRIC", array())
                ->where($where)
                ->where("F.CD_LOCAL IN ( ' 2 2 1 4', ' 2 2 1 5', ' 2 2 1 6', ' 5 6 7')");

        return $select;
    }


}

?>