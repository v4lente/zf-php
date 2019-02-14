<?php
/**
 * Esta classe tem como objetivo manipular os dados da Aba de Grupo Mensagem.
 *
 * @author     Márcio Souza Duarte
 * @category   Marca Sistemas
 * @copyright  Copyright (c) 1991-2010 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 */
class ConfiguracoesSistema_TabGrupoMensagemController extends Marca_Controller_Abstract_Operacao {
    
    /**
     * 
     * Construtor da classe
     * 
     * @return void
     */
    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array()) {
        
        // Chama o construtor pai
        parent::__construct($request, $response, $invokeArgs);
        
        // Captura os parametros passados por GET
        $params = $this->getRequest()->getParams();
        
        // Captura a sessão
        $sessao = new Zend_Session_Namespace('portoweb');

        // Caputara e grava o código da transação da janela pai
        if (isset($params['pai_cd_mensagem'])) {
            $sessao->pai_cd_mensagem = $params['pai_cd_mensagem'];
        }
        
        Zend_Registry::set("pai_cd_mensagem", $sessao->pai_cd_mensagem);
    }
    
    /**
     * Método inicial para carregamento de classes do controlador
     *
     * @return void
     */
    public function init() {
        
        // Carrega o método de inicialização da classe pai
        parent::init();
        
        // Define o layout a ser utilizado
        $this->_helper->layout->setLayout("layout-aba");
        
        // Carrega o modelo de dados
        Zend_Loader::loadClass('WebGrupoMensagemModel');
        //Zend_Loader::loadClass('GrupoModel');
    }


    /**
     * Método principal da classe
     *
     * @return void
     */
    public function indexAction() {
        
        // Captura os parametros passados por GET
        $params = $this->getRequest()->getParams();
        
        // Instancia a classe de sistemas web
        $gruposMensagems = new WebGrupoMensagemModel();
        
        // Captura a sessão
        $sessao = new Zend_Session_Namespace('portoweb');
        
        // Busca os grupos/usuário que estão ligados a mensagem
        $dados = array("cd_mensagem" => Zend_Registry::get("pai_cd_mensagem"));

        $select = $gruposMensagems->buscaGruposMensagem($dados)->orderByList();

        // Define os parâmetros para a consulta e retorna o resultado da pesquisa
        $resConsulta = $gruposMensagems->fetchAll($select);
        
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
     * Gera um documento para cadastro de um novo aditivo
     *
     * @return void
     */
    public function novoAction(){
        
    	// Captura a sessão
    	$sessao = new Zend_Session_Namespace('portoweb');
    	
        // Instancia a classe de sistemas web
    	$gruposMensagems = new WebGrupoMensagemModel();
        
        // Retorna os grupos existentes na tabela GRUPO
        $dados  = array("cd_mensagem" => Zend_Registry::get("pai_cd_mensagem"));

        $select = $gruposMensagems->buscaGruposMensagemNaoConectados($dados);

        $this->view->grupos = $gruposMensagems->getCombo($select, array("CODIGO" => "CD_GRUPO", "DESCRICAO" => "NO_GRUPO"));
        
        // Reenvia os valores para o formulário
        $this->_helper->RePopulaFormulario->repopular(array("CD_MENSAGEM" => $sessao->pai_cd_mensagem), "lower");
    
    }
    
    
    /**
     * Salva uma ação a transação
     *
     * @return void
     */
    public function salvarAction() {
        
        // Recupera os parametros da requisição
        $params = $this->_request->getParams();
        
        // Instancia o modelo
        $gruposMensagems = new WebGrupoMensagemModel();
        
        // Monta os dados para salvar
        $dados = array("CD_MENSAGEM" => Zend_Registry::get("pai_cd_mensagem"),
                       "CD_GRUPO"    => $params["cd_grupo"]);
        
        // Valida os dados obrigatórios
        if($gruposMensagems->isValid($dados)) {
            
            // Se o registro não existir insere, caso contrário edita
            if($params["operacao"] == "novo") {
                // Insere o novo aditivo
                $gruposMensagems->insert($dados);
                
                // Redireciona para ação novo
                $this->_forward('novo');
                
            } else {
                // Monta a condição do where
                $where = "CD_MENSAGEM            = " . Zend_Registry::get("pai_cd_mensagem") . " AND " . 
                         "RTRIM(LTRIM(CD_GRUPO)) = '" . trim($params["cd_grupo"]) . "'";
                
                // Atualiza os dados
                $gruposMensagems->update($dados, $where);
                
                // Redireciona para ação de selecionar
                $this->_forward('selecionar');
                
            }
            
        }
        
    }


    /**
     * Exclui um aditivo selecionado de um contrato
     *
     * @return void
     */
    public function excluirAction(){
        
        // Recupera os parametros da requisição
        $params = $this->_request->getParams();
        
        // Instancia o modelo
        $gruposMensagems = new WebGrupoMensagemModel();
        
        // Valida os dados obrigatórios
        if(Zend_Registry::get("pai_cd_mensagem") != "" && $params['cd_grupo'] != "") {
            
        	// Monta a condição do where
            $where = "CD_MENSAGEM            = "  . Zend_Registry::get("pai_cd_mensagem") . " AND " . 
                     "RTRIM(LTRIM(CD_GRUPO)) = '" . trim($params["cd_grupo"]) . "'";
            
            // Atualiza os dados
            $delete = $gruposMensagems->delete($where);
            
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
        
    }


    /**
     * Seleciona um aditivo referente a um contrato
     * @return void
     */
    public function selecionarAction() {
        
        // Recupera os parametros da requisição
        $params = $this->_request->getParams();
        
        // Instancia a classe de sistemas web
        $gruposMensagems = new WebGrupoMensagemModel();
                        
        // Define os parâmetros para a consulta e retorna o resultado da pesquisa
        $dados  = array("cd_mensagem" => $params['cd_mensagem'], "cd_grupo" => $params['cd_grupo']);

        $select = $gruposMensagems->buscaGruposMensagem($dados);

        $linha  = $gruposMensagems->fetchRow($select);
        
        // Reenvia os valores para o formulário
        $this->_helper->RePopulaFormulario->repopular($linha->toArray(), "lower");
        
    }


    /**
     * Pesquisa os aditivos
     *
     * @return void
     */
    public function pesquisarAction() { }
    
    
    /**
     * Gera o relatório de aditivos a partir de uma listagem
     *
     * @return void
     */
    public function relatorioAction() { }
    
}