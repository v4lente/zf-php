<?php

/*
// Turn off all error reporting
error_reporting(0);

// Report simple running errors
error_reporting(E_ERROR | E_WARNING | E_PARSE);

// Reporting E_NOTICE can be good too (to report uninitialized
// variables or catch variable name misspellings ...)
error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

// Report all errors except E_NOTICE
// This is the default value set in php.ini
error_reporting(E_ALL ^ E_NOTICE);

// Report all PHP errors
error_reporting(E_ALL);

// Same as error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);

ini_set("display_errors", 1);
*/

ini_set("soap.wsdl_cache_enabled", "0");

// Carrega a(s) classe(s) que ser·(„o) utilizada(s) via soap
require_once LIBRARY_PATH . '/Soap/Classes/AuthUser.php';
require_once LIBRARY_PATH . '/Soap/Classes/Response.php';
require_once LIBRARY_PATH . '/Soap/Classes/MovTecon.php';
require_once LIBRARY_PATH . '/Soap/Classes/Array2XML.php';

/**
 * Classe ServiceTeconController
 *
 * Com o intuito de remodelar o Sistema de EstatÌstica da SUPRG, este È o novo 
 * formato de arquivo que ser· em XML para o envio de informaÁıes via Web Service, 
 * tendo como objetivo listar as informaÁıes de movimentaÁ„o operacionais realizadas 
 * pelo TECON.
 * 
 * @category   Marca Sistemas
 * @package    ServiceTeconController
 * @copyright  Copyright (c) 1991-2014 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 * @version    $Id: ServiceTeconController.php 0 2014-01-13 09:15:00 marcio $
 */
class ServiceTeconController extends Zend_Controller_Action {

    // Armazena na vari·vel o endereÁo do webserver no servidor
    private $_URI = '';
    
    /**
     * MÈtodo inicial ao qual carrega o wsdl do servidor.
     *
     * @return void
     */
    public function init() {
        
        // Captura a sess„o
		//$sessao = new Zend_Session_Namespace('portoweb');
        
        // Carrega as classes de autenticaÁ„o
        Zend_Loader::loadClass('Marca_Auth_Adapter_Autentica');
        Zend_Loader::loadClass('UsuarioModel');
        
        // Define o WSDL
        $site = 'http://' . $_SERVER['SERVER_NAME'] . $this->view->baseUrl();
        Zend_Registry::set('site', $site);
        
        // Seta o local da wsdl
        $this->_URI = $site . '/default/service-tecon';

        // Desabilita o layout padr„o
        $this->_helper->layout->disableLayout();
        
    }
    
    /**
     * MÈtodo principal da classe ao qual define o que 
     * ser· carregado pelo lado do cliente.
     *
     * @return void
     */
    public function indexAction() {
        
        try {
            
            if(APPLICATION_ENV == "producao") {
                die("ServiÁo temporariamente desativado!");
            }

            $params = $this->getRequest()->getParams();

            if (isset($params['wsdl'])) {
                 // Desabilita a renderizaÁ„o da view
                $this->_helper->viewRenderer->setNoRender();

                //return the WSDL
                $this->handleWSDL();

            } else {
                 // Desabilita a renderizaÁ„o da view
                $this->_helper->viewRenderer->setNoRender();

                //handle SOAP request
                $this->handleSOAP();
            }
            
        } catch(Exception $e) {
            echo "Erro: " . $e->getMessage();
        }
        
    }

    /**
     * MÈtodo ao qual mostra a estrutura da classe a ser manipulada.
     *
     * @return void
     */
    private function handleWSDL() {
        
        try {
            
            $strategy = 'Zend_Soap_Wsdl_Strategy_ArrayOfTypeComplex';
            $autodiscover = new Zend_Soap_AutoDiscover($strategy);
            //$site = Zend_Registry::get('site');
            //$autodiscover = new SoapServer($site . "/public/soap/tecon/scol.wsdl", array('soap_version' => SOAP_1_2));
            $autodiscover->setClass('MovTecon');
            //$autodiscover->setUri($this->_URI);
            $autodiscover->handle();
            
        } catch(Exception $e) {
            echo "Erro: " . $e->getMessage();
        }
    }

    /**
     * MÈtodo que permite manipular as classes criadas para a transferÍncia
     * de arquivos entre o Tecon e o Porto de Rio Grande.
     *
     * @return void
     */
    private function handleSOAP() {
        
        try {
            
            $options = array('uri' => $this->_URI, 'soap_version' => SOAP_1_2);
            $soap = new Zend_Soap_Server(null, $options);
            $soap->setClass('MovTecon');
            $soap->handle();
            
        } catch(Exception $e) {
            echo "Erro: " . $e->getMessage();
        }
    }
    
