<?php

Abstract class BaseFormAbstract extends Zend_Form {

    protected $_standardElementDecorator = array(
        'Errors',
        'ViewHelper',
        'Description',
        array('Label'),
        array('HtmlTag', array('tag'=>'p'))
    );


    protected $_standardElementFileDecorator = array(
        'Errors',
        'File',
        'Description',
        array('Label'),
        array('HtmlTag', array('tag'=>'p'))
    );

    protected $_buttonElementDecorator = array(
        'ViewHelper',
        array('HtmlTag', array('tag'=>'p'))
    );

    protected $_standardGroupDecorator = array(
        'FormElements',        
        array('Fieldset', array('class' => 'fs-central'))
    );
    
    protected $_buttonGroupDecorator = array(
        'FormElements',
        array('Fieldset', array('class' => 'fs-central'))
    );

    protected $_noElementDecorator = array(
        'ViewHelper'
    );
    
    
    /**
     * Construtor da classe
     * 
     * @return void
     */
    public function __construct($name = 'form_base', $options = array('disableLoadDefaultDecorators' => true)) { 
        parent::__construct($options);
        
        $this->setMethod('post');
        $this->setDisableLoadDefaultDecorators(true);
        $this->setName($name);
        $this->setAttrib('accept-charset', 'ISO-8859-1');
        $this->setAttrib('onsubmit', 'return false;');
        $this->setDecorators(array(
            'Description',
            'FormElements',
            'Form'
        )); 
        
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
    
	/**
     * Codifica os caracteres para poder utilizar o picklist
     * 
     * @param String $str
     * @return string
     */
    public function codificaStringPickList($str="") {
        $str = str_replace("'", "_as_", $str);
	    $str = str_replace("\"", "_ad_", $str);
	    $str = str_replace("+", "_mais_", $str);
	    
	    return $str;
    }
    
	/**
     * Decodifica os caracteres para poder utilizar o picklist
     * 
     * @param String $str
     * @return string
     */
    public function decodificaStringPickList($str="") {
        $str = str_replace("_as_", "'", $str);
        $str = str_replace("_ad_", "\"", $str);
        $str = str_replace("_mais_", "+", $str);
        $str = str_replace("\\t",  " ", $str);
        $str = str_replace("\\n",  " ", $str);
        
        return $str;
    }
    
    /**
     * Retorna o decorator para elementos padrï¿½o
     * 
     * @return array
     */
    public function getStandardElementDecorator() {
        return $this->_standardElementDecorator;
    }
    
    /**
     * Retorna o decorator para arquivos padrï¿½o
     * 
     * @return array
     */
    public function getStandardElementFileDecorator() {
        return $this->_standardElementFileDecorator;
    }
    
    /**
     * Retorna o decorator para botï¿½es
     * 
     * @return array
     */
    public function getButtonElementDecorator() {
        return $this->_buttonElementDecorator;
    }
    
    /**
     * Retorna o decorator para grupos de elementos padrï¿½o
     * 
     * @return array
     */
    public function getStandardGroupDecorator() {
        return $this->_standardGroupDecorator;
    }
    
    /**
     * Retorna o decorator para grupos de botï¿½es
     * 
     * @return array
     */
    public function getButtonGroupDecorator() {
        return $this->_buttonGroupDecorator;
    }
    
    /**
     * Retorna elementos sem decorator
     * 
     * @return array
     */
    public function getNoElementDecorator() {
        return $this->_noElementDecorator;
    }
    
}
