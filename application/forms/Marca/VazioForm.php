<?php
include_once(APPLICATION_PATH . "/forms/Marca/BaseFormAbstract.php");
/**
 * Classe auxiliar para criação de formulário em tempo de execução
 * 
 * @author david
 *
 */
class Marca_VazioForm extends BaseFormAbstract {

    /**
     * Construtor da classe
     * 
     * @return void
     */
    public function __construct($name = 'form_vazio', $options = array('disableLoadDefaultDecorators' => true)) { 
        
        // Chama o construtor pai
        parent::__construct($name, $options);
        
    }    
}