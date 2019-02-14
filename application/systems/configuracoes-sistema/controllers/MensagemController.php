<?php

/**
 *
 * Classe respons�vel por cadastrar as transa��es WEB
 *
 * @author     M�rcio Souza Duarte
 * @category   Marca Sistemas
 * @copyright  Copyright (c) 1991-2010 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 */
class ConfiguracoesSistema_MensagemController extends Marca_Controller_Abstract_Operacao {

    /**
     * (non-PHPdoc)
     * @see library/Marca/Controller/Abstract/Marca_Controller_Abstract_Operacao#init()
     */
    public function init() {
        parent::init();
        
        // Carrega os modelos de dados
        Zend_Loader::loadClass("WebMensagemModel");
        Zend_Loader::loadClass("WebGrupoMensagemModel");
        
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
                                                        "LB_TITULO",
                                                        "LB_DESCRICAO",
                                                        "LB_DT_INI",
                                                        "LB_DT_FIM",
                                                        "LB_URGENTE"), $sessao->perfil->CD_IDIOMA);
    }
    
    /**
     * Metodo index
     * objetivo: M�todo principal da classe
     */
    public function indexAction() { 

        // Seta alguns valores padr�o para a p�gina
        $this->defineValoresPadrao(array());
    }
    
     /**
     * Metodo novo
     * objetivo: utilizado para formularios de cadastro
     */
    public function novoAction() { 
        
        // Captura os parametros passados por GET
        $params = $this->getRequest()->getParams();
        
        // Seta alguns valores padr�o para a p�gina
        $this->defineValoresPadrao($params);
        
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
        
        // Instancia as classe de dados
        $mensagens  = new WebMensagemModel();
        
        // Pega o pr�ximo c�digo
        if($params["cd_mensagem"] == "") {
            $params["cd_mensagem"] = $mensagens->nextVal();
        }
        
        
        // Monta os dados que ser�o salvos
        $dados = array("CD_MENSAGEM"    => $params["cd_mensagem"],
                       "TITULO"         => $params["titulo"],
                       "DESCRICAO"      => $params["descricao"],
                       "DT_INI"         => $params["dt_ini"],
                       "DT_FIM"         => $params["dt_fim"],
                       "FL_URGENTE"     => $params["fl_urgente"]);
        
        // Verifica as regras do modelo de dados
        if($mensagens->isValid($dados)) {
            
            // Verifica se a opera��o � de NOVO
            if($params['operacao'] == "novo") {
                
                // Grava os dados
                $insert = $mensagens->insert($dados);
                
                // Redireciona para a��o novo
                $this->_forward('selecionar', null, null, $params);
                
            } else {
                
                // Define os filtros para a atualiza��o
                $where = $mensagens->addWhere(array("CD_MENSAGEM = ?" => $params['cd_mensagem']))
                                   ->getWhere();
                
                // Atualiza os dados
                $update = $mensagens->update($dados, $where);

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
        
        // Instancia as classe de dados
        $mensagens       = new WebMensagemModel();
        $gruposMensagems = new WebGrupoMensagemModel();
        
        // Define os filtros para a exclus�o
        $where = $mensagens->addWhere(array("CD_MENSAGEM = ?" => $params['cd_mensagem']))
                                    ->getWhere();
        
        // Exclui a mensagem
        $delete1 = $gruposMensagems->delete($where);
        $delete2 = $mensagens->delete($where);
        
        // Verifica se o registro foi exclu�do
        if($delete2) {
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
        // Captura os parametros passados por GET
        $params = $this->getRequest()->getParams();
        
        // Seta alguns valores padr�o para a p�gina
        $this->defineValoresPadrao($params);
        
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
        
        // Instancia as classe de dados
        $mensagens  = new WebMensagemModel();
        
        // Define os filtros para a cosulta
        $where = $mensagens->addWhere(array("CD_MENSAGEM = ?" => $params['cd_mensagem']))
                                    ->getWhere();
        
        // Retorna as transa��es, menus e sistemas
        $select = $mensagens->select()
                            ->from(array("WM" => "WEB_MENSAGEM"), array("WM.*"))
                            ->where($where);
        
        // Recupera a transa��o
        $mensagem  = $mensagens->fetchRow($select);
        
         // Reenvia os valores para o formul�rio
        $this->_helper->RePopulaFormulario->repopular($mensagem->toArray(), "lower");
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
        
        // Seta alguns valores padr�o para a p�gina sem pagina��o
        $this->defineValoresPadrao($params, false);
        
        // Renderiza a view jogando para a variavel
        $html = $this->view->render('mensagem/relatorio.phtml');

        // Monta o PDF com os dados da view
        //$this->view->domPDF($html, "a4", ("portrait" or "landscape"));
        $this->view->domPDF($html, "a4", "landscape");
    }
    
    /**
     * Define alguns valores padr�o que ser�o carregados e 
     * mostrados em mais de uma p�gina
     * 
     * @param array $params Parametros da requisi��o
     */
    public function defineValoresPadrao($params = array(), $pagination = true) {
        
        $db = Zend_Registry::get("db");
        
        // Instancia as classe de dados
        $mensagens  = new WebMensagemModel();
        
        // Captura a sess�o
        $sessao = new Zend_Session_Namespace('portoweb');
        
        // Retorna para a view os sistemas cadastrados
        $this->view->mensagens = $mensagens->fetchAll();
        
        // Retorna a query de consulta
        $select = $mensagens->queryBuscaMensagens($params);
        
        // Define os par�metros para a consulta e retorna o resultado da pesquisa
        $resConsulta = $mensagens->fetchAll($select);
        
        // Se estiver habilitado a pagina��o mostra os dados paginados
        if($pagination) {
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
            
        } else {
            // Joga para a view o resultado da consulta
            $this->view->resConsulta = $resConsulta;
        }
    }
}