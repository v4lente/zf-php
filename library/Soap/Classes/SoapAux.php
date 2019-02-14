<?php

require_once LIBRARY_PATH . '/Soap/Classes/Object2XML.php';
require_once LIBRARY_PATH . '/Soap/Classes/Array2XML.php';

/*
 * Classe auxiliar responsavel pela autenticacao e outras funcoes necessarias para 
 * trabalhar com as classes soap.
 * 
 */
class SoapAux {
    
    // Objeto com os dados do usuario autenticado
	protected $_autenticacao = null;
    
    /**
	 *
	 * Metodo responsavel por autenticar o usuario interno para insercao
     * dos dados no porto.
     *
	 * @param  String  $usuario
     * @param  String  $senha
     * @return Boolean
     */
    public function authenticate($usuario, $senha) {
        
		// Captura a sessao
        $sessao = new Zend_Session_Namespace('portoweb_soap');
		
        // Inicializa a classe de autenticacao
		$this->_autenticacao = Marca_Auth::getInstance();
        
        // Passa o login e a senha para logar no banco
        $resultado = $this->_autenticacao->authenticate(new Marca_Auth_Adapter_Autentica($usuario, $senha));
        
        $aut = false;
        
        // Retorna o codigo de validacao
        switch($resultado->getCode()) {

            case Marca_Auth_Result::FAILURE_CREDENTIAL_INVALID:
            case Marca_Auth_Result::FAILURE_ACCOUNT_USER_LOCKED:
            case Marca_Auth_Result::FAILURE_UNCATEGORIZED:
                                
                // Remove o usuario da sessao
                $this->_autenticacao->clearIdentity();
								
                break;						

            default: 
                
                // Instancia o usuario
                $usuario       = new UsuarioModel();
                $usuarioLinha  = $usuario->find(strtoupper($this->_autenticacao->getIdentity()));
                $usuarioLogado = $usuarioLinha->current();
                
                // Captura o adaptador padrao
                $db = Zend_Registry::get('db');

                // Altera algumas sessoes do ORACLE
                $db->query("ALTER SESSION SET NLS_COMP = 'LINGUISTIC'");
                $db->query("ALTER SESSION SET NLS_SORT = 'BINARY_AI'");
                $db->query("ALTER SESSION SET NLS_DATE_FORMAT = 'DD/MM/YYYY HH24:MI:SS'");
                
                $aut = true;
                
                break;
        }
        
        return $aut;
    }
	
	/**
	 * Retorna a autenticacao
	 * 
	 * return Marca_Auth_Adapter_Autentica
	 */
	public function getAuth() {
		return $this->_autenticacao->getIdentity();
	}
	
	/**
	 *
     * Metodo responsavel por desautenticar o usuario.
     *
	 * @param  String  $usuario
     * @param  String  $senha
     * @return void
     */
    public function closeConnection($usuario, $senha) {
		
		// Captura a conexao com o banco de dados
		$db = Zend_Registry::get('db');
		
		// Mata a conexao do banco
		$db->closeConnection();
		
		// Remove o usuario da sessao
		$this->_autenticacao->clearIdentity();
		
	}
	
	/**
	 *
     * Metodo responsavel por filtrar os erros.
     *
	 * @param  Object  $error
     * @return string
     */
	public function libxml_display_error($error) { 
		
		$retorno = ""; 
		switch ($error->level) { 
			case LIBXML_ERR_WARNING: 
			$retorno .= "Warning: $error->code."; 
			break; 
			
			case LIBXML_ERR_ERROR: 
			$retorno .= "Error: $error->code."; 
			break; 
			
			case LIBXML_ERR_FATAL: 
			$retorno .= "Fatal Erro: $error->code."; 
			break; 
		} 
		
		$retorno .= trim($error->message); 
		/*
		if ($error->file) { 
			$retorno .= " in $error->file"; 
		} else {
			$retorno .= " on line $error->line\n"; 
		}
		*/
		return $retorno; 
	} 
	
	/**
	 *
     * Metodo responsavel por montar os erros.
     *
     * @return string
     */
	public function libxml_display_errors() { 
	
		$errors = libxml_get_errors(); 
		$retorno = "";
		foreach ($errors as $error) { 
			$retorno .= $this->libxml_display_error($error); 
		} 
		
		// Limpa os erros da memoria
		libxml_clear_errors();
		
		// Retorna os erros
		return $retorno;
	} 
	
