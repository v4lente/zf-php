<?php

/**
 *
 * Classe responsável por cadastrar os menus WEB
 *
 * @author     Márcio Souza Duarte
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
        
        // Captura a sessão
        $sessao = new Zend_Session_Namespace('portoweb');
        
        // Carrega a classe de tradução
        Zend_Loader::loadClass("Marca_Controller_Action_Helper_Traduz");
        
        // Instancia o objeto de tradução
        $traducao = new Marca_Controller_Action_Helper_Traduz();
        
        // Monta a tradução dos campos.
        // Nessessário para reduzir a quantidade de acessos ao banco,  
        // diminuindo assim o tempo de carregamento da página.
        $this->view->traducao = $traducao->traduz(array("LB_COD_MENU",
                                                        "LB_NO_SISTEMA",
                                                        "LB_NOME_NS", 
                                                        "LB_DESCRICAO",
                                                        "LB_ORDEM",
                                                        "LB_COD_SISTEMA"), $sessao->perfil->CD_IDIOMA);
    }
    
    /**
     * Metodo index
     * objetivo: Método principal da classe
     */
    public function indexAction() { 
        
        // Recupera a instância da base de dados
        $db = Zend_Registry::get("db");
        
        // Instancia a classes de dados
        $sistemas = new WebSistemaModel();
        $menus    = new WebMenuSistemaModel();
        
        // Captura a sessão
        $sessao = new Zend_Session_Namespace('portoweb');
        
        // Retorna para a view os sistemas cadastrados
        $this->view->sistemas = $sistemas->getSistemas();
        
        // Ordenação da consulta
        $order = $this->_helper->OrdenaConsulta->ordenar("WM.NO_MENU", "ASC");
        
        // Retorna os menus e seus sistemas
        $select = $menus->select()
                        ->setIntegrityCheck(false)
                        ->from(array("WM" => "WEB_MENU_SISTEMA"), array("WM.*", "WS.*"))
                        ->join(array("WS" => "WEB_SISTEMA"), "WS.CD_SISTEMA = WM.CD_SISTEMA", array())
                        ->order($order);
        
        // Define os parâmetros para a consulta e retorna o resultado da pesquisa
        $resConsulta = $menus->fetchAll($select);
        
        // Seta o número da página corrente
        $pagina = $this->_getParam('pagina', 1);

        // Recebe a instância do paginator por singleton
        $paginator = Zend_Paginator::factory($resConsulta);
        
        // Define a página corrente
        $paginator->setCurrentPageNumber($pagina);
        
        // Define o total de linhas por página
        $paginator->setItemCountPerPage($sessao->perfil->QT_LINHAS);
        
        // Joga para a view a paginação
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
        
        // Recupera o último código do sistema
        $params["cd_menu"] = $menus->nextVal();
        
        // Retorna para a view os sistemas cadastrados
        $this->view->sistemas = $sistemas->getSistemas();
        
        // Reenvia os valores para o formulário
        $this->_helper->RePopulaFormulario->repopular($params);
        
    }

    /**
     * Metodo salvar
     * objetivo: utilizado para as operações de INSERT/UPDATE
     */
    public function salvarAction() { 
        // Recupera os parametros da requisição
        $params = $this->_request->getParams();
        
        // Instancia a classe de menu web
        $menus = new WebMenuSistemaModel();
        
        // Monta os dados que serão salvos
        $dados = array("CD_MENU"    => $params["cd_menu"],
                       "CD_SISTEMA" => $params["cd_sistema"],
                       "NO_MENU"    => $params["no_menu"],
                       "DS_MENU"    => $params["ds_menu"],
                       "ORD_MENU"   => $params["ord_menu"]);
        
        // Verifica as regras do modelo de dados
        if($menus->isValid($dados)) {

            // Verifica se a operação é de NOVO
            if($params['operacao'] == "novo") {
                
                // Insere os dados do sistema
                $insert = $menus->insert($dados);
                
                // Redireciona para ação novo
                $this->_forward('novo');
                
            } else {
                
                // Define os filtros para a atualização
                $where = $menus->addWhere(array("CD_MENU = ?" => $params['cd_menu']))
                                  ->getWhere();
                
                // Atualiza os dados
                $update = $menus->update($dados, $where);

                // Redireciona para ação de selecionar
                $this->_forward('selecionar');             
            }
            
        }
        
    }

    /**
     * Metodo excluir
     * objetivo: utilizado para a operação de DELETE
     */
    public function excluirAction() { 
        // Recupera os parametros da requisição
        $params = $this->_request->getParams();
        
        // Instancia a classe de sistemas web
        $sistemas = new WebSistemaModel();
        
        // Define os filtros para a exclusão
        $where = $sistemas->addWhere(array("CD_SISTEMA = ?" => $params['cd_sistema']))
                          ->getWhere();
        
        // Exclui o sistema
        $delete = $sistemas->delete($where);
        
        // Verifica se o registro foi excluído
        if($delete) {
            // Limpa os dados da requisição
            $params = $this->_helper->LimpaParametrosRequisicao->limpar();
            
            // Redireciona para o index
            $this->_forward("index", null, null, $params);
            
        } else {
            // Se não conseguir excluir, retorna pra seleção do registro
            $this->_forward("selecionar");
        }
    }

    /**
     * Metodo pesquisar
     * objetivo: utilizado para executar pesquisas
     */
    public function pesquisarAction() {
        // Recupera os parametros da requisição
        $params = $this->_request->getParams();
        
        // Recupera a instância da base de dados
        $db = Zend_Registry::get("db");
        
        // Instancia a classes de dados
        $sistemas = new WebSistemaModel();
        $menus    = new WebMenuSistemaModel();
        
        // Retorna para a view os sistemas cadastrados
        $this->view->sistemas = $sistemas->getSistemas();
        
        // Captura a sessão
        $sessao = new Zend_Session_Namespace('portoweb');
        
        // Retorna a query com os menus
        $select = $menus->queryBuscaMenusSistema($params);

        // Recebe a instância do paginator por singleton
        $paginator = Zend_Paginator::factory($select);
        
        // Seta o número da página corrente
        $pagina = $this->_getParam('pagina', 1);
        
        // Define a página corrente
        $paginator->setCurrentPageNumber($pagina);
        
        // Define o total de linhas por página
        $paginator->setItemCountPerPage($sessao->perfil->QT_LINHAS);
        
        // Joga para a view a paginação
        $this->view->paginator = $paginator;
        
        // Reenvia os valores para o formulário
        $this->_helper->RePopulaFormulario->repopular($params);
    }

    /**
     * Metodo selecionar
     * objetivo: utilizado para selecionar um registro a partir de uma listagem
     */
    public function selecionarAction() { 
        // Recupera os parametros da requisição
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
        
         // Reenvia os valores para o formulário
        $this->_helper->RePopulaFormulario->repopular($menu->toArray(), "lower");
    }

    /**
     * Metodo relatorio
     * objetivo: utilizado para gerar um relatorio a partir de uma listagem
     */
    public function relatorioAction() { 
        ini_set('max_execution_time', 180);
        ini_set('memory_limit', '256M');

        // Desabilita o layout padrão
        $this->_helper->layout->disableLayout();

        // Não deixa a view renderizar
        $this->_helper->viewRenderer->setNoRender(TRUE);

        // Captura os parametros passados por GET
        $params = $this->getRequest()->getParams();

        // Associa as variáveis do banco
        $db = Zend_Registry::get('db');

        // Carrega a sessão do usuário
        $sessao = new Zend_Session_Namespace('portoweb');

        // inicializa a variavel como um array
        $resConsulta = array();
        
        // Instancia a classe de menus web
        $menus = new WebMenuSistemaModel();
        
        // Retorna a query com os menus
        $select = $menus->queryBuscaMenusSistema($params);
        
        // Define os parâmetros para a consulta e retorna o resultado da pesquisa
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