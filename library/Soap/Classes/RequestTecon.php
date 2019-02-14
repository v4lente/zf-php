<?php
/** 
 * Classe responsavel por manipular as mercadorias
 */
class Mercadoria {
    /** 
     * Codigo do Sistema do TECON referente a mercadoria do TECON.
     * 
     * @var integer 
     */
    public $cd_merc_tecon;
	/** @var string */
    public $no_merc_tecon;
	/** @var integer */
    public $cd_ncm;
	/** @var integer */
    public $ce_mercante;
    /** 
     * Formato aaa-mm-ddThh24:mi:ss
     * 
     * @var dateTime 
     */
    public $dthr_ent_sai;
	/** @var float */
    public $vl_receita_cambial;
	/** @var string */
    public $unidade_merc;
	/** @var integer */
    public $qtd_merc;
	/** @var integer */
    public $peso_merc;
	/** 
     * S=SIM, N=NAO
     * 
     * @var string
     */
    public $fl_carga_perigosa;
    /** 
     * Sigla de dois digitos do Pais.
     * 
     * @var string 
     */
    public $sg_pais_orig_dest_merc;
	/** 
     * Sigla de tres digitos do Porto.
     * 
     * @var string 
     */
    public $sg_porto_orig_dest_merc;
	/** 
     * Municpio.
     * 
     * @var string 
     */
    public $munic_orig_dest_merc;
    /** 
     * Estado de origem ou destino da mercadoria.
     * 
     * @var string 
     */
    public $uf_orig_dest_merc;
    /** 
     * Sigla de dois digitos do Pais de origem
     * ou destino final da mercadoria. Utilizado
     * para a cobranca.
     * 
     * @var string 
     */
    public $sg_pais_orig_dest_merc_eds;
	/** 
     * Sigla de trs digitos do Porto de origem
     * ou destino final da mercadoria. Utilizado
     * para a cobranca.
     * 
     * @var string 
     */
    public $sg_porto_orig_dest_merc_eds;
}

/** 
 * Classe responsavel por armazenar as mercadorias do container
 */
class Mercadorias {
    /** 
     * Classe Mercadoria
     * 
     * @var Mercadoria 
     */
    public $mercadoria = '';
}

/** 
 * Classe responsavel por manipular os containeres
 */
class Conteiner {
    /** 
     * Identificador do registro do TECON.
     * 
     * @var integer 
     */
    public $nr_id_tecon;
	/** @var string */
    public $cd_conteiner;
	/** @var string */
    public $tp_conteiner;
	/** @var string */
    public $tp_transbordo;
	/** @var string */
    public $tp_rotacao;
    /** @var string */
    public $iso_conteiner;
	/** @var integer */
    public $peso_tara;
	/** @var integer */
    public $peso_bruto;
    /** 
     * Formato aaa-mm-ddThh24:mi:ss
     * 
     * @var dateTime 
     */
    public $dthr_ent_sai;
    /** 
     * Sigla de dois digitos do Pais.
     * 
     * @var string 
     */
    public $sg_pais_orig_dest_cont;
	/** 
     * Sigla de tres digitos do Porto.
     * 
     * @var string 
     */
    public $sg_porto_orig_dest_cont;
	/** 
     * Municpio.
     * 
     * @var string 
     */
    public $munic_orig_dest_cont;
    /** 
     * Estado de origem ou destino do conteiner vazio.
     * 
     * @var string 
     */
    public $uf_orig_dest_cont;
    /** 
     * Sigla de dois digitos do Pais de origem
     * ou destino final do conteiner vazio. Utilizado
     * para a cobranca.
     * 
     * @var string 
     */
    public $sg_pais_orig_dest_cont_eds;
	/** 
     * Sigla de trs digitos do Porto de origem
     * ou destino final do conteiner vazio. Utilizado
     * para a cobranca.
     * 
     * @var string 
     */
    public $sg_porto_orig_dest_cont_eds;
	/** 
     * Classe Mercadorias
     * 
     * @var Mercadorias 
     */
    public $mercadorias;
}

/** 
 * Classe responsavel por manipular os conteiners
 */
class Conteineres {
    /** 
     * Classe Conteiner
     * 
     * @var Conteiner
     */
    public $conteiner = '';
}

/** 
 * Classe responsavel por manipular as cargas gerais
 */
class Carga {
    /** 
     * Identificador do registro do TECON.
     * 
     * @var integer 
     */
    public $nr_id_tecon;
	/** 
     * Formato aaaa-mm-ddThh24:mi:ss
     * 
     * @var dateTime 
     */
    public $dthr_ent_sai;
	
