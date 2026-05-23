## app de fluxo de compra

## Logins e permissions

sistema de login, só pode ver as paginas se tiver logado

ja esta conectado ao banco de dados criar tabela para os logis

id, login, nome, password

esse login terá permissões como

isAdmin admin do sistema (vai poder acessar tudo e cadastrar outros usuarios)
SOLICITANTE_APPROVAL
ADMIN_APPROVAL
FINANCIAL_APPROVAL
PURCHASING_APPROVAL


## pagina de cadastro de item

- itemName
- description
- price
- categeory

## o usuario logado vai poder selecionar itens (previamente cadastrados) e isso dispara um fluxo de solicitação

## os itens passarão por etapas de aprovação, é necessario que cada gestor aprove ou reprove, se um reprovar já não é possivel proseguir é necessário ambos ou sei lá quantos gestores tiver aprovar o items para a proxima etapa

## Ordem Etapa Status técnico do fluxo

1 Solicitação Aberta OPEN
2 Aprovação Administrativa ADMIN_APPROVAL
3 Aprovação Financeira FINANCIAL_APPROVAL
4 Compra em Andamento PURCHASING
5 Finalizado COMPLETED
X Reprovado REJECTED

```js
fluxo: Compra padrão exemplo

Etapa 1: Aprovação Administrativa
Aprovadores: João, Maria
Regra: todos_aprovam

Etapa 2: Aprovação Financeira
Aprovadores: Financeiro
Regra: um_aprova ou todos_aprovam

Etapa 3: Compra
Responsável: Comprador
Ação: anexar comprovante

Etapa 4: Finalizado

```
