<?php
/**
 *
 * Classe GlossarioController
 *
 * Esta classe tem como objetivo receber as requisições
 * quando não é passado parâmetro no navegador, ou seja,
 * ela é responsável por mostrar o conteúdo inicial do
 * sistema.
 *
 * @category   Marca Sistemas
 * @package    IndexController
 * @copyright  Copyright (c) 1991-2009 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 * @version    $Id: IndexController.php 0 2009-11-10 17:25:00 marcio $
 */


/**
 * @category   Marca Sistemas
 * @package    IndexController
 * @copyright  Copyright (c) 1991-2009 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class ConfiguracoesSistema_GlossarioController extends Marca_Controller_Abstract_Base {

    /**
     * Método inicial para carregamento de classes do controlador
     *
     * @return void
     */
    public function init() {

    	// Carrega o método de inicialização da classe pai
        parent::init();

	// Carrega a regra classe das tradução
    	Zend_Loader::loadClass('Marca_MetodosAuxiliares');
    	Zend_Loader::loadClass('MarcaGlossarioModel');

    }


    /**
     * Método principal da classe
     *
     * @return void
     */
    public function indexAction() {

        $tradutor = new Marca_MetodosAuxiliares();

        $termo  = $tradutor->traduz('LB_PLV_TERMO', 'POR');
        $desc   = $tradutor->traduz('LB_DESC_TERMO','POR');
        $tabela = $tradutor->traduz('LB_TAB_NOME','POR');
        $coluna = $tradutor->traduz('LB_COLUNA','POR');

        $this->view->termo  = $termo;
        $this->view->desc   = $desc;
        $this->view->tabela = $tabela;
        $this->view->coluna = $coluna;

    }


	/**
     *
     * @return unknown_type
     */
    public function novoAction(){

        $tradutor = new Marca_MetodosAuxiliares();

        $coluna = $tradutor->traduz('LB_COLUNA','POR');
        $termo  = $tradutor->traduz('LB_PLV_TERMO', 'POR');
        $desc   = $tradutor->traduz('LB_DESC_TERMO','POR');
        $tabela = $tradutor->traduz('LB_TAB_NOME','POR');
        $codigo = $tradutor->traduz('LB_CODIGO','POR');


        $this->view->codigo = $codigo;
        $this->view->termo  = $termo;
        $this->view->desc   = $desc;
        $this->view->tabela = $tabela;
        $this->view->coluna = $coluna;
    }


    /**
     *
     * @return unknown_type
     */
    public function excluirAction(){

        $tradutor = new Marca_MetodosAuxiliares();

        $termo  = $tradutor->traduz('LB_PLV_TERMO', 'POR');
        $desc   = $tradutor->traduz('LB_DESC_TERMO','POR');
        $tabela = $tradutor->traduz('LB_TAB_NOME','POR');
        $coluna = $tradutor->traduz('LB_COLUNA','POR');

        $this->view->termo  = $termo;
        $this->view->desc   = $desc;
        $this->view->tabela = $tabela;
        $this->view->coluna = $coluna;


    	// Verifica se foi realizada a requisição
        if($this->getRequest()->isPost()){

	    	$glossario = new MarcaGlossarioModel();

	    	$params = $this->_request->getParams();

	    	$where = "CD_GLOSSARIO = {$params['cd_glossario']} ";

	    	try {
		    	$glossario->delete($where);

		    	$this->view->mensagemSistema = $this->view->mostraMensagem(
	                                                       array('Operação realizada.')
	                                                       , "SUCESSO",2);
                // Parametros para a paginação
                $this->view->extraParams = $params;

                $resConsulta = array();

                $resConsulta = $glossario->fetchAll(null, 'PALAVRA ASC');

                $pagina = $this->_getParam('pagina', 1);

                $paginator = Zend_Paginator::factory($resConsulta);
                $paginator->setCurrentPageNumber($pagina);
                $paginator->setItemCountPerPage(10);

                $this->view->paginator = $paginator;


	    	} catch (Exception $e) {
	    	  $this->view->mensagemSistema = $this->view->mostraMensagem(
                                                           array("Este registro não pode ser excluido.")
                                                           , "ERRO",3);
	    	  // Reenvia os valores para o formulário
              $this->_helper->RePopulaFormulario->repopular($params);
	    	}
        }
    }


    /**
     *
     * @return unknown_type
     */
    public function selecionarAction() {

        $tradutor = new Marca_MetodosAuxiliares();

        $coluna = $tradutor->traduz('LB_COLUNA','POR');
        $termo  = $tradutor->traduz('LB_PLV_TERMO', 'POR');
        $desc   = $tradutor->traduz('LB_DESC_TERMO','POR');
        $tabela = $tradutor->traduz('LB_TAB_NOME','POR');
        $codigo = $tradutor->traduz('LB_CODIGO','POR');


        $this->view->codigo = $codigo;
        $this->view->termo  = $termo;
        $this->view->desc   = $desc;
        $this->view->tabela = $tabela;
        $this->view->coluna = $coluna;

    	try {
    		$glossarios = new MarcaGlossarioModel();

    		// Pega os parametros enviados na requisição
	        $params = $this->_request->getParams();

	        $where = "CD_GLOSSARIO = {$params['cd_glossario']} ";

	        // Executa a busca e retorna um registro
	        $row = $glossarios->fetchRow($where);

	        // Reenvia os valores para o formulário
	        $this->_helper->RePopulaFormulario->repopular($row->toArray(), "lower");

    	} catch (Exception $e) {
    	   // Mostra mensagem de erro caso não passe na validação
           $this->view->mensagemSistema = $this->view->mostraMensagem(
                                                              array("Erro ao selecionar registro.")
                                                              , "ATENÇÃO",1);

    	}

    }


    /**
     * Salva
     *
     * @return void
     */
    public function salvarAction() {

        $tradutor = new Marca_MetodosAuxiliares();

        $coluna = $tradutor->traduz('LB_COLUNA','POR');
        $termo  = $tradutor->traduz('LB_PLV_TERMO', 'POR');
        $desc   = $tradutor->traduz('LB_DESC_TERMO','POR');
        $tabela = $tradutor->traduz('LB_TAB_NOME','POR');
        $codigo = $tradutor->traduz('LB_CODIGO','POR');


        $this->view->codigo = $codigo;
        $this->view->termo  = $termo;
        $this->view->desc   = $desc;
        $this->view->tabela = $tabela;
        $this->view->coluna = $coluna;
        
        // Instancia do modelo
    	$glossarios = new MarcaGlossarioModel();

    	// Pega os parametros da requisição
    	$params = $this->_request->getParams();

    	// Verifica se foi realizada a requisição
    	if($this->getRequest()->isPost()){


	    	$dados = array(	"CD_GLOSSARIO"  => (int)$params['cd_glossario'],
	    	                "PALAVRA"       => $params['palavra'],
	    	                "DS_PALAVRA"    => $params['ds_palavra'],
	    				    "NO_TABELA"     => $params['no_tabela'],
	    				    "NO_COLUNA"     => $params['no_coluna']
	    				   );

            // Verifica as regras do modelo de dados
	    	if($glossarios->isValid($dados)) {

	    		//Retorno da operação do modelo
	    		$retorno = FALSE;

	    		// Verifica se a operação é de NOVO
	            if($params['operacao'] == "novo"){

		            try {

                        //Pega sequência da tabela cd_glossario
                        $dados['CD_GLOSSARIO'] = $glossarios->nextVal();

	                    // Insere os dados
	                    $retorno = $glossarios->insert($dados);

	                    $this->view->mensagemSistema = $this->view->mostraMensagem(
	                                                           array('Operação realizada.')
	                                                           , "SUCESSO",2);

		            } catch (Exception $e) {
		                $this->view->mensagemSistema = $this->view->mostraMensagem(
		                                                           array($e->getMessage())
		                                                           , "ERRO",3);
		              // Reenvia os valores para o formulário
		              $this->_helper->RePopulaFormulario->repopular($params);
		            }

	            } else {

	               try {
	                    // Monta a condição do where
	                    $where = "CD_GLOSSARIO = {$params['cd_glossario']} ";

	                    // Atualiza os dados
	                    $retorno = $glossarios->update($dados, $where);

                        $this->view->mensagemSistema = $this->view->mostraMensagem(
                                                               array('Operação realizada.')
                                                               , "SUCESSO",2);
                        // Redireciona para ação de selecionar
                        $this->_forward('selecionar');

                    } catch (Exception $e) {
                        $this->view->mensagemSistema = $this->view->mostraMensagem(
                                                                   array("Erro ao salvar o registro.")
                                                                   , "ERRO",3);
                      // Reenvia os valores para o formulário
                      $this->_helper->RePopulaFormulario->repopular($params);
                    }
	            }

	        } else {
	            // Mostra mensagem de erro caso não passe na validação
	        	$this->view->mensagemSistema = $this->view->mostraMensagem(
	        	                                              $glossarios->getValidationMessages()
	        	                                              , "ATENÇÃO",1);
	        	// Reenvia os valores para o formulário
                $this->_helper->RePopulaFormulario->repopular($params);
	        }
    	}

    }


 	/**
     * Pesquisa os processos
     *
     * @return void
     */
    public function pesquisarAction() {

        //Tradução dos labels
        $tradutor = new Marca_MetodosAuxiliares();

        $termo  = $tradutor->traduz('LB_PLV_TERMO', 'POR');
        $desc   = $tradutor->traduz('LB_DESC_TERMO','POR');
        $tabela = $tradutor->traduz('LB_TAB_NOME','POR');
        $coluna = $tradutor->traduz('LB_COLUNA','POR');

        $this->view->termo  = $termo;
        $this->view->desc   = $desc;
        $this->view->tabela = $tabela;
        $this->view->coluna = $coluna;

        // Captura os parametros passados por GET
		$params = $this->getRequest()->getParams();

		// Associa as variáveis do banco
        $db = Zend_Registry::get('db');

        // inicializa a variavel como um array
        $resConsulta = array();

        // Instancia a classe
        $glossarios = new MarcaGlossarioModel();
       

        try {
            
            // Retorna a query de consulta
            $select = $glossarios->queryBuscaGlossarios($params);
            
        	// Resultado da pesquisa
            $resConsulta = $glossarios->fetchAll($select);

	        // Parametros para a paginação
	        $this->view->extraParams = $params;

	        $pagina = $this->_getParam('pagina', 1);

	        $paginator = Zend_Paginator::factory($resConsulta);
	        $paginator->setCurrentPageNumber($pagina);
	        $paginator->setItemCountPerPage(10);

	        $this->view->paginator = $paginator;

        } catch (Exception $e) {
            $this->view->mensagemSistema = $this->view->mostraMensagem(
                                                       array("Parametros inválidos.")
                                                       , "ERRO",1);
        }

        // Reenvia os valores para o formulário
        $this->_helper->RePopulaFormulario->repopular($params);

        // Limpa os objetos da memoria
		unset($glossarios);
    }

/**
     * Gera o relatório da pesquisa
     *
     * @return void
     */
    public function relatorioAction() {

    	ini_set('max_execution_time', 180);
        ini_set('memory_limit','256M');

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

        // Instancia a classe
        $glossarios = new MarcaGlossarioModel();

        try {
            
            // Retorna a query de consulta
            $select = $glossarios->queryBuscaGlossarios($params);
            
            // Resultado da pesquisa
            $resConsulta = $glossarios->fetchAll($select);

            $this->view->resConsulta = $resConsulta;

            // Renderiza a view jogando para a variavel
            $html = $this->view->render('index/relatorio.phtml');

            // Monta o PDF com os dados da view
            //$this->view->domPDF($html, "a4", "landscape");
            $this->view->domPDF($html, "a4");

        } catch (Exception $e) {
            Zend_Debug::dump($e);
        }

        // Limpa os objetos da memoria
        unset($glossarios);
    }

    

}