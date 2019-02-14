<?php
/**
 * Created on 12/02/2010
 *
 * Classe que servirá de base para poder fazer as validações direto nos modelos
 *
 * @filesource
 * @author			David Valente / Márcio Souza Duarte
 * @copyright		Copyright 2009 Marca
 * @package			zendframework
 * @subpackage		zendframework.library.marca
 * @version			1.0
 */
abstract class Marca_Db_Table_Abstract extends Zend_Db_Table_Abstract {

	protected $_rules;
	protected $_filters;
	protected $_validationMessages;
	protected $_params;
	protected $_query;
	protected $_view;
	protected $_codeError;
	protected $showMessage;
        
    
	/**
     * Define a exclusão como fisica [!lógica]
     *
     * @var boolean
     */
    protected $_logicalDelete = false;
    

	public function __construct($config = array()) {

		parent::__construct($config);

		$this->_view = Zend_Controller_Front::getInstance()->getParam('bootstrap')
                                                           ->getResource('layout')
                                                           ->getView();

		// Seta os códigos de erro retornados pelo banco
        $this->_codeError = array("2292"  => "O registro não pode ser excluído, pois em outras telas do sistema, existem dados vinculados a este.", 
                                  "1"     => "Registro já inserido. Verifique a unicidade dos dados.",
        						  "06550" => "O objeto deve ser declarado");

        try {
            $sessao = new Zend_Session_Namespace('portoweb');
            $msg_padrao_bd = $sessao->bd->msg_padrao_bd;

            // Ativa as mensagem
            $this->showMessage = true;

            if (isset($msg_padrao_bd)){
                if(! $msg_padrao_bd){
                    // Desativa as mensagem
                    $this->showMessage = false;
                }
            }
        } catch(Zend_Exception $e) {
            // Ativa as mensagem
            $this->showMessage = true;
        }

    }

    

	/**
	 * Adicona regras de negocio ao modelo
	 * 
	 * É passado um array com os nomes "name" das regras
     * a serem adicionadas no modelo
	 *
	 * @param $rules array("name"=>"CD_EXEMPLO", "class"=>"NotEmpty", "errorMessage"=>"Campo Obrigatório", "filter"=>"StringToUpper")
	 * @return void
	 */
	public function addRules($rules = array()) {

		if (count($rules) > 0) {
			if(in_array("name", array_keys($rules)) && in_array("class", array_keys($rules)) ){
				array_push($this->_rules, $rules);
			}
			else {
				throw new Exception('Nome ou classe não definida ao adicionar a regra.');
			}
		}

	}
	
	
	/**
     * Remove regras de negocio do modelo
     * 
     * É passado um array com os nomes "name" das regras
     * a serem removidas do modelo
     *
     * @param $rules array("regra1", "regra2", "regra...")
     * @return void
     */	
	public function removeRules($rules = array()) {
	    
	    // Captura o total de regras do modelo
	    $totalRegras = count($rules); 
	    
	    // Captura as regras para poder removelas
	    $editRules = $this->_rules;
	    
	    // Busca e remove a regra caso ela exista
	    if ($totalRegras > 0) {
	        
	        // Percorre as regras passadas para serem removidas
	        for($i=0; $i < $totalRegras; $i++) {
	            $j=0;
	            
	            // Percorre cada regra do modelo
    	        foreach($this->_rules as $rule) {
    	            
    	            // Verifica se a regra passada existe na regra
    	            // cadastrada no modelo
    	            if(trim($rule["name"]) == trim($rules[$i])) {
    	                unset($editRules[$j]);
    	            }
    	            $j++;
    	        }
	        }
	        
	        // Zera as regras para serem reinceridas
	        $this->_rules = array();
	        
	        // Repopula as regras nos índices certos
	        foreach($editRules as $rules) {
	            array_push($this->_rules, $rules);
	        }
	        
	        // Desaloca da memória a variável
	        unset($editRules);
	        
	    } else {
            throw new Exception('Name or Class not defined in addRules.');
        }
        
	}
	
	
	/**
     * Returns an instance of a Zend_Db_Table_Select object.
     *
     * @param bool $withFromPart Whether or not to include the from part of the select based on the table
     * @return Zend_Db_Table_Select
     */
    public function select($withFromPart = self::SELECT_WITHOUT_FROM_PART)
    {
        require_once 'Marca/Db/Table/Select.php';
        $select = new Marca_Db_Table_Select($this);
        if ($withFromPart == self::SELECT_WITH_FROM_PART) {
            $select->from($this->info(self::NAME), Marca_Db_Table_Select::SQL_WILDCARD, $this->info(self::SCHEMA));
        }
        return $select;
    }


