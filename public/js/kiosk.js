const kioskToken = sessionStorage.getItem('ironid_token');
const modelUrl = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model';

const video = document.getElementById('video');
const ring = document.getElementById('ring');
const statusLabel = document.getElementById('statusLabel');
const statusHint = document.getElementById('statusHint');
const card = document.getElementById('card');
const cardName = document.getElementById('cardName');
const cardMatricula = document.getElementById('cardMatricula');
const cardStatus = document.getElementById('cardStatus');
const cardPlan = document.getElementById('cardPlan');
const avatar = document.getElementById('avatar');

const statusLabels = {
  active: 'PLANO EM DIA',
  expiring_soon: 'PLANO VENCENDO',
  expired: 'PLANO VENCIDO',
  inactive: 'PLANO INATIVO',
};

let busy = false;
let resultTimer = null;

function setRingState(state) {
  ring.className = 'ring ' + state;
  statusLabel.className = 'status-label ' + state;
}

function setIdle() {
  setRingState('scanning');
  statusLabel.textContent = 'POSICIONE O ROSTO';
  statusHint.textContent = 'Olhe para a câmera para fazer o check-in';
  card.classList.remove('visible');
  busy = false;
}

function showRecognized(payload) {
  const member = payload.member;
  setRingState('success');
  statusLabel.textContent = 'Olá, ' + member.name.split(' ')[0] + '!';
  statusHint.textContent = 'Check-in registrado com sucesso';

  cardName.textContent = member.name;
  cardMatricula.textContent = 'Matrícula ' + member.matricula;
  cardPlan.textContent = member.plan ? member.plan.name : 'Sem plano';
  cardStatus.textContent = statusLabels[member.membership_status] || member.membership_status;
  cardStatus.className = 'pill ' + member.membership_status;
  avatar.style.backgroundImage = member.avatar_url ? `url(${member.avatar_url})` : 'none';

  card.classList.add('visible');

  clearTimeout(resultTimer);
  resultTimer = setTimeout(setIdle, 5000);
}

function showNotRecognized() {
  setRingState('failed');
  statusLabel.textContent = 'NÃO RECONHECIDO';
  statusHint.textContent = 'Tente novamente ou procure a recepção';

  clearTimeout(resultTimer);
  resultTimer = setTimeout(setIdle, 2500);
}

async function recognize(descriptor) {
  busy = true;

  try {
    const response = await fetch('/api/kiosk/recognize', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': 'Bearer ' + kioskToken,
      },
      body: JSON.stringify({ descriptor: Array.from(descriptor) }),
    });

    if (response.status === 200) {
      const payload = await response.json();
      showRecognized(payload.data);
      return;
    }

    if (response.status === 401 || response.status === 403) {
      setRingState('failed'); statusLabel.textContent = 'SESSÃO ENCERRADA';
      statusHint.textContent = 'Volte à gestão e entre novamente.';
      return;
    }
    if (response.status === 422 || response.status === 429) {
      const error = await response.json();
      setRingState('failed'); statusLabel.textContent = 'ATENDIMENTO NECESSÁRIO';
      statusHint.textContent = error.errors ? Object.values(error.errors).flat().join(' ') : 'Aguarde um momento antes de tentar novamente.';
      resultTimer = setTimeout(setIdle, 5000); return;
    }
    showNotRecognized();
  } catch (error) {
    showNotRecognized();
  }
}

async function startCamera() {
  const stream = await navigator.mediaDevices.getUserMedia({ video: { width: 480, height: 480 } });
  video.srcObject = stream;
}

async function detectLoop() {
  if (!busy) {
    const detection = await faceapi
      .detectAllFaces(video, new faceapi.TinyFaceDetectorOptions())
      .withFaceLandmarks()
      .withFaceDescriptor();

    if (detection.length === 1) {
      recognize(detection[0].descriptor);
    }
  }

  setTimeout(detectLoop, 1200);
}

function tickClock() {
  const now = new Date();
  document.getElementById('clock').textContent = now.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
}

async function bootstrap() {
  if (!kioskToken) { setRingState('failed'); statusLabel.textContent = 'ENTRE NA GESTÃO'; statusHint.textContent = 'Autentique-se na página inicial para iniciar o totem.'; return; }
  statusHint.textContent = 'Preparando os modelos e a câmera…';
  await faceapi.nets.tinyFaceDetector.loadFromUri(modelUrl);
  await faceapi.nets.faceLandmark68Net.loadFromUri(modelUrl);
  await faceapi.nets.faceRecognitionNet.loadFromUri(modelUrl);

  await startCamera();

  tickClock();
  setInterval(tickClock, 1000 * 30);

  detectLoop();
}

bootstrap().catch(error => { setRingState('failed'); statusLabel.textContent = 'CÂMERA INDISPONÍVEL'; statusHint.textContent = 'Verifique a permissão da câmera e a conexão. Recarregue para tentar novamente.'; });
window.addEventListener('pagehide', () => video.srcObject?.getTracks().forEach(track => track.stop()));
