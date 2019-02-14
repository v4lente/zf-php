<?php
/**
 * 
 * Esta classe tem como objetivo permitir a inclusão de campos BLOB no banco
 *
 * @filesource
 * @author			Márcio Souza Duarte
 * @copyright		Copyright 2009 Marca
 * @package			zendframework
 * @subpackage		zendframework.library.marca
 * @version			1.0
 */
class Marca_Db_Adapter_Oracle extends Zend_Db_Adapter_Oracle {
    
    /**
     * @var boolean
     */
    protected $_transactionOpen = null;

    /**
     * Identifies whether the adapter has an open transaction
     *
     * @return boolean
     */
    protected function _hasOpenTransaction()
    {
        return $this->_transactionOpen;
    }
    
    /**
     * Leave autocommit mode and begin a transaction.
     *
     * Overloads Zend_Db_Adapter_Oracle::_beginTransaction to
     * track the open transaction
     *  
     * @return void
     */
    protected function _beginTransaction()
    {
        // Do the parent code
        parent::_beginTransaction();

        $this->_transactionOpen = true;       
    }

    /**
     * Commit a transaction and return to autocommit mode.
     *
     * Overloads Zend_Db_Adapter_Oracle::_commit to track
     * the open transaction
     * 
     * @return void
     * @throws Zend_Db_Adapter_Oracle_Exception
     */
    protected function _commit()
    {
        $this->_transactionOpen = false;

        // Do the parent code
        parent::_commit();        
    }