    /**
     *  Retorna o próximo numero da sequência
     *
     *  @return integer
     */
	public function nextVal($nameSequence=""){

        // Verifica se existe a sequence e se não está vazia
        if($this->_sequence !== true || $nameSequence != "") {
        	
            try {

                // Captura a instância do banco
                $db = Zend_Registry::get("db");
				
				if($this->_sequence !== true) {
					$nameSequence = $this->_sequence;
				}
				
                //Gera o sequence da tabela marca glossario
                $sql = $db->select()
                          ->from("dual", array("SEQUENCE" => "{$nameSequence}.NEXTVAL"));
                
                // Executa a consulta
                $objSequence = $db->fetchRow($sql);
                
                // Retorna a sequencia do banco
                return $objSequence->SEQUENCE;

            } catch(Exception $e) {
                return $e;
            }

        } else {
        	
            // Será implementado aqui pois a tabela não possui sequencia
	        try {
	        	
	            // Verifica se a chave é composta   
                if (count($this->_primary) > 1) {
                    throw new Zend_Db_Table_Exception("ERRO: Chave composta. Implemente sua sequencia(SEQUENCE)!");
                }
                
	            // Caso seja um array pega o primeiro indice
                if(is_array($this->_primary)) {
                	$key     = array_keys($this->_primary);
                	$primary = $this->_primary[$key[0]];
                } else {
                    $primary = $this->_primary;
                }
	            	            
	            // Pega o último valor da sequencia
	            if(! isset($this->_sequenceNumber)) {
	            	$this->_sequenceNumber = 0;
	            }
	            
	            if($this->_sequenceNumber == 0) {
	            	
		            try {
	    	        	
	    	        	// Busca o último código cadastrado
	    	            $select = $this->select()
	    	                           ->from($this, array("SEQUENCE" => new Zend_Db_Expr('(MAX(' . $primary . ')+1)')))
	    	                           ->where("FL_EXLUIDO = 0");
	    	                           
	    	            $row    = $this->fetchRow($select);
	    	                        
	    	            // Retorna o próximo código 
	    	            $this->_sequenceNumber = ($row->SEQUENCE == 0) ? 1 : $row->SEQUENCE;
	    	            return $this->_sequenceNumber;
	    	            
		            } catch(Exception $e) {
		                
		                // Busca o último código cadastrado
	                   	$select = $this->select()
	                                   ->from($this, array("SEQUENCE" => new Zend_Db_Expr('(MAX(' . $primary . ')+1)')));
	                                   
	                    $row    = $this->fetchRow($select);
	                                
	                    // Retorna o próximo código 
	                    $this->_sequenceNumber = ($row->SEQUENCE == 0) ? 1 : $row->SEQUENCE;
	                    return $this->_sequenceNumber;
		            }
		            
	            } else {
	            	
	            	$this->_sequenceNumber += 1;
	            	return $this->_sequenceNumber;
	            }
	
	        } catch (Exception $e) {
	            echo $e->getMessage();
	        }
        }
    }

	/**
	 * Retorna as regras do modelo
	 *
	 * @return array
	 */
	public function getRules(){
		return $this->_rules;
	}

