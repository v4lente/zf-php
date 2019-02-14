<?php

require_once("Fpdf/fpdf.php");

/**
 * Classe para gera��o de PDF
 *
 * @author M�rcio Souza Duarte
 */
class GeraRelatorioPdf {
	
    private $pdf            = null;
    private $baseUrl        = null;
    private $titulo         = null;
    private $no_empr        = null;
    private $orientacao     = null;
    private $tamMaxLinha    = null;
    private $tamanhoFonte   = null;
    private $alturaLinha    = null;
    private $totalLinhas    = null;
    private $arrayObjetos   = null;
    private $campos         = null;
    private $grupos         = null;
    private $totais         = null;
    private $totalRegistros = null;
    
    /**
     * 
     * M�todo principal da classe.
     * 
     * @param  string  $baseUrl
     * @param  array   $arrayObjetos
     * @param  string  $titulo
     * @param  array   $campos
     * @param  string  $orientacao
     * @param  string  $unidade
     * @param  string  $formato
     * @param  int     $tamanhoFonte
     * 
     * @return object  $pdf
     */
    public function __construct($baseUrl      = "",
                                $arrayObjetos = array(), 
                                $titulo       = "Relat�rio do Porto", 
                                $campos       = array(), 
                                $orientacao   = 'P', 
                                $unidade      = 'mm', 
                                $formato      = 'A4',
                                $tamanhoFonte = 6) {

		try {
            
		    // Captura a sess�o
            $sessao = new Zend_Session_Namespace('portoweb');
            
            $this->baseUrl = $baseUrl;
            
            $this->titulo  = $titulo;
            $this->no_empr = $sessao->perfil->NO_EMPR;
		    
		    // Instancia a classe do PDFD
		    // N�o tem suporte a unidade e formato ainda no helper,
		    // somente a orienta��o poder� ser definida
            $this->pdf = new FPDF($orientacao, 'mm', 'A4');
            
            // Seta alguns par�metros do relat�rio
            $this->pdf->SetAuthor("Marca Sistemas");
            $this->pdf->SetTitle($titulo);
            
            // Seta os dados da fonte
            $this->tamanhoFonte = $tamanhoFonte;

            // Define a altura de cada linha
            if ($this->tamanhoFonte == 6){
                $this->alturaLinha  = 4.5;
            }elseif ($this->tamanhoFonte == 7){
                $this->alturaLinha  = 5.3;
            }elseif ($this->tamanhoFonte == 8){
                $this->alturaLinha  = 6.1;
            }elseif ($this->tamanhoFonte == 9){
                $this->alturaLinha  = 6.9;
            }elseif ($this->tamanhoFonte == 10){
                $this->alturaLinha  = 7.7;
            }elseif ($this->tamanhoFonte == 11){
                $this->alturaLinha  = 8.5;
            }elseif ($this->tamanhoFonte == 12){
                $this->alturaLinha  = 9.3;
            }elseif ($this->tamanhoFonte == 13){
                $this->alturaLinha  = 10.1;
            }elseif ($this->tamanhoFonte == 14){
                $this->alturaLinha  = 10.9;
            }

            // Seta os objetos
            $this->arrayObjetos = $arrayObjetos;

            // Seta o total de registros da query
            $this->totalRegistros = count($arrayObjetos);

            // Seta os campos
            $this->campos = $campos;

            // Monta os grupos
            $this->grupos();

            // Controla o total de linhas dependendo da fonte
            $this->orientacao = $orientacao;
            if(strtoupper($orientacao) != 'P') {
                $this->totalLinhas  = 30 - (count($this->grupos) * 2);
                $this->tamaMaxLinha = 277;
            } else {
                $this->totalLinhas  = 50 - (count($this->grupos) * 2);
                $this->tamaMaxLinha = 190;
            }

            // Adiciona uma nova p�gina
            $this->pdf->AddPage();

            // Prepara os dados para a visualiza��o
            $this->preparaDados();

            // Monta o corpo
            $this->corpo();

            // Envia a sa�da para a tela
            $this->pdf->Output();

			// Retorna o texto traduzido
			return $this->pdf;

		} catch (Exception $e) {
			echo "Erro ao traduzir {$e->getMensagem()}";
		}

	}
	
