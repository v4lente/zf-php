<?php
/**
 * View Helper para gerar script de configuração do DomPDF
 * 
 * @author Marcio Duarte
 *
 * @param  string  $titulo
 * @return string
 */
 
class Zend_View_Helper_ScriptDomPDF extends Zend_View_Helper_Abstract
{   
    
    public function scriptDomPDF($titulo="SUPRG") {   
        
        $script = "
            // Abre o objeto
            \$header  = \$pdf->open_object();
            
            // Pega a largura e altura da pagina
            \$wpagina = \$pdf->get_width();
            \$hpagina = \$pdf->get_height();
            
            // Retorna a fonte
            \$font     = Font_Metrics::get_font('verdana', 'bold');
            
            // Converte de iso para utf
            \$titulo_1 = iconv('iso-8859-1', 'utf-8', 'Superintendência do Porto de Rio Grande');
            \$titulo_2 = iconv('iso-8859-1', 'utf-8', '{$titulo}');
            
            // Retorna o tamanha dos textos
            \$width_1  = Font_Metrics::get_text_width(\$titulo_1 , \$font, 14);
            \$width_2  = Font_Metrics::get_text_width(\$titulo_2 , \$font, 14);
            
            // Insere uma imagem na esquerda no topo de cada pagina 
            \$pdf->image(dirname(\$_SERVER['SCRIPT_FILENAME']).'/images/logo_porto.jpg', 'jpg', 20, 20, 50, 50);
            
            // Insere o texto centralisado no topo de cada pagina
            \$pdf->page_text(\$wpagina/2 - \$width_1/2, 30, \$titulo_1, \$font , 14, array(0, 0, 0));
            \$pdf->page_text(\$wpagina/2 - \$width_2/2, 45, \$titulo_2, \$font , 14, array(0, 0, 0));
            
            // Desenha o retangulo de definição do cabeçalho de cada pagina
            \$pdf->rectangle(\$wpagina - (\$wpagina - 20), 20, \$wpagina - 40, 50, array(0.5,0.5,0.5), 0.5);
            
            // Insere a numeração no final de cada pagina na direita
            \$pdf->page_text(\$wpagina-50 , \$hpagina-35, '{PAGE_NUM}/{PAGE_COUNT}' , \$font, 6, array(0,0,255));
            
            // Insere a data e hora no final de cada pagina na esquerda
            \$pdf->page_text(21 , \$hpagina-35, date('d/m/Y') , \$font, 6, array(0,0,255));
            \$pdf->page_text(58 , \$hpagina-35, date('h:i:s') , \$font, 6, array(0,0,255));
            
            // Fecha o objeto
            \$pdf->close_object();
            
            // Adiciona o objeto ('all'  => todas as paginas
            //                    'odd'  => paginas impares
            //                    'even' => paginas pares)
            \$pdf->add_object(\$header, 'all');
        ";
        
        // Retorna o script para a tela
        echo $script;
        
    }
}