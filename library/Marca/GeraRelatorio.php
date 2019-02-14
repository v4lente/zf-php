<?php

/**
 * Classe que processa os dados do relatório feito pelo gerador de relatórios
 *
 * @filesource
 * @author          David Valente, Márcio Souza Duarte
 * @copyright       Copyright 2010 Marca
 * @package         zendframework
 * @subpackage      zendframework.application
 * @version         1.0
 */
class Marca_GeraRelatorio {
    
    /**
     * Contrutor da classe
     * 
     * @param int    $cd_relatorio código do relatório a ser gerado
     * @param array  $extra_params parametros extra da consulta
     * @param string $nome_empresa nome da empresa que mostrará no relatório
     */
    public function __construct($cd_relatorio = 0, $extra_params, $nome_empresa="") {
        
        // Pega o adaptador padrão do banco
        $db = Zend_Registry::get('db');
        
        // Se existe variáveis de parâmetros seta elas
        if($extra_params != null) {
            if(is_array($extra_params)) {
                $this->vars = $extra_params;
            }
        }
        
        // Busca o relatório específico do usuário referente ao código passado
        $select = $db->select()->from(array("ru"=>"RELATORIO_USUARIO"), array("ru.*"))
                               ->where("ru.CD_RELATORIO = '{$cd_relatorio}'");
        
        // Retorna o registro
        $relatorio = $db->fetchRow($select);
        if($relatorio->TX_TABELAS != "") {
            
            $this->rel              = $relatorio;
            $this->cd_relatorio     = $relatorio->CD_RELATORIO;
            $this->no_relatorio     = $relatorio->NO_RELATORIO;
            $this->no_modulo        = $relatorio->NO_MODULO;
            $this->fl_formato       = $relatorio->FL_FORMATO;
            $this->fl_grafico       = $relatorio->FL_GRAFICO;
            $this->fl_explicacao    = $relatorio->FL_EXPLICACAO;
            $this->cd_operador      = $relatorio->CD_OPERADOR;
            $this->cd_unid          = $relatorio->CD_UNID;
            $this->fl_publico       = $relatorio->FL_PUBLICO;
            $this->fonte            = $relatorio->FONTE;
            $this->tamanho_fonte    = $relatorio->TAMANHO_FONTE;
            $this->orientacao       = (strtoupper($relatorio->ORIENTACAO == 'P') ? 'L' : 'P');
            $this->espacejamento    = $relatorio->ESPACEJAMENTO;
            $this->tx_sql_livre     = $relatorio->TX_SQL_LIVRE;
            $this->ds_grupo         = $relatorio->DS_GRUPO;
            $this->config_analise   = $relatorio->CONFIG_ANALISE;
            $this->fl_lista_tabelas = $relatorio->FL_LISTA_TABELAS;
            
            // Verifica se existe algum sql para rodar
            if(trim($this->tx_sql_livre) == "") {
                
                $this->pegaTabelas();
                $this->pegaColunas();
                $this->pegaConsideracoesCol();
                
                // Chama os métodos que farão a montagem do relatório
                if($extra_params != null) {
                    
                    $this->pegaLigacoes();
                    $this->pegaConsideracoesGrp();
                    $this->pegaTxGrupos();
                    $this->pegaTxOrdenacoes();
                    
                    // Monta o sql
                    $sql = "SELECT "                 . 
                            $this->colunas           . " " . 
                            $this->tabelas           . " " .  
                            $this->ligacoes          . " " . 
                            $this->consideracoesCol  . " " . 
                            $this->consideracoesGrp  . " " . 
                            $this->txGrupos          . " " . 
                            $this->txOrdenacoes;
                    
                    $sql = $this->traduzSql($sql, ' DIAMES',     ' TO_CHAR(', ", 'DD/MM')");
                    $sql = $this->traduzSql($sql, ' DIAMÊS',     ' TO_CHAR(', ", 'DD/MM')");
                    $sql = $this->traduzSql($sql, ' MESDIA',     ' TO_CHAR(', ", 'MM/DD')");
                    $sql = $this->traduzSql($sql, ' MÊSDIA',     ' TO_CHAR(', ", 'MM/DD')");
                    $sql = $this->traduzSql($sql, ' MESANO',     ' TO_CHAR(', ", 'MM/YYYY')");
                    $sql = $this->traduzSql($sql, ' MÊSANO',     ' TO_CHAR(', ", 'MM/YYYY')");
                    $sql = $this->traduzSql($sql, ' ANOMES',     ' TO_CHAR(', ", 'YYYY/MM')");
                    $sql = $this->traduzSql($sql, ' ANOMÊS',     ' TO_CHAR(', ", 'YYYY/MM')");
                    $sql = $this->traduzSql($sql, ' HORAMINUTO', ' TO_CHAR(', ", 'hh24:mi')");
                    $sql = $this->traduzSql($sql, ' DIA',        ' TO_NUMBER(TO_CHAR(', ", 'DD'))");
                    $sql = $this->traduzSql($sql, ' MES',        ' TO_NUMBER(TO_CHAR(', ", 'MM'))");
                    $sql = $this->traduzSql($sql, ' MÊS',        ' TO_NUMBER(TO_CHAR(', ", 'MM'))");
                    $sql = $this->traduzSql($sql, ' ANO',        ' TO_NUMBER(TO_CHAR(', ", 'YYYY'))");
                    $sql = $this->traduzSql($sql, ' HORA',       ' TO_NUMBER(TO_CHAR(', ", 'hh24'))");
                    $sql = $this->traduzSql($sql, ' MINUTO',     ' TO_NUMBER(TO_CHAR(', ", 'mi'))");
                    $sql = $this->traduzSql($sql, ' SEGUNDO',    ' TO_NUMBER(TO_CHAR(', ", 'ss'))");
                    
                    // Executa a consulta
                    $resultado = $db->fetchAll($sql);
                
                } else {
                    $resultado = array();
                }
                
            } else {
                
                // Captura o sql
                $sql = $this->tx_sql_livre;
                
                // Executa a consulta
                $resultado = $db->fetchAll($sql);

                // Define o cabeçalho
                $x = 0;
                foreach($resultado as $linha) {
                    
                    foreach($linha as $indice => $valor) {
                        $this->cabecalhos[] = $indice;
                    }
                    
                    break;
                }
                
            }
            
            // Seta os parâmetros para view
            $this->cabecalho    = $this->cabecalhos;
            $this->no_porto     = $nome_empresa;
            $this->cd_relatorio = $this->cd_relatorio;
            $this->no_relatorio = $this->no_relatorio . " (Relatório " . $this->cd_relatorio . ")";
            
            // Tira os espaços
            $sql = str_replace("   ",  " ",       $sql);
            $sql = str_replace("  ",   " ",       $sql);
            $sql = str_replace("= '%", "like '%", $sql);
            $sql = str_replace("='%",  "like '%", $sql);
            
            // Define o resultado da consulta
            $this->resultado = $resultado;
            
        }
        
    }
    
