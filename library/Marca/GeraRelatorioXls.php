<?php
/**
 * Classe para gera��o de XLS (Excel)
 *
 * @author M�rcio Souza Duarte
 */

// Inclui as classes para os relatorios r�pido
require_once("PHPExcel/PHPExcel.php");

class GeraRelatorioXls {
	
    /**
     * 
     * M�todo principal da classe.
     * 
     * @param  array   $arrayObjetos
     * @param  string  $titulo
     * @param  array   $campos
     * 
     * @return void
     */
    public function __construct($baseUrl      = "",
                                $arrayObjetos = array(), 
                                $titulo       = "Relat�rio do Porto", 
                                $campos       = array()) {

		try {
            
		    // Captura a sess�o
            $sessao = new Zend_Session_Namespace('portoweb');
            
            // Instancia a classe PHPExcel
            $xls = new PHPExcel();
            
            // Seta as c�lulas que ser�o usadas para o relat�rio
            $celulas = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 
                             'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'Z');
            
            // Seta as propriedades da planinha
            $xls->getProperties()->setCreator(iconv('ISO-8859-1', 'UTF-8', "Marca Sistemas"))
                                 ->setLastModifiedBy(iconv('ISO-8859-1', 'UTF-8', "Marca Sistemas"))
                                 ->setTitle(iconv('ISO-8859-1', 'UTF-8', $titulo))
                                 ->setSubject(iconv('ISO-8859-1', 'UTF-8', $titulo))
                                 ->setDescription(iconv('ISO-8859-1', 'UTF-8', $titulo))
                                 ->setKeywords("php xls")
                                 ->setCategory("Relat�rio");
            
            // Cria o estilo para a primeira linha
            $styleArray = array(
                'font' => array(
                    'bold' => true,
                )
            );
            
            // Seta o estilo da primeira linha
            $xls->getActiveSheet()
                ->getStyle('A1:Z1')
                ->applyFromArray($styleArray);
            
            // Gera o cabe�alho
            $i=0;
            foreach($campos as $indice => $valor) {
                $xls->setActiveSheetIndex(0)
                    ->setCellValue($celulas[$i] . '1', iconv('ISO-8859-1', 'UTF-8', $valor["title"]));
                $i++;
            }
            
            // Percorre todos as linhas da consulta
            $i=2;
            foreach($arrayObjetos as $linha) {
                
                // Percorre campo a campo e insere nas c�lulas da planilha
                $j=0;
                foreach($campos as $indice => $valor) {
                
                    // Aplica o alinhamento nos campos
                    $align = "L";
                    if(isset($valor["text-align"])) {
                        if(trim(strtolower($valor["text-align"])) == "right") {
                            $align = "R";
                        } else if(trim(strtolower($valor["text-align"])) == "center") {
                            $align = "C";
                        }
                    }
                    
                    // Grava na c�lula o valor do campo
                    $xls->setActiveSheetIndex(0)
                        ->setCellValue($celulas[$j] . $i, iconv('ISO-8859-1', 'UTF-8', $linha[$indice]));
                    
                    // Incrementa a vari�vel
                    $j++;
                }
                
                // Incrementa a vari�vel
                $i++;
            }
            
            // Gera a planilha
            $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel5');
            $objWriter->save('php://output');
            
		} catch (Exception $e) {
			echo "Erro ao traduzir {$e->getMensagem()}";
		}

	}
	
}
