## Projeto MVC Starter

Projeto MVC simples em PHP puro, pensado para estudo e evolução gradual.

### Caracteristicas

- Sem Composer.
- Sem framework.
- Autoload manual com `spl_autoload_register`.
- Estrutura MVC separada em `controllers`, `models`, `views` e `core`.
- Conexao com banco usando `PDO`.

### Observacao

Esse projeto esta intencionalmente bem puro. Ele nao usa `composer.json`, nao possui pasta `vendor/` e depende apenas de recursos nativos do PHP. Isso deixa a base leve e facil de entender, mas em projetos maiores o uso de Composer passa a ser recomendavel para autoload, bibliotecas externas, dotenv, testes e melhor organizacao.
