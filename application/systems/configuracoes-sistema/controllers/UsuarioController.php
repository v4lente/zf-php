<?php

/**
 *
 * Classe para cadastrar os usu�rios.
 * Tamb�m ser�o cadastrados os grupos de acessos web, 
 * grupos de acessos cliente/servidor e os clientes
 *
 * @author     M�rcio Souza Duarte
 * @category   Marca Sistemas
 * @copyright  Copyright (c) 1991-2010 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 */
class ConfiguracoesSistema_UsuarioController extends Marca_Controller_Abstract_Operacao {

    /**
     * (non-PHPdoc)
     * @see library/Marca/Controller/Abstract/Marca_Controller_Abstract_Operacao#init()
     */
    public function init() {
        parent::init();
        
        // Carrega os modelos de dados
        Zend_Loader::loadClass("UsuarioModel");
        Zend_Loader::loadClass("WebGrupoModel");
        Zend_Loader::loadClass("WebGrupoUsuarioModel");
        Zend_Loader::loadClass("SistemaModel");
        Zend_Loader::loadClass("FuncionarioModel");
        
    }
    
    /**
     * Metodo index
     * objetivo: M�todo principal da classe
     */
    public function indexAction() { 
        
        // Captura os parametros passados por GET
        $params = $this->getRequest()->getParams();
        
        // Seta alguns valores padr�o para a p�gina
        $this->defineValoresPadrao($params);
        
        // Reenvia os valores para o formul�rio
        $this->_helper->RePopulaFormulario->repopular($params);
        
    }
    
     /**
     * Metodo novo
     * objetivo: utilizado para formularios de cadastro
     */
    public function novoAction() { 
        
        // Instancia o modelo de dados
        $sistemas = new SistemaModel();
        
        // Retorna os sistemas
        $this->view->sistemas = $sistemas->getSistemas();
        
    }

