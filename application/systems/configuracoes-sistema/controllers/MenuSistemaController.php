<?php

/**
 *
 * Classe respons�vel por cadastrar os menus WEB
 *
 * @author     M�rcio Souza Duarte
 * @category   Marca Sistemas
 * @copyright  Copyright (c) 1991-2010 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 */
class ConfiguracoesSistema_MenuSistemaController extends Marca_Controller_Abstract_Operacao {

    /**
     * (non-PHPdoc)
     * @see library/Marca/Controller/Abstract/Marca_Controller_Abstract_Operacao#init()
     */
    public function init() {
        parent::init();
        
        // Carrega os modelos de dados
        Zend_Loader::loadClass("WebSistemaModel");
        Zend_Loader::loadClass("WebMenuSistemaModel");
        Zend_Loader::loadClass('Marca_PicklistDb');
        
        // Captura a sess�o
        $sessao = new Zend_Session_Namespace('portoweb');
        
        // Carrega a classe de tradu��o
        Zend_Loader::loadClass("Marca_Controller_Action_Helper_Traduz");
        
        // Instancia o objeto de tradu��o
        $traducao = new Marca_Controller_Action_Helper_Traduz();
        
        // Monta a tradu��o dos campos.
        // Nessess�rio para reduzir a quantidade de acessos ao banco,  
        // diminuindo assim o tempo de carregamento da p�gina.
        $this->view->traducao = $traducao->traduz(array("LB_COD_MENU",
                                                        "LB_NO_SISTEMA",
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
        
        // Recupera a inst�ncia da base de dados
        $db = Zend_Registry::get("db");
        
        // Instancia a classes de dados
        $sistemas = new WebSistemaModel();
        $menus    = new WebMenuSistemaModel();
        
        // Captura a sess�o
        $sessao = new Zend_Session_Namespace('portoweb');
        
        // Retorna para a view os sistemas cadastrados
        $this->view->sistemas = $sistemas->getSistemas();
        
        // Ordena��o da consulta
        $order = $this->_helper->OrdenaConsulta->ordenar("WM.NO_MENU", "ASC");
        
        // Retorna os menus e seus sistemas
        $select = $menus->select()
                        ->setIntegrityCheck(false)
                        ->from(array("WM" => "WEB_MENU_SISTEMA"), array("WM.*", "WS.*"))
                        ->join(array("WS" => "WEB_SISTEMA"), "WS.CD_SISTEMA = WM.CD_SISTEMA", array())
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
        
        // Instancia a classes de dados
        $sistemas = new WebSistemaModel();
        $menus    = new WebMenuSistemaModel();
        
        // Recupera o �ltimo c�digo do sistema
        $params["cd_menu"] = $menus->nextVal();
        
        // Retorna para a view os sistemas cadastrados
        $this->view->sistemas = $sistemas->getSistemas();
        
        // Reenvia os valores para o formul�rio
        $this->_helper->RePopulaFormulario->repopular($params);
        
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
        
        // Instancia a classe de sistemas web
        $sistemas = new WebSistemaModel();
        
        // Define os filtros para a exclus�o
        $where = $sistemas->addWhere(array("CD_SISTEMA = ?" => $params['cd_sistema']))
                          ->getWhere();
        
        // Exclui o sistema
        $delete = $sistemas->delete($where);
        
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
        // Recupera os parametros da requisi��o
        $params = $this->_request->getParams();
        
        // Recupera a inst�ncia da base de dados
        $db = Zend_Registry::get("db");
        
        // Instancia a classes de dados
        $sistemas = new WebSistemaModel();
        $menus    = new WebMenuSistemaModel();
        
        // Retorna para a view os sistemas cadastrados
        $this->view->sistemas = $sistemas->getSistemas();
        
        // Captura a sess�o
        $sessao = new Zend_Session_Namespace('portoweb');
        
        // Retorna a query com os menus
        $select = $menus->queryBuscaMenusSistema($params);

        // Recebe a inst�ncia do paginator por singleton
        $paginator = Zend_Paginator::factory($select);
        
        // Seta o n�mero da p�gina corrente
        $pagina = $this->_getParam('pagina', 1);
        
        // Define a p�gina corrente
        $paginator->setCurrentPageNumber($pagina);
        
        // Define o total de linhas por p�gina
        $paginator->setItemCountPerPage($sessao->perfil->QT_LINHAS);
        
        // Joga para a view a pagina��o
        $this->view->paginator = $paginator;
        
        // Reenvia os valores para o formul�rio
        $this->_helper->RePopulaFormulario->repopular($params);
    }

    /**
     * Metodo selecionar
     * objetivo: utilizado para selecionar um registro a partir de uma listagem
     */
    public function selecionarAction() { 
        // Recupera os parametros da requisi��o
        $params = $this->_request->getParams();
        
        // Instancia a classes de dados
        $sistemas = new WebSistemaModel();
        $menus    = new WebMenuSistemaModel();
        
        // Retorna para a view os sistemas cadastrados
        $this->view->sistemas = $sistemas->getSistemas();
        
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
        ini_set('max_execution_time', 180);
        ini_set('memory_limit', '256M');

        // Desabilita o layout padr�o
        $this->_helper->layout->disableLayout();

        // N�o deixa a view renderizar
        $this->_helper->viewRenderer->setNoRender(TRUE);

        // Captura os parametros passados por GET
        $params = $this->getRequest()->getParams();

        // Associa as vari�veis do banco
        $db = Zend_Registry::get('db');

        // Carrega a sess�o do usu�rio
        $sessao = new Zend_Session_Namespace('portoweb');

        // inicializa a variavel como um array
        $resConsulta = array();
        
        // Instancia a classe de menus web
        $menus = new WebMenuSistemaModel();
        
        // Retorna a query com os menus
        $select = $menus->queryBuscaMenusSistema($params);
        
        // Define os par�metros para a consulta e retorna o resultado da pesquisa
        $resConsulta = $menus->fetchAll($select);
                    
        // Joga para a view o resultado da consulta
        $this->view->resConsulta = $resConsulta;

        // Renderiza a view jogando para a variavel
        $html = $this->view->render('menu-sistema/relatorio.phtml');

        // Monta o PDF com os dados da view
        //$this->view->domPDF($html, "a4", ("portrait" or "landscape"));
        $this->view->domPDF($html, "a4", "landscape");
    }
    
}