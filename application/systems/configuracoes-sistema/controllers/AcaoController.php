<?php

/**
 *
 * Classe responsável por cadastrar os ações WEB
 * 
 * Tem como função permitir o cadastro de todas as ações/eventos que serão utilizados/chamados 
 * através da barra de tarefas do layout WEB, assim como rotinas Ajax utilizadas para aplicar 
 * consistências em transações da aplicação WEB.
 *
 * @author     Márcio Souza Duarte
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
                                                        "LB_NOME",
                                                        "LB_ACAO_BARRA_TAREFA",
                                                        "LB_IMAGEM_ACAO",
                                                        "LB_ORD_ACAO",
                                                        "LB_DESCRICAO"), $sessao->perfil->CD_IDIOMA);
    }
    
    /**
     * Metodo index
     * objetivo: Método principal da classe
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
     * objetivo: utilizado para as operações de INSERT/UPDATE
     */
    public function salvarAction() { 
        // Recupera os parametros da requisição
        $params = $this->_request->getParams();
        
        // Instancia a classe de acoes web
        $acoes = new WebAcaoModel();
        
        // Captura o próximo código se for um novo cadastro
        if($params["cd_acao"] == "") {
            $cd_acao = $acoes->nextVal();
            
            // Seta o código
            $params["cd_acao"] = $cd_acao;
            
            // Joga o código nos parâmetros da requisição
            $this->_request->setParam("cd_acao", $cd_acao);
        }
        
        // Sem arquivo de imagem
        $params["arq_img_acao"] = null;
        
        // Monta os dados que serão salvos
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

            // Verifica se a operação é de NOVO
            if($params['operacao'] == "novo") {
                
                // Insere os dados do acao
                $insert = $acoes->insert($dados);
                
            } else {
                
                // Define os filtros para a atualização
                $where = $acoes->addWhere(array("CD_ACAO = ?" => $params['cd_acao']))
                                  ->getWhere();
                
                // Atualiza os dados
                $update = $acoes->update($dados, $where);
            }
            
            // Redireciona para ação de selecionar
            $this->_forward('selecionar');
            
        }
        
    }

    /**
     * Metodo excluir
     * objetivo: utilizado para a operação de DELETE
     */
    public function excluirAction() { 
        // Recupera os parametros da requisição
        $params = $this->_request->getParams();
        
        // Instancia a classe de acoes web
        $acoes = new WebAcaoModel();
        
        // Define os filtros para a exclusão
        $where = $acoes->addWhere(array("CD_ACAO = ?" => $params['cd_acao']))
                       ->getWhere();
        
        // Exclui o acao
        $delete = $acoes->delete($where);
        
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
        
        // Instancia a classe de acoes web
        $acoes = new WebAcaoModel();
        
        // Captura a sessão
        $sessao = new Zend_Session_Namespace('portoweb');
        
        // Define os parâmetros para a consulta e retorna o resultado da pesquisa
        $resConsulta = $acoes->fetchAll($where, $order);
        
        // Retorna a query de pesquisa
        $select = $acoes->queryBuscaAcoes($params);

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
        
        // Instancia a classe de acoes web
        $acoes = new WebAcaoModel();
        
        // Define os filtros para a cosulta
        $where = $acoes->addWhere(array("CD_ACAO = ?" => $params['cd_acao']))->getWhere();
        
        // Recupera o acao selecionado
        $acao  = $acoes->fetchRow($where);
        
         // Reenvia os valores para o formulário
        $this->_helper->RePopulaFormulario->repopular($acao->toArray(), "lower");
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
        
        // Recupera os parametros da requisição
        $params = $this->_request->getParams();
        
        // Instancia a classe de acoes web
        $acoes = new WebAcaoModel();
        
        // Captura a sessão
        $sessao = new Zend_Session_Namespace('portoweb');
        
        // Retorna a query de pesquisa
        $select = $acoes->queryBuscaAcoes($params);
        
        // Define os parâmetros para a consulta e retorna o resultado da pesquisa
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