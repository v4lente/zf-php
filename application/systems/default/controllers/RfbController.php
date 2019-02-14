<?php

/**
 *
 * Classe RfbController
 *
 * Esta classe cria colunas e atualiza tabelas da receita
 *
 * @category   Marca Sistemas
 * @package    MenuController
 * @copyright  Copyright (c) 1991-2009 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 * @version    $Id: MenuController.php 0 2009-11-10 17:25:00 marcio $
 */
class RfbController extends Marca_Controller_Abstract_Comum {
    
    private $tabelas = array();
    private $tabelasSemChave = array();
    
    /**
     * Método inicial para carregamento de classes do controlador
     *
     * @return void
     */
    public function init() {

        // Carrega o método de inicialização da classe pai
        parent::init();
        
        // Inclui os modelos de dados
        Zend_Loader::loadClass("LogSessaoTabelasModel");
        
        // Instancia os modelos de dados
        $logSessaoTabelas = new LogSessaoTabelasModel();
        
        // Joga para o array as tabelas sem chave primaria
        $this->tabelasSemChave[] = "AVARIA_LIN";
		$this->tabelasSemChave[] = "MANIF_LIN_LOCALIZ";
		$this->tabelasSemChave[] = "MERCADORIA_TECON";
		$this->tabelasSemChave[] = "SAIDA_VEIC_CONTR_OPER";
		$this->tabelasSemChave[] = "SAIDA_VEICULO";
		$this->tabelasSemChave[] = "SAIDA_VEICULO_TRANSITO";
		$this->tabelasSemChave[] = "TRANSBORDO";
		$this->tabelasSemChave[] = "TRANSBORDO_EMBARC";
		$this->tabelasSemChave[] = "TRANSBORDO_EXP";
		$this->tabelasSemChave[] = "VEICULOS_AGENDA";
		$this->tabelasSemChave[] = "WEB_CONTRA_CHEQUE_LOG";
        
        // Busca todas as tabelas cadastradas
        $resultadoTabelas = $logSessaoTabelas->fetchAll(null, "NO_TABELA ASC");
        // Instancia a conexão 
        $db = Zend_Registry::get("db");
        // Joga para o array as tabelas
        foreach($resultadoTabelas as $linha) {
        	
        	//$db->query(new Zend_Db_Expr("BEGIN P_CRIA_TRIGGER_LOG('{$linha->NO_TABELA}'); END;"));
        	
        	if($linha->FL_EXCLUSAO_LOGICA == 1) {
                $this->tabelas[] = array($linha->NO_TABELA, 'X', 'X');
                
            } else {
                $this->tabelas[] = array($linha->NO_TABELA, 'X', '');
            }
            
        }
                
    }
    
	/**
     * Método principal da classe
     *
     * @return void
     */
    public function indexAction() {
    	
    	//$this->view->tabelas = array();    	
    	$this->view->tabelas = $this->tabelas;
    }
    
    
    public function criaColunasTabelasRfbAction() {
        
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '128M');
        
        // Direciona para a view index
        $this->_forward("index");
        
        // Instancia a conexão 
        $db = Zend_Registry::get("db");
        
        // Variável que adicionará as colunas nas tabelas
        $sql = "";
        
        // Variável que conterá as colunas a serem inseridas
        $colunas    = "";
            
