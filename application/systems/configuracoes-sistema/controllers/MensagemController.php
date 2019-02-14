<?php

/**
 *
 * Classe responsável por cadastrar as transações WEB
 *
 * @author     Márcio Souza Duarte
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
        
        // Captura a sessão
        $sessao = new Zend_Session_Namespace('portoweb');
        
        // Carrega a classe de tradução
        Zend_Loader::loadClass("Marca_Controller_Action_Helper_Traduz");
        
        // Instancia o objeto de tradução
        $traducao = new Marca_Controller_Action_Helper_Traduz();
        
        // Monta a tradução dos campos.
        // Nessessário para reduzir a quantidade de acessos ao banco,  
        // diminuindo assim o tempo de carregamento da página.
        $this->view->traducao = $traducao->traduz(array("LB_CODIGO",
                                                        "LB_TITULO",
                                                        "LB_DESCRICAO",
                                                        "LB_DT_INI",
                                                        "LB_DT_FIM",
                                                        "LB_URGENTE"), $sessao->perfil->CD_IDIOMA);
    }
    
    /**
     * Metodo index
     * objetivo: Método principal da classe
     */
    public function indexAction() { 

        // Seta alguns valores padrão para a página
        $this->defineValoresPadrao(array());
    }
    
     /**
     * Metodo novo
     * objetivo: utilizado para formularios de cadastro
     */
    public function novoAction() { 
        
        // Captura os parametros passados por GET
        $params = $this->getRequest()->getParams();
        
        // Seta alguns valores padrão para a página
        $this->defineValoresPadrao($params);
        
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
        
        // Instancia as classe de dados
        $mensagens  = new WebMensagemModel();
        
        // Pega o próximo código
        if($params["cd_mensagem"] == "") {
            $params["cd_mensagem"] = $mensagens->nextVal();
        }
        
        
        // Monta os dados que serão salvos
        $dados = array("CD_MENSAGEM"    => $params["cd_mensagem"],
                       "TITULO"         => $params["titulo"],
                       "DESCRICAO"      => $params["descricao"],
                       "DT_INI"         => $params["dt_ini"],
                       "DT_FIM"         => $params["dt_fim"],
                       "FL_URGENTE"     => $params["fl_urgente"]);
        
        // Verifica as regras do modelo de dados
        if($mensagens->isValid($dados)) {
            
            // Verifica se a operação é de NOVO
            if($params['operacao'] == "novo") {
                
                // Grava os dados
                $insert = $mensagens->insert($dados);
                
                // Redireciona para ação novo
                $this->_forward('selecionar', null, null, $params);
                
            } else {
                
                // Define os filtros para a atualização
                $where = $mensagens->addWhere(array("CD_MENSAGEM = ?" => $params['cd_mensagem']))
                                   ->getWhere();
                
                // Atualiza os dados
                $update = $mensagens->update($dados, $where);

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
        
        // Instancia as classe de dados
        $mensagens       = new WebMensagemModel();
        $gruposMensagems = new WebGrupoMensagemModel();
        
        // Define os filtros para a exclusão
        $where = $mensagens->addWhere(array("CD_MENSAGEM = ?" => $params['cd_mensagem']))
                                    ->getWhere();
        
        // Exclui a mensagem
        $delete1 = $gruposMensagems->delete($where);
        $delete2 = $mensagens->delete($where);
        
        // Verifica se o registro foi excluído
        if($delete2) {
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
        // Captura os parametros passados por GET
        $params = $this->getRequest()->getParams();
        
        // Seta alguns valores padrão para a página
        $this->defineValoresPadrao($params);
        
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
        
        // Instancia as classe de dados
        $mensagens  = new WebMensagemModel();
        
        // Define os filtros para a cosulta
        $where = $mensagens->addWhere(array("CD_MENSAGEM = ?" => $params['cd_mensagem']))
                                    ->getWhere();
        
        // Retorna as transações, menus e sistemas
        $select = $mensagens->select()
                            ->from(array("WM" => "WEB_MENSAGEM"), array("WM.*"))
                            ->where($where);
        
        // Recupera a transação
        $mensagem  = $mensagens->fetchRow($select);
        
         // Reenvia os valores para o formulário
        $this->_helper->RePopulaFormulario->repopular($mensagem->toArray(), "lower");
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
        
        // Seta alguns valores padrão para a página sem paginação
        $this->defineValoresPadrao($params, false);
        
        // Renderiza a view jogando para a variavel
        $html = $this->view->render('mensagem/relatorio.phtml');

        // Monta o PDF com os dados da view
        //$this->view->domPDF($html, "a4", ("portrait" or "landscape"));
        $this->view->domPDF($html, "a4", "landscape");
    }
    
    /**
     * Define alguns valores padrão que serão carregados e 
     * mostrados em mais de uma página
     * 
     * @param array $params Parametros da requisição
     */
    public function defineValoresPadrao($params = array(), $pagination = true) {
        
        $db = Zend_Registry::get("db");
        
        // Instancia as classe de dados
        $mensagens  = new WebMensagemModel();
        
        // Captura a sessão
        $sessao = new Zend_Session_Namespace('portoweb');
        
        // Retorna para a view os sistemas cadastrados
        $this->view->mensagens = $mensagens->fetchAll();
        
        // Retorna a query de consulta
        $select = $mensagens->queryBuscaMensagens($params);
        
        // Define os parâmetros para a consulta e retorna o resultado da pesquisa
        $resConsulta = $mensagens->fetchAll($select);
        
        // Se estiver habilitado a paginação mostra os dados paginados
        if($pagination) {
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
            
        } else {
            // Joga para a view o resultado da consulta
            $this->view->resConsulta = $resConsulta;
        }
    }
}