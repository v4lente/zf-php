<?php
/**
 * View Helper para gerar PDF a partir de HTML usando DOMPDF
 * 
 * @author David Valente, Marcio Duarte
 *
 * @param string  $html
 * @param string  $tipoPapel
 * @param string  $formatoPapel
 * @param integer $tipoSaida
 *
 * Saidas:
 * 0: Envia para a saída padrão
 * 1: Download do arquivo 
 */
 
class Zend_View_Helper_DomPDF extends Zend_View_Helper_Abstract
{   
	
	public function domPDF($html, $tipoPapel="a4", $formatoPapel="portrait", $tipoSaida=0)
    {	
		$dompdf = new DOMPDF();
		$dompdf->set_paper($tipoPapel, $formatoPapel);
		
		$dompdf->load_html($html);
		
		$dompdf->set_base_path($_SERVER['DOCUMENT_ROOT']);
		$dompdf->render();		
		$pdf = $dompdf->output(); 
		$dompdf->stream($pdf, array('Attachment' => $tipoSaida));
    }
}