    /**
     * Traduz a SQL
     * 
     * @return string
     */
    private function traduzSql($sql, $procura, $trocaEsq, $trocaDir) {
        
        $valor        = $sql;
        $valor        = str_replace('CARACTER(',  'TO_CHAR(',   strtoupper($valor));
        $valor        = str_replace('NÚMERO(',    'TO_NUMBER(', strtoupper($valor));
        $valor        = str_replace('SUBTEXTO(',  'SUBSTR(',    strtoupper($valor));
        $valor        = str_replace('SE(',        'DECODE(',    strtoupper($valor));
        $valor        = str_replace('MÁXIMO(',    'MAX(',       strtoupper($valor));
        $valor        = str_replace('SOMA(',      'SUM(',       strtoupper($valor));
        $valor        = str_replace('CONTA(',     'COUNT(',     strtoupper($valor));
        $valor        = str_replace('MÍNIMO(',    'MIN(',       strtoupper($valor));
        $valor        = str_replace('MÉDIA(',     'AVG(',       strtoupper($valor));
        $valor        = str_replace('(DISTINTO)', ' DISTINCT ', strtoupper($valor));
        $valor        = str_replace('DISTINTO',   'DISTINCT',   strtoupper($valor));
        $sql          = $valor;
        $cont1        = true;
        $contIniBusca = 0;
        $sqlRetorno   = $sql;
        
        do {
            $cont1 = @strpos($sqlRetorno, $procura, $contIniBusca);
            if($cont1 !== false){
                $cont2          = 0;
                $cont3          = 0;
                $cont2          = strpos($sqlRetorno, '(', $cont1);
                $cont3          = strpos($sqlRetorno, ')', $cont2);
                $sqlColuna      = substr($sqlRetorno, $cont2 + 1, $cont3 - $cont2 - 1);
                $contIniBranco  = $cont1 + strlen($procura);
                $sqlIniBranco   = substr($sql, $contIniBranco, $cont2 - $contIniBranco);
                if((trim($sqlIniBranco) == '' || $cont2 == $contIniBranco) && $cont2 !== false){
                    $sqlExpUsuario  = substr($sqlRetorno, $cont1, $cont3-$cont1+1);
                    $sqlExpTraducao = $trocaEsq.$sqlColuna.$trocaDir;
                    $sqlRetorno     = $this->expReplace($sqlRetorno, $sqlExpUsuario, $sqlExpTraducao);
                }else{
                    $contIniBusca   = $cont1 + $contIniBranco;
                }
            }
        } while($cont1 !== false);
        
        return $sqlRetorno;
    }
    
