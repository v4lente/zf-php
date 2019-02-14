<?php

/**
 * Created on 12/02/2010
 *
 * Classe picklist
 *
 * Esta classe recebe os mesmos parâmetros da classe "Select" e
 * monsta a consulta a partir dos dados passados, é ela também que
 * irá montar o formulário dinâmico na tela.
 *
 * @filesource
 * @author			Márcio Souza Duarte/David Valente
 * @copyright		Copyright 2009 Marca
 * @package			zendframework
 * @subpackage		zendframework.library.marca
 * @version			1.0
 */
class Marca_PicklistDb {


	/**
	 * Define os campos do formulario que serão utilizados na pesquisa
	 *
	 * @var array
	 */
	private $_camposFormulario = array();

	/**
	 * Objeto de conexão
	 *
	 * @var Zend_Db
	 */
	private $_db;

	/**
	 * Array com os dados do From da classe Zend_Db_Select
	 *
	 * @var array
	 */
	private $_from = array();

	/**
	 * Array com os dados das colunas retornadas pelo From da classe Zend_Db_Select
	 *
	 * @var array
	 */
	private $_columns = array();

	/**
	 * Array com os dados da union da classe Zend_Db_Select
	 *
	 * @var array
	 */
	private $_union = array();

	/**
	 * Array com os dados da join da classe Zend_Db_Select
	 *
	 * @var array
	 */
	private $_join = array();

	/**
	 * Array com os dados da joinInner da classe Zend_Db_Select
	 *
	 * @var array
	 */
	private $_joinInner = array();

	/**
	 * Array com os dados da joinLeft da classe Zend_Db_Select
	 *
	 * @var array
	 */
	private $_joinLeft = array();

	/**
	 * Array com os dados da joinRight da classe Zend_Db_Select
	 *
	 * @var array
	 */
	private $_joinRight = array();

	/**
	 * Array com os dados da joinFull da classe Zend_Db_Select
	 *
	 * @var array
	 */
	private $_joinFull = array();

	/**
	 * Array com os dados da joinCross da classe Zend_Db_Select
	 *
	 * @var array
	 */
	private $_joinCross = array();

	/**
	 * Array com os dados da joinNatural da classe Zend_Db_Select
	 *
	 * @var array
	 */
	private $_joinNatural = array();

	/**
	 * Array com os dados da where da classe Zend_Db_Select
	 *
	 * @var array
	 */
	private $_where = array();

	/**
	 * Array com os dados da orWhere da classe Zend_Db_Select
	 *
	 * @var array
	 */
	private $_orWhere = array();

	/**
	 * dados da group da classe Zend_Db_Select
	 *
	 * @var array
	 */
	private $_group;

	/**
	 * dados da having da classe Zend_Db_Select
	 *
	 * @var string
	 */
	private $_having;

	/**
	 * dados da orHaving da classe Zend_Db_Select
	 *
	 * @var string
	 */
	private $_orHaving;

	/**
	 * dados da order da classe Zend_Db_Select
	 *
	 * @var string
	 */
	private $_order;

	/**
	 * array com os dados da limit da classe Zend_Db_Select
	 *
	 * @var array
	 */
	private $_limit = array();

	/**
	 * array com os dados da limitPage da classe Zend_Db_Select
	 *
	 * @var array
	 */
	private $_limitPage = array();




	/**
	 * Construtor
	 *
	 * @param array $config
	 */
	public function __construct($db)
    {
    	$this->_db = $db;
    }


	/**
	 * Método que simula o select da classe Zend_Db
	 *
	 * @return Objeto
	 */
    public function select(){

    	return $this;
    }

    /**
     * Método que simula o from da classe Zend_Db
     *
     * @param string $name
     * @param unknown_type $cols
     * @param string $schema
     */
	public function from($name, $cols = '*', $schema = null, $style = null)
    {
        // Trata as colunas para capturar possíveis expressões passadas
        
        $this->_from = array($name, $cols, $schema, $style);
        return $this;
    }

