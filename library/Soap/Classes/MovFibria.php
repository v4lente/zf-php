<?php

ini_set("soap.wsdl_cache_enabled", 0);
ini_set('soap.wsdl_cache_ttl', 0);
ini_set('max_execution_time', 180);
ini_set('memory_limit', '128M');

// Carrega a(s) classe(s) que sera(ao) utilizada(s) via soap
require_once LIBRARY_PATH . '/Soap/Classes/SoapAux.php';
require_once LIBRARY_PATH . '/Soap/Classes/Array2XML.php';
require_once LIBRARY_PATH . '/Soap/Classes/AuthUser.php';
require_once LIBRARY_PATH . '/Soap/Classes/Response.php';
require_once LIBRARY_PATH . '/Soap/Classes/RequestFibria.php';

/**
 *
 * Classe responsavel por importar para o banco de dados o arquivo XML enviado pela empresa Fibria com os dados da pesagem dos caminhões
 * que estão transportando as toras de madeira para dentro do Porto do Rio Grande.
 * 
 * @category   Marca Sistemas
 * @package    MovFibria
 * @copyright  Copyright (c) 1991-2015 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 * @version    $Id: MovTecon.php 0 2015-02-10 13:00:00 marcio $
 */
class MovFibria {
    
	/**
     *
	 * Metodo responsavel por enviar os dados da pesagem.
	 *
     * @param  Request  $request
	 * @param  AuthUser $auth 
	 *
     * @return Response|SoapFault
     */
    public function enviaPesagem($request, $auth) {
		
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
			
			//if($_SERVER['PHP_AUTH_USER'] != "fibria" || $_SERVER['PHP_AUTH_PW'] != "adm123") {
			//	throw new SoapFault("Server", utf8_encode("Operação abortada pois um erro foi encontrado"), null, array("Login" => utf8_encode("Usuário ou Senha inválida do Header")), "FaultSpecified");
			//}
			
			// Instancia a classe auxiliar para conexão e tratamento do xml
            $aux = new SoapAux();
			
			// Autentica no banco o usuário
			if(! $aux->authenticate($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
				throw new SoapFault("Server", utf8_encode("Operação abortada pois um erro foi encontrado"), null, array("Login" => utf8_encode("Usuário ou Senha inválida")), "FaultSpecified");
			} // ##############################################################################################################################################################################
			
			// Converte o objeto xml para json e posteriormente para Array
			$xmlArray = json_decode(json_encode($request), true);
			
			// Converte o array para XML
			$xml = Array2XML::createXML('root', $xmlArray);
			try {
				$request = $xml->saveXML();
			} catch(Zend_Exception $e) {
				throw new SoapFault("Server", utf8_encode("Operação abortada pois um erro foi encontrado"), null, array("XML" => utf8_encode("XML inválido")), "FaultSpecified");
			} // ###############################################################################################################################################################
			
			// Carrega o documento para validação do xml
			$dom = new DOMDocument('1.1', 'utf-8');
			
			try {
				$loadXML = $dom->loadXML($request, LIBXML_NOBLANKS);
			} catch(Zend_Exception $e) {
				throw new SoapFault("Server", utf8_encode("Operação abortada pois um erro foi encontrado"), null, array("XML" => utf8_encode("Não foi possível ler o XML")), "FaultSpecified");
			} // #############################################################################################################################################################################
			
			// Carrega o XSD para validação do XML enviado pelo Tecon
			if(is_file('./soap/fibria/pesagem.xsd')) {
				
				libxml_use_internal_errors(true);
				
				// Valida as tags xml
				try {
					$validaXML = $aux->validateXML($request);
				} catch(Zend_Exception $e) {
					throw new SoapFault("Server", utf8_encode("Operação abortada pois um erro foi encontrado"), null, array("XML" => utf8_encode("Erro ao validar XML")), "FaultSpecified");
				} // ######################################################################################################################################################################
			
                // Valida o esquema xml a partir do xsd
                if(! $dom->schemaValidate('./soap/fibria/pesagem.xsd')) {
					throw new SoapFault("Server", utf8_encode("Operação abortada pois um erro foi encontrado"), null, array("XML" => utf8_encode("Esquema do XML Inválido")), "FaultSpecified");
                } // ##########################################################################################################################################################################
                
                // Lê a requisição xml e transforma os dados em um array
				$xmlDoc = simplexml_load_string($request);
				
				$arrayData = $aux->xmlToArray($xmlDoc);
				
				// Chama o método que irá importar os dados do tecon
				$importacao = $this->importaDadosFibria($arrayData["root"], $_SERVER['PHP_AUTH_USER']);
                
				// Se for diferente de verdadeiro, retorna o erro para o cliente
				if($importacao === 0) {
					throw new SoapFault($importacao->code, $importacao->message, null, $importacao->detail, "FaultSpecified");
				} // ########################################################################################################
				
			} else {
				throw new SoapFault("Server", utf8_encode("Operação abortada pois um erro foi encontrado"), null, array("XSD" => utf8_encode("Não foi possível abrir o arquivo XSD")), "FaultSpecified");
			} // #######################################################################################################################################################################################
            
			// Se ocorreu tudo bem, grava o arquivo no diretório de uploads
			$caArquivo = './uploads/fibria/';
			$noArquivo = 'PESAGEM_' . $importacao . '.xml';
			$dom->save($caArquivo . $noArquivo);
			if(! is_file($caArquivo . $noArquivo)) {
				throw new SoapFault("Server", utf8_encode("Operação concluída, mas um erro foi encontrado"), null, array("TXT" => utf8_encode("Dados importados, mas não foi possível salvar o arquivo xml no seridor.")), "FaultSpecified");
			} // #######################################################################################################################################################################################################
			
            // Retorna a mensagem de sucesso
			$response->code        = "1";
			$response->title       = "Sucesso";
			$response->description = "Dados lidos, validados e importados com sucesso!";
			
			return $response;
            
        } catch(SoapFault $fault) {
            
            // Retorna o erro
			$response->code        = "0";
			$response->title       = $fault->faultstring;
			$response->description = $fault->detail;
			
			return $response;
		}
		
		// Desautentica após executar a operação
		$aux->closeConnection();
		
    }
	