    /**
     * Converte uma expressão
     * 
     * @return string
     */
    private function expReplace($sqlRetorno, $sqlExpUsuario, $sqlExpTraducao) {
        $len        = strlen($sqlExpUsuario);
        $lenReplace = strlen($sqlExpTraducao);
        $pos        = strpos($sqlRetorno, $sqlExpUsuario);
        while($pos !== false){
            $sqlRetorno = substr_replace($sqlRetorno, $sqlExpTraducao, $pos, $len);
            $pos        = strpos($sqlRetorno, $sqlExpUsuario, $pos + $len);
        }
        return $sqlRetorno;
    }
    
    /**
     * Traduz as funçõies de datas para as considerações
     * 
     * @return string
     */
    private function traduzDatas($valor) {
        if(trim($valor) == '') {
            return '';
        }
        
        $valorRetorno = $valor;
        $valor        = explode(' ', $valor);
        $y            = 0;
        
        for($x=0; $x < sizeof($valor); $x++) {
            if($valor[$x] != '') {
                $valorClean[$y++] = $valor[$x];
            }
        }
        
        if(isset($valorClean)) {
            
            // Pega o adaptador padrão do banco
            $db = Zend_Registry::get('db');
    
            $sqlIni = " SELECT TO_CHAR(sysdate, 'dd/mm/yyyy hh24:mi') ";
            $sqlFim = " AS DATAR FROM DUAL                            ";
            if(sizeof($valorClean) == 1) {
                
                // Monta a consulta
                $sql = $sqlIni . $sqlFim;
                
                // Retorna o registro
                $sysdate = $db->fetchRow($sql);
               
                $datahora   = $sysdate->DATAR;
                $data       = $sysdate->DATAR;
                $dataArr    = explode(' ', $datahora);
                $data       = $dataArr[0];
                $dataArr    = explode('/', $dataArr[0]);
                
                switch(strtoupper($valorClean[0])) {
                    case 'DATAATUAL':
                        $dataf = $data;
                        break;
                        
                    case    'MESATUAL':
                    case    'MÊSATUAL':
                        $dataf = $dataArr[1];
                        break;
                        
                    case 'ANOATUAL':
                        $dataf = $dataArr[2];
                        break;
                        
                    case 'DIAATUAL':
                        $dataf = $dataArr[0];
                        break;
                        
                    case 'DATAHORAATUAL':
                        $dataf = $datahora;
                        break;
                        
                    default:
                        $dataf = $valorRetorno;
                }
                
                return $dataf;
                
            } else if(sizeof($valorClean) == 4) {
                
                $sqlMid   = '';
                $operador = $valorClean[1] . $valorClean[2];
                if(!is_numeric($operador)) {
                    $operador = 0;
                }
                
                switch(strtoupper($valorClean[3])){
                    case 'DIA':
                    case 'DIAS':
                        $sqlMid = " + (" . $operador . ") ";
                        break;
                        
                    case 'MES':
                    case 'MÊS':
                    case 'MESES':
                    case 'MÊSES':
                        $sqlMid = " + (" . $operador . "*30) + trunc(" . $operador . "/2,0) ";
                        break;
                        
                    case 'ANO':
                    case 'ANOS':
                        $sqlMid = " + (" . $operador . "*365) ";
                        break;
                }
                
                // Monta a consulta
                $sql = $sqlIni . $sqlMid . $sqlFim;
                
                // Retorna o registro
                $sysdate = $db->fetchRow($sql);
                
                $datahora   = $sysdate->DATAR;
                $dataArr    = explode(' ', $datahora);
                $dataArr    = explode('/', $dataArr);

                switch(strtoupper($valorClean[0])) {
                    case 'DATAATUAL':
                        $dataf = $dataArr[0] + $dataArr[1] + $dataArr[2];
                        break;
                        
                    case    'MESATUAL':
                    case    'MÊSATUAL':
                        $dataf = $dataArr[1];
                        break;
                        
                    case 'ANOATUAL':
                        $dataf = $dataArr[2];
                        break;
                        
                    case 'DIAATUAL':
                        $dataf = $dataArr[0];
                        break;
                        
                    case 'DATAHORAATUAL':
                        $dataf = $datahora;
                        break;
                        
                    default:
                        $dataf = $valorRetorno;
                }
                
                return $dataf;
                
            } else {
                return $valorRetorno;
            }   
            
        } else {
            return $valorRetorno;
        }
    }