        // Percorre todas as tabelas e insere
        $totalTabelas = count($this->tabelas); 
        for($i=0; $i < $totalTabelas; $i++) {
            
            // Captura a tabela
            $tabela     = $this->tabelas[$i][0];
            
            // Captura o sequencial
            $sequencial = $this->tabelas[$i][1] == "X" ? true : false;
            if($sequencial) {
                $colunas = "SEQ_VERSAO NUMBER(15)";
            }
            
            // Captura a exclusão lógica
            $excLogica  = $this->tabelas[$i][2] == "X" ? true : false;
            $constraint = "";
            if($excLogica) {
                if($colunas != "") {
                    $colunas .= ", ";
                }
                
                $colunas .= "DTHR_CANCEL DATE, CD_USR_CANCEL VARCHAR2(20 BYTE)";
                
                // Trunca a tabela para gerar a constraint
                $tabTrunc = substr($tabela, 0, 16);
                
                $constraint = "CONSTRAINT FK_USUARIO_X_{$tabTrunc} FOREIGN KEY (CD_USR_CANCEL) REFERENCES PORTO.USUARIO (CD_USUARIO)";
                
            }
            
            // Monta a query para criar as colunas nas tabelas
            $sql .= "<br /><br />ALTER TABLE PORTO.{$tabela} ADD ( {$colunas} );";
            
            // Pega a constraint se tiver
            if($constraint != "") {
                $sql .= "<br />ALTER TABLE PORTO.{$tabela} ADD ( {$constraint} );";
            }
			
			//$sql .= "<br />BEGIN P_CRIA_ESTRUTURA_LOG('{$tabela}'); END;";
			//$sql .= "<br />BEGIN P_CRIA_TRIGGER_LOG('{$tabela}'); END;";
			//$sql .= "<br />";
            
        }
        