	/**
	 * Converte o xml em array
	 *
	 * @return array
	 */
	public function xmlToArray($xml, $options = array()) {
		$defaults = array(
			'namespaceSeparator' 	=> ':',		//you may want this to be something other than a colon
			'attributePrefix' 		=> '@',   	//to distinguish between attributes and nodes with the same name
			'alwaysArray' 			=> array(), //array of xml tag names which should always become arrays
			'autoArray' 			=> true,    //only create arrays for tags which appear more than once
			'textContent' 			=> '$',     //key used for the text content of elements
			'autoText' 				=> true,    //skip textContent key if node has no attributes or child nodes
			'keySearch' 			=> false,   //optional search and replace on tag and attribute names
			'keyReplace' 			=> false    //replace values for above search values (as passed to str_replace())
		);
		$options = array_merge($defaults, $options);
		$namespaces = $xml->getDocNamespaces();
		$namespaces[''] = null; //add base (empty) namespace
	 
		//get attributes from all namespaces
		$attributesArray = array();
		foreach ($namespaces as $prefix => $namespace) {
			foreach ($xml->attributes($namespace) as $attributeName => $attribute) {
				//replace characters in attribute name
				if ($options['keySearch']) $attributeName =
						str_replace($options['keySearch'], $options['keyReplace'], $attributeName);
				$attributeKey = $options['attributePrefix']
						. ($prefix ? $prefix . $options['namespaceSeparator'] : '')
						. $attributeName;
				$attributesArray[$attributeKey] = (string)$attribute;
			}
		}
	 
		//get child nodes from all namespaces
		$tagsArray = array();
		foreach ($namespaces as $prefix => $namespace) {
			foreach ($xml->children($namespace) as $childXml) {
				//recurse into child nodes
				$childArray = $this->xmlToArray($childXml, $options);
				list($childTagName, $childProperties) = each($childArray);
	 
				//replace characters in tag name
				if ($options['keySearch']) $childTagName =
						str_replace($options['keySearch'], $options['keyReplace'], $childTagName);
				//add namespace prefix, if any
				if ($prefix) $childTagName = $prefix . $options['namespaceSeparator'] . $childTagName;
	 
				if (!isset($tagsArray[$childTagName])) {
					//only entry with this key
					//test if tags of this type should always be arrays, no matter the element count
                    if(is_array($childArray[$childTagName]) && count($childArray[$childTagName]) == 0) {
                        $tagsArray[$childTagName] = "";
                    } else {
                        //print_r($childArray);
                        $tagsArray[$childTagName] =
                                in_array($childTagName, $options['alwaysArray']) || !$options['autoArray']
                                ? array($childProperties) : $childProperties;
                    }
				} elseif (
					is_array($tagsArray[$childTagName]) && array_keys($tagsArray[$childTagName])
					=== range(0, count($tagsArray[$childTagName]) - 1)) {
					//key already exists and is integer indexed array
					$tagsArray[$childTagName][] = $childProperties;
				} else {
					//key exists so convert to integer indexed array with previous value in position 0
					$tagsArray[$childTagName] = array($tagsArray[$childTagName], $childProperties);
				}
			}
		}
	    //echo "<pre>"; print_r($tagsArray); echo "</pre>";
		//get text content of node
		$textContentArray = array();
		$plainText = trim((string)$xml);
		if ($plainText !== '') $textContentArray[$options['textContent']] = $plainText;
	 
		//stick it all together
		$propertiesArray = !$options['autoText'] || $attributesArray || $tagsArray || ($plainText === '')
				? array_merge($attributesArray, $tagsArray, $textContentArray) : $plainText;
	 
		//return node as array
		return array(
			$xml->getName() => $propertiesArray
		);
	}
    
    /**
     * Valida o esquema xml
     * 
     * @param type $xml
     * @return boolean|string
     */
    public static function validateXML($xml) {
        
        libxml_use_internal_errors(true);
        
        $xml = utf8_encode($xml);

        $doc = new DOMDocument('1.0', 'utf-8');
        $doc->loadXML($xml);

        $errors = libxml_get_errors();
        if (empty($errors)) {
            return true;
        }

        $error = $errors[0];
        if ($error->level < 3) {
            return true;
        }

        $lines = explode("\r", $xml);
        $line = $lines[($error->line)-1];

        $message = $error->message.' at line '.$error->line.': '.htmlentities($line);

        return $message;
    }
    
}

?>
