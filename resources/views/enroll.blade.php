<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cadastro facial</title>
<link rel="stylesheet" href="/css/enroll.css">
</head>
<body>

<div class="panel">
  <div class="brand">
<a href="/" style="color:inherit;text-decoration:none;font-size:13px;margin-right:16px">← Gestão</a>
    <span class="brand-mark">IRONID</span>
    <span class="brand-sub">cadastro facial</span>
  </div>

  <div class="steps">
    <div class="step-dot done" id="dot1">
</div>
    <div class="step-dot" id="dot2">
</div>
    <div class="step-dot" id="dot3">
</div>
  </div>

  <div class="card" id="step1">
    <p class="step-title">1. Autenticação</p>
    <p class="step-desc">Informe o token de acesso da recepção</p>
    <label for="tokenInput">Token de acesso</label>
    <input type="password" id="tokenInput" placeholder="Cole o token gerado no login">
    <button onclick="goToStep2()">Continuar</button>
    <div class="message" id="step1Message" style="display:none">
</div>
  </div>

  <div class="card" id="step2" style="display:none">
    <p class="step-title">2. Localizar aluno</p>
    <p class="step-desc">Digite a matrícula do aluno já cadastrado</p>
    <label for="matriculaInput">Matrícula</label>
    <input type="text" id="matriculaInput" placeholder="Ex: ID-26-00042">
    <button onclick="findMember()">Buscar aluno</button>
    <button class="secondary" onclick="goToStep1()">Voltar</button>
    <div class="message" id="step2Message" style="display:none">
</div>
  </div>

  <div class="card" id="step3" style="display:none">
    <p class="step-title">3. Capturar rosto</p>
    <p class="step-desc">Peça para o aluno olhar de frente para a câmera</p>

    <div class="member-preview">
      <div>
        <div class="name" id="memberName">
</div>
        <div class="matricula" id="memberMatricula">
</div>
      </div>
    </div>

    <div class="camera-box">
      <video id="video" autoplay muted playsinline>
</video>
      <div class="capture-hint" id="captureHint">Procurando rosto...</div>
    </div>

    <button id="saveButton" disabled onclick="saveFace()">Salvar rosto</button>
    <button class="secondary" onclick="goToStep2()">Voltar</button>
    <div class="message" id="step3Message" style="display:none">
</div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.js">
</script>
<script src="/js/enroll.js">
</script>
</body>
</html>
