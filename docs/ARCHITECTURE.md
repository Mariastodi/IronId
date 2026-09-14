# Arquitetura do IronID

Aplicação educacional monolítica em Laravel com API REST e interface Blade/JavaScript. A divisão por responsabilidade usa Form Requests para validação, Controllers para HTTP, Resources para representação, Services para regras e Eloquent para persistência.

## Fluxo facial

Câmera → face-api.js no navegador → vetor de 128 componentes → POST /api/kiosk/recognize → FaceRecognitionService → CheckInService → resposta com nome, matrícula e plano.

A comparação euclidiana é linear no número de alunos cadastrados. O limiar 0,5 precisa de avaliação em dados representativos; o valor de confidence é apenas uma transformação da distância, não uma probabilidade calibrada. Não há prova de vida. O projeto precisa de supervisão humana e não deve acionar uma catraca em produção sem validação adicional.

## Consistência

CheckInService compartilha a regra de plano válido entre check-in facial e manual. Uma transação bloqueia a linha do aluno nos bancos que suportam row locking e reutiliza check-ins dos últimos 60 segundos. SQLite serializa escritas e é adequado à demonstração local; testes de concorrência em SQL Server ainda são necessários.

## Autenticação

Sanctum autentica a API. As rotas de operação exigem admin ou attendant. Login e reconhecimento têm limitação de requisições. O token da interface fica em sessionStorage da aba e não é incluído no HTML público. Em produção, preferir sessão com cookie HttpOnly e proteção CSRF, HTTPS e política de conteúdo.

## Limitações explícitas

Descritores estão no banco em JSON e não têm criptografia de campo. Não foram implementados ERP real, SQL Server Jobs operacionais, prova de vida, auditoria completa de alterações nem sincronização offline. Uma implantação real exige tratar consentimento, retenção, exclusão, segurança e avaliação de falsos positivos. Dados do seeder são fictícios.
