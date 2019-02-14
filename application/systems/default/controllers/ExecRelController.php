<?php

// Inclui a classe para os relatorios rápido
require_once("Fpdf/fpdf.php");

// Classe que processa os dados do relatório feito pelo gerador de relatórios
include_once("Marca/GeraRelatorio.php");

/**
 *
 * Classe ExecRelController
 *
 * Esta classe tem como objetivo listar e executar os relatórios que 
 * foram gravados pelo gerador de relatórios.
 * 
 * Obs.: Esta classe é uma adaptação do sistema antigo, ela usa classes antigas para gerar 
 * os relatórios.
 * 
 *
 * @category   Marca Sistemas
 * @package    ExecRelController
 * @copyright  Copyright (c) 1991-2009 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 * @version    $Id: ExecRelController.php 0 2010-09-14 17:25:00 Marcio $
 */

class ExecRelController extends Marca_Controller_Abstract_Operacao {
    
    /**
     * Método inicial para carregamento de classes do controlador
     *
     * @return void
     */
    public function init() {
        // Carrega o método de inicialização da classe pai
        parent::init();
        
        // Carrega o modelo de dados do usuário
        Zend_Loader::loadClass('UsuarioModel');
        
        // Carrega o modelo de dados dos relatórios
        Zend_Loader::loadClass('UsuarioRelatorioModel');
        
        // Carrega a classe que irá processar os dados do relatório
        Zend_Loader::loadClass("Marca_GeraRelatorio");
        
        // Carrega a classe que irá processar os htmls
        Zend_Loader::loadClass("Marca_HtmMcTable");
        
        // Carrega a classe que irá processar os pdfs
        Zend_Loader::loadClass("Marca_PdfMcTable");
        
        // Carrega a classe que irá processar os xsls do excel
        Zend_Loader::loadClass("Marca_XslMcTable");
    }

    /**
     * Método principal da classe
     *
     * @return void
     */
    public function indexAction() {
        
        // Pega o adaptador padrão do banco
        $db = Zend_Registry::get('db');
        
        // Captura os parâmetros passados pela requisição
        $params = $this->_getAllParams();
        
        // Pega o código do relatório
        $cd_relatorio = $params["cd_relatorio"];
        
        // Instancia o modelo
        $usuRels = new UsuarioRelatorioModel();
        
        // Define os filtros para a cosulta
        $where = $usuRels->addWhere(array("ur.CD_USUARIO = ?"      => $this->getAutenticacao()->getIdentity(),
                                          "ru.CD_RELATORIO = ?"    => $params["cd_relatorio"],
                                          "ru.NO_MODULO like ?"    => $params["no_modulo"],
                                          "ru.DS_GRUPO like ?"     => $params["ds_grupo"],
                                          "ru.NO_RELATORIO like ?" => $params["no_relatorio"])
                                    )->getWhere();
        
                                    
        // ordenação
        $order = null;
        if(isset($params['column'])){
            $order = $params['column'] . " " . $params['orderby'];
        }
        
        // Define os parâmetros para a consulta e retorna o resultado da pesquisa
        $resConsulta = $usuRels->getRelatorios($where, $order);
        
        // Define a página
        $pagina = $this->_getParam('pagina', 1);
        
        // Instancia e seta o paginador
        $paginator = Zend_Paginator::factory($resConsulta);
        $paginator->setCurrentPageNumber($pagina);
        $paginator->setItemCountPerPage(10);
        
        // Joga pra view o objeto paginado
        $this->view->paginator = $paginator;
        
    }
    
    /**
     * Metodo novo
     * método do padrão da marca
     * objetivo: utilizado para formularios de cadastro
     */
    public function novoAction(){}

    /**
     * Metodo salvar
     * método do padrão da marca
     * objetivo: utilizado para as operações de INSERT/UPDATE
     */
    public function salvarAction(){}

    /**
     * Metodo excluir
     * método do padrão da marca
     * objetivo: utilizado para a operação de DELETE
     */
    public function excluirAction(){}