	/**
     * Specifies the columns used in the FROM clause.
     *
     * The parameter can be a single string or Zend_Db_Expr object,
     * or else an array of strings or Zend_Db_Expr objects.
     *
     * @param  array|string|Zend_Db_Expr $cols The columns to select from this table.
     * @param  string $correlationName Correlation name of target table. OPTIONAL
     * @return Zend_Db_Select This Zend_Db_Select object.
     */
    public function columns($cols = '*', $correlationName = null)
    {
        $this->_columns = array($cols, $correlationName);

    	return $this;
    }

    /**
     * Adds a UNION clause to the query.
     *
     * The first parameter $select can be a string, an existing Zend_Db_Select
     * object or an array of either of these types.
     *
     * @param  array|string|Zend_Db_Select $select One or more select clauses for the UNION.
     * @return Zend_Db_Select This Zend_Db_Select object.
     */
    public function union($select = array(), $type = 'UNION')
    {
        $this->_union = array($select, $type);

    	return $this;
    }

    /**
     * Adds a JOIN table and columns to the query.
     *
     * The $name and $cols parameters follow the same logic
     * as described in the from() method.
     *
     * @param  array|string|Zend_Db_Expr $name The table name.
     * @param  string $cond Join on this condition.
     * @param  array|string $cols The columns to select from the joined table.
     * @param  string $schema The database name to specify, if any.
     * @return Zend_Db_Select This Zend_Db_Select object.
     */
    public function join($name, $cond, $cols = '*', $schema = null)
    {
        $this->_join = array($name, $cond, $cols, $schema);

    	return $this;
    }

    /**
     * Add an INNER JOIN table and colums to the query
     * Rows in both tables are matched according to the expression
     * in the $cond argument.  The result set is comprised
     * of all cases where rows from the left table match
     * rows from the right table.
     *
     * The $name and $cols parameters follow the same logic
     * as described in the from() method.
     *
     * @param  array|string|Zend_Db_Expr $name The table name.
     * @param  string $cond Join on this condition.
     * @param  array|string $cols The columns to select from the joined table.
     * @param  string $schema The database name to specify, if any.
     * @return Zend_Db_Select This Zend_Db_Select object.
     */
    public function joinInner($name, $cond, $cols = '*', $schema = null)
    {
        $this->_joinInner = array($name, $cond, $cols, $schema);

    	return $this;
    }

    /**
     * Add a LEFT OUTER JOIN table and colums to the query
     * All rows from the left operand table are included,
     * matching rows from the right operand table included,
     * and the columns from the right operand table are filled
     * with NULLs if no row exists matching the left table.
     *
     * The $name and $cols parameters follow the same logic
     * as described in the from() method.
     *
     * @param  array|string|Zend_Db_Expr $name The table name.
     * @param  string $cond Join on this condition.
     * @param  array|string $cols The columns to select from the joined table.
     * @param  string $schema The database name to specify, if any.
     * @return Zend_Db_Select This Zend_Db_Select object.
     */
    public function joinLeft($name, $cond, $cols = '*', $schema = null)
    {
        $this->_joinLeft = array($name, $cond, $cols, $schema);

    	return $this;
    }

    /**
     * Add a RIGHT OUTER JOIN table and colums to the query.
     * Right outer join is the complement of left outer join.
     * All rows from the right operand table are included,
     * matching rows from the left operand table included,
     * and the columns from the left operand table are filled
     * with NULLs if no row exists matching the right table.
     *
     * The $name and $cols parameters follow the same logic
     * as described in the from() method.
     *
     * @param  array|string|Zend_Db_Expr $name The table name.
     * @param  string $cond Join on this condition.
     * @param  array|string $cols The columns to select from the joined table.
     * @param  string $schema The database name to specify, if any.
     * @return Zend_Db_Select This Zend_Db_Select object.
     */
    public function joinRight($name, $cond, $cols = '*', $schema = null)
    {
        $this->_joinRight = array($name, $cond, $cols, $schema);

    	return $this;
    }