    /**
     * Roll back a transaction and return to autocommit mode.
     *
     * Overloads Zend_Db_Adapter_Oracle::_rollBack to track
     * the open transaction
     * 
     * @return void
     * @throws Zend_Db_Adapter_Oracle_Exception
     */
    protected function _rollBack()
    {
        $this->_transactionOpen = false;
        
        // Do the parent code
        parent::_rollBack();
    }
    
    
    /**
     * Inserts a table row with specified data.
     *
     * Oracle does not support anonymous ('?') binds.
     *
     * @param mixed $table The table to insert data into.
     * @param array $bind Column-value pairs.
     * @return int The number of affected rows.
     */
    public function insert($table, array $bind)
    {
        // Use transaction management rather than commit on success
        $transactionOpenedHere = false;
        if (!$this->_hasOpenTransaction()) {
            $this->beginTransaction();
            $transactionOpenedHere = true;
        }
        $tb = explode(".", $table);
        if(count($tb) == 2)
        {
            $tbName = $tb[1];
            $tbSchema = $tb[0];
        }else
            $tbName = $tb[0];
        // Get the table metadata
        $columns = $this->describeTable($tbName, @$tbSchema);

        // Check the columns in the array against the database table
        // to identify BLOB (or CLOB) columns
        foreach (array_keys($bind) as $column) {
            if ( in_array($columns[$column]['DATA_TYPE'], array('BLOB', 'CLOB'))) {
                $lobs[]=$column;
            }
        }
        
        // If there are no blob columns then use the normal insert procedure
        if ( !isset($lobs)) {
            $result = parent::insert($table, $bind);

        } else {
            // There are blobs in the $bind array so insert them separately
            $ociTypes = array('BLOB' => OCI_B_BLOB, 'CLOB' => OCI_B_CLOB);

            // Extract and quote col names from the array keys
            $i = 0;
            $cols = array();
            $vals = array();
            foreach ($bind as $col => $val) {
                $cols[] = $this->quoteIdentifier($col, true);
                if (in_array($col, $lobs)) {
                    $vals[] = 'EMPTY_' . $columns[$col]['DATA_TYPE'] . '()';
					$dataGetContents = ($val == '') ? null : @file_get_contents($val);
                    $lobData[':'.$col.$i] = array('ociType' => $ociTypes[$columns[$col]['DATA_TYPE']],
                                                  'data'    => $dataGetContents );
                    unset($bind[$col]);
                    $lobDescriptors[':'.$col.$i] = oci_new_descriptor($this->_connection, OCI_D_LOB);
                    $returning[] = ':'.$col.$i;
                    $bind[':'.$col.$i] = $lobDescriptors[':'.$col.$i];
                } elseif ($val instanceof Zend_Db_Expr) {
                    $vals[] = $val->__toString();
                    unset($bind[$col]);
                } else {
                    $vals[] = ':'.$col.$i;
                    unset($bind[$col]);
                    $bind[':'.$col.$i] = $val;
                }
                $i++;
            }

            // build the statement
            $sql = "INSERT INTO "
                 . $this->quoteIdentifier($table, true)
                 . ' (' . implode(', ', $cols) . ') '
                 . 'VALUES (' . implode(', ', $vals) . ') '
                 . 'RETURNING ' . implode(', ', $lobs) . ' '
                 . 'INTO '  . implode(', ', $returning);

                 
            // Execute the statement
            $stmt = new Zend_Db_Statement_Oracle($this, $sql);
            foreach (array_keys($bind) as $name) {
                if (in_array($name, array_keys($lobData))) {
                    $stmt->bindParam($name, $bind[$name], $lobData[$name]['ociType'], -1);
                } else {
                    $stmt->bindParam($name, $bind[$name]);
                }
            }

            //Execute without committing
            $stmt->execute();
            $result = $stmt->rowCount();
            
            // Write the LOB data & free the descriptor
            foreach ( $lobDescriptors as $name => $lobDescriptor) {
                $lobDescriptor->write($lobData[$name]['data']);
                $lobDescriptor->flush(); 
                $lobDescriptor->free();
            }

        }

        // Commit
        if ($transactionOpenedHere) {
            $this->commit();
        }

        // Return result
        return $result;

    }
    
    
	/**
     * 
     * Updates table rows with specified data based on a WHERE clause.
     *
     * @param  mixed        $table The table to update.
     * @param  array        $bind  Column-value pairs.
     * @param  mixed        $where UPDATE WHERE clause(s).
     * @return int          The number of affected rows.
     */
    public function update($table, array $bind, $where = '')
    {
       // Use transaction management rather than commit on success
        $transactionOpenedHere = false;
        if (!$this->_hasOpenTransaction()) {
            $this->beginTransaction();
            $transactionOpenedHere = true;
        }
        $tb = explode(".", $table);
        if(count($tb) == 2)
        {
            $tbName = $tb[1];
            $tbSchema = $tb[0];
        }else
            $tbName = $tb[0];
        // Get the table metadata
        $columns = $this->describeTable($tbName, @$tbSchema);

        // Check the columns in the array against the database table
        // to identify BLOB (or CLOB) columns
        foreach (array_keys($bind) as $column) {
            if ( in_array($columns[$column]['DATA_TYPE'], array('BLOB', 'CLOB'))) {
                $lobs[]=$column;
            }
        }

        // If there are no blob columns then use the normal insert procedure
        if ( !isset($lobs)) {
            $result = parent::update($table, $bind, $where);

        } else {
            // There are blobs in the $bind array so insert them separately
            $ociTypes = array('BLOB' => OCI_B_BLOB, 'CLOB' => OCI_B_CLOB);

            /**
             * Build "col = ?" pairs for the statement,
             * except for Zend_Db_Expr which is treated literally.
             */
            $set = array();
            $i = 0;
            foreach ($bind as $col => $val) {
                if (in_array($col, $lobs))
                {
                    $dataGetContents = ($val == '') ? null : @file_get_contents($val);
					$lobData[':'.$col.$i] = array('ociType' => $ociTypes[$columns[$col]['DATA_TYPE']],
                                                  'data'    => $dataGetContents);
                    unset($bind[$col]);
                    $lobDescriptors[':'.$col.$i] = oci_new_descriptor($this->_connection, OCI_D_LOB);
                    $returning[] = ':'.$col.$i;
                    $bind[':'.$col.$i] = $lobDescriptors[':'.$col.$i];
                    $val = 'EMPTY_' . $columns[$col]['DATA_TYPE'] . '()';
                    
                }elseif ($val instanceof Zend_Db_Expr) {
                    $val = $val->__toString();
                    unset($bind[$col]);
                } else {
                    if ($this->supportsParameters('positional')) {
                        $val = '?';
                    } else {
                        if ($this->supportsParameters('named')) {
                            unset($bind[$col]);
                            $bind[':'.$col.$i] = $val;
                            $val = ':'.$col.$i;
                            $i++;
                        } else {
                            /** @see Zend_Db_Adapter_Exception */
                            require_once 'Zend/Db/Adapter/Exception.php';
                            throw new Zend_Db_Adapter_Exception(get_class($this) ." doesn't support positional or named binding");
                        }
                    }
                }
                $set[] = $this->quoteIdentifier($col, true) . ' = ' . $val;
            }

            $where = $this->_whereExpr($where);

            /**
             * Build the UPDATE statement
             */
            $sql = "UPDATE "
                 . $this->quoteIdentifier($table, true)
                 . ' SET ' . implode(', ', $set)
                 . (($where) ? " WHERE $where" : ''). ' '
                 . 'RETURNING ' . implode(', ', $lobs) . ' '
                 . 'INTO '  . implode(', ', $returning);

            // Execute the statement
            $stmt = new Zend_Db_Statement_Oracle($this, $sql);
            foreach (array_keys($bind) as $name) {
                if (in_array($name, array_keys($lobData))) {
                    $stmt->bindParam($name, $bind[$name], $lobData[$name]['ociType'], -1);
                } else {
                    $stmt->bindParam($name, $bind[$name]);
                }
            }

            //Execute without committing
            $stmt->execute();
            $result = $stmt->rowCount();

            // Write the LOB data & free the descriptor
            foreach ( $lobDescriptors as $name => $lobDescriptor) {            	
            	$lobDescriptor->write($lobData[$name]['data']);
                $lobDescriptor->free();
            }

        }

        // Commit
        if ($transactionOpenedHere) {
            $this->commit();
        }

        // Return result
        return $result;
    }
    
}