	public $tp_carga = '';
	/** 
     * Codigo do Sistema do TECON referente a mercadoria do TECON.
     * 
     * @var integer 
     */
    public $cd_merc_tecon;
	/** @var string */
    public $no_merc_tecon;
	/** @var integer */
    public $cd_ncm;
	/** @var string */
    public $unidade_merc;
	/** @var integer */
    public $qtd_merc;
	/** @var integer */
    public $peso_merc;
	/** @var float */
    public $vl_receita_cambial;
	/** @var integer */
    public $ce_mercante;
    /** 
     * S=SIM, N=NAO
     * 
     * @var string
     */
    public $fl_carga_perigosa;
    /** 
     * Sigla de dois digitos do Pais.
     * 
     * @var string 
     */
    public $sg_pais_orig_dest_merc;
	/** 
     * Sigla de tres digitos do Porto.
     * 
     * @var string 
     */
    public $sg_porto_orig_dest_merc;
	/** 
     * Municpio.
     * 
     * @var string 
     */
    public $munic_orig_dest_merc;
    /** 
     * Estado de origem ou destino da mercadoria.
     * 
     * @var string 
     */
    public $uf_orig_dest_merc;
    /** 
     * Sigla de dois digitos do Pais de origem
     * ou destino final da mercadoria. Utilizado
     * para a cobranca.
     * 
     * @var string 
     */
    public $sg_pais_orig_dest_merc_eds;
	/** 
     * Sigla de trs digitos do Porto de origem
     * ou destino final da mercadoria. Utilizado
     * para a cobranca.
     * 
     * @var string 
     */
    public $sg_porto_orig_dest_merc_eds;
}

/** 
 * Classe responsavel por manipular as cargas gerais
 */
class Cargas {
    /** 
     * Classe Carga
     * 
     * @var Carga
     */
    public $carga = '';
}

/** 
 * Classe responsavel por armazenar as cargas gerais e conteineres
 */
class Operacao {
    /** 
	 * E=EMBARQUE, D=DESEMBARQUE, R=ROTACAO, T=TRANSBORDO.
	 *
	 * @var string
	 */
    public $tp_operacao = '';    
    /**
     * CGU=CARGA GERAL UNITIZADA, CGN=CARGA GERAL NO UNITIZADA,
     * CC=CONTAINER CHEIO, CV=CONTAINER VAZIO.
     *  
     * @var string 
     */
    public $tp_carga = '';
    /** 
     * No utilizar sigla
     * 
     * @var string 
     */
    public $no_agente;
    /** 
     * Sem formatao
     * 
     * @var string 
     */
    public $cnpj_agente;
    /** 
     * No utilizar sigla
     * 
     * @var string 
     */
    public $no_armador;
    /** 
     * Sem formatao
     * 
     * @var string 
     */
    public $cnpj_armador;
    /** 
     * No utilizar sigla
     * 
     * @var string 
     */
    public $no_imp_exp;
    /** 
     * Sem formatao
     * 
     * @var string 
     */
    public $cnpj_imp_exp;
    /** @var integer */
    public $nr_berco;
    /** 
     * Classe Cargas
     * 
     * @var Cargas 
     */
    public $cargas = '';
    /** 
     * Classe Conteineres
     * 
     * @var Conteineres 
     */
    public $conteineres = '';
}

/** 
 * Classe responsavel por armazenar as operacoes de cargas
 */
class Operacoes {
    /** 
     * Classe Operacao
     * 
     * @var Operacao
     */
    public $operacao = '';
}


/** 
 * Classe responsavel por manipular as atracacoes
 */
class Atracacao {
    /** 
     * Formato aaa-mm-ddThh24:mi:ss
     * 
     * @var dateTime 
     */
    public $dthr_atracacao;
    /** 
     * Formato aaa-mm-ddThh24:mi:ss
     * 
     * @var dateTime 
     */
    public $dthr_desatrac;
    /** 
     * Formato aaa-mm-ddThh24:mi:ss
     * 
     * @var dateTime 
     */
    public $dthr_ini_oper;
    /** 
     * Formato aaa-mm-ddThh24:mi:ss
     * 
     * @var dateTime 
     */
    public $dthr_fim_oper;
    /** @var integer */
    public $qtde_reb_atrac;
    /** @var integer */
    public $qtde_reb_desatrac;
    /** @var float */
    public $calado_chegada_proa;
    /** @var float */
    public $calado_chegada_popa;
    /** @var float */
    public $calado_saida_proa;
    /** @var float */
    public $calado_saida_popa;
    /** 
     * Classe Operacoes
     * 
     * @var Operacoes
     */
    public $operacoes;
}

/** 
 * Classe Atracacoes
 */
class Atracacoes {
	/** 
     * Classe Atracacao
     * 
     * @var atracacao
     */
    public $atracacao = '';	
}
/** 
 * Classe responsavel por manipular cada PDA
 */
class Fundeio {
    /** 
     * LC=LONGO CURSO, CB=CABOTAGEM, NI=NAVEGAO INTERIOR
     * 
     * @var string 
     */
    public $tp_viagem;    
    /** @var integer */
    public $nr_ped_atrac;
    /** 
     * Ano com 4 digitos
     * 
     * @var integer 
     */
    public $ano_ped_atrac;
    /** 
     * No utilizar sigla
     * 
     * @var string 
     */
    public $no_embarc;
    /** 
     * Mesmo nmero Lloyds Register da embarcao
     * 
     * @var integer 
     */
    public $nr_imo;
    /** 
     * Classe Atracacoes
     * 
     * @var Atracacoes 
     */
    public $atracacoes = '';
}

/** 
 * Classe responsavel por guardar os fundeios
 */
class Fundeios {
    /** 
     * Classe Fundeio
     * 
     * @var Fundeio
     */
    public $fundeio = '';
}

/** 
 * Classe responsavel pelo documento xml
 */
class Document {
    /** 
     * Classe Fundeios
     * 
     * @var Fundeios 
     */
    public $fundeios = '';
}

/** 
 * Classe responsavel enviar a requisicao
 */
class Request {
    /** 
     * Classe Document
     * 
     * @var Document 
     */
    public $document = '';
}
?>