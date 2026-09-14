<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>IronID Check-in</title>
<link rel="stylesheet" href="/css/kiosk.css">
</head>
<body>

<div class="brand">
<a href="/" style="color:inherit;text-decoration:none;font-size:13px;margin-right:16px">← Gestão</a>
  <span class="brand-mark">IRONID</span>
  <span class="brand-sub">check-in facial</span>
</div>
<div class="clock" id="clock">
</div>

<div class="stage">
  <div class="ring scanning" id="ring">
    <div class="camera-frame">
      <video id="video" autoplay muted playsinline>
</video>
      <div class="scan-line" id="scanLine">
</div>
    </div>
  </div>
</div>

<div class="status" role="status" aria-live="polite">
  <div class="status-label scanning" id="statusLabel">POSICIONE O ROSTO</div>
  <div class="status-hint" id="statusHint">Olhe para a câmera para fazer o check-in</div>
</div>

<div class="card" id="card">
  <div class="avatar" id="avatar">
</div>
  <div class="card-body">
    <div class="card-name" id="cardName">
</div>
    <div class="card-matricula" id="cardMatricula">
</div>
    <div class="card-plan-row">
      <span class="pill" id="cardStatus">
</span>
      <span class="plan-name" id="cardPlan">
</span>
    </div>
  </div>
</div>

<div class="footer-hint">Sem reconhecer? Procure a recepção para check-in manual.</div>

<script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.js">
</script>
<script src="/js/kiosk.js">
</script>
</body>
</html>