    /**
     * Metodo salvar
     * objetivo: utilizado para as opera��es de INSERT/UPDATE
     */
    public function salvarAction() { 
        
        // Recupera os parametros da requisi��o
        $params = $this->_request->getParams();
        
        // Instancia a classe de usu�rios
        $usuarios = new UsuarioModel();
        
        // Monta os dados que ser�o salvos
        $dados = array("CD_USUARIO" 		=> strtoupper(trim($params["cd_usuario"])),
                       "NO_USUARIO" 		=> strtoupper(trim($params["no_usuario"])),
                       "NR_MATRIC" 			=> $params["nr_matric"],
                       "TP_USUARIO" 		=> $params["tp_usuario"],
                       "EMAIL" 				=> $params["email"],
                       "FL_SUPER" 			=> $params["fl_super"],
                       "CD_SISTEMA_PADRAO" 	=> $params["cd_sistema_padrao"],
                       "FL_ESTACAO" 		=> $params["fl_estacao"],
                       "FL_ACESSO_INTRANET" => $params["fl_acesso_intranet"]);
        
        // Verifica as regras do modelo de dados
        if($usuarios->isValid($dados)) {

            // Verifica se a opera��o � de NOVO
            if($params['operacao'] == "novo") {
                
                // Verifica se o c�digo j� n�o est� em uso
                $usuario = $usuarios->fetchRow("TRIM(CD_USUARIO) = '{$dados['CD_USUARIO']}'");
                if($usuario->CD_USUARIO != "") {
                    $msg = "O c�digo \"{$usuario->CD_USUARIO}\" j� est� sendo usado para outro usu�rio.";
                    $this->view->mensagemSistema = $this->view->mostraMensagem(array($msg), "ERRO", 3);
                    
                    // Reenvia os valores para o formul�rio
                    $this->_helper->RePopulaFormulario->repopular($params, "lower");
                    
                    // Redireciona para a��o de selecionar
                    $this->_forward('novo');
                    
                } else {
                    
                    // Insere os dados do sistema
                    $insert = $usuarios->insert($dados);
                    
                    // Se o usu�rio foi inserido com sucesso, 
                    // cria-o tamb�m como usu�rio do sistema
                    if($insert) {
                        
                        // Cria o usu�rio no banco de dados
                        $insert2 = $usuarios->createUser($dados["CD_USUARIO"]);
                        
                    }
                    
                    // Seta o c�digo da transa��o
                    $this->_request->setParam("cd_usuario", $params["cd_usuario"]);
                    
                    // Redireciona para a��o de selecionar
                    $this->_forward('selecionar');
                }
                
            } else {
                
                // Define os filtros para a atualiza��o
                $where = $usuarios->addWhere(array("CD_USUARIO = ?" => $params['cd_usuario']))
                                  ->getWhere();
                
                unset($dados['CD_USUARIO']);
                
                // Atualiza os dados
                $update = $usuarios->update($dados, $where);
                
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
        $usuarios = new UsuarioModel();
        
        // Define os filtros para a exclus�o
        $where = $webGrupos->addWhere(array("CD_USUARIO = ?" => $params['cd_usuario']))
                           ->getWhere();
        
        // Exclui o usu�rio
        $delete = $usuarios->delete($where);
        
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
        
        // Instancia a classe de usuario
        $usuarios = new UsuarioModel();
        $sistemas = new SistemaModel();
        
        // Retorna os sistemas
        $this->view->sistemas = $sistemas->getSistemas();
        
        // Define os filtros para a cosulta
        $where = $usuarios->addWhere(array("U.CD_USUARIO = ?" => $params['cd_usuario']))->getWhere();
        
        // Monta a consulta
        $select = $usuarios->select()
                           ->setIntegrityCheck(false)
                           ->from(array("U" => "USUARIO"), array("U.*", "F.NO_FUNC"))
                           ->joinLeft(array("F" => "FUNCIONARIO"), "U.NR_MATRIC = F.NR_MATRIC", array())
                           ->where($where);
        
        // Recupera o sistema selecionado
        $usuario  = $usuarios->fetchRow($select);
        
        // Reenvia os valores para o formul�rio
        $this->_helper->RePopulaFormulario->repopular($usuario->toArray(), "lower");
    }

    /**
     * Metodo relatorio
     * objetivo: utilizado para gerar um relatorio a partir de uma listagem
     */
    public function relatorioAction() { 
        
        // Chama o m�todo relat�rio da classe pai
        parent::relatorio();

        // Captura os parametros passados por GET
        $params = $this->getRequest()->getParams();

        // inicializa a variavel como um array
        $resConsulta = array();
        
        // Instancia a classe de grupos
        $usuarios = new UsuarioModel();
        
        // Retorna a query com os grupos
        $select = $usuarios->queryBuscaUsuarios($params);
        
        // Define os par�metros para a consulta e retorna o resultado da pesquisa
        $resConsulta = $usuarios->fetchAll($select);
        
        // Joga para a view o resultado da consulta
        $this->view->resConsulta = $resConsulta;
    }
    
	/**
     * Retorna o funcion�rio selecionado
     *
     * @return JSON
     */
    public function retornaFuncionarioXAction() {

        // Verifica se arrequisi��o foi passada por Ajax
        if($this->_request->isXmlHttpRequest()) {
            // Captura os parametros passados por GET
            $params = $this->getRequest()->getParams();
            
            // Instancia o modelo
            $funcionarios = new FuncionarioModel();
            
            // Define os filtros para a cosulta
            $where = $funcionarios->addWhere(array("NR_MATRIC = ?"  => $params['nr_matric']))
                                  ->addWhere(array("NO_FUNC LIKE ?" => $params['no_func']))
                                  ->getWhere();
            
            // Captura o registro
            $linha = $funcionarios->fetchRow($where);
            
            // Converte a linha para array
            if($linha->NR_MATRIC != "") {
                $linha = $linha->toArray();
            }
            
            // Retorna os dados por json
            $this->_helper->json(Marca_ConverteCharset::converter($linha), true);

            // Limpa os objetos da memoria
            unset($funcionarios);
        }

    }
    
    
     /**
     * Define alguns valores padr�o que ser�o carregados e 
     * mostrados em mais de uma p�gina
     * 
     * @param array $params Parametros da requisi��o
     */
    public function defineValoresPadrao($params = array(), $pagination = true) {
        
        // Instancia a classe de grupos
        $usuarios = new UsuarioModel();
        
        // Captura a sess�o
        $sessao = new Zend_Session_Namespace('portoweb');
        
        // Retorna a query com os grupos
        $select = $usuarios->queryBuscaUsuarios($params);
        
        // Se estiver habilitado a pagina��o mostra os dados paginados
        if($pagination) {
            
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
            
        } else {
            
            // Define os par�metros para a consulta e retorna o resultado da pesquisa
            $resConsulta = $webGrupos->fetchAll($select);
            
            // Joga para a view o resultado da consulta
            $this->view->resConsulta = $resConsulta;
        }
    
    }
    
}