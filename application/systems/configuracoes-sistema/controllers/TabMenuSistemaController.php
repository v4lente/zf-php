<?php

/**
 *
 * Classe respons�vel por cadastrar os menus WEB dentro das Abas do Menu
 *
 * @author     M�rcio Souza Duarte
 * @category   Marca Menus
 * @copyright  Copyright (c) 1991-2010 Marca Menus. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 */
class ConfiguracoesSistema_TabMenuSistemaController extends Marca_Controller_Abstract_Operacao {
    
    /**
     * 
     * Construtor da classe
     * 
     * @return void
     */
    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array()) {
        
        // Chama o construtor pai
        parent::__construct($request, $response, $invokeArgs);
        
        // Captura os parametros passados por GET
        $params = $this->getRequest()->getParams();
        
        // Captura a sess�o
        $sessao = new Zend_Session_Namespace('portoweb');

        // Caputara e grava o c�digo da transa��o da janela pai
        if (isset($params['pai_cd_sistema'])) {
            $sessao->pai_cd_sistema = $params['pai_cd_sistema'];
        }
        
        Zend_Registry::set("pai_cd_sistema", $sessao->pai_cd_sistema);

    }
    
    /**
     * (non-PHPdoc)
     * @see library/Marca/Controller/Abstract/Marca_Controller_Abstract_Operacao#init()
     */
    public function init() {
        
        // Carrega o m�todo de inicializa��o da classe pai
        parent::init();
        
        // Define o layout a ser utilizado
        $this->_helper->layout->setLayout("layout-aba");
        
        // Carrega os modelos de dados
        Zend_Loader::loadClass("WebMenuSistemaModel");
        
        // Captura a sess�o
        $sessao = new Zend_Session_Namespace('portoweb');
        
        // Carrega a classe de tradu��o
        Zend_Loader::loadClass("Marca_Controller_Action_Helper_Traduz");
        
        // Instancia o objeto de tradu��o
        $traducao = new Marca_Controller_Action_Helper_Traduz();
        
        // Monta a tradu��o dos campos.
        // Nessess�rio para reduzir a quantidade de acessos ao banco,  
        // diminuindo assim o tempo de carregamento da p�gina.
        $this->view->traducao = $traducao->traduz(array("LB_CODIGO",
                                                        "LB_NOME",
                                                        "LB_NOME_NS", 
                                                        "LB_DESCRICAO",
                                                        "LB_ORDEM",
                                                        "LB_COD_SISTEMA"), $sessao->perfil->CD_IDIOMA);
    }
    
    /**
     * Metodo index
     * objetivo: M�todo principal da classe
     */
    public function indexAction() { 
        
        // Captura os parametros passados por GET
        $params = $this->getRequest()->getParams();
        
        // Recupera a inst�ncia da base de dados
        $db = Zend_Registry::get("db");
        
        // Instancia a classes de dados
        $menus    = new WebMenuSistemaModel();
        
        // Captura a sess�o
        $sessao = new Zend_Session_Namespace('portoweb');
        
        // Ordena��o
        $order = "WM.ORD_MENU ASC";
        if(isset($params['column'])){
            $order = $params['column'] . " " . $params['orderby'];
        }
        
        // Retorna os menus e seus menus
        $select = $menus->select()
                        ->from(array("WM" => "WEB_MENU_SISTEMA"), array("WM.*"))
                        ->where("WM.CD_SISTEMA = " . Zend_Registry::get("pai_cd_sistema"))
                        ->order($order);
        
        // Define os par�metros para a consulta e retorna o resultado da pesquisa
        $resConsulta = $menus->fetchAll($select);
        
        // Seta o n�mero da p�gina corrente
        $pagina = $this->_getParam('pagina', 1);

        // Recebe a inst�ncia do paginator por singleton
        $paginator = Zend_Paginator::factory($resConsulta);
        
        // Define a p�gina corrente
        $paginator->setCurrentPageNumber($pagina);
        
        // Define o total de linhas por p�gina
        $paginator->setItemCountPerPage($sessao->perfil->QT_LINHAS);
        
        // Joga para a view a pagina��o
        $this->view->paginator = $paginator;
    }
    
     /**
     * Metodo novo
     * objetivo: utilizado para formularios de cadastro
     */
    public function novoAction() { 
        
        // Captura os parametros passados por GET
        $params = $this->getRequest()->getParams();
        
        // Captura a sess�o
        $sessao = new Zend_Session_Namespace('portoweb');
        
        $params["cd_sistema"] = Zend_Registry::get("pai_cd_sistema");
        
        // Reenvia os valores para o formul�rio
        $this->_helper->RePopulaFormulario->repopular($params, "lower");
        
    }

    /**
     * Metodo salvar
     * objetivo: utilizado para as opera��es de INSERT/UPDATE
     */
    public function salvarAction() { 
        // Recupera os parametros da requisi��o
        $params = $this->_request->getParams();
        
        // Instancia a classe de menu web
        $menus = new WebMenuSistemaModel();
        
        // Captura o pr�ximo c�digo se for um novo cadastro
        if($params["cd_menu"] == "") {
            $params["cd_menu"] = $menus->nextVal();
        }
        
        // C�digo do sistema
        $params["cd_sistema"] = Zend_Registry::get("pai_cd_sistema");
                
        // Monta os dados que ser�o salvos
        $dados = array("CD_MENU"    => $params["cd_menu"],
                       "CD_SISTEMA" => $params["cd_sistema"],
                       "NO_MENU"    => $params["no_menu"],
                       "DS_MENU"    => $params["ds_menu"],
                       "ORD_MENU"   => $params["ord_menu"]);
        
        // Verifica as regras do modelo de dados
        if($menus->isValid($dados)) {

            // Verifica se a opera��o � de NOVO
            if($params['operacao'] == "novo") {
                
                // Insere os dados do sistema
                $insert = $menus->insert($dados);
                
                // Redireciona para a��o novo
                $this->_forward('novo');
                
            } else {
                
                // Define os filtros para a atualiza��o
                $where = $menus->addWhere(array("CD_MENU = ?" => $params['cd_menu']))
                               ->getWhere();
                
                // Atualiza os dados
                $update = $menus->update($dados, $where);

                // Redireciona para a��o de selecionar
                $this->_forward('selecionar');
            }
            
        }
        
    }

    /**
     * Metodo excluir
     * objetivo: utilizado para a opera��o de DELETE
     */
    public function excluirAction() { 
        // Recupera os parametros da requisi��o
        $params = $this->_request->getParams();
        
        // Instancia a classe de menus web
        $menus = new WebMenuSistemaModel();
        
        // Define os filtros para a exclus�o
        $where = $menus->addWhere(array("CD_MENU = ?" => $params['cd_menu']))
                       ->getWhere();
        
        // Exclui o sistema
        $delete = $menus->delete($where);
        
        // Verifica se o registro foi exclu�do
        if($delete) {
            // Limpa os dados da requisi��o
            $params = $this->_helper->LimpaParametrosRequisicao->limpar();
            
            // Redireciona para o index
            $this->_forward("index", null, null, $params);
            
        } else {
            // Se n�o conseguir excluir, retorna pra sele��o do registro
            $this->_forward("selecionar");
        }
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
        // Recupera os parametros da requisi��o
        $params = $this->_request->getParams();
        
        // Instancia a classes de dados
        $menus    = new WebMenuSistemaModel();
        
        // Retorna para a view os menus cadastrados
        $this->view->menus = $menus->getMenus();
        
        // Define os filtros para a cosulta
        $where = $menus->addWhere(array("CD_MENU = ?" => $params['cd_menu']))->getWhere();
        
        // Recupera o sistema selecionado
        $menu  = $menus->fetchRow($where);
        
         // Reenvia os valores para o formul�rio
        $this->_helper->RePopulaFormulario->repopular($menu->toArray(), "lower");
    }

    /**
     * Metodo relatorio
     * objetivo: utilizado para gerar um relatorio a partir de uma listagem
     */
    public function relatorioAction() { 
        
    }
    
}