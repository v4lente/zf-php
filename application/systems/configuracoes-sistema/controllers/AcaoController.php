<?php

/**
 *
 * Classe respons�vel por cadastrar os a��es WEB
 * 
 * Tem como fun��o permitir o cadastro de todas as a��es/eventos que ser�o utilizados/chamados 
 * atrav�s da barra de tarefas do layout WEB, assim como rotinas Ajax utilizadas para aplicar 
 * consist�ncias em transa��es da aplica��o WEB.
 *
 * @author     M�rcio Souza Duarte
 * @category   Marca Sistemas
 * @copyright  Copyright (c) 1991-2010 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 */
class ConfiguracoesSistema_AcaoController extends Marca_Controller_Abstract_Operacao {

    /**
     * (non-PHPdoc)
     * @see library/Marca/Controller/Abstract/Marca_Controller_Abstract_Operacao#init()
     */
    public function init() {
        parent::init();
        
        // Carrega os modelos de dados
        Zend_Loader::loadClass("WebAcaoModel");
        Zend_Loader::loadClass("UsuarioModel");//teste wagner
        
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
                                                        "LB_ACAO_BARRA_TAREFA",
                                                        "LB_IMAGEM_ACAO",
                                                        "LB_ORD_ACAO",
                                                        "LB_DESCRICAO"), $sessao->perfil->CD_IDIOMA);
    }
    
    /**
     * Metodo index
     * objetivo: M�todo principal da classe
     */
    public function indexAction() { 
        
        // Redireciona para a action pesquisar
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
        
        // Instancia a classe de acoes web
        $acoes = new WebAcaoModel();
        
        // Captura o pr�ximo c�digo se for um novo cadastro
        if($params["cd_acao"] == "") {
            $cd_acao = $acoes->nextVal();
            
            // Seta o c�digo
            $params["cd_acao"] = $cd_acao;
            
            // Joga o c�digo nos par�metros da requisi��o
            $this->_request->setParam("cd_acao", $cd_acao);
        }
        
        // Sem arquivo de imagem
        $params["arq_img_acao"] = null;
        
        // Monta os dados que ser�o salvos
        $dados = array("CD_ACAO"      => $params["cd_acao"],
                       "NO_ACAO"      => $params["no_acao"],
                       "DS_ACAO"      => $params["ds_acao"],
                       "FL_MENU"      => $params["fl_menu"],
                       "FL_TIPO_ACAO" => $params["fl_tipo_acao"],
                       "ORD_ACAO"     => $params["ord_acao"],
                       "LNK_IMG_ACAO" => $params["lnk_img_acao"],
                       "ARQ_IMG_ACAO" => $params["arq_img_acao"],
                       "FL_PERMISSAO" => $params["fl_permissao"]);
                       
        // Verifica as regras do modelo de dados
        if($acoes->isValid($dados)) {

            // Verifica se a opera��o � de NOVO
            if($params['operacao'] == "novo") {
                
                // Insere os dados do acao
                $insert = $acoes->insert($dados);
                
            } else {
                
                // Define os filtros para a atualiza��o
                $where = $acoes->addWhere(array("CD_ACAO = ?" => $params['cd_acao']))
                                  ->getWhere();
                
                // Atualiza os dados
                $update = $acoes->update($dados, $where);
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
        
        // Instancia a classe de acoes web
        $acoes = new WebAcaoModel();
        
        // Define os filtros para a exclus�o
        $where = $acoes->addWhere(array("CD_ACAO = ?" => $params['cd_acao']))
                       ->getWhere();
        
        // Exclui o acao
        $delete = $acoes->delete($where);
        
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
        
        // Instancia a classe de acoes web
        $acoes = new WebAcaoModel();
        
        // Captura a sess�o
        $sessao = new Zend_Session_Namespace('portoweb');
        
        // Define os par�metros para a consulta e retorna o resultado da pesquisa
        $resConsulta = $acoes->fetchAll($where, $order);
        
        // Retorna a query de pesquisa
        $select = $acoes->queryBuscaAcoes($params);

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
        
        // Instancia a classe de acoes web
        $acoes = new WebAcaoModel();
        
        // Define os filtros para a cosulta
        $where = $acoes->addWhere(array("CD_ACAO = ?" => $params['cd_acao']))->getWhere();
        
        // Recupera o acao selecionado
        $acao  = $acoes->fetchRow($where);
        
         // Reenvia os valores para o formul�rio
        $this->_helper->RePopulaFormulario->repopular($acao->toArray(), "lower");
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
        
        // Recupera os parametros da requisi��o
        $params = $this->_request->getParams();
        
        // Instancia a classe de acoes web
        $acoes = new WebAcaoModel();
        
        // Captura a sess�o
        $sessao = new Zend_Session_Namespace('portoweb');
        
        // Retorna a query de pesquisa
        $select = $acoes->queryBuscaAcoes($params);
        
        // Define os par�metros para a consulta e retorna o resultado da pesquisa
        $resConsulta = $acoes->fetchAll($select);
                    
        // Joga para a view o resultado da consulta
        $this->view->resConsulta = $resConsulta;

        // Renderiza a view jogando para a variavel
        $html = $this->view->render('acao/relatorio.phtml');

        // Monta o PDF com os dados da view
        //$this->view->domPDF($html, "a4", ("portrait" or "landscape"));
        $this->view->domPDF($html, "a4", "landscape");

    }

}