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
        
        // Captura a sess�o
        $sessao = new Zend_Session_Namespace('portoweb');

        // Captura e grava o c�digo da transa��o da janela pai
        if (isset($params['pai_cd_tabela'])) {
            $sessao->pai_cd_tabela = $params['pai_cd_tabela'];
        }
        
        Zend_Registry::set("pai_cd_tabela", $sessao->pai_cd_tabela);
        
        // Joga para a view o c�digo da transa��o pai
        $this->view->pai_cd_tabela = $sessao->pai_cd_tabela;
        
    }    
   
    /**
     * M�todo inicial para carregamento de classes do controlador
     *
     * @return void
     */
    public function init() {
        
        // Carrega o m�todo de inicializa��o da classe pai
        parent::init();
        
        // Define o layout a ser utilizado
        $this->_helper->layout->setLayout("layout-aba");
        
        // Carrega o modelo de dados
        Zend_Loader::loadClass('TabelaRefModel');       
        
        
    }


    /**
     * M�todo principal da classe
     *
     * @return void
     */
    public function indexAction() {
        
        // Captura os parametros passados por GET
        $params = $this->getRequest()->getParams();
        
        // Instancia a classe de sistemas web
        $tabela = new TabelaRefModel();
        
        // Captura o c�digo da transa��o pai
        $pai_cd_tabela = Zend_Registry::get("pai_cd_tabela");
        
        // Define os filtros para a cosulta
        $where = $tabela->addWhere(array("CD_TABELA_PAI = ?" => $pai_cd_tabela))
                        ->getWhere();

        // Define os par�metros para a consulta e retorna o resultado da pesquisa
        $select = $tabela->queryBuscaColunas($where);                

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
    }


    /**
     * Gera um documento para cadastro de um novo aditivo
     *
     * @return void
     */
    public function novoAction(){}
    
    
    /**
     * Salva uma a��o a transa��o
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
     * Gera o relat�rio de aditivos a partir de uma listagem
     *
     * @return void
     */
    public function relatorioAction() {}    
    
}