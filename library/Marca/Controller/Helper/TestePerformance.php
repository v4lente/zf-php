<?php
/**
 * Helper para verificar a performance de um determinado techo de código
 *
 * @author David Valente/Marcio Souza
 *
 * @return string 
 *
 */

class Marca_Controller_Helper_TestePerformance 
{
	private $time_start = 0;
	private $time_end   = 0;
	
	
	private function getTime() {				
		$sec = explode(" ",microtime());
		return  $sec[1] + $sec[0];		
	}
	
	public function start() {		
		$this->time_start = $this->getTime();
	}
	
	public function end() {			
		$this->time_end = $this->getTime();
		
		return $this;
	}
	
	public function show() {		
		$tempo = number_format(($this->time_end - $this->time_start),6);		
		
		// Agora á só imprimir o resultado
		print "<br /><h1 style='color:black;'>Tempo de Execução: <b>".$tempo."</b> segundos </h1><br />";		
	}
}