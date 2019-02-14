<?php

/** 
 * Classe responsavel enviar a requisicao
 */
class Request {
    /** 
     * Numero do Tichet.
     * 
     * @var integer 
     */
    public $nr_ticket;
	
	/** 
     * Placa do Veoculo.
     * 
     * @var string 
     */
    public $pl_veiculo;
	
	/** 
	 * Data/Hora da Pesagem da Tara
     * Formato aaa-mm-ddThh24:mi:ss
     * 
     * @var dateTime 
     */
    public $dthr_peso_tara;
	
	/** 
	 * Data/Hora da Pesagem do Bruto
     * Formato aaa-mm-ddThh24:mi:ss
     * 
     * @var dateTime 
     */
    public $dthr_peso_bruto;
	
	/** 
     * Peso Bruto.
     * 
     * @var integer 
     */
    public $peso_tara;
	
	/** 
     * Peso Tara.
     * 
     * @var integer 
     */
    public $peso_bruto;
}

?>