	/**
	 * 
	 * Monta o cabe�alho do pdf.
	 */
	private function cabecalho() {
	    
	    // Seta a fonte
        $this->pdf->SetFont('Arial', 'B', $this->tamanhoFonte + 2);
        
        // Joga a imagem no cabecalho
        $baseUrl = $_SERVER["DOCUMENT_ROOT"] . $this->baseUrl;
	    
        // Controla o total de linhas dependendo da fonte
        // Se for L entra no primeiro item
        if(strtoupper($this->orientacao) != 'P') {

            $this->pdf->Line(10, 10, 287, 10);
            $this->pdf->Line(10, 10, 10, 25);
            $this->pdf->Line(10, 25, 287, 25);
            $this->pdf->Line(287, 10, 287, 25);

            $this->pdf->Image($baseUrl . "/public/images/logo_porto.jpg", 12.5, 12.5, 12, 12);

            // T�tulo do relat�rio
            $this->pdf->SetXY(10, 12.5);
            $this->pdf->MultiCell(287, 5, $this->no_empr, 0, 'C');

            // Nome da empresa
            $this->pdf->SetXY(10, 18.5);
            $this->pdf->MultiCell(287, 5, $this->titulo, 0, 'C');

        } else {

    	    $this->pdf->Line(10, 10, 200, 10);
            $this->pdf->Line(10, 10, 10, 25);
            $this->pdf->Line(10, 25, 200, 25);
            $this->pdf->Line(200, 10, 200, 25);

            $this->pdf->Image($baseUrl . "/public/images/logo_porto.jpg", 12.5, 12.5, 12, 12);

            // T�tulo do relat�rio
            $this->pdf->SetXY(10, 12.5);
            $this->pdf->MultiCell(190, 5, $this->no_empr, 0, 'C');

            // Nome da empresa
            $this->pdf->SetXY(10, 18.5);
            $this->pdf->MultiCell(190, 5, $this->titulo, 0, 'C');

        }

	}

	/**
	 * 
	 * Monta o rodap� do pdf.
	 * 
	 * @param int $total
	 */
    private function rodape($num=0) {
        //Arial italic 8
        $this->pdf->SetFont('Arial', 'I', $this->tamanhoFonte + 2);

        // Controla o total de linhas dependendo da fonte
        if(strtoupper($this->orientacao) != 'P') {

            $this->pdf->Line(10, 182, 287, 182);

            //Data e numero da pagina
            $this->pdf->SetXY(10, 184);
            $this->pdf->MultiCell(95, 5, date('d/m/Y  G:i:s'), 0, 'L');

            //Put the position to the right of the cell
            $this->pdf->SetXY(105, 184);
            $this->pdf->MultiCell(97, 5, $num . ' de ' . $this->totalRegistros . ' registros', 0, 'C');

            //Put the position to the right of the cell
            $this->pdf->SetXY(202, 184);
            $this->pdf->MultiCell(85, 5, 'P�g. ' . $this->pdf->PageNo(), 0, 'R'); // . '/{nb}'
            
        } else {
            
            $this->pdf->Line(10, 268, 200, 268);
            
            //Data e numero da pagina
            $this->pdf->SetXY(10, 270);
            $this->pdf->MultiCell(60, 5, date('d/m/Y  G:i:s'), 0, 'L');
            
            //Put the position to the right of the cell
            $this->pdf->SetXY(70, 270);
            $this->pdf->MultiCell(70, 5, $num . ' de ' . $this->totalRegistros . ' registros', 0, 'C');
            
            //Put the position to the right of the cell
            $this->pdf->SetXY(140, 270);
            $this->pdf->MultiCell(60, 5, 'P�g. ' . $this->pdf->PageNo(), 0, 'R'); // . '/{nb}'
            
        }
        
    }
    
     /**
     * 
     * Monta os agrupamentos dos campos
     * 
     * @return void
     */
	private function grupos() {
        
        // Captura os campos a serem mostrados no relat�rio
        $campos = $this->campos;
	    
	    // Grava os grupos
        $this->grupos = array();
        $g      = 0;
        foreach($campos as $indice => $valor) {
            
            // Captura os grupos 
            if(isset($valor["group"]) && $valor["group"]) {
                
                // Guarda o nome do grupo a descri��o
                $this->grupos[$g] = array($indice, "");
                $g++;                
            }
        }
        
        // Libera da memoria        
        unset($campos);
        
	}
	
	/**
	 * 
	 * Limpa os valores dos grupos internos caso seja alterado o valor
	 * de um grupo acima.
	 * 
	 * @param int $indice
	 */
	public function limpaGruposInternos($indice) {
	    
	    // Total de grupos
	    $totalGrupos = count($this->grupos);
	    
	    // Percorre os grupos internos
	    for($i=($indice+1); $i < $totalGrupos; $i++) {
	        
	        // Limpa o grupo
	        $this->grupos[$i][1] = "";
	        
	    }
	    
	}
	
