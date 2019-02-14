<?php
/**
 * Created on 26/10/2009
 *
 * Manipula a linha da classe UsuarioModel
 *
 * @filesource
 * @author          Márcio Souza Duarte, David Valente
 * @copyright       Copyright 2010 Marca
 * @package         zendframework
 * @subpackage      zendframework.application.models
 * @version         1.0
 */

class UsuarioRow extends Zend_Db_Table_Row_Abstract {

	
	private $perfil;
	
    /**
     * (non-PHPdoc)
     * @see library/Zend/Db/Table/Row/Zend_Db_Table_Row_Abstract#init()
     */
    public function init(){
        
        if(isset($this->NO_USUARIO)) {
            $filter = new Zend_Filter_StringToUpper();
            $filter->setEncoding("iso-8859-1");
            
            $this->NO_USUARIO = $filter->filter($this->NO_USUARIO);            
        }
        
    }

    /**
     * Retorna os grupos do usuário
     *
     * @return array
     */
	public function getGrupos(){

        try {
            // Pega o adaptador padrão do banco
            $db = Zend_Registry::get('db');

            // Busca todos os grupos cadastrados para o usuário
            $select = $db->select()->from(array("gru"=>"WEB_GRUPO"), array("gru.CD_GRUPO, gru.NO_GRUPO"))
                                   ->join(array("grupusu"=>"WEB_GRUPO_USUARIO"), "gru.CD_GRUPO = grupusu.CD_GRUPO", array())
                                   ->where("UPPER(TRIM(grupusu.CD_USUARIO)) = UPPER(TRIM('{$this->CD_USUARIO}'))");
            $gruposUsuario = $db->fetchAll($select);
            $grupos = array();
            foreach($gruposUsuario as $grupo) {
                $grupos[$grupo->CD_GRUPO] = $grupo->NO_GRUPO;
            }

            return $grupos;

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Retorna as transações do usuário
     * na forma de array de objetos
     *
     * @return array
     */
    public function getTransacoes(){

        try {
            // Pega o adaptador padrão do banco
            $db = Zend_Registry::get('db');
            
            // Busca todas as transações cadastradas para o usuário logado
			$select = $db->select()
			             ->distinct()
			             ->from(array("grupusu"	 => "WEB_GRUPO_USUARIO"), array("grutrans.CD_GRUPO",
			             														"trans.CD_TRANSACAO",
			                                                                    "trans.OBJ_EXECUTADO",
			             														"acao.CD_ACAO",
			             														"acao.NO_ACAO",
			             														"sist.NO_PASTA_ZEND",
			             														"temp.NO_PASTA_ZEND2"))
			             ->join(array("gru"      => "WEB_GRUPO"), 			"grupusu.CD_GRUPO        = gru.CD_GRUPO",         array())
						 ->join(array("grutrans" => "WEB_GRUPO_TRANSACAO"), "gru.CD_GRUPO      	     = grutrans.CD_GRUPO",    array())
						 ->join(array("trans"	 => "WEB_TRANSACAO"), 	    "grutrans.CD_TRANSACAO   = trans.CD_TRANSACAO",   array())
						 ->join(array("acao"	 => "WEB_ACAO"), 	        "(trans.FL_TIPO_TRANSACAO = acao.FL_TIPO_ACAO OR acao.FL_TIPO_ACAO = 'C') 
                                                                        AND (grutrans.FL_PERMISSAO   = acao.FL_PERMISSAO 
                                                                         OR  acao.FL_PERMISSAO       = 'C')",                 array())
						 ->join(array("menusis"  => "WEB_MENU_SISTEMA"),    "trans.CD_MENU 	  	     = menusis.CD_MENU",      array())
					     ->join(array("sist"     => "WEB_SISTEMA"),         "menusis.CD_SISTEMA      = sist.CD_SISTEMA",      array())
						 ->joinLeft(array("temp" => new Zend_Db_Expr("(SELECT CD_SISTEMA, NO_PASTA_ZEND AS NO_PASTA_ZEND2 FROM WEB_SISTEMA)")), "trans.CD_SISTEMA_ORIGEM = temp.CD_SISTEMA", array())
						->where("UPPER(TRIM(grupusu.CD_USUARIO)) = TRIM('{$this->CD_USUARIO}')")
						->where("trans.AMB_DESENV = 'ZEND'")
						->where("trans.OBJ_EXECUTADO IS NOT NULL")
						->group(array("grutrans.CD_GRUPO", 
									  "trans.CD_TRANSACAO", 
									  "trans.OBJ_EXECUTADO", 
									  "acao.CD_ACAO", 
								      "acao.NO_ACAO", 
									  "sist.NO_PASTA_ZEND",
									  "temp.NO_PASTA_ZEND2"))
                        ->order(array("grutrans.CD_GRUPO", "trans.CD_TRANSACAO", "acao.CD_ACAO"));
            
            $transacoesUsuario = $db->fetchAll($select);
            
            $transacoes        = array();
            foreach($transacoesUsuario as $transacao) {
                $transacoes[] = $transacao;
            }

            return $transacoes;

        } catch (Exception $e) {
            echo $e->getMessage();die;
        }
    }

	/**
     * Retorna os sistemas do usuário
     * na forma de array de objetos
     *
     * @return array
     */
    public function getSistemas() {

    	try {
	    	// Pega o adaptador padrão do banco
			$db = Zend_Registry::get('db');
			
            $select = $db->select()
                         ->distinct()
                         ->from(array("usu"=>"USUARIO"), array("wsist.CD_SISTEMA", 
                         									   "wsist.NO_SISTEMA", 
                         									   "wsist.NO_PASTA_ZEND"))
						 ->join(array("wgrpusu"       => "WEB_GRUPO_USUARIO"), 	 "wgrpusu.CD_USUARIO 		 = usu.CD_USUARIO",         array())
						 ->join(array("wgrp"          => "WEB_GRUPO"), 			 "wgrpusu.CD_GRUPO 			 = wgrp.CD_GRUPO",          array())
						 ->join(array("wgrptrans"     => "WEB_GRUPO_TRANSACAO"), "wgrptrans.CD_GRUPO 		 = wgrp.CD_GRUPO",          array())
						 ->join(array("wgrptransacao" => "WEB_TRANSACAO_ACAO"),  "wgrptransacao.CD_TRANSACAO = wgrptrans.CD_TRANSACAO", array())
						 ->join(array("wtrans"        => "WEB_TRANSACAO"), 		 "wgrptransacao.CD_TRANSACAO = wtrans.CD_TRANSACAO",    array())
						 ->join(array("wmensist"      => "WEB_MENU_SISTEMA"), 	 "wtrans.CD_MENU 			 = wmensist.CD_MENU",       array())
						 ->join(array("wsist"         => "WEB_SISTEMA"), 		 "wmensist.CD_SISTEMA 		 = wsist.CD_SISTEMA",       array())
						 ->where("TRIM(usu.CD_USUARIO) = TRIM('{$this->CD_USUARIO}')")
						 ->where("wtrans.OBJ_EXECUTADO IS NOT NULL")
						 ->where("wtrans.FL_VISIVEL = 1")
						 ->where("TRUNC(SYSDATE) >= TRUNC(wgrpusu.DT_ACESSO_INI)")
						 ->where("(wgrpusu.DT_ACESSO_FIM IS NULL OR TRUNC(SYSDATE) <= TRUNC(wgrpusu.DT_ACESSO_FIM))")						 
						 ->order("wsist.NO_SISTEMA ASC");
						
			$sistemasUsuario = $db->fetchAll($select);
			
			$sistemas        = array();
	        foreach($sistemasUsuario as $sistema) {
	        	$sistemas[] = $sistema;
	        }
	        
	        return $sistemas;

    	} catch (Exception $e) {
    		echo $e->getMessage();
    	}
    }
    
        
	/**
     * Retorna o perfil do usuário
     *
     * @return object
     */
    public function getPerfil() {

    	try {
	    	// Pega o adaptador padrão do banco
			$db = Zend_Registry::get('db');
			
			// Monta a consulta que gera as configurações do usuário
	        $sql = 	"Select usuario.CD_USUARIO, usuario.NO_USUARIO, f.NR_MATRIC, f.NO_FUNC, l.NO_LOCAL ,NO_EMPR, usuario.CD_EMPR ,TRIM(TO_CHAR(SUBSTR(EMPR_CGC,1,1),'00')) || '.' || TRIM(TO_CHAR(SUBSTR(EMPR_CGC,2,3),'000')) || '.' || TRIM(TO_CHAR(SUBSTR(EMPR_CGC,5,3),'000')) || '/' || TRIM(TO_CHAR(SUBSTR(EMPR_CGC,8,4),'0000')) || '-' || TRIM(TO_CHAR(SUBSTR(EMPR_CGC,12,2),'00')) EMPR_CGC ,".
					"EMPR_ENDERECO ,EMPR_MUNIC , usuario.FL_SUPER, ".
					"EMPR_UF ,EMPR_PAIS , ".
					"TRIM(TO_CHAR(SUBSTR(EMPR_CEP,1,2),'00')) || '.' || TRIM(TO_CHAR(SUBSTR(EMPR_CEP,3,3),'000')) || '-' || TRIM(TO_CHAR(SUBSTR(EMPR_CEP,6,3),'000')) EMPR_CEP,".
					"'(' || TRIM(TO_CHAR(SUBSTR(EMPR_FONE,1,2),'00')) || ')' || TRIM(TO_CHAR(SUBSTR(EMPR_FONE,3,3),'000')) || '-' || TRIM(TO_CHAR(SUBSTR(EMPR_FONE,6,2),'00')) || TRIM(TO_CHAR(SUBSTR(EMPR_FONE,8,2),'00')) EMPR_FONE, ".
					"'(' || TRIM(TO_CHAR(SUBSTR(EMPR_FAX,1,2),'00')) || ')' || TRIM(TO_CHAR(SUBSTR(EMPR_FAX,3,3),'000')) || '-' || TRIM(TO_CHAR(SUBSTR(EMPR_FAX,6,2),'00')) || TRIM(TO_CHAR(SUBSTR(EMPR_FAX,8,2),'00')) EMPR_FAX, ".
					"EMPR_CX_POSTAL ,NOME_SUPERINT ,".
					"NVL(UC.CD_CLIENTE,0) as CD_CLIENTE_PRINCIPAL, ".
	                "usuario.tp_usuario as TP_USUARIO, ".
					"Decode (usuario.CD_IDIOMA, null, param_sist.CD_IDIOMA, usuario.CD_IDIOMA) as CD_IDIOMA, ".
					"Decode (usuario.COLOR_BODY, null, param_sist.COLOR_BODY, usuario.COLOR_BODY) as COLOR_BODY, ".
					"Decode (usuario.BGCOLOR_BODY, null, param_sist.BGCOLOR_BODY, usuario.BGCOLOR_BODY) as BGCOLOR_BODY, ".
					"Decode (usuario.COLOR_TITULO, null, param_sist.COLOR_TITULO, usuario.COLOR_TITULO) as COLOR_TITULO, ".
					"Decode (usuario.BGCOLOR_TITULO, null, param_sist.BGCOLOR_TITULO, usuario.BGCOLOR_TITULO) as BGCOLOR_TITULO, ".
					"Decode (usuario.COLOR_TABELA, null, param_sist.COLOR_TABELA, usuario.COLOR_TABELA) as COLOR_TABELA, ".
					"Decode (usuario.BGCOLOR_TABELA, null, param_sist.BGCOLOR_TABELA, usuario.BGCOLOR_TABELA) as BGCOLOR_TABELA, ".
					"Decode (usuario.COLOR_CABECALHO, null, param_sist.COLOR_CABECALHO, usuario.COLOR_CABECALHO) as COLOR_CABECALHO, ".
					"Decode (usuario.BGCOLOR_CABECALHO, null, param_sist.BGCOLOR_CABECALHO, usuario.BGCOLOR_CABECALHO) as BGCOLOR_CABECALHO, ".
					"Decode (usuario.BGCOLOR_ONMOUSEOVER, null, param_sist.BGCOLOR_ONMOUSEOVER, usuario.BGCOLOR_ONMOUSEOVER) as BGCOLOR_ONMOUSEOVER, ".
					"Decode (usuario.COLOR_TR_FRACA, null, param_sist.COLOR_TR_FRACA, usuario.COLOR_TR_FRACA) as COLOR_TR_FRACA, ".
					"Decode (usuario.BGCOLOR_TR_FRACA, null, param_sist.BGCOLOR_TR_FRACA, usuario.BGCOLOR_TR_FRACA) as BGCOLOR_TR_FRACA, ".
					"Decode (usuario.COLOR_TR_FORTE, null, param_sist.COLOR_TR_FORTE, usuario.COLOR_TR_FORTE) as COLOR_TR_FORTE, ".
					"Decode (usuario.BGCOLOR_TR_FORTE, null, param_sist.BGCOLOR_TR_FORTE, usuario.BGCOLOR_TR_FORTE) as BGCOLOR_TR_FORTE, ".
					"Decode (usuario.COLOR_LINK, null, param_sist.COLOR_LINK, usuario.COLOR_LINK) as COLOR_LINK, ".
					"Decode (usuario.COLOR_ALINK, null, param_sist.COLOR_ALINK, usuario.COLOR_ALINK) as COLOR_ALINK, ".
					"Decode (usuario.COLOR_VLINK, null, param_sist.COLOR_VLINK, usuario.COLOR_VLINK) as COLOR_VLINK, ".
					"Decode (usuario.COLOR_BOTAO, null, param_sist.COLOR_BOTAO, usuario.COLOR_BOTAO) as COLOR_BOTAO, ".
					"Decode (usuario.COLOR_TEXTO_BOTAO, null, param_sist.COLOR_TEXTO_BOTAO, usuario.COLOR_TEXTO_BOTAO) as COLOR_TEXTO_BOTAO, ".
					"Decode (usuario.BGCOLOR_TEXTO_FORM, null, param_sist.BGCOLOR_TEXTO_FORM, usuario.BGCOLOR_TEXTO_FORM) as BGCOLOR_TEXTO_FORM, ".
					"Decode (usuario.COLOR_TEXTO_FORM, null, param_sist.COLOR_TEXTO_FORM, usuario.COLOR_TEXTO_FORM) as COLOR_TEXTO_FORM, ".
					"Decode (usuario.QT_LINHAS, null, param_sist.QT_LINHAS, usuario.QT_LINHAS) as QT_LINHAS, ".
					"Decode (usuario.BGCOLOR_MENU, null, param_sist.BGCOLOR_MENU, usuario.BGCOLOR_MENU) as BGCOLOR_MENU, ".
					"Decode (usuario.COLOR_LABEL_FORM, null, param_sist.COLOR_LABEL_FORM, usuario.COLOR_LABEL_FORM) as COLOR_LABEL_FORM, ".
                    "param_sist.TEMPO_SEM_ATIV_WEB, ". 
					"param_sist.NO_EMPR, l.CD_LOCAL,  usuario.EMAIL, ".
                                        "usuario.FL_ATIVO, usuario.FL_CERTIFICADO_DIGITAL, usuario.CPF ".
					"from usuario, param_sist,funcionario f,local l, usuario_cliente uc  ".
					"where usuario.cd_usuario Like '{$this->CD_USUARIO}'".
					"  and f.cd_empr	(+)		= usuario.cd_empr 	".
					"  and f.nr_matric	(+)		= usuario.nr_matric	".
					"  and f.cd_local			= l.cd_local (+)".
	                "  and uc.cd_usuario (+) = usuario.cd_usuario ".
	                "  and uc.fl_principal (+) = 1 ";
        
            return $db->fetchRow($sql);
			
    	} catch (Exception $e) {
    		echo $e->getMessage();
    	}
    }
    
	/**
     * Retorna os dados do menu do usuário
     *
     * @return Object
     */
    public function getMenuUsuario() {

        // Monta a consulta que gerará o menu
        $sql = "select nvl(ace.cd_sistema_transacao, ' ') as CD_SISTEMA_TRANSACAO,
                    nvl(ace.cd_sistema_transacao_pai, ' ') as CD_SISTEMA_TRANSACAO_PAI,
                    decode(ace.nr_nivel, 1, nvl(trsis.no_texto, '???'), decode(ace.nr_nivel, 2, decode(ace.no_arq_php,NULL,nvl(trsis.no_texto, '???'), nvl(trtrn.no_texto, '???')), nvl(trtrn.no_texto, '???')))    as no_transacao_traduzida,
                    decode(ace.nr_nivel, 1, nvl(trsis.ds_texto, '???'), decode(ace.nr_nivel, 2, decode(ace.no_arq_php,NULL,nvl(trsis.ds_texto, '???'), nvl(trtrn.no_texto, '???')), nvl(trtrn.ds_texto, '???')))    as DS_TRANSACAO_TRADUZIDA,
                    nvl(ace.no_arq_php, ' ') as NO_ARQ_PHP,
                    decode(ace.cd_sistema, usu.cd_sistema_padrao_internet, 1, 0) as padrao,
                    utr.fl_manutencao,
                    1 as ordem_sql,
                    decode(ace.cd_sistema, 'POR', '   ', sis.no_sistema) as sist
                    , ace.ordem
                    , rownum
                    , ace.ordem_apresent_internet
                    , ace.NO_CONTROLLER
                from v_acessos      ace,
                    Sistema         sis,
                    traducao        trsis,
                    traducao        trtrn,
                    usuario         usu,
                    (select act.cd_sistema||act.cd_transacao as cd_sistema_transacao,
                        max(act.fl_tipo_oper) as fl_manutencao
                    from    usuario_grupo       ugr,
                        acesso_transacao    act,
                        transacao           trn
                    where   ugr.cd_grupo = act.cd_grupo and
                        act.cd_sistema||act.cd_transacao = trn.cd_sistema||trn.cd_transacao and
                        trn.tp_ambiente = 'I' and
                        act.fl_acesso_trans = 1 and
                        ugr.cd_usuario = '{$this->CD_USUARIO}'
                        group by act.cd_sistema||act.cd_transacao
                    ) utr
                where   ace.cd_sistema = sis.cd_sistema
                    and  'APLICATIVO_'||sis.cd_sistema = trsis.cd_texto (+) and (trsis.cd_idioma is null or trsis.cd_idioma = '{$this->perfil->CD_IDIOMA}')
                    and     ace.cd_sistema_transacao = trtrn.cd_texto (+) and (trtrn.cd_idioma is null or trtrn.cd_idioma = '{$this->perfil->CD_IDIOMA}')
                    and ace.cd_sistema_transacao = utr.cd_sistema_transacao
                    and     usu.cd_usuario = '{$this->CD_USUARIO}'

                UNION

                ( select vt.cd_sistema_transacao, vt.cd_sistema_transacao_pai,
                decode(t.no_texto, NULL, vt.no_transacao_traduzida,t.no_texto) as no_transacao_traduzida,
                decode(t.no_texto, NULL, vt.ds_transacao_traduzida,t.ds_texto) as ds_transacao_traduzida,
                vt.no_arq_php, padrao, vt.fl_manutencao,
                DECODE(vt.cd_sistema_transacao,'PORmenu_aplicativos',1,2) as ordem_sql,
                decode(vt.cd_sistema_transacao,'PORmenu_aplicativos','   ',decode(vt.cd_sistema_transacao, 'exec_rel', ' ', vt.no_transacao_traduzida)
                )  as sist,
                rownum
                ,0
                , ''
                , ''
                from v_acesso_exec_rel_web vt, traducao t
                where vt.cd_usuario = '{$this->CD_USUARIO}'
                    and t.cd_idioma (+) = '{$this->perfil->CD_IDIOMA}'
                    and t.cd_texto (+) = vt.no_transacao_traduzida )
                order by 8,9, 12,11 ";

        // Executa a consulta
        return Zend_Registry::get("db")->fetchAll($sql);
    }
	
	/**
     * Verifica se o usuário tem permissão de acesso ao sistema antigo
     *
     * @return boolean
     */
	public function verificaPermissaoSistemaAntigo() {
	
		// Captura a instância do banco
		$db = Zend_Registry::get("db");
		
		// Captura o recurso do front controller
		$front = Zend_Controller_Front::getInstance();
		
		// Verifica se o usuário tem acesso a algum sistema na web
		$sql1 = "SELECT COUNT(*) AS TOTAL
				 FROM USUARIO_GRUPO U, ACESSO_TRANSACAO A, TRANSACAO T
				 WHERE U.CD_USUARIO = '{$this->CD_USUARIO}'
				 AND A.CD_GRUPO = U.CD_GRUPO
				 AND T.CD_TRANSACAO = A.CD_TRANSACAO
				 AND T.TP_AMBIENTE = 'I'";
		
		$linha1 = $db->fetchRow($sql1);
		
		// Verifica se o usuário tem acesso a algum relatório web
		$sql2 = "SELECT COUNT(*) AS TOTAL
				 FROM V_ACESSO_EXEC_REL_WEB
				 WHERE CD_USUARIO = '{$this->CD_USUARIO}'";
				
		$linha2 = $db->fetchRow($sql2);

		// Verifica se o usuário tem permissão de acessar a intranet
		$sql3 = "SELECT FL_ACESSO_INTRANET
				 FROM USUARIO
				 WHERE CD_USUARIO = '{$this->CD_USUARIO}'";
				
		$linha3 = $db->fetchRow($sql3);
		
		// Se uma das consultas retornar 
		if($linha1->TOTAL >= 1 || $linha2->TOTAL >= 1) {
			// Verifica se o usuário tem permissão apenas de acessar a web pela intranet
			$mascaraIpLocal   = "10.76";
			$mascaraIpUsuario = $front->getRequest()->getServer('REMOTE_ADDR');
			
			if($linha3->FL_ACESSO_INTRANET == 1 && strpos($mascaraIpUsuario, $mascaraIpLocal) === false) {
				return false;
				
			} else {
				return true;
			}
			
		} else {
			return false;
		}
		
	}
	
 	/**
     * Verifica se ouve paralisações para os grupos que o usuário pertence
     *
     * @return array
     */
	public function verificaParalisacoes(){

        try {
            // Pega o adaptador padrão do banco
            $db            = Zend_Registry::get('db');
            
            $sql = "SELECT CD_GRUPO_WEB_RFB FROM PARAM_SIST";
            
            $gruposUsuario = $db->fetchRow($sql);                			 											      	
            
            // Caso tenha o grupo do RFB vinculado aos grupos de acesso
            if (trim($gruposUsuario->CD_GRUPO_WEB_RFB) != "") {
            	
            	// Busca o grupo RFB das paralisações e relaciona com os grupos do usuário
	            $select = $db->select()->from(array("gru"=>"WEB_GRUPO_USUARIO"), array(new Zend_Db_Expr("COUNT(*) as TOTAL_RFB")))
	                                   ->where("CD_GRUPO   = ? ", $gruposUsuario->CD_GRUPO_WEB_RFB)
	                                   ->where("CD_USUARIO = ? ", $this->CD_USUARIO);
	                                     
	            $temGrupoRFB = $db->fetchRow($select);
	            
	            // Caso possua o grupo das paralisações vinculado para o usuário
	            if ($temGrupoRFB->TOTAL_RFB > 0) {
	            	
	            	$select2 = $db->select()->from(array("msg"=>"MSG_PARALISACAO"), array(new Zend_Db_Expr("COUNT(*) as TOTAL_PARALIS"))) 
               			            ->where("TIPO = 'P'")
               			            ->where(new Zend_Db_Expr("sysdate") . " BETWEEN DTHR_INI AND DTHR_FIM");
                       
            		$paralisacao = $db->fetchRow($select2);            		
            		
            		// Caso tenha mensagem de paralisação para o periodo
            		if ($paralisacao->TOTAL_PARALIS > 0) {
            			return true;
            		}	            	
	            }            	
            }
            
            return false;
            
            
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
    
	/**
     * Verifica se a conta do usuário esta expirada
     *
     * @return bolean
     */
	public function verificaContaExpirada(){

        try {
            // Pega o adaptador padrão do banco
            $db            = Zend_Registry::get('db');
           
            // Busca o grupo RFB das paralisações e relaciona com os grupos do usuário
	        $select = $db->select()->from(array("P"=>"PARAM_SIST"), array(new Zend_Db_Expr("COUNT(*) as EXPIROU")))
	                               ->where("PZ_VCTO_SENHA < ?", new Zend_Db_Expr("sysdate - TO_DATE(TO_CHAR(TO_DATE('".$this->DT_ALT_SENHA."','DD/MM/YYYY HH24:MI:SS'), 'DD/MM/YYYY'))"));
	                                     
	        $contaExpirada = $db->fetchRow($select);
	            
	        // Caso conta tenha expirado
	        if ($contaExpirada->EXPIROU == 1) {
	        	return true;            			            	
	        }            	
          	return false;
            
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
    
	/**
     * Verifica se o usuário acesso o sistema dentro do período limite
     *
     * @return bolean
     */
	public function verificaPeriodoLimiteAcesso(){

        try {
            // Pega o adaptador padrão do banco
            $db            = Zend_Registry::get('db');
           
            // Busca o grupo RFB das paralisações e relaciona com os grupos do usuário
	        $select = $db->select()->from(array("P"=>"PARAM_SIST"), array(new Zend_Db_Expr("P.PZ_VCTO_ACESSO as LIMITE")))
	                               ->where("P.PZ_VCTO_ACESSO < ?", new Zend_Db_Expr("sysdate - TO_DATE(TO_CHAR(TO_DATE('".$this->DT_ULT_ACESSO."','DD/MM/YYYY HH24:MI:SS'), 'DD/MM/YYYY'))"));
	                                     
	        $contaExpirada = $db->fetchRow($select);
	            
	        // Caso o período de acesso tenha estrapolado
	        if (trim($contaExpirada->LIMITE) != "") {
	        	
	        	return $contaExpirada->LIMITE;            			            	
	        }            	
          	
	        return false;
            
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
	
    /**
     * Retorna o operador portuário do usuario logado
     *
     * @return array
     */
    public function getOperadorPortuarioUsuarioLogado() {
    
    	try {
    		
    		// Pega o adaptador padrão do banco
            $db = Zend_Registry::get('db');
    		
    		// Busca os clientes do usuário
    		$select  = $db->select()
				    		->from(array("CLI" => "CLIENTE"), array("CD_OPERADOR" => "CLI.CD_CLIENTE",
                                                                                                         "CLI.RAZAO_SOCIAL",
				    									 "CLI.NO_FANTASIA",
                                                                                                         "CLI.NR_DOC",
                                                                                        "NR_DOC_FORMATADO" => new Zend_Db_Expr("F_FORMATA_CNPJCPF(CLI.NR_DOC)")))
		    				->where("CLI.FL_OP_PORT = 1")
		    				->where("CLI.FL_ATIVO   = 1")
		    				->where("CLI.CD_CLIENTE  IN (" . (
		    				   $db->select()
                                                        ->from(array("UC" => "USUARIO_CLIENTE"), array("CD_CLIENTE" => new Zend_Db_Expr("MAX(UC.CD_CLIENTE)")))
                                                        ->join(array("C2" => "CLIENTE"), "UC.CD_CLIENTE = C2.CD_CLIENTE", array())
                                                        ->where("UC.CD_USUARIO = '" . $this->CD_USUARIO . "'")
                                                        ->where("UC.FL_PRINCIPAL = 1")
		    				) . ")");
    		
    		// Retorna o operador portuário
    		return $db->fetchRow($select);
    
    	} catch (Exception $e) {
    		echo $e->getMessage();
    	}
    }
    /**
     * Retorna o operador portuário do usuario logado
     *
     * @return array
     */
    public function getAgenciaMaritimaUsuarioLogado() {
    
    	try {
    		
    		// Pega o adaptador padrão do banco
            $db = Zend_Registry::get('db');
    		
    		// Busca os clientes do usuário
    		$select  = $db->select()
				    		->from(array("CLI" => "CLIENTE"), array("CLI.CD_CLIENTE",
                                                                    "CLI.NO_FANTASIA",
                                                                    "RAZAO_SOCIAL" => new Zend_Db_Expr("CLI.RAZAO_SOCIAL || ' - ' || F_FORMATA_CNPJCPF(CLI.NR_DOC)")))
		    				->where("CLI.FL_ATIVO   = 1")
		    				->where("CLI.CD_CLIENTE  IN (" . (
		    				   $db->select()
                                                        ->from(array("UC" => "USUARIO_CLIENTE"), array("CD_CLIENTE" => new Zend_Db_Expr("MAX(UC.CD_CLIENTE)")))
                                                        ->join(array("C2" => "CLIENTE"), "UC.CD_CLIENTE = C2.CD_CLIENTE", array())
                                                        ->where("UC.CD_USUARIO = '" . $this->CD_USUARIO . "'")
                                                        ->where("UC.FL_PRINCIPAL = 1")
		    				) . ")");
    		
    		// Retorna o operador portuário
    		return $db->fetchRow($select);
    
    	} catch (Exception $e) {
    		echo $e->getMessage();
    	}
    }
    
     /**
     * Retorna o operador portuário do usuario logado
     *
     * @return array
     */
    public function getClientePortoUsuarioLogado() {
    
    	try {
    		
    		// Pega o adaptador padrão do banco
            $db = Zend_Registry::get('db');
    		
    		// Busca os clientes do usuário
    		$select  = $db->select()
				    		->from(array("CLI" => "CLIENTE"), array( "CLI.CD_CLIENTE",
                                                                                                         "CLI.RAZAO_SOCIAL",
				    									 "CLI.NO_FANTASIA",
                                                                                                         "CLI.NR_DOC",
                                                                                        "NR_DOC_FORMATADO" => new Zend_Db_Expr("F_FORMATA_CNPJCPF(CLI.NR_DOC)")))
		    				->where("CLI.FL_ATIVO   = 1")
		    				->where("CLI.CD_CLIENTE  IN (" . (
		    				   $db->select()
                                                        ->from(array("UC" => "USUARIO_CLIENTE"), array("CD_CLIENTE" => new Zend_Db_Expr("MAX(UC.CD_CLIENTE)")))
                                                        ->join(array("C2" => "CLIENTE"), "UC.CD_CLIENTE = C2.CD_CLIENTE", array())
                                                        ->where("UC.CD_USUARIO = '" . $this->CD_USUARIO . "'")
                                                        ->where("UC.FL_PRINCIPAL = 1")
		    				) . ")");
    		
    		// Retorna o operador portuário
                //echo($select);die;
    		return $db->fetchRow($select);
    
    	} catch (Exception $e) {
    		echo $e->getMessage();
    	}
    }
    
    
}
?>