<?php

/**
 *
 * Classe responsável por atulizar as diferenças entre tabelas e colunas.
 * 
 * Permite a comparação entre os objetos (tabelas e colunas) existentes no 
 * Banco de Dados com os dados contidos no cadastro de tabelas do Sistema Porto do RG, 
 * podendo o usuário atualizar as diferenças importando os objetos que não existem 
 * para o Sistema Porto.
 *
 * @author     Márcio Souza Duarte
 * @category   Marca Sistemas
 * @copyright  Copyright (c) 1991-2010 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 */
class ConfiguracoesSistema_ImportacaoTabelasController extends Marca_Controller_Abstract_Operacao {

    /**
     * (non-PHPdoc)
     * @see library/Marca/Controller/Abstract/Marca_Controller_Abstract_Operacao#init()
     */
    public function init() {
        parent::init();
        
        ini_set('max_execution_time', 180);
        ini_set('memory_limit', '256M');
		
        // Carrega os modelos de dados
        Zend_Loader::loadClass("AllTablesModel");
        Zend_Loader::loadClass("AllViewsModel");
        Zend_Loader::loadClass("TabelaRModel");
        Zend_Loader::loadClass("ColunaModel");
        Zend_Loader::loadClass("TabelaRefModel");
        Zend_Loader::loadClass("TabelaSistemaModel");
        
    }
    