    /**
     * Add a FULL OUTER JOIN table and colums to the query.
     * A full outer join is like combining a left outer join
     * and a right outer join.  All rows from both tables are
     * included, paired with each other on the same row of the
     * result set if they satisfy the join condition, and otherwise
     * paired with NULLs in place of columns from the other table.
     *
     * The $name and $cols parameters follow the same logic
     * as described in the from() method.
     *
     * @param  array|string|Zend_Db_Expr $name The table name.
     * @param  string $cond Join on this condition.
     * @param  array|string $cols The columns to select from the joined table.
     * @param  string $schema The database name to specify, if any.
     * @return Zend_Db_Select This Zend_Db_Select object.
     */
    public function joinFull($name, $cond, $cols = '*', $schema = null)
    {
        $this->_joinFull = array($name, $cond, $cols, $schema);

    	return $this;
    }

    /**
     * Add a CROSS JOIN table and colums to the query.
     * A cross join is a cartesian product; there is no join condition.
     *
     * The $name and $cols parameters follow the same logic
     * as described in the from() method.
     *
     * @param  array|string|Zend_Db_Expr $name The table name.
     * @param  array|string $cols The columns to select from the joined table.
     * @param  string $schema The database name to specify, if any.
     * @return Zend_Db_Select This Zend_Db_Select object.
     */
    public function joinCross($name, $cols = '*', $schema = null)
    {
        $this->_joinCross = array($name, $cols, $schema);

    	return $this;
    }

    /**
     * Add a NATURAL JOIN table and colums to the query.
     * A natural join assumes an equi-join across any column(s)
     * that appear with the same name in both tables.
     * Only natural inner joins are supported by this API,
     * even though SQL permits natural outer joins as well.
     *
     * The $name and $cols parameters follow the same logic
     * as described in the from() method.
     *
     * @param  array|string|Zend_Db_Expr $name The table name.
     * @param  array|string $cols The columns to select from the joined table.
     * @param  string $schema The database name to specify, if any.
     * @return Zend_Db_Select This Zend_Db_Select object.
     */
    public function joinNatural($name, $cols = '*', $schema = null)
    {
        $this->_joinNatural = array($name, $cols, $schema);

    	return $this;
    }

    /**
	 * Sobrescreve os metodos da classe Zend_Db_Table_Abstract
	 *
     * Adds a WHERE condition to the query by AND.
     *
     * If a value is passed as the second param, it will be quoted
     * and replaced into the condition wherever a question-mark
     * appears. Array values are quoted and comma-separated.
     *
     * <code>
     * // simplest but non-secure
     * $select->where("id = $id");
     *
     * // secure (ID is quoted but matched anyway)
     * $select->where('id = ?', $id);
     *
     * // alternatively, with named binding
     * $select->where('id = :id');
     * </code>
     *
     * Note that it is more correct to use named bindings in your
     * queries for values other than strings. When you use named
     * bindings, don't forget to pass the values when actually
     * making a query:
     *
     * <code>
     * $db->fetchAll($select, array('id' => 5));
     * </code>
     *
     * @param string   $cond    The WHERE condition.
     * @param string   $value   OPTIONAL A single value to quote into the condition.
     * @param constant $type    OPTIONAL The type of the given value
     * @param array    $campo   Parametros que definirão o campo do formulario
     * @param array    $valores Valores do campo do formulario quando (combo, checkbox, radio)
     * @return Zend_Db_Select This Zend_Db_Select object.
     */
    public function where($cond, $value = null, $type = null, $campo=array(), $valores=array())
    {
        if (count($campo) > 0) {
        	$this->setCamposFormulario($campo, $valores);
        }

    	$this->_where[] = array($cond, $value, $type, $campo, $valores);

    	return $this;
    }

    /**
     * Sobrescreve os metodos da classe Zend_Db_Table_Abstract
     *
     * Adds a WHERE condition to the query by OR.
     *
     * Otherwise identical to where().
     *
     * @param string   $cond  The WHERE condition.
     * @param string   $value OPTIONAL A single value to quote into the condition.
     * @param constant $type  OPTIONAL The type of the given value
     * @param array    $campo   Parametros que definirão o campo do formulario
     * @param array    $valores Valores do campo do formulario quando (combo, checkbox, radio)
     * @return Zend_Db_Select This Zend_Db_Select object.
     *
     * @see where()
     */
    public function orWhere($cond, $value = null, $type = null, $campo=array(), $valores=array())
    {
    	if (count($campo) > 0){
        	$this->setCamposFormulario($campo, $valores);
        }

    	$this->_orWhere[] = array($cond, $value, $type, $campo, $valores);

    	return $this;
    }