    /**
     * MÈtodo respons·vel por consolidar os dados do tecon.
     *
     * @return void
     */
    public function consolidaDadosTeconAction() {
        
        try {
            
            if(APPLICATION_ENV == "producao") {
                die("ServiÁo temporariamente desativado!");
            }

            $params = $this->getRequest()->getParams();
            
            $site = Zend_Registry::get('site');
            $diretorio = "./uploads/tecon/";
            $arquivos = scandir($diretorio, 1);
            $arquivo_leitura = "";
            $leu_arquivo = 0;
            foreach ($arquivos as $arquivo) {
                if (is_file($diretorio . $arquivo)) {
                    $arquivo_leitura = $diretorio . $arquivo;
                    $leu_arquivo = 1;
                    break;
                }
            }
            
            // Se n„o tem arquivo para leitura
            if($leu_arquivo == 0) {
                die("Nenhum arquivo para leitura");
            }
            
            if ($arquivo_leitura != "") {
                $aux = new SoapAux();
                // LÍ a requisiÁ„o xml e transforma os dados em um array
                $xmlDoc = simplexml_load_file($arquivo_leitura);
                $arrayData = $aux->xmlToArray($xmlDoc);
                
            } else {
                $arquivo_erros = './uploads/tecon/criticas/log_erros.txt';
                $fp = fopen($arquivo_erros, 'a');
                $conteudo = "erro na leitura do arquivo:" . $arquivo;
                fwrite($fp, $conteudo);
                fclose($fp);
                rename('./uploads/tecon/' . $arquivo, './uploads/tecon/criticas/' . $arquivo);
                die("erro na leitura do arquivo");
            }
            
            // Autentica no banco o usu·rio
			if(! $aux->authenticate($params["u"], $params["s"])) {
                $erros[] = utf8_encode("OperaÁ„o abortada pois um erro foi encontrado: Usu·rio ou Senha inv·lida");
			}
            
            // Captura a conex„o com o banco de dados
            $db = Zend_Registry::get('db');

            // Inicializa a transaÁ„o
            $db->beginTransaction();

            // Inicia o array de retorno de erro
            $faultError = array();

            // Vari·veis padr„o
            $no_arq_tecon = "WEB_SERVICE";
            $fl_tipo_reg = "I";
            $cd_estacao = "TECON";

            // Inicia a vari·vel
            $totalInsert = 0;

            // Continua o processo posterior aos testes de autenticaÁ„o pelo esquema do xml.
            // Esta verificaÁ„o abaixo È necess·ria, pois caso o array de fundeio n„o possuir
            // mais de um indice, ele percorre entre os par√¢metros do fundeio e n„o entre o 
            // array de fundeios, que È o correto.
            // $fundeios = isset($fundeios["fundeio"][1]) ? $fundeios["fundeio"] : $fundeios;
            $fundeios = $arrayData['root']['document']["fundeios"];
            
            foreach ($fundeios as $fundeio) {
                
                // Incializa a vari·vel de erro
                $erros = array();

                // Dados do Fundeio
                $tp_viagem      = trim($fundeio["tp_viagem"]);
                $nr_ped_atrac   = $fundeio["nr_ped_atrac"];
                $ano_ped_atrac  = $fundeio["ano_ped_atrac"];
                $no_embarc      = $fundeio["no_embarc"];
                $nr_imo         = $fundeio["nr_imo"];
                
                try {
                    // Apaga os dados a serem inseridos na tabela MOV_TECON caso existam
                    $db->delete("MOV_TECON", "ANO_PED_ATRAC = " . $ano_ped_atrac. " and NR_PED_ATRAC = " . $nr_ped_atrac);
                    
                    // Se tudo ocorreu corretamente, Executa todo o processo do banco
                    $db->commit();
                } catch (Zend_Exception $e) {
                    $db->rollBack();
                }
                                
                // Verificar se o PDA a ser importado encontra-se fechado pelo sistema porto.
                $total_pda = 0;
                if ($no_embarc != "") {

                    $select = $db->select()
                                 ->from("FECHAMENTO_PDA", array("TOTAL" => new Zend_Db_Expr("COUNT(*)")))
                                 ->where("NR_PED_ATRAC  = " . $nr_ped_atrac)
                                 ->where("ANO_PED_ATRAC = " . $ano_ped_atrac)
                                 ->where("DTHR_REABERTURA IS NULL");

                    $res = $db->fetchRow($select);

                    $total_pda = (int) $res->TOTAL;
                }

                if ($total_pda == 1) {
                    $erros[] = utf8_encode("Nr/Ano PDA: " . $nr_ped_atrac . "/" . $ano_ped_atrac . " - O PDA informado pelo TECON encontra-se fechado no Sistema Porto");
                }

                // Busca o cÛdigo da embarcaÁ„o a partir do nome da mesma
                $cd_embarc = 0;
                if ($no_embarc != "") {

                    //$select = $db->select()
                    //			 ->from("EMBARCACAO", array("CD_EMBARC"))
                    //			 ->where("UPPER(TRIM(NO_EMBARC)) LIKE UPPER(TRIM('" . trim($no_embarc) . "'))");

                    $select = $db->select()
                                 ->from("FUNDEIO", array("CD_EMBARC"))
                                 ->where("NR_PED_ATRAC = " . $nr_ped_atrac . " AND ANO_PED_ATRAC = " . $ano_ped_atrac);

                    $res = $db->fetchRow($select);

                    $cd_embarc = (int) $res->CD_EMBARC;
                }

                // Busca o n˙mero do fundeio
                $nr_fundeio = 0;
                if ($nr_ped_atrac != "" && $ano_ped_atrac != "") {

                    $select = $db->select()
                                 ->from("PED_ATRAC", array("PED_ATRAC.NR_FUNDEIO"))
                                 ->where("PED_ATRAC.NR_PED_ATRAC  = " . $nr_ped_atrac)
                                 ->where("PED_ATRAC.ANO_PED_ATRAC = " . $ano_ped_atrac);

                    $res = $db->fetchRow($select);

                    $nr_fundeio = (int) $res->NR_FUNDEIO;
                }

                // Captura as atracaÁıes do navio
                $atracacoes = $fundeio["atracacoes"];
                
                // Percorre as atracaÁıes
                foreach ($atracacoes as $atracacao) {

                    // Inicializa a vari·vel de erro
                    $erros = array();

                    // Dados da AtracaÁ„o
                    $dateTimeAtracacao      = new DateTime($atracacao["dthr_atracacao"]);
                    $dthr_atracacao         = $dateTimeAtracacao->format('d/m/Y H:i:s');
                    $dateTimeDesatrac       = new DateTime($atracacao["dthr_desatrac"]);
                    $dthr_desatrac          = $dateTimeDesatrac->format('d/m/Y H:i:s');
                    $dateTimeIniOper        = new DateTime($atracacao["dthr_ini_oper"]);
                    $dthr_ini_oper          = $dateTimeIniOper->format('d/m/Y H:i:s');
                    $dateTimeFimOper        = new DateTime($atracacao["dthr_fim_oper"]);
                    $dthr_fim_oper          = $dateTimeFimOper->format('d/m/Y H:i:s');
                    $qtde_reb_atrac         = $atracacao["qtde_reb_atrac"];
                    $qtde_reb_desatrac      = $atracacao["qtde_reb_desatrac"];
                    $calado_chegada_proa    = $atracacao["calado_chegada_proa"];
                    $calado_chegada_popa    = $atracacao["calado_chegada_popa"];
                    $calado_saida_proa      = $atracacao["calado_saida_proa"];
                    $calado_saida_popa      = $atracacao["calado_saida_popa"];

                    $operacoes = isset($atracacao["operacoes"]["operacao"][1]) ? $atracacao["operacoes"]["operacao"] : $atracacao["operacoes"];
                    
                    foreach ($operacoes as $operacao) {

                        // Inicializa a vari·vel de erro
                        $erros = array();
                        
                        $tp_operacao    = trim($operacao["tp_operacao"]);
                        $tp_carga       = trim($operacao["tp_carga"]);
                        $no_agente      = trim($operacao["no_agente"]);
                        $cnpj_agente    = $operacao["cnpj_agente"];
                        $no_armador     = trim($operacao["no_armador"]);
                        $cnpj_armador   = $operacao["cnpj_armador"];
                        $no_imp_exp     = $operacao["no_imp_exp"];
                        $cnpj_imp_exp   = $operacao["cnpj_imp_exp"];
                        $nr_berco       = $operacao["nr_berco"];

                        // Busca o cÛdigo do agÍnte marÌtimo
                        $cd_agente = 0;
                        if ($cnpj_agente != "") {

                            $select = $db->select()
                                         ->from("CNPJ_TECON", "CD_CLIENTE")
                                         ->where("NR_CNPJ_TECON = TO_NUMBER('" . $cnpj_agente . "')");

                            $res = $db->fetchRow($select);

                            if ($res->CD_CLIENTE == "") {
                                $select = $db->select()
                                             ->from("CLIENTE", "CD_CLIENTE")
                                             ->where("NR_DOC = '" . $cnpj_agente . "'");

                                $res = $db->fetchRow($select);
                            }

                            $cd_agente = $res->CD_CLIENTE;

                            // Se n„o retornou o agente insere ele no banco
                            if ($cd_agente == 0) {

                                // Captura o maior cÛdigo de cliente
                                $select = $db->select()
                                             ->from("CLIENTE", array("CD_CLIENTE" => new Zend_Db_Expr("MAX(NVL(CD_CLIENTE,0))+1")));

                                $res = $db->fetchRow($select);

                                $cd_agente = $res->CD_CLIENTE;

                                // Insere o cliente nas tabelas abaixo
                                $db->insert("CLIENTE", array("CD_CLIENTE"   => $cd_agente,
                                                             "RAZAO_SOCIAL" => $no_agente,
                                                             "NO_FANTASIA"  => $no_agente,
                                                             "NR_DOC"       => $cnpj_agente,
                                                             "FL_AG_MAR"    => 1,
                                                             "FL_OUTROS"    => 1));

                                // Vincula o cliente na tabela do tecon
                                $db->insert("CNPJ_TECON", array("CD_CLIENTE"    => $cd_agente,
                                                                "NR_CNPJ_TECON" => $cnpj_agente));
                            }
                        }

                        //if($cd_agente == 0) {
                        //    $erros["cnpj_agente"] = utf8_encode("O cÛdigo do agente n„o encontrado no sistema porto");
                        //}
                        // Busca o cÛdigo do armador
                        $cd_armador = 0;
                        if ($cnpj_armador != "") {

                            $select = $db->select()
                                         ->from("CNPJ_TECON", "CNPJ_TECON.CD_CLIENTE")
                                         ->where("CNPJ_TECON.NR_CNPJ_TECON = TO_NUMBER('" . $cnpj_armador . "')");

                            $res = $db->fetchRow($select);

                            if ($res->CD_CLIENTE == "") {
                                $select = $db->select()
                                             ->from("CLIENTE", "CD_CLIENTE")
                                             ->where("NR_DOC = '" . $cnpj_armador . "'");

                                $res = $db->fetchRow($select);
                            }

                            $cd_armador = $res->CD_CLIENTE;

                            // Se n„o retornou o armador insere ele no banco
                            if ($cd_armador == 0) {

                                // Captura o maior cÛdigo de cliente
                                $select = $db->select()
                                             ->from("CLIENTE", array("CD_CLIENTE" => new Zend_Db_Expr("MAX(NVL(CD_CLIENTE,0))+1")));

                                $res = $db->fetchRow($select);

                                $cd_armador = $res->CD_CLIENTE;

                                // Insere o cliente nas tabelas abaixo
                                $db->insert("CLIENTE", array("CD_CLIENTE"   => $cd_armador,
                                                             "RAZAO_SOCIAL" => $no_armador,
                                                             "NO_FANTASIA"  => $no_armador,
                                                             "NR_DOC"       => $cnpj_armador,
                                                             "FL_ARMADOR"   => 1,
                                                             "FL_OUTROS"    => 1));

                                // Vincula o cliente na tabela do tecon
                                $db->insert("CNPJ_TECON", array("CD_CLIENTE" => $cd_armador,
                                                                "NR_CNPJ_TECON" => $cnpj_armador));
                            }
                        }

                        //if($cd_armador == 0) {
                        //    $erros["cnpj_armador"] = utf8_encode("O cÛdigo do armador n„o encontrado no sistema porto");
                        //}
                        // Busca o cÛdigo do importador/exportador
                        $cd_imp_exp = 0;
                        if ($cnpj_imp_exp != "") {

                            $select = $db->select()
                                         ->from("CNPJ_TECON", "CNPJ_TECON.CD_CLIENTE")
                                         ->where("CNPJ_TECON.NR_CNPJ_TECON = TO_NUMBER('" . $cnpj_imp_exp . "')");

                            $res = $db->fetchRow($select);

                            if ($res->CD_CLIENTE == "") {
                                $select = $db->select()
                                             ->from("CLIENTE", "CD_CLIENTE")
                                             ->where("NR_DOC = '" . $cnpj_imp_exp . "'");

                                $res = $db->fetchRow($select);
                            }

                            $cd_imp_exp = $res->CD_CLIENTE;

                            // Se n„o retornou o importador/exportador insere ele no banco
                            if ($cd_imp_exp == 0) {

                                // Captura o maior cÛdigo de cliente
                                $select = $db->select()
                                             ->from("CLIENTE", array("CD_CLIENTE" => new Zend_Db_Expr("MAX(NVL(CD_CLIENTE,0))+1")));

                                $res = $db->fetchRow($select);

                                $cd_imp_exp = $res->CD_CLIENTE;

                                // Insere o cliente nas tabelas abaixo
                                $db->insert("CLIENTE", array("CD_CLIENTE"   => $cd_imp_exp,
                                                             "RAZAO_SOCIAL" => $no_imp_exp,
                                                             "NO_FANTASIA"  => $no_imp_exp,
                                                             "NR_DOC"       => $cnpj_imp_exp,
                                                             "FL_IMP"       => 1,
                                                             "FL_EXP"       => 1,
                                                             "FL_OUTROS"    => 1));

                                // Vincula o cliente na tabela do tecon
                                $db->insert("CNPJ_TECON", array("CD_CLIENTE"    => $cd_imp_exp,
                                                                "NR_CNPJ_TECON" => $cnpj_imp_exp));
                            }
                        }

                        //if($cd_imp_exp == 0) {
                        //    $erros["cnpj_imp_exp"] = utf8_encode("O cÛdigo do importador/exportador n„o encontrado no sistema porto");
                        //}

                        if (isset($operacao["cargas"])) {

                            // Captura as cargas
                            $cargas = isset($operacao["cargas"]["carga"][1]) ? $operacao["cargas"]["carga"] : $operacao["cargas"];
                            foreach ($cargas as $carga) {

                                // Inicializa a vari·vel de erro
                                $erros = array();

                                $nr_id_tecon                 = $carga["nr_id_tecon"];
                                $tp_carga                    = trim($carga["tp_carga"]);
                                $dateTimeEntSai              = new DateTime($carga["dthr_ent_sai"]);
                                $dthr_ent_sai                = $dateTimeEntSai->format('d/m/Y H:i:s');
                                $cd_merc_tecon               = $carga["cd_merc_tecon"];
                                $no_merc_tecon               = trim($carga["no_merc_tecon"]);
                                $cd_ncm                      = $carga["cd_ncm"];
                                $unidade_merc                = $carga["unidade_merc"];
                                $qtd_merc                    = $carga["qtd_merc"];
                                $peso_merc                   = $carga["peso_merc"];
                                $vl_receita_cambial          = $carga["vl_receita_cambial"];
                                $ce_mercante                 = $carga["ce_mercante"];
                                $fl_carga_perigosa           = $carga["fl_carga_perigosa"];
                                $sg_pais_orig_dest_merc      = $carga["sg_pais_orig_dest_merc"];
                                $sg_porto_orig_dest_merc     = $carga["sg_porto_orig_dest_merc"];
                                $munic_orig_dest_merc        = $carga["munic_orig_dest_merc"];
                                $uf_orig_dest_merc           = $carga["uf_orig_dest_merc"];
                                $sg_pais_orig_dest_merc_eds  = $carga["sg_pais_orig_dest_merc_eds"];
                                $sg_porto_orig_dest_merc_eds = $carga["sg_porto_orig_dest_merc_eds"];

                                // ValidaÁıes
                                if ($tp_viagem != "LC" && $tp_viagem != "CB" && $tp_viagem != "NI") {
                                    $erros[] = utf8_encode("Nr. Id. TECON: " . $nr_id_tecon . " - Nr/Ano PDA: " . $nr_ped_atrac . "/" . $ano_ped_atrac . " - Os tipos de viagens aceitos s„o: LC=Longo Curso, CB=Cabotagem, NI=NavegaÁ„o Interior");
                                }

                                if ($cd_embarc == 0) {
                                    $erros[] = utf8_encode("Nr. Id. TECON: " . $nr_id_tecon . " - Nr/Ano PDA: " . $nr_ped_atrac . "/" . $ano_ped_atrac . " - EmbarcaÁ„o n„o consta no sistema porto");
                                }

                                if ($nr_fundeio == 0) {
                                    $erros[] = utf8_encode("Nr. Id. TECON: " . $nr_id_tecon . " - Nr/Ano PDA: " . $nr_ped_atrac . "/" . $ano_ped_atrac . " - O n˙mero do fundeio n„o est„o vinculado no sistema porto ao n˙mero/ano do PDA informado");
                                }

                                // Controla o tipo de operaÁ„o
                                if ($tp_operacao != "E" && $tp_operacao != "D" && $tp_operacao != "R" && $tp_operacao != "T") {
                                    $erros[] = utf8_encode("Nr. Id. TECON: " . $nr_id_tecon . " -  Nr/Ano PDA: " . $nr_ped_atrac . "/" . $ano_ped_atrac . " - O tipo de operaÁ„o È inv·lido");
                                }

                                // Verifica o tipo de carga
                                if ($tp_carga != "CGU" && $tp_carga != "CGN" && $tp_carga != "CC" && $tp_carga != "CV") {
                                    $erros[] = utf8_encode("Nr. Id. TECON: " . $nr_id_tecon . "[" . $tp_carga . "] - Os tipos de cargas aceitos s„o: CGU=Carga Geral Unitizada, CGN=Carga Geral N„o Unitizada, CC=Container Cheio, CV=Container Vazio");
                                }

                                // Busca o cÛdigo do porto de origem ou destino do navio
                                $cd_porto_od_merc = $sg_pais_orig_dest_merc . $sg_porto_orig_dest_merc;
                                $cd_porto_od = 0;
                                if ($sg_porto_orig_dest_merc != "" && $sg_pais_orig_dest_merc != "") {

                                    $select = $db->select()
                                                 ->from("PORTO", array("PORTO.CD_PORTO"))
                                                 ->join("PAIS", "PORTO.CD_PAIS = PAIS.CD_PAIS", array())
                                                 ->where("UPPER(PORTO.SG_PORTO) = UPPER('" . $sg_porto_orig_dest_merc . "')")
                                                 ->where("UPPER(PAIS.SG_PAIS)   = UPPER('" . $sg_pais_orig_dest_merc . "')")
                                                 ->where("PORTO.FL_ATIVO = 1");

                                    $res = $db->fetchRow($select);

                                    $cd_porto_od = $res->CD_PORTO;
                                }

                                // Verifica o cÛdigo do porto se existe no sistema
                                if ($cd_porto_od == 0) {
                                    $erros[] = utf8_encode("Nr. Id. TECON: " . $nr_id_tecon . " - PaÌs/Porto n„o consta no sistema porto");
                                }

                                // Verifica o tipo de carga
                                if ($tp_carga != "CGU" && $tp_carga != "CGN" && $tp_carga != "CC" && $tp_carga != "CV") {
                                    $erros[] = utf8_encode("Nr. Id. TECON: " . $nr_id_tecon . " - Os tipos de cargas aceitos s„o: CGU=Carga Geral Unitizada, CGN=Carga Geral N„o Unitizada, CC=Container Cheio, CV=Container Vazio");
                                }

                                // Busca o n˙mero da mercadoria
                                $cd_merc = 0;
                                if ($cd_merc_tecon != "") {

                                    $select = $db->select()
                                            ->from("MERCADORIA_TECON", array("CD_MERC"))
                                            ->where("CD_MERC_TECON = " . $cd_merc_tecon);

                                    $res = $db->fetchRow($select);

                                    $cd_merc = (int) $res->CD_MERC;
                                }

                                if ($cd_merc == 0) {
                                    $erros[] = utf8_encode("Nr. Id. TECON: " . $nr_id_tecon . " - O cÛdigo da mercadoria n„o encontrado no sistema do porto");
                                }

                                if ($peso_merc == 0 || $peso_merc == "") {
                                    $erros[] = utf8_encode("Nr. Id. TECON: " . $nr_id_tecon . " - O peso da mercadoria n„o pode ser zero");
                                }

                                // Se houve erro no bloco superior, dispara a mensagem
                                if (count($erros) > 0) {
                                    $faultError[] = $erros;
                                } // ##########################################################################################################################
                                
                                // Define o array de dados a serem inseridos
                                $dados =  array("NR_ID_TECON" => $nr_id_tecon,
                                                "TP_OPERACAO" => $tp_operacao,
                                                "TP_CARGA" => $tp_carga,
                                                "TP_VIAGEM" => $tp_viagem,
                                                "ANO_PED_ATRAC" => $ano_ped_atrac,
                                                "NR_PED_ATRAC" => $nr_ped_atrac,
                                                "NR_FUNDEIO" => $nr_fundeio,
                                                "NO_EMBARC" => $no_embarc,
                                                "LLOYDS_EMBARC" => $nr_imo,
                                                "CD_EMBARC" => $cd_embarc,
                                                "DTHR_ATRACACAO" => $dthr_atracacao,
                                                "DTHR_OPERACAO" => $dthr_ini_oper,
                                                "DTHR_ENTRADA_SAIDA" => $dthr_ent_sai,
                                                "CNPJ_AGENTE" => $cnpj_agente,
                                                "NO_AGENTE" => $no_agente,
                                                "CD_CLIENTE_AGENTE" => $cd_agente,
                                                "CNPJ_ARMADOR" => $cnpj_armador,
                                                "NO_ARMADOR" => $no_armador,
                                                "CD_CLIENTE_ARMADOR" => $cd_armador,
                                                "CNPJ_IMP_EXP" => $cnpj_imp_exp,
                                                "NO_IMP_EXP" => $no_imp_exp,
                                                "CD_CLIENTE_IMP_EXP" => $cd_imp_exp,
                                                "CD_PORTO_OD_MERC" => $cd_porto_od_merc,
                                                "CD_PORTO_OD" => $cd_porto_od,
                                                "NO_MUN_OD_MERC" => $munic_orig_dest_merc,
                                                "UF_MUN_OD_MERC" => $uf_orig_dest_merc,
                                                "CD_MERC_TECON" => $cd_merc_tecon,
                                                "NO_MERC_TECON" => $no_merc_tecon,
                                                "CD_MERC" => $cd_merc,
                                                "NO_UM" => $unidade_merc,
                                                "PESO_BRUTO" => "",
                                                "PESO_LIQUIDO" => $peso_merc,
                                                "TARA" => "",
                                                "VL_RECEITA_CAMBIAL" => floatval($vl_receita_cambial),
                                                "FL_CARGA_PERIGOSA" => $fl_carga_perigosa,
                                                "CD_CONTAINER" => "",
                                                "TP_CONTAINER" => "",
                                                "DS_TP_CONTAINER" => "",
                                                "FL_REFRIGERADO" => "",
                                                "ISO_CONTAINER" => "",
                                                "TAM_CONTAINER" => "",
                                                "NR_TEUS" => "",
                                                "NO_ARQ_TECON" => $no_arq_tecon,
                                                "FL_TIPO_REG" => $fl_tipo_reg,
                                                "CD_USUARIO" => $cd_usuario,
                                                "CD_ESTACAO" => $cd_estacao,
                                                "CD_NCM" => $cd_ncm,
                                                "TP_TRANSBORDO" => " ",
                                                "TP_ROTACAO" => " ",
                                                "NR_BERCO" => $nr_berco,
                                                "QT_MERC" => $qtd_merc,
                                                "CE_MERCANTE" => $ce_mercante,
                                                "BIGR_PORTO_OI_DF" => $sg_pais_orig_dest_merc_eds,
                                                "TRIG_PORTO_OI_DF" => $sg_porto_orig_dest_merc_eds);

                                try {

                                    // Se n„o tiver erro insere
                                    if (count($erros) == 0) {
                                        // Insere as mercadorias
                                        $inseriu = $db->insert("MOV_TECON", $dados);
                                    }

                                    $totalInsert += 1;
                                } catch (Zend_Exception $e) {
                                    $faultError[] = array("Insert" => utf8_encode("Nr. Id. TECON: " . $nr_id_tecon . " - Erro ao importar os dados no sistema porto. Exception: " . $e->getMessage()), "Data" => $dados);
                                } // ################################################################################################################################################################################################
                            }
                            
                        } else if (isset($operacao["conteineres"])) {

                            // Captura os conteineres
                            $conteineres = isset($operacao["conteineres"]["conteiner"][1]) ? $operacao["conteineres"]["conteiner"] : $operacao["conteineres"];
                            
                            foreach ($conteineres as $conteiner) {

                                // Inicializa a vari·vel de erro
                                $erros = array();
                                
                                $nr_id_tecon = $conteiner["nr_id_tecon"];
                                $cd_conteiner = $conteiner["cd_conteiner"];
                                $tp_conteiner = trim($conteiner["tp_conteiner"]);
                                $tp_transbordo = trim($conteiner["tp_transbordo"]) != "" ? trim($conteiner["tp_transbordo"]) : " ";
                                $tp_rotacao = is_array($conteiner["tp_rotacao"]) ? "" : trim($conteiner["tp_rotacao"]);
                                $iso_conteiner = trim($conteiner["iso_conteiner"]);
                                $peso_tara = $conteiner["peso_tara"];
                                $peso_bruto = $conteiner["peso_bruto"];

                                // ValidaÁıes
                                if ($tp_viagem != "LC" && $tp_viagem != "CB" && $tp_viagem != "NI") {
                                    $erros[] = utf8_encode("Nr. Id. TECON: " . $nr_id_tecon . " - Nr/Ano PDA: " . $nr_ped_atrac . "/" . $ano_ped_atrac . " - Os tipos de viagens aceitos s„o: LC=Longo Curso, CB=Cabotagem, NI=NavegaÁ„o Interior");
                                }

                                if ($cd_embarc == 0) {
                                    $erros[] = utf8_encode("Nr. Id. TECON: " . $nr_id_tecon . " - Nr/Ano PDA: " . $nr_ped_atrac . "/" . $ano_ped_atrac . " - EmbarcaÁ„o n„o consta no sistema porto");
                                }

                                if ($nr_fundeio == 0) {
                                    $erros[] = utf8_encode("Nr. Id. TECON: " . $nr_id_tecon . " - Nr/Ano PDA: " . $nr_ped_atrac . "/" . $ano_ped_atrac . " - O n˙mero do fundeio n„o est„o vinculado no sistema porto ao n˙mero/ano do PDA informado");
                                }

                                // Controla o tipo de operaÁ„o
                                if ($tp_operacao != "E" && $tp_operacao != "D" && $tp_operacao != "R" && $tp_operacao != "T") {
                                    $erros[] = utf8_encode("Nr. Id. TECON: " . $nr_id_tecon . " -  Nr/Ano PDA: " . $nr_ped_atrac . "/" . $ano_ped_atrac . " - O tipo de operaÁ„o È inv·lido");
                                }

                                // Verifica o tipo de carga
                                if ($tp_carga != "CGU" && $tp_carga != "CGN" && $tp_carga != "CC" && $tp_carga != "CV") {
                                    $erros[] = utf8_encode("Nr. Id. TECON: " . $nr_id_tecon . "[" . $tp_carga . "] - Os tipos de cargas aceitos s„o: CGU=Carga Geral Unitizada, CGN=Carga Geral N„o Unitizada, CC=Container Cheio, CV=Container Vazio");
                                }

                                // Busca os dados do conteiner
                                $ds_tipo_conteiner = "";
                                if ($tp_conteiner != "") {

                                    $select = $db->select()
                                            ->from("TIPO_CONTAINER_TECON", array("DS_TIPO", "FL_REFRIGERADO", "TAMANHO"))
                                            ->where("CD_TIPO = '" . $tp_conteiner . "'");

                                    $res = $db->fetchRow($select);

                                    $ds_tipo_conteiner = $res->DS_TIPO;
                                    $fl_refrigerado = $res->FL_REFRIGERADO;
                                    $tamanho_conteiner = (int) $res->TAMANHO;
                                }

                                if ($ds_tipo_conteiner == "") {
                                    $erros[] = utf8_encode("Nr. Id. TECON: " . $nr_id_tecon . " - Tipo de conteiner n„o encontrado no sistema do porto");
                                }

                                // Define o n˙mero de TEUS a partir do tamanho do conteiner
                                $nr_teus = 0;
                                if ($tamanho_conteiner == 10) {
                                    $nr_teus = 0.5;
                                } else if ($tamanho_conteiner == 20) {
                                    $nr_teus = 1;
                                } else if ($tamanho_conteiner == 40) {
                                    $nr_teus = 2;
                                } else if ($tamanho_conteiner == 45) {
                                    $nr_teus = 2.25;
                                }

                                if ($peso_tara == 0 || $peso_tara == "") {
                                    $erros[] = utf8_encode("Nr. Id. TECON: " . $nr_id_tecon . " - A tara do conteiner n„o pode ser zero");
                                }

                                if ($peso_bruto == 0 || $peso_bruto == "") {
                                    $erros[] = utf8_encode("Nr. Id. TECON: " . $nr_id_tecon . " - O peso bruto n„o pode ser zero");
                                }

                                // Zera o peso total
                                $peso_merc_total = 0;

                                // Se for Conteiner Vazio
                                if ($tp_carga == "CV") {

                                    $sg_pais_orig_dest_cont = $conteiner["sg_pais_orig_dest_cont"];
                                    $sg_porto_orig_dest_cont = $conteiner["sg_porto_orig_dest_cont"];
                                    $munic_orig_dest_cont = $conteiner["munic_orig_dest_cont"];
                                    $uf_orig_dest_cont = $conteiner["uf_orig_dest_cont"];
                                    $sg_pais_orig_dest_cont_eds = $conteiner["sg_pais_orig_dest_cont_eds"];
                                    $sg_porto_orig_dest_cont_eds = $conteiner["sg_porto_orig_dest_cont_eds"];
                                    $dateTimeEntSai = new DateTime($mercadoria["dthr_ent_sai"]);
                                    $dthr_ent_sai = $dateTimeEntSai->format('d/m/Y H:i:s');

                                    // Controla o tipo de transbordo
                                    if ($tp_operacao == "T" && ($tp_transbordo != "E" && $tp_transbordo != "D")) {
                                        $erros[] = utf8_encode("Nr. Id. TECON: " . $nr_id_tecon . " - O tipo de transbordo È inv·lido para o tipo de operaÁ„o");
                                    }

                                    // Controla o tipo de rotacao
                                    if ($tp_operacao == "R" && ($tp_rotacao != "B" && $tp_rotacao != "T" && $tp_rotacao != "")) {
                                        $erros[] = utf8_encode("Nr. Id. TECON: " . $nr_id_tecon . " - O tipo de rotaÁ„o È inv·lido para o tipo de operaÁ„o");
                                    }

                                    // Controla as exportaÁıes para o pentagrama BRRIG
                                    if ($tp_operacao == "C" && $sg_pais_orig_dest_merc == "BR" && $sg_porto_orig_dest_merc == "RIG") {
                                        $erros[] = utf8_encode("Nr. Id. TECON: " . $nr_id_tecon . " - A OperaÁ„o È de Carga e o Porto de Destino informado È o de Rio Grande");
                                    }

                                    if ($tp_transbordo == "E" && $sg_pais_orig_dest_merc == "BR" && $sg_porto_orig_dest_merc == "RIG") {
                                        $erros[] = utf8_encode("Nr. Id. TECON: " . $nr_id_tecon . " - O Tipo de Transbordo È de Embarque e o Porto de Destino informado È o de Rio Grande");
                                    }

                                    // Controla as exportaÁıes para o pentagrama BRRIG para a cobran√ßa
                                    if ($tp_operacao == "C" && $sg_pais_orig_dest_merc_eds == "BR" && $sg_porto_orig_dest_merc_eds == "RIG") {
                                        $erros[] = utf8_encode("Nr. Id. TECON: " . $nr_id_tecon . " - A OperaÁ„o È de Carga e o Porto de Destino informado para cobranÁa È o de Rio Grande");
                                    }

                                    // Controla as importaÁıes para o pentagrama BRRIG
                                    if ($tp_operacao == "D" && $sg_pais_orig_dest_merc == "BR" && $sg_porto_orig_dest_merc == "RIG") {
                                        $erros[] = utf8_encode("Nr. Id. TECON: " . $nr_id_tecon . " - A OperaÁ„o È de Descarga e o Porto de Origem informado È o de Rio Grande");
                                    }

                                    if ($tp_transbordo == "D" && $sg_pais_orig_dest_merc == "BR" && $sg_porto_orig_dest_merc == "RIG") {
                                        $erros[] = utf8_encode("Nr. Id. TECON: " . $nr_id_tecon . " - O Tipo de Transbordo È de Desembarque e o Porto de Origem informado È o de Rio Grande");
                                    }

                                    // Controla as importaÁıes para o pentagrama BRRIG para a cobran√ßa
                                    if ($tp_operacao == "D" && $sg_pais_orig_dest_merc_eds == "BR" && $sg_porto_orig_dest_merc_eds == "RIG") {
                                        $erros[] = utf8_encode("Nr. Id. TECON: " . $nr_id_tecon . " - A OperaÁ„o È de Descarga e o Porto de Origem informado para a cobranÁa È o de Rio Grande");
                                    }

                                    if ($tp_transbordo == "D" && $sg_pais_orig_dest_merc_eds == "BR" && $sg_porto_orig_dest_merc_eds == "RIG") {
                                        $erros[] = utf8_encode("Nr. Id. TECON: " . $nr_id_tecon . " - O Tipo de Transbordo È de Desembarque e o Porto de Origem informado para a cobranÁa È o de Rio Grande");
                                    }

                                    // Controla os embarques para o pentagrama BRRIG
                                    if (($tp_operacao == "E" && $tp_carga == "CV") && $sg_pais_orig_dest_merc == "BR" && $sg_porto_orig_dest_merc == "RIG") {
                                        $erros[] = utf8_encode("Nr. Id. TECON: " . $nr_id_tecon . " - A OperaÁ„o È de Embarque e o Porto de Origem informado È o de Rio Grande");
                                    }

                                    // Busca o cÛdigo do porto de origem ou destino do navio
                                    $cd_porto_od_cont = $sg_pais_orig_dest_cont . $sg_porto_orig_dest_cont;
                                    $cd_porto_od = 0;
                                    if ($sg_porto_orig_dest_cont != "" && $sg_pais_orig_dest_cont != "") {

                                        $select = $db->select()
                                                ->from("PORTO", array("PORTO.CD_PORTO"))
                                                ->join("PAIS", "PORTO.CD_PAIS = PAIS.CD_PAIS", array())
                                                ->where("UPPER(PORTO.SG_PORTO) = UPPER('" . $sg_porto_orig_dest_cont . "')")
                                                ->where("UPPER(PAIS.SG_PAIS)   = UPPER('" . $sg_pais_orig_dest_cont . "')")
                                                ->where("PORTO.FL_ATIVO = 1");

                                        $res = $db->fetchRow($select);

                                        $cd_porto_od = $res->CD_PORTO;
                                    }

                                    // Verifica o cÛdigo do porto se existe no sistema
                                    if ($cd_porto_od == 0) {
                                        $erros[] = utf8_encode("Nr. Id. TECON: " . $nr_id_tecon . " - Nr/Ano PDA: " . $nr_ped_atrac . "/" . $ano_ped_atrac . " - PaÌs/Porto n„o consta no sistema porto");
                                    }

                                    // Se houve erro no bloco superior, dispara a mensagem
                                    if (count($erros) > 0) {
                                        $faultError[] = $erros;
                                    } // ##########################################################################################################################
                                    
                                    // Define o array de dados a serem inseridos
                                    $dados = array("NR_ID_TECON" => $nr_id_tecon,
                                        "TP_OPERACAO" => $tp_operacao,
                                        "TP_CARGA" => $tp_carga,
                                        "TP_VIAGEM" => $tp_viagem,
                                        "ANO_PED_ATRAC" => $ano_ped_atrac,
                                        "NR_PED_ATRAC" => $nr_ped_atrac,
                                        "NR_FUNDEIO" => $nr_fundeio,
                                        "NO_EMBARC" => $no_embarc,
                                        "LLOYDS_EMBARC" => $nr_imo,
                                        "CD_EMBARC" => $cd_embarc,
                                        "DTHR_ATRACACAO" => $dthr_atracacao,
                                        "DTHR_OPERACAO" => $dthr_ini_oper,
                                        "DTHR_ENTRADA_SAIDA" => $dthr_ent_sai,
                                        "CNPJ_AGENTE" => $cnpj_agente,
                                        "NO_AGENTE" => $no_agente,
                                        "CD_CLIENTE_AGENTE" => $cd_agente,
                                        "CNPJ_ARMADOR" => $cnpj_armador,
                                        "NO_ARMADOR" => $no_armador,
                                        "CD_CLIENTE_ARMADOR" => $cd_armador,
                                        "CNPJ_IMP_EXP" => $cnpj_imp_exp,
                                        "NO_IMP_EXP" => $no_imp_exp,
                                        "CD_CLIENTE_IMP_EXP" => $cd_imp_exp,
                                        "CD_PORTO_OD_MERC" => $cd_porto_od_cont,
                                        "CD_PORTO_OD" => $cd_porto_od,
                                        "NO_MUN_OD_MERC" => $munic_orig_dest_cont,
                                        "UF_MUN_OD_MERC" => $uf_orig_dest_cont,
                                        "NO_MERC_TECON" => "CONTAINER VAZIO",
                                        "CD_MERC" => 6,
                                        "NO_UM" => "QUILO",
                                        "PESO_BRUTO" => $peso_bruto,
                                        "PESO_LIQUIDO" => 0,
                                        "TARA" => $peso_tara,
                                        "VL_RECEITA_CAMBIAL" => floatval("0.0"),
                                        "FL_CARGA_PERIGOSA" => "N",
                                        "CD_CONTAINER" => $cd_conteiner,
                                        "TP_CONTAINER" => $tp_conteiner,
                                        "DS_TP_CONTAINER" => $ds_tipo_conteiner,
                                        "FL_REFRIGERADO" => $fl_refrigerado,
                                        "ISO_CONTAINER" => $iso_conteiner,
                                        "TAM_CONTAINER" => $tamanho_conteiner,
                                        "NR_TEUS" => $nr_teus,
                                        "NO_ARQ_TECON" => $no_arq_tecon,
                                        "FL_TIPO_REG" => $fl_tipo_reg,
                                        "CD_USUARIO" => $cd_usuario,
                                        "CD_ESTACAO" => $cd_estacao,
                                        "CD_NCM" => "0",
                                        "TP_TRANSBORDO" => $tp_transbordo,
                                        "TP_ROTACAO" => ($tp_rotacao != "" ? $tp_rotacao : " "),
                                        "NR_BERCO" => $nr_berco,
                                        "QT_MERC" => $qtd_merc,
                                        "CE_MERCANTE" => "0",
                                        "BIGR_PORTO_OI_DF" => $sg_pais_orig_dest_cont_eds,
                                        "TRIG_PORTO_OI_DF" => $sg_porto_orig_dest_cont_eds);

                                    try {
                                        // Se n„o tiver erro insere
                                        if (count($erros) == 0) {
                                            // Insere as mercadorias
                                            $inseriu = $db->insert("MOV_TECON", $dados);
                                        }

                                        $totalInsert += 1;
                                    } catch (Zend_Exception $e) {
                                        $faultError[] = array("Insert" => utf8_encode("Nr. Id. TECON: " . $nr_id_tecon . " - Erro ao importar os dados no sistema porto. Exception: " . $e->getMessage()), "Data" => $dados);
                                    } // ################################################################################################################################################################################################
                                    
                                } else { // Se for Conteiner Cheio
                                    // Cria um controle de CE Mercante, ao qual, caso exista a mesma CE Mercante
                                    // dentro de um mesmo conteiner, dever· avisar
                                    $ces_mercantes = array();

                                    // Captura as mercadorias do conteiner
                                    $mercadorias = isset($conteiner["mercadorias"]["mercadoria"][1]) ? $conteiner["mercadorias"]["mercadoria"] : $conteiner["mercadorias"];
                                    foreach ($mercadorias as $mercadoria) {

                                        // Inicializa a vari·vel de erro
                                        $erros = array();

                                        $cd_merc_tecon = $mercadoria["cd_merc_tecon"];
                                        $no_merc_tecon = trim($mercadoria["no_merc_tecon"]);
                                        $cd_ncm = trim($mercadoria["cd_ncm"]);
                                        $ce_mercante = $mercadoria["ce_mercante"];
                                        $dateTimeEntSai = new DateTime($mercadoria["dthr_ent_sai"]);
                                        $dthr_ent_sai = $dateTimeEntSai->format('d/m/Y H:i:s');
                                        $vl_receita_cambial = trim($mercadoria["vl_receita_cambial"]) != "" ? $mercadoria["vl_receita_cambial"] : "0";
                                        $unidade_merc = $mercadoria["unidade_merc"];
                                        $qtd_merc = $mercadoria["qtd_merc"];
                                        $peso_merc = $mercadoria["peso_merc"];
                                        $fl_carga_perigosa = $mercadoria["fl_carga_perigosa"];
                                        $sg_pais_orig_dest_merc = $mercadoria["sg_pais_orig_dest_merc"];
                                        $sg_porto_orig_dest_merc = $mercadoria["sg_porto_orig_dest_merc"];
                                        $munic_orig_dest_merc = $mercadoria["munic_orig_dest_merc"];
                                        $uf_orig_dest_merc = $mercadoria["uf_orig_dest_merc"];
                                        $sg_pais_orig_dest_merc_eds = $mercadoria["sg_pais_orig_dest_merc_eds"];
                                        $sg_porto_orig_dest_merc_eds = $mercadoria["sg_porto_orig_dest_merc_eds"];

                                        // Controla o tipo de transbordo
                                        if ($tp_operacao == "T" && ($tp_transbordo != "E" && $tp_transbordo != "D")) {
                                            $erros[] = utf8_encode("Nr. Id. TECON: " . $nr_id_tecon . " - O tipo de transbordo È inv·lido para o tipo de operaÁ„o");
                                        }

                                        // Controla o tipo de rotacao
                                        if ($tp_operacao == "R" && ($tp_rotacao != "B" && $tp_rotacao != "T")) {
                                            $erros[] = utf8_encode("Nr. Id. TECON: " . $nr_id_tecon . " - O tipo de rotaÁ„o È inv·lido para o tipo de operaÁ„o");
                                        }

                                        // Controla as exportaÁıes para o pentagrama BRRIG
                                        if ($tp_operacao == "C" && $sg_pais_orig_dest_merc == "BR" && $sg_porto_orig_dest_merc == "RIG") {
                                            $erros[] = utf8_encode("Nr. Id. TECON: " . $nr_id_tecon . " - A OperaÁ„o È de Carga e o Porto de Destino informado È o de Rio Grande");
                                        }

                                        if ($tp_transbordo == "E" && $sg_pais_orig_dest_merc == "BR" && $sg_porto_orig_dest_merc == "RIG") {
                                            $erros[] = utf8_encode("Nr. Id. TECON: " . $nr_id_tecon . " - O Tipo de Transbordo È de Embarque e o Porto de Destino informado È o de Rio Grande");
                                        }

                                        // Controla as exportaÁıes para o pentagrama BRRIG para a cobran√ßa
                                        if ($tp_operacao == "C" && $sg_pais_orig_dest_merc_eds == "BR" && $sg_porto_orig_dest_merc_eds == "RIG") {
                                            $erros[] = utf8_encode("Nr. Id. TECON: " . $nr_id_tecon . " - A OperaÁ„o È de Carga e o Porto de Destino informado para cobranÁa È o de Rio Grande");
                                        }

                                        // Controla as importaÁıes para o pentagrama BRRIG
                                        if ($tp_operacao == "D" && $sg_pais_orig_dest_merc == "BR" && $sg_porto_orig_dest_merc == "RIG") {
                                            $erros[] = utf8_encode("Nr. Id. TECON: " . $nr_id_tecon . " - A OperaÁ„o È de Descarga e o Porto de Origem informado È o de Rio Grande");
                                        }

                                        if ($tp_transbordo == "D" && $sg_pais_orig_dest_merc == "BR" && $sg_porto_orig_dest_merc == "RIG") {
                                            $erros[] = utf8_encode("Nr. Id. TECON: " . $nr_id_tecon . " - O Tipo de Transbordo È de Desembarque e o Porto de Origem informado È o de Rio Grande");
                                        }

                                        // Controla as importaÁıes para o pentagrama BRRIG para a cobran√ßa
                                        if ($tp_operacao == "D" && $sg_pais_orig_dest_merc_eds == "BR" && $sg_porto_orig_dest_merc_eds == "RIG") {
                                            $erros[] = utf8_encode("Nr. Id. TECON: " . $nr_id_tecon . " - A OperaÁ„o È de Descarga e o Porto de Origem informado para a cobranÁa È o de Rio Grande");
                                        }

                                        if ($tp_transbordo == "D" && $sg_pais_orig_dest_merc_eds == "BR" && $sg_porto_orig_dest_merc_eds == "RIG") {
                                            $erros[] = utf8_encode("Nr. Id. TECON: " . $nr_id_tecon . " - O Tipo de Transbordo È de Desembarque e o Porto de Origem informado para a cobranÁa È o de Rio Grande");
                                        }

                                        // Controla os embarques para o pentagrama BRRIG
                                        if (($tp_operacao == "E" && $tp_carga == "CV") && $sg_pais_orig_dest_merc == "BR" && $sg_porto_orig_dest_merc == "RIG") {
                                            $erros[] = utf8_encode("Nr. Id. TECON: " . $nr_id_tecon . " - A OperaÁ„o È de Embarque e o Porto de Origem informado È o de Rio Grande");
                                        }

                                        // Busca o cÛdigo do porto de origem ou destino do navio
                                        $cd_porto_od_merc = $sg_pais_orig_dest_merc . $sg_porto_orig_dest_merc;
                                        $cd_porto_od = 0;
                                        if ($sg_porto_orig_dest_merc != "" && $sg_pais_orig_dest_merc != "") {

                                            $select = $db->select()
                                                    ->from("PORTO", array("PORTO.CD_PORTO"))
                                                    ->join("PAIS", "PORTO.CD_PAIS = PAIS.CD_PAIS", array())
                                                    ->where("UPPER(PORTO.SG_PORTO) = UPPER('" . $sg_porto_orig_dest_merc . "')")
                                                    ->where("UPPER(PAIS.SG_PAIS)   = UPPER('" . $sg_pais_orig_dest_merc . "')")
                                                    ->where("PORTO.FL_ATIVO = 1");

                                            $res = $db->fetchRow($select);

                                            $cd_porto_od = $res->CD_PORTO;
                                        }

                                        // Verifica o cÛdigo do porto se existe no sistema
                                        if ($cd_porto_od == 0) {
                                            $erros[] = utf8_encode("Nr. Id. TECON: " . $nr_id_tecon . " - Nr/Ano PDA: " . $nr_ped_atrac . "/" . $ano_ped_atrac . " - PaÌs/Porto n„o consta no sistema porto");
                                        }

                                        // Adiciona as ces mercantes no array
                                        array_push($ces_mercantes, $ce_mercante);
                                        
                                        // Busca o n˙mero da mercadoria
                                        $cd_merc = 0;
                                        if ($cd_merc_tecon != "") {

                                            $select = $db->select()
                                                         ->from("MERCADORIA", array("CD_MERC"))
                                                         ->where("CD_NCM = " . $cd_ncm);

                                            $res = $db->fetchRow($select);

                                            $cd_merc = (int) $res->CD_MERC;
                                        }

                                        if ($cd_merc == 0) {
                                            $erros[] = utf8_encode("Nr. Id. TECON: " . $nr_id_tecon . " - O cÛdigo da mercadoria (" . $cd_ncm . ") n„o encontrado no sistema porto");
                                        }

                                        // Se o conteiner for diferente de 6(vazio) e o peso for zero
                                        if ($cd_merc != 6 && $peso_merc == 0 || $peso_merc == "") {
                                            $erros[] = utf8_encode("Nr. Id. TECON: " . $nr_id_tecon . " - O peso da mercadoria n„o pode ser zero");
                                        }

                                        // Se o conteiner for 6(vazio) e o peso for maior que zero
                                        if ($cd_merc == 6 && $peso_merc > 0) {
                                            $erros[] = utf8_encode("Nr. Id. TECON: " . $nr_id_tecon . " - O peso da mercadoria n„o pode ser maior que zero para conteiner vazio");
                                        }

                                        // Se o tipo de operaÁ„o for ‚??R‚?? e o tipo de rotaÁ„o for ‚??T‚?? alguns campos dever„o ser multiplicados por 2
                                        if ($tp_operacao == "R" || $tp_rotacao == "T") {
                                            $qtd_merc = 2;
                                            $nr_teus = $nr_teus * 2;
                                            $peso_tara = $peso_tara * 2;
                                            $peso_bruto = $peso_bruto * 2;
                                            $peso_merc = $peso_merc * 2;
                                        }

                                        // Soma o peso das mercadorias para validar o peso bruto do conteiner
                                        $peso_merc_total = $peso_merc_total + $peso_merc;

                                        // Se houve erro no bloco superior, dispara a mensagem
                                        if (count($erros) > 0) {
                                            $faultError[] = $erros;
                                        } // ######################
                                        
                                        // Define o array de dados a serem inseridos
                                        $dados = array("NR_ID_TECON" => $nr_id_tecon,
                                            "TP_OPERACAO" => $tp_operacao,
                                            "TP_CARGA" => $tp_carga,
                                            "TP_VIAGEM" => $tp_viagem,
                                            "ANO_PED_ATRAC" => $ano_ped_atrac,
                                            "NR_PED_ATRAC" => $nr_ped_atrac,
                                            "NR_FUNDEIO" => $nr_fundeio,
                                            "NO_EMBARC" => $no_embarc,
                                            "LLOYDS_EMBARC" => $nr_imo,
                                            "CD_EMBARC" => $cd_embarc,
                                            "DTHR_ATRACACAO" => $dthr_atracacao,
                                            "DTHR_OPERACAO" => $dthr_ini_oper,
                                            "DTHR_ENTRADA_SAIDA" => $dthr_ent_sai,
                                            "CNPJ_AGENTE" => $cnpj_agente,
                                            "NO_AGENTE" => $no_agente,
                                            "CD_CLIENTE_AGENTE" => $cd_agente,
                                            "CNPJ_ARMADOR" => $cnpj_armador,
                                            "NO_ARMADOR" => $no_armador,
                                            "CD_CLIENTE_ARMADOR" => $cd_armador,
                                            "CNPJ_IMP_EXP" => $cnpj_imp_exp,
                                            "NO_IMP_EXP" => $no_imp_exp,
                                            "CD_CLIENTE_IMP_EXP" => $cd_imp_exp,
                                            "CD_PORTO_OD_MERC" => $cd_porto_od_merc,
                                            "CD_PORTO_OD" => $cd_porto_od,
                                            "NO_MUN_OD_MERC" => $munic_orig_dest_merc,
                                            "UF_MUN_OD_MERC" => $uf_orig_dest_merc,
                                            "CD_MERC_TECON" => $cd_merc_tecon,
                                            "NO_MERC_TECON" => $no_merc_tecon,
                                            "CD_MERC" => $cd_merc,
                                            "NO_UM" => $unidade_merc,
                                            "PESO_BRUTO" => $peso_bruto,
                                            "PESO_LIQUIDO" => $peso_merc,
                                            "TARA" => $peso_tara,
                                            "VL_RECEITA_CAMBIAL" => floatval($vl_receita_cambial),
                                            "FL_CARGA_PERIGOSA" => $fl_carga_perigosa,
                                            "CD_CONTAINER" => $cd_conteiner,
                                            "TP_CONTAINER" => $tp_conteiner,
                                            "DS_TP_CONTAINER" => $ds_tipo_conteiner,
                                            "FL_REFRIGERADO" => $fl_refrigerado,
                                            "ISO_CONTAINER" => $iso_conteiner,
                                            "TAM_CONTAINER" => $tamanho_conteiner,
                                            "NR_TEUS" => $nr_teus,
                                            "NO_ARQ_TECON" => $no_arq_tecon,
                                            "FL_TIPO_REG" => $fl_tipo_reg,
                                            "CD_USUARIO" => $cd_usuario,
                                            "CD_ESTACAO" => $cd_estacao,
                                            "CD_NCM" => $cd_ncm,
                                            "TP_TRANSBORDO" => $tp_transbordo,
                                            "TP_ROTACAO" => ($tp_rotacao != "" ? $tp_rotacao : " "),
                                            "NR_BERCO" => $nr_berco,
                                            "QT_MERC" => $qtd_merc,
                                            "CE_MERCANTE" => $ce_mercante,
                                            "BIGR_PORTO_OI_DF" => $sg_pais_orig_dest_merc_eds,
                                            "TRIG_PORTO_OI_DF" => $sg_porto_orig_dest_merc_eds);

                                        try {

                                            // Se n„o tiver erro insere
                                            if (count($erros) == 0) {
                                                // Insere as mercadorias
                                                $inseriu = $db->insert("MOV_TECON", $dados);
                                            }

                                            $totalInsert += 1;
                                        } catch (Zend_Exception $e) {
                                            $faultError[] = array("Insert" => utf8_encode("Nr. Id. TECON: " . $nr_id_tecon . " - Erro ao importar os dados no sistema porto. Exception: " . $e->getMessage()), "Data" => $dados);
                                        } // ################################################################################################################################################################################################
                                        
                                    } //foreach($mercadorias as $mercadoria) {
                                    
                                    // Verifica se a ce_mercante j· n„o est· vinculado a outra mercadoria no mesmo conteiner
                                    if (count($ces_mercantes) > 1) {

                                        // Agrupa os valores iguais em um array
                                        $valoresArray = array_count_values($ces_mercantes);

                                        // Verifica se existe mais de uma valor igual
                                        $mostra_ces_mercantes = "";
                                        foreach ($valoresArray as $indice => $valor) {
                                            if ($valor > 1) {
                                                if ($mostra_ces_mercantes != "") {
                                                    $mostra_ces_mercantes .= ",";
                                                }
                                                $mostra_ces_mercantes .= $indice;
                                            }
                                        }

                                        if ($mostra_ces_mercantes != "") {
                                            $erros[] = utf8_encode("Nr. Id. TECON: " . $nr_id_tecon . " - A CE Mercante '" . $ce_mercante . "' do Conteiner '" . $cd_conteiner . "' j√° est„o vinculado a outra mercadoria");
                                        }
                                    }

                                    // Verifica se o peso bruto est· dentro do limite aceit·vel de 10% de diferen√ßa 
                                    // entre a soma da tara com o peso lÌquido da mercadoria.
                                    if ($tp_carga == "CC") {
                                        $verifica_peso = $peso_merc_total + $peso_tara;
                                        $verifica_peso_aceitavel = $peso_bruto * 0.1;
                                        $verifica_peso_min = $peso_bruto - $verifica_peso_aceitavel;
                                        $verifica_peso_max = $peso_bruto + $verifica_peso_aceitavel;
                                        if (!($verifica_peso >= $verifica_peso_min && $verifica_peso <= $verifica_peso_max)) {
                                            $erros[] = utf8_encode("Nr. Id. TECON: " . $nr_id_tecon . " - O peso bruto da mercadoria est„o com valor superior ou inferior a 10%");
                                        }
                                    }

                                    // Para conteineres consolidados, se o peso bruto for diferente da soma total de mercadorias
                                    if ($tp_carga != "CV" && $peso_bruto != ($peso_merc_total + $peso_tara)) {
                                        $erros[] = utf8_encode("Nr. Id. TECON: " . $nr_id_tecon . " - O peso bruto (" . $peso_bruto . " Kg) n„o corresponde ao peso total (" . ($peso_merc_total + $peso_tara) . " Kg) das mercadorias");
                                    }
                                }

                                // Se houve erro no bloco superior, dispara a mensagem
                                if (count($erros) > 0) {
                                    $faultError[] = $erros;
                                } // ######################
                                
                            } //foreach($conteineres as $conteiner) {
                            
                        } //else if(isset($operacao["conteineres"])) {
                        
                    } //foreach($operacoes as $operacao) {
                    
                } //foreach($atracacoes as $atracacao) {
                
            } //foreach($fundeios as $fundeio) {
            
            // Se houve erro nos blocos superiores, dispara email contendo os erros
            if (count($faultError) > 0) {
                //throw new SoapFault("Server", utf8_encode("Erros de importaÁ„o dos dados"), null, $faultError, "FaultSpecified");
                $embarcacao = $nr_ped_atrac . "-" . $ano_ped_atrac;
                $this->sendMail($embarcacao, $faultError, $arquivo);
                rename('./uploads/tecon/' . $arquivo, './uploads/tecon/criticas/' . $arquivo);
                
                // Retorna 0 de erro
                $this->view->ret = 0;
                
            } else {
                
                // Comita os dados
                $db->commit();
                
                // Executa a consolidaÁ„o dos dados importados pelo tecon em segundo plano, via procedure do banco, n„o trancando os dados de serem importados.
                shell_exec("nohup php -c /etc/php5/apache2/php.ini " . LIBRARY_PATH . "/Soap/Classes/ConsolidaTecon.php " . $nr_ped_atrac . " " . $ano_ped_atrac . " > /dev/null &");
                $embarcacao = $nr_ped_atrac . "/" . $ano_ped_atrac;
                // Se ocorreu tudo bem, envia email
                $this->sendMail($embarcacao, "sucesso");
                rename('./uploads/tecon/' . $arquivo, './uploads/tecon/importados/' . $arquivo);
                
                // Retorna 1 de sucesso
                $this->view->ret = 1;
                
            }
            
        } catch (Zend_Exception $e) {

            // Volta tudo que foi feito no banco
            //$db->rollBack();
            
            // Retorna o erro
            $this->view->ret = $e;
            
        }

        // Destroi a vari·vel de conex„o local
        unset($db);
        
    }
    
