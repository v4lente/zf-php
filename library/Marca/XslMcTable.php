<?php
/**
 *
 * Classe Marca_XslMcTable
 *
 * Esta classe tem como objetivo criar um relatório XSL do Excel
 *
 * @category   Marca Sistemas
 * @package    Marca_XslMcTable
 * @copyright  Copyright (c) 1991-2009 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 * @version    $Id: XslMcTable.php 0 2010-09-23 15:20:00 Marcio $
 */
class Marca_XslMcTable {
    
    var $fp = null;
    var $error;
        
    /**
     * Construtor da classe
     */
    public function __construct($cd_usuario="", $cd_relatorio=0) {
        return $this->open($cd_usuario, $cd_relatorio);
    }
        
    function open($cd_usuario, $cd_relatorio) {
        
    	$this->handler = $cd_usuario . '_rel_' . $cd_relatorio . '.xls';
        
        $this->fp=fopen($this->handler, "w+");
        
        fwrite($this->fp,$this->GetHeader());
        
        return $this->fp;
    }
        
    function close() {
        if($this->newRow) {
            fwrite($this->fp, "</tr>");
            $this->newRow=false;
        }
        fwrite($this->fp, $this->GetFooter());
        fclose($this->fp);
        return ;
    }

    function GetHeader() {
        $header = <<<EOH
            <html xmlns:o="urn:schemas-microsoft-com:office:office"
            xmlns:x="urn:schemas-microsoft-com:office:excel"
            xmlns="http://www.w3.org/TR/REC-html40">

            <head>
            <meta http-equiv=Content-Type content="text/html; charset=iso-8859-1">
            <meta name=ProgId content=Excel.Sheet>
            <!--[if gte mso 9]><xml>
             <o:DocumentProperties>
              <o:LastAuthor>Planilha</o:LastAuthor>
              <o:LastSaved>2005-01-02T07:46:23Z</o:LastSaved>
              <o:Version>10.2625</o:Version>
             </o:DocumentProperties>
             <o:OfficeDocumentSettings>
              <o:DownloadComponents/>
             </o:OfficeDocumentSettings>
            </xml><![endif]-->
            <style>
            <!--table
            @page
                {margin:1.0in .75in 1.0in .75in;
                mso-header-margin:.5in;
                mso-footer-margin:.5in;}
            tr
                {mso-height-source:auto;}
            col
                {mso-width-source:auto;}
            br
                {mso-data-placement:same-cell;}
            .style0
                {mso-number-format:General;
                text-align:general;
                vertical-align:bottom;
                white-space:nowrap;
                mso-rotate:0;
                mso-background-source:auto;
                mso-pattern:auto;
                color:windowtext;
                font-size:10.0pt;
                font-weight:400;
                font-style:normal;
                text-decoration:none;
                font-family:Arial;
                mso-generic-font-family:auto;
                mso-font-charset:0;
                border:none;
                mso-protection:locked visible;
                mso-style-name:Normal;
                mso-style-id:0;}
            td
                {mso-style-parent:style0;
                padding-top:1px;
                padding-right:1px;
                padding-left:1px;
                mso-ignore:padding;
                color:windowtext;
                font-size:10.0pt;
                font-weight:400;
                font-style:normal;
                text-decoration:none;
                font-family:Arial;
                mso-generic-font-family:auto;
                mso-font-charset:0;
                mso-number-format:General;
                text-align:general;
                vertical-align:bottom;
                border:none;
                mso-background-source:auto;
                mso-pattern:auto;
                mso-protection:locked visible;
                white-space:nowrap;
                mso-rotate:0;}
            .xl24
                {mso-style-parent:style0;
                white-space:normal;}
            .style16
                {mso-number-format:"_\(* \#\,\#\#0\.00_\)\;_\(* \\\(\#\,\#\#0\.00\\\)\;_\(* \0022-\0022??_\)\;_\(\@_\)";
                mso-style-name:"Separador de milhares";
                mso-style-id:3;}
            .xl37
                {mso-style-parent:style16;
                mso-number-format:"_\(* \#\,\#\#0\.00_\)\;_\(* \\\(\#\,\#\#0\.00\\\)\;_\(* \0022-\0022??_\)\;_\(\@_\)";
                white-space:normal;}

            -->
            </style>
            <!--[if gte mso 9]><xml>
             <x:ExcelWorkbook>
              <x:ExcelWorksheets>
               <x:ExcelWorksheet>
                <x:Name>Planilha</x:Name>
                <x:WorksheetOptions>
                 <x:Selected/>
                 <x:ProtectContents>False</x:ProtectContents>
                 <x:ProtectObjects>False</x:ProtectObjects>
                 <x:ProtectScenarios>False</x:ProtectScenarios>
                </x:WorksheetOptions>
               </x:ExcelWorksheet>
              </x:ExcelWorksheets>
              <x:WindowHeight>10005</x:WindowHeight>
              <x:WindowWidth>10005</x:WindowWidth>
              <x:WindowTopX>120</x:WindowTopX>
              <x:WindowTopY>135</x:WindowTopY>
              <x:ProtectStructure>False</x:ProtectStructure>
              <x:ProtectWindows>False</x:ProtectWindows>
             </x:ExcelWorkbook>
            </xml><![endif]-->
            </head>

            <body link=blue vlink=purple>
            <table x:str border=0 cellpadding=0 cellspacing=0 style='border-collapse: collapse;table-layout:fixed;'>
EOH;
        return $header."\n";
    }
    
    function GetFooter() {
        return "</table></body></html>";
    }
    
    function writeLine($line_arr) {
        if(!is_array($line_arr)) {
            $this->error="Erro de dados na criação do xls.";
            return false;
        }
        fwrite($this->fp,"<tr>\n");
        foreach($line_arr as $col) {
            $format='';
            if(is_numeric($col)) {
                $format='x:num';
            }
            fwrite($this->fp, "<td class=xl37 width=64 $format>$col</td>\n");
        }
        
        fwrite($this->fp,"</tr>\n");
    }
    
}