    /**
     * Adds grouping to the query.
     *
     * @param  array|string $spec The column(s) to group by.
     * @return Zend_Db_Select This Zend_Db_Select object.
     */
    public function group($spec)
    {
        $this->_group = $spec;

        return $this;
    }

    /**
     * Adds a HAVING condition to the query by AND.
     *
     * If a value is passed as the second param, it will be quoted
     * and replaced into the condition wherever a question-mark
     * appears. See {@link where()} for an example
     *
     * @param string $cond The HAVING condition.
     * @param string|Zend_Db_Expr $val A single value to quote into the condition.
     * @return Zend_Db_Select This Zend_Db_Select object.
     */
    public function having($cond)
    {
        $this->_having = $cond;

        return $this;
    }

    /**
     * Adds a HAVING condition to the query by OR.
     *
     * Otherwise identical to orHaving().
     *
     * @param string $cond The HAVING condition.
     * @param string $val A single value to quote into the condition.
     * @return Zend_Db_Select This Zend_Db_Select object.
     *
     * @see having()
     */
    public function orHaving($cond)
    {
        $this->_orHaving = $cond;

        return $this;
    }

    /**
     * Adds a row order to the query.
     *
     * @param mixed $spec The column(s) and direction to order by.
     * @return Zend_Db_Select This Zend_Db_Select object.
     */
    public function order($spec)
    {
        $this->_order = $spec;

        return $this;
    }

    /**
     * Sets a limit count and offset to the query.
     *
     * @param int $count OPTIONAL The number of rows to return.
     * @param int $offset OPTIONAL Start returning after this many rows.
     * @return Zend_Db_Select This Zend_Db_Select object.
     */
    public function limit($count = null, $offset = null)
    {
        $this->_limit = array($count, $offset);

        return $this;
    }

    /**
     * Sets the limit and count by page number.
     *
     * @param int $page Limit results to this page number.
     * @param int $rowCount Use this many rows per page.
     * @return Zend_Db_Select This Zend_Db_Select object.
     */
    public function limitPage($page, $rowCount)
    {
        $this->_limitPage = array($page, $rowCount);

        return $this;
    }

    /**
     * Seta os campos do formulario
     *
     * @param $campo
     * @param $valores
     */
    public function setCamposFormulario($campo=array(), $valores=array()) {

    	$this->_camposFormulario[] = array($campo, $valores);
    }

    /**
     * Retorna os campos do formulario
     *
     * @return array
     */
	public function getCamposFormulario() {

		return $this->_camposFormulario;
	}

	/**
     * Retorna os NOMES dos campos da tabela
     *
     * @return array
     */
	public function getNomesCamposSelect() {

		$campos = $this->_from[1];
		$nomes = array();
		foreach($campos as $indice => $valor){
		  if(is_numeric($indice)) {
                if(strpos(strtoupper($valor), ' AS ') > 0) {
                    $divValor = explode(' AS ', strtoupper($valor));
                    $nomes[]  = str_replace("\"", "", trim($divValor[0]));
                } else {
                    $nomes[] = trim(strtoupper($valor));
                }
            } else {
                $nomes[] = trim(strtoupper($valor));
            }
		}
		
		return $nomes;
	}
	
    /**
     * Retorna os ALIAS dos campos da tabela
     *
     * @return array
     */
    public function getAliasCamposSelect() {

        $campos = $this->_from[1];
        $alias = array();
        foreach($campos as $indice => $valor) {
            if(is_numeric($indice)) {
                if(strpos(strtoupper($valor), ' AS ') > 0) {
                    $divValor = explode(' AS ', strtoupper($valor));
                    $alias[]  = str_replace("\"", "", trim($divValor[1]));
                } else {
                    $alias[] = trim(strtoupper($campo));
                }
            } else {
                $alias[] = trim(strtoupper($indice));
            }
        }
        
        return $alias;
    }