    /**
     * Metodo pesquisar
     * método do padrão da marca
     * objetivo: utilizado para executar pesquisas
     */
    public function pesquisarAction() {
        
        // Pega o adaptador padrão do banco
        $db = Zend_Registry::get('db');
        
        // Captura os parâmetros passados pela requisição
        $params = $this->_getAllParams();
        
        // Pega o código do relatório
        $cd_relatorio = $params["cd_relatorio"];
        
        // Instancia o modelo
        $usuRels = new UsuarioRelatorioModel();

        // Define os filtros para a cosulta
        $where = $usuRels->addWhere(array("ur.CD_USUARIO = ?"      => $this->getAutenticacao()->getIdentity(),
                                          "ru.CD_RELATORIO = ?"    => $params["cd_relatorio"],
                                          "ru.NO_MODULO like ?"    => $params["no_modulo"],
                                          "ru.DS_GRUPO like ?"     => $params["ds_grupo"],
                                          "ru.NO_RELATORIO like ?" => $params["no_relatorio"])
                                    )->getWhere();
        
                                    
        // ordenação
        $order = null;
        if(isset($params['column'])){
            $order = $params['column'] . " " . $params['orderby'];
        }
        
        // Define os parâmetros para a consulta e retorna o resultado da pesquisa
        $resConsulta = $usuRels->getRelatorios($where, $order);
        
        // Define a página
        $pagina = $this->_getParam('pagina', 1);
        
        // Instancia e seta o paginador
        $paginator = Zend_Paginator::factory($resConsulta);
        $paginator->setCurrentPageNumber($pagina);
        $paginator->setItemCountPerPage(10);
        
        // Joga pra view o objeto paginado
        $this->view->paginator = $paginator;
        
        // Reenvia os valores para o formulário
        $this->_helper->RePopulaFormulario->repopular($params);
    }

    /**
     * Metodo selecionar
     * método do padrão da marca
     * objetivo: utilizado para selecionar um registro a partir de uma listagem
     */
    public function selecionarAction() {
        // Captura os parâmetros passados pela requisição
        $params = $this->_getAllParams();
        
        // Pega o código do relatório
        $cd_relatorio = $params["cd_relatorio"];
        
        // Código do relatório
        $this->view->cd_relatorio = $cd_relatorio;
        
        // Instancia a classe que irá montar o relatório
        $geraRel = new Marca_GeraRelatorio($cd_relatorio, null, $_SESSION["PARAM"][0]['no_empr']);
        
        // Recupera os parâmetros do relatório
        $campos = array();
        if($geraRel->consideracoesCol != '' && $geraRel->labels != '') {
            for($x = 1; $x <= $geraRel->labelsNum; $x++) {
                $campos[$x - 1] = array("NOME_CAMPO"    => "var[" . ($x - 1) . "]",
                                        "LABEL"         => $geraRel->labels[$x], 
                                        "RELACIONAL"    => $geraRel->relacionais[$x], 
                                        "VALOR_INICIAL" => $geraRel->valorinicial[$x]);
            }
            
            // Retorna os valores
            $this->view->campos = $campos;
        
        }
        
    }

