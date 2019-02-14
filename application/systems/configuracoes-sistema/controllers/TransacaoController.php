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
class ConfiguracoesSistema_TransacaoController extends Marca_Controller_Abstract_Operacao {

    /**
     * (non-PHPdoc)
     * @see library/Marca/Controller/Abstract/Marca_Controller_Abstract_Operacao#init()
     */
    public function init() {
        parent::init();
        
        // Carrega os modelos de dados
        Zend_Loader::loadClass("WebSistemaModel");
        Zend_Loader::loadClass("WebMenuSistemaModel");
        Zend_Loader::loadClass("WebTransacaoModel");
        Zend_Loader::loadClass("WebTransacaoAcaoModel");
        Zend_Loader::loadClass("WebGrupoTransacaoModel");
        Zend_Loader::loadClass("WebAcaoModel");
        
    }
    
    /**
     * Metodo index
     * objetivo: Método principal da classe
     */
    public function indexAction() { 
        
        // Captura os parametros passados por GET
        $params = $this->getRequest()->getParams();
        
        // Seta alguns valores padrão para a página
        $this->defineValoresPadrao($params);
        
        // Reenvia os valores para o formulário
        $this->_helper->RePopulaFormulario->repopular($params);
                
    }
    
     /**
     * Metodo novo
     * objetivo: utilizado para formularios de cadastro
     */
    public function novoAction() { 
        
        // Captura os parametros passados por GET
        $params = $this->getRequest()->getParams();
        
        // Instancia os modelos de dados
        $sistemas   = new WebSistemaModel();
        $menus      = new WebMenuSistemaModel();
        $transacoes = new WebTransacaoModel();
        
        // Joga para a view os sistemas
        $this->view->sistemas = $sistemas->getSistemas();
        
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
        
        // Instancia a classe de transacao
        $transacoes     = new WebTransacaoModel();
        $transacoesAcao = new WebTransacaoAcaoModel();
        $acoes          = new WebAcaoModel();
        
        if($params["cd_transacao"] == "") {
            $params["cd_transacao"] = $transacoes->nextVal();
        }
        
        // Se não for passada a ordem da transação, pega uma automática
        if($params["ord_transacao"] == "") {
            $select = $transacoes->select()
                                 ->from($transacoes, array("ORD_TRANSACAO" => new Zend_Db_Expr("MAX(ORD_TRANSACAO) + 1")))
                                 ->where("CD_MENU = {$params["cd_menu"]}");

            $linha  = $transacoes->fetchRow($select);
            
            if($linha->ORD_TRANSACAO != "") {
                $params["ord_transacao"] = $linha->ORD_TRANSACAO; 
            } else {
                $params["ord_transacao"] = 1;
            }
        }
        
        // Monta os dados que serão salvos
        $dados = array("CD_MENU"           => (int) $params["cd_menu"],
                       "CD_TRANSACAO"      => (int) $params["cd_transacao"],
                       "NO_TRANSACAO"      => $params["no_transacao"],
                       "DS_TRANSACAO"      => $params["ds_transacao"],
                       "ORD_TRANSACAO"     => (int) $params["ord_transacao"],
                       "AMB_DESENV"        => $params["amb_desenv"],
                       "OBJ_EXECUTADO"     => $params["obj_executado"],
                       "FORMAT_REL"        => $params["format_rel"],
                       "FL_VISIVEL"        => (int) $params["fl_visivel"],
                       "FL_PUBLICO"        => (int) $params["fl_publico"],
                       "FL_NOVA_JANELA"    => (int) $params["fl_nova_janela"],
                       "FL_LOG_SESSAO"     => (int) $params["fl_log_sessao"],
                       "FL_RELATORIO"      => (int) $params["fl_relatorio"],
                       "NO_ARQ_AJUDA"      => strtolower($params["no_arq_ajuda"]),
                       "FL_TIPO_TRANSACAO" => $params["fl_tipo_transacao"]);
        
        // Verifica as regras do modelo de dados
        if($transacoes->isValid($dados)) {
        
            // Verifica se a operação é de NOVO
            if($params["operacao"] == "novo") {
                
            	// Seta o código da transação
                $this->_request->setParam("cd_transacao", $params["cd_transacao"]);
            	
                // Insere os dados do sistema
                $insert = $transacoes->insert($dados);
                
                // Cadastra as principais ações para cadas transação nova
                if($insert) {
                    
                    // Busca as ações que são obrigatórias
                    $resAcoes = $acoes->fetchAll("FL_OBRIGATORIO = 1 AND FL_TIPO_ACAO IN ('C', '{$params["fl_tipo_transacao"]}')", "ORD_ACAO ASC");
                    $ordem    = 1;
                    foreach($resAcoes as $acao) {
                        
                        // Monta os dados para salvar
                        $dados = array("CD_TRANSACAO" => $params["cd_transacao"],
                                       "CD_ACAO"      => $acao->CD_ACAO,
                                       "ORD_ACAO"     => $ordem);
                        
                        // Insere a nova ação
                        $transacoesAcao->insert($dados);
                        
                        // Incrementa a ordem
                        $ordem++;
                    }
                    
                }
                
            } else {
                
                // Define os filtros para a atualização
                $where = $transacoes->addWhere(array("CD_TRANSACAO = ?" => $params['cd_transacao']))
                                    ->getWhere();
                
                // Atualiza os dados
                $update = $transacoes->update($dados, $where);
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
        
        // Instancia a classe de transacao
        $transacoes       = new WebTransacaoModel();
        $transacoesAcoes  = new WebTransacaoAcaoModel();
        $gruposTransacoes = new WebGrupoTransacaoModel();
        
        // Define os filtros para a exclusão
        $where = $transacoes->addWhere(array("CD_TRANSACAO = ?" => $params['cd_transacao']))
                            ->getWhere();
        
        // Exclui os grupos ligados a transação
        $delete1 = $gruposTransacoes->delete($where);
        
        // Exclui as ações ligadas a transação
        $delete2 = $transacoesAcoes->delete($where);
        
        // Exclui a transação
        $delete3 = $transacoes->delete($where);
        
        // Verifica se o registro foi excluído
        if($delete3) {
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
        
        // Instancia os modelos de dados
        $sistemas   = new WebSistemaModel();
        $menus      = new WebMenuSistemaModel();
        $transacoes = new WebTransacaoModel();
        $acoes      = new WebAcaoModel();
        
        // Define os filtros para a cosulta
        $where = $transacoes->addWhere(array("WT.CD_TRANSACAO = ?" => $params['cd_transacao']))
                            ->getWhere();
        
        // Retorna as transações, menus e sistemas
       $select = $transacoes->select()
                             ->setIntegrityCheck(false)
                             ->from(array("WT" => "WEB_TRANSACAO"), array("WT.*", "WM.NO_MENU", "WS.CD_SISTEMA", "WS.NO_SISTEMA"))
                             ->join(array("WM" => "WEB_MENU_SISTEMA"), "WT.CD_MENU = WM.CD_MENU", array())
                             ->join(array("WS" => "WEB_SISTEMA"), "WM.CD_SISTEMA = WS.CD_SISTEMA", array())
                             ->where($where);
        
        // Recupera a transação
        $transacao  = $transacoes->fetchRow($select);
        
        // Joga para a view os sistemas
        $this->view->sistemas = $sistemas->getSistemas();
        
        // Joga para a view os sistemas
        $this->view->menus    = $menus->getMenus($transacao->CD_SISTEMA);
        
        // Reenvia os valores para o formulário
        $this->_helper->RePopulaFormulario->repopular($transacao->toArray(), "lower");
    }

    /**
     * Metodo relatorio
     * objetivo: utilizado para gerar um relatorio a partir de uma listagem
     */
    public function relatorioAction() { 

        // Chama o método relatório da classe pai
        parent::relatorio();

        // Captura os parametros passados por GET
        $params = $this->getRequest()->getParams();
        
        // Seta alguns valores padrão para a página sem paginação
        $this->defineValoresPadrao($params, false);
        
    }
    
    /**
     * Busca os menus dos sistemas via ajax
     *
     * @return JSON
     */
    public function retornaMenuSistemaXAction() {

        // Verifica se arrequisição foi passada por Ajax
        if($this->_request->isXmlHttpRequest()) {
            // Captura os parametros passados por GET
            $params = $this->getRequest()->getParams();

            // Instancia o modelo
            $menus = new WebMenuSistemaModel();
            
            // Define os filtros para a cosulta
            $where = $menus->addWhere(array("CD_SISTEMA = ?" => $params['cd_sistema']))->getWhere();

            // Captura o registro
            $linhas = $menus->fetchAll($where, "NO_MENU ASC");
                        
            // Retorna os dados por json
            $this->_helper->json(Marca_ConverteCharset::converter($linhas->toArray()), true);

            // Limpa os objetos da memoria
            unset($menus);
        }

    }
    
    
    /**
     * Verifica a ordem de exibição da transação
     *
     * @return JSON
     */
    public function verificaOrdemExibicaoXAction() {

        // Verifica se arrequisição foi passada por Ajax
        if($this->_request->isXmlHttpRequest()) {
            // Captura os parametros passados por GET
            $params = $this->getRequest()->getParams();
            
            // Instancia o modelo
            $transacoes = new WebTransacaoModel();
            
            // Define os filtros para a cosulta
            $where = $transacoes->addWhere(array("CD_MENU = ?"       => $params['cd_menu']))
                                ->addWhere(array("ORD_TRANSACAO = ?" => $params['ord_transacao']))
                                ->getWhere();

            // Captura o registro
            $linha = $transacoes->fetchRow($where);
            
            // Converte a linha para array
            $retLinha = array();
            if($linha->CD_TRANSACAO != "") {
                $retLinha = $linha->toArray();
            }
            
            // Retorna os dados por json
            $this->_helper->json(Marca_ConverteCharset::converter($retLinha), true);

            // Limpa os objetos da memoria
            unset($transacoes);
        }

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
        $sistemas   = new WebSistemaModel();
        $menus      = new WebMenuSistemaModel();
        $transacoes = new WebTransacaoModel();
        
        // Captura a sessão
        $sessao = new Zend_Session_Namespace('portoweb');
        
        // Retorna para a view os sistemas cadastrados
        $this->view->sistemas = $sistemas->getSistemas();
        
        // Se houver sistema monta os menus
        if($params['cd_sistema'] != "") {
            // Retorna para a view os menus cadastrados a partir do sistema
            $this->view->menus = $menus->getMenus($params['cd_sistema']);
        }
        
        // Retorna a query das transações
        $select = $transacoes->queryBuscaTransacoes($params);
        
        // Se estiver habilitado a paginação mostra os dados paginados
        if($pagination) {
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
            
        } else {
            // Executa a consulta
            $resConsulta = $transacoes->fetchAll($select);
            
            // Joga para a view o resultado da consulta
            $this->view->resConsulta = $resConsulta;
        }
    
    }

}