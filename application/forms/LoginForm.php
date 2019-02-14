<?php

include_once(APPLICATION_PATH . "/forms/Marca/BaseFormAbstract.php");

class LoginForm extends BaseFormAbstract {
    

    protected $_standardElementDecorator = array(
        'Errors',
        'ViewHelper',
        'Description',
        array('Label'),
        array('HtmlTag', array('tag'=>'li', 'class' => 'form-row'))
    );


    protected $_standardElementFileDecorator = array(
        'Errors',
        'File',
        'Description',
        array('Label'),
        array('HtmlTag', array('tag'=>'li'))
    );

    protected $_buttonElementDecorator = array(
        'ViewHelper',
        array('HtmlTag', array('tag'=>'li', 'class' => 'form-row'))
    );

    protected $_standardGroupDecorator = array(
        'FormElements',
        array('HtmlTag', array('tag' => 'ul')),
        'Fieldset'
    );
    
    protected $_buttonGroupDecorator = array(
        'FormElements',
        array('HtmlTag', array('tag' => 'ul')),
        array('Fieldset', array('class' => 'elements-group')),
    );

    protected $_noElementDecorator = array(
        'ViewHelper'
    );
    
    /**
     * Construtor da classe
     * 
     * @return void
     */
    public function __construct($options = null) { 
        
    	parent::__construct();
    	
        // Cria os elementos do formulï¿½rio
        $usuario = new Zend_Form_Element_Text('usuario');
        $usuario->setLabel('Usuário')
                ->setRequired(true)
                ->addValidator('NotEmpty')
                ->setDecorators($this->_standardElementDecorator);

        $senha = new Zend_Form_Element_Password('senha');
        $senha->setLabel('Senha')
              ->setRequired(true)
              ->addValidator('NotEmpty')
              ->setDecorators($this->_standardElementDecorator);
             
        $submit = new Zend_Form_Element_Submit('enviar');
        $submit->setLabel('logar')
               ->setDecorators($this->_buttonElementDecorator);
        
        $reset = new Zend_Form_Element_Reset('cancelar');
        $reset->setLabel('cancelar')
              ->setDecorators($this->_buttonElementDecorator);
        
        // Adiciona os elementos no formulï¿½rio
        $this->addElements(array($usuario, $senha, $submit, $reset));
        
        // Agrupa os elementos do formulï¿½rio
        $this->addDisplayGroup(array('usuario', 'senha'), 'elementGroup',
            array('decorators' => $this->_standardGroupDecorator)
        ); 
        
        $this->addDisplayGroup(array('enviar', 'cancelar'), 'submitGroup',
            array('decorators' => $this->_buttonGroupDecorator)
        );
    } 
}
