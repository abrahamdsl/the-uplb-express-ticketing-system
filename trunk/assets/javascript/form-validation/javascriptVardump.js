http://rubsphp.blogspot.com/2011/03/vardump-para-javascript.html
/**
 * Versao JavaScript da funcao var_dump do PHP
 * @param mixed ... Qualquer valor
 * @return string Informacoes do valor
 */
function var_dump(/* ... */) {
    
    /**
     * Recursao do metodo var_dump
     * @param midex item Qualquer valor
     * @param int nivel Nivel de indentacao
     * @return string Informacoes do valor
     */
    this.var_dump_rec = function(item, nivel) {
        if (var_dump.max_iteracoes > 0 && var_dump.max_iteracoes < nivel) {
            return this.indentar(nivel) + "*MAX_ITERACOES(" + var_dump.max_iteracoes+ ")*\n";
        }
        if (item === null) {
            return this.indentar(nivel) + "NULL\n";
        } else if (item === undefined) {
            return this.indentar(nivel) + "undefined\n";
        }

        var str = '';
        var tipo = typeof(item);
        switch (tipo) {
        case 'object':
            var classe = this.get_classe(item);
            switch (classe) {
            case 'Array':
                str += this.indentar(nivel) + "Array(" + item.length + ") {\n";
                for (var i in item) {
                    str += this.indentar(nivel + 1) + "[" + i + "] =>\n";
                    str += this.var_dump_rec(item[i], nivel + 1);
                }
                str += this.indentar(nivel) + "}\n";
                break;

            case 'Number':
            case 'Boolean':
                str += this.indentar(nivel) + classe + "(" + item.toString() + ")\n";
                break;

            case 'String':
                str += this.indentar(nivel) + classe + "(" + item.toString().length + ") \"" + item.toString() + "\"\n";
                break;
            
            default:
                str += this.indentar(nivel) + "object(" + classe + ") {\n";
                var exibiu = false;
                for (var i in item) {
                    exibiu = true;
                    str += this.indentar(nivel + 1) + "[" + i + "] =>\n";
                    try {
                        str += this.var_dump_rec(item[i], nivel + 1);
                    } catch (e) {
                        str += this.indentar(nivel + 1) + "(Erro: " + e.message + ")\n";
                    }
                }
                if (!exibiu) {
                    str += this.indentar(nivel + 1) + "JSON(" + JSON.stringify(item) + ")\n";
                }
                str += this.indentar(nivel) + "}\n";
                break;
            }
            break;
        case 'number':
            str += this.indentar(nivel) + "number(" + item.toString() + ")\n";
            break;
        case 'string':
            str += this.indentar(nivel) + "string(" + item.length + ") \"" + item + "\"\n";
            break;
        case 'boolean':
            str += this.indentar(nivel) + "boolean(" + (item ? "true" : "false") + ")\n";
            break;
        case 'function':
            str += this.indentar(nivel) + "function {\n";
            str += this.indentar(nivel + 1) + "[code] =>\n";
            str += this.var_dump_rec(item.toString(), nivel + 1);
            str += this.indentar(nivel + 1) + "[prototype] =>\n";
            str += this.indentar(nivel + 1) + "object(prototype) {\n";
            for (var i in item.prototype) {
                str += this.indentar(nivel + 2) + "[" + i + "] =>\n";
                str += this.var_dump_rec(item.prototype[i], nivel + 2);
            }
            str += this.indentar(nivel + 1) + "}\n";

            str += this.indentar(nivel) + "}\n";
            break;
        default:
            str += this.indentar(nivel) + tipo + "(" + item + ")\n";
            break;
        }
        return str;
    };

    /**
     * Devolve o nome da classe de um objeto
     * @param Object obj Objeto a ser verificado
     * @return string Nome da classe
     */
    this.get_classe = function(obj) {
        if (obj.constructor) {
            return obj.constructor.toString().replace(/^.*function\s+([^\s]*|[^\(]*)\([^\x00]+$/, "$1");
        }
        return "Object";
    };

    /**
     * Retorna espacos para indentacao
     * @param int nivel Nivel de indentacao
     * @return string Espacos de indentacao
     */
    this.indentar = function(nivel) {
        var str = '';
        while (nivel > 0) {
            str += '  ';
            nivel--;
        }
        return str;
    };

    var str = "";
    var argv = var_dump.arguments;
    var argc = argv.length;
    for (var i = 0; i < argc; i++) {
        str += this.var_dump_rec(argv[i], 0);
    }
    return str;
}
var_dump.prototype.max_iteracoes = 0;