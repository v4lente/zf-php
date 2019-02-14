<?php
/**
 *
 * Classe MascaraController
 *
 * Esta classe tem como objetivo a padroniza��o das mascaras do sistema.
 *
 * @category   Marca Sistemas
 * @package    MascaraController
 * @copyright  Copyright (c) 1991-2009 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 * @version    $Id: MascaraController.php 0 2009-11-10 17:25:00 Wagner e Roberto $
 */


/**
 * @category   Marca Sistemas
 * @package    MascaraController
 * @copyright  Copyright (c) 1991-2009 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class ConfiguracoesSistema_MascaraController extends Marca_Controller_Abstract_Base {

    /**
     * M�todo inicial para carregamento de classes do controlador
     *
     * @return void
     */
    public function init() {

    	// Carrega o m�todo de inicializa��o da classe pai
        parent::init();

	// Carrega a regra classe das tradu��o
    	Zend_Loader::loadClass('Marca_MetodosAuxiliares');
    	Zend_Loader::loadClass('MarcaMascaraModel');

    }


    /**
     * M�todo principal da classe
     *
     * @return void
     */
    public function indexAction() {

        $tradutor = new Marca_MetodosAuxiliares();

        $cd_masc    = $tradutor->traduz('LB_CD_MASCARA', 'POR');
        $ds_masc  = $tradutor->traduz('LB_DESC_MASCARA','POR');

        $this->view->cd_masc  = $cd_masc;
        $this->view->ds_masc  = $ds_masc;
        
    }


	/**
     *
     * @return unknown_type
     */
    public function novoAction(){

        $tradutor = new Marca_MetodosAuxiliares();

        $cd_masc    = $tradutor->traduz('LB_CD_MASCARA', 'POR');
        $ds_masc    = $tradutor->traduz('LB_DESC_MASCARA','POR');
        $tp_alinha  = $tradutor->traduz('LB_TP_ALINHA','POR');
        $exemplo    = $tradutor->traduz('LB_EXEMPLO','POR');

        $this->view->cd_masc    = $cd_masc;
        $this->view->ds_masc    = $ds_masc;
        $this->view->tp_alinha  = $tp_alinha;
        $this->view->exemplo    = $exemplo;

        $lista = new MarcaMascaraModel();

        $lista_ali = $lista->tipos_alinhamento();

        $this->view->alinhamento = $lista_ali;

    }


    /**
     *
     * @return unknown_type
     */
    public function excluirAction(){

        $tradutor = new Marca_MetodosAuxiliares();

        $cd_masc    = $tradutor->traduz('LB_CD_MASCARA', 'POR');
        $ds_masc    = $tradutor->traduz('LB_DESC_MASCARA','POR');
        $tp_alinha  = $tradutor->traduz('LB_TP_ALINHA','POR');
        $exemplo    = $tradutor->traduz('LB_EXEMPLO','POR');

        $this->view->cd_masc    = $cd_masc;
        $this->view->ds_masc    = $ds_masc;
        $this->view->tp_alinha  = $tp_alinha;
        $this->view->exemplo    = $exemplo;

        $lista = new MarcaMascaraModel();

        $lista_ali = $lista->tipos_alinhamento();

        $this->view->alinhamento = $lista_ali;

    	// Verifica se foi realizada a requisi��o
        if($this->getRequest()->isPost()){

	    	$mascaras = new MarcaMascaraModel();

	    	$params = $this->_request->getParams();

	    	$where = "CD_MASCARA = '{$params['cd_mascara']}' ";

	    	try {
		    	$mascaras->delete($where);

		    	$this->view->mensagemSistema = $this->view->mostraMensagem(
	                                                       array('Opera��o realizada.')
	                                                       , "SUCESSO",2);
                $this->view->paginator = $paginator;

                $resConsulta = array();

                $resConsulta = $mascaras->fetchAll(null, 'CD_MASCARA ASC');

                $pagina = $this->_getParam('pagina', 1);

                $paginator = Zend_Paginator::factory($resConsulta);
                $paginator->setCurrentPageNumber($pagina);
                $paginator->setItemCountPerPage(10);

                $this->view->paginator = $paginator;

	    	} catch (Exception $e) {
	    	  $this->view->mensagemSistema = $this->view->mostraMensagem(
                                                           array("Este registro n�o pode ser excluido.")
                                                           , "ERRO",3);
	    	  // Reenvia os valores para o formul�rio
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

        $cd_masc    = $tradutor->traduz('LB_CD_MASCARA', 'POR');
        $ds_masc    = $tradutor->traduz('LB_DESC_MASCARA','POR');
        $tp_alinha  = $tradutor->traduz('LB_TP_ALINHA','POR');
        $exemplo    = $tradutor->traduz('LB_EXEMPLO','POR');

        $this->view->cd_masc    = $cd_masc;
        $this->view->ds_masc    = $ds_masc;
        $this->view->tp_alinha  = $tp_alinha;
        $this->view->exemplo    = $exemplo;

        $lista = new MarcaMascaraModel();

        $lista_ali = $lista->tipos_alinhamento();

        $this->view->alinhamento = $lista_ali;
        

    	try {
    		$mascaras = new MarcaMascaraModel();

    		// Pega os parametros enviados na requisi��o
	        $params = $this->_request->getParams();

	        $where = "CD_MASCARA = '{$params['cd_mascara']}' ";

	        // Executa a busca e retorna um registro
	        $row = $mascaras->fetchRow($where);

	        // Reenvia os valores para o formul�rio
	        $this->_helper->RePopulaFormulario->repopular($row->toArray(), "lower");

    	} catch (Exception $e) {
    	   // Mostra mensagem de erro caso n�o passe na valida��o
           $this->view->mensagemSistema = $this->view->mostraMensagem(
                                                              array("Erro ao selecionar registro.")
                                                              , "ATEN��O",1);
    	}

    }


    /**
     * Salva
     *
     * @return void
     */
    public function salvarAction() {

        $tradutor = new Marca_MetodosAuxiliares();

        $cd_masc    = $tradutor->traduz('LB_CD_MASCARA', 'POR');
        $ds_masc    = $tradutor->traduz('LB_DESC_MASCARA','POR');
        $tp_alinha  = $tradutor->traduz('LB_TP_ALINHA','POR');
        $exemplo    = $tradutor->traduz('LB_EXEMPLO','POR');

        $this->view->cd_masc    = $cd_masc;
        $this->view->ds_masc    = $ds_masc;
        $this->view->tp_alinha  = $tp_alinha;
        $this->view->exemplo    = $exemplo;

        $lista = new MarcaMascaraModel();

        $lista_ali = $lista->tipos_alinhamento();

        $this->view->alinhamento = $lista_ali;
        
        // Instancia do modelo
    	$mascaras = new MarcaMascaraModel();

    	// Pega os parametros da requisi��o
    	$params = $this->_request->getParams();

    	// Verifica se foi realizada a requisi��o
    	if($this->getRequest()->isPost()){


	    	$dados = array(	"CD_MASCARA"     => $params['cd_mascara'],
	    	                "DS_MASCARA"     => $params['ds_mascara'],
	    	                "EXEMPLO"        => $params['exemplo'],
	    				    "TP_ALINHAMENTO" => $params['tp_alinhamento']
	    				   );

            // Verifica as regras do modelo de dados
	    	if($mascaras->isValid($dados)) {

	    		//Retorno da opera��o do modelo
	    		$retorno = FALSE;

	    		// Verifica se a opera��o � de NOVO
	            if($params['operacao'] == "novo"){

		            try {

	                    // Insere os dados
	                    $retorno = $mascaras->insert($dados);

	                    $this->view->mensagemSistema = $this->view->mostraMensagem(
	                                                           array('Opera��o realizada.')
	                                                           , "SUCESSO",2);

		            } catch (Exception $e) {
		                $this->view->mensagemSistema = $this->view->mostraMensagem(
		                                                           array($e->getMessage())
		                                                           , "ERRO",3);
		              // Reenvia os valores para o formul�rio
		              $this->_helper->RePopulaFormulario->repopular($params);
		            }

	            } else {

	               try {
	                    // Monta a condi��o do where
	                    $where = "CD_MASCARA = '{$params['cd_mascara']}' ";

	                    // Atualiza os dados
	                    $retorno = $mascaras->update($dados, $where);

                        $this->view->mensagemSistema = $this->view->mostraMensagem(
                                                               array('Opera��o realizada.')
                                                               , "SUCESSO",2);
                        // Redireciona para a��o de selecionar
                        $this->_forward('selecionar');

                    } catch (Exception $e) {
                        $this->view->mensagemSistema = $this->view->mostraMensagem(
                                                                   array("Erro ao salvar o registro.")
                                                                   , "ERRO",3);
                      // Reenvia os valores para o formul�rio
                      $this->_helper->RePopulaFormulario->repopular($params);
                    }
	            }

	        } else {
	            // Mostra mensagem de erro caso n�o passe na valida��o
	        	$this->view->mensagemSistema = $this->view->mostraMensagem(
	        	                                              $mascaras->getValidationMessages()
	        	                                              , "ATEN��O",1);
	        	// Reenvia os valores para o formul�rio
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

        //Tradu��o dos labels
        $tradutor = new Marca_MetodosAuxiliares();

        $cd_masc  = $tradutor->traduz('LB_CD_MASCARA', 'POR');
        $ds_masc  = $tradutor->traduz('LB_DESC_MASCARA','POR');

        $this->view->cd_masc  = $cd_masc;
        $this->view->ds_masc  = $ds_masc;

        // Captura os parametros passados por GET
		$params = $this->getRequest()->getParams();

		// Associa as vari�veis do banco
        $db = Zend_Registry::get('db');

        // inicializa a variavel como um array
        $resConsulta = array();

        // Instancia a classe
        $mascaras = new MarcaMascaraModel();

        try {
            
            // Retorna a query de consulta
            $select = $mascaras->queryBuscaMascaras($params);
            
        	// Resultado da pesquisa
            $resConsulta = $mascaras->fetchAll($select);

	        // Parametros para a pagina��o
	        $this->view->extraParams = $params;

	        $pagina = $this->_getParam('pagina', 1);

	        $paginator = Zend_Paginator::factory($resConsulta);
	        $paginator->setCurrentPageNumber($pagina);
	        $paginator->setItemCountPerPage(10);

	        $this->view->paginator = $paginator;

        } catch (Exception $e) {
            
            $this->view->mensagemSistema = $this->view->mostraMensagem(
                                                       array("Parametros inv�lidos.")
                                                       , "ERRO",1);
        }

        // Reenvia os valores para o formul�rio
        $this->_helper->RePopulaFormulario->repopular($params);

        // Limpa os objetos da memoria
		unset($mascaras);
    }

/**
     * Gera o relat�rio da pesquisa
     *
     * @return void
     */
    public function relatorioAction() {

    	ini_set('max_execution_time', 180);
        ini_set('memory_limit','256M');

        // Desabilita o layout padr�o
        $this->_helper->layout->disableLayout();

        // N�o deixa a view renderizar
        $this->_helper->viewRenderer->setNoRender(TRUE);

        // Captura os parametros passados por GET
        $params = $this->getRequest()->getParams();

        // Associa as vari�veis do banco
        $db = Zend_Registry::get('db');

        // Carrega a sess�o do usu�rio
        $sessao = new Zend_Session_Namespace('portoweb');

        // inicializa a variavel como um array
        $resConsulta = array();

        // Instancia a classe
        $mascaras = new MarcaMascaraModel();
        
        try {

             // Retorna a query de consulta
            $select = $mascaras->queryBuscaMascaras($params);
            
            // Resultado da pesquisa
            $resConsulta = $mascaras->fetchAll($select);

            $this->view->resConsulta = $resConsulta;

            // Renderiza a view jogando para a variavel
            // Caminho para chamar o relat�rio
            $html = $this->view->render('mascara/relatorio.phtml');

            // Monta o PDF com os dados da view
            //$this->view->domPDF($html, "a4", "landscape");
            $this->view->domPDF($html, "a4");

        } catch (Exception $e) {
            Zend_Debug::dump($e);
        }

        // Limpa os objetos da memoria
        unset($mascaras);
    }

    

}