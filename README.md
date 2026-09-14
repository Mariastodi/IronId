# IronID - Gestão de alunos e check-in facial

Projeto de estudo de Maria Beatriz em PHP e Laravel. API REST autenticada, painel responsivo, cadastro facial e totem com nome, matrícula e situação do plano. Desenvolvido para demonstrar modelagem de domínio, integração entre front-end e API e regras de negócio testáveis.

Repositório: https://github.com/Mariastodi/IronId

## Executar localmente

Requisitos: PHP 8.3+, Composer e SQLite. As dependências exatas estão no composer.lock.

```bash
git clone https://github.com/Mariastodi/IronId.git
cd IronId
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Não execute o seeder sobre dados reais. Na instalação existente, preserve o .env e o banco.

Acesse http://127.0.0.1:8000 e entre com `admin@ironid.com` / `password` (somente demonstração local). A recepção usa `recepcao@ironid.com` / `password`. Não publique essas credenciais.

## Telas

- `/`: gestão, login, busca paginada, cadastro, renovação e check-in manual.
- `/enroll`: cadastro do rosto para matrícula existente.
- `/kiosk`: câmera e retorno com nome do aluno reconhecido.
- `/api-reference.html`: documentação e exemplos da API.

Faça login pela gestão e navegue na mesma aba. O token fica no sessionStorage; não é mais necessário configurar KIOSK_API_TOKEN no .env. Modelos faciais são carregados de CDN; a câmera exige localhost ou HTTPS e permissão do usuário.

## Regras implementadas

- Cadastro gera matrícula aleatória única e vigência baseada na duração do plano.
- Status: ativo, vencendo em até cinco dias, vencido ou inativo.
- Plano vencido/inativo não registra check-in.
- Leituras do mesmo aluno em até 60 segundos reutilizam o registro anterior.
- Cadastro facial verifica duplicidade; reconhecimento aceita um rosto por captura.
- API protege operações por Sanctum e papéis admin/attendant.
- Tokens não são embutidos no HTML público; descritores não aparecem nos Resources.

## Exemplo de API no zsh

```bash
curl -X POST 'http://127.0.0.1:8000/api/login' \
  -H 'Content-Type: application/json' \
  -H 'Accept: application/json' \
  -d '{"email":"admin@ironid.com","password":"password"}'

curl 'http://127.0.0.1:8000/api/members?per_page=1' \
  -H "Authorization: Bearer $TOKEN" \
  -H 'Accept: application/json'
```

O erro `zsh: no matches found` é evitado colocando a URL com `?` entre aspas. Não copie os colchetes e parênteses de links Markdown para o terminal. Se aparecer `bquote>`, use Ctrl+C para cancelar a crase aberta.

## Qualidade e arquitetura

```bash
php artisan test
vendor/bin/pint --test app routes tests
```

Controllers coordenam HTTP, Form Requests validam entradas, Services concentram regras, Resources definem o JSON e Models representam persistência. Veja [decisões e limites](docs/ARCHITECTURE.md).

Inclui [laboratório SQL Server](docs/sql-server/README.md) separado com View, Procedure, Function, Trigger e permissões. A aplicação foi validada em SQLite; o laboratório ainda precisa ser executado em SQL Server. Não há integração ERP real.

## Reconhecimento e limites

face-api.js extrai um vetor de 128 números no navegador. O backend busca a menor distância euclidiana entre descritores de alunos ativos. O nome vem do cadastro associado ao vetor. O sistema não identifica desconhecidos pela internet.

Este é um protótipo educacional assistido pela recepção: não há prova de vida, garantia contra fotografia, criptografia de campo ou validação de acurácia em população real. Não utilizar como controle físico autônomo. O banco local contém dados de demonstração; cadastre rostos apenas com autorização. Não versione .env, banco, tokens ou biometria.