	/**
	 * 
	 * Insere os t�tulos nos campos
	 * 
	 * @return void
	 */
	public function insereTitulosCampos() {

		// Captura os campos a serem mostrados no relat�rio
        $campos = $this->campos;

		// Seta a cor da linha
        $this->pdf->SetDrawColor(240, 240, 240);

        // Gera o t�tulo dos campos
        $borderLeft = "";
        $x = 10;
        $y = 35;
        foreach($campos as $indice => $valor) {

            // Se for um grupo n�o mostra na listagem do relat�rio
            if(! isset($valor["group"])) {

                // Captura o valor em porcentagem
                $width = ($this->tamaMaxLinha * intval($valor["width"])) / 100;

                // Seta o alinhamento
				switch(strtolower($valor["text-align"])) {
					case "left"   : $align = "L"; break;
					case "right"  : $align = "R"; break;
					case "center" : $align = "C"; break;
					default: $align = "L";
				}

                $this->pdf->setXY($x, $y - $this->alturaLinha);
//                $this->pdf->setXY($x, 25);
               // $this->pdf->MultiCell($width, $this->alturaLinha, $valor["title"], $borderLeft, $align, 1);
                $this->pdf->MultiCell($width, $this->alturaLinha, $valor["title"], $borderLeft, $align, 1);
                $x += $width;
                $borderLeft = "L";
            }
        }

	}

