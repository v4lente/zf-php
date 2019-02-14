var Mascara = {
    // Método principal da classe
    init: function () {

        try {

            // Seta mascara ANO para 4 digitos    
            if ($(".ano").size() > 0) {
                $(".ano").each(function () {
                    Mascara.setAno(this);
                });
            }

            // seta mascara CEP
            if ($(".cep").size() > 0) {
                $(".cep").each(function () {
                    Mascara.setCep(this);
                });
            }

            /*Alterado dia 21/02/2011 - Guilherme Padilha
             * 	Arrumado valor da máscara
             */
            // seta mascara CNPJ    
            if ($(".cnpj").size() > 0) {
                $(".cnpj").each(function () {
                    Mascara.setCnpj(this);
                });
            }

            // seta mascara CÓDIGO    
            if ($(".codigo").size() > 0) {
                $(".codigo").each(function () {
                    Mascara.setCodigo(this);
                });
            }

            // seta mascara CONTAINER    
            if ($(".container").size() > 0) {
                $(".container").each(function () {
                    Mascara.setContainer(this);
                });
            }

            // seta mascara para Conteiner
            if ($(".conteiner").size() > 0) {
                $(".conteiner").each(function () {
                    Mascara.setConteiner(this);
                });
            }

            // seta mascara CPF    
            if ($(".cpf").size() > 0) {
                $(".cpf").each(function () {
                    Mascara.setCpf(this);
                });
            }

            // Seta o plugin datepicker
            if ($(".data").size() > 0) {
                $(".data").each(function () {
                    Mascara.setData(this);
                });
            }

            // Seta o plugin datepicker    
            if ($(".datahoraminuto").size() > 0) {
                Mascara.setDataHoraMinuto($(".datahoraminuto"));
            }

            // Seta mascara horaminuto
            if ($(".horaminuto").size() > 0) {
                $(".horaminuto").each(function () {
                    Mascara.setHoraMinuto(this);
                });
            }

            // Seta mascara hora
            if ($(".hora").size() > 0) {
                $(".hora").each(function () {
                    Mascara.setHora(this);
                });
            }

            // seta mascara para DI ou BL
            if ($(".dibl").size() > 0) {
                $(".dibl").each(function () {
                    Mascara.setDibl(this);
                });
            }

            // Seta o campo com mascara para DATA e HORA
            if ($(".datahora").size() > 0) {
                $(".datahora").each(function () {
                    Mascara.setDataHora(this);
                });
            }

            // Seta o campo com mascara para DATA e HORA
            if ($(".dthr").size() > 0) {
                $(".dthr").each(function () {
                    Mascara.setDataHora(this);
                });
            }

            // Seta mascara FONE    (Marcelo)
            if ($(".fone").size() > 0) {
                $(".fone").each(function () {
                    Mascara.setFone(this);
                });
            }

            // Seta mascara para tonelada
            if ($(".tonelada").size() > 0) {
                $(".tonelada").each(function () {
                    Mascara.setTonelada(this);
                });
            }

            // Seta mascara para números inteiros
            if ($(".numero").size() > 0) {
                $(".numero").each(function () {
                    Mascara.setNumero(this);
                });
            }

            // Seta mascara para números inteiros
            if ($(".int").size() > 0) {
                $(".int").each(function () {
                    Mascara.setNumero(this);
                });
            }

            // Seta mascara para números inteiros
            if ($(".int_c").size() > 0) {
                $(".int_c").each(function () {
                    Mascara.setNumero(this);
                });
            }

            // Seta mascara para números inteiros
            if ($(".int_e").size() > 0) {
                $(".int_e").each(function () {
                    Mascara.setNumero(this);
                });
            }

            // Seta mascara MES para 2 digitos    
            if ($(".mes").size() > 0) {
                $(".mes").each(function () {
                    Mascara.setMes(this);
                });
            }

            // Seta a mascara MOEDA
            if ($(".moeda").size() > 0) {
                // Seta mascara MOEDA
                $(".moeda").each(function () {
                    Mascara.setMoeda(this);
                });
            }

            // Seta a mascara CALADO
            if ($(".calado").size() > 0) {
                // Seta mascara MOEDA
                $(".calado").each(function () {
                    Mascara.setCalado(this);
                });
            }

            // Seta a mascara PESO
            if ($(".peso").size() > 0) {
                // Seta mascara PESO
                $(".peso").each(function () {
                    Mascara.setPeso(this);
                });
            }


            // Seta a mascara PESO-PORTO
            if ($(".peso-porto").size() > 0) {
                // Seta mascara PESO-PORTO
                $(".peso-porto").each(function () {
                    Mascara.setPesoPorto(this);
                });
            }

            // Seta a mascara QUANTIDADE
            if ($(".qtde").size() > 0) {
                // Seta mascara QUANTIDADE
                $(".qtde").each(function () {
                    Mascara.setQtde(this);
                });
            }

            // Seta a mascara QUANTIDADE
            if ($(".nr_nf").size() > 0) {
                // Seta mascara QUANTIDADE
                $(".nr_nf").each(function () {
                    Mascara.setNrNf(this);
                    //Mascara.removePontuacaoCampo(this);
                });
            }
            // seta mascara para números de processo
            if ($(".nr_proc").size() > 0) {
                $(".nr_proc").each(function () {
                    Mascara.setNrProc(this);
                });
            }

            // seta mascara PDA
            if ($(".pda").size() > 0) {
                $(".pda").each(function () {
                    Mascara.setPda(this);
                });
            }

            // Seta mascara para stru
            if ($(".stru").size() > 0) {
                $(".stru").each(function () {
                    Mascara.setStru(this);
                });
            }

            // Seta mascara intervalo de horarios    
            if ($(".intervalohora").size() > 0) {
                $(".intervalohora").each(function () {
                    Mascara.setIntervaloHora(this);
                });
            }

            // Seta mascara MES/ANO para MM/AAAA    
            if ($(".mesano").size() > 0) {
                $(".mesano").each(function () {
                    Mascara.setMesAno(this);
                });
            }

            // Seta mascara PLACA
            if ($(".placa").size() > 0) {
                // Seta mascara PLACA
                $(".placa").each(function () {
                    Mascara.setPlaca(this);
                });
            }

            if ($(".pl_veic").size() > 0) {
                // Seta mascara PLACA
                $(".pl_veic").each(function () {
                    Mascara.setPlaca(this);
                });
            }

            // Seta mascara PLACA ESTRANGEIRA
            if ($(".placa-estrangeira").size() > 0) {
                // Seta mascara PLACA
                $(".placa-estrangeira").each(function () {
                    Mascara.setPlacaEstrangeira(this);
                });
            }

            if ($(".decimal").size() > 0) {
                // Seta mascara DECIMAL
                $(".decimal").each(function () {
                    Mascara.setDecimal(this, 2);
                });
            }


            // Seta a mascara para aceitar LETRAS somente
            if ($(".letra").size() > 0) {
                // Seta mascara PLACA
                $(".letra").each(function () {
                    Mascara.setLetra(this);
                });
            }

            if ($(".duv").size() > 0) {
                // Seta mascara da DUV
                $(".duv").each(function () {
                    Mascara.setDuv(this);
                });
            }

            if ($(".nr_lote").size() > 0) {
                // Seta mascara da DUV
                $(".nr_lote").each(function () {
                    Mascara.setNrLote(this);
                });
            }
            if ($(".nr_re").size() > 0) {
                // Seta mascara ddo Numero da RE
                $(".nr_re").each(function () {
                    Mascara.setNrRe(this);
                });
            }


        } catch (e) {
            alert("Erro de implementação em MASCARA.JS");
        }
    },
    // Remove todas as máscaras possíveis
    unsetAll: function (obj) {

        if ($(obj).hasClass("ano")) {
            this.unsetAno(obj);
        }
        ;
        if ($(obj).hasClass("cep")) {
            this.unsetCep(obj);
        }
        ;
        if ($(obj).hasClass("codigo")) {
            this.unsetCodio(obj);
        }
        ;
        if ($(obj).hasClass("container")) {
            this.unsetContainer(obj);
        }
        ;
        if ($(obj).hasClass("conteiner")) {
            this.unsetConteiner(obj);
        }
        ;
        if ($(obj).hasClass("cpf")) {
            this.unsetCpf(obj);
        }
        ;
        if ($(obj).hasClass("data")) {
            this.unsetData(obj);
        }
        ;
        if ($(obj).hasClass("horaminuto")) {
            this.unsetHoraMinuto(obj);
        }
        ;
        if ($(obj).hasClass("dibl")) {
            this.unsetDibl(obj);
        }
        ;
        if ($(obj).hasClass("fone")) {
            this.unsetFone(obj);
        }
        ;
        if ($(obj).hasClass("nr_proc")) {
            this.unsetNrProc(obj);
        }
        ;
        if ($(obj).hasClass("intervalohora")) {
            this.unsetIntervaloHora(obj);
        }
        ;
        if ($(obj).hasClass("mesano")) {
            this.unsetMesAno(obj);
        }
        ;
        if ($(obj).hasClass("pda")) {
            this.unsetPda(obj);
        }
        ;
        if ($(obj).hasClass("mes")) {
            this.unsetMes(obj);
        }
        ;
        if ($(obj).hasClass("qtde")) {
            this.unsetQtde(obj);
        }
        ;
        if ($(obj).hasClass("nr_nf")) {
            this.unsetNrNf(obj);
        }
        ;
        if ($(obj).hasClass("moeda")) {
            this.unsetMoeda(obj);
        }
        ;
        if ($(obj).hasClass("calado")) {
            this.unsetCalado(obj);
        }
        ;
        if ($(obj).hasClass("peso")) {
            this.unsetPeso(obj);
        }
        ;
        if ($(obj).hasClass("peso-porto")) {
            this.unsetPesoPorto(obj);
        }
        ;
        if ($(obj).hasClass("placa")) {
            this.unsetPlaca(obj);
        }
        ;
        if ($(obj).hasClass("hora")) {
            this.unsetHora(obj);
        }
        ;
        if ($(obj).hasClass("data")) {
            this.unsetData(obj);
        }
        ;
        if ($(obj).hasClass("dthr")) {
            this.unsetDataHora(obj);
        }
        ;
        if ($(obj).hasClass("datahora")) {
            this.unsetDataHora(obj);
        }
        ;
        if ($(obj).hasClass("decimal")) {
            this.unsetDecimal(obj);
        }
        ;
        if ($(obj).hasClass("numero")) {
            this.unsetNumero(obj);
        }
        ;
        if ($(obj).hasClass("cnpj")) {
            this.unsetCnpj(obj);
        }
        ;
        if ($(obj).hasClass("tonelada")) {
            this.unsetTonelada(obj);
        }
        ;
        if ($(obj).hasClass("letra")) {
            this.unsetLetra(obj);
        }
        ;
        if ($(obj).hasClass("datahoraminuto")) {
            this.unsetDataHoraMinuto(obj);
        }
        ;
        if ($(obj).hasClass("duv")) {
            this.unsetDuv(obj);
        }
        ;
        if ($(obj).hasClass("nr_lote")) {
            this.unsetNrLote(obj);
        }
        ;
        if ($(obj).hasClass("nr_re")) {
            this.unsetNrRe(obj);
        }
        ;

        // Remove todas as classes da máscara
        $(obj).removeClass();

        return $(obj);
    },
    // Remove a máscara ANO
    unsetAno: function (obj) {

        $(obj).unmask().unbind("keydown").unbind("keyup").unbind("keypress");

        return $(obj).removeClass("ano");

    },
    // Seta a mascara QTDE
    setAno: function (obj) {

        try {
            obj = this.unsetAno(obj);
        } catch (e) {
            // alert(e);
        }

        try {
            
            // verifica se o objeto existe
            if($(obj).size() > 0) {
                
                // Verifica se o objeto é um campo input.
                // Caso não seja, cria um, atribui o valor
                // e depois retorna este valor ao objeto original
                if(! $(obj).is(":input")) {
                    var obj2 = $(obj);
                    var obj  = $('<input/>').attr({
                        type  : 'hidden',
                        id    : 'tempId',
                        name  : 'tempName',
                        value : $(obj2).text()
                    }).after($(obj2));
                }
                
                $(obj).mask("9999").addClass("ano");
                
                // Retorna o valor para o objeto original e exclui o temporario
                if(typeof(obj2) !== 'undefined') {
                    $(obj2).addClass("ano").text($(obj).val());
                    $("#tempId").remove();
                }
                
            }
            
        } catch (e) {
            // alert(e);
        }

    },
    // Remove a máscara CEP
    unsetCep: function (obj) {

        $(obj).unmask().unbind("keydown").unbind("keyup").unbind("keypress");

        return $(obj).removeClass("cep");

    },
    // Seta a mascara CEP
    setCep: function (obj) {

        try {
            obj = this.unsetCep(obj);
        } catch (e) {
            // alert(e);
        }

        try {
            
            // verifica se o objeto existe
            if($(obj).size() > 0) {
                
                // Verifica se o objeto é um campo input.
                // Caso não seja, cria um, atribui o valor
                // e depois retorna este valor ao objeto original
                if(! $(obj).is(":input")) {
                    var obj2 = $(obj);
                    var obj  = $('<input/>').attr({
                        type  : 'hidden',
                        id    : 'tempId',
                        name  : 'tempName',
                        value : $(obj2).text()
                    }).after($(obj2));
                }
                
                $(obj).mask("99999-999").addClass("cep");
                
                // Retorna o valor para o objeto original e exclui o temporario
                if(typeof(obj2) !== 'undefined') {
                    $(obj2).addClass("cep").text($(obj).val());
                    $("#tempId").remove();
                }
                
            }
            
        } catch (e) {
            // alert(e);
        }

    },
    // Remove a máscara CNPJ
    unsetCnpj: function (obj) {

        $(obj).unmask().unbind("keydown").unbind("keyup").unbind("keypress");

        return $(obj).removeClass("cnpj");

    },
    // Seta a mascara CNPJ
    setCnpj: function (obj) {

        try {
            obj = this.unsetCnpj(obj);
        } catch (e) {
            // alert(e);
        }

        try {
            
            // verifica se o objeto existe
            if($(obj).size() > 0) {
                
                // Verifica se o objeto é um campo input.
                // Caso não seja, cria um, atribui o valor
                // e depois retorna este valor ao objeto original
                if(! $(obj).is(":input")) {
                    var obj2 = $(obj);
                    var obj  = $('<input/>').attr({
                        type  : 'hidden',
                        id    : 'tempId',
                        name  : 'tempName',
                        value : $(obj2).text()
                    }).after($(obj2));
                }
                
                $(obj).mask("99.999.999/9999-99").addClass("cnpj");
                
                // Retorna o valor para o objeto original e exclui o temporario
                if(typeof(obj2) !== 'undefined') {
                    $(obj2).addClass("cnpj").text($(obj).val());
                    $("#tempId").remove();
                }
                
            }
            
        } catch (e) {
            // alert(e);
        }

    },
    // Remove a máscara CODIGO
    unsetCodigo: function (obj) {

        $(obj).unmask().unbind("keydown").unbind("keyup").unbind("keypress");

        return $(obj).removeClass("codigo");

    },
    // Seta a mascara CODIGO
    setCodigo: function (obj) {

        try {
            obj = this.unsetCodigo(obj);
        } catch (e) {
            // alert(e);
        }

        try {
            
            // verifica se o objeto existe
            if($(obj).size() > 0) {
                
                // Verifica se o objeto é um campo input.
                // Caso não seja, cria um, atribui o valor
                // e depois retorna este valor ao objeto original
                if(! $(obj).is(":input")) {
                    var obj2 = $(obj);
                    var obj  = $('<input/>').attr({
                        type  : 'hidden',
                        id    : 'tempId',
                        name  : 'tempName',
                        value : $(obj2).text()
                    }).after($(obj2));
                }
                
                $(obj).mask("99999999").addClass("codigo");
                
                // Retorna o valor para o objeto original e exclui o temporario
                if(typeof(obj2) !== 'undefined') {
                    $(obj2).addClass("codigo").text($(obj).val());
                    $("#tempId").remove();
                }
                
            }
            
        } catch (e) {
            // alert(e);
        }

    },
    // Remove a máscara CONTAINER
    unsetContainer: function (obj) {

        $(obj).unmask().unbind("keydown").unbind("keyup").unbind("keypress");

        return $(obj).removeClass("container");

    },
    // Seta a mascara CONTAINER
    setContainer: function (obj) {

        try {
            obj = this.unsetContainer(obj);
        } catch (e) {
            // alert(e);
        }

        try {
            
            // verifica se o objeto existe
            if($(obj).size() > 0) {
                
                // Verifica se o objeto é um campo input.
                // Caso não seja, cria um, atribui o valor
                // e depois retorna este valor ao objeto original
                if(! $(obj).is(":input")) {
                    var obj2 = $(obj);
                    var obj  = $('<input/>').attr({
                        type  : 'hidden',
                        id    : 'tempId',
                        name  : 'tempName',
                        value : $(obj2).text()
                    }).after($(obj2));
                }
                
                $(obj).mask("aaaa-999.999-9").addClass("container");
                
                // Retorna o valor para o objeto original e exclui o temporario
                if(typeof(obj2) !== 'undefined') {
                    $(obj2).addClass("container").text($(obj).val());
                    $("#tempId").remove();
                }
                
            }
            
        } catch (e) {
            // alert(e);
        }

    },
    // Remove a máscara CONTEINER
    unsetConteiner: function (obj) {

        $(obj).unmask().unbind("keydown").unbind("keyup").unbind("keypress");

        return $(obj).removeClass("conteiner");

    },
    // Seta a mascara CONTEINER
    setConteiner: function (obj) {

        try {
            obj = this.unsetConteiner(obj);
        } catch (e) {
            // alert(e);
        }

        try {
            
            // verifica se o objeto existe
            if($(obj).size() > 0) {
                
                // Verifica se o objeto é um campo input.
                // Caso não seja, cria um, atribui o valor
                // e depois retorna este valor ao objeto original
                if(! $(obj).is(":input")) {
                    var obj2 = $(obj);
                    var obj  = $('<input/>').attr({
                        type  : 'hidden',
                        id    : 'tempId',
                        name  : 'tempName',
                        value : $(obj2).text()
                    }).after($(obj2));
                }
                
                $(obj).mask("aaaa-999.999-9").addClass("conteiner");
                
                // Retorna o valor para o objeto original e exclui o temporario
                if(typeof(obj2) !== 'undefined') {
                    $(obj2).addClass("conteiner").text($(obj).val());
                    $("#tempId").remove();
                }
                
            }
            
        } catch (e) {
            // alert(e);
        }

    },
    // Remove a máscara CPF
    unsetCpf: function (obj) {

        $(obj).unmask().unbind("keydown").unbind("keyup").unbind("keypress");

        return $(obj).removeClass("cpf");

    },
    // Seta a mascara CPF
    setCpf: function (obj) {

        try {
            obj = this.unsetCpf(obj);
        } catch (e) {
            // alert(e);
        }

        try {
            
            // verifica se o objeto existe
            if($(obj).size() > 0) {
                
                // Verifica se o objeto é um campo input.
                // Caso não seja, cria um, atribui o valor
                // e depois retorna este valor ao objeto original
                if(! $(obj).is(":input")) {
                    var obj2 = $(obj);
                    var obj  = $('<input/>').attr({
                        type  : 'hidden',
                        id    : 'tempId',
                        name  : 'tempName',
                        value : $(obj2).text()
                    }).after($(obj2));
                }
                
                $(obj).mask("999.999.999-99").addClass("cpf");
                
                // Retorna o valor para o objeto original e exclui o temporario
                if(typeof(obj2) !== 'undefined') {
                    $(obj2).addClass("cpf").text($(obj).val());
                    $("#tempId").remove();
                }
                
            }
            
        } catch (e) {
            // alert(e);
        }

    },
    // Remove a máscara DI/BL
    unsetDibl: function (obj) {

        $(obj).unmask().unbind("keydown").unbind("keyup").unbind("keypress");

        return $(obj).removeClass("dibl");

    },
    // Seta a mascara DI/BL
    setDibl: function (obj) {

        try {
            obj = this.unsetDibl(obj);
        } catch (e) {
            // alert(e);
        }

        try {
            
            // verifica se o objeto existe
            if($(obj).size() > 0) {
                
                // Verifica se o objeto é um campo input.
                // Caso não seja, cria um, atribui o valor
                // e depois retorna este valor ao objeto original
                if(! $(obj).is(":input")) {
                    var obj2 = $(obj);
                    var obj  = $('<input/>').attr({
                        type  : 'hidden',
                        id    : 'tempId',
                        name  : 'tempName',
                        value : $(obj2).text()
                    }).after($(obj2));
                }
                
                $(obj).mask("99/9999999-9").addClass("dibl");
                
                // Retorna o valor para o objeto original e exclui o temporario
                if(typeof(obj2) !== 'undefined') {
                    $(obj2).addClass("dibl").text($(obj).val());
                    $("#tempId").remove();
                }
                
            }
            
        } catch (e) {
            // alert(e);
        }

    },
    // Remove a máscara FONE
    unsetFone: function (obj) {

        $(obj).unmask().unbind("keydown").unbind("keyup").unbind("keypress");

        return $(obj).removeClass("fone");

    },
    // Seta a mascara FONE
    setFone: function (obj) {

        try {
            obj = this.unsetFone(obj);
        } catch (e) {
            // alert(e);
        }

        try {
            
            // verifica se o objeto existe
            if($(obj).size() > 0) {
                
                // Verifica se o objeto é um campo input.
                // Caso não seja, cria um, atribui o valor
                // e depois retorna este valor ao objeto original
                if(! $(obj).is(":input")) {
                    var obj2 = $(obj);
                    var obj  = $('<input/>').attr({
                        type  : 'hidden',
                        id    : 'tempId',
                        name  : 'tempName',
                        value : $(obj2).text()
                    }).after($(obj2));
                }
                
                $(obj).mask("(99) 9999-9999").addClass("fone");
                
                // Retorna o valor para o objeto original e exclui o temporario
                if(typeof(obj2) !== 'undefined') {
                    $(obj2).addClass("fone").text($(obj).val());
                    $("#tempId").remove();
                }
                
            }
            
        } catch (e) {
            // alert(e);
        }

    },
    // Remove a máscara MES
    unsetMes: function (obj) {

        $(obj).unmask().unbind("keydown").unbind("keyup").unbind("keypress");

        return $(obj).removeClass("mes");

    },
    // Seta a mascara MES
    setMes: function (obj) {

        try {
            obj = this.unsetMes(obj);
        } catch (e) {
            // alert(e);
        }

        try {
            
            // verifica se o objeto existe
            if($(obj).size() > 0) {
                
                // Verifica se o objeto é um campo input.
                // Caso não seja, cria um, atribui o valor
                // e depois retorna este valor ao objeto original
                if(! $(obj).is(":input")) {
                    var obj2 = $(obj);
                    var obj  = $('<input/>').attr({
                        type  : 'hidden',
                        id    : 'tempId',
                        name  : 'tempName',
                        value : $(obj2).text()
                    }).after($(obj2));
                }
                
                $(obj).mask("99").addClass("mes");
                
                // Retorna o valor para o objeto original e exclui o temporario
                if(typeof(obj2) !== 'undefined') {
                    $(obj2).addClass("mes").text($(obj).val());
                    $("#tempId").remove();
                }
                
            }
            
        } catch (e) {
            // alert(e);
        }

    },
    // Remove a máscara NR_PROC
    unsetNrProc: function (obj) {

        $(obj).unmask().unbind("keydown").unbind("keyup").unbind("keypress");

        return $(obj).removeClass("nr_proc");

    },
    // Seta a mascara NR_PROC
    setNrProc: function (obj) {

        try {
            obj = this.unsetNrProc(obj);
        } catch (e) {
            // alert(e);
        }

        try {
            
            // verifica se o objeto existe
            if($(obj).size() > 0) {
                
                // Verifica se o objeto é um campo input.
                // Caso não seja, cria um, atribui o valor
                // e depois retorna este valor ao objeto original
                if(! $(obj).is(":input")) {
                    var obj2 = $(obj);
                    var obj  = $('<input/>').attr({
                        type  : 'hidden',
                        id    : 'tempId',
                        name  : 'tempName',
                        value : $(obj2).text()
                    }).after($(obj2));
                }
                
                $(obj).mask("99999-99 99/99-9").addClass("nr_proc");
                
                // Retorna o valor para o objeto original e exclui o temporario
                if(typeof(obj2) !== 'undefined') {
                    $(obj2).addClass("nr_proc").text($(obj).val());
                    $("#tempId").remove();
                }
                
            }
            
        } catch (e) {
            // alert(e);
        }

    },
    // Remove a máscara PDA
    unsetPDA: function (obj) {

        $(obj).unmask().unbind("keydown").unbind("keyup").unbind("keypress");

        return $(obj).removeClass("pda");

    },
    // Seta a mascara PDA
    setPDA: function (obj) {

        try {
            obj = this.unsetPDA(obj);
        } catch (e) {
            // alert(e);
        }

        try {
            
            // verifica se o objeto existe
            if($(obj).size() > 0) {
                
                // Verifica se o objeto é um campo input.
                // Caso não seja, cria um, atribui o valor
                // e depois retorna este valor ao objeto original
                if(! $(obj).is(":input")) {
                    var obj2 = $(obj);
                    var obj  = $('<input/>').attr({
                        type  : 'hidden',
                        id    : 'tempId',
                        name  : 'tempName',
                        value : $(obj2).text()
                    }).after($(obj2));
                }
                
                $(obj).mask("9999/9999").addClass("pda");
                
                // Retorna o valor para o objeto original e exclui o temporario
                if(typeof(obj2) !== 'undefined') {
                    $(obj2).addClass("pda").text($(obj).val());
                    $("#tempId").remove();
                }
                
            }
            
        } catch (e) {
            // alert(e);
        }

    },
    // Remove a máscara INTERVALOHORA
    unsetIntervaloHora: function (obj) {

        $(obj).unmask().unbind("keydown").unbind("keyup").unbind("keypress");

        return $(obj).removeClass("intervalohora");

    },
    // Seta a mascara INTERVALOHORA
    setIntervaloHora: function (obj) {

        try {
            obj = this.unsetIntervaloHora(obj);
        } catch (e) {
            // alert(e);
        }

        try {
            
            // verifica se o objeto existe
            if($(obj).size() > 0) {
                
                // Verifica se o objeto é um campo input.
                // Caso não seja, cria um, atribui o valor
                // e depois retorna este valor ao objeto original
                if(! $(obj).is(":input")) {
                    var obj2 = $(obj);
                    var obj  = $('<input/>').attr({
                        type  : 'hidden',
                        id    : 'tempId',
                        name  : 'tempName',
                        value : $(obj2).text()
                    }).after($(obj2));
                }
                
                $(obj).mask("99:99h. - 99:99h.").addClass("intervalohora");
                
                // Retorna o valor para o objeto original e exclui o temporario
                if(typeof(obj2) !== 'undefined') {
                    $(obj2).addClass("intervalohora").text($(obj).val());
                    $("#tempId").remove();
                }
                
            }
            
        } catch (e) {
            // alert(e);
        }

    },
    // Remove a máscara MESANO
    unsetMesAno: function (obj) {

        $(obj).unmask().unbind("keydown").unbind("keyup").unbind("keypress");

        return $(obj).removeClass("mesano");

    },
    // Seta a mascara MESANO
    setMesAno: function (obj) {

        try {
            obj = this.unsetMesAno(obj);
        } catch (e) {
            // alert(e);
        }

        try {
            
            // verifica se o objeto existe
            if($(obj).size() > 0) {
                
                // Verifica se o objeto é um campo input.
                // Caso não seja, cria um, atribui o valor
                // e depois retorna este valor ao objeto original
                if(! $(obj).is(":input")) {
                    var obj2 = $(obj);
                    var obj  = $('<input/>').attr({
                        type  : 'hidden',
                        id    : 'tempId',
                        name  : 'tempName',
                        value : $(obj2).text()
                    }).after($(obj2));
                }
                
                $(obj).mask("99/9999").addClass("mesano");
                
                // Retorna o valor para o objeto original e exclui o temporario
                if(typeof(obj2) !== 'undefined') {
                    $(obj2).addClass("mesano").text($(obj).val());
                    $("#tempId").remove();
                }
                
            }
            
        } catch (e) {
            // alert(e);
        }

    },
    // Remove a máscara STRU
    unsetStru: function (obj) {

        $(obj).unbind("keyup");

        return $(obj).removeClass("stru");

    },
    // Seta a mascara STRU
    setStru: function (obj) {

        try {
            obj = this.unsetStru(obj);
        } catch (e) {
            // alert(e);
        }
        
        try {
            
            // verifica se o objeto existe
            if($(obj).size() > 0) {
                
                // Verifica se o objeto é um campo input.
                // Caso não seja, cria um, atribui o valor
                // e depois retorna este valor ao objeto original
                if(! $(obj).is(":input")) {
                    var obj2 = $(obj);
                    var obj  = $('<input/>').attr({
                        type  : 'hidden',
                        id    : 'tempId',
                        name  : 'tempName',
                        value : $(obj2).text()
                    }).after($(obj2));
                }
                                
                $(obj).bind("keyup", function (event) {
                    var ew = event.which;
                    if (ew != 8 && ew != 13 && ew != 37 && ew != 38 &&
                            ew != 39 && ew != 40 && ew != 46 && ew != 144) {
                        $(this).val($(this).val().toUpperCase());
                    }
                }).addClass("stru");
                
                // Retorna o valor para o objeto original e exclui o temporario
                if(typeof(obj2) !== 'undefined') {
                    $(obj2).addClass("stru").text($(obj).val());
                    $("#tempId").remove();
                }
                
            }
        
        } catch(e) {
            // alert(e);
        }
        
    },
    // Remove a máscara HORAMINUTO
    unsetHoraMinuto: function (obj) {

        $(obj).unmask().unbind("keydown").unbind("keyup").unbind("keypress");

        return $(obj).removeClass("horaminuto");

    },
    // Seta a mascara HORAMINUTO
    setHoraMinuto: function (obj) {

        try {
            obj = this.unsetHoraMinuto(obj);
        } catch (e) {
            // alert(e);
        }

        try {
            
            // verifica se o objeto existe
            if($(obj).size() > 0) {
                
                // Verifica se o objeto é um campo input.
                // Caso não seja, cria um, atribui o valor
                // e depois retorna este valor ao objeto original
                if(! $(obj).is(":input")) {
                    var obj2 = $(obj);
                    var obj  = $('<input/>').attr({
                        type  : 'hidden',
                        id    : 'tempId',
                        name  : 'tempName',
                        value : $(obj2).text()
                    }).after($(obj2));
                }
                
                $(obj).mask("99:99").addClass("horaminuto");
                
                // Retorna o valor para o objeto original e exclui o temporario
                if(typeof(obj2) !== 'undefined') {
                    $(obj2).addClass("horaminuto").text($(obj).val());
                    $("#tempId").remove();
                }
                
            }
            
        } catch (e) {
            // alert(e);
        }

        // Verifica se o navegador é IE e nao insere o componente de controle
        if (!$.browser.msie) {
            $(obj).datetime({withDate: false, format: 'hh:ii'});
            $(obj).blur(function () {
                $("div.ui-datetime").css("display", "none");
            });
        }

    },
    // Remove a máscara QUANTIDADE
    unsetQtde: function (obj) {

        $(obj).unmask().unbind("keydown").unbind("keyup").unbind("keypress");

        return $(obj).removeClass("qtde");

    },
    // Seta a mascara QTDE
    setQtde: function (obj) {

        var precisao = 0;

        try {
            obj = this.unsetQtde(obj);
        } catch (e) {
            // alert(e);
        }
        
        try {
            
            // verifica se o objeto existe
            if ($(obj).size() > 0) {
                
                // Verifica se o objeto é um campo input.
                // Caso não seja, cria um, atribui o valor
                // e depois retorna este valor ao objeto original
                if(! $(obj).is(":input")) {
                    var obj2 = $(obj);
                    var obj  = $('<input/>').attr({
                        type  : 'hidden',
                        id    : 'tempId',
                        name  : 'tempName',
                        value : $(obj2).text()
                    }).after($(obj2));
                }
                
                // Corrige o valor retornado do banco ao qual
                // supreme os zeros a direita
                obj = this.corrigeDecimal(obj, precisao);

                var maxlength = $(obj).attr("maxlength");
                if (maxlength == -1) {
                    maxlength = 10;
                }
                
                var pf = $(obj).priceFormat({
                    limit: maxlength,
                    centsLimit: precisao,
                    prefix: '',
                    centsSeparator: '',
                    thousandsSeparator: '.',
                    fillZeroes: true
                });
                
                $(obj).addClass("qtde").val($(pf).val());
                
                // Retorna o valor para o objeto original e exclui o temporario
                if(typeof(obj2) !== 'undefined') {
                    $(obj2).addClass("qtde").text($(obj).val());
                    $("#tempId").remove();
                }

            }
            
            return $(obj);
        
        } catch(e) {
            // alert(e);
        }
        
    },
    // Remove a máscara Nota
    unsetNrNf: function (obj) {

        $(obj).unmask().unbind("keydown").unbind("keyup").unbind("keypress");

        return $(obj).removeClass("nr_nf");

    },
    // Seta a mascara QTDE
    setNrNf: function (obj) {

        var precisao = 0;

        try {
            obj = this.unsetQtde(obj);
        } catch (e) {
            // alert(e);
        }
        
        try {
            
            // verifica se o objeto existe
            if ($(obj).size() > 0) {

                // Verifica se o objeto é um campo input.
                // Caso não seja, cria um, atribui o valor
                // e depois retorna este valor ao objeto original
                if(! $(obj).is(":input")) {
                    var obj2 = $(obj);
                    var obj  = $('<input/>').attr({
                        type  : 'hidden',
                        id    : 'tempId',
                        name  : 'tempName',
                        value : $(obj2).text()
                    }).after($(obj2));
                }

                // Corrige o valor retornado do banco ao qual
                // supreme os zeros a direita
                obj = this.corrigeDecimal(obj, precisao);

                var maxlength = $(obj).attr("maxlength");
                if (maxlength == -1) {
                    maxlength = 10;
                }

                var pf = $(obj).priceFormat({
                    limit: maxlength,
                    centsLimit: precisao,
                    prefix: '',
                    centsSeparator: '',
                    thousandsSeparator: '',
                    fillZeroes: true
                });

                $(obj).addClass("nr_nf").val($(pf).val());

                // Retorna o valor para o objeto original e exclui o temporario
                if(typeof(obj2) !== 'undefined') {
                    $(obj2).addClass("nr_nf").text($(obj).val());
                    $("#tempId").remove();
                }

            }
            
            return $(obj);
            
        } catch(e) {
            // alert(e);
        }
        
 },
    // Remove a máscara MOEDA
    unsetMoeda: function (obj) {

        $(obj).unmask().unbind("keydown").unbind("keyup").unbind("keypress");

        return $(obj).removeClass("moeda");

    },
    // Seta a mascara MOEDA
    setMoeda: function (obj) {
        
        var precisao = 2;

        try {
            obj = this.unsetMoeda(obj);
        } catch (e) {
            // alert(e);
        }
        
        try {
            
            // verifica se o objeto existe
            if ($(obj).size() > 0) {

                // Verifica se o objeto é um campo input.
                // Caso não seja, cria um, atribui o valor
                // e depois retorna este valor ao objeto original
                if(! $(obj).is(":input")) {
                    var obj2 = $(obj);
                    var obj  = $('<input/>').attr({
                        type  : 'hidden',
                        id    : 'tempId',
                        name  : 'tempName',
                        value : $(obj2).text()
                    }).after($(obj2));
                }

                // Corrige o valor retornado do banco ao qual
                // supreme os zeros a direita
                obj = this.corrigeDecimal(obj, precisao);

                var maxlength = $(obj).attr("maxlength");
                if (maxlength == -1) {
                    maxlength = 10;
                }

                var pf = $(obj).priceFormat({
                    limit: maxlength,
                    centsLimit: precisao,
                    prefix: '',
                    centsSeparator: ',',
                    thousandsSeparator: '.',
                    fillZeroes: true
                });
                
                $(obj).addClass("moeda").val($(pf).val());
                
                // Retorna o valor para o objeto original e exclui o temporario
                if(typeof(obj2) !== 'undefined') {
                    return $(obj2).addClass("moeda").text($(pf).val());
                    $("#tempId").remove();
                }

            }

            return $(obj);

        } catch (e) {
            // alert(e);
        }
    },
    // Remove a máscara CALADO
    unsetCalado: function (obj) {

        $(obj).unmask().unbind("keydown").unbind("keyup").unbind("keypress");

        return $(obj).removeClass("calado");

    },
    // Seta a mascara CALADO
    setCalado: function (obj) {

        var precisao = 2;

        try {
            obj = this.unsetCalado(obj);
        } catch (e) {
            // alert(e);
        }
        
        try {
            
            // verifica se o objeto existe
            if ($(obj).size() > 0) {
                
                // Verifica se o objeto é um campo input.
                // Caso não seja, cria um, atribui o valor
                // e depois retorna este valor ao objeto original
                if(! $(obj).is(":input")) {
                    var obj2 = $(obj);
                    var obj  = $('<input/>').attr({
                        type  : 'hidden',
                        id    : 'tempId',
                        name  : 'tempName',
                        value : $(obj2).text()
                    }).after($(obj2));
                }
                
                // Corrige o valor retornado do banco ao qual
                // supreme os zeros a direita
                obj = this.corrigeDecimal(obj, precisao);

                var maxlength = $(obj).attr("maxlength");
                if (maxlength == -1) {
                    maxlength = 10;
                }

                var pf = $(obj).priceFormat({
                    limit: maxlength,
                    centsLimit: precisao,
                    prefix: '',
                    centsSeparator: ',',
                    thousandsSeparator: '.',
                    fillZeroes: true
                });
                
                $(obj).addClass("calado").val($(pf).val());
                
                // Retorna o valor para o objeto original e exclui o temporario
                if(typeof(obj2) !== 'undefined') {
                    return $(obj2).addClass("calado").text($(pf).val());
                    $("#tempId").remove();
                }

            }
            
            return $(obj);
        
        } catch (e) {
            // alert(e);
        }

   },
    // Remove a máscara PESO
    unsetPeso: function (obj) {

        $(obj).unmask().unbind("keydown").unbind("keyup").unbind("keypress");

        return $(obj).removeClass("peso");

    },
    // Seta a mascara PESO
    setPeso: function (obj) {

        var precisao = 3;

        try {
            obj = this.unsetPeso(obj);
        } catch (e) {
            // alert(e);
        }
        
        try {
            
            // verifica se o objeto existe
            if ($(obj).size() > 0) {
                
                // Verifica se o objeto é um campo input.
                // Caso não seja, cria um, atribui o valor
                // e depois retorna este valor ao objeto original
                if(! $(obj).is(":input")) {
                    var obj2 = $(obj);
                    var obj  = $('<input/>').attr({
                        type  : 'hidden',
                        id    : 'tempId',
                        name  : 'tempName',
                        value : $(obj2).text()
                    }).after($(obj2));
                }
                
                // Corrige o valor retornado do banco ao qual
                // supreme os zeros a direita
                obj = this.corrigeDecimal(obj, precisao);

                var maxlength = $(obj).attr("maxlength");
                if (maxlength == -1) {
                    maxlength = 10;
                }

                var pf = $(obj).priceFormat({
                    limit: maxlength,
                    centsLimit: precisao,
                    prefix: '',
                    centsSeparator: ',',
                    thousandsSeparator: '.',
                    fillZeroes: true
                });
                
                $(obj).addClass("peso").val($(pf).val());
                
                // Retorna o valor para o objeto original e exclui o temporario
                if(typeof(obj2) !== 'undefined') {
                    return $(obj2).addClass("peso").text($(pf).val());
                    $("#tempId").remove();
                }
                
            }
            
            return $(obj);

        } catch (e) {
            // alert(e);
        }

   },
    // Remove a máscara PESO-PORTO
    unsetPesoPorto: function (obj) {

        $(obj).unmask().unbind("keydown").unbind("keyup").unbind("keypress");

        return $(obj).removeClass("peso-porto");

    },
    // Seta a mascara PESO
    setPesoPorto: function (obj) {

        var precisao = 3;

        try {
            obj = this.unsetPeso(obj);
        } catch (e) {
            // alert(e);
        }
        
        try {
            
            // verifica se o objeto existe
            if ($(obj).size() > 0) {
                
                // Verifica se o objeto é um campo input.
                // Caso não seja, cria um, atribui o valor
                // e depois retorna este valor ao objeto original
                if(! $(obj).is(":input")) {
                    var obj2 = $(obj);
                    var obj  = $('<input/>').attr({
                        type  : 'hidden',
                        id    : 'tempId',
                        name  : 'tempName',
                        value : $(obj2).text()
                    }).after($(obj2));
                }
                
                var maxlength = $(obj).attr("maxlength");
                if (maxlength == -1) {
                    maxlength = 10;
                }

                var pf = $(obj).priceFormat({
                    limit: maxlength,
                    centsLimit: 0,
                    prefix: '',
                    centsSeparator: '',
                    thousandsSeparator: '.',
                    fillZeroes: true
                });
                
                $(obj).addClass("peso-porto").val($(pf).val());
                
                // Retorna o valor para o objeto original e exclui o temporario
                if(typeof(obj2) !== 'undefined') {
                    return $(obj2).addClass("peso-porto").text($(pf).val());
                    $("#tempId").remove();
                }

            }

            return $(obj);

        } catch (e) {
            // alert(e);
        }

    },
    // Remove a máscara PLACA
    unsetPlaca: function (obj) {

        $(obj).unmask().unbind("keydown").unbind("keyup").unbind("keypress");

        return $(obj).removeClass("placa");

    },
    // Seta mascara PLACA
    setPlaca: function (obj) {

        try {
            obj = this.unsetPlaca(obj);
        } catch (e) {
            // alert(e);
        }
        
        try {
            
            // verifica se o objeto existe
            if($(obj).size() > 0) {
                
                // Verifica se o objeto é um campo input.
                // Caso não seja, cria um, atribui o valor
                // e depois retorna este valor ao objeto original
                if(! $(obj).is(":input")) {
                    var obj2 = $(obj);
                    var obj  = $('<input/>').attr({
                        type  : 'hidden',
                        id    : 'tempId',
                        name  : 'tempName',
                        value : $(obj2).text()
                    }).after($(obj2));
                }
                
                $(obj).mask("aaa-9999").addClass("placa");
                
                // Retorna o valor para o objeto original e exclui o temporario
                if(typeof(obj2) !== 'undefined') {
                    $(obj2).addClass("placa").text($(obj).val());
                    $("#tempId").remove();
                }
                
            }
            
            return $(obj);
            
        } catch(e) {
            // alert(e);
        }
        
    },
    // Remove a máscara PLACA
    unsetPlacaEstrangeira: function (obj) {

        $(obj).unmask().unbind("keydown").unbind("keyup").unbind("keypress");

        return $(obj).removeClass("placa-estrangeira");

    },
    // Seta mascara PLACA
    setPlacaEstrangeira: function (obj) {

        try {
            obj = this.unsetPlaca(obj);
        } catch (e) {
            // alert(e);
        }
        
        try {
            
            // verifica se o objeto existe
            if ($(obj).size() > 0) {
                
                // Verifica se o objeto é um campo input.
                // Caso não seja, cria um, atribui o valor
                // e depois retorna este valor ao objeto original
                if(! $(obj).is(":input")) {
                    var obj2 = $(obj);
                    var obj  = $('<input/>').attr({
                        type  : 'hidden',
                        id    : 'tempId',
                        name  : 'tempName',
                        value : $(obj2).text()
                    }).after($(obj2));
                }
                
                // Verifica se foi digitado uma data válida
                $(obj).keyup(function (e) {
                    // Desconsidera Backspace e return
                    if (e.which != 8 && e.which != 0) {
                        Mascara.regexPlacaEstrangeira(this);
                    }

                });
                
                // Se tiver valor coloca a máscara
                if ($(obj).val() != "") {
                    Mascara.regexPlacaEstrangeira(obj);
                }
                
                $(obj).addClass("placa-estrangeira");
                
                // Retorna o valor para o objeto original e exclui o temporario
                if(typeof(obj2) !== 'undefined') {
                    $(obj2).addClass("placa-estrangeira").text($(obj).val());
                    $("#tempId").remove();
                }
                
            }

            return $(obj);

        } catch (e) {
            // alert(e);
        }
        
    },
    // Define a máscara por expressão regular
    regexPlacaEstrangeira: function (obj) {

        var regex = /(^[a-zA-Z]*$)|(^[a-zA-Z]*-$)|(^[a-zA-Z]*-[0-9]*$)/;
        var valor = $(obj).val();
        // Se não validar remove o último caracter inserido
        if (!regex.test(valor)) {
            var regex2 = /([a-zA-Z]+)([0-9]+)/;
            // Se não colocar o traço, insere automaticamente
            if (regex2.test(valor)) {
                result = regex2.exec(valor);
                valor = result[1] + '-' + result[2];
                $(obj).val(valor);

            } else {
                tamanho = valor.length;
                valor = valor.substr(0, (tamanho - 1));
                $(obj).val(valor);
            }
        }

    },
    
    // Remove a máscara HORA
    unsetHora: function (obj) {

        $(obj).unmask().unbind("keydown").unbind("keyup").unbind("keypress");

        return $(obj).removeClass("hora");

    },
    // Seta a mascara no campo com Hora
    setHora: function (obj) {

        try {
            obj = this.unsetHora(obj);
        } catch (e) {
            // alert(e);
        }

        try {
            
            // verifica se o objeto existe
            if ($(obj).size() > 0) {
                
                // Verifica se o objeto é um campo input.
                // Caso não seja, cria um, atribui o valor
                // e depois retorna este valor ao objeto original
                if(! $(obj).is(":input")) {
                    var obj2 = $(obj);
                    var obj  = $('<input/>').attr({
                        type  : 'hidden',
                        id    : 'tempId',
                        name  : 'tempName',
                        value : $(obj2).text()
                    }).after($(obj2));
                }
                
                var readonly = ($(obj).attr("readonly") == "readonly" || $(".hora").attr("readonly") == true) ? true : false;
                var disabled = ($(obj).attr("disabled") == "disabled" || $(".hora").attr("disabled") == true) ? true : false;

                if ((!readonly) && (!disabled)) {
                    date_obj = new Date();
                    var secs = date_obj.getSeconds();
                    $(obj).datetime({withDate: false, format: 'hh:ii' + ':' + secs});
                }
                
                $(obj).mask("99:99:99").addClass("hora");
                
                // Retorna o valor para o objeto original e exclui o temporario
                if(typeof(obj2) !== 'undefined') {
                    $(obj2).addClass("hora").text($(obj).val());
                    $("#tempId").remove();
                }

            }

            return $(obj);

        } catch (e) {
            // alert(e);
        }
        
    },
    // Remove a máscara DATA
    unsetData: function (obj) {

        $(obj).unmask().datepicker("destroy");

        return $(obj).removeClass("data");

    },
    // Seta a mascara no campo com DATA
    setData: function (obj) {

        try {
            obj = this.unsetData(obj);
        } catch (e) {
            // alert(e);
        }

        try {
            
            // verifica se o objeto existe
            if ($(obj).size() > 0) {
                
                // Verifica se o objeto é um campo input.
                // Caso não seja, cria um, atribui o valor
                // e depois retorna este valor ao objeto original
                if(! $(obj).is(":input")) {
                    var obj2 = $(obj);
                    var obj  = $('<input/>').attr({
                        type  : 'hidden',
                        id    : 'tempId',
                        name  : 'tempName',
                        value : $(obj2).text()
                    }).after($(obj2));
                }
                
                var readonly = ($(obj).attr("readonly") == "readonly" || $(obj).attr("readonly") == true) ? true : false;
                var disabled = ($(obj).attr("disabled") == "disabled" || $(obj).attr("disabled") == true) ? true : false;
                var hidden   =  $(obj).attr("type")     == "hidden" ? true : false;
                
                if ((!readonly) && (!disabled) && (!hidden)) {  

                    $(obj).datepicker({
                        beforeShow: function (input, inst) {
                            $(this).addClass("datepicker-open");
                        },
                        onClose: function (input, inst) {
                            $(this).removeClass("datepicker-open");
                            $(this).focus();
                        },
                        onSelect: function () {
                            $(this).focus();
                        },
                        regional: 'pt-BR',
                        showOn: 'button',
                        buttonImage: baseUrl + '/public/images/calendar.gif',
                        buttonImageOnly: true
                    });
                }

                // Seta a máscara
                $(obj).mask("99/99/9999");

                // Verifica se foi digitado uma data válida
                $(obj).blur(function () {
                    var regex = /^[0-9]{2,}\/[0-9]{2,}\/[0-9]{4,}$/;
                    if (regex.test($(this).val())) {
                        if (!Data.verificaData($(this).val())) {
                            Base.montaMensagemSistema(Array('Data inválida!'), 'Alerta', 4);
                            $(this).val("");
                        }
                    }
                });
                
                $(obj).addClass("data");
                
                // Retorna o valor para o objeto original e exclui o temporario
                if(typeof(obj2) !== 'undefined') {
                    $(obj2).addClass("data").text($(obj).val());
                    $(obj).datepicker("destroy");
                    $("#tempId").remove();
                }

            }

            return $(obj);

        } catch (e) {
            // alert(e);
        }
        
    },
    // Remove a máscara DATAHORA
    unsetDataHora: function (obj) {

        $(obj).datepicker("destroy");

        $(obj).unmask().unbind("keydown").unbind("keyup").unbind("keypress");

        return $(obj).removeClass("dthr").removeClass("datahora");

    },
    // Seta a mascara no campo com DATA e HORA
    setDataHora: function (obj) {

        try {
            var classe = "";
            if ($(obj).is("dthr")) {
                classe = "dthr";
            } else if ($(obj).is("datahora")) {
                classe = "datahora";
            }

            obj = this.unsetDataHora(obj);
        } catch (e) {
            // alert(e);
        }

        try {
            
            // verifica se o objeto existe
            if ($(obj).size() > 0) {
                
                // Verifica se o objeto é um campo input.
                // Caso não seja, cria um, atribui o valor
                // e depois retorna este valor ao objeto original
                if(! $(obj).is(":input")) {
                    var obj2 = $(obj);
                    var obj  = $('<input/>').attr({
                        type  : 'hidden',
                        id    : 'tempId',
                        name  : 'tempName',
                        value : $(obj2).text()
                    }).after($(obj2));
                }
                
                var readonly = ($(obj).attr("readonly") == "readonly" || $(obj).attr("readonly") == true) ? true : false;
                var disabled = ($(obj).attr("disabled") == "disabled" || $(obj).attr("disabled") == true) ? true : false;
                var hidden   =  $(obj).attr("type")     == "hidden" ? true : false;

                if ((!readonly) && (!disabled)) {

                    date_obj = new Date();
                    var hora = date_obj.getHours();
                    var mins = date_obj.getMinutes();
                    var secs = date_obj.getSeconds();
                    if (hora < 10) {
                        hora = "0" + hora;
                    }
                    if (mins < 10) {
                        mins = "0" + mins;
                    }
                    if (secs < 10) {
                        secs = "0" + secs;
                    }
                    var horaFull = hora + ':' + mins;
                    $(obj).datepicker({dateFormat: 'dd/mm/yy' + ' ' + horaFull,
                        showOn: 'button',
                        buttonImage: baseUrl + '/public/images/calendar.gif',
                        buttonImageOnly: true,
                        beforeShow: function (input, inst) {
                            $(this).addClass("datepicker-open");
                        },
                        onClose: function (input, inst) {
                            $(this).removeClass("datepicker-open");
                            $(this).focus();
                        },
                        onSelect: function () {
                            $(this).focus();
                        }
                    });
                }

                $(obj).mask("99/99/9999 99:99");

                // Verifica se foi digitado uma data válida
                $(obj).blur(function () {

                    var regex = /^[0-9]{2,}\/[0-9]{2,}\/[0-9]{4,} [0-9]{2,}:[0-9]{2,}$/;
                    if (regex.test($(this).val())) {
                        if (!Data.verificaDataHora($(this).val())) {
                            Base.montaMensagemSistema(Array('Data/Hora inválida!'), 'Alerta', 4);
                            $(this).val("");
                        }
                    }

                });
                
                $(obj).addClass(classe);
                
                // Retorna o valor para o objeto original e exclui o temporario
                if(typeof(obj2) !== 'undefined') {
                    $(obj2).addClass(classe).text($(obj).val());
                    $(obj).datepicker("destroy");
                    $("#tempId").remove();
                }
                
            }

            return $(obj);

        } catch (e) {
            // alert(e);
        }
        
    },
    // Remove a máscara Decimal
    unsetDecimal: function (obj) {

        $(obj).unmask().unbind("keydown").unbind("keyup").unbind("keypress");

        return $(obj).removeClass("decimal");

    },
    // Seta mascara Decimal
    setDecimal: function (obj, precisao) {

        try {
            obj = this.unsetDecimal(obj);
        } catch (e) {
            // alert(e);
        }

        try {
            
            if (precisao == undefined) {
                precisao = 2;
            }

            // verifica se o objeto existe
            if ($(obj).size() > 0) {
                
                // Verifica se o objeto é um campo input.
                // Caso não seja, cria um, atribui o valor
                // e depois retorna este valor ao objeto original
                if(! $(obj).is(":input")) {
                    var obj2 = $(obj);
                    var obj  = $('<input/>').attr({
                        type  : 'hidden',
                        id    : 'tempId',
                        name  : 'tempName',
                        value : $(obj2).text()
                    }).after($(obj2));
                }

                // Corrige o valor retornado do banco ao qual
                // supreme os zeros a direita
                obj = this.corrigeDecimal(obj, precisao);

                var maxlength = $(obj).attr("maxlength");
                if (maxlength == -1) {
                    maxlength = 10;
                }

                var pf = $(obj).priceFormat({
                    limit: maxlength,
                    centsLimit: precisao,
                    prefix: '',
                    centsSeparator: ',',
                    thousandsSeparator: '.',
                    fillZeroes: true
                });
                
                $(obj).addClass("decimal").val($(pf).val());
                
                // Retorna o valor para o objeto original e exclui o temporario
                if(typeof(obj2) !== 'undefined') {
                    $(obj2).addClass(classe).text($(obj).val());
                    $("#tempId").remove();
                }
                
            }
            
            return $(obj);

        } catch (e) {
            // alert(e);
        }

    },
    // Remove a máscara numero
    unsetNumero: function (obj) {
        $(obj).unbind("keypress");
        return $(obj).removeClass("numero");

    },
    // Seta a mascara para aceitar somente NUMEROS
    setNumero: function (obj, charcode) {

        try {
            obj = this.unsetNumero(obj);
        } catch (e) {
            // alert(e);
        }

        try {
            
            // verifica se o objeto existe
            if ($(obj).size() > 0) {
                
                // Verifica se o objeto é um campo input.
                // Caso não seja, cria um, atribui o valor
                // e depois retorna este valor ao objeto original
                if(! $(obj).is(":input")) {
                    var obj2 = $(obj);
                    var obj  = $('<input/>').attr({
                        type  : 'hidden',
                        id    : 'tempId',
                        name  : 'tempName',
                        value : $(obj2).text()
                    }).after($(obj2));
                }
                
                // Se não for passado o caracter especial seta zero
                if (charcode == undefined) {
                    charcode = -1;
                }

                // Validação de Somente Número    
                if ($(obj).size() > 0) {
                    $(obj).each(function () {
                        $(this).keypress(function (e) {
                            if ($(this).hasClass("numero")) {
                                if (e.which != charcode && e.which != 0 && e.which != 8 && e.which != 42 && (e.which < 48 || e.which > 57)) {
                                    return false;
                                }
                            }
                        });
                    });
                }

                $(obj).addClass("numero");
                
                // Retorna o valor para o objeto original e exclui o temporario
                if(typeof(obj2) !== 'undefined') {
                    $(obj2).addClass("numero").text($(obj).val());
                    $("#tempId").remove();
                }
                
            }
            
            return $(obj);
            
        } catch (e) {
            // alert(e);
        }

    },
    // Remove a máscara tonelada
    unsetTonelada: function (obj) {

        $(obj).unmaskMoney();

        return $(obj).removeClass("tonelada");

    },
    // Seta a mascara para aceitar somente NUMEROS e vírgula
    setTonelada: function (obj, precisao) {

        try {
            obj = this.unsetTonelada(obj);
        } catch (e) {
            // alert(e);
        }

        try {
            
            // verifica se o objeto existe
            if ($(obj).size() > 0) {
                
                // Verifica se o objeto é um campo input.
                // Caso não seja, cria um, atribui o valor
                // e depois retorna este valor ao objeto original
                if(! $(obj).is(":input")) {
                    var obj2 = $(obj);
                    var obj  = $('<input/>').attr({
                        type  : 'hidden',
                        id    : 'tempId',
                        name  : 'tempName',
                        value : $(obj2).text()
                    }).after($(obj2));
                }
                
                if (precisao == undefined) {
                    precisao = 3;
                }

                // seta mascara Decimal
                if ($(obj).size() > 0) {
                    $(obj).maskMoney({symbol: "", decimal: ",", thousands: ".", precision: precisao, defaultZero: false});
                }

                $(obj).addClass("tonelada");
                
                // Retorna o valor para o objeto original e exclui o temporario
                if(typeof(obj2) !== 'undefined') {
                    $(obj2).addClass("tonelada").text($(obj).val());
                    $("#tempId").remove();
                }
                
            }
            
            return $(obj);
            
        } catch (e) {
            // alert(e);
        }
        
    },
    // Remove a máscara letra
    unsetLetra: function (obj) {

        return $(obj).removeClass("letra");

    },
    // Seta a máscara para aceitar somente LETRAS
    setLetra: function (obj) {

        try {
            obj = this.unsetLetra(obj);
        } catch (e) {
            // alert(e);
        }

        try {
            
            // verifica se o objeto existe
            if ($(obj).size() > 0) {
                
                // Verifica se o objeto é um campo input.
                // Caso não seja, cria um, atribui o valor
                // e depois retorna este valor ao objeto original
                if(! $(obj).is(":input")) {
                    var obj2 = $(obj);
                    var obj  = $('<input/>').attr({
                        type  : 'hidden',
                        id    : 'tempId',
                        name  : 'tempName',
                        value : $(obj2).text()
                    }).after($(obj2));
                }
                
                // Validação de Somente Letras
                if ($(obj).size() > 0) {
                    $(obj).each(function () {
                        $(this).keypress(function (e) {
                            if ($(obj).hasClass("letra")) {
                                if (!((e.which > 64 && e.which < 91) || (e.which > 96 && e.which < 123) || e.which == 37 || e.which == 42 || e.which == 32 || e.which == 8 || e.which == 0 || (e.which > 224 && e.which < 251))) {
                                    return false;
                                }
                            }
                        });
                    });
                }

                $(obj).addClass("letra");
                
                // Retorna o valor para o objeto original e exclui o temporario
                if(typeof(obj2) !== 'undefined') {
                    $(obj2).addClass("letra").text($(obj).val());
                    $("#tempId").remove();
                }
            
            }
            
            return $(obj);
            
        } catch (e) {
            // alert(e);
        }
        
    },
    // Remove a máscara do campo datahoraminuto
    unsetDataHoraMinuto: function (obj) {

        // Remove a máscara
        $(obj).unmask().datepicker("destroy");

        return $(obj).removeClass("datahoraminuto");

},
    // Seta a data, hora e o minuto de um campo
    setDataHoraMinuto: function (obj, hora, mins) {

        try {
            obj = this.unsetDataHoraMinuto(obj);
        } catch (e) {
            // alert(e);
        }

        try {
            
            // verifica se o objeto existe
            if ($(obj).size() > 0) {
                
                if (obj == undefined) {
                    obj = $(".datahoraminuto");
                }
                
                // Verifica se o objeto é um campo input.
                // Caso não seja, cria um, atribui o valor
                // e depois retorna este valor ao objeto original
                if(! $(obj).is(":input")) {
                    var obj2 = $(obj);
                    var obj  = $('<input/>').attr({
                        type  : 'hidden',
                        id    : 'tempId',
                        name  : 'tempName',
                        value : $(obj2).text()
                    }).after($(obj2));
                }

                var readonly = ($(obj).attr("readonly") == "readonly" || $(obj).attr("readonly") == true) ? true : false;
                var disabled = ($(obj).attr("disabled") == "disabled" || $(obj).attr("disabled") == true) ? true : false;

                if ((!readonly) && (!disabled)) {
                    date_obj = new Date();
                    if (hora == undefined) {
                        hora = date_obj.getHours();
                        if (hora < 10) {
                            hora = "0" + hora;
                        }
                    }
                    if (mins == undefined) {
                        mins = date_obj.getMinutes();
                        if (mins < 10) {
                            mins = "0" + mins;
                        }
                    }

                    var horaFull = hora + ':' + mins;

                    $(obj).datepicker({
                        beforeShow: function (input, inst) {
                            $(this).addClass("datepicker-open");
                        },
                        onClose: function (input, inst) {
                            $(this).removeClass("datepicker-open");
                        },
                        onSelect: function () {
                            $(this).focus();
                        },
                        dateFormat: 'dd/mm/yy' + ' ' + horaFull,
                        showOn: 'button',
                        buttonImage: baseUrl + '/public/images/calendar.gif',
                        buttonImageOnly: true
                    });
                }

                $(obj).addClass("datahoraminuto").mask("99/99/9999 99:99");
                
                // Retorna o valor para o objeto original e exclui o temporario
                if(typeof(obj2) !== 'undefined') {
                    $(obj2).addClass("datahoraminuto").text($(obj).val());
                    $(obj).datepicker("destroy");
                    $("#tempId").remove();
                }

            }
            
        } catch (e) {
            // alert(e);
        }

    },
    // Remove a máscara PDA
    unsetDuv: function (obj) {

        $(obj).unmask().unbind("keydown").unbind("keyup").unbind("keypress");

        return $(obj).removeClass("duv");

    },
    // Seta a mascara PDA
    setDuv: function (obj) {

        try {
            obj = this.unsetDuv(obj);
        } catch (e) {
            // alert(e);
        }

        try {
            
            // verifica se o objeto existe
            if ($(obj).size() > 0) {
                
                // Verifica se o objeto é um campo input.
                // Caso não seja, cria um, atribui o valor
                // e depois retorna este valor ao objeto original
                if(! $(obj).is(":input")) {
                    var obj2 = $(obj);
                    var obj  = $('<input/>').attr({
                        type  : 'hidden',
                        id    : 'tempId',
                        name  : 'tempName',
                        value : $(obj2).text()
                    }).after($(obj2));
                }
                
                $(obj).mask("999999/9999").addClass("duv");
                
                // Retorna o valor para o objeto original e exclui o temporario
                if(typeof(obj2) !== 'undefined') {
                    $(obj2).addClass("duv").text($(obj).val());
                    $("#tempId").remove();
                }
                
            }
            
        } catch (e) {
            // alert(e);
        }

    },
    // Remove a máscara PDA
    unsetNrLote: function (obj) {

        $(obj).unmask().unbind("keydown").unbind("keyup").unbind("keypress");

        return $(obj).removeClass("nr_lote");

    },
    // Seta a mascara PDA
    setNrLote: function (obj) {

        try {
            obj = this.unsetNrLote(obj);
        } catch (e) {
            // alert(e);
        }

        try {
            
            // verifica se o objeto existe
            if ($(obj).size() > 0) {
                
                // Verifica se o objeto é um campo input.
                // Caso não seja, cria um, atribui o valor
                // e depois retorna este valor ao objeto original
                if(! $(obj).is(":input")) {
                    var obj2 = $(obj);
                    var obj  = $('<input/>').attr({
                        type  : 'hidden',
                        id    : 'tempId',
                        name  : 'tempName',
                        value : $(obj2).text()
                    }).after($(obj2));
                }
                
                $(obj).mask("99.999999-a").addClass("nr_lote");
                
                // Retorna o valor para o objeto original e exclui o temporario
                if(typeof(obj2) !== 'undefined') {
                    $(obj2).addClass("nr_lote").text($(obj).val());
                    $("#tempId").remove();
                }
                
            }
            
        } catch (e) {
            // alert(e);
        }

    },
    // Remove a máscara PDA
    unsetNrRe: function (obj) {

        $(obj).unmask().unbind("keydown").unbind("keyup").unbind("keypress");

        return $(obj).removeClass("nr_re");

    },
    // Seta a mascara PDA
    setNrRe: function (obj) {

        try {
            obj = this.unsetNrRe(obj);
        } catch (e) {
            // alert(e);
        }

        try {
            
            // verifica se o objeto existe
            if ($(obj).size() > 0) {
                
                // Verifica se o objeto é um campo input.
                // Caso não seja, cria um, atribui o valor
                // e depois retorna este valor ao objeto original
                if(! $(obj).is(":input")) {
                    var obj2 = $(obj);
                    var obj  = $('<input/>').attr({
                        type  : 'hidden',
                        id    : 'tempId',
                        name  : 'tempName',
                        value : $(obj2).text()
                    }).after($(obj2));
                }
                
                $(obj).mask("99/999999-99").addClass("nr_re");
                
                // Retorna o valor para o objeto original e exclui o temporario
                if(typeof(obj2) !== 'undefined') {
                    $(obj2).addClass("nr_re").text($(obj).val());
                    $("#tempId").remove();
                }
                
            }
            
        } catch (e) {
            // alert(e);
    }

    },
    // Corrige os valores decimais que retornam do banco
    // incluindo os zeros a direita que faltam nas casas decimais
    corrigeDecimal: function (obj, precisao) {

        var valor = $(obj).val();
        var sv = "";
        var i = 0;
        var inteiro = "";
        var decimal = "";
        var tamDec = 0;

        // Seta uma precisão padrão
        if (precisao == undefined) {
            precisao = 2;
        }

        try {

            sv = valor.split(",");
            inteiro = sv[0];
            decimal = sv[1];

            // Se não retornar casa decimal
            // inclui todos os zeros referentes 
            // ao tamanho da precisão passada
            if (decimal == undefined) {
                throw "Sem decimal";
            }

            tamDec = decimal.length;
            for (i = tamDec; i < precisao; i++) {
                decimal = decimal + "0";
            }

            valor = inteiro + "," + decimal;

        } catch (e) {

            valor = valor + ",";

            for (i = 0; i < precisao; i++) {
                valor = valor + "0";
            }
        }

        // Seta o valor e retorna o objeto
        return $(obj).val(valor);

    },
    // Remove a pontuação (vírgula e ponto) de um campo numérico
    removePontuacaoCampo: function (obj) {

        var valor = null;

        // Seta o plugin datepicker    
        if ($(obj).size() > 0) {
            valor = $(obj).val().replace(/[ \.\,]/g, "");
        }

        return valor;

    }

};