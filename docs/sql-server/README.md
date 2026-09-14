# Laboratório SQL Server

`lab.sql` é um exercício independente do banco da aplicação: contém modelagem relacional, índice, View de presença diária, Function de status, Stored Procedure transacional, Trigger que trata múltiplas linhas e Role com permissões mínimas. Não foi executado nesta máquina, que usa SQLite para Laravel.

Execute em um banco vazio de estudo no SQL Server, usando ferramenta que interprete GO (SSMS ou sqlcmd). O script cria objetos e não é idempotente; o smoke test final usa rollback apenas nos dados de exemplo.

Para estudar Jobs, configure em uma instância com SQL Server Agent disponível uma tarefa que consulte a View e registre métricas em tabela própria, com conta restrita e alerta de falha. Não há Job instalado nem integração ERP real neste projeto.

Próximas validações: executar o script, testar lote de renovações para verificar a Trigger, analisar plano de execução com volume representativo e testar dois check-ins concorrentes. Somente depois descrever esses itens como validados no portfólio.
