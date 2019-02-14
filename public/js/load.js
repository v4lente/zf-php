// Carrega o documento e todas as funções de inicialização das páginas internas
$(document).ready(function() {

	try {

        // Remove elementos JQuery do html, para evitar multiplicações.
        $("div.tooltip").remove();
        $("div.moxie-shim, div.moxie-shim-html5").remove();

        // Corrige a posição do botão do upload
        $('#tabs a').click(function(){
            $("div.moxie-shim, div.moxie-shim-html5").css("top", "0");
            $("div.moxie-shim, div.moxie-shim-html5").css("left", "0");
            $("div.moxie-shim, div.moxie-shim-html5").css("height", "0");
            $("div.moxie-shim, div.moxie-shim-html5").css("width", "0");                
        });

        // Verifica se existe o arquivo de controle dos formulários
		if (typeof Form == "object") {
			Form.init();
		}
		
		// Verifica se existe o arquivo com os controles das tabelas
		if (typeof Tabela == "object") {
			Tabela.init();
		}
		
		// Verifica se existe o arquivo com as ações de botões da toolbar/palheta
		if (typeof Eventos == "object") {
			Eventos.init();
		}
		
		// Verifica se existe o arquivo com as validações
		if (typeof Data == "object") {
			Data.init();
		}
		
		// Verifica se existe o arquivo com as validações
		if (typeof Valida == "object") {
			Valida.init();
		}
		
		// Verifica se existe o arquivo com os cálculos
		if (typeof Calcula == "object") {
			Calcula.init();
		}
		
		// Verifica se existe o arquivo com as mascaras
		if (typeof Mascara == "object") {
			Mascara.init();
		}
		
		// Verifica se existe o arquivo com os controles das abas
		if (typeof Abas == "object") {
			Abas.init();
		}
		
		// Verifica se existe o arquivo com de controle das sessões
		if (typeof Sessao == "object") {
			Sessao.init();
		}
		
        // Correção para os campos select multiplos que utilizam o plugin
        // para controle em tela
        $("select").removeAttr("multiple");
        
	} catch (e) {
		// Mostra o erro
		alert("Base.js - Erro: " + e);
	}
});