	/**
	 * Verifica se passou na validação
	 * Regras validas possiveis:
	 * 	Zend_validate: Alnum, Alpha, Barcode, Between, Ccnum, Date, Digits, EmailAddress, Exception, Float,
	 * 	GreaterThan, Hex, Hostname, Iban, Identical, InArray, Int, Interface, Ip, LessThan, NotEmpty, Regex, StringLength.
	 *
	 * @param array $params
	 * @return boolean
	 */
	public function isValid(&$params = array()) {

		$this->_validationMessages = array ();

		$arrayIndiceCampos = array();
		$arrayValuesCampos = array();
		// Joga em array os indice e valores dos campos do formulario
		foreach ( $params as $key => $value ) {
			$arrayIndiceCampos[] = strtoupper($key);
			$arrayValuesCampos[strtoupper($key)] = $value;
		}
		
		// Se não possuir regras para validação, retorna true
		if(! isset($this->_rules) || count($this->_rules)  == 0) {
			return true;
		}
		
		// Verifica em cada regra se encontra o campo correspondente no formulario
		foreach ( $this->_rules as $rule ) {

			// Se não foi informado um name/campo da tabela
			if(!isset($rule['name'])) {
				$rule['name'] = '';
			}

			// Se não foi informado o campo do formulario
			// Este campo serve para relacionar o nome do campo no formulário com o
			// nome do campo da tabela no banco de dados
			if(!isset($rule['nameForm'])) {
				$rule['nameForm'] = '';
			}

			// Verifica se tem regra para o compo do formulairo passado
			if (in_array(strtoupper($rule['name']), $arrayIndiceCampos) || in_array(strtoupper($rule['nameForm']), $arrayIndiceCampos) ) {

    			// Seta os filtros nos dados
                try {
                    if(isset($rule['filter'])) {
                    	
                        $aFilter = array();
                        if(is_array($rule['filter'])) {
                            $aFilter = $rule['filter'];
                        } else {
                            $aFilter = array($rule['filter']);
                        }
						                       
                        // Busca todos os filtros passados e aplica nos valores
                        foreach($aFilter as $indice => $valor) {

                        	// Pega o nome do FILTRO
                        	if (is_int($indice)) {
                        		$nameFilter = $valor;
                        	} else {                        		
                        		$nameFilter = $indice;
                        	}
                        	
                        	// Pega os paramentros do FILTRO
                        	if (is_array($valor)) {
                        		$paramsFilter = $valor;
                        	} else {                        		
                        		$paramsFilter = null;                        		
                        	}
                        	
                            // Se for uma função nativa do php executa como filtro
                            if (function_exists($nameFilter)) {
								
                                $filter = new Zend_Filter_Callback($nameFilter);

                                if(! is_array($params[$rule['name']])) {
                                    $params[$rule['name']]            = $filter->filter($params[$rule['name']]);
                                    $arrayValuesCampos[$rule['name']] = $params[$rule['name']];

                                } else {
                                    $totVal = count($params[$rule['name']]);
                                    for($i=0; $i < $totVal; $i++) {
                                        $params[$rule['name']][$i]            = $filter->filter($params[$rule['name']][$i]);
                                        $arrayValuesCampos[$rule['name'][$i]] = $params[$rule['name']];
                                    }
                                }

                            } else {
								
                            	// Se possuir filtro
                            	if (trim($nameFilter) != "") {
									eval("\$filter = new {$nameFilter}();");
                            	
	                                if(! is_array($params[$rule['name']])) {
	                                        $params[$rule['name']]            = $filter->filter($params[$rule['name']]);
	                                        $arrayValuesCampos[$rule['name']] = $params[$rule['name']];
	                                } else {
	                                    $totVal = count($params[$rule['name']]);
	                                    for($i=0; $i < $totVal; $i++) {
	                                        Zend_Filter::filterStatic($params[$rule['name']][$i], $filter);
	                                        Zend_Filter::filterStatic($arrayValuesCampos[$rule['name']][$i], $filter);
	                                    }
	                                }
	                                
                            	}
                            }
                        }
                    }

                } catch(Zend_Exception $e) {
                    echo $e->getMessage();
                }

				$result = true;
				$value = $arrayValuesCampos[strtoupper($rule['name'])];
				
				// Verifica se foi passado alguma regra, caso não tenha sido é aplicado
				// apenas o filtro no valor				
				if(isset($rule['class']) && ! is_null($rule['class'])) {

    				// A regra options serve para comparar se o valor passado está contido no
    				// conjunto de valores definido na regra
    				// Ex.: $_rule = ('InArray', array(0, 1)); $value = 1;
    				if (isset ( $rule ['options'] )) {

    					if(! is_array($value)) {
    						$result = Zend_Validate::is ( $value, $rule['class'], array($rule['options']) );
    					} else {
    						$totVal = count($value);
    						for($i=0; $i < $totVal; $i++){
    							$result = Zend_Validate::is ( $value[$i], $rule['class'], array($rule['options']) );
    						}
    					}

    				} else {

    					if(! is_array($value)) {
    						$result = Zend_Validate::is ( $value, $rule['class']);
    					} else {
    						$totVal = count($value);
    						for($i=0; $i < $totVal; $i++){
    							$result = Zend_Validate::is ( $value[$i], $rule['class']);
    						}
    					}
    				}

    				if (! $result) {
    					$this->_validationMessages [] = "(".strtoupper($rule['name']).") ". $rule['errorMessage'];
    				}
				}

			} else {
				// Verifica se tem class de validação para gerar a mensagem para o campo
				if (isset($rule['class'])) {
					// Caso não encontreo o campo correspondente a regra seta a mensagem de erro
					$this->_validationMessages [] = "(".strtoupper($rule['name']).") ". $rule['errorMessage'];	
				}
				
			}

		}

		if (sizeof ( $this->_validationMessages ) > 0) {
			
		    if($this->showMessage) {
    		    
    			$mensagemSistema = Zend_Registry::get("mensagemSistema");
        		
        		$msg = array("msg"    => $this->_validationMessages, 
    						 "titulo" => "ATENÇÃO",
    						 "tipo"   => 4);
        		
    			$mensagemSistema->send(serialize($msg));

		    }
			
			return false;
		}

		return true;
	}

