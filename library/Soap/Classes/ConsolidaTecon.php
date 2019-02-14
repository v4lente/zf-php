<?php

ini_set("soap.wsdl_cache_enabled", 0);
ini_set('soap.wsdl_cache_ttl', 0);
ini_set('max_execution_time', 180);
ini_set('memory_limit', '128M');

/**
 *
 * Classe responsavel por consolidar os dados do tecon
 * 
 * @category   Marca Sistemas
 * @package    ConsolidaTecon
 * @copyright  Copyright (c) 1991-2015 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 * @version    $Id: MovTecon.php 0 2015-02-10 13:00:00 marcio $
 */
class ConsolidaTecon {
    
	/**
     *
	 * Metodo responsavel por chamar o método de consolidação dos dados do tecon.
	 *
     * @param  Integer  $nr_ped_atrac
	 * @param  Integer  $ano_ped_atrac
	 *
     * @return null
     */
    public function consolidar($nr_ped_atrac, $ano_ped_atrac) {
		
		$db = "(DESCRIPTION = (ADDRESS = (PROTOCOL = TCP)(HOST = 10.76.1.33)(PORT = 1521)) (CONNECT_DATA = (SID = suporte)))";
        
        $conn = @oci_connect('tecon', 't', $db);
        
        if (!$conn) {
            $e = oci_error();
            trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        }
        
        $sql = "BEGIN P_CONSOLIDA_TECON(" . $nr_ped_atrac . ", " . $ano_ped_atrac . "); END;";
        $stid = oci_parse($conn, $sql);
        oci_execute($stid);
		
    }
	
}

// Captura os parâmetros passados pelo shell
$nr_ped_atrac  = $argv[1];
$ano_ped_atrac = $argv[2];

// Chama o método de consolidação
$consolida = new ConsolidaTecon();
$consolida->consolidar($nr_ped_atrac, $ano_ped_atrac);

?>
