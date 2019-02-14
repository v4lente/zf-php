<?php
/**
 *
 * Classe responsável por desconectar usuários que ficaram presos ao sistema matando suas sessões do banco de dados.
 *
 * @author     Marcio Souza Duarte
 * @category   Marca Sistemas
 * @copyright  Copyright (c) 1991-2015 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 */
class ConfiguracoesSistema_DesconectaUsuarioSistemaController extends Marca_Controller_Abstract_Operacao {

    /**
     * 
     * Construtor da classe
     * 
     * @return void
     */
    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array()) {

        // Chama o construtor pai
        parent::__construct($request, $response, $invokeArgs);
        
    }

    /**
     * (non-PHPdoc)
     * @see library/Marca/Controller/Abstract/Marca_Controller_Abstract_Operacao#init()
     */
    public function init() {

        parent::init();

        // Carrega os modelos de dados
        Zend_Loader::loadClass("ClienteModel");

        // Captura a sessão
        $sessao = new Zend_Session_Namespace('portoweb');
        
    }

    /**
     * Metodo index
     * objetivo: Método principal da classe
     */
    public function indexAction() {
        
        // Associa as variáveis do banco
        $db = Zend_Registry::get('db');
        
        $select = "  SELECT V\$SESSION.USERNAME,
                            V\$SESSION.SID,
                            V\$SESSION.SERIAL# as SERIAL, 
                            0 as fl_derruba, 
                            V\$SESSION.OSUSER,   
                            V\$SESSION.MACHINE,   
                            V\$SESSION.PROGRAM,   
                            V\$SESSION.LOGON_TIME 
                       FROM V\$SESSION  
                      WHERE ( V\$SESSION.TYPE not in ('BACKGROUND') ) AND  
                            ( V\$SESSION.USERNAME is not NULL )       AND 
                            ( V\$SESSION.STATUS <> 'KILLED' )         AND 
                            ( V\$SESSION.USERNAME NOT IN ('SYSMAN','DBSNMP') ) 
                   ORDER BY V\$SESSION.USERNAME ASC,
                            V\$SESSION.MACHINE  ASC,
                            V\$SESSION.PROGRAM  ASC";
        
        $result = $db->fetchAll($select);
        
        $this->view->usuarios = $result;
        
    }

    /**
     * Metodo novo
     * objetivo: utilizado para formularios de cadastro
     */
    public function novoAction() {
        
    }

    /**
     * Metodo salvar
     * objetivo: utilizado para as operações de INSERT/UPDATE
     */
    public function salvarAction() {

        // Recupera os parametros da requisição
        $params = $this->_request->getParams();

        // Associa as variáveis do banco
        $db = Zend_Registry::get('db');
        
        try {
            $db->beginTransaction();

            $totalUsuarios = count($params["usuarios"]);
            for($i=0; $i < $totalUsuarios; $i++) {

                list($sid, $serial) = explode("@", $params["usuarios"][$i]);
                
                $query = "ALTER SYSTEM KILL SESSION '" . $sid . "," . $serial . "'";
                $db->query($query);
            }

            $mensagemSistema = Zend_Registry::get("mensagemSistema");
            $msg = array("msg" => array("Operação realizada."),
                         "titulo" => "SUCESSO",
                         "tipo" => 2);
            
            $mensagemSistema->send(serialize($msg));

            // Confirma a operação no banco
            $db->commit();
            
        } catch(Exception $e) {
            
            $db->rollback();
            
            $mensagemSistema = Zend_Registry::get("mensagemSistema");
            $msg = array("msg" => array("Erro ao desconectar usuário: " . $e->getMessage()),
                         "titulo" => "ATENÇÃO",
                         "tipo" => 4);
            $mensagemSistema->send(serialize($msg));

        }
        
        // Refaz a consulta
        $select = "  SELECT V\$SESSION.USERNAME,
                            V\$SESSION.SID,
                            V\$SESSION.SERIAL# as SERIAL, 
                            0 as fl_derruba, 
                            V\$SESSION.OSUSER,   
                            V\$SESSION.MACHINE,   
                            V\$SESSION.PROGRAM,   
                            V\$SESSION.LOGON_TIME 
                       FROM V\$SESSION  
                      WHERE ( V\$SESSION.TYPE not in ('BACKGROUND') ) AND  
                            ( V\$SESSION.USERNAME is not NULL )       AND 
                            ( V\$SESSION.STATUS <> 'KILLED' )         AND 
                            ( V\$SESSION.USERNAME NOT IN ('SYSMAN','DBSNMP') ) 
                   ORDER BY V\$SESSION.USERNAME ASC,
                            V\$SESSION.MACHINE  ASC,
                            V\$SESSION.PROGRAM  ASC";
        
        $result = $db->fetchAll($select);
        
        $this->view->usuarios = $result;
        
    }

    /**
     * Metodo excluir
     * objetivo: utilizado para a operação de DELETE
     */
    public function excluirAction() {
        
    }

    /**
     * Metodo pesquisar
     * objetivo: utilizado para executar pesquisas
     */
    public function pesquisarAction() {
        
    }

    /**
     * Metodo selecionar
     * objetivo: utilizado para selecionar um registro a partir de uma listagem
     */
    public function selecionarAction() {
        
    }

    /**
     * Metodo relatorio
     * objetivo: utilizado para gerar um relatorio a partir de uma listagem
     */
    public function relatorioAction() {
        
    }

}