    /**
     * Traduz os comparadores relacionais
     * 
     * @return string
     */
    private function traduzRelacionais($relacional) {

        switch(trim(strtolower($relacional))) {
            case 'between':
                $relacional = 'está entre';
                break;
                
            case 'exists':
                $relacional = 'existe em';
                break;
                
            case 'in':
                $relacional = 'está em';
                break;
                
            case 'like':
                $relacional = 'é como';
                break;
                
            case 'not between':
                $relacional = 'não está entre';
                break;
                
            case 'not exists':
                $relacional = 'não existe em';
                break;
                
            case 'not in':
                $relacional = 'não está em';
                break;
                
            case 'not like':
                $relacional = 'não é como';
                break;
                
            case 'is':
                $relacional = 'é';
                break;
                
            case 'is not':
                $relacional = 'não é';
                break;
                
            case '= all':
                $relacional = '= a todos';
                break;
                
            case '!= all':
                $relacional = '<> de todos';
                break;
                
            case '< all':
                $relacional = '< que todos';
                break;
                
            case '> all':
                $relacional = '> que todos';
                break;
                
            case '<= all':
                $relacional = '<= a todos';
                break;
                
            case '>= all':
                $relacional = '>= a todos';
                break;
                
            case '= any':
                $relacional = '>= a qualquer';
                break;
                
            case '< any':
                $relacional = '< que qualquer';
                break;
                
            case '> any':
                $relacional = '> que qualquer';
                break;
                
            case '<= any':
                $relacional = '<= a qualquer';
                break;
                
            case '>= any':
                $relacional = '>= a qualquer';
                break;
                
            case '!= any':
                $relacional = '<> de qualquer';
                break;
        }
        
        return $relacional;
    }
    
    /**
     * Quebra as funções(expressões) do sql armazenadas no banco
     * 
     * @return string
     */
    private function quebraExpressoes($valor) {
        if(ereg("(.*)([(])(.*[^)])([)])", $valor, $arr)) {
            return $arr;
            
        } else {
            return null;
        }
    }
    
    /**
     * Pega as tabelas usadas
     * 
     * @return string
     */
    private function pegaTabelas($codifica = '') {
        
        $tabelas = explode('#', $this->rel->TX_TABELAS);
        
        $y=0;
        for($x=0; $x < sizeof($tabelas); $x++) {
            if(($x % 2) !=0) {
                $tabelasClean[$y++] = $tabelas[$x];
            }
        }
        
        $tabelas='';
        $virgula='';
        
        $z=0;
        for($x = 0; $x < sizeof($tabelasClean); $x+=2) {
            if($x > 0) { 
                $virgula = ', ';
            }
            
            $tabelas .= $virgula . $tabelasClean[$x] . ' ' . $tabelasClean[$x+1];
            $this->tabelasNomes[$z]  = $tabelasClean[$x];
            $this->tabelasLabels[$z] = $tabelasClean[$x+1];
            $z++;
        }
        
        $this->tabelas = ' from ' . $tabelas;
    }
    