	/**
     * Inserts a new row.
     *
     * @param  array  $data  Column-value pairs.
     * @return mixed         The primary key of the row inserted.
     */
    public function insert(array $data) {

    	// Define a operação na view
		$this->_view->operacao = "novo";
		
		$this->_setupPrimaryKey();

        /**
         * Zend_Db_Table assumes that if you have a compound primary key
         * and one of the columns in the key uses a sequence,
         * it's the _first_ column in the compound key.
         */
        $primary = (array) $this->_primary;
        $pkIdentity = $primary[(int)$this->_identity];
		
        /**
         * Monta a o esquema junto a tabela
         */
        $tableSpec = ($this->_schema ? $this->_schema . '.' : '') . $this->_name;
        
	    try {
			            
			// Inicializa a operação
			$this->_db->beginTransaction();
			
			// Se for um array multiplo
			if(is_array($data[0])) {
				
				// Percorre todas as tuplas
				foreach ($data as $colval) {
					
                    // Zera os valores a serem inseridos
                    $columns = array();
                    $values  = array();
                    
					// Captura a sequencia caso ela tenha sido definida
					if (is_string($this->_sequence) && !isset($colval[$pkIdentity])) {
						$colval[$pkIdentity] = $this->_db->nextSequenceId($this->_sequence);
					}
					
					// Percorre cada coluna da linha
                    $i = 0;
					foreach ($colval as $column => $value) {
                        
                        $columns[] = $this->_db->quoteIdentifier($column, true);
                        if ($value instanceof Zend_Db_Expr) {
                            $values[] = $value->__toString();
                            unset($colval[$column]);
                        } else {
                            if ($this->_db->supportsParameters('positional')) {
                                $values[] = '?';
                            } else {
                                if ($this->_db->supportsParameters('named')) {
                                    unset($colval[$column]);
                                    $colval[':col' . $i] = $value;
                                    $values[] = ':col' . $i;
                                    $i++;
                                } else {
                                    throw new Zend_Db_Adapter_Exception(get_class($this) ." doesn't support positional or named binding");
                                }
                            }
                        }
                        
					}
                    
                    // Insere a linha corrente
                    $query = "INSERT INTO " 
                             . $this->_db->quoteIdentifier($tableSpec, true)
                             . ' (' . implode(', ', $columns) . ') '
                             . 'VALUES (' . implode(', ', $values) . ')';
                    
                    // execute the statement and return the number of affected rows
                    if ($this->_db->supportsParameters('positional')) {
                        $colval = array_values($colval);
                    }
                    
                    $this->_db->query($query, $colval);
				}
				
			} else { // Entra caso exista apenas uma linha a ser inserida
				
				// Captura a sequencia caso ela tenha sido definida
				if (is_string($this->_sequence) && !isset($data[$pkIdentity])) {
					$data[$pkIdentity] = $this->_db->nextSequenceId($this->_sequence);
				}
				
                // Percorre a linha campo a campo
                $i = 0;
                foreach ($data as $column => $value) {

                    $columns[] = $this->_db->quoteIdentifier($column, true);
                    if ($value instanceof Zend_Db_Expr) {
                        $values[] = $value->__toString();
                        unset($data[$column]);
                    } else {
                        if ($this->_db->supportsParameters('positional')) {
                            $values[] = '?';
                        } else {
                            if ($this->_db->supportsParameters('named')) {
                                unset($data[$column]);
                                $data[':col' . $i] = $value;
                                $values[] = ':col' . $i;
                                $i++;
                            } else {
                                throw new Zend_Db_Adapter_Exception(get_class($this) ." doesn't support positional or named binding");
                            }
                        }
                    }

                }
                				
                // Insere a linha
                $query = "INSERT INTO " 
                             . $this->_db->quoteIdentifier($tableSpec, true)
                             . ' (' . implode(', ', $columns) . ') '
                             . 'VALUES (' . implode(', ', $values) . ')';
                
                // execute the statement and return the number of affected rows
                if ($this->_db->supportsParameters('positional')) {
                    $data = array_values($data);
                }
                
				$this->_db->query($query, $data);
			    
			}
			
			// Finaliza a operação
			$this->_db->commit();
			
			if($this->showMessage) {
			
				$mensagemSistema = Zend_Registry::get("mensagemSistema");
		
				$msg = array("msg"    => array("Operação realizada."), 
							 "titulo" => "SUCESSO",
							 "tipo"   => 2);
		
				$mensagemSistema->send(serialize($msg));
			
			}
			
			return true;

		} catch (Zend_Exception $e) {
			
			// Se der algo errado no processo, volta todas operações executadas
			$this->_db->rollBack();
			
		    if($this->showMessage) {
		        
    			$mensagemSistema = Zend_Registry::get("mensagemSistema");
        	
        		// Captura o erro do sistema
        		echo "<br />" . $e->getCode();
        		echo "<br />" . $e->getMessage();
                $erro = $this->_codeError[$e->getCode()];
                
                // Se o erro não estiver traduzido, retorna o do banco
                if($erro == "") {
                    $erro = str_replace("'", "\"", $e->getMessage());
                }
                
                // Parametro para ser gerada a mensagem
                $msg = array("msg"    => array($erro), 
                             "titulo" => "ERRO",
                             "tipo"   => 3);
                
    			$mensagemSistema->send(serialize($msg));
    			
		    }
		    
            return false;
		}
        
    }
    

