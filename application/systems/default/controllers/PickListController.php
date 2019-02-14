<?php
/**
 *
 * Classe PickListController
 *
 * Esta classe tem como objetivo montar um formul�rio din�mico.
 *
 * @author     David Valente, Marcio Souza
 * @category   Marca Sistemas
 * @package    PickListController
 * @copyright  Copyright (c) 1991-2009 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 * @version    $Id: IndexController.php 0 2009-11-10 17:25:00 marcio $
 */
class PickListController extends Marca_Controller_Abstract_Comum {
    
    /**
     * M�todo inicial para carregamento de classes do controlador
     *
     * @return void
     */
    public function init() {

        // Carrega o m�todo de inicializa��o da classe pai
        parent::init();
        
        Zend_Loader::loadClass("UsuarioModel");
        Zend_Loader::loadClass("Marca_MetodosAuxiliares");
        Zend_Loader::loadClass("PickListForm");
        Zend_Loader::loadClass("Marca_PicklistDb");
        Zend_Loader::loadClass("Marca_Controller_Action_Helper_Traduz");
        
        // Registra as vari�veis globalmente
        $sessao = new Zend_Session_Namespace('portoweb');
        $metAut = new Marca_MetodosAuxiliares();
        Zend_Registry::set('sessao', $sessao);
        Zend_Registry::set('metaux', $metAut);
    }

    /**
     * M�todo principal da classe
     *
     * Este m�todo � chamado quando o bot�o da picklist for executado.
     * Ao clicar no bot�o � recuperado da sess�o o nome do pr�prio bot�o
     * que conter� dentro os dados da picklist a ser gerada
     *
     * @return void
     */
    public function indexAction() {

	    // Pega os parametros da requisi��o
        $parametros = $this->getRequest()->getParams();

        // Monta o formulario com os dados da picklist
        $this->geraFormulario($parametros['picklist_id'], $parametros['reference']);

    }
	/**
     * Faz a pesquisa referente aos dados passados
     *
     * @return void
     */
    public function pesquisarAction() {
        // Associa as vari�veis do banco
        $db         = Zend_Registry::get('db');

    	// capitura a sess�o
    	$sessao     = Zend_Registry::get('sessao');

        // Retorna os dados do formul�rio
        $parametros = $this->_getAllParams();
        
        $valores    = array();
        foreach($parametros as $indice => $valor) {
        	$indice  = str_replace("_p_", ".", $indice);
        	$valores[$indice] = $valor;
        }

        // Limpa a memoria
       	unset($parametros);

       	 // Captura sess�o da picklist
        $picklist_estrutura = $sessao->picklist[$valores['picklist_id']];

        // converte de utf-8 para iso-8859-1 e desserializa 
        // a estrutura da picklist retornando um obejto
        $picklist = unserialize(iconv("utf-8", "iso-8859-1", $picklist_estrutura));

        // Gera o formul�rio
        $formulario = $this->geraFormulario($valores['picklist_id'], $valores['reference']);
        
        // Ordena��o
        $order = null;
        if(isset($valores['column'])){
            $order  = $valores['column'] . " " . $valores['orderby'];
        }
        
        // Gera a query da picklist
        // Obs.: Ao gerar a linha da pesquisa, retorna para o documento pai o primeiro 
        // campo(Geralmente o C�digo do registro), mais a referencia, que � o campo onde 
        // este c�digo ser� jogado.
        $sql = $picklist->executa($valores, $order);
                
        // Define o retorno dos dados por �ndices
        $db->setFetchMode(Zend_Db::FETCH_NUM);
        
        // Monta e executa a consulta
        $resConsulta = array();
        $resConsulta = $db->fetchAll($sql);

        $url_params = $this->getRequest()->getParams();

		$this->view->extraParams = $url_params;

        $pagina = $this->_getParam('pagina', 1);

        $paginator = Zend_Paginator::factory($resConsulta);
        $paginator->setCurrentPageNumber($pagina);
        $paginator->setItemCountPerPage(10);

        // Retorna para o modo objeto o retorno das consultas
        // Define o retorno dos dados por �ndices
        $db->setFetchMode(Zend_Db::FETCH_OBJ);
        
        $nomesColunas = $picklist->getNomesCamposSelect();
        $aliasColunas = $picklist->getAliasCamposSelect();
        
        Zend_Loader::loadClass("Marca_Controller_Action_Helper_Traduz");
        $traducao = new Marca_Controller_Action_Helper_Traduz();
        
        // Recupera os campos a serem traduzidos
        $labels = array();
        foreach($aliasColunas as $label) {
        	$labels[] = $label;
        }
        
        // Recupera os campos traduzidos
        $idioma = $sessao->perfil->CD_IDIOMA != "" ? $sessao->perfil->CD_IDIOMA : "POR";
        $traducoes = $traducao->traduz($labels, $idioma);
        
        // Recupera os campos do formulario
        $camposFormulario = $picklist->getCamposFormulario();
        
        // Recupera os estilos dos campos
        $estilos = $picklist->getStyleCamposSelect();
        
        $this->view->reference     = $valores['reference'];
        $this->view->nomesColunas  = $nomesColunas;
        $this->view->tituloColunas = $traducoes;
        $this->view->estiloColunas = $estilos;
        $this->view->paginator     = $paginator;

        // Reenvia os valores para o formul�rio
        $this->_helper->RePopulaFormulario->repopular($url_params);

    }

