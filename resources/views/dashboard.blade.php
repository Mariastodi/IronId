<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>IronID Gestão de acesso</title>
<link rel="stylesheet" href="/css/app.css">
<script src="/js/dashboard.js" defer>
</script>
</head>
<body>
<div class="shell">
<aside class="sidebar">
<a class="logo" href="/">iron<b>id.</b>
</a>
<div>
<div class="eyebrow">Workspace / Academia</div>
<nav class="nav" style="margin-top:20px">
<a class="selected" href="/">Visão geral</a>
<a href="/kiosk">◎ &nbsp; Check-in facial</a>
<a href="/enroll">⊕ &nbsp; Cadastro facial</a>
<a href="/api-reference.html">↗ &nbsp; Documentação API</a>
</nav>
</div>

</aside>
<main class="main">
<header class="topbar">
<span>Operação <span class="muted"> / Visão geral</span>
</span>
<div class="toolbar">
<span id="operator" class="muted">Área da recepção</span>
<button class="secondary" id="logout" hidden>Sair</button>
</div>
</header>
<section id="login" class="login">
<div class="eyebrow muted">Bem-vinda ao IronID</div>
<h1 style="margin-top:12px">Tudo pronto para começar.</h1>
<p class="muted">Entre para gerenciar alunos e acompanhar os acessos da academia.</p>
<form id="login-form">
<label for="email">E-mail</label>
<input id="email" type="email" autocomplete="username" required placeholder="seu@email.com">
<label for="password">Senha</label>
<input id="password" type="password" autocomplete="current-password" required>
<button>Entrar no workspace →</button>
</form>
</section>
<div id="notice" role="status" class="message" hidden>
</div>
<section id="workspace" hidden>
<div class="heading">
<div>
<div class="eyebrow muted">Gestão inteligente de acesso</div>
<h1 style="margin-top:8px">Sua academia, em movimento.</h1>
<p class="muted">Pessoas, planos e presença em um só lugar.</p>
</div>
<button id="new-member">+ Novo aluno</button>
</div>
<div class="hero">
<div>
<div class="eyebrow">Menos espera. Mais treino.</div>
<h2>O próximo check-in começa com um olhar.</h2>
<p class="muted">Identifique o aluno e confira a situação do plano na recepção.</p>
</div>
<a class="button" href="/kiosk">Abrir totem facial ↗</a>
</div>
<div class="metrics">
<article class="metric">
<span class="muted">Alunos cadastrados</span>
<strong id="total">—</strong>
<small class="muted">Base de alunos</small>
</article>
<article class="metric">
<span class="muted">Check-ins hoje</span>
<strong id="today">—</strong>
<small class="muted">Faciais e manuais</small>
</article>
<article class="metric">
<span class="muted">Reconhecimento facial</span>
<strong id="enrolled">—</strong>
<small class="muted">Rostos cadastrados na base</small>
</article>
</div>
<section class="panel">
<div class="panel-head">
<div>
<h2>Alunos</h2>
<p class="muted">Acompanhe planos e facilite cada chegada.</p>
</div>
<input id="search" aria-label="Buscar aluno pelo nome" placeholder="Buscar por nome…" type="search">
</div>
<div class="table-wrap">
<table>
<thead>
<tr>
<th>Aluno / Matrícula</th>
<th>Plano</th>
<th>Situação</th>
<th>Biometria</th>
<th>Ações</th>
</tr>
</thead>
<tbody id="members">
</tbody>
</table>
</div>
<div class="pagination">
<span id="page-label" class="muted">
</span>
<div class="toolbar">
<button class="secondary" id="previous">←</button>
<button class="secondary" id="next">→</button>
</div>
</div>
</section>
<p class="footnote">Reconhecimento assistido pela recepção. O cadastro facial deve ser feito com autorização do aluno.</p>
</section>
</main>
</div>
<dialog id="member-dialog">
<h2>Cadastrar aluno</h2>
<p class="muted">A matrícula e a vigência são geradas automaticamente.</p>
<form id="member-form">
<div class="form-grid">
<div>
<label for="name">Nome completo</label>
<input name="name" id="name" required maxlength="255">
</div>
<div>
<label for="cpf">CPF</label>
<input name="cpf" id="cpf" required maxlength="14">
</div>
<div>
<label for="member-email">E-mail (opcional)</label>
<input name="email" id="member-email" type="email">
</div>
<div>
<label for="plan">Plano</label>
<select name="plan_id" id="plan" required>
</select>
</div>
</div>
<div id="form-error" role="alert">
</div>
<div class="toolbar">
<button>Salvar aluno</button>
<button type="button" class="secondary" id="cancel">Cancelar</button>
</div>
</form>
</dialog>
</body>
</html>