    /**
     * Metodo index
     * objetivo: Método principal da classe
     */
    public function indexAction() { 
        
    	// Instancia as tabelas do banco
    	$allTables = new AllTablesModel();
    	$allViews  = new AllViewsModel();
    	$tabelaR   = new TabelaRModel();
    	
    	// Captura a sessão
        $sessao = new Zend_Session_Namespace('portoweb');
        
        // Captura o nome do banco
        $dbname = $sessao->arrayconfig["database"]["params"]["dbname"]; 
		$expr = '/\(SID = ([^\)]+)\)/';
		preg_match($expr, $dbname, $matches);
		$this->view->nomeBanco = $matches[1];
        
        // Recupera os parametros da requisição
        $params = $this->_request->getParams();
    	
        /*
    	// Para facilitar :P
    	$letras = array("a", "b", "c", "d", "e",
    					"f", "g", "h", "i", "j",
    					"k", "l", "m", "n", "o",
    					"p", "q", "r", "s", "t",
    					"u", "v", "x", "y", "w", "z");
    	$letra = "";
    	if(isset($params["letra"]) && $params["letra"] != "") {
    		if(array_search($params["letra"], $letras) !== false) {
    			$sessao->indiceLetra = array_search($params["letra"], $letras);
    			$letra = $letras[$sessao->indiceLetra];
    		}
    	}
    	    	
    	if(! isset($sessao->indiceLetra)) {
    		$sessao->indiceLetra = 0;
    		$letra = $letras[$sessao->indiceLetra];
    		
    	} else if($params["letra"] == "") {
    		if($sessao->indiceLetra == 25) {
    			$sessao->indiceLetra = -1;
    			
    		} else {
    			$sessao->indiceLetra += 1;
    			$letra = $letras[$sessao->indiceLetra];
    			echo "<br /> Letra: " . $letra . "<br />";
    		}
    	}
        */
        
        $sessao->indiceLetra = -1;
        $letra = "";
    	$tableName = strtoupper($letra."%");
    	
		// Variável responsável por unir os dados das duas consultas do dicionário de dados
    	$resUnionTabelasDicionarioDados = array();
    	
    	// Verifica se o dicionário de dados já foi carregado    	
    	if(! isset($sessao->resUnionTabelasDicionarioDados)) {
	    	
	    	// Busca as tabelas do dicionário de dados
	    	$dados = array('table_name' => $tableName);
	    	$resTabelasDicionarioDados  = $allTables->fetchall($allTables->queryBuscaDadosTabelasDicionarioDados($dados));
	    	$resTabelasDicionarioDados  = $resTabelasDicionarioDados->toArray();
	    	
	    	// Monta o novo array com os índices pelo nome da tabela
	    	foreach($resTabelasDicionarioDados as $indice => $valor) {
	    		$constraint_name = $valor["CONSTRAINT_NAME"];
	    		$tabela_pai      = $valor["TABELA_PAI"];
	    		if(! isset($resUnionTabelasDicionarioDados[$valor["TABLE_NAME"]][$valor["COLUMN_NAME"]])) {
	    			$resUnionTabelasDicionarioDados[$valor["TABLE_NAME"]][$valor["COLUMN_NAME"]] = $valor;
	    			$resUnionTabelasDicionarioDados[$valor["TABLE_NAME"]][$valor["COLUMN_NAME"]]["CONSTRAINT_NAME"] = array($constraint_name);
	    			$resUnionTabelasDicionarioDados[$valor["TABLE_NAME"]][$valor["COLUMN_NAME"]]["TABELA_PAI"]      = array($tabela_pai);
	    		} else {
	    			$totalInserido = count($resUnionTabelasDicionarioDados[$valor["TABLE_NAME"]][$valor["COLUMN_NAME"]]["CONSTRAINT_NAME"]);
	    			$resUnionTabelasDicionarioDados[$valor["TABLE_NAME"]][$valor["COLUMN_NAME"]]["CONSTRAINT_NAME"][$totalInserido] = $constraint_name;
	    			$resUnionTabelasDicionarioDados[$valor["TABLE_NAME"]][$valor["COLUMN_NAME"]]["TABELA_PAI"][$totalInserido]      = $tabela_pai;
	    		}
	    	} 
	    	
	    	// Busca as tabelas da view
	    	$dados = array('view_name' => $tableName);
	    	$resTabelasViews = $allViews->fetchall($allViews->queryBuscaDadosTabelasViews($dados));
	    	$resTabelasViews = $resTabelasViews->toArray();
	    	
	    	// Monta o novo array com os índices pelo nome da tabela
	    	foreach($resTabelasViews as $indice => $valor) {
	    		$constraint_name = $valor["CONSTRAINT_NAME"];
	    		$tabela_pai      = $valor["TABELA_PAI"];
	    		if(! isset($resUnionTabelasDicionarioDados[$valor["TABLE_NAME"]][$valor["COLUMN_NAME"]])) {
	    			$resUnionTabelasDicionarioDados[$valor["TABLE_NAME"]][$valor["COLUMN_NAME"]] = $valor;
	    			$resUnionTabelasDicionarioDados[$valor["TABLE_NAME"]][$valor["COLUMN_NAME"]]["CONSTRAINT_NAME"] = array($constraint_name);
	    			$resUnionTabelasDicionarioDados[$valor["TABLE_NAME"]][$valor["COLUMN_NAME"]]["TABELA_PAI"]      = array($tabela_pai);
	    		} else {
	    			$totalInserido = count($resUnionTabelasDicionarioDados[$valor["TABLE_NAME"]][$valor["COLUMN_NAME"]]["CONSTRAINT_NAME"]);
	    			$resUnionTabelasDicionarioDados[$valor["TABLE_NAME"]][$valor["COLUMN_NAME"]]["CONSTRAINT_NAME"][$totalInserido] = $constraint_name;
	    			$resUnionTabelasDicionarioDados[$valor["TABLE_NAME"]][$valor["COLUMN_NAME"]]["TABELA_PAI"][$totalInserido]      = $tabela_pai;
	    		}
	    	}
			
	    	// Ordena a união das tabelas do array pelo índice
	    	ksort($resUnionTabelasDicionarioDados);
	    	
	    	// Limpa as variáveis
	    	unset($resTabelasDicionarioDados);
	    	unset($resTabelasViews);
	    	
    	} else {
    		// Seta o array do dicionário de dados 
    		//$resUnionTabelasDicionarioDados = $sessao->resUnionTabelasDicionarioDados;
    	}
    	
    	// Variável responsável montar os dados da tabela R do sistema porto
    	$resTabelasR = array();
    	
    	// Busca as tabelas do sistema porto 
    	$dados = array('cd_tabela' => $tableName);
    	$resTabelasSistemaPorto  = $tabelaR->fetchall($tabelaR->queryBuscaDadosTabelasSistemaPorto($dados));
    	$resTabelasSistemaPorto  = $resTabelasSistemaPorto->toArray();
    	
        // Monta o novo array com os índices pelo nome da tabela
    	foreach($resTabelasSistemaPorto as $indice => $valor) {
    		$constraint_name = $valor["CONSTRAINT_NAME"];
    		$tabela_pai      = $valor["TABELA_PAI"];
    		if(! isset($resTabelasR[$valor["TABLE_NAME"]][$valor["COLUMN_NAME"]])) {
    			$resTabelasR[$valor["TABLE_NAME"]][$valor["COLUMN_NAME"]] = $valor;
    			$resTabelasR[$valor["TABLE_NAME"]][$valor["COLUMN_NAME"]]["CONSTRAINT_NAME"] = array($constraint_name);
    			$resTabelasR[$valor["TABLE_NAME"]][$valor["COLUMN_NAME"]]["TABELA_PAI"]      = array($tabela_pai);
    		} else {
    			$totalInserido = count($resTabelasR[$valor["TABLE_NAME"]][$valor["COLUMN_NAME"]]["CONSTRAINT_NAME"]);
    			$resTabelasR[$valor["TABLE_NAME"]][$valor["COLUMN_NAME"]]["CONSTRAINT_NAME"][$totalInserido] = $constraint_name;
    			$resTabelasR[$valor["TABLE_NAME"]][$valor["COLUMN_NAME"]]["TABELA_PAI"][$totalInserido]      = $tabela_pai;
    		}
    	}
    	
    	// Responsável unir os dados de todas as tabelas
    	// Laço do dicionário de dados
    	// Inclui todos os campos no array
    	foreach($resUnionTabelasDicionarioDados as $indiceTabela => $valorTabela) {
    		foreach($valorTabela as $indiceColuna => $valorColuna) {
    			$resTodasTabelas[$indiceTabela][$indiceColuna] = $valorColuna;
    		}
    		
    		// Adiciona os dados extras da tabela_r
    		if(isset($resTabelasR[$indiceTabela])) {
	    		foreach($resTabelasR[$indiceTabela] as $indiceColuna => $valorColuna) {
	    			if(! isset($resTodasTabelas[$indiceTabela][$indiceColuna])) {
	    				$resTodasTabelas[$indiceTabela][$indiceColuna] = $valorColuna;
	    			}
	    		}
    		}
    	}
    	
    	// Laço da tabela_r
    	// Adiciona tabelas que estão no tabela_r e não estão no dicionário de dados 
    	foreach($resTabelasR as $indiceTabela => $valorTabela) {
    		if(! isset($resTodasTabelas[$indiceTabela])) {
	    		foreach($valorTabela as $indiceColuna => $valorColuna) {
	    			$resTodasTabelas[$indiceTabela][$indiceColuna] = $valorColuna;
	    		}
    		}
    	}
    	
    	// Ordena a união das tabelas do array pelo índice
    	ksort($resTodasTabelas);
    	
    	// Ordena os campos internos pelo indice
    	foreach($resUnionTabelasDicionarioDados as $indiceTabela => $valorTabela) {
   			ksort($resTodasTabelas[$indiceTabela]);
    	}
    	
    	// Limpa as variáveis
    	unset($allTables);
    	unset($allViews);
    	unset($tabelaR);
    	
    	// Array contendo todas as diferenças entre os campos
    	$arrayDifsTotais = array();
    	
    	// Monta o array com as diferenças entre o banco e a TabelaR
    	// Percorre todas as tabelas encontradas
    	foreach($resTodasTabelas as $nomeTabela => $colunasTabela) {
    		
    		// Se a tabela não existir no dicionário de dados é por que deverá ser excluída na TabelaR
    		if(! isset($resUnionTabelasDicionarioDados[$nomeTabela])) {
    			
    			// Percorre as colunas da tabela
    			foreach($colunasTabela as $nomeCampo => $dadosCampo) {
    				
    				// Captura os dados da query
    				$TABLE_NAME = $resTodasTabelas[$nomeTabela][$nomeCampo]["TABLE_NAME"];
    				
    				// Marca para ser excluído na TabelaR, neste caso só é necessário a tabela mesmo
    				$arrayDifsTotais[$nomeTabela][$nomeCampo] = array("TABLE_NAME" => array($TABLE_NAME, "E"));
    				
    			}
    			
    		// Se a tabela não existir na TabelaR deverá ser incluída na mesma	
    		} elseif(! isset($resTabelasR[$nomeTabela])) {
    			
    		    // Percorre as colunas da tabela
    			foreach($colunasTabela as $nomeCampo => $dadosCampo) {
    				
    				// Captura os dados da query
    				$TABLE_NAME 	  = $resTodasTabelas[$nomeTabela][$nomeCampo]["TABLE_NAME"];
				    $NO_TABELA 		  = $resTodasTabelas[$nomeTabela][$nomeCampo]["NO_TABELA"]; 
				    $COLUMN_NAME 	  = $resTodasTabelas[$nomeTabela][$nomeCampo]["COLUMN_NAME"];
				    $NO_COLUNA 		  = $resTodasTabelas[$nomeTabela][$nomeCampo]["NO_COLUNA"];
				    $PK				  = $resTodasTabelas[$nomeTabela][$nomeCampo]["PK"];
				    $TIPO 			  = $resTodasTabelas[$nomeTabela][$nomeCampo]["TIPO"];
				    $TAMANHO 		  = $resTodasTabelas[$nomeTabela][$nomeCampo]["TAMANHO"];
				    if($TAMANHO == ",") {
				    	$TAMANHO = "";
				    } else if(strpos($TAMANHO, ",") !== false) {
				    	$TAMANHO = $this->acrescentaZeroCasasDecimais($TAMANHO);
				    }
				    $NULLABLE 		  = $resTodasTabelas[$nomeTabela][$nomeCampo]["NULLABLE"];
				    $DATA_DEFAULT 	  = $resTodasTabelas[$nomeTabela][$nomeCampo]["DATA_DEFAULT"];
				    $SEARCH_CONDITION = $resTodasTabelas[$nomeTabela][$nomeCampo]["SEARCH_CONDITION"];
				    $CONSTRAINT_NAME  = $resTodasTabelas[$nomeTabela][$nomeCampo]["CONSTRAINT_NAME"];
				    $TABELA_PAI 	  = $resTodasTabelas[$nomeTabela][$nomeCampo]["TABELA_PAI"];
    				
    				// Marca para ser incluido na TabelaR
    				$arrayDifsTotais[$nomeTabela][$nomeCampo] = array(
						    										  "TABLE_NAME" 		 => array($TABLE_NAME, 		 "I"),
																	  "NO_TABELA" 		 => array($NO_TABELA, 		 $NO_TABELA        != "" ? "I" : "X"),
																	  "COLUMN_NAME" 	 => array($COLUMN_NAME, 	 $COLUMN_NAME      != "" ? "I" : "X"),
																	  "NO_COLUNA" 		 => array($NO_COLUNA, 		 $NO_COLUNA        != "" ? "I" : "X"),
																	  "PK" 				 => array($PK, 				 $PK               != "" ? "I" : "X"),
																	  "TIPO" 			 => array($TIPO, 			 $TIPO             != "" ? "I" : "X"),
																	  "TAMANHO" 		 => array($TAMANHO, 		 $TAMANHO          != "" ? "I" : "X"),
																	  "NULLABLE" 		 => array($NULLABLE, 		 $NULLABLE         != "" ? "I" : "X"),
																	  "DATA_DEFAULT" 	 => array($DATA_DEFAULT, 	 $DATA_DEFAULT     != "" ? "I" : "X"),
																	  "SEARCH_CONDITION" => array($SEARCH_CONDITION, $SEARCH_CONDITION != "" ? "I" : "X")
						    								         );
						    								         
    				for($i=0; $i < count($CONSTRAINT_NAME); $i++) {
    					$arrayDifsTotais[$nomeTabela][$nomeCampo]["CONSTRAINT_NAME"][$i] = array($CONSTRAINT_NAME[$i], $CONSTRAINT_NAME[$i] != "" ? "I" : "X");
    					$arrayDifsTotais[$nomeTabela][$nomeCampo]["TABELA_PAI"][$i]      = array($TABELA_PAI[$i], 	   $TABELA_PAI[$i]      != "" ? "I" : "X");
    				}
    				
    			}
    			
    		} else {
    			
    			// Se a tabela existir tanto no dicionário de dados quanto na TabelaR(Sistema Porto)
    			// percorre as colunas para ver o que deverá ser incluído na TabelaR
    			foreach($colunasTabela as $nomeCampo => $dadosCampo) {
    				
    				// Captura os dados da query
    				$TABLE_NAME 	  = $resTodasTabelas[$nomeTabela][$nomeCampo]["TABLE_NAME"];
				    $NO_TABELA 		  = $resTodasTabelas[$nomeTabela][$nomeCampo]["NO_TABELA"]; 
				    $COLUMN_NAME 	  = $resTodasTabelas[$nomeTabela][$nomeCampo]["COLUMN_NAME"];
				    $NO_COLUNA 		  = $resTodasTabelas[$nomeTabela][$nomeCampo]["NO_COLUNA"];
				    $PK				  = $resTodasTabelas[$nomeTabela][$nomeCampo]["PK"];
				    $TIPO 			  = $resTodasTabelas[$nomeTabela][$nomeCampo]["TIPO"];
				    $TAMANHO 		  = $resTodasTabelas[$nomeTabela][$nomeCampo]["TAMANHO"];
				    if($TAMANHO == ",") {
				    	$TAMANHO = "";
				    } else if(strpos($TAMANHO, ",") !== false) {
				    	$TAMANHO = $this->acrescentaZeroCasasDecimais($TAMANHO);
				    }
				    $NULLABLE 		  = $resTodasTabelas[$nomeTabela][$nomeCampo]["NULLABLE"];
				    $DATA_DEFAULT 	  = $resTodasTabelas[$nomeTabela][$nomeCampo]["DATA_DEFAULT"];
				    $SEARCH_CONDITION = $resTodasTabelas[$nomeTabela][$nomeCampo]["SEARCH_CONDITION"];
				    $CONSTRAINT_NAME  = $resTodasTabelas[$nomeTabela][$nomeCampo]["CONSTRAINT_NAME"];
				    $TABELA_PAI 	  = $resTodasTabelas[$nomeTabela][$nomeCampo]["TABELA_PAI"];
    				
    				// Se a coluna existir no dicionário de dados e não existir na TabelaR
    				if(isset($resUnionTabelasDicionarioDados[$nomeTabela][$nomeCampo]) && (! isset($resTabelasR[$nomeTabela][$nomeCampo]))) {
	    				
		    			// Marca para ser incluido na TabelaR
		    			$arrayDifsTotais[$nomeTabela][$nomeCampo] = array(
								    									  "TABLE_NAME" 		 => array($TABLE_NAME, 		 "_"),
																		  "NO_TABELA" 		 => array($NO_TABELA, 		 $NO_TABELA        != "" ? "I" : "X"),
																		  "COLUMN_NAME" 	 => array($COLUMN_NAME, 	 $COLUMN_NAME      != "" ? "I" : "X"),
																		  "NO_COLUNA" 		 => array($NO_COLUNA, 		 $NO_COLUNA        != "" ? "I" : "X"),
																		  "PK" 				 => array($PK, 				 $PK               != "" ? "I" : "X"),
																		  "TIPO" 			 => array($TIPO, 			 $TIPO             != "" ? "I" : "X"),
																		  "TAMANHO" 		 => array($TAMANHO, 		 $TAMANHO          != "" ? "I" : "X"),
																		  "NULLABLE" 		 => array($NULLABLE, 		 $NULLABLE         != "" ? "I" : "X"),
																		  "DATA_DEFAULT" 	 => array($DATA_DEFAULT, 	 $DATA_DEFAULT     != "" ? "I" : "X"),
																		  "SEARCH_CONDITION" => array($SEARCH_CONDITION, $SEARCH_CONDITION != "" ? "I" : "X")
								    								      );
    					
	    				for($i=0; $i < count($CONSTRAINT_NAME); $i++) {
	    					$arrayDifsTotais[$nomeTabela][$nomeCampo]["CONSTRAINT_NAME"][$i] = array($CONSTRAINT_NAME[$i], $CONSTRAINT_NAME[$i] != "" ? "I" : "X");
	    					$arrayDifsTotais[$nomeTabela][$nomeCampo]["TABELA_PAI"][$i]      = array($TABELA_PAI[$i], 	   $TABELA_PAI[$i]      != "" ? "I" : "X");
	    				}

	    			// Se a coluna existir na TabelaR e não existir no dicionário de dados
    				} elseif ((! isset($resUnionTabelasDicionarioDados[$nomeTabela][$nomeCampo])) && isset($resTabelasR[$nomeTabela][$nomeCampo])) {
    					
    					// Marca para ser excluído na TabelaR
		    			$arrayDifsTotais[$nomeTabela][$nomeCampo] = array(
								    									  "TABLE_NAME" 		 => array($TABLE_NAME, 		 "_"),
																		  "NO_TABELA" 		 => array($NO_TABELA, 		 $NO_TABELA        != "" ? "E" : "X"),
																		  "COLUMN_NAME" 	 => array($COLUMN_NAME, 	 $COLUMN_NAME      != "" ? "E" : "X"),
																		  "NO_COLUNA" 		 => array($NO_COLUNA, 		 $NO_COLUNA        != "" ? "E" : "X"),
																		  "PK" 				 => array($PK, 				 $PK               != "" ? "E" : "X"),
																		  "TIPO" 			 => array($TIPO, 			 $TIPO             != "" ? "E" : "X"),
																		  "TAMANHO" 		 => array($TAMANHO, 		 $TAMANHO          != "" ? "E" : "X"),
																		  "NULLABLE" 		 => array($NULLABLE, 		 $NULLABLE         != "" ? "E" : "X"),
																		  "DATA_DEFAULT" 	 => array($DATA_DEFAULT, 	 $DATA_DEFAULT     != "" ? "E" : "X"),
																		  "SEARCH_CONDITION" => array($SEARCH_CONDITION, $SEARCH_CONDITION != "" ? "E" : "X")
								    								      );
						
						for($i=0; $i < count($CONSTRAINT_NAME); $i++) {
	    					$arrayDifsTotais[$nomeTabela][$nomeCampo]["CONSTRAINT_NAME"][$i] = array($CONSTRAINT_NAME[$i], $CONSTRAINT_NAME[$i] != "" ? "I" : "X");
	    					$arrayDifsTotais[$nomeTabela][$nomeCampo]["TABELA_PAI"][$i]      = array($TABELA_PAI[$i], 	   $TABELA_PAI[$i]      != "" ? "I" : "X");
	    				}
    					
    				// Quer dizer que a coluna existe em ambos os lados e tem que verificar o resto dos campos
    				} else {
    					
    					// Percorre os campos da tabela/coluna e analisa um por um
	    				foreach($dadosCampo as $indiceCampo => $valorCampo) {
	    					
	    					// Remove a quebra e o retrocesso
				        	$ar = array("\r", "\n", "\\", "  ");
				        	
				        	// Se o valor não for um array
				        	if($indiceCampo != "CONSTRAINT_NAME" && $indiceCampo != "TABELA_PAI") {
						        $valorCampo      = str_replace($ar, "", trim($valorCampo));
						        $valorDicionario = str_replace($ar, "", trim($resUnionTabelasDicionarioDados[$nomeTabela][$nomeCampo][$indiceCampo])); 
						        $valorTabelaR    = str_replace($ar, "", trim($resTabelasR[$nomeTabela][$nomeCampo][$indiceCampo]));
						        
		    					// Trata o tamanho
		    					if($indiceCampo == "TAMANHO") {
			    					// Arruma as casas decimais dos valores
								    $valorCampo      = $this->acrescentaZeroCasasDecimais($valorCampo);
								    $valorDicionario = $this->acrescentaZeroCasasDecimais($valorDicionario);
								    $valorTabelaR    = $this->acrescentaZeroCasasDecimais($valorTabelaR);
								    
								    // Seta o novo valor do campo
								    $resTodasTabelas[$nomeTabela][$nomeCampo][$indiceCampo] = $valorCampo;
		    					}
		    					
				        	}
	    					
	    					// Se o índice do campo for um destes, define para não incluir ou excluir os dados na TabelaR
	    					if($indiceCampo == "TABLE_NAME"  ||
	    					   $indiceCampo == "NO_TABELA"   ||
	    					   $indiceCampo == "COLUMN_NAME" ||
	    					   $indiceCampo == "NO_COLUNA") {
	    					   	
	    					   	if($resTodasTabelas[$nomeTabela][$nomeCampo][$indiceCampo] != "") {
	    					   		$arrayDifsTotais[$nomeTabela][$nomeCampo][$indiceCampo] = array($valorCampo, "_");
	    					   	} else {
	    					   		$arrayDifsTotais[$nomeTabela][$nomeCampo][$indiceCampo] = array($valorCampo, "X");
	    					   	}
	    					
	    					// Se o indice for da tabela_ref entra aqui
	    					} else if($indiceCampo == "CONSTRAINT_NAME"  ||
	    					   		  $indiceCampo == "TABELA_PAI") {
	    					   	
	    					   	// Captura o valor da constraint e da tabela pai, apenas pelo indice da primeira
	    					   	if($indiceCampo == "CONSTRAINT_NAME") {
	    					   		
		    					   	// Percorre todos os valores do array
		    					   	for($j=0; $j < count($valorCampo); $j++) {
		    					   		
		    					   		// Captura o valor da constraint e da tabela pai no dicionário de dados
		    					   		$valorConstraint1 = str_replace($ar, "", trim($resUnionTabelasDicionarioDados[$nomeTabela][$nomeCampo]["CONSTRAINT_NAME"][$j]));
			    					   	$valorTabelaPai1  = str_replace($ar, "", trim($resUnionTabelasDicionarioDados[$nomeTabela][$nomeCampo]["TABELA_PAI"][$j]));
			    					   	
			    					   	// Captura o valor da constraint e da tabela pai na tabela_r
			    					   	$valorConstraint2 = "";
			    					   	$valorTabelaPai2  = "";
			    					   	if(isset($resTabelasR[$nomeTabela][$nomeCampo]["CONSTRAINT_NAME"][$j])) {
			    					   		$valorConstraint2 = str_replace($ar, "", trim($resTabelasR[$nomeTabela][$nomeCampo]["CONSTRAINT_NAME"][$j]));
			    					   		$valorTabelaPai2  = str_replace($ar, "", trim($resTabelasR[$nomeTabela][$nomeCampo]["TABELA_PAI"][$j]));
			    					   	}
			    					   	
			    					   	// Procura os valores dentro do array da tabela_r
			    					   	$encontrouConstraint = false;
			    					   	for($k=0; $k < count($resTabelasR[$nomeTabela][$nomeCampo][$indiceCampo]); $k++) {
			    					   		
			    					   		// Se encontrar, seta a variável para true
			    					   		if(in_array($valorConstraint1, $resTabelasR[$nomeTabela][$nomeCampo]["CONSTRAINT_NAME"])) {
			    					   			$encontrouConstraint = true;
			    					   		}
			    					   		
			    					   	}
			    					   	
			    					   	// Se não existir na tabela_r seta para inserir
			    					   	if(! $encontrouConstraint) {
			    					   		$arrayDifsTotais[$nomeTabela][$nomeCampo]["CONSTRAINT_NAME"][$j] = array($valorConstraint1, "I");
			    					   		$arrayDifsTotais[$nomeTabela][$nomeCampo]["TABELA_PAI"][$j]      = array($valorTabelaPai1, "I");
			    					   		
			    					   	} else {
			    							
			    							// Colocar "_" para mostrar na tela ou "X" para não mostrar
		    					   			$arrayDifsTotais[$nomeTabela][$nomeCampo]["CONSTRAINT_NAME"][$j] = array($valorConstraint1, "X");
		    					   			$arrayDifsTotais[$nomeTabela][$nomeCampo]["TABELA_PAI"][$j]      = array($valorTabelaPai1, "X");
		    					   			
			    						}
			    						
		    					   	}
		    					   	
	    					   	}
	    					
	    					// Se existir valor nos dois arrays entra aqui
	    					} else {
	    						
	    						// Se o campo for diferente nos dois arrays marca para alterar
	    						if($valorDicionario != $valorTabelaR) {
	    							$arrayDifsTotais[$nomeTabela][$nomeCampo][$indiceCampo] = array($valorCampo, "A");
	    							
	    						// Se for tudo igual
	    						} else {
	    							// Colocar "_" para mostrar na tela ou "X" para não mostrar
	    							$arrayDifsTotais[$nomeTabela][$nomeCampo][$indiceCampo] = array($valorCampo, "X"); 
	    						}
	    					}
		    				
	    				}
    					
    				}
    				
    			}
    			
    		}
    		
    	}
    	
    	// Remove as tabelas/colunas/campos que estiverem iguais em ambos os lados
    	$arrayDifsTotais2 = $arrayDifsTotais;
    	foreach($arrayDifsTotais2 as $indiceNomeTabela => $tabela) {
    		
    		// Verifica se a tabela existe no dicionário de dados 
    		if(isset($resUnionTabelasDicionarioDados[$indiceNomeTabela])) {
    			
	    		// Verifica se a tabela existe na tabela_r
				if(isset($resTabelasR[$indiceNomeTabela])) {
	    		
					// Percorre todas as colunas da tabela
					foreach($tabela as $indiceNomeColuna => $coluna) {
						
						// Verifica se a coluna existe no dicionário de dados
						if(isset($resUnionTabelasDicionarioDados[$indiceNomeTabela][$indiceNomeColuna])) {
							
							// Verifica se a coluna existe na tabela_r
							if(isset($resTabelasR[$indiceNomeTabela][$indiceNomeColuna])) {
								
								// Percorre os campos da coluna
								foreach($coluna as $indiceNomeCampo => $campo) {
									
									if($indiceNomeCampo == "PK"               || 
									   $indiceNomeCampo == "TIPO"             || 
									   $indiceNomeCampo == "TAMANHO"          || 
									   $indiceNomeCampo == "NULLABLE"         || 
									   $indiceNomeCampo == "DATA_DEFAULT"     || 
									   $indiceNomeCampo == "SEARCH_CONDITION") {
									
										// Verifica se tem a coluna na tabela_r
										if($campo[1] == "_" || $campo[1] == "X") {
											unset($arrayDifsTotais[$indiceNomeTabela][$indiceNomeColuna][$indiceNomeCampo]);
										}
										
									} else if($indiceNomeCampo == "CONSTRAINT_NAME"  || 
									   		  $indiceNomeCampo == "TABELA_PAI") {
										
									   	// Percorre o array das constrainst relacionadas a coluna
									   	for($i=0; $i < count($campo); $i++) {
										   	$campo2 = $campo[$i];
										   	// Verifica se tem a coluna na tabela_r
											if($campo2[1] == "_" || $campo2[1] == "X") {
												unset($arrayDifsTotais[$indiceNomeTabela][$indiceNomeColuna][$indiceNomeCampo][$i]);
											}
									   	}
									   	
									   	// Se limpou todos os dados deste array, remove o campo também
									   	if(count($arrayDifsTotais[$indiceNomeTabela][$indiceNomeColuna][$indiceNomeCampo]) == 0) {
									   		unset($arrayDifsTotais[$indiceNomeTabela][$indiceNomeColuna][$indiceNomeCampo]);
									   	}
									}
									
								}
								
								// Verifica se sobrou algum atributo dentro da coluna, no mínimo 4 pois
								// TABLE_NAME, NO_TABELA, COLUMN_NAME E NO_COLUNA são obrigatórios
								$totalCol = count($arrayDifsTotais[$indiceNomeTabela][$indiceNomeColuna]);
								if($totalCol <= 4) {
									unset($arrayDifsTotais[$indiceNomeTabela][$indiceNomeColuna]);
								}
								
							}
							
						}
						
					}
					
				}
				
				// Verifica se sobrou algum atributo dentro da tabela
				if(count($arrayDifsTotais[$indiceNomeTabela]) == 0) {
					unset($arrayDifsTotais[$indiceNomeTabela]);
				}
				
    		}
    		
    	}
    	
    	// Joga os dados para a view
    	$this->view->arrayDifsTotais = $arrayDifsTotais;
    	$this->view->resTabelasR     = $resTabelasR;
    	
    	// Limpa as variáveis
    	unset($arrayDifsTotais2);
    	unset($arrayDifsTotais);
    	unset($resTabelasR);
    }
    