	/**
     * Monta o formul�rio e joga na view
     *
     * @return $formulario
     */
    private function geraFormulario($picklist_id, $reference) {
        
    	// Pega os parametros da  requisi��o
    	$params = $this->getRequest()->getParams();
    	
    	// capitura a sess�o
    	$sessao = Zend_Registry::get('sessao');

        // Captura sess�o da picklist
        $picklist_estrutura = $sessao->picklist[$picklist_id];
         
        // Gera o formul�rio
        $formulario = new PickListForm('form_picklist', array('disableLoadDefaultDecorators' => true));
        $formulario->setAttrib('onsubmit', 'return false;');
        $formulario->setMethod("GET");
        $formulario->setAction($this->view->baseUrl() . "/pick-list/pesquisar/picklist_id/$picklist_id/reference/$reference");

        $picklist = unserialize(iconv("utf-8", "iso-8859-1", $picklist_estrutura));
        $campos = $picklist->getCamposFormulario();

        $arrayElementos = array();
        
        // Instancia a classe de tradu��o
        $traducao = new Marca_Controller_Action_Helper_Traduz();

        // Captura o elemento e monta o formul�rio
		if(count($campos) > 0) {

			// Cria campo hidden com a identifica��o da picklist, utilizado pela sess�o
			$formulario->addElement("hidden","picklist_id", array('value'=>$picklist_id));

			// Cria campo hidden com o campo chave de referencia para o JS
			$formulario->addElement("hidden","reference", array('value'=>$reference));

			// Adiciona a identifica��o do campo (picklist) no formulario
			$arrayElementos[] = "picklist_id";

			// Adiciona a referencia do campo chave que sera manipulado
			$arrayElementos[] = "reference";
			
			// Percorre cada elemento gerado no where da consulta
			foreach ($campos as $elemento) {
                
				// Captura os atributos do campo setado no where (NAME, TYPE, LABEL)
				$atributos = $elemento[0];
                
				// Captura os valores setados (utilizado para campos RADIO, SELECT, CHECKBOX)
				$valores   = $elemento[1];

				$atributos["name"] = str_replace(".", "_p_", $atributos['name']);
				$arrayElementos[]  = $atributos["name"];

				// Caso n�o tenha o atributo LABEL setado, assume o NAME como LABEL
				if(empty($atributos["label"])) {
					$atributos["label"] = $atributos["name"];
				}

			    // Se for select monta o array
			    if(strtolower($atributos["type"]) != "text") {
			        $options = array();
			        
			        // Controla se foram passados valores para a condi��o
			        // no �ltimo par�metro, caso n�o tenha nenhum valor, 
			        // verifica se tem o campo "value" nos atributos.
			        // Caso tenha verifica se tem valor e joga na vari�vel 
			        // $valores os valores setados para o campo. 
			        if(count($valores) == 0) {
			            if(count($atributos["value"]) > 0) {
			                $valores = $atributos["value"];
			            }
			        }

			        $defaultValue = "";
			        foreach($valores as $indice => $valor) {
			        	
			        	// Pega o primeiro campo para setar como default no radio
			            if ($defaultValue == "") {
			            	$defaultValue = $valor;
			            }			        	
			            $options[$indice] = $valor;
			        }

			        // Traduz o label do campo
			        $label = $traducao->traduz($atributos["label"], $sessao->perfil->CD_IDIOMA);

			        $formulario->addElement($atributos["type"], $atributos["name"], $atributos["options"]);
				    $atributosForm = $formulario->getElement($atributos["name"]);
				    $atributosForm->setLabel($label.":")				                  
	                              ->setDecorators($formulario->getStandardElementDecorator())	                              
	                              ->removeDecorator('Errors');
			    	
	                    
					// Se for do tipo radio seta os multioptions
	                if(strtolower($atributos["type"]) == "radio") {
	                	$atributosForm->setMultiOptions($options)	                				  
	                				  ->setSeparator("");
	                }
	                
	                // Seta o value que foi informado na tela pelo usuario              
					if (trim($params[$atributos["name"]]) !="") {
	                	$atributosForm->setValue($params[$atributos["name"]]);						
	                } else {
	                	$atributosForm->setValue($defaultValue);
	                }

	                // Limpa os atributos que n�o ser�o utilizados mais
	                unset($atributos["name"]);
	                unset($atributos["label"]);
	                unset($atributos["type"]);
	                
	                // Seta o resto dos atributos no elemento
	                foreach($atributos as $indice => $valor) {
	                   $atributosForm->setAttrib($indice, $valor);
	                }

			    } else {
					
			    	// Traduz o label do campo
			    	$label = $traducao->traduz($atributos["label"], $sessao->perfil->CD_IDIOMA);

				    $formulario->addElement($atributos["type"], $atributos["name"], $atributos["options"]);
				    $atributosForm = $formulario->getElement($atributos["name"]);
				    $atributosForm->setLabel($label.":")
	                              ->setDecorators($formulario->getStandardElementDecorator())
	                              ->removeDecorator('Errors');
	                              
	                // Seta o value que foi informado na tela pelo usuario              
					if (trim($params[$atributos["name"]]) !="") {
						$atributosForm->setValue($params[$atributos["name"]]);						
					} else if (isset($atributos["value"])){ // Pega o value informado como default na cria��o da picklist
						$atributosForm->setValue($atributos["value"]);
					}
	                              

	               // Limpa os atributos que n�o ser�o utilizados mais
                    unset($atributos["name"]);
                    unset($atributos["label"]);
                    unset($atributos["type"]);
                    
                    // Seta o resto dos atributos no elemento
                    foreach($atributos as $indice => $valor) {
                       $atributosForm->setAttrib($indice, $valor);
                    }
			    }
			}

            // Agrupa os elementos
            $formulario->addDisplayGroup(
                            $arrayElementos,
                            'elementGroup',
                            array('decorators' => $formulario->getStandardGroupDecorator())
                        );
		}

		// Seta o t�tulo
		$this->view->titulo = 'Picklist Pesquisa';

        $this->view->formulario = $formulario;

		// Retorna o formulario
		return $arrayElementos;
    }
}