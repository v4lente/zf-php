<?php

/**
 *
 * Classe para manipular os grupos de usuários
 *
 * @author     Márcio Souza Duarte
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
                                                        "LB_NOME_NS", 
                                                        "LB_DESCRICAO"), $sessao->perfil->CD_IDIOMA);
    }
    
    /**
     * Metodo index
     * objetivo: Método principal da classe
     */
    public function indexAction() { 
        
        // Recupera os parametros da requisição
        $params = $this->_request->getParams();
        
        // Instancia a classe de grupos
        $webGrupos = new WebGrupoModel();
        
        // Captura a sessão
        $sessao = new Zend_Session_Namespace('portoweb');
        
        // Ordenação da consulta
        $order = $this->_helper->OrdenaConsulta->ordenar("NO_GRUPO", "ASC");
          
        // Define os parâmetros para a consulta e retorna o resultado da pesquisa
        $resConsulta = $webGrupos->fetchAll(null, $order);
        
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
                
                // Insere os dados do sistema
                $insert = $webGrupos->insert($dados);
                
                // Redireciona para ação novo
                $this->_forward('novo');
                
            } else {
                
                // Define os filtros para a atualização
                $where = $webGrupos->addWhere(array("CD_GRUPO = ?" => $params['cd_grupo']))
                                   ->getWhere();
                
                // Atualiza os dados
                $update = $webGrupos->update($dados, $where);

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
        
        // Instancia a classe de grupos
        $webGrupos = new WebGrupoModel();
        
        // Define os filtros para a exclusão
        $where = $webGrupos->addWhere(array("CD_GRUPO = ?" => $params['cd_grupo']))
                           ->getWhere();
        
        // Exclui o sistema
        $delete = $webGrupos->delete($where);
        
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
        
        // Instancia a classe de grupos
        $webGrupos = new WebGrupoModel();
        
        // Captura a sessão
        $sessao = new Zend_Session_Namespace('portoweb');
        
        // Define os filtros para a cosulta
        $where = $webGrupos->addWhere(array("CD_GRUPO    = ?" => $params['cd_grupo']))
                           ->addWhere(array("NO_GRUPO LIKE ?" => $params['no_grupo']))
                           ->addWhere(array("DS_GRUPO LIKE ?" => $params['ds_grupo']))
                           ->getWhere();
        
        // Ordenação da consulta
        $order = $this->_helper->OrdenaConsulta->ordenar("NO_GRUPO", "ASC");
                           
        // Define os parâmetros para a consulta e retorna o resultado da pesquisa
        $resConsulta = $webGrupos->fetchAll($where, $order);
        
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
        
        // Define os filtros para a cosulta
        $where = $webGrupos->addWhere(array("CD_GRUPO    = ?" => $params['cd_grupo']))
                           ->addWhere(array("NO_GRUPO LIKE ?" => $params['no_grupo']))
                           ->addWhere(array("DS_GRUPO LIKE ?" => $params['ds_grupo']))
                           ->getWhere();
                          
        // Ordenação da consulta
        $order = $this->_helper->OrdenaConsulta->ordenar();
        
        // Define os parâmetros para a consulta e retorna o resultado da pesquisa
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