	/**
     * Updates existing rows.
     *
     * @param  array        $data  Column-value pairs.
     * @param  array|string $where An SQL WHERE clause, or an array of SQL WHERE clauses.
     * @return int          The number of rows updated.
     */
    public function update(array $data, $where="9=9") {

    	// Define a operação na view
		$this->_view->operacao = "editar";

	    try {
	        // Define a variável de retorno
            $retorno = false;
	        
	    	// Protege a atualização caso não tenha uma codição definida pelo usuário
    		// No método addWhere
    		if ($where == '9=9') {
    			$where = "";
    		}
			
			// Remove a condição de verdade da alteração
			$where = str_replace("1=1", "", $where);

	    	// Insere na tabela privilegios os metodos
			if ($retorno = parent::update($data, $where)) {
			    
			    if($this->showMessage) {
			        
    				// Pega do registro a fila
    				$mensagemSistema = Zend_Registry::get("mensagemSistema");
    				
        			// Parametro para ser gerada a mensagem
        			$msg = array("msg"    => array("Operação realizada."), 
    						     "titulo" => "SUCESSO",
    							 "tipo"   => 2);
        			
        			// Registra a mensagem do sistema
    				$mensagemSistema->send(serialize($msg));
    				
			    }
	
			} else {
			    
			    if($this->showMessage) {
			        
    				// Pega do registro a fila
    				$mensagemSistema = Zend_Registry::get("mensagemSistema");
        	        
    				// Parametro para ser gerada a mensagem
        			$msg = array("msg"    => $this->getValidationMessages(), 
    						     "titulo" => "ERRO",
    							 "tipo"   => 3);
        	        
        			// Registra a mensagem do sistema
    				$mensagemSistema->send(serialize($msg));
    				
			    }
			    
			}

			return $retorno;

		} catch (Zend_Exception $e) {
		    
		    if($this->showMessage) {
    			
		        // Pega do registro a fila			
    			$mensagemSistema = Zend_Registry::get("mensagemSistema");
        		
    			// Captura o erro do sistema
                $erro = $this->_codeError[$e->getCode()];
                
    		    // Se o erro não estiver traduzido, retorna o do banco
                if($erro == "") {
                    $erro = str_replace("'", "\"", $e->getMessage());
                }
                
                // Parametro para ser gerada a mensagem
                $msg = array("msg"    => array($erro), 
                             "titulo" => "ERRO",
                             "tipo"   => 3);
                
        		// Registra a mensagem do sistema
    			$mensagemSistema->send(serialize($msg));
    			
		    }
		    
            return false;
		}
    }

    /**
     * Deletes existing rows.
     *
     * @param  array|string $where SQL WHERE clause(s).
     * @return int          The number of rows deleted.
     */
    public function delete($where="9=9") {

    	// Define a operação na view
		$this->_view->operacao = "novo";

    	try {
    	    // Define a variável de retorno
            $retorno = false;
    	    
    		// Protege a exclusao caso não tenha uma codição definida pelo usuário
    		// No método addWhere
    		if (trim($where) == "9=9") {
    			$where = "";
    		}
			
			// Remove a condição de verdade da exclusão
			$where  = str_replace("1=1", "", $where);
            
    		// Verifica se o atributo "_logicalDelete" foi setado na classe que 
    		// gerencia a tabela, caso exista, não remove fisicamente a linha, 
    		// apenas marca a mesma como excluída. 
    		if($this->_logicalDelete === true) {
    		    
    		    // Atualiza a linha passada pela condição,
    		    // setando a flag para remoção lógica
                if ($retorno = parent::update(array("FL_EXCLUIDO" => "1"), $where)) {
                    
                    if($this->showMessage) {
                        
                        // Pega do registro a fila
                        $mensagemSistema = Zend_Registry::get("mensagemSistema");
                        
                        // Parametro para ser gerada a mensagem
                        $msg = array("msg" => array("Operação realizada."), 
                                     "titulo" => "SUCESSO",
                                     "tipo"   => 2);
                        // Registra a mensagem do sistema
                        $mensagemSistema->send(serialize($msg));
                        
                    }
                }
    		    
    		} else {
				
    	    	// Remove a linha passada pela condição
    			if ($retorno = parent::delete($where)) {
    			    
    			    if($this->showMessage) {
    			        
        				// Pega do registro a fila
        				$mensagemSistema = Zend_Registry::get("mensagemSistema");
        				
            			// Parametro para ser gerada a mensagem
            			$msg = array("msg" => array("Operação realizada."), 
        						     "titulo" => "SUCESSO",
        							 "tipo"   => 2);
            			// Registra a mensagem do sistema
        				$mensagemSistema->send(serialize($msg));
        				
    			    }
    
    			} else {
    				
    			    if($this->showMessage) {
    			        
        				$mensagemSistema = Zend_Registry::get("mensagemSistema");
            	
            			$msg = array("msg" => $this->getValidationMessages(), 
        						     "titulo" => "ERRO",
        							 "tipo"   => 3);
            	
        				$mensagemSistema->send(serialize($msg));
        				
    			    }	

    			}
    			
    		}

			return $retorno;

		} catch (Zend_Exception $e) {
			
		    if($this->showMessage) {
    		    
		        // Pega do registro a fila
    			$mensagemSistema = Zend_Registry::get("mensagemSistema");
    			
    			// Captura o erro do sistema
                $erro = $this->_codeError[$e->getCode()];
                
    		    // Se o erro não estiver traduzido, retorna o do banco
                if($erro == "") {
                    $erro = str_replace("'", "\"", $e->getMessage());
                }
    			
        		// Parametro para ser gerada a mensagem
        		$msg = array("msg"    => array($erro), 
    						 "titulo" => "ERRO",
    						 "tipo"   => 3);
        		// Registra a mensagem do sistema
    			$mensagemSistema->send(serialize($msg));
    			
		    }
		    
            return false;
		}
    }


	/**
	 *
	 * Adiciona os parametros para montar a condição WHERE da consulta
	 *
	 * @param array 	$params		Campo e valor da condição WHERE
	 * @param string 	$operador	Operador lógio do WHERE [AND/OR]
	 * @param boolean 	$condicao 	Se [FALSE] e o valor do campo esteja vazio seta "1=1" na condição WHERE
	 * @return object
	 */
	public function addWhere($params = array(), $operador = "AND", $condicao = false, $parenteses = "") {
		// Operador estiver null seta com valor padrão
		if (is_null($operador)){
			$operador = "AND";
		}
        
		$this->_params[] = array($params, $operador, $condicao, $parenteses);

		return $this;

	}

