<?php

/**
 *
 * Classe responsável por cadastrar os sistemas WEB
 *
 * @author     Márcio Souza Duarte
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
        
        // Captura a sessão
        $sessao = new Zend_Session_Namespace('portoweb');
        
        // Carrega a classe de tradução
        Zend_Loader::loadClass("Marca_Controller_Action_Helper_Traduz");
        
        // Instancia o objeto de tradução
        $traducao = new Marca_Controller_Action_Helper_Traduz();
        
        // Monta a tradução dos campos.
        // Nessessário para reduzir a quantidade de acessos ao banco,  
        // diminuindo assim o tempo de carregamento da página.
        $this->view->traducao = $traducao->traduz(array("LB_COD_SISTEMA",
                                                        "LB_NO_SISTEMA", 
                                                        "LB_ORDENACAO", 
                                                        "LB_ORDEM_EXIBICAO_MENU", 
                                                        "LB_DS_SISTEMA"), $sessao->perfil->CD_IDIOMA);
    }
    
    /**
     * Metodo index
     * objetivo: Método principal da classe
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
     * objetivo: utilizado para as operações de INSERT/UPDATE
     */
    public function salvarAction() { 
        // Recupera os parametros da requisição
        $params = $this->_request->getParams();
        
        // Instancia a classe de sistemas web
        $sistemas = new WebSistemaModel();
        
        // Captura o próximo código se for um novo cadastro
        if($params["cd_sistema"] == "") {
            $cd_sistema = $sistemas->nextVal();
            
            // Seta o código
            $params["cd_sistema"] = $cd_sistema;
            
            // Joga o código nos parâmetros da requisição
            $this->_request->setParam("cd_sistema", $cd_sistema);
        }
        
        // Monta os dados que serão salvos
        $dados = array("CD_SISTEMA"     => $params["cd_sistema"],
                       "NO_SISTEMA"     => $params["no_sistema"],
                       "NO_PASTA_ZEND"  => $params["no_pasta_zend"],
                       "DS_SISTEMA"     => $params["ds_sistema"],
                       "ORD_SISTEMA"    => $params["ord_sistema"]);
        
        // Verifica as regras do modelo de dados
        if($sistemas->isValid($dados)) {

            // Verifica se a operação é de NOVO
            if($params['operacao'] == "novo") {
                
                // Insere os dados do sistema
                $insert = $sistemas->insert($dados);
                
            } else {
                
                // Define os filtros para a atualização
                $where = $sistemas->addWhere(array("CD_SISTEMA = ?" => $params['cd_sistema']))
                                  ->getWhere();
                
                // Atualiza os dados
                $update = $sistemas->update($dados, $where);
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
        
        // Instancia a classe de sistemas web
        $sistemas = new WebSistemaModel();
        
        // Captura a sessão
        $sessao = new Zend_Session_Namespace('portoweb');
        
        // Retorna a query com os sistemas
        $select = $sistemas->queryBuscaSistemas($params);

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
        
        // Instancia a classe de sistemas web
        $sistemas = new WebSistemaModel();
        
        // Define os filtros para a cosulta
        $where = $sistemas->addWhere(array("CD_SISTEMA = ?" => $params['cd_sistema']))->getWhere();
        
        // Recupera o sistema selecionado
        $sistema  = $sistemas->fetchRow($where);
        
         // Reenvia os valores para o formulário
        $this->_helper->RePopulaFormulario->repopular($sistema->toArray(), "lower");
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
        
        // Instancia a classe de sistemas web
        $sistemas = new WebSistemaModel();
        
        // Retorna a query com os sistemas
        $select = $sistemas->queryBuscaSistemas($params);
        
        // Define os parâmetros para a consulta e retorna o resultado da pesquisa
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