    /**
     * Pega as descrições das colunas para ativar o label da caixa de diálogo
     * 
     * @return string
     */
    private function pegaDsColuna($campo, $tipo = '') {
        
        // Pega o adaptador padrão do banco
        $db = Zend_Registry::get('db');
        
        $arr = $this->quebraExpressoes($campo);
        if(isset($arr)){
            $campo = $arr[0];
            return $campo;
            
        }else{
            $campoDados = explode('.', $campo);
            for($x=0; $x < sizeof($this->tabelasLabels); $x++) {
                if($campoDados[0] == $this->tabelasLabels[$x]) {

                    $tabela = $this->tabelasNomes[$x];
                    
                     // Busca o nome da coluna da tabela específica
                    $select = $db->select()->from(array("co"=>"COLUNA"), array("co.CD_COLUNA, co.NO_COLUNA"))
                                           ->where("co.CD_COLUNA = '" . $campoDados[1] . "'")
                                           ->where("co.CD_TABELA = '" . $tabela . "'");
                    
                    // Retorna os registros
                    $coluna = $db->fetchRow($select);

                    if($coluna->NO_COLUNA == "") {
                        return 'Label desconhecido';
                    
                    }else{
                        return $coluna->NO_COLUNA;
                    }
                }
            }
        }   
    }
    
    /**
     * Pega o código da coluna para montar as considerações
     * 
     * @return string
     */
    private function pegaCdColuna($consideracoes) {
        
        // Pega o adaptador padrão do banco
        $db = Zend_Registry::get('db');
        
        for($x=0; $x < sizeof($this->tabelasLabels); $x++) {
            
            $tabela   = $this->tabelasNomes[$x];

            // Busca todas as colunas da tabela específica
            $select = $db->select()->from(array("co"=>"COLUNA"), array("co.CD_COLUNA, co.NO_COLUNA"))
                                   ->where("co.CD_TABELA = '{$tabela}'");
            
            // Retorna os registros
            $colunas = $db->fetchAll($select);
            
            if(count($colunas) > 0) {
                $xy = 0;
                foreach($colunas as $coluna) {
                    $cd_coluna      = $coluna->CD_COLUNA;
                    $no_coluna      = trim(str_replace(' PK', '', $coluna->NO_COLUNA));
                    
                    $procura        = $this->tabelasLabels[$x] . '.' . $no_coluna;
                    $troca          = $this->tabelasLabels[$x] . '.' . $cd_coluna;
                    $consideracoes  = str_replace($procura, $troca, $consideracoes);
                    
                    $xy++;
                }
            }
        }
        
        return $consideracoes;
    }
    