    /**
     * FunÁ„o para enviar a resposta de sucesso ou de erro da consolidaÁ„o
     * 
     * @param Numeric $embarcacao
     * @param Array   $erros
     */
    private function sendMail($embarcacao, $erros, $arquivo="") {
        
        try {
            
            $html = "<table style='width:95%; font-size:14px;' border='0'>";
            $html.= "";
            if(is_array($erros)) {
                $arquivo = str_replace(".xml", "", $arquivo);
                $arquivo_erros = './uploads/tecon/criticas/' . $arquivo . '_CRITICAS.txt';
                $fpt           = fopen($arquivo_erros, 'a');
                $conteudo      = utf8_encode("ERROS - EMBARCA«√O: " . $embarcacao . "\n");
                $assunto       = "CRÕTICAS NA CONSOLIDA«√O DO PDA: " . $embarcacao . " - IMPORTA«√O DE DADOS DO TECON";
                
                $html .= "<tr>";
                $html .= "<td>";
                $html .= "<strong>" . date("d/m/Y") . " - " . $assunto . "</strong><br><br>";
                $html .= "</td>";
                $html .= "</tr>";
                
                foreach($erros as $erro) {
                    
                    foreach($erro as $dados) {
                        $html .= "<tr>";
                        $html .= "<td>" . utf8_decode($dados);
                        $html .= "</td>";
                        $html .= "</tr>";
                        $conteudo .= $dados . "\n";
                    }
                    
                    fwrite($fpt, $conteudo);
                    
                    $conteudo = "";
                    
                }
                
                fclose($fpt);
                
            } else {
                $assunto = "SUCESSO NA CONSOLIDA«√O DO PDA: " . $embarcacao . " - IMPORTA«√O DE DADOS DO TECON";
                $html   .= "IMPORTA«√O REALIZADA COM SUCESSO E OS DADOS DA EMBARCA«√O J√Å FORAM CONSOLIDADO PELO SISTEMA.";
            }

            $html .= "</table>";
            $mail = new Marca_Mail();
            $mail->setBodyHtml($html);

            $mail->setFrom("portoriogrande@portoriogrande.com.br");
            $mail->addTo("tecon@tecon.com.br");
                        
            $mail->setSubject($assunto);
            $mail->send();
           
        } catch (Marca_Mail_Exception $e) {
            echo $e->getMessage();
        }
        
    }
    
}


?>