    /**
     * Metodo relatorio
     * método do padrão da marca
     * objetivo: utilizado para gerar um relatorio a partir de uma listagem
     */
    public function relatorioAction() {
        
        ini_set('max_execution_time', 180);
        ini_set('memory_limit', '256M');
        
        // Desabilita o layout padrão
        $this->_helper->layout->disableLayout();

        // Não deixa a view renderizar
        $this->_helper->viewRenderer->setNoRender(TRUE);

        // Captura os parametros passados por GET
        $params = $this->getRequest()->getParams();

        // Associa as variáveis do banco
        $db = Zend_Registry::get('db');

        // Carrega a sessão do usuário
        $sessao = new Zend_Session_Namespace('portoweb');

        // Inicializa a variavel como um array
        $resConsulta = array();
        
        // Instancia o modelo de relatórios
        $usuarios = new UsuarioModel();
        
        // Captura os dados do usuário
        $this->view->usuario = $usuarios->fetchRow("CD_USUARIO LIKE '" . $this->getAutenticacao()->getIdentity() . "'");
        
        // Instancia o modelo de relatórios
        $usuRels  = new UsuarioRelatorioModel();

        try {
            // Define os filtros para a cosulta
            $where = $usuRels->addWhere(array("ur.CD_USUARIO = ?"      => $this->getAutenticacao()->getIdentity(),
                                              "ru.CD_RELATORIO = ?"    => $params["_cd_relatorio"],
                                              "ru.NO_MODULO like ?"    => $params["no_modulo"],
                                              "ru.DS_GRUPO like ?"     => $params["ds_grupo"],
                                              "ru.NO_RELATORIO like ?" => $params["no_relatorio"])
                                        )->getWhere();
            
            // Define os parâmetros para a consulta e retorna o resultado da pesquisa
            $resConsulta = $usuRels->getRelatorios($where);

            $this->view->resConsulta = $resConsulta;
            
            // Renderiza a view jogando para a variavel
            $html = $this->view->render('exec-rel/relatorio.phtml');
            
            // Monta o PDF com os dados da view
            $this->view->domPDF($html, "a4", $sessao->format_rel);

        } catch (Exception $e) {
            echo "Erro: " . $e->getMessage() . "<br />";
            Zend_Debug::dump($e);
        }

        // Limpa os objetos da memoria
        unset($usuRels);
        
    }

    
    /**
     * Gera um relatório HTML a partir do gerador de relatório
     * 
     *  @return void
     */
    public function geraRelatorioHtmlAction() {
        
        // Desabilita o layout padrão
        $this->_helper->layout->disableLayout();
        
        // Captura os parâmetros passados pela requisição
        $params = $this->_getAllParams();
        
        // Pega o código do relatório
        $cd_relatorio = $params["_cd_relatorio"];
        
        if(! isset($params["var"])) {
            $params["var"] = true;
        }
        
        // Instancia a classe que irá montar o relatório
        $geraRel = new Marca_GeraRelatorio($cd_relatorio, $params["var"], $_SESSION["PARAM"][0]['no_empr']);
        
        $htm = new Marca_HtmMcTable(20);
        $htm->showHeader = true;
        $htm->Header();
        $htm->SetWidths($geraRel->tamanhos);
        $reg = $dados[0];
        for($x=0;$x<sizeof($reg);$x++){
            if(is_numeric($reg[$x])){
                $al[$x]='right';
            }else{
                $al[$x]='left';
            }
        }
        $htm->aligns        = $al;
        //$htm->showRow($geraRel->cabecalhos, true, true, 'cab');
        $htm->cabecalhos    = $geraRel->cabecalhos;
        $htm->espacejamento = $geraRel->espacejamento;
        $htm->formatos      = $geraRel->formatos;
        $htm->quebras       = $geraRel->quebras;
        $htm->totais        = $geraRel->totais;
        $htm->dadosNumRegs  = count($geraRel->resultado);
        $htm->dados         = true;
        
        $dados = array();
        $i     = 0;
        foreach($geraRel->resultado as $resultado){
            foreach($resultado as $indice => $valor) {
                $dados[$i][] = $valor;
            }
            $i++;
        }
        
        if(count($dados) > 0) {
            foreach($dados as $row) {
                $htm->Row($row, true);
            }
        }
                
        // Seta os parâmetros na view
        $this->view->cabecalho    = $geraRel->cabecalho;
        $this->view->no_porto     = $geraRel->no_porto;
        $this->view->cd_relatorio = $geraRel->cd_relatorio;
        $this->view->no_relatorio = $geraRel->no_relatorio;
        
        // Joga a consulta para a view
        $this->view->resultado    = $htm->resultado;
        
    }
    
    
    /**
     * Gera um relatório PDF a partir do gerador de relatório
     * 
     *  @return void
     */
    public function geraRelatorioPdfAction() {
        
        // Desabilita o layout padrão
        $this->_helper->layout->disableLayout();
        
        // Captura os parâmetros passados pela requisição
        $params = $this->_getAllParams();
        
        // Pega o código do relatório
        $cd_relatorio = $params["_cd_relatorio"];
        
        if(! isset($params["var"])) {
            $params["var"] = true;
        }
        
        // Instancia a classe que irá montar o relatório
        $geraRel = new Marca_GeraRelatorio($cd_relatorio, $params["var"], $_SESSION["PARAM"][0]['no_empr']);
        
        // Joga o total de linhas na variável
        $dadosNumRegs = count($geraRel->resultado);
                
        // Joga o resultado na variável
        $dados  = $geraRel->resultado;
        
        $no_rel = $geraRel->no_relatorio;
        $wPage  = @array_sum($geraRel->tamanhos);
        $pdf    = new Marca_PdfMcTable(strtoupper($geraRel->orientacao), 'mm', 'A4');
        
        $pdf->AliasNbPages();
        
        if(isset($dialogo)) {
            for($x = 1; $x <= $dialogo; $x++) {
                $pos = strpos($no_rel, '&');
                if($pos !=0 )
                    $no_rel = substr_replace($no_rel, $params["var"][$x - 1], $pos, 1);
            }
        }
        
        $cd_relatorio    = $geraRel->cd_relatorio;
        $pdf->showHeader = true;
        $pdf->showFooter = true;
        
        $geraRel->fonte         = "Arial";
        $geraRel->tamanho_fonte = 8;
        
        $pdf->AddPage();
        //fonte inicial do pdf
        $pdf->SetFont($geraRel->fonte, '', $geraRel->tamanho_fonte);
        //Largura das linhas de descricao (mesma do cabecalho do documento)
        //$pdf->SetWidths(array($pdf->wCab));
        //setando larguras das colunas do Cabecalho
        //echo $geraRel->tamanhos[1];
        $pdf->SetWidths($geraRel->tamanhos);
        //setando a fonte para o cabecalho
        $pdf->SetFont($geraRel->fonte, 'B', $geraRel->tamanho_fonte);
        //setando alinhamentos do cabecalho
        for($x = 0; $x < sizeof($dados); $x++) {
            if(is_numeric($reg[$x])) {
                $al[$x] = 'R';
            } else {
                $al[$x] = 'L';
            }
        }
        
        $pdf->aligns = $al;
        
        //setando textos do cabecalho
        $pdf->showRow($geraRel->cabecalhos, true, true, true);
        //quebra de linha
        $pdf->Ln(3);
        //setando a fonte para listagem de dados
        $pdf->cabecalhos    = $geraRel->cabecalhos;
        $pdf->SetFont($geraRel->fonte, '', $geraRel->tamanho_fonte);
        $pdf->espacejamento = $geraRel->espacejamento;
        $pdf->formatos      = $geraRel->formatos;
        $pdf->quebras       = $geraRel->quebras;
        $pdf->totais        = $geraRel->totais;
        $pdf->dadosNumRegs  = $dadosNumRegs;
        $pdf->dados         = true;
        
        $dados = array();
        $i     = 0;
        foreach($geraRel->resultado as $resultado){
            foreach($resultado as $indice => $valor) {
                $dados[$i][] = $valor;
            }
            $i++;
        }
        
        if(count($dados) > 0) {
            foreach($dados as $row) {
                $pdf->Row($row, true);
            }
        }
        
        if(! isset($pdf->debug)) {
            $pdf->Output();
        }
        
    }
    
    
    /**
     * Gera um relatório em xsl (Excel) a partir do gerador de relatório
     * 
     *  @return void
     */
    public function geraRelatorioXslAction() {
        
        // Desabilita o layout padrão
        $this->_helper->layout->disableLayout();
        
        // Não renderiza a página
        $this->_helper->viewRenderer->setNoRender();
        
        // Captura os parâmetros passados pela requisição
        $params = $this->_getAllParams();
        
        // Pega o código do relatório
        $cd_relatorio = $params["_cd_relatorio"];
        
        if(! isset($params["var"])) {
            $params["var"] = true;
        }
        
        // Instancia a classe que irá montar o relatório
        $geraRel = new Marca_GeraRelatorio($cd_relatorio, $params["var"], $_SESSION["PARAM"][0]['no_empr']);
        
        // Joga o total de linhas na variável
        $dadosNumRegs = count($geraRel->resultado);
                
        // Joga o resultado na variável
        $dados  = $geraRel->resultado;
        
        $excel  = new Marca_XslMcTable($this->getAutenticacao()->getIdentity(), $cd_relatorio);

        $excel->writeLine($geraRel->cabecalhos);
        
        $dados = array();
        $i     = 0;
        foreach($geraRel->resultado as $resultado){
            foreach($resultado as $indice => $valor) {
                $dados[$i][] = $valor;
            }
            $i++;
        }
        
        if(count($dados) > 0) {
            foreach($dados as $row) {
                $excel->writeLine($row);
            }
        }
        
        $excel->close();
        $filename = $excel->handler;
        $filename = realpath($filename);
        $ctype="application/vnd.ms-excel";
        if (!file_exists($filename)) {
           die("NO FILE HERE");
        }
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);
        header("Content-Type: $ctype");
        header("Content-Disposition: attachment; filename=\"".basename($filename)."\";");
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: ".filesize($filename));
        set_time_limit(0);
        @readfile("$filename") or die("File not found.");
        @unlink($filename);
        
    }
    
}