    /**
     * Pega o nome coluna e nome tabela para calcular largura das colunas do relatório
     * 
     * @return string
     */
    private function pegaDadosCampo($campo) {
        
        // Pega o adaptador padrão do banco
        $db     = Zend_Registry::get('db');
        
        $string = $campo;
        $len    = strlen($string);
        for($a = 0; $a < $len; $a++) {
            
            $p = ord($string[$a]);
            
            if(($p > 47 && $p < 58)  || 
               ($p > 64 && $p < 90)  || 
               ($p > 96 && $p < 123) || 
               ($p == 95)            || 
               ($p == 46)) {
                   
                $exp = false;
                
            }else{
                $exp = true;
                break;
            }
        }
        
        $campoDados = explode('.', $campo);
        if($exp) {
            $string = $campoDados[0];
            $len    = strlen($string);
            for($a = ($len-1); $a >= 0; $a--) {
                $p = ord($string[$a]);
                if(!(($p > 47 && $p < 58)  || 
                     ($p > 64 && $p < 90)  || 
                     ($p > 96 && $p < 123) || 
                     ($p == 95)            || 
                     ($p == 46))) {
                         
                    $pos = $a;
                    break;
                }   
            }
            $campoDados[0] = substr($string, $pos + 1);
            
            $string = $campoDados[1];
            $len    = strlen($string);
            for($a = 0; $a<$len; $a++) {
                $p = ord($string[$a]);
                if(!(($p > 47 && $p < 58)  || 
                     ($p > 64 && $p < 90)  || 
                     ($p > 96 && $p < 123) || 
                     ($p == 95)            || 
                     ($p == 46))) {
                         
                    $pos = $a;
                    break;
                }
            }
            
            $campoDados[1] = substr($string, -$len, $pos);
        }
        
        for($x=0; $x < sizeof($this->tabelasLabels); $x++) {
            
            if($campoDados[0] == $this->tabelasLabels[$x]) {
                
                $tabela = $this->tabelasNomes[$x];
                
                // Busca os dados de uma tabela
                $select = $db->select()->from(array("c"=>"COL"), array("c.COLTYPE, c.WIDTH"))
                                       ->where("c.TNAME = '{$tabela}'")
                                       ->where("c.CNAME = '{$campoDados[1]}'");
                
                // Retorna os registros
                $coluna = $db->fetchRow($select);
                
                if($coluna->COLTYPE != "") {
                    return '';
                }else{
                    return $coluna;
                }
                
                break;
            }
        }
    }
    
    /**
     * Pega as colunas
     * 
     * @return string
     */
    private function pegaColunas() {
        $colunas = explode('#', $this->rel->TX_COLUNAS);
        $y       = 0;
        for($x = 0; $x < sizeof($colunas); $x++) {
            if(($x % 2) != 0) {
                $colunasClean[$y++] = $colunas[$x];
            }
        }
        
        $colunas          = '';
        $virgula          = '';
        $this->cabecalhos = '';
        
        $y = 0;
        for($x = 0; $x < sizeof($colunasClean); $x += 7) {
            $this->tiposCol[$x] = $colunasClean[$x];
            
            if(strtoupper($colunasClean[$x])!= 'BRA' || $y == 0) {
                
                // monta sql com as colunas
                if(strtoupper($colunasClean[$x]) == 'DIS') {
                    $dis = true;
                    
                }else{
                    $colunas .= $virgula . $colunasClean[$x+1];
                    
                    // fim monta sql com as colunas
                    // pega labels das colunas (cabecalhos)
                    if(trim($colunasClean[$x+2]) == '') {
                        $colunasClean[$x+2] = $colunasClean[$x+1];
                        
                    }else if($colunasClean[$x+2] == "''") {
                        $colunasClean[$x+2] = '';
                    }
                    
                    $this->cabecalhos[$y] = trim($colunasClean[$x+2]);
                    // fim pega labels das colunas (cabecalhos)
                    
                    // pega formato das colunas 
                    $this->formatos[$y]   = trim($colunasClean[$x+3]);
                    
                    // pega tamanhos das colunas
                    if(trim($colunasClean[$x+4]) == '') {
                        $dados = $this->pegaDadosCampo($colunasClean[$x+1]);
                        if($dados != '') {
                            if($dados['coltype'] == 'CHAR' || $dados['coltype'] == 'VARCHAR2') {
                                if($dados['width'] < 8) {
                                    $dados['width'] = 8;
                                }
                                
                                $tam = round(($dados['width'] * 30) / 51);
                                
                            }else if($dados['coltype'] == 'NUMBER'){
                                $tam = round(629 / 30);
                                
                            }else if($dados['coltype'] == 'DATE'){
                                $tam = round(644 / 30);
                            }
                            
                            $colunasClean[$x+4] = $tam;
                            
                            unset($dados);
                            unset($tam);
                            
                        }else{
                            $colunasClean[$x+4] = 4;
                        }
                    }
                    
                    $colunasClean[$x+4] = $colunasClean[$x+4] * 3;
                    $this->tamanhos[$y] = trim($colunasClean[$x+4]);
                    // fim pega tamanhos das colunas
                    // pega quebras das colunas 
                    if(strtolower(trim($colunasClean[$x+5])) == 's') {
                        $this->quebras[$y] = 's';
                    } else {
                        $this->quebras[$y] = 'n';
                    }
                    
                    // fim pega quebras das colunas
                    // pega totais das colunas 
                    $this->totais[$y] = trim($colunasClean[$x+6]);
                    
                    // fim pega totais das colunas
                    $y++;
                    if($virgula == '') {
                        $virgula = ', ';
                    }
                    
                }//fim DIS  
            }//fim BRA  
        }
        
        if(isset($dis)) {
            $colunas = ' (DISTINTO) ' . $colunas;
        }
        
        $this->colunas = $colunas;
    }
    
