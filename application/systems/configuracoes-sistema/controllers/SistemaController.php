<?php

/**
 *
 * Classe respons�vel por cadastrar os sistemas WEB
 *
 * @author     M�rcio Souza Duarte
 * @category   Marca Sistemas
 * @copyright  Copyright (c) 1991-2010 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 */
class ConfiguracoesSistema_SistemaController extends Marca_Controller_Abstract_Operacao {

    /**
     * (non-PHPdoc)
     * @see library/Marca/Controller/Abstract/Marca_Controller_Abstract_Operacao#init()
     */
    public function init() {
        parent::init();
        
        // Carrega os modelos de dados
        Zend_Loader::loadClass("WebSistemaModel");
        
        // Captura a sess�o
        $sessao = new Zend_Session_Namespace('portoweb');
        
        // Carrega a classe de tradu��o
        Zend_Loader::loadClass("Marca_Controller_Action_Helper_Traduz");
        
        // Instancia o objeto de tradu��o
        $traducao = new Marca_Controller_Action_Helper_Traduz();
        
        // Monta a tradu��o dos campos.
        // Nessess�rio para reduzir a quantidade de acessos ao banco,  
        // diminuindo assim o tempo de carregamento da p�gina.
        $this->view->traducao = $traducao->traduz(array("LB_COD_SISTEMA",
                                                        "LB_NO_SISTEMA", 
                                                        "LB_ORDENACAO", 
                                                        "LB_ORDEM_EXIBICAO_MENU", 
                                                        "LB_DS_SISTEMA"), $sessao->perfil->CD_IDIOMA);
    }
    
    /**
     * Metodo index
     * objetivo: M�todo principal da classe
     */
    public function indexAction() { 
        
        // Redireciona para a tela de pesquisa
        $this->_forward("pesquisar");
        
    }
    
     /**
     * Metodo novo
     * objetivo: utilizado para formularios de cadastro
     */
    public function novoAction() { 
        
    }

    /**
     * Metodo salvar
     * objetivo: utilizado para as opera��es de INSERT/UPDATE
     */
    public function salvarAction() { 
        // Recupera os parametros da requisi��o
        $params = $this->_request->getParams();
        
        // Instancia a classe de sistemas web
        $sistemas = new WebSistemaModel();
        
        // Captura o pr�ximo c�digo se for um novo cadastro
        if($params["cd_sistema"] == "") {
            $cd_sistema = $sistemas->nextVal();
            
            // Seta o c�digo
            $params["cd_sistema"] = $cd_sistema;
            
            // Joga o c�digo nos par�metros da requisi��o
            $this->_request->setParam("cd_sistema", $cd_sistema);
        }
        
        // Monta os dados que ser�o salvos
        $dados = array("CD_SISTEMA"     => $params["cd_sistema"],
                       "NO_SISTEMA"     => $params["no_sistema"],
                       "NO_PASTA_ZEND"  => $params["no_pasta_zend"],
                       "DS_SISTEMA"     => $params["ds_sistema"],
                       "ORD_SISTEMA"    => $params["ord_sistema"]);
        
        // Verifica as regras do modelo de dados
        if($sistemas->isValid($dados)) {

            // Verifica se a opera��o � de NOVO
            if($params['operacao'] == "novo") {
                
                // Insere os dados do sistema
                $insert = $sistemas->insert($dados);
                
            } else {
                
                // Define os filtros para a atualiza��o
                $where = $sistemas->addWhere(array("CD_SISTEMA = ?" => $params['cd_sistema']))
                                  ->getWhere();
                
                // Atualiza os dados
                $update = $sistemas->update($dados, $where);
            }
            
            // Redireciona para a��o de selecionar
            $this->_forward('selecionar');
            
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
        
        // Instancia a classe de sistemas web
        $sistemas = new WebSistemaModel();
        
        // Captura a sess�o
        $sessao = new Zend_Session_Namespace('portoweb');
        
        // Retorna a query com os sistemas
        $select = $sistemas->queryBuscaSistemas($params);

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
        
        // Instancia a classe de sistemas web
        $sistemas = new WebSistemaModel();
        
        // Define os filtros para a cosulta
        $where = $sistemas->addWhere(array("CD_SISTEMA = ?" => $params['cd_sistema']))->getWhere();
        
        // Recupera o sistema selecionado
        $sistema  = $sistemas->fetchRow($where);
        
         // Reenvia os valores para o formul�rio
        $this->_helper->RePopulaFormulario->repopular($sistema->toArray(), "lower");
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
        
        // Instancia a classe de sistemas web
        $sistemas = new WebSistemaModel();
        
        // Retorna a query com os sistemas
        $select = $sistemas->queryBuscaSistemas($params);
        
        // Define os par�metros para a consulta e retorna o resultado da pesquisa
        $resConsulta = $sistemas->fetchAll($select);
                    
        // Joga para a view o resultado da consulta
        $this->view->resConsulta = $resConsulta;

        // Renderiza a view jogando para a variavel
        $html = $this->view->render('sistema/relatorio.phtml');

        // Monta o PDF com os dados da view
        //$this->view->domPDF($html, "a4", ("portrait" or "landscape"));
        $this->view->domPDF($html, "a4", "landscape");
    }

}