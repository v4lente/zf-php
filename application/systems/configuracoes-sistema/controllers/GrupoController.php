<?php

/**
 *
 * Classe para manipular os grupos de acessos que estão
 * ligados aos usuários e as transações
 *
 * @author     Márcio Souza Duarte
 * @category   Marca Sistemas
 * @copyright  Copyright (c) 1991-2010 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 */
class ConfiguracoesSistema_GrupoController extends Marca_Controller_Abstract_Operacao {

    /**
     * (non-PHPdoc)
     * @see library/Marca/Controller/Abstract/Marca_Controller_Abstract_Operacao#init()
     */
    public function init() {
        parent::init();
        
        // Carrega os modelos de dados
        Zend_Loader::loadClass("WebGrupoModel");
        Zend_Loader::loadClass("WebGrupoUsuarioModel");
        Zend_Loader::loadClass("WebGrupoTransacaoModel");
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
        /*
        // Instancia a classe de grupos
        $webGrupos = new WebGrupoModel();
        
        // Recupera o último código do sistema
        $params["cd_grupo"] = $webGrupos->nextVal();
        
        // Reenvia os valores para o formulário
        $this->_helper->RePopulaFormulario->repopular($params);
        */
    }

    /**
     * Metodo salvar
     * objetivo: utilizado para as operações de INSERT/UPDATE
     */
    public function salvarAction() { 
        
        // Recupera os parametros da requisição
        $params = $this->_request->getParams();
        
        // Instancia a classe de grupos
        $webGrupos = new WebGrupoModel();
        
        if($params["cd_grupo"] == "") {
            $params["cd_grupo"] = $webGrupos->nextVal();
        }
        
        // Monta os dados que serão salvos
        $dados = array("CD_GRUPO" => $params["cd_grupo"],
                       "NO_GRUPO" => $params["no_grupo"],
                       "DS_GRUPO" => $params["ds_grupo"]);
        
        // Verifica as regras do modelo de dados
        if($webGrupos->isValid($dados)) {
            
            // Verifica se a operação é de NOVO
            if($params['operacao'] == "novo") {
                 $dados["FL_ATIVO"] = 1 ;
                
                // Insere os dados do sistema
                $insert = $webGrupos->insert($dados);
                
                // Seta o código da transação
                $this->_request->setParam("cd_grupo", $params["cd_grupo"]);
                
            } else {
                
                // Define os filtros para a atualização
                $where = $webGrupos->addWhere(array("CD_GRUPO = ?" => $params['cd_grupo']))
                                   ->getWhere();
                 $dados["FL_ATIVO"] = $params['fl_ativo'] ;
                
                // Atualiza os dados
                $update = $webGrupos->update($dados, $where);
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
        
        // Instancia a classe de grupos
        $webGrupos           = new WebGrupoModel();
        $webGruposUsuarios   = new WebGrupoUsuarioModel();
        $webGruposTransacoes = new WebGrupoTransacaoModel();
        
        // Define os filtros para a exclusão
        $where = $webGrupos->addWhere(array("CD_GRUPO = ?" => $params['cd_grupo']))
                           ->getWhere();
        
        // Exclui os grupos ligados aos usuário
        $delete1 = $webGruposUsuarios->delete($where);
        
        // Exclui os grupos ligados as transacaoes
        $delete2 = $webGruposTransacoes->delete($where);
        
        // Exclui o sistema
        $delete3 = $webGrupos->delete($where);
        
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
        // Seta o código da transação
         $this->_request->setParam("ativo", $params["ativo"]);
        //print_r($params);die;
        
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
        
        // Instancia a classe de grupos
        $webGrupos = new WebGrupoModel();
        
        // Define os filtros para a cosulta
        $where = $webGrupos->addWhere(array("CD_GRUPO = ?" => $params['cd_grupo']))->getWhere();
        
        // Recupera o sistema selecionado
        $grupo  = $webGrupos->fetchRow($where);
        
         // Reenvia os valores para o formulário
        $this->_helper->RePopulaFormulario->repopular($grupo->toArray(), "lower");
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

        // inicializa a variavel como um array
        $resConsulta = array();
        
        // Instancia a classe de grupos
        $webGrupos = new WebGrupoModel();
        
        // Retorna a query com os grupos
        $select = $webGrupos->queryBuscaGrupos($params);
        
        // Define os parâmetros para a consulta e retorna o resultado da pesquisa
        $resConsulta = $webGrupos->fetchAll($select);
                    
        // Joga para a view o resultado da consulta
        $this->view->resConsulta = $resConsulta;

        // Renderiza a view jogando para a variavel
        $html = $this->view->render('grupo-usuario/relatorio.phtml');

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
        
        // Instancia a classe de grupos
        $webGrupos = new WebGrupoModel();
        
        // Captura a sessão
        $sessao = new Zend_Session_Namespace('portoweb');
        
        // Retorna a query com os grupos
        $select = $webGrupos->queryBuscaGrupos($params);
        //print_r($params);die;
                
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
            
            // Define os parâmetros para a consulta e retorna o resultado da pesquisa
            $resConsulta = $webGrupos->fetchAll($select);
            
            // Joga para a view o resultado da consulta
            $this->view->resConsulta = $resConsulta;
        }
    
    }
    
}