        // Imprime na tela o script
        echo $sql;
        
    }
    
    public function atualizaColunasTabelasRfbAction() {
        
        ini_set('max_execution_time', 3600);
        ini_set('memory_limit', '1024M');
        
        // Direciona para a view index
        $this->_forward("index");
        
        // Captura os parametros passados por GET
        $params = $this->getRequest()->getParams();
        
        // Instancia a conexão 
        $db = Zend_Registry::get("db");
        
        // Variável que conterá as colunas a serem inseridas
        $colunas    = "";
		
        // Captura a tabela
        $tabela = strtoupper(trim($params["tabela"]));
        
        if($tabela != "") {
        	/*
	        // Se a tabela possuir chave primária entra na primaira condição se não entra na excessão
	        if(! in_array($tabela, $this->tabelasSemChave)) {  
	            
	            
	            // Monta a consulta que retornará todas as chaves primárias da tabela
	            $select2 = $db->select()
	                          ->from(array("AC"  => "SYS.ALL_CONSTRAINTS"), array("COLUMN_NAME" => "ACL.COLUMN_NAME", "DATA_TYPE" => "ATC.DATA_TYPE"))
	                          ->joinLeft(array("ACL" => "SYS.ALL_CONS_COLUMNS"), "AC.CONSTRAINT_NAME = ACL.CONSTRAINT_NAME", array())
	                          ->joinLeft(array("ATC" => "SYS.ALL_TAB_COLUMNS"),  "ACL.COLUMN_NAME = ATC.COLUMN_NAME AND ACL.TABLE_NAME = ATC.TABLE_NAME", array())
	                          ->where("AC.TABLE_NAME      = '{$tabela}'")
	                          ->where("ATC.TABLE_NAME     = '{$tabela}'")
	                          ->where("AC.CONSTRAINT_TYPE = 'P'")
	                          ->where("ACL.OWNER          = 'PORTO'")
							  ->limit(10000, 0);
	            
	            // Busca todos os registros da tabela
	            $resultado2 = $db->fetchAll($select2);
	            
	            // Se não encontrar chave primária não altera as sequencias
	            if(count($resultado2) > 0) {
	                
	                echo "<br />Tabela: " . $tabela . ".";
	                
	                $colunas = "";
	                foreach($resultado2 as $linha2) {
	                    if($colunas != "") {
	                        $colunas .= ", ";
	                    }
	                    
	                    $colunas .= $linha2->COLUMN_NAME;
	                }
	                
	                // Adiciona a coluna sequencial no tabela
	                $colunas .= ", SEQ_VERSAO";
	                
	                // Monta a consulta que retornará todos os registros da consulta
	                $select1 = $db->select()
	                              ->from(array($tabela), $colunas)
	                              ->order("1, 2 ASC");
	                
	                // Busca todos os registros da tabela
	                $resultado1 = $db->fetchAll($select1);
	                
	                // Verifica se retornou algum registro
	                if(count($resultado1) > 0) {
	                     
	                    try {
	                        
	                        // Zera o sequencial
	                        $sequencial = 0;
	                        
	                        // Desabilita as triggers da tabela
	            			$db->query(new Zend_Db_Expr("ALTER TABLE PORTO.{$tabela} DISABLE ALL TRIGGERS"));
	                        
	                        // Monta a query para inserção do sequêncial
	                        foreach($resultado1 as $linha1) {
	                            
	                            // Busca o código da sequencia para a coluna SEQ_VERSAO
	                            //$sql_seq_versao = new Zend_Db_Expr("SELECT PORTO.F_GERA_SEQ_VERSAO('{$tabela}', 'I', 0) AS SEQ_VERSAO FROM DUAL");
	                            //$seq_versao     = $db->fetchRow($sql_seq_versao);
	                            
	                            $sequencial += 1;
	                            $seq_versao  = '2011' . str_pad($sequencial, 7, "0", STR_PAD_LEFT) . '0001';
	                            
	                            // Monta a condição de atualização
	                            $where = "";
	                            foreach($resultado2 as $linha2) {
	                                if($where != "") {
	                                    $where .= " AND ";
	                                }
	                                
	                                $pKey  = $linha2->COLUMN_NAME;
	                                $cName = $linha1->$pKey;
	                                if($linha2->DATA_TYPE == "NUMBER") {
	                                    $where .= $linha2->COLUMN_NAME . " = " . $cName;
	                                } elseif($linha2->DATA_TYPE == "DATE") {
	                                    $where .= $linha2->COLUMN_NAME . " = TO_DATE('" . $cName . "', 'DD/MM/YYYY HH24:MI:SS')";
	                                } else {
	                                    $where .= $linha2->COLUMN_NAME . " = '" . $cName . "'";
	                                }
	                            }
	                            
	                            // Seta o valor da versão
	                            $dados = array("SEQ_VERSAO" => $seq_versao);
	                            
	                            //$update = "UPDATE PORTO.{$tabela} SET SEQ_VERSAO = '{$seq_versao->SEQ_VERSAO}' WHERE {$where};";
	                            $update = "UPDATE PORTO.{$tabela} SET SEQ_VERSAO = '{$seq_versao}' WHERE {$where}";
	                            
	                            echo "<br />{$update};";
	                            
	                            // Salva a versão na tabela
	                            //$db->query($update);
	                            
	                        }
	                        
	                    } catch (Zend_Exception $e) {
	                        
	                        echo $e->getMessage();
	                        break;
	                    }
	                    
	                    echo "<br />Total linhas alteradas: " . count($resultado1);
	                    
	                } else {
	                    
	                    echo "<br />Esta tabela não foi alterada pois não pósui registro.";
	                }
	                
	            } else {
	                
	                echo "<br />Tabela: " . $tabela . " não possui chave primária. 0 Linhas";
	                
	            }
	            
	        } else {
	        */	
	        	try {
	        		// Desabilita as triggers da tabela
	            	$db->query(new Zend_Db_Expr("ALTER TABLE PORTO.{$tabela} DISABLE ALL TRIGGERS"));
	        		
		        	//$update = "UPDATE PORTO.{$tabela} SET SEQ_VERSAO = '{$seq_versao->SEQ_VERSAO}' WHERE {$where};";
		            $update = new Zend_Db_Expr("UPDATE PORTO.{$tabela} SET SEQ_VERSAO = ('2011' || LPAD(TO_CHAR(ROWNUM), 7, '0') || '0001')");
		                            
		            // Salva a versão na tabela
		            $db->query($update);
	            	
		            echo "<br />Tabela sem chave primária alterda com sucesso." . count($resultado1);
		            
	        	} catch(Zend_Exception $e) {
	        		echo $e->getMessage();
	        	}
	        	
	        //}
	        
	        // Espera 3 segundo a cada nova tabela
	        //sleep(3);
	        /*
	    	try {

	            // Cria a estrutura de log para a tabela após atulizar o sequencial
	            $db->query(new Zend_Db_Expr("BEGIN P_CRIA_ESTRUTURA_LOG('{$tabela}'); END;"));
	            sleep(1); // Espera 1 segundo para executar a outra procedure
	            $db->query(new Zend_Db_Expr("BEGIN P_CRIA_TRIGGER_LOG('{$tabela}'); END;"));
	            
	            echo "<br />Tabela espelho e triggers criadas com sucesso.";
	                	
	        } catch(Zend_Exception $e) {
	            echo $e->getMessage();
	        }
	        */
        } else {
        	echo "<br />Tabela não informada.";
        }
        
    }
    
    public function mostraTabelasSemChavePrimariaAction() {
        
        ini_set('max_execution_time', 180);
        ini_set('memory_limit', '64M');
        
        // Direciona para a view index
        $this->_forward("index");
        
        // Instancia a conexão 
        $db = Zend_Registry::get("db");
        
        // Variável que conterá as colunas a serem inseridas
        $colunas    = "";

        echo "<br /><br />Tabelas sem chave primária: ";
        
        // Percorre todas as tabelas e insere
        $totalTabelas = count($this->tabelas); 
        for($i=0; $i < $totalTabelas; $i++) {
            
            // Captura a tabela
            $tabela = $this->tabelas[$i][0];
            
            // Monta a consulta que retornará todas as chaves primárias da tabela
            $select2 = $db->select()
                          ->from(array("AC"  => "SYS.ALL_CONSTRAINTS"), array("COLUMN_NAME" => "ACL.COLUMN_NAME", "DATA_TYPE" => "ATC.DATA_TYPE"))
                          ->joinLeft(array("ACL" => "SYS.ALL_CONS_COLUMNS"), "AC.CONSTRAINT_NAME = ACL.CONSTRAINT_NAME", array())
                          ->joinLeft(array("ATC" => "SYS.ALL_TAB_COLUMNS"),  "ACL.COLUMN_NAME = ATC.COLUMN_NAME AND ACL.TABLE_NAME = ATC.TABLE_NAME", array())
                          ->where("AC.TABLE_NAME      = '{$tabela}'")
                          ->where("ATC.TABLE_NAME     = '{$tabela}'")
                          ->where("AC.CONSTRAINT_TYPE = 'P'")
                          ->where("ACL.OWNER          = 'PORTO'");
            
            // Busca todos os registros da tabela
            $resultado2 = $db->fetchAll($select2);
            
            // Se não encontrar chave primária não altera as sequencias
            if(count($resultado2) == 0) {
                              
                echo "<br /> - " . $tabela;
                
            }
            
        }
        
    }
    
    
	public function geraScriptComentarioColunasAction() {
        
        ini_set('max_execution_time', 180);
        ini_set('memory_limit', '64M');
        
        // Direciona para a view index
        $this->_forward("index");
        
        // Instancia a conexão 
        $db = Zend_Registry::get("db");
        
        // Variável que conterá as colunas a serem inseridas
        $colunas    = "";

        echo "<br /><br />Script: ";
        
        // Percorre todas as tabelas
        $totalTabelas = count($this->tabelas); 
        for($i=0; $i < $totalTabelas; $i++) {
            
            // Captura a tabela
            $tabela = $this->tabelas[$i][0];
            
            // Monta a consulta que retornará todas as COLUNAS da tabela
            $select2 = $db->select()
                          ->from(array("ATC"  => "SYS.ALL_TAB_COLUMNS"), array("ATC.COLUMN_NAME", "ATC.DATA_TYPE", "DS_TABELA" => new Zend_Db_Expr("(SELECT DS_TABELA FROM LOG_SESSAO_TABELAS WHERE NO_TABELA = '{$tabela}')")))
                          ->where("ATC.TABLE_NAME = '{$tabela}'")
                          ->order("ATC.COLUMN_NAME ASC");
            
            // Busca todos os registros da tabela
            $resultado2 = $db->fetchAll($select2);
			
            echo "<br /><span style='font-weight: bold; color: red;'>-- {$tabela}: {$resultado2[0]->DS_TABELA}</span>";            
            // Se não encontrar colunas mostra a mensagem
            if(count($resultado2) == 0) {
                              
                echo "<br />-- " . $tabela . " não possui colunas";
                
            } else {
            	
            	foreach($resultado2 as $linha) {
            		echo "<br />COMMENT ON COLUMN {$tabela}.{$linha->COLUMN_NAME} IS ''; -- {$linha->DATA_TYPE}";
            	}
            	
            }
            
            echo "<br /><br />";
            
        }
        
    }
    
}
