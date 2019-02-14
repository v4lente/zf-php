<?php
include_once(APPLICATION_PATH . "/forms/Marca/BaseFormAbstract.php");
/**
 * Classe auxiliar para cria��o de formul�rio em tempo de execu��o
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