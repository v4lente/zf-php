<?php
/**
 * Esta classe tem como objetivo manipular os dados da Aba de TabTabelasRelacionadas.
 *
 * @author     David Valente
 * @category   Marca Sistemas
 * @copyright  Copyright (c) 1991-2011 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 */
class ConfiguracoesSistema_TabTabelasRelacionadasController extends Marca_Controller_Abstract_Operacao {
    
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

        // Captura e grava o código da transação da janela pai
        if (isset($params['pai_cd_tabela'])) {
            $sessao->pai_cd_tabela = $params['pai_cd_tabela'];
        }
        
        Zend_Registry::set("pai_cd_tabela", $sessao->pai_cd_tabela);
        
        // Joga para a view o código da transação pai
        $this->view->pai_cd_tabela = $sessao->pai_cd_tabela;
        
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
        Zend_Loader::loadClass('TabelaRefModel');       
        
        
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
        $tabela = new TabelaRefModel();
        
        // Captura o código da transação pai
        $pai_cd_tabela = Zend_Registry::get("pai_cd_tabela");
        
        // Define os filtros para a cosulta
        $where = $tabela->addWhere(array("CD_TABELA_PAI = ?" => $pai_cd_tabela))
                        ->getWhere();

        // Define os parâmetros para a consulta e retorna o resultado da pesquisa
        $select = $tabela->queryBuscaColunas($where);                

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
    }


    /**
     * Gera um documento para cadastro de um novo aditivo
     *
     * @return void
     */
    public function novoAction(){}
    
    
    /**
     * Salva uma ação a transação
     *
     * @return void
     */
    public function salvarAction() {}


    /**
     * Exclui um aditivo selecionado de um contrato
     *
     * @return void
     */
    public function excluirAction(){}


    /**
     * Seleciona um aditivo referente a um contrato
     * @return void
     */
    public function selecionarAction() {}


    /**
     * Pesquisa os aditivos
     *
     * @return void
     */
    public function pesquisarAction() {}
    
    
    /**
     * Gera o relatório de aditivos a partir de uma listagem
     *
     * @return void
     */
    public function relatorioAction() {}    
    
}