    /**
     * Pega as ligações
     * 
     * @return string
     */
    private function pegaLigacoes() {
        
        $ligacoes = explode('#', $this->rel->TX_LIGACOES);
        $y=0;
        for($x=0; $x < sizeof($ligacoes); $x++){
            if(($x % 2) !=0) {
                $ligacoesClean[$y++] = $ligacoes[$x];
            }
        }
        
        $ligacoes='';
        $and='';
        
        if(isset($ligacoesClean)) {
            for($x = 0; $x < sizeof($ligacoesClean); $x += 4) {
                if($x > 0) { 
                    $and = ' and ';
                }
                
                $ligacoes .= $and . $ligacoesClean[$x]   . ' ' . 
                                    $ligacoesClean[$x+1] . ' ' . 
                                    $ligacoesClean[$x+2];
                
                if($ligacoesClean[$x+3] == 'S') {
                    $ligacoes .= ' (+)';
                }
            }
        }
        
        $and = '';
        if($ligacoes != '') {
            $and = ' and ';
        }
        
        $this->ligacoes = 'where 0=0 ' . $and . $ligacoes;
        if($this->consideracoesCol != '') {
            $this->ligacoes .= ' and ';
        }
    }

    /**
     * Pega as considerações das colunas
     * 
     * @return string
     */
    private function pegaConsideracoesCol() {
        $consideracoesCol = explode('#', $this->rel->TX_CONSIDERACOES_COL);
        $y=0;
        for($x = 0; $x < sizeof($consideracoesCol); $x++){
            if(($x % 2) != 0){
                $consideracoesColClean[$y++] = $consideracoesCol[$x];
            }
        }
        
        $consideracoesCol   = '';
        $this->valorinicial = '';
        $this->labels       = '';
        $this->relacionais  = '';
        
        $y=0;
        if(isset($consideracoesColClean)){
            for($x = 0; $x < sizeof($consideracoesColClean); $x += 7){

                $consideracoesCol .= $consideracoesColClean[$x]   . ' ' . 
                                     $consideracoesColClean[$x+1] . ' ' . 
                                     $consideracoesColClean[$x+2] . ' ';
                
                if(ereg("&", $consideracoesColClean[$x+3])) {
                    ++$y;
                    $this->var[$y] = '';
                    
                    if(isset($this->vars[$y - 1])) {
                        $this->var[$y]=str_replace('*', '%', $this->vars[$y - 1]);
                    }
                    
                    $this->relacionais[$y]       = $this->traduzRelacionais($consideracoesColClean[$x+2]);
                    $consideracoesColClean[$x+3] = ereg_replace('&','',trim($consideracoesColClean[$x+3]));
                    ereg("(<)(.*)(>)", $consideracoesColClean[$x+3], $arr);
                    
                    if(isset($arr)) {
                        if(isset($arr[2])) {
                            $arr[2] = str_replace('#^#', '*', $arr[2]);
                            $this->labels[$y] = $arr[2];
                        }
                        
                        $consideracoesColClean[$x+3] = str_replace($arr[0], '', $consideracoesColClean[$x+3]);
                        $this->valorinicial[$y]      = $this->traduzDatas(trim($consideracoesColClean[$x+3]));
                        
                        if (ereg("([^0-9])", $this->valorinicial[$y]) || $this->valorinicial[$y] == ''){
                            $consideracoesCol .= ' ' . "'" . $this->var[$y] . "'";
                        } else {
                            $consideracoesCol .= ' ' . $this->var[$y];
                        }
                        
                        unset($arr);
                        
                    }else{
                        
                        $this->valorinicial[$y] = $this->traduzDatas(trim($consideracoesColClean[$x+3]));
                        $this->labels[$y]       = $this->pegaDsColuna(trim($consideracoesColClean[$x+1]));
                        
                        if (ereg("[^0-9]", $this->valorinicial[$y]) || $this->valorinicial[$y] == ''){
                            $consideracoesCol .= ' ' . "'" . $this->var[$y] . "'";
                        }else {
                            $consideracoesCol .= ' ' . $this->var[$y];
                        }                     
                    }               
                
                }else {
                    $consideracoesCol .= ' ' . str_replace('*', '%', $consideracoesColClean[$x+3]);
                }
                
                $consideracoesCol .= ' ' . $consideracoesColClean[$x+4] . ' ' . 
                                           $consideracoesColClean[$x+5] . ' ' . 
                                           $consideracoesColClean[$x+6];
            }
        }   

        if($this->labels != '') {
            $this->labelsNum = sizeof($this->labels);
        }
        
        $this->consideracoesCol = $this->pegaCdColuna($consideracoesCol);
    }
    