	/**
     *
	 * Metodo responsavel por importar os dados da pesagem enviados pela empresa Fibria.
	 *
     * @param  Array  $pesagem
	 * @param  String $cd_usuario
     * @return Boolean
     */
	private function importaDadosFibria($pesagem, $cd_usuario) {
		
		try {
									
			// Captura a conexão com o banco de dados
			$db = Zend_Registry::get('db');
			
			// Inicializa a transação
			$db->beginTransaction();
			
			// Dados da pesagem
			$nr_ticket      	= $pesagem["nr_ticket"];
			$pl_veiculo     	= $pesagem["pl_veiculo"];
			$dthr_peso_tara		= date_format(new DateTime($pesagem["dthr_peso_tara"]), "d/m/Y H:i");
			$dthr_peso_bruto	= date_format(new DateTime($pesagem["dthr_peso_bruto"]), "d/m/Y H:i");
			$peso_tara      	= $pesagem["peso_tara"];
			$peso_bruto     	= $pesagem["peso_bruto"];
			
			// Variáveis padrão
			$tp_operacao 	= "F2";
			$cd_merc_emb 	= "1407";
			$cd_cliente_emb = "31891";
			$cd_usr_peso_1  = "FIBRIA";
			$cd_usr_peso_2  = "FIBRIA";
			$observacao  	= utf8_encode("IMPORTAÇÃO DOS DADOS DA PESAGEM DOS CAMINHÕES NO PÁTIO GOTA - Ticket: " . $nr_ticket);
			
			// Busca os dados do fundeio
			$select = $db->select()
						 ->from("V_VEICULOS_AUTORIZADOS", array("NR_FUNDEIO" => new Zend_Db_Expr("MAX(NR_FUNDEIO)")))
						 ->where("TRIM(PL_VEICULO)  = '" . trim($pl_veiculo) . "'")
						 ->where("CD_TP_OPERACAO = 'ED'");
			
			$res = $db->fetchRow($select);
			
			$nr_fundeio = (int) $res->NR_FUNDEIO;
			
			// Confirma a variável
			if($nr_fundeio == "") {
				$erros[] = utf8_encode("Número de fundeio não encontrado para a placa {$pl_veiculo}.");
			}
			
			// Valida se já não foi inserido esta pesagem
			$select = $db->select()
						 ->from("PES_PESAGEM", array("TOTAL" => new Zend_Db_Expr("COUNT(*)")))
						 ->where("DTHR_PESAGEM1  = '" . $dthr_peso_tara  . "'")
						 ->where("DTHR_PESAGEM2  = '" . $dthr_peso_bruto . "'");
			
			$res = $db->fetchRow($select);
			
			$total = (int) $res->TOTAL;
			
			// Confirma a variável
			if($total > 0) {
				$erros[] = utf8_encode("Pesagem já inserida no sistema.");
			}
			
			//Gera Sequencial para Pes_pesagem
			$select = $db->select()->from("PES_PESAGEM", array("SEQ_PESAGEM" => new Zend_Db_Expr("MAX(SEQ_PESAGEM) + 1")));
			
			$res = $db->fetchRow($select);
			
			$seq_pesagem = (int) $res->SEQ_PESAGEM;
			
			// Confirma a variável
			if($nr_fundeio == "") {
				$erros[] = utf8_encode("Número sequencial da pesagem não gerada pois não foi encontrado o número de fundeio.");
			}
			
			$dados = array("SEQ_PESAGEM"   	 => $seq_pesagem,
						   "CD_USR_PESO1" 	 => $cd_usuario,
						   "CD_USR_PESO2" 	 => $cd_usuario,
						   "PESO1_BALANCA"   => $peso1_balanca,
						   "PESO1_SISTEMA"   => $peso1_sistema,
						   "DTHR_PESAGEM1"   => $dthr_pesagem1,
						   "TP_OPERACAO"     => $tp_operacao,
						   "CD_MERC_EMB"     => $cd_merc_emb,
						   "CD_CLIENTE_EMB"  => $cd_cliente_emb,
						   //"NR_TICKET"     => $nr_ticket,
						   "PL_VEICULO"    	 => $pl_veiculo,
						   "DTHR_PESAGEM1"   => $dthr_peso_tara,
						   "DTHR_PESAGEM2"   => $dthr_peso_bruto,
						   "PESO1_BALANCA"   => $peso_tara,
						   "PESO1_SISTEMA"   => $peso_tara,
						   "PESO2_BALANCA"   => $peso_bruto,
						   "PESO2_SISTEMA"   => $peso_bruto,
						   "NR_FUNDEIO"    	 => $nr_fundeio,
						   "OBSERVACAO"		 => utf8_decode($observacao));
			
			// Insere o cliente nas tabelas abaixo
			$db->insert("PES_PESAGEM", $dados);
			
			// Se houve erro no bloco superior, dispara a mensagem
			if(count($erros) > 0) {
				throw new SoapFault("Server", utf8_encode("Operação abortada pois um erro foi encontrado"), null, $erros, "FaultSpecified");
			} // ##########################################################################################################################
			
			// Se tudo ocorreu corretamente, Executa todo o processo do banco
			$db->commit();
			
			// Se ocorreu tudo bem, retorna o sequencial da pesagem
			return $seq_pesagem;
			
		} catch(Zend_Exception $e) {
            
            // Volta tudo que foi feito no banco
            $db->rollBack();
		
            // Retorna o erro
			return 0;
		}
		
	}
    
}
?>