    /**
     * 
     * Prepara os dados para o corpo do pdf
     * 
     * @return void
     */
	private function preparaDados() {
	    
        // Captura os objetos da consulta
        $arrayObjetos = $this->arrayObjetos;
        
        // Captura os campos a serem mostrados no relat�rio
        $campos = $this->campos;
        
        // Captura os grupos
        $grupos      = $this->grupos;
        $totalLinhas = count($arrayObjetos); // Total de Dados da consulta
        $dados       = array(); // Monta os dados para serem visualizados
        
        if($totalLinhas > 0) {
            
            foreach($arrayObjetos as $linha) {
                
                // Insere o grupo entre as linhas
                if(count($grupos) > 0) {
                    
                    // Verifica se o grupo existe e atualiza o valor do grupo
                    $g=0;
                    foreach($grupos as $indice => $valor) {
                        
                        // Verifica se o valor do grupo � diferente do valor da linha,
                        // caso seja, seta o novo grupo e joga o valor no pdf 
                        if($this->grupos[$g][1] != $linha[$valor[0]]) {
                            
                            // Seta o novo valor do grupo
                            $this->grupos[$g][1] = $linha[$valor[0]];
                            
	                        // Tipo, Valor, N�vel
	                        $dados[] = array("E", ""); // Insere o espa�o
                            
                            // Tipo, Valor, N�vel
                            $dados[] = array("G", $linha[$valor[0]], $campos[$this->grupos[$g][0]]["title"], $g); // Insere o grupo
                            
                            // Limpa os dados dos grupos mais internos
                            $this->limpaGruposInternos($g);                            
                        }

                        // Incrementa o �ndie do grupo
                        $g++;
                    }

                } 
                
                $novosDados = array();
                foreach($campos as $indice => $valor) {
                    
                    // Se for um grupo n�o mostra na listagem do relat�rio
                    if(! isset($valor["group"])) {
                    	// Coluna, Options do campo
                    	$novosDados[] = array($linha[$indice], $valor);
                    	
                    }
                }
                
                // Tipo, Valor, N�vel
                $dados[] = array("D", $novosDados); // Insere os dados da linha
                
            }
            
            // Recebe os dados atualizados para inser��o na view do pdf
            $this->arrayObjetos = $dados;
            
            // Limpa o valor dos dados
            unset($dados);
            unset($novosDados);
            unset($arrayObjetos);
        	unset($campos);
        	unset($grupos);
    	}
	
	}
	
	
	/**
     * 
     * Monta os dados do corpo do pdf
     * 
     * @return void
     */
	private function corpo() {
	    
        // Captura os objetos da consulta
        $arrayObjetos = $this->arrayObjetos;
        
        // Captura os campos a serem mostrados no relat�rio
        $campos = $this->campos;
        
        // Seta a fonte
        $this->pdf->SetFont('Arial', '', $this->tamanhoFonte);
        
        // Seta a cor do texto
        $this->pdf->SetTextColor(20, 20, 20);
        
    	$i = 1;  // Controla o total de linhas por p�gina
        $y = 40; // Controla a altura de cada linha dentro da p�gina
        $j = 0;  // Controla o total de registro inseridos
        $zebra = false; // Respons�vel por zebrar a linha
        $totalLinhas = count($arrayObjetos);
        
        if($totalLinhas > 0) {
            
            foreach($arrayObjetos as $linha) {
                
                $x = 10; // Controla a posi��o de cada coluna na linha
                
                // Seta a fonte
                $this->pdf->SetFont('Arial', 'B', $this->tamanhoFonte);
                $this->pdf->SetFillColor(230, 230, 230);
                $atualY = $this->pdf->GetY();

                // Cria uma linha com o cabe�alho e rodap�
                if($i == 1) {

                    // Gera o cabecalho
                    $this->cabecalho();

                    // Insere o t�tulo dos campos
                    $this->insereTitulosCampos();
                    
                } else if(($atualY >= 263 && $this->orientacao == "P") || ($atualY >= 175 && $this->orientacao == "L")) {
                    
                    // Seta a cor da linha
                    $this->pdf->SetDrawColor(0, 0, 0);
                    
                    // Gera o rodap�
                    $this->rodape($j);

                    // Adiciona uma p�gina
                    $this->pdf->AddPage();

                    // Reseta os valores
                    $i = 1;
                    $y = 40;
                    $zebra = false;

                    // Gera o cabecalho
                    $this->cabecalho();
                    
                    // Insere o t�tulo dos campos
                    $this->insereTitulosCampos();
                    
                }
                    
                // Seta a fonte
                $this->pdf->SetFont('Arial', 'B', $this->tamanhoFonte);
                
                // Seta a cor da linha
                $this->pdf->SetDrawColor(255, 255, 255);
                $this->pdf->SetFillColor(255, 255, 255);
                
				// Define o alinhamento padr�o
				$align = "L";
				
                // Joga o valor da linha na view do pdf
                $x = 10;
                if($linha[0] == "E") { // Espa�o
                	
                	// Decrementa a altura da linha quando for uma nova p�gina
                	if($i == 1) {
                		//$y = $y - $this->alturaLinha;
                		$y = 35;
                	}
                	
                   	$this->pdf->setXY($x, $y);
                   	$this->pdf->MultiCell($this->tamaMaxLinha, $this->alturaLinha, " " . $linha[1] . " ", $borderLeft, $align, 1);
                	
                   	$zebra = false;
                   	
                } else if($linha[0] == "G") { // Grupo

                	$x = $x + ($linha[3] * 3);
                    $this->pdf->setXY($x, $y);
                            
                    // Mostra o grupo no pdf
                    $this->pdf->MultiCell($this->tamaMaxLinha - ($x - 10), $this->alturaLinha, $linha[2] . ": " . $linha[1], 0, "L", 0);
                	
                    $zebra = false;
                    
                } else if($linha[0] == "D") { // Dados
                	
                	// Seta a fonte
                    $this->pdf->SetFont('Arial', '', $this->tamanhoFonte);
                    $this->pdf->SetTextColor(100, 100, 100);
                    
                    // Seta o contador das p�ginas
                    $this->pdf->setXY(5, $y);
                    $this->pdf->Cell(5, $this->alturaLinha, ($j + 1), 1, 1, 'R');
                    
	                // Seta a cor de fundo da linha para gerar o zebrado
	                $zebra = ! $zebra;
	                if($zebra) {
	                    $this->pdf->SetFillColor(200, 200, 200);
	                }

	                $this->pdf->SetTextColor(20, 20, 20);
	                 
	                $x = 10;
	                $totalCampos = count($campos);
	                $borderLeft = "";
	                $dados = $linha[1];
	                foreach($dados as $indice => $valor) {
	                    	                	
	                	// Captura a descri��o
	                	$descricao = $valor[0];
	                	
	                	// Captura os atributos do campo
	                	$atributos = $valor[1];
	                	
	                     // Captura o valor em porcentagem
                        $width = ($this->tamaMaxLinha * intval($atributos["width"])) / 100;
                        
						// Seta o alinhamento
						switch(strtolower($atributos["text-align"])) {
							case "left"   : $align = "L"; break;
							case "right"  : $align = "R"; break;
							case "center" : $align = "C"; break;
							default: $align = "L";
						}
						
                        $this->pdf->setXY($x, $y);
                        $this->pdf->MultiCell($width, $this->alturaLinha, $descricao, $borderLeft, $align, 1);
                        
                        $x += $width;
                        $borderLeft = "L";
	                }
	                
	                $j++;
                }
                
                // Incrementa os dados de controle da linha
                $i += 1;
                $y += $this->alturaLinha;                
            }
            
            // Faz o controle do �ltimo rodap�
            if($i < ($this->totalLinhas + 2)) {
            	// Seta a cor da linha
                $this->pdf->SetDrawColor(0, 0, 0);
                
                // Gera o rodap�
                $this->rodape($j);
            }
            
    	
    	} else {
    	    
    	    // Seta a fonte
            $this->pdf->SetFont('Arial', 'B', $this->tamanhoFonte);
            $this->pdf->SetFillColor(230, 230, 230);
    	    
    	    // Gera o cabecalho
            $this->cabecalho();
            
            // Gera o rodap�
            $this->rodape(0);
    	}
    	
		// Libera da memoria
    	unset($arrayObjetos);
        unset($campos);
	}
}