     /**
     * Metodo novo
     * objetivo: utilizado para formularios de cadastro
     */
    public function novoAction() {
        
    }

    /**
     * Metodo salvar
     * objetivo: utilizado para as operações de INSERT/UPDATE
     */
    public function salvarAction() { 
        
        // Controle de excessões para a transação
        try {
        	
        	// Recupera os parametros da requisição
        	$params = $this->_request->getParams();
        	
        	// Instancia os modelos de dados
        	$tabelaR       = new TabelaRModel();
        	$coluna        = new ColunaModel();
        	$tabelaRef     = new TabelaRefModel();
        	$tabelaSistema = new TabelaSistemaModel();
        	
	        // Percorre os campos para inserção/remoção dos valores passados
	        $query          = "";
        	$tabelaCorrente = "";
	        $colunaCorrente = "";
	        $excluiuTabela  = false; // Controla se foi excluído a tabela para não alterar os próximos dados
	        $excluiuColuna  = false; // Controla se foi excluído a coluna para não alterar os próximos dados
	        $dadosColuna    = array(); // Controla os dados de inserção da coluna, vai adicionando os dados para inserir posteriormente 
	        $dadosTabelaRef = array(); // Controla os dados de inserção da tabela_ref, vai adicionando os dados para inserir posteriormente
	        $totalRegistros = count($params["marca"]);
	        $query			= "";
	        for($i=0; $i < $totalRegistros; $i++) {
	        	
	        	// Define se será aplicado alguma ação nos campos, ou seja, 
	        	// se o checkbox foi marcado
	        	$marca = $params["marca"][$i];
	        	
	        	// Define o tipo de dado: tabela, coluna, campo, etc...
	        	$tipo  = $params["tipo"][$i];
	        	
	        	// Tipo da descrição de tabelas e colunas
	        	$tipo2 = $tipo;
	        	if(isset($params["tipo2"][$i]) && trim($params["tipo2"][$i]) != "") {
	        		$tipo2 = $params["tipo2"][$i];
	        	}
	        	
	        	// Remove a quebra e o retrocesso
	        	$ar = array("\r", "\n", "\\", "  ");
		        $valor = str_replace($ar, "", trim($params["valor"][$i]));
	        	
	        	// Define o valor passado para inclusão/exclusão
	        	$valor = htmlspecialchars_decode($valor, ENT_COMPAT);
	        	
	        	// Valor da descrição de tabelas e colunas
	        	$valor2 = $valor;
	        	if(isset($params["valor2"][$i]) && trim($params["valor2"][$i]) != "") {
	        		$valor2  = htmlspecialchars_decode($params["valor2"][$i], ENT_COMPAT);
	        		$valor2 = str_replace($ar, "", trim($valor2));
	        	}
	        	
	        	// Define a ação executado na TabelaR: "I"=inclusão / "E"=exclusão / "_" ou "X"=não faz nada 
	        	$acao  = $params["acao"][$i];
	        	
        		// Verifica se a tabela é diferente da corrente
        		if($tipo == "TABLE_NAME" && $tabelaCorrente != $valor) {
        			
        			// Verifica se os dados da coluna já estão prontos para serem inseridos
        			// Os 4 primeiros 4 são padrão da tabela coluna
	        		if(count($dadosColuna) > 4) {
	        			// Monta os dados da edição dos campos
	        			$where = "CD_TABELA = '{$tabelaCorrente}' AND CD_COLUNA = '{$colunaCorrente}'";
	        			
	        			// Limpa as chaves
	        			unset($dadosColuna["CD_TABELA"]);
	        			unset($dadosColuna["CD_COLUNA"]);
	        			
	        			// Insere os dados na tabela
		        		$coluna->update($dadosColuna, $where);
		        		
		        		// Zera os dados novamente
		        		$dadosColuna = array();
	        		}
	        			
		        	// Verifica se os dados da tabela_ref já estão prontos para serem inseridos
	        		if(count($dadosTabelaRef) == 2) {
	        			// Monta os dados de inserção
	        			$dadosTabelaRef["CD_TABELA"] = $tabelaCorrente;
						$dadosTabelaRef["CD_COLUNA"] = $colunaCorrente;
	        				
	        			// Insere os dados na tabela
	        			$tabelaRef->insert($dadosTabelaRef);
	        			
	        			// Zera os dados novamente
		        		$dadosTabelaRef = array();
	        		}
        			
        			// Seta a exclusão como falsa
        			$excluiuTabela = false;
        			
        			// Define a tabela corrente
        			$tabelaCorrente = $valor;
        			
        			// Seta a nova tabela para inserção da coluna
        			$dadosColuna["CD_TABELA"] = $tabelaCorrente; 
        			
        			// Se foi marcado o checkbox entra na condição
        			if($marca == 1) {
        				
	        			// Verifica se a ação é de inserção/exclusão
	        			if($acao == "I") {
	        				
	        				// Monta os dados de inserção
	        				$dados = array("CD_TABELA"    => $valor,
										   "NO_TABELA"    => substr($valor2, 0, 50),
										   "SG_TABELA"    => substr($valor,  0, 5),
										   "DS_TABELA"    => $valor2,
										   "FL_COMPLETA"  => "S",
										   "FL_REDUZIDA"  => "N",
										   "FL_RELATORIO" => "1");
	        				
	        				// Insere os dados na tabela
	        				$tabelaR->insert($dados);
	        				
	        			} else if($acao == "E") {
	        				
	        				// Cria a condição de exclusão
	        				$where1 = "CD_TABELA = '{$valor}' OR CD_TABELA_PAI = '{$valor}'";
	        				$where2 = "CD_TABELA = '{$valor}'";
	        				
	        				// Remove os dados das tabelas
	        				$tabelaRef->delete($where1);
	        				$coluna->delete($where2);
	        				$tabelaSistema->delete($where2);
	        				$tabelaR->delete($where2);
	        				
	        				// Seta a exclusão como verdadeira
	        				$excluiuTabela = true;
	        				
	        			}
        				
        			}
        			
        		}
        		
        		// Se não houve alguma exclusão de tabela entra nas outras condições
        		if(! $excluiuTabela) {
	        		
	        		// Verifica se a coluna é diferente da corrente
	        		if($tipo == "COLUMN_NAME" && $colunaCorrente != $valor && $tabelaCorrente != "") {
	        			
		        		// Verifica se os dados da coluna já estão prontos para serem inseridos
	        			if(count($dadosColuna) > 4) {
	        				// Monta os dados da edição dos campos
	        				$where = "CD_TABELA = '{$tabelaCorrente}' AND CD_COLUNA = '{$colunaCorrente}'";
	        				
	        				// Limpa as chaves
		        			unset($dadosColuna["CD_TABELA"]);
		        			unset($dadosColuna["CD_COLUNA"]);
	        				
	        				// Insere os dados na tabela
		        			$coluna->update($dadosColuna, $where);
		        			
		        			// Zera os dados novamente
		        			$dadosColuna = array();
	        			}
	        			
		        		// Verifica se os dados da tabela_ref já estão prontos para serem inseridos
	        			if(count($dadosTabelaRef) == 2) {
	        				// Monta os dados de inserção
	        				$dadosTabelaRef["CD_TABELA"] = $tabelaCorrente;
							$dadosTabelaRef["CD_COLUNA"] = $colunaCorrente;
	        				
	        				// Insere os dados na tabela
	        				$tabelaRef->insert($dadosTabelaRef);
	        				
	        				// Zera os dados novamente
		        			$dadosTabelaRef = array();
	        			}
	        			
		        		// Seta a exclusão como falsa
	        			$excluiuColuna = false;
	        			
	        			// Define a coluna corrente
	        			$colunaCorrente = $valor;
	        			
        				// Monta os dados de inserção/edição
        				$dadosColuna = array("CD_TABELA"        => $tabelaCorrente,
										     "CD_COLUNA"        => $colunaCorrente,
										     "NO_COLUNA"        => substr($valor2, 0, 60),
										     "DS_COLUNA"        => $valor2);
	        			
	        			// Se foi marcado o checkbox entra na condição
	        			if($marca == 1) {
	        				
		        			// Verifica se a ação é de inserção/exclusão
		        			if($acao == "I") {
		        				
		        				if(! isset($dadosColuna["TP_COLUNA"])) {
		        					$dadosColuna["TP_COLUNA"] = "C";
		        				}
		        				
		        				if(! isset($dadosColuna["FL_PK"])) {
		        					$dadosColuna["FL_PK"] = "0";
		        				}
		        				
		        				if(! isset($dadosColuna["FL_NULL"])) {
		        					$dadosColuna["FL_NULL"] = "0";
		        				}
		        				
		        				// Insere os dados na tabela
		        				$coluna->insert($dadosColuna);
		        						        				
		        			} else if($acao == "E") {
		        				
		        				// Cria a condição de exclusão
		        				$where1 = "(CD_TABELA = '{$tabelaCorrente}' OR CD_TABELA_PAI = '{$tabelaCorrente}') AND CD_COLUNA = '{$valor}'";
		        				$where2 = " CD_TABELA = '{$tabelaCorrente}' AND CD_COLUNA = '{$valor}'";
		        				
		        				// Remove os dados das tabelas
		        				$tabelaRef->delete($where1);
		        				$coluna->delete($where2);
		        				
		        				// Seta a exclusão como verdadeira
		        				$excluiuColuna = true;
		        				
		        				// Zera os dados novamente
		        				$dadosColuna = array();
		        				
		        			}
	        				
	        			}
	        			
	        		}
	        		
	        		// Se não houve alguma exclusão de coluna entra nas outras condições
	        		if(! $excluiuColuna && $colunaCorrente != "" && $tabelaCorrente != "") {
	        			
	        			// Verifica o tipo do dado para ver em qual tabela irá inserir
	        			if($tipo == "PK"           ||
	        			   $tipo == "TIPO"         ||
	        			   $tipo == "TAMANHO"      ||
	        			   $tipo == "NULLABLE"     ||
	        			   $tipo == "DATA_DEFAULT" ||
	        			   $tipo == "SEARCH_CONDITION") {
	        			   	
		        			// Captura o tipo da coluna
		        			if($tipo == "TIPO") {
		        				switch($valor) {
		        					case "BLOB"     : 
		        					case "RAW" 		: 
		        					case "LONG RAW" : 
		        					case "CLOB"     : $valor = "B"; break;
		        					case "STRING"   : $valor = "C"; break;
		        					case "DATE"     : $valor = "D"; break;
		        					case "LONG"     : $valor = "L"; break;
		        					case "NUMÉRICO" : $valor = "N"; break;
		        					default			: $valor = "C";
		        				}
		        				
	        				}
	        				
	        			    // Altera os alias para os nomes reais dos campos 
	        			    if($tipo == "TIPO") {
		        				$tipo = "TP_COLUNA";
		        				
		        			} else if($tipo == "NULLABLE") {
		        				$tipo = "FL_NULL";		        				
		        				
		        			} else if($tipo == "DATA_DEFAULT") {
		        				$tipo = "DEFAULT_VALUE";
		        				
		        			} else if($tipo == "SEARCH_CONDITION") {
		        				$tipo = "CHECK_CONSTRAINT";
		        				
		        			} else if($tipo == "PK") {
		        				$tipo = "FL_PK";
		        				
		        			}
		        			
		        			if($tipo == "FL_PK") {
		        				if($valor == "PK") {
		        					$valor = "1";
		        				} else {
		        					$valor = "0";
		        				}
		        				
		        			} else if($tipo == "FL_NULL") {
		        				if($valor == "NULL") {
		        					$valor = "1";
		        				} else {
		        					$valor = "0";
		        				}
		        				
		        			}
		        			
	        				// Se foi marcado o checkbox entra na condição
	        				if($marca == 1) {
	        					
		        				// Verifica se a ação é de inserção/exclusão/alteração
			        			if($acao == "I" || $acao == "A") {
				        			// Monta os dados para posterior inserção
			        				$dadosColuna[$tipo] = $valor;
			        				
			        			// Exclui os dados da coluna
			        			} else if($acao == "E") {
			        				
			        				// Monta a condição
			        				$where = "CD_TABELA = '{$tabelaCorrente}' AND CD_COLUNA = '{$colunaCorrente}'";
			        				
			        				// Exclui os dados da tabela
			        				$coluna->delete($where);
			        				
			        			}
	        					
	        				}
	        				
		        		// Se o tipo for algum da condição insere na TABELA_REF
	        			} else if($tipo == "CONSTRAINT_NAME" ||
	        			   		  $tipo == "TABELA_PAI") {
	        				
	        			   	// Verifica se os dados da tabela_ref já estão prontos para serem inseridos
		        			if(count($dadosTabelaRef) == 2) {
		        				// Monta os dados de inserção
		        				$dadosTabelaRef["CD_TABELA"] = $tabelaCorrente;
								$dadosTabelaRef["CD_COLUNA"] = $colunaCorrente;
		        				
		        				// Insere os dados na tabela
		        				$tabelaRef->insert($dadosTabelaRef);
		        				
		        				// Zera os dados novamente
			        			$dadosTabelaRef = array();
		        			}
	        			   	
	        			   	// Altera os alias para os nomes reais dos campos 
	        			    if($tipo == "CONSTRAINT_NAME") {
		        				$tipo = "NO_REFERENCIA";
		        				
		        			} else if($tipo == "TABELA_PAI") {
		        				$tipo = "CD_TABELA_PAI";		        				
		        				
		        			}
		        			
	        			   	// Se foi marcado o checkbox entra na condição
	        				if($marca == 1) {
	        			   		
		        			   	// Verifica se a ação é de inserção/exclusão/alteração
			        			if($acao == "I") {
			        				
			        				// Monta os dados para posterior inserção
			        				$dadosTabelaRef[$tipo] = $valor;
			        				
			        			}
			        			
	        				}
	        				
	        			}
	        			
	        		}
	        		
        		}
        		
	        }
	        
	        // Verifica se os dados da coluna já estão prontos para serem inseridos
	        // Se tiver os campos principais(4) mais algum extra grava os novos valores
	        if(count($dadosColuna) > 4) {
	        	// Monta os dados da edição dos campos
	        	$where = "CD_TABELA = '{$tabelaCorrente}' AND CD_COLUNA = '{$colunaCorrente}'";
	        	
	        	// Limpa as chaves
	        	unset($dadosColuna["CD_TABELA"]);
	        	unset($dadosColuna["CD_COLUNA"]);
	        	
	        	// Insere os dados na tabela
	        	$coluna->update($dadosColuna, $where);
	        	
		        // Zera os dados novamente
		        $dadosColuna = array();
	        }
	        			
		    // Verifica se os dados da tabela_ref já estão prontos para serem inseridos
	        if(count($dadosTabelaRef) == 2) {
	        	// Monta os dados de inserção
	        	$dadosTabelaRef["CD_TABELA"] = $tabelaCorrente;
				$dadosTabelaRef["CD_COLUNA"] = $colunaCorrente;
	        	
	        	// Insere os dados na tabela
	        	$tabelaRef->insert($dadosTabelaRef);
	        	
	        	// Zera os dados novamente
		        $dadosTabelaRef = array();
		        
	        }
	        
	        // Mostra a mensagem de sucesso
	        $mensagemSistema = Zend_Registry::get("mensagemSistema");
			$msg             = array("msg"    => array("Operação realizada."),
                                     "titulo" => "SUCESSO",
                                     "tipo"   => 2);
			$mensagemSistema->send(serialize($msg));
	        
        } catch(Zend_Exception $e) {
        	
        	// Mostra a mensagem de erro
        	$mensagemSistema = Zend_Registry::get("mensagemSistema");
			$msg             = array("msg"    => array("O registro não foi salvo: " . $e->getMessage()),
                                     "titulo" => "ATENÇÃO",
                                     "tipo"   => 4);
			$mensagemSistema->send(serialize($msg));
        	
        }
        
        // Volta para o index
	    //$this->_redirect("configuracoes-sistema/importacao-tabelas/index");
	    $this->_forward('index');
       
	    // Limpa da memória as variáveis
	    unset($params);
    }
    
    /**
     * 
     * Método responsável por acrescentar zeros na casa decimal
     * 
     * @param int $valor
     */
    private function acrescentaZeroCasasDecimais($valor="") {
    	if($valor != "") {
	    	if($valor == ",") {
		    	$valor = "";
		    	
		    } else if(strpos($valor, ",") !== false) {
		    	$divVirg = explode(",", $valor);
		    	if(intval($divVirg[1]) == 0) {
		    		$valor = $divVirg[0];
		    	} else {
		    		$valor = $divVirg[0] . "," . str_pad($divVirg[1], 2, "0");
		    	}
		    }
    	}
    	
    	return $valor;
    }

    /**
     * Metodo excluir
     * objetivo: utilizado para a operação de DELETE
     */
    public function excluirAction() { 
        
    }

    /**
     * Metodo pesquisar
     * objetivo: utilizado para executar pesquisas
     */
    public function pesquisarAction() { 
       
    }

    /**
     * Metodo selecionar
     * objetivo: utilizado para selecionar um registro a partir de uma listagem
     */
    public function selecionarAction() { 
        
    }

    /**
     * Metodo relatorio
     * objetivo: utilizado para gerar um relatorio a partir de uma listagem
     */
    public function relatorioAction() { 
        
    }

}