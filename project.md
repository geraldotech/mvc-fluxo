## app de fluxo de compra

## Logins e permissions

sistema de login, só pode ver as paginas se tiver logado


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


## pagina solicitação de compras

- titulo da solicitação
- selecionar vários itens numa lista (previamente cadastrados) 
- no clique abre o fluxo seguindo

e isso dispara um fluxo de solicitação onde os itens passarão por etapas de aprovação, é necessario que cada gestor aprove ou reprove, se um reprovar já não é possivel proseguir é necessário ambos ou sei lá quantos gestores tiver aprovar o items para a proxima etapa


## Ordem Etapa do fluxo + papel

1. Usuário cria solicitação -> papel de Solicitante
2. Sistema gera itens pendentes
3. Gestores aprovam/reprovam item por item
4. Quando todos aprovam, a etapa segue para a próxima APENAS com os itens aprovados sempre permitaindo os que tem o approvel da etapa aprovar
5. Financeiro aprova/reprova os itens aprovados
7. Solicitação finaliza 


| Ordem | Etapa                                             | papel        |
| ----- | ------------------------                          | ------------------    |
| 1     | Solicitação Aberta                                | SOLICITANTE_APPROVAL  |
| 2     | Aprovação Administrativa ambos devem aprovar                         | ADMIN_APPROVAL        |
| 3     | Aprovação Financeira  ja pode aprovar ou reprovar                            | FINANCIAL_APPROVAL    |
| 4     | Compra em Andamento (pode anexar compronante)     | PURCHASING            |
| 5     | Finalizado quando a etapa anterior for finalizada | COMPLETED              |


# status das aprovações
| X     | Reprovado                | REJECTED           |
| X     | Aprovado                | Approved           |


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
