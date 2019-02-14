<?php

/**
 *
 * Classe para manipular os grupos de usu�rios
 *
 * @author     M�rcio Souza Duarte
 * @category   Marca Sistemas
 * @copyright  Copyright (c) 1991-2010 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 */
class ConfiguracoesSistema_GrupoUsuarioController extends Marca_Controller_Abstract_Operacao {

    /**
     * (non-PHPdoc)
     * @see library/Marca/Controller/Abstract/Marca_Controller_Abstract_Operacao#init()
     */
    public function init() {
        parent::init();
        
        // Carrega os modelos de dados
        Zend_Loader::loadClass("WebGrupoModel");
        
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
                                                        "LB_NOME_NS", 
                                                        "LB_DESCRICAO"), $sessao->perfil->CD_IDIOMA);
    }
    
    /**
     * Metodo index
     * objetivo: M�todo principal da classe
     */
    public function indexAction() { 
        
        // Recupera os parametros da requisi��o
        $params = $this->_request->getParams();
        
        // Instancia a classe de grupos
        $webGrupos = new WebGrupoModel();
        
        // Captura a sess�o
        $sessao = new Zend_Session_Namespace('portoweb');
        
        // Ordena��o da consulta
        $order = $this->_helper->OrdenaConsulta->ordenar("NO_GRUPO", "ASC");
          
        // Define os par�metros para a consulta e retorna o resultado da pesquisa
        $resConsulta = $webGrupos->fetchAll(null, $order);
        
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
        /*
        // Instancia a classe de grupos
        $webGrupos = new WebGrupoModel();
        
        // Recupera o �ltimo c�digo do sistema
        $params["cd_grupo"] = $webGrupos->nextVal();
        
        // Reenvia os valores para o formul�rio
        $this->_helper->RePopulaFormulario->repopular($params);
        */
    }

    /**
     * Metodo salvar
     * objetivo: utilizado para as opera��es de INSERT/UPDATE
     */
    public function salvarAction() { 
        
        // Recupera os parametros da requisi��o
        $params = $this->_request->getParams();
        
        // Instancia a classe de grupos
        $webGrupos = new WebGrupoModel();
        
        if($params["cd_grupo"] == "") {
            $params["cd_grupo"] = $webGrupos->nextVal();
        }
        
        // Monta os dados que ser�o salvos
        $dados = array("CD_GRUPO" => $params["cd_grupo"],
                       "NO_GRUPO" => $params["no_grupo"],
                       "DS_GRUPO" => $params["ds_grupo"]);
        
        // Verifica as regras do modelo de dados
        if($webGrupos->isValid($dados)) {

            // Verifica se a opera��o � de NOVO
            if($params['operacao'] == "novo") {
                
                // Insere os dados do sistema
                $insert = $webGrupos->insert($dados);
                
                // Redireciona para a��o novo
                $this->_forward('novo');
                
            } else {
                
                // Define os filtros para a atualiza��o
                $where = $webGrupos->addWhere(array("CD_GRUPO = ?" => $params['cd_grupo']))
                                   ->getWhere();
                
                // Atualiza os dados
                $update = $webGrupos->update($dados, $where);

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
        
        // Instancia a classe de grupos
        $webGrupos = new WebGrupoModel();
        
        // Define os filtros para a exclus�o
        $where = $webGrupos->addWhere(array("CD_GRUPO = ?" => $params['cd_grupo']))
                           ->getWhere();
        
        // Exclui o sistema
        $delete = $webGrupos->delete($where);
        
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
        
        // Instancia a classe de grupos
        $webGrupos = new WebGrupoModel();
        
        // Captura a sess�o
        $sessao = new Zend_Session_Namespace('portoweb');
        
        // Define os filtros para a cosulta
        $where = $webGrupos->addWhere(array("CD_GRUPO    = ?" => $params['cd_grupo']))
                           ->addWhere(array("NO_GRUPO LIKE ?" => $params['no_grupo']))
                           ->addWhere(array("DS_GRUPO LIKE ?" => $params['ds_grupo']))
                           ->getWhere();
        
        // Ordena��o da consulta
        $order = $this->_helper->OrdenaConsulta->ordenar("NO_GRUPO", "ASC");
                           
        // Define os par�metros para a consulta e retorna o resultado da pesquisa
        $resConsulta = $webGrupos->fetchAll($where, $order);
        
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
        
        // Instancia a classe de grupos
        $webGrupos = new WebGrupoModel();
        
        // Define os filtros para a cosulta
        $where = $webGrupos->addWhere(array("CD_GRUPO = ?" => $params['cd_grupo']))->getWhere();
        
        // Recupera o sistema selecionado
        $grupo  = $webGrupos->fetchRow($where);
        
         // Reenvia os valores para o formul�rio
        $this->_helper->RePopulaFormulario->repopular($grupo->toArray(), "lower");
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

        // inicializa a variavel como um array
        $resConsulta = array();
        
        // Instancia a classe de grupos
        $webGrupos = new WebGrupoModel();
        
        // Define os filtros para a cosulta
        $where = $webGrupos->addWhere(array("CD_GRUPO    = ?" => $params['cd_grupo']))
                           ->addWhere(array("NO_GRUPO LIKE ?" => $params['no_grupo']))
                           ->addWhere(array("DS_GRUPO LIKE ?" => $params['ds_grupo']))
                           ->getWhere();
                          
        // Ordena��o da consulta
        $order = $this->_helper->OrdenaConsulta->ordenar();
        
        // Define os par�metros para a consulta e retorna o resultado da pesquisa
        $resConsulta = $webGrupos->fetchAll($where, $order);
                    
        // Joga para a view o resultado da consulta
        $this->view->resConsulta = $resConsulta;

        // Renderiza a view jogando para a variavel
        $html = $this->view->render('grupo-usuario/relatorio.phtml');

        // Monta o PDF com os dados da view
        //$this->view->domPDF($html, "a4", ("portrait" or "landscape"));
        $this->view->domPDF($html, "a4", "landscape");
    }
    
}