	/**
     * Retorna os STYLES dos campos da tabela
     *
     * @return array
     */
	public function getStyleCamposSelect() {

		$campos = $this->_from[3];
		$estilos = array();
		$contaCampos = 0;
		foreach($campos as $campo){
			$estilos[$contaCampos] = "";

			foreach($campo as $indice => $valor){

				if($indice == 'style'){
					$valor = trim($valor);
					$estilos[$contaCampos] .= (substr($valor, strlen($valor)-1, 1) == ';') ? $valor." " : $valor."; ";
				} else {
					$estilos[$contaCampos] .= $indice.":".$valor."; ";
				}
			}

			$contaCampos++;
		}

		return $estilos;
	}

 	/**
 	 * Monta a consulta que sera executada na picklist
 	 * 
 	 * @param  array  $parametros Valores dos campos da pesquisa
 	 * @return string $select     Zend_Db_Select 
 	 */
	public function executa($parametros=array(), $order=""){

		$select = '';

    	$sql = '$select = $this->_db->select()';

    	if (! empty($this->_from[0])){
    		$sql .= '->from($this->_from[0], $this->_from[1], $this->_from[2])';
    	}

		if (! empty($this->_columns[0])){
    		$sql .= '->columns($this->_columns[0], $this->_columns[1])';
    	}

		if (! empty($this->_union[0])){
    		$sql .= '->union($this->_union[0], $this->_union[1])';
    	}

		if (! empty($this->_join[0])){
    		$sql .= '->join($this->_join[0], $this->_join[1], $this->_join[2], $this->_join[3])';
    	}

		if (! empty($this->_joinInner[0])){
    		$sql .= '->joinInner($this->_joinInner[0], $this->_joinInner[1], $this->_joinInner[2], $this->_joinInner[3])';
    	}

		if (! empty($this->_joinLeft[0])){
    		$sql .= '->joinLeft($this->_joinLeft[0], $this->_joinLeft[1], $this->_joinLeft[2], $this->_joinLeft[3])';
    	}

		if (! empty($this->_joinRight[0])){
    		$sql .= '->joinRight($this->_joinRight[0], $this->_joinRight[1], $this->_joinRight[2], $this->_joinRight[3])';
    	}

		if (! empty($this->_joinFull[0])){
    		$sql .= '->joinFull($this->_joinFull[0], $this->_joinFull[1], $this->_joinFull[2], $this->_joinFull[3])';
    	}

		if (! empty($this->_joinCross[0])){
    		$sql .= '->joinCross($this->_joinCross[0], $this->_joinCross[1], $this->_joinCross[2])';
    	}

		if (! empty($this->_joinNatural[0])){
    		$sql .= '->joinNatural($this->_joinNatural[0], $this->_joinNatural[1], $this->_joinNatural[2])';
    	}

    	// Conta o total de condições passadas para o sql
    	$totalWhere = count($this->_where);
    	// Verifica se foi passada alguma condição para o where
		if ($totalWhere > 0){
			// Pega os nomes dos campos do formulario
			$camposChaves = array_keys($parametros);
			// Total de campos do formulario
			$totalCamposChaves = count($camposChaves);

			// Percorre todas as condições wheres passadas
			for($i=0; $i < $totalWhere; $i++) {

				// Verifica se foi informado o campo a ser pego pelo formulario
				if (count($this->_where[$i][3]) > 0){

					// Percorre todos os campos do formulario
					for ($j=0; $j < $totalCamposChaves; $j++) {

						// Verifica se o campo do formulario existe na condição where passada
						if (strcmp($this->_where[$i][3]['name'], $camposChaves[$j]) == 0){

							// Verifica se tem valor no campo do formulario
							if (! empty($parametros[$camposChaves[$j]])){
								// Substitu o * por %
								$valor = str_replace("*", "%", $parametros[$camposChaves[$j]]);
								 
								// Joga o valor do campo do formulario na condição where
								if(! empty($this->_where[$i][2])) {
								    $sql .= '->where(strtoupper($this->_where['.$i.'][0]), "'.strtoupper($valor).'", $this->_where['.$i.'][2])';
								} else {
								    $sql .= '->where(strtoupper($this->_where['.$i.'][0]), "'.strtoupper($valor).'", null)';
								}
							}
						}
					}

				} else {
					// Seta a condição where se não for passado o campo para formulario
				    if(! empty($this->_where[$i][2])) {
	    			    $sql .= '->where($this->_where['.$i.'][0], $this->_where['.$i.'][1], $this->_where['.$i.'][2])';
				    } else {
				        $sql .= '->where($this->_where['.$i.'][0], $this->_where['.$i.'][1], null)';
				    }
				}
			}
    	}

		// Conta o total de condições passadas para o sql
    	$totalOrWhere = count($this->_orWhere);
    	// Verifica se foi passada alguma condição para o where
		if ($totalOrWhere > 0){
			// Pega os nomes dos campos do formulario
			$camposChaves = array_keys($parametros);
			// Total de campos do formulario
			$totalCamposChaves = count($camposChaves);

			// Percorre todas as condições wheres passadas
			for($i=0; $i < $totalOrWhere; $i++) {

				// Verifica se foi informado o campo a ser pego pelo formulario
				if (count($this->_orWhere[$i][3]) > 0){

					// Percorre todos os campos do formulario
					for ($j=0; $j < $totalCamposChaves; $j++) {

						// Verifica se o campo do formulario existe na condição orWhere passada
						if (strcmp($this->_where[$i][3]['name'], $camposChaves[$j]) == 0){

							// Verifica se tem valor no campo do formulario
							if (! empty($parametros[$camposChaves[$j]])){
								// Substitu o * por %
								$valor = str_replace("*", "%", $parametros[$camposChaves[$j]]);
								// Joga o valor do campo do formulario na condição orWhere
								if(! empty($this->_orWhere[$i][2])) {
								    $sql .= '->orWhere(strtoupper($this->_orWhere['.$i.'][0]), "'.strtoupper($valor).'", $this->_orWhere['.$i.'][2])';
								} else {
								    $sql .= '->orWhere(strtoupper($this->_orWhere['.$i.'][0]), "'.strtoupper($valor).'", null)';
								}
							}
						}
					}

				} else {
					// Seta a condição orWhere se não for passado o campo para formulario
				    if(! empty($this->_orWhere[$i][2])) {
	    			    $sql .= '->orWhere($this->_orWhere['.$i.'][0], $this->_orWhere['.$i.'][1], $this->_orWhere['.$i.'][2])';
				    } else {
				        $sql .= '->orWhere($this->_orWhere['.$i.'][0], $this->_orWhere['.$i.'][1], null)';
				    }
				}
			}
    	}

		if (! empty($this->_group)){
    		$sql .= '->group($this->_group)';
    	}

		if (! empty($this->_having)){
    		$sql .= '->having($this->_having)';
    	}

		if (! empty($this->_orHaving)){
    		$sql .= '->orHaving($this->_orHaving)';
    	}
        
    	if(! empty($order)) {
    	   
    	    if(strpos($order, "_p_") >= 0) {
                $order = str_replace("_p_", ".", $order);
            }
    	    
    	    $sql .= '->order(\'' . $order . '\')';
    	    
    	} elseif (! empty($this->_order)){
    		$sql .= '->order($this->_order)';
    	}

		if (! empty($this->_limit[0])){
    		$sql .= '->limit($this->_limit[0], $this->_limit[1])';
    	}

		if (! empty($this->_limitPage[0])){
    		$sql .= '->limitPage($this->_limitPage[0], $this->_limitPage[1])';
    	}

    	$sql .= ';';
    	
    	try {
    	    // Executa a consulta
    	    eval($sql);
    	   
    	} catch(Exception $e) {
    	    echo $e->getMessage();
    	    $select = '';
    	}
        
    	// Retorna a consulta montada 
    	// pela classe Zend_Db_Select
    	return $select;
    }
    
    /**
     * Retorna a string da consulta.
     *
     * @return string
     */
    public function __toString() {
        
        try {
            // Monta a consulta
            $select = $this->executa();
            
            // Retorna a string da consulta
            $sql    = $select->__toString();
            
        } catch (Exception $e) {
            echo $e->getMessage();
            $sql = '';
        }
        
        // Retorna a string da consulta
        return (string)$sql;
    }

}