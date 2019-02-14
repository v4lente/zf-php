<?php

ini_set("soap.wsdl_cache_enabled", 0);
ini_set('soap.wsdl_cache_ttl', 0);
ini_set('max_execution_time', 360);
ini_set('memory_limit', '64M');

// Carrega a(s) classe(s) que sera(ao) utilizada(s) via soap
require_once LIBRARY_PATH . '/Soap/Classes/SoapAux.php';
require_once LIBRARY_PATH . '/Soap/Classes/Array2XML.php';
require_once LIBRARY_PATH . '/Soap/Classes/AuthUser.php';
require_once LIBRARY_PATH . '/Soap/Classes/Response.php';
require_once LIBRARY_PATH . '/Soap/Classes/RequestTecon.php';

/**
 *
 * Classe responsavel por importar para o banco de dados o arquivo XML enviado pelo Tecon via SOAP.
 * 
 * @category   Marca Sistemas
 * @package    MovTecon
 * @copyright  Copyright (c) 1991-2015 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 * @version    $Id: MovTecon.php 0 2015-02-10 13:00:00 marcio $
 */
class MovTecon {
    
	/**
     *
	 * Metodo responsavel por listar os dados da movimentacao portuaria.
	 *
     * @param  Integer  $nrIdTecon
	 * @param  AuthUser $auth 
	 *
     * @return Response|SoapFault
     */
    public function listaMovPortuaria($nrIdTecon, $auth) {
		
		try {
			
			// Instancia a classe de retorno
			$response = new Response();
			
			// Converte o objeto xml para json e posteriormente para Array
			$authArray = json_decode(json_encode($auth), true);
			
			// Para poder receber o usuário e senha de outro cliente que não seja php
			// foi adicionado o usuário e senha diretamente na função.
			$_SERVER['PHP_AUTH_USER'] = $authArray["user"];
			$_SERVER['PHP_AUTH_PW']   = $authArray["password"];
			
			if(!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
				throw new SoapFault("Server", utf8_encode("Operação abortada pois um erro foi encontrado"), null, array("Login" => utf8_encode("Usuário ou Senha não enviados no Header")), "FaultSpecified");
			}
			
			// Instancia a classe auxiliar para conexão e tratamento do xml
            $aux = new SoapAux();
			
			// Inicializa o array de erros
			$erros = array();
			
			// Autentica no banco o usuário
			if(! $aux->authenticate($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
				throw new SoapFault("Server", utf8_encode("Operação abortada pois um erro foi encontrado"), null, array("Login" => utf8_encode("Usuário ou Senha inválida")), "FaultSpecified");
			} // ##############################################################################################################################################################################
			
			// Captura a conexão com o banco de dados
			$db = Zend_Registry::get('db');
			
			// Inicializa a transação
			$db->beginTransaction();
			
			$select = $db->select()
						 ->from("MOV_TECON",  array("NR_ID_TECON", 
													"TP_OPERACAO", 
													"TP_CARGA", 
													"TP_VIAGEM", 
													"ANO_PED_ATRAC", 
													"NR_PED_ATRAC", 
													"NR_FUNDEIO", 
													"NO_EMBARC", 
													"LLOYDS_EMBARC", 
													"CD_EMBARC", 
												    "TO_DATE(TO_CHAR(DTHR_ATRACACAO,     'DD/MM/YYYY HH24:MI:SS'), 'DD/MM/YYYY HH24:MI:SS') AS DTHR_ATRACACAO", 
													"TO_DATE(TO_CHAR(DTHR_OPERACAO,      'DD/MM/YYYY HH24:MI:SS'), 'DD/MM/YYYY HH24:MI:SS') AS DTHR_OPERACAO", 
													"TO_DATE(TO_CHAR(DTHR_ENTRADA_SAIDA, 'DD/MM/YYYY HH24:MI:SS'), 'DD/MM/YYYY HH24:MI:SS') AS DTHR_ENTRADA_SAIDA", 
													"CNPJ_AGENTE", 
													"NO_AGENTE", 
													"CD_CLIENTE_AGENTE", 
													"CNPJ_ARMADOR", 
													"NO_ARMADOR", 
													"CD_CLIENTE_ARMADOR", 
													"CNPJ_IMP_EXP", 
													"NO_IMP_EXP", 
													"CD_CLIENTE_IMP_EXP", 
													"CD_PORTO_OD_MERC", 
													"CD_PORTO_OD", 
													"NO_MUN_OD_MERC", 
													"UF_MUN_OD_MERC", 
													"CD_MERC_TECON", 
													"NO_MERC_TECON", 
													"CD_MERC", 
													"NO_UM", 
													"PESO_BRUTO", 
													"PESO_LIQUIDO", 
													"TARA", 
													"VL_RECEITA_CAMBIAL", 
													"FL_CARGA_PERIGOSA", 
													"CD_CONTAINER", 
													"TP_CONTAINER", 
													"DS_TP_CONTAINER", 
													"FL_REFRIGERADO", 
													"ISO_CONTAINER", 
													"TAM_CONTAINER", 
													"NO_ARQ_TECON", 
													"FL_TIPO_REG", 
													"CD_USUARIO", 
													"CD_ESTACAO", 
													"CD_NCM", 
													"TP_TRANSBORDO", 
													"TP_ROTACAO", 
													"NR_BERCO", 
													"QT_MERC", 
													"CE_MERCANTE", 
													"NR_TEUS", 
													"TRIG_PORTO_OI_DF", 
													"BIGR_PORTO_OI_DF", 
													"OP_TRANSITO"))
						 ->where("NR_ID_TECON  = " . $nrIdTecon);
			
			// Executa a query
			$res = $db->fetchRow($select);
			
			// Inicializa o array de dados
			$dados     = array();
			$descricao = utf8_encode("Dados lidos com sucesso!");
						
			if(count($res) == 0 && $res->NR_ID_TECON != "") {
				$erros["tp_viagem"] = utf8_encode("Nr/Ano PDA: " . $nr_ped_atrac . "/" . $ano_ped_atrac . " - Os tipos de viagens aceitos são: LC=Longo Curso, CB=Cabotagem, NI=Navegação Interior");
			} else {
				
				if(isset($res->NR_ID_TECON) && $res->NR_ID_TECON != "") {
					
					$dados = array("NR_ID_TECON" 		=> $res->NR_ID_TECON, 
								   "TP_OPERACAO" 		=> $res->TP_OPERACAO, 
								   "TP_CARGA" 			=> $res->TP_CARGA, 
								   "TP_VIAGEM" 			=> $res->TP_VIAGEM, 
								   "ANO_PED_ATRAC" 		=> $res->ANO_PED_ATRAC, 
								   "NR_PED_ATRAC" 		=> $res->NR_PED_ATRAC, 
								   "NR_FUNDEIO" 		=> $res->NR_FUNDEIO, 
								   "NO_EMBARC" 			=> $res->NO_EMBARC, 
								   "LLOYDS_EMBARC" 		=> $res->LLOYDS_EMBARC, 
								   "CD_EMBARC" 			=> $res->CD_EMBARC, 
								   "DTHR_ATRACACAO" 	=> $res->DTHR_ATRACACAO, 
								   "DTHR_OPERACAO" 		=> $res->DTHR_OPERACAO, 
								   "DTHR_ENTRADA_SAIDA" => $res->DTHR_ENTRADA_SAIDA, 
								   "CNPJ_AGENTE" 		=> $res->CNPJ_AGENTE, 
								   "NO_AGENTE" 			=> $res->NO_AGENTE, 
								   "CD_CLIENTE_AGENTE" 	=> $res->CD_CLIENTE_AGENTE, 
								   "CNPJ_ARMADOR" 		=> $res->CNPJ_ARMADOR, 
								   "NO_ARMADOR" 		=> $res->NO_ARMADOR, 
								   "CD_CLIENTE_ARMADOR" => $res->CD_CLIENTE_ARMADOR, 
								   "CNPJ_IMP_EXP" 		=> $res->CNPJ_IMP_EXP, 
								   "NO_IMP_EXP" 		=> $res->NO_IMP_EXP, 
								   "CD_CLIENTE_IMP_EXP" => $res->CD_CLIENTE_IMP_EXP, 
								   "CD_PORTO_OD_MERC" 	=> $res->CD_PORTO_OD_MERC, 
								   "CD_PORTO_OD"	 	=> $res->CD_PORTO_OD, 
								   "NO_MUN_OD_MERC" 	=> $res->NO_MUN_OD_MERC, 
								   "UF_MUN_OD_MERC" 	=> $res->UF_MUN_OD_MERC, 
								   "CD_MERC_TECON" 		=> $res->CD_MERC_TECON, 
								   "NO_MERC_TECON" 		=> $res->NO_MERC_TECON, 
								   "CD_MERC" 			=> $res->CD_MERC, 
								   "NO_UM" 				=> $res->NO_UM, 
								   "PESO_BRUTO" 		=> $res->PESO_BRUTO, 
								   "PESO_LIQUIDO" 		=> $res->PESO_LIQUIDO, 
								   "TARA"			 	=> $res->TARA, 
								   "VL_RECEITA_CAMBIAL" => $res->VL_RECEITA_CAMBIAL, 
								   "FL_CARGA_PERIGOSA"  => $res->FL_CARGA_PERIGOSA, 
								   "CD_CONTAINER"		=> $res->CD_CONTAINER, 
								   "TP_CONTAINER" 		=> $res->TP_CONTAINER, 
								   "DS_TP_CONTAINER" 	=> $res->DS_TP_CONTAINER, 
								   "FL_REFRIGERADO" 	=> $res->FL_REFRIGERADO, 
								   "ISO_CONTAINER" 		=> $res->ISO_CONTAINER, 
								   "TAM_CONTAINER" 		=> $res->TAM_CONTAINER, 
								   "NO_ARQ_TECON" 		=> $res->NO_ARQ_TECON, 
								   "FL_TIPO_REG" 		=> $res->FL_TIPO_REG, 
								   "CD_USUARIO" 		=> $res->CD_USUARIO, 
								   "CD_ESTACAO" 		=> $res->CD_ESTACAO, 
								   "CD_NCM" 			=> $res->CD_NCM, 
								   "TP_TRANSBORDO" 		=> $res->TP_TRANSBORDO, 
								   "TP_ROTACAO"		 	=> $res->TP_ROTACAO, 
								   "NR_BERCO" 			=> $res->NR_BERCO, 
								   "QT_MERC" 			=> $res->QT_MERC, 
								   "CE_MERCANTE" 		=> $res->CE_MERCANTE, 
								   "NR_TEUS" 			=> $res->NR_TEUS, 
								   "TRIG_PORTO_OI_DF" 	=> $res->TRIG_PORTO_OI_DF, 
								   "BIGR_PORTO_OI_DF" 	=> $res->BIGR_PORTO_OI_DF, 
								   "OP_TRANSITO" 		=> $res->OP_TRANSITO);
					
				} else {
					$descricao = utf8_encode("Nenhum registro encontrado");
				}
				
			}
			
			// Se houve erro no bloco superior, dispara a mensagem
			if(count($erros) > 0) {
				throw new SoapFault("Server", utf8_encode("Operação abortada pois um erro foi encontrado"), null, $erros, "FaultSpecified");
			} // ##########################################################################################################################
			
			// Se tudo ocorreu corretamente, Executa todo o processo do banco
			$db->commit();
			
			// Retorna a mensagem de sucesso
			$response->code        = "1";
			$response->title       = "Sucesso";
			$response->description = $descricao;
			$response->data        = $dados;
			
			return $response;
						
		} catch(SoapFault $fault) {
            
			// Volta tudo que foi feito no banco
            $db->rollBack();
			
            // Retorna o erro
			$response->code        = "0";
			$response->title       = $fault->faultstring;
			$response->description = $fault->detail;
			$response->data        = array();
			
			return $response;
		}
		
		// Desautentica após executar a operação
		$aux->closeConnection();
		
	}
	
	/**
     *
	 * Metodo responsavel por enviar os dados da movimentacao portuaria.
	 *
     * @param  Request  $request
	 * @param  AuthUser $auth 
	 *
     * @return Response|SoapFault
     */
    public function enviaMovPortuaria($request, $auth) {
		
		try {
            
			// Instancia a classe de retorno
			$response = new Response();
            
			// Converte o objeto xml para json e posteriormente para Array
			$authArray = json_decode(json_encode($auth), true);
			
			// Para poder receber o usuário e senha de outro cliente que não seja php
			// foi adicionado o usuário e senha diretamente na função.
			$_SERVER['PHP_AUTH_USER'] = $authArray["user"];
			$_SERVER['PHP_AUTH_PW']   = $authArray["password"];
			
			$erro = 0;
			
			if(!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
				$erro = 1;
				throw new SoapFault("Server", utf8_encode("Operação abortada pois um erro foi encontrado"), null, array("Login" => utf8_encode("Usuário ou Senha não enviados no Header")), "FaultSpecified");
				
			}
						
			// Instancia a classe auxiliar para conexão e tratamento do xml
            $aux = new SoapAux();
			
			// Autentica no banco o usuário
			if(! $aux->authenticate($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
				$erro = 1;
				throw new SoapFault("Server", utf8_encode("Operação abortada pois um erro foi encontrado"), null, array("Login" => utf8_encode("Usuário ou Senha inválida")), "FaultSpecified");
				
			} // ##############################################################################################################################################################################
			
			try {
                // Converte o objeto xml para json e posteriormente para Array
                $xmlArray = json_decode(json_encode($request), true);
                
                // Converte o array para XML
                $xml = Array2XML::createXML('root', $xmlArray);
                
				$request = $xml->saveXML();
			} catch(Zend_Exception $e) {
				$erro = 1;
				throw new SoapFault("Server", utf8_encode("Operação abortada pois um erro foi encontrado"), null, array("XML" => utf8_encode("XML inválido. Exception: " . $e->getMessage())), "FaultSpecified");
			} // ###############################################################################################################################################################
			
			// Carrega o documento para validação do xml
			$dom = new DOMDocument('1.1', 'utf-8');
			
			try {
				$loadXML = $dom->loadXML($request, LIBXML_NOBLANKS);
			} catch(Zend_Exception $e) {
				$erro = 1;
				throw new SoapFault("Server", utf8_encode("Operação abortada pois um erro foi encontrado"), null, array("XML" => utf8_encode("Não foi possível ler o XML. Exception: " . $e->getMessage())), "FaultSpecified");
			} // #############################################################################################################################################################################
			
			// Carrega o XSD para validação do XML enviado pelo Tecon
			if(is_file('./soap/tecon/scol.xsd')) {
				
				libxml_use_internal_errors(true);
				
				// Valida as tags xml
				try {
					$validaXML = $aux->validateXML($request);				
				} catch(Zend_Exception $e) {
					$erro = 1;
					throw new SoapFault("Server", utf8_encode("Operação abortada pois um erro foi encontrado"), null, array("XML" => utf8_encode("Erro ao validar XML")), "FaultSpecified");
				} // ######################################################################################################################################################################
			
                // Valida o esquema xml a partir do xsd
                if(! $dom->schemaValidate('./soap/tecon/scol.xsd')) {
                    $errors = libxml_get_errors();
                    $eschemeError = "";
                    foreach($errors as $error) {
                        $eschemeError .= "\n Mensagem: " . $error->message;// ." | Nível: ". $error->level ." | Código: ". $error->code ." | Arquivo: ". $error->file ." | Linha: ". $error->line ." | Coluna: ". $error->column;
                    }
					$erro = 1;
					throw new SoapFault("Server", utf8_encode("Operação abortada pois um erro foi encontrado"), null, array("XML" => utf8_encode("Esquema do XML Inválido: " . $eschemeError)), "FaultSpecified");
                } // ##########################################################################################################################################################################
                
                // Lê a requisição xml e transforma os dados em um array
				$xmlDoc = simplexml_load_string($request);
				
				$arrayData = $aux->xmlToArray($xmlDoc);
				
				// Chama o método que irá importar os dados do tecon
				//$importacao = $this->importaDadosTecon($arrayData["root"]["document"]["fundeios"], $login);
                
                // Chama o método que irá consolidar os dados do tecon
				//$consolidacao = $this->consolidaDadosTecon($arrayData["root"]["document"]["fundeios"]);
                
                if(is_soap_fault($importacao)) {
					$erro = 1;
                    throw new SoapFault("Server", utf8_encode("Operação abortada pois um erro foi encontrado"), null, $importacao, "FaultSpecified");
                } // ####################################################################################################
				
			} else {
				$erro = 1;
				throw new SoapFault("Server", utf8_encode("Operação abortada pois um erro foi encontrado"), null, array("XSD" => utf8_encode("Não foi possível abrir o arquivo XSD")), "FaultSpecified");
			} // #######################################################################################################################################################################################
            
			// Remove os dados de credenciais
			$domDocument = $dom->documentElement;
			foreach($domDocument->getElementsByTagName('credential') as $domElement) {
				$domElement->parentNode->removeChild($domElement);
			}
			
			// Se ocorreu tudo bem, grava o arquivo no diretório de uploads
			$caArquivo = './uploads/tecon/';
			$noArquivo = 'SCOL_' .date('Y_m_d_H_i_s'). '.xml';
			$dom->save($caArquivo . $noArquivo);
			if(! is_file($caArquivo . $noArquivo)) {
				$erro = 1;
				throw new SoapFault("Server", utf8_encode("Operação concluída, mas um erro foi encontrado"), null, array("TXT" => utf8_encode("Erro ao salvar na pasta o arquivo de conclusão da importação")), "FaultSpecified");
			} // #######################################################################################################################################################################################################
            
			if($erro == 0){
                
                // Da permissão de escrita no arquivo
                shell_exec("nohup chmod 777 " . $caArquivo . $noArquivo . " > /dev/null &");
                
				try {
					// Envia o email ao tecon e ao setor de estatística
					$mail = new Marca_Mail();
					$mail->setFrom("estatistica@portoriogrande.com.br", "Porto do Rio Grande");
					//	Alterar  o email para o responsável do tecon
					//$mail->addTo("mateus.santos@gmail.com"); //@@@@@@@@@@@@@ TESTE @@@@@@@@@@@@@
					//$mail->addTo("estatistica@portoriogrande.com.br");
					//$mail->addTo("dumont@portoriogrande.com.br"); // Atual fiscal do TECON
					$mail->setSubject("Movimentação TECON - " . $noArquivo);
					$mail->setBodyHtml("<br />Importação realizada com sucesso. Arquivo: " . $noArquivo);
					$mail->send();
					
				} catch(Marca_Mail_Exception $e) {
					throw new SoapFault("Server", utf8_encode("Operação concluída, mas um erro foi encontrado"), null, array("EMAIL" => utf8_encode("Erro ao enviar email de conclusão da importação")), "FaultSpecified");
				} // #####################################################################################################################################################################################################
				
				
				// Retorna a mensagem de sucesso
				$response->code        = "1";
				$response->title       = "Sucesso";
				$response->description = utf8_encode($importacao[0] . " registros lidos e importados com sucesso, aguarde e-mail com a validação dos dados.");
				$response->data        = array();
				
			}else{
				$response->code        = "0";
				$response->title       = "Erro";
				$response->description = utf8_encode("Nenhum registro foi importado!");
				$response->data        = array();		
			}
			
            // Retorna os dados
			return $response;
			
        } catch(SoapFault $fault) {
            
            // Retorna o erro
			$response->code        = "0";
			$response->title       = $fault->faultstring;
			$response->description = $fault->detail;
			$response->data        = array();
			
			return $response;
		}
		
		// Desautentica após executar a operação
		$aux->closeConnection();
    }
	
	
}
?>