	/**
	 * Retorna uma string com a expressão WHERE para consulta
	 * caso não tenha parametros sempre retornara uma condição verdadeira [1=1]
	 *
	 * @return string	(String formatada para uma condição WHERE de uma SQL)
	 */
	public function getWhere() {
		
	    $where = "";

		// Verifica se foi passado algum parametro no array
		if (count($this->_params) > 0){

			foreach($this->_params as $parametro) {

				// Retorna os valores a serem formatados para consulta
				$campos   = $parametro[0];

				// Retorna o operador lógico
				$operador = $parametro[1];

				// Retorna a condição que define se o valor do campo é obrigatório
				$condicao = $parametro[2];
				
				// Verifica se a condição foi passada parênteses
				$parenteses = $parametro[3];
                
				// Seta o campo como array
				if (! is_array($campos)) {
					$campos = array($campos);
				}
				
				// Percorre cada valor e concatena com o operador
				foreach ($campos as $chave => $valor) {
                    
				    // Zera a variável
				    $montaWhere = "";
					
					// Verifica se foi passado valor no campo
					if($valor != "") {
						
						// Concatena com o operador a cada iteração
						if($where != "") {
							$where .= " " . $operador . " ";
						}
                        
						// Se passou parenteses de abertura
						if($parenteses !== "") {
							if($parenteses == "(") {
								$where .= $parenteses;
							}
						}
						
						// Verifica se foi passado um array
						if(! is_array($valor)) {
							
							// Se encontrar o asterisco na expressão
							// converte ele por porcento e substitui
							// qualquer operação igual por like
							if(strpos($valor, "*") !== false) {
								// Se encontrar alguma dessas duas letras na chave
								// faz o processo de substituição
								if(strpos($chave, "=") !== false || strpos(strtoupper($chave), "LIKE") !== false) {
									// Converte o igual por like
									$chave = str_replace("=", " like ", $chave);
							
									// Converte o asterisco para porcento
									$valor = str_replace("*", "%", $valor);
							
								} else {
									// Converte o asterisco para nada
									$valor = str_replace("*", "", $valor);
								}
							}
						} 
						
						
						// Se a chave for numérica é por que a expressão foi passada toda no valor
						if (is_numeric($chave)) {
							$montaWhere .= $valor;
							
						} else {

							// Caso tenha uma INTERROGAÇÃO aplica-se o metodo quoteInto
							if(strpos(strtolower($chave), "?") !== false) {
							    
							    // Verifica se na chave não tem a clausula "in", ao qual 
							    // especifica que a busca será feito dentro de um array
							    if(strpos(strtoupper($chave), " IN ") !== false) {
							        
							        // Remove a interrogação
                                    $chave = str_replace("?", "", $chave);
							        
							        // Verifica se foi passado um array
							        if(is_array($valor) && count($valor) > 0) {
    							        $valsArray = "";
    							        $vConta    = 0;
    							        
    							        // Percorre cada valor e monta a clausula "in"
    							        foreach($valor as $iV => $vV) {
    							            // Vai concatenando os valores
    							            if($vConta > 0) {
    							                $valsArray .= ",";
    							            }
    							            
    							            // Concatena o valor aos demais
											if($vV != "") {
												$valsArray .= "'" . $vV . "'";
												
												$vConta++;
												
											} else {
												$valsArray = "";
												break;
											}
    							        }
    							        
							        } else {
							            $valsArray = $valor;
							        }
							        
							        // Recebe o valor do where
									if($valsArray != "") {
										if(strpos($valsArray, "(") !== false) {
											$montaWhere = $chave . $valsArray;
										} else {
											$montaWhere = $chave . " (" . $valsArray . ")";
										}
									} else {
										$montaWhere = "1=1";
									}
									
								} else if(strpos(strtoupper($chave), " BETWEEN ") !== false) {
							        
							        // Verifica se foi passado um array
							        if(is_array($valor) && count($valor) > 0) {
    							        $valsArray = "";
    							        $vConta    = 0;
										
										// Remove a interrogação
										$chave = str_replace("?", "", $chave);
    							        
    							        // Percorre cada valor e monta a clausula "between"
    							        foreach($valor as $iV => $vV) {
    							            // Vai concatenando os valores
    							            if($vConta > 0) {
    							                $valsArray .= " AND ";
    							            }
    							            
    							            // Concatena o valor aos demais
											if($vV != "") {
												if (is_numeric($vV)) {
													$valsArray .= $vV;
												} else {
													$valsArray .= "'" . $vV . "'";
												}
												
												$vConta++;
												
											} else {
												$valsArray = "";
												break;
											}
    							        }
										
										// Recebe o valor do where
										if($valsArray != "") {
											$montaWhere = $chave . " " . $valsArray;
										} else {
											$montaWhere = "1=1";
										}
    							            							        
							        } else if ($valor != "") {
							            $montaWhere = $this->getAdapter()->quoteInto($chave, $valor);
										
							        } else {
										"1=1";
									}
							        
							    } else if ($valor != "") {
						            $montaWhere = $this->getAdapter()->quoteInto($chave, $valor);
									
								} else if($condicao === false) {
									$montaWhere = "1=1";
									
								} else {
									$montaWhere = "";
								}
								
							} else {
								// Caso encontrar algum operador listado,
								if(strpos($chave, "=") !== false || strpos($chave, ">") !== false ||
								   strpos($chave, "<") !== false || strpos($chave, "<>") !== false ||
								   strpos(strtoupper($chave), "LIKE") !== false || 
								   (strpos(strtoupper(trim($chave)), " IN") && substr(strtoupper(trim($chave)), -1) == "N")) {
                                    
								    // Verifica se foi passado um array
                                    if(is_array($valor)) {
								        $valsArray = "(";
                                        $vConta    = 0;
                                        
                                        // Percorre cada valor e monta a clausula "in" 
                                        foreach($valor as $iV => $vV) {
                                            // Vai concatenando os valores
                                            if($vConta > 0) {
                                                $valsArray .= ",";
                                            }
                                            
                                            // Concatena o valor aos demais
                                            $valsArray .= "'" . $vV . "'";
                                            
                                            $vConta++;
                                        }
                                        
                                        $valsArray .= ")";
                                        
                                        // Recebe o valor do where
                                        $montaWhere = $chave . " " . $valsArray;
                                        
                                    } else if (is_numeric($valor) || is_double($valor)) {
										$montaWhere = $chave . " " . $valor;
										
									} else {
										$montaWhere = $chave . " '" . $valor . "'";
									}

								} else {

									if (is_numeric($valor)) {
										$montaWhere = $chave . " = " . $valor;
									} else {
										$montaWhere = $chave . " = '" . $valor . "'";
									}

								}
							}
						}

						// Concatena a expressão do WHERE do SQL
						if (! empty($montaWhere)) {
							$where .= "(" . $montaWhere . ")";
						}
						
						// Se passou parenteses de abertura
						if($parenteses !== "") {
							if($parenteses == ")") {
								$where .= $parenteses;
							}
						}
						
					} else {
						
						if($condicao === false) {
							// Concatena com o operador a cada iteração
							if(! empty($where)) {
								$where .= " " . $operador . " ";
							}
							
							// Se passou parenteses de abertura
							if($parenteses !== "") {
								if($parenteses === "(") {
									$where .= $parenteses;
								}
							}

							$montaWhere = "1=1";

							$where .= $montaWhere;
						}
						
						// Se passou parenteses de abertura
						if($parenteses !== "") {
							if($parenteses === ")") {
								$where .= $parenteses;
							}
						}

					}

				}
			}

		} else {

			if($condicao === false) {
				$montaWhere = "1=1";
			}

			$where = $montaWhere;
		}

		// Limpa os parametros
		$this->_params = array();

		// Retorna a condição
		return $where;
	}

	
	/**		
     * Retorna um array para montar um combo baseado em uma Zend_Db_Select     
     *	
     * @return array array("indice"=>"valor")
     */
    public function getCombo($select=null, $campos=array(), $order=null, $max=null) {

    	try {
    		
    		if ($order == null) {
    			$order = "{$campos["DESCRICAO"]} ASC";
    		}
    		
    		if ($max == null){
    			$max   = 1000;
    		}
    		
			// Busca os dados da query
			// fetchAll($select,$order,rowCount)	*rouncount = nro de registros q vai trazer
			$todos = $this->fetchAll($select, $order, $max);
			$dados = array();
	        foreach($todos as $linha) {
            	$dados[$linha->$campos["CODIGO"]] = $linha->$campos["DESCRICAO"];            	
	        }
	        
	        return $dados;

    	} catch (Exception $e) {
    		echo $e->getMessage();
    	}
    }
	
	
	