    /**
     * Pega as considerações dos grupos
     * 
     * @return string
     */
    private function pegaConsideracoesGrp(){
        if(trim($this->rel->TX_CONSIDERACOES_GRP) == ''){
            $this->consideracoesGrp = '';
            return;
        }
        
        $consideracoesGrp = explode('#', $this->rel->TX_CONSIDERACOES_GRP);
        $y=0;
        for($x = 0; $x < sizeof($consideracoesGrp); $x++){
            if(($x % 2) != 0){
                $consideracoesGrpClean[$y++] = $consideracoesGrp[$x];
            }
        }
        
        $consideracoesGrp = '';
        $and = '';
        for($x = 0; $x < sizeof($consideracoesGrpClean); $x += 3){
            if($x>0) { 
                $and = ' and ';
            }
            
            $consideracoesGrp .= $and . $consideracoesGrpClean[$x]   . ' ' . 
                                        $consideracoesGrpClean[$x+1] . ' ' . 
                                        $consideracoesGrpClean[$x+2] . ' ';
        }
        
        $this->consideracoesGrp = '';
        if($consideracoesGrp != '') {
            $this->consideracoesGrp = ' having ' . $consideracoesGrp;
        }
    }
    
    /**
     * Pega os grupos
     * 
     * @return string
     */
   private function pegaTxGrupos() {
        $txGrupos = explode('#', $this->rel->TX_GRUPOS);
        $y=0;
        for($x = 0; $x < sizeof($txGrupos); $x++) {
            if(($x % 2) != 0){
                $txGruposClean[$y++] = $txGrupos[$x];
            }
        }
        
        $txGrupos='';
        $virgula='';
        if(isset($txGruposClean)) {
            for($x = 0; $x < sizeof($txGruposClean); $x++) {
                
                if($x > 0) {
                    $virgula = ', ';
                }
                $txGrupos .= $virgula . $txGruposClean[$x];
            }
        }
        
        $this->txGrupos = '';
        if($txGrupos != '') {
            $this->txGrupos = 'group by ' . $txGrupos;
        }
    }
        
    /**
     * Pega as ordenações
     * 
     * @return string
     */
    private function pegaTxOrdenacoes() {
        $txOrdenacoes = explode('#', $this->rel->TX_ORDENACOES);
        $y = 0;
        for($x = 0; $x < sizeof($txOrdenacoes); $x++){
            if(($x % 2) !=0){
                $txOrdenacoesClean[$y++] = $txOrdenacoes[$x];
            }
        }
        
        $txOrdenacoes = '';
        $virgula      = '';
        for($x = 0; $x < sizeof($txOrdenacoesClean); $x += 2) {
            
            if($x>0) {
                $virgula = ', ';
            }
            
            $ord = 'ASC';
            if(strtoupper($txOrdenacoesClean[$x+1]) == 'D') {
                $ord = 'DESC';
            }
            
            $txOrdenacoes .= $virgula . $txOrdenacoesClean[$x] . ' ' . $ord;
        }
        
        $this->txOrdenacoes = '';
        if($txOrdenacoes != '') {
            $this->txOrdenacoes = 'order by ' . $txOrdenacoes;
        }
    }
}