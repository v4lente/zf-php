<?php

// Desativa as mensagens de erro
ini_set("display_errors", "Off");

// Carrega o cliente SOAP
require_once('SOAP/Client.php');

define("TYPE_FOLDER","folder");
define("TYPE_REPORTUNIT","reportUnit");
define("TYPE_DATASOURCE","datasource");
define("TYPE_DATASOURCE_JDBC","jdbc");
define("TYPE_DATASOURCE_JNDI","jndi");
define("TYPE_DATASOURCE_BEAN","bean");
define("TYPE_IMAGE","img");
define("TYPE_FONT","font");
define("TYPE_JRXML","jrxml");
define("TYPE_CLASS_JAR","jar");
define("TYPE_RESOURCE_BUNDLE","prop");
define("TYPE_REFERENCE","reference");
define("TYPE_INPUT_CONTROL","inputControl");
define("TYPE_DATA_TYPE","dataType");
define("TYPE_OLAP_MONDRIAN_CONNECTION","olapMondrianCon");
define("TYPE_OLAP_XMLA_CONNECTION","olapXmlaCon");
define("TYPE_MONDRIAN_SCHEMA","olapMondrianSchema");
define("TYPE_XMLA_CONNTCTION","xmlaConntction");
define("TYPE_UNKNOW","unknow");
define("TYPE_LOV","lov"); // List of values...
define("TYPE_QUERY","query"); // List of values...

/**
 * These constants are copied here from DataType for facility
 */
define("DT_TYPE_TEXT",1);
define("DT_TYPE_NUMBER",2);
define("DT_TYPE_DATE",3);
define("DT_TYPE_DATE_TIME",4);

/**
 * These constants are copied here from InputControl for facility
 */
define("IC_TYPE_BOOLEAN",1);
define("IC_TYPE_SINGLE_VALUE",2);
define("IC_TYPE_SINGLE_SELECT_LIST_OF_VALUES",3);
define("IC_TYPE_SINGLE_SELECT_QUERY",4);
define("IC_TYPE_MULTI_VALUE",5);
define("IC_TYPE_MULTI_SELECT_LIST_OF_VALUES",6);
define("IC_TYPE_MULTI_SELECT_QUERY",7);


define("PROP_VERSION","PROP_VERSION");
define("PROP_PARENT_FOLDER","PROP_PARENT_FOLDER");
define("PROP_RESOURCE_TYPE","PROP_RESOURCE_TYPE");
define("PROP_CREATION_DATE","PROP_CREATION_DATE");

// File resource properties
define("PROP_FILERESOURCE_HAS_DATA","PROP_HAS_DATA");
define("PROP_FILERESOURCE_IS_REFERENCE","PROP_IS_REFERENCE");
define("PROP_FILERESOURCE_REFERENCE_URI","PROP_REFERENCE_URI");
define("PROP_FILERESOURCE_WSTYPE","PROP_WSTYPE");

// Datasource properties
define("PROP_DATASOURCE_DRIVER_CLASS","PROP_DATASOURCE_DRIVER_CLASS");
define("PROP_DATASOURCE_CONNECTION_URL","PROP_DATASOURCE_CONNECTION_URL");
define("PROP_DATASOURCE_USERNAME","PROP_DATASOURCE_USERNAME");
define("PROP_DATASOURCE_PASSWORD","PROP_DATASOURCE_PASSWORD");
define("PROP_DATASOURCE_JNDI_NAME","PROP_DATASOURCE_JNDI_NAME");
define("PROP_DATASOURCE_BEAN_NAME","PROP_DATASOURCE_BEAN_NAME");
define("PROP_DATASOURCE_BEAN_METHOD","PROP_DATASOURCE_BEAN_METHOD");


// ReportUnit resource properties
define("PROP_RU_DATASOURCE_TYPE","PROP_RU_DATASOURCE_TYPE");
define("PROP_RU_IS_MAIN_REPORT","PROP_RU_IS_MAIN_REPORT");

// DataType resource properties
define("PROP_DATATYPE_STRICT_MAX","PROP_DATATYPE_STRICT_MAX");
define("PROP_DATATYPE_STRICT_MIN","PROP_DATATYPE_STRICT_MIN");
define("PROP_DATATYPE_MIN_VALUE","PROP_DATATYPE_MIN_VALUE");
define("PROP_DATATYPE_MAX_VALUE","PROP_DATATYPE_MAX_VALUE");
define("PROP_DATATYPE_PATTERN","PROP_DATATYPE_PATTERN");
define("PROP_DATATYPE_TYPE","PROP_DATATYPE_TYPE");

// ListOfValues resource properties
define("PROP_LOV","PROP_LOV");
define("PROP_LOV_LABEL","PROP_LOV_LABEL");
define("PROP_LOV_VALUE","PROP_LOV_VALUE");


// InputControl resource properties
define("PROP_INPUTCONTROL_TYPE","PROP_INPUTCONTROL_TYPE");
define("PROP_INPUTCONTROL_IS_MANDATORY","PROP_INPUTCONTROL_IS_MANDATORY");
define("PROP_INPUTCONTROL_IS_READONLY","PROP_INPUTCONTROL_IS_READONLY");

// SQL resource properties
define("PROP_QUERY","PROP_QUERY");
define("PROP_QUERY_VISIBLE_COLUMNS","PROP_QUERY_VISIBLE_COLUMNS");
define("PROP_QUERY_VISIBLE_COLUMN_NAME","PROP_QUERY_VISIBLE_COLUMN_NAME");
define("PROP_QUERY_VALUE_COLUMN","PROP_QUERY_VALUE_COLUMN");
define("PROP_QUERY_LANGUAGE","PROP_QUERY_LANGUAGE");


// SQL resource properties
define("PROP_QUERY_DATA","PROP_QUERY_DATA");
define("PROP_QUERY_DATA_ROW","PROP_QUERY_DATA_ROW");
define("PROP_QUERY_DATA_ROW_COLUMN","PROP_QUERY_DATA_ROW_COLUMN");


define("MODIFY_REPORTUNIT","MODIFY_REPORTUNIT_URI");
define("CREATE_REPORTUNIT","CREATE_REPORTUNIT_BOOLEAN");
define("LIST_DATASOURCES","LIST_DATASOURCES");
define("IC_GET_QUERY_DATA","IC_GET_QUERY_DATA");

define("VALUE_TRUE","true");
define("VALUE_FALSE","false");

define("RUN_OUTPUT_FORMAT","RUN_OUTPUT_FORMAT");
define("RUN_OUTPUT_FORMAT_PDF","PDF");
define("RUN_OUTPUT_FORMAT_JRPRINT","JRPRINT");
define("RUN_OUTPUT_FORMAT_HTML","HTML");
define("RUN_OUTPUT_FORMAT_XLS","XLS");
define("RUN_OUTPUT_FORMAT_XML","XML");
define("RUN_OUTPUT_FORMAT_CSV","CSV");
define("RUN_OUTPUT_FORMAT_RTF","RTF");
define("RUN_OUTPUT_IMAGES_URI","IMAGES_URI");
define("RUN_OUTPUT_PAGE","PAGE");

/**
 *
 * Classe JasperController
 *
 * Esta classe tem como objetivo processar as chamadas de relatórios 
 * que serão geradas pelo JasperReports
 *
 * @category   Marca Sistemas
 * @package    JasperController
 * @copyright  Copyright (c) 1991-2009 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 * @version    $Id: JasperController.php 0 2012-02-27 17:00:00 marcio $
 */
class JasperController extends Marca_Controller_Abstract_Comum {
	
	private $jasperserver_url  = null;
	private $SCHEDULING_WS_URI = null;
	private $webservices_uri   = null;
	private $namespace         = null;
	private $params			   = null;
	
	/**
	 * 
	 * Construtor da classe
	 * 
	 * @param Zend_Controller_Request_Abstract $request
	 * @param Zend_Controller_Response_Abstract $response
	 * @param array $invokeArgs
	 */
	public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array()){

		parent::__construct($request, $response, $invokeArgs);
		
		// Desabilita o layout padrão
    	$this->_helper->layout->disableLayout();
		
		$this->jasperserver_url = JASPER_URL . "/jasperserver/services/";
		
		$this->SCHEDULING_WS_URI = $this->jasperserver_url . "ReportScheduler?wsdl";
		$this->webservices_uri   = $this->jasperserver_url . "repository";
		
		$this->namespace = "http://www.jaspersoft.com/namespaces/php";

		session_start();
		$_SESSION["username"] = "jasperadmin";
		$_SESSION["password"] = "jasperadmin";
		$this->username       = "jasperadmin";
		$this->password       = "jasperadmin";
		
		// Captura os parametros passados por GET
		$this->params = $this->getRequest()->getParams();
				
		// Remove os parametros desnecessários
		unset($this->params["module"]);
		unset($this->params["controller"]);
		unset($this->params["action"]);
		unset($this->params["PARAM_controller"]);
		unset($this->params["PARAM_action"]);
		unset($this->params["PARAM__rel_metodo"]);
		unset($this->params["PARAM__cd_sistema"]);
		unset($this->params["PARAM__cd_menu"]);
		unset($this->params["PARAM__cd_transacao"]);
		
		// Passa para utf8 os parâmetros
		$v1 = array("__par1__", "__par2__", "__apos__", "__aspas__", "__barra__", "__cbarra__", "*");
		$v2 = array("(", ")", "'", "\"", "/", "\\", "%");
		foreach($this->params as $indice => $valor) {
            if($valor != "") {
                $valor = utf8_encode(stripslashes($valor));
                $valor = str_replace($v1, $v2, $valor);
                $this->params[$indice] = $valor;
            }
		}
		
		//echo "<pre>";
		//print_r($this->params); die;
		
		// Instancia o cliente SOAP
		$connection_params = array("user" => $this->username, "pass" => $this->password, "timeout" => 950);
    	$this->client 	   = new SOAP_client($this->webservices_uri, false, false, $connection_params);
	}
	
    /**
     * Método que gerará o relatório no formato desejado
     *
     * @return void
     */
    public function indexAction() {
        
    	if ($_SESSION["username"] == '') {
    		$this->_request("menu");
    	}
    	
    	// 1 Get the ReoportUnit ResourceDescriptor...
    	$currentUri = "/";
    	
    	if ($this->params['uri'] != '') {
            $currentUri = $this->params['uri'];
    	}
    	
		$result = $this->ws_get($currentUri);
    	if (get_class($result) == 'SOAP_Fault') {
    		$errorMessage = $result->getFault()->faultstring;
    		
    		echo $errorMessage;
    		exit();
    	}
    	else {
    		$folders = $this->getResourceDescriptors($result);
    	}
		
    	if (count($folders) != 1 || $folders[0]['type'] != 'reportUnit') {
    		echo "<H1>Invalid RU ($currentUri)</H1>";
    		echo "<pre>$result</pre>";
            
    		exit();
    	}
    	
    	$reportUnit = $folders[0];
    	
    	// 2. Prepare the parameters array looking in the $this->params for params
    	// starting with PARAM_ ...
    	//
    	
    	$report_params = array();
    	
    	foreach (array_keys($this->params) AS $param_name) {
	    	if (strncmp("PARAM_", $param_name,6) == 0) {
	    		$report_params[substr($param_name,6)] = $this->params[$param_name];
	    	}
	    	
    	}
    	
    	// 3. Execute the report
    	$output_params = array();
    	$output_params[RUN_OUTPUT_FORMAT] = $this->params['format'];
    	
    	if ( $this->params['format'] == RUN_OUTPUT_FORMAT_HTML) {
    	    $page = 0;
    		if ($this->params['page'] != '') $page = $this->params['page'];
    		$output_params[RUN_OUTPUT_PAGE] = $page;
    	 }
    	
    	$result = $this->ws_runReport($currentUri, $report_params,  $output_params, $attachments);
    	
    	// 4.
    	if (get_class($result) == 'SOAP_Fault') {
    		$errorMessage = $result->getFault()->faultstring;
    	 	
    		echo $errorMessage;
    		exit();
    	}
    	
    	$operationResult = $this->getOperationResult($result);
    	
    	if ($operationResult['returnCode'] != '0') {
    		echo "Error executing the report:<br><font color=\"red\">".$operationResult['returnMessage']."</font>";
    	 	exit();
    	 }
    	 
    	 if (is_array($attachments)) {
    	 	if ($this->params['format'] == RUN_OUTPUT_FORMAT_PDF) {
    			
                if(isset($this->params['path']) && $this->params['path'] != "") {
                    
                    foreach (array_keys($attachments) as $key){
                        if ($key == "cid:report"){
                            $f = fopen($this->params['path'], "w");
                            fwrite($f, $attachments[$key]);
                            fclose($f);
                            
                            break;
                        }
                    }
                    
                    echo $this->params['path'];
                    
                } else {
                    header("Content-type: application/pdf");
					// Verifica se o dispositivo é mobile
					if(preg_match("/(android|iphone|avantgo|blackberry|bot|boot|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"])) {
						header("Content-disposition: attachment; filename=rel_" . (rand() * 1000) . ".pdf");
					}
					
                    echo( $attachments["cid:report"]);
                }
                
    		}
    		else if ($this->params['format'] == RUN_OUTPUT_FORMAT_HTML) {
    			// 1. Save attachments....
	   			// 2. Print the report....
    		
    			header ( "Content-type: text/html");
    			
    			foreach (array_keys($attachments) as $key) {
    				if ($key != "cid:report") {
    	 				$f = fopen("images/".substr($key,4),"w");
    					fwrite($f, $attachments[$key]);
    					fclose($f);
    	 			}
    			}
    		
                echo "<center><br />";

                $prevpage = ($page > 0) ? $page-1 : 0;
                $nextpage = $page+1;

                $this->params["page"] = 0;

                $paramsPrevPage = $this->params; $paramsPrevPage["page"] = $prevpage;
                $paramsNextPage = $this->params; $paramsNextPage["page"] = $nextpage;

                $moveToPrevPage = $this->_helper->Criptografia->criptografar($paramsPrevPage);
                $moveToNextPage = $this->_helper->Criptografia->criptografar($paramsNextPage);

                // Pega o caminho base
                $base = $this->view->baseUrl() . "/default/jasper/index/";

                echo "<a href=\"".$base.$moveToPrevPage."\">P&aacute;gina anterior</a> | <a href=\"".$base.$moveToNextPage."\">Pr&oacute;xima p&aacute;gina</a>";
                echo "</center><br /><hr>";

                echo $attachments["cid:report"];

                //print_r(array_keys($attachments));
    		
    		}
    		else if ($this->params['format'] == RUN_OUTPUT_FORMAT_XLS) {
    			header ( 'Content-type: application/xls' );
    			header ( 'Content-Disposition: attachment; filename="report.xls"');
    	  		echo( $attachments["cid:report"]);
    	
    		}
    	
    		exit();
    	 }
    	 else echo "No attachment found!";
    	
    }
	
	/**
     * Método que envia um relatório para o servidor
     *
     * @return void
     */
    public function enviarAction() {
		
		$this->_helper->viewRenderer->setNoRender(true);
		
		// Captura os parâmetros
		$params = $this->params;
		
		// Envia o relatório para o servidor
		$retorno = $this->ws_put($params["cdConsulta"],        // Código da consulta
								 $params["nomeRelatorio"],     // ID do relatório, um nome único
								 $params["titulo"],            // o título do relatório
								 $params["descricao"],         // uma descrição do relatório
								 $params["ambiente"],          // ambiente do relatório (desenv/producao)
								 $params["sistema"],           // sistema que o relatório está
								 $params["caminhoRelatorio"]); // padrão jasper
		
		echo json_encode($retorno);
	}
	
	/**
     * Método que exclui um relatório do servidor
     *
     * @return void
     */
    public function excluirAction() {
		
		$this->_helper->viewRenderer->setNoRender(true);
		
		// Captura os parâmetros
		$params = $this->params;
		
		// Exclui o relatório
		$retorno = $this->ws_delete($params["ambiente"],       // ambiente do relatório (desenv/producao)
									$params["sistema"],        // sistema que o relatório está
									$params["nomeRelatorio"]); // Id do relatório
		
		echo json_encode($retorno);
		
	}
	
	/**
     * Método que move um relatório de uma pasta para outra dentro do servidor
     *
     * @return void
     */
    public function moverAction() {
		
		$this->_helper->viewRenderer->setNoRender(true);
		
		// Captura os parâmetros
		$params = $this->params;
		
		// Move o relatório de local
		$retorno = $this->ws_move($params["ambienteOrigem"],  // ambiente de origem do relatório (desenv/producao)
								  $params["sistemaOrigem"],   // sistema de origem que o relatório está
								  $params["ambienteDestino"], // ambiente de destino do relatório (desenv/producao)
								  $params["sistemaDestino"],  // sistema de destino que o relatório está
								  $params["nomeRelatorio"]);  // ID do relatório
		
		echo json_encode($retorno);
	}
    
    // ws_checkUsername try to list a void URL. If no WS error occurs, the credentials are fine
    function ws_checkUsername($username, $password) {
    
    	$op_xml = "<request operationName=\"list\"><resourceDescriptor name=\"\" wsType=\"folder\" uriString=\"\" isNew=\"false\">".
    		"<label></label></resourceDescriptor></request>";
    
    	$params = array("request" => $op_xml );
		$response = $this->client->call("list", $params, array('namespace' => $this->namespace));
    
    	return $response;
    }
    
    function ws_list($uri, $args = array()) {
    
    	$op_xml = "<request operationName=\"list\">";
    
    	if (is_array ($args)) {
    		$keys = array_keys($args);
    		foreach ($keys AS $key) {
    			$op_xml .="<argument name=\"$key\">".$args[$key]."</argument>";
    		}
    	}
    
    	$op_xml .="<resourceDescriptor name=\"$uri\" wsType=\"folder\" uriString=\"$uri\" isNew=\"false\">".
    		"<label></label></resourceDescriptor></request>";
    
    	$params = array("request" => $op_xml );
		$response = $this->client->call("list", $params, array('namespace' => $this->namespace));
    
    	return $response;
    }
    
    function ws_get($uri, $args = array()) {
    
    	$op_xml = "<request operationName=\"get\">";
    
    	if (is_array ($args)) {
    		$keys = array_keys($args);
    		foreach ($keys AS $key) {
    			$op_xml .="<argument name=\"$key\">".$args[$key]."</argument>";
    		}
    	}
    
    	$op_xml .= "<resourceDescriptor name=\"$uri\" wsType=\"reportUnit\" uriString=\"$uri\" isNew=\"false\">".
    		"<label></label></resourceDescriptor></request>";
    
    	$params = array("request" => $op_xml );
    	
		$response = $this->client->call("get", $params, array('namespace' => $this->namespace));
		    
    	return $response;
    }
    
    
    function ws_runReport($uri, $report_params, $output_params, &$attachments ) {
    
    	$op_xml = "<request operationName=\"runReport\">";
    
    	if (is_array ($output_params)) {
    		$keys = array_keys($output_params);
    		foreach ($keys AS $key) {
    			$op_xml .="<argument name=\"$key\">".$output_params[$key]."</argument>\n";
    		}
    	}
        
    	$op_xml .="<argument name=\"USE_DIME_ATTACHMENTS\"><![CDATA[1]]></argument>";
       	$op_xml .="<resourceDescriptor name=\"\" wsType=\"reportUnit\" uriString=\"$uri\" isNew=\"false\"><label></label>"; 
        
    	// Add parameters...
    	if (is_array ($report_params)) {
    		$keys = array_keys($report_params);
    		foreach ($keys AS $key) {
    			$op_xml .="<parameter name=\"$key\"><![CDATA[".$report_params[$key]."]]></parameter>";
    		}
    	}
    
    	$op_xml     .="</resourceDescriptor></request>";
    	$params      = array("request" => $op_xml );
    	$response    = $this->client->call("runReport", $params, array('namespace' => "http://www.jaspersoft.com/client"));
    	$attachments = $this->client->_soap_transport->attachments;
				
		return $response;
    
    }
    
    
    // Sample to put something on the server
    function ws_put($xmlCdConsulta		 = "",
					$xmlNomeRelatorio    = "",
					$xmlTitulo           = "",
					$xmlDescricao        = "",
					$xmlAmbiente         = "",
					$xmlSistema          = "",
					$xmlCaminhoRelatorio = "") {
		
		if($xmlAmbiente == "") {
			$xmlAmbiente = "DESENV";
		} else {
			$xmlAmbiente = strtoupper($xmlAmbiente);
		}
		
		if($xmlCaminhoRelatorio == "") {
			$xmlCaminhoRelatorio = "jasper";
		}
		
		/*
		// Captura a conexão com o banco de dados
		$db = new Zend_Registry::get("db");
		
		$stmt = $db->query("SELECT * FROM CONSULTA WHERE CD_CONSULTA = " . $xmlCdConsulta);
		while ($stmt->fetchAll() = $linha) {
			$data = utf8_encode($linha->XML);
		}
		unset($stmt);
		*/
		
		$arquivo = $xmlCaminhoRelatorio . "/" . $xmlNomeRelatorio .  ".jrxml";
		$fh      = fopen($arquivo, "rb");
		$data    = fread($fh, filesize("{$arquivo}"));
		fclose($fh);
		
		$attachment = array("body" => $data, "content_type" => "application/octet-stream", "cid" => "123456");
		$this->client->_attachments = array($attachment);
		
		// Captura a sessão
        $sessao = new Zend_Session_Namespace('portoweb');

        // Pega o endereço do banco dados do ambiente
        $config     = new Zend_Config_Ini('./../application/configs/application.ini', APPLICATION_ENV);
        $bdJasper   = $config->resources->db->params->hostJasper;

    	// ESTE ESTÁ FUNCIONANDO PERFEITAMENTE PARA ENVIO
		$op_xml  =	"<request operationName='put' locale='en'>";
		$op_xml .=		"<argument name='CREATE_REPORTUNIT_BOOLEAN'>true</argument>";
		$op_xml .=		"<resourceDescriptor name='{$xmlNomeRelatorio}' wsType='reportUnit' uriString='/reports/{$xmlAmbiente}/{$xmlSistema}/{$xmlNomeRelatorio}' isNew='true'>";
		$op_xml .=			"<label>{$xmlTitulo}</label>";
		$op_xml .=			"<description>{$xmlDescricao}</description>";
		$op_xml .=			"<resourceProperty name='PROP_PARENT_FOLDER'>";
		$op_xml .=				"<value>/reports/{$xmlAmbiente}/{$xmlSistema}</value>";
		$op_xml .=			"</resourceProperty>";
		$op_xml .=	      	"<resourceDescriptor name='{$xmlNomeRelatorio}.jrxml' wsType='jrxml' uriString='/reports/{$xmlAmbiente}/{$xmlSistema}/{$xmlNomeRelatorio}' isNew='true'>";
		$op_xml .=	        	"<label>Main jrxml</label>";
		$op_xml .=	        	"<description>Main jrxml</description>";
		$op_xml .=	        	"<resourceProperty name='PROP_RU_IS_MAIN_REPORT'>";
		$op_xml .=	          		"<value>true</value>";
		$op_xml .=	        	"</resourceProperty>";
		$op_xml .=	        	"<resourceProperty name='PROP_HAS_DATA'>";
		$op_xml .=	          		"<value><![CDATA[true]]></value>";
		$op_xml .=	        	"</resourceProperty>";
		$op_xml .=	        	"<resourceProperty name='PROP_PARENT_FOLDER'>";
		$op_xml .=	          		"<value>/reports/{$xmlAmbiente}/{$xmlSistema}</value>";
		$op_xml .=	        	"</resourceProperty>";
		$op_xml .=	    	"</resourceDescriptor>";
		$op_xml .=	      	"<resourceDescriptor name='{$xmlAmbiente}' wsType='jdbc' uriString='/datasources/{$xmlAmbiente}' isNew='false'>";
		$op_xml .=	      		"<resourceProperty name='PROP_DATASOURCE_DRIVER_CLASS'>";
		$op_xml .=	      			"<value>oracle.jdbc.driver.OracleDriver</value>";
		$op_xml .=	      		"</resourceProperty>";
		$op_xml .=	      		"<label>JServer Jdbc data source</label>";
		$op_xml .=	      		"<description>JServer Jdbc data source</description>";
		$op_xml .=	      		"<resourceProperty name='PROP_PARENT_FOLDER'>";
		$op_xml .=	      			"<value>/datasources/{$xmlAmbiente}</value>";
		$op_xml .=	      		"</resourceProperty>";
		$op_xml .=	      		"<resourceProperty name='PROP_VERSION'>";
		$op_xml .=	      			"<value>0</value>";
		$op_xml .=	      		"</resourceProperty>";
		$op_xml .=	      		"<resourceProperty name='PROP_DATASOURCE_PASSWORD'>";
		$op_xml .=	      			"<value>{$sessao->perfil->SENHA_REAL}</value>";
		//$op_xml .=	      			"<value>p</value>";
		$op_xml .=	      		"</resourceProperty>";
		$op_xml .=	      		"<resourceProperty name='PROP_DATASOURCE_USERNAME'>";
		$op_xml .=	      			"<value>{$sessao->perfil->CD_USUARIO}</value>";
		//$op_xml .=	      			"<value>porto</value>";
		$op_xml .=	      		"</resourceProperty>";
		$op_xml .=	      		"<resourceProperty name='PROP_DATASOURCE_CONNECTION_URL'>";
		$op_xml .=	      			"<value>jdbc:oracle:thin:{$bdJasper}:{$xmlAmbiente}</value>";
		$op_xml .=	      		"</resourceProperty>";
		$op_xml .=	      	"</resourceDescriptor>";
		$op_xml .=		"</resourceDescriptor>";
		$op_xml .=	"</request>";
		
    	$params = array("request" => $op_xml);
    	$response = $this->client->call("put", $params, array('namespace' => $this->namespace));
		
		return $op_xml;
    
    }
	
	// Delete report from server
    function ws_delete($xmlAmbiente = "", $xmlSistema = "", $xmlNomeRelatorio = "") {
    	
		$xmlAmbiente = strtoupper($xmlAmbiente);
		
		// EXCLUIR UM RELATÓRIO
		$op_xml  =	"<request operationName='delete' locale='en'>";
		$op_xml .=		"<argument name='DESTINATION_URI'>/reports/{$xmlAmbiente}/{$xmlSistema}</argument>";
		$op_xml .=		"<resourceDescriptor name='{$xmlNomeRelatorio}' wsType='reportUnit' uriString='/reports/{$xmlAmbiente}/{$xmlSistema}/{$xmlNomeRelatorio}'>";
		$op_xml .=		"</resourceDescriptor>";
		$op_xml .=	"</request>";
		
    	$params = array("request" => utf8_encode($op_xml));
		$response = $this->client->call("delete", $params, array('namespace' => $this->namespace));
        
    	return $response;

    }
	
	//  The report moves from one folder to another
    function ws_move($xmlAmbienteOrigem  = "", $xmlSistemaOrigem  = "", 
					 $xmlAmbienteDestino = "", $xmlSistemaDestino = "", $xmlNomeRelatorio = "") {
		
		$xmlAmbienteOrigem  = strtoupper($xmlAmbienteOrigem);
		$xmlAmbienteDestino = strtoupper($xmlAmbienteDestino);
		
    	// MOVE O RELATÓRIO DE LUGAR
		$op_xml  =	"<request operationName='move' locale='en'>";
		$op_xml .=		"<argument name='DESTINATION_URI'>/reports/{$xmlAmbienteDestino}/{$xmlSistemaDestino}</argument>";
		$op_xml .=		"<resourceDescriptor name='{$xmlNomeRelatorio}' wsType='reportUnit' uriString='/reports/{$xmlAmbienteOrigem}/{$xmlSistemaOrigem}/{$xmlNomeRelatorio}'>";
		$op_xml .=		"</resourceDescriptor>";
		$op_xml .=	"</request>";
    
    	$params = array("request" => utf8_encode($op_xml));
		$response = $this->client->call("move", $params, array('namespace' => $this->namespace));
    
    	return $response;
    
    }
	
	
	// ********** XML related functions *******************
    function getOperationResult($operationResult) {
		
    	$domDocument = new DOMDocument();
    	$domDocument->loadXML($operationResult);
    
    	$operationResultValues = array();
    
    	foreach( $domDocument->childNodes AS $ChildNode ) {
    		
    		if ( $ChildNode->nodeName != '#text' ) {
    
    			if ($ChildNode->nodeName == "operationResult") {
    				
    				foreach( $ChildNode->childNodes AS $ChildChildNode ) {
    
    					if ( $ChildChildNode->nodeName == 'returnCode' ) {
    						$operationResultValues['returnCode'] = $ChildChildNode->nodeValue;
    					}
    					else if ( $ChildChildNode->nodeName == 'returnMessage' ) {
    						$operationResultValues['returnMessage'] = $ChildChildNode->nodeValue;
    					}
    				}
    			}
    		}
    	}
    
    	return $operationResultValues;
    }
    
    
    function getResourceDescriptors($operationResult) {
    	$domDocument = new DOMDocument();
    	$domDocument->loadXML($operationResult);
    
    	$folders = array();
    	$count = 0;
    
    	foreach( $domDocument->childNodes AS $ChildNode ) {
    		
    		if ( $ChildNode->nodeName != '#text' ) {
    
    			if ($ChildNode->nodeName == "operationResult") {
    				
    				foreach( $ChildNode->childNodes AS $ChildChildNode ) {
    
    					if ( $ChildChildNode->nodeName == 'resourceDescriptor' ) {
    						$resourceDescriptor = $this->readResourceDescriptor($ChildChildNode);
    						$folders[ $count ] = $resourceDescriptor;
    						$count++;
    					}
    				}
    			}
    
    		}
    	}
    
    	return $folders;
    }
    
    function readResourceDescriptor($node) {
    	$resourceDescriptor = array();
    
    	$resourceDescriptor['name'] = $node->getAttributeNode("name")->value;
    	$resourceDescriptor['uri']  = $node->getAttributeNode("uriString")->value;
    	$resourceDescriptor['type'] = $node->getAttributeNode("wsType")->value;
    
    	$resourceProperties = array();
    	$subResources       = array();
    	$parameters         = array();
    
    	// Read subelements...
    	foreach( $node->childNodes AS $ChildNode ) {
    		if ( $ChildNode->nodeName == 'label' ) {
    			$resourceDescriptor['label'] = 	$ChildNode->nodeValue;
    		}
    		else if ( $ChildNode->nodeName == 'description' ) {
    			$resourceDescriptor['description'] = 	$ChildNode->nodeValue;
    		}
    		else if ( $ChildNode->nodeName == 'creationDate' ) {
    			$resourceDescriptor['creationDate'] = 	$ChildNode->nodeValue / 1000;
    		}
    		else if ( $ChildNode->nodeName == 'resourceProperty' ) {
    			//$resourceDescriptor['resourceProperty'] = $ChildChildNode->nodeValue;
    			// read properties...
    			$resourceProperty = $this->addReadResourceProperty($ChildNode );
    			$resourceProperties[ $resourceProperty["name"] ] = $resourceProperty;
    		}
    		else if ( $ChildNode->nodeName == 'resourceDescriptor' ) {
    			array_push( $subResources, $this->readResourceDescriptor($ChildNode));
    		}
    		else if ( $ChildNode->nodeName == 'parameter' ) {
    			$parameters[ $ChildNode->getAttributeNode("name")->value ] =  $ChildNode->nodeValue;
    		}
    	}
    
    	$resourceDescriptor['properties'] = $resourceProperties;
    	$resourceDescriptor['resources']  = $subResources;
    	$resourceDescriptor['parameters'] = $parameters;
    
    
    	return $resourceDescriptor;
    }
    
    function addReadResourceProperty($node) {
    	$resourceProperty = array();
    
    	$resourceProperty['name'] = $node->getAttributeNode("name")->value;
    
    	$resourceProperties = array();
    
    	// Read subelements...
    	foreach( $node->childNodes AS $ChildNode ) {
    		if ( $ChildNode->nodeName == 'value' ) {
    			$resourceProperty['value'] = $ChildNode->nodeValue;
    		}
    		else if ( $ChildNode->nodeName == 'resourceProperty' ) {
    			//$resourceDescriptor['resourceProperty'] = $ChildChildNode->nodeValue;
    			// read properties...
    			array_push( $resourceProperties, $this->addReadResourceProperty($ChildNode ) );
    		}
    	}
    
    	$resourceProperty['properties'] = $resourceProperties;
    
    	return $resourceProperty;
    }

}