	/**
	 * Retorna as mensagens de validação
	 *
	 * @return array string
	 */
	public function getValidationMessages() {
		return $this->_validationMessages;
	}
	
	/**
	 * Seta se a mensagem irá aparecer ou não
	 *
	 * @param boolean $showMessage
	 */
	public function setShowMessage($showMessage = true) {
		$this->showMessage = $showMessage;
	}
	
	/**
	 * Retorna um boleano que informa se a mensagem irá ser mostrada ou não
	 *
	 * @return boolean $showMessage
	 */
	public function getShowMessage() {
		return $this->showMessage;
	}
	

	/**
     * Monta as mensagems que serão mostradas na view
     * método estático
     *
     * @param Array $mensagens
     * @param string $titulo
     * @param int $tipo
     * @return string
     */
    public static function montaMensagemSistema($mensagens=array(), $titulo="Mensagem do Sistema", $tipo=1) {
        $mensagem  = "<div id='mensagemSistema'>";
        switch($tipo) {
            case 1 : $mensagem  = "<div class='mensagemSistema' id='mensagemSistema'>"; break;
            case 2 : $mensagem  = "<div class='mensagemSistema' id='mensagemSucesso'>"; break;
            case 3 : $mensagem  = "<div class='mensagemSistema' id='mensagemErro'>";    break;
            case 4 : $mensagem  = "<div class='mensagemSistema' id='mensagemAlerta'>";  break;
        }
        $mensagem .= "<div class='tituloMensagemSistema'>$titulo</div>";
        $mensagem .= "<ul>";
        for($i=0; $i < count($mensagens); $i++) {
            $mensagem .= "<li>{$mensagens[$i]}</li>";
        }
        $mensagem .= "</ul>";
        $mensagem .= "</div>";

        return $mensagem;
    }
    
}