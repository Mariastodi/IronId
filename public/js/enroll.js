const modelUrl = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model';

let accessToken = sessionStorage.getItem('ironid_token') || '';
let cameraTimer;
let captureGeneration = 0;
let currentMember = null;
let currentDescriptor = null;
let modelsLoaded = false;

function showMessage(elementId, text, type) {
  const element = document.getElementById(elementId);
  element.textContent = text;
  element.className = 'message ' + type;
  element.style.display = 'block';
}

function hideMessage(elementId) {
  document.getElementById(elementId).style.display = 'none';
}

function setStep(step) {
  captureGeneration++; clearTimeout(cameraTimer); currentDescriptor = null;
  document.getElementById('video').srcObject?.getTracks().forEach(track => track.stop());
  document.getElementById('saveButton').disabled = true;
  document.getElementById('step1').style.display = step === 1 ? 'block' : 'none';
  document.getElementById('step2').style.display = step === 2 ? 'block' : 'none';
  document.getElementById('step3').style.display = step === 3 ? 'block' : 'none';

  document.getElementById('dot1').classList.toggle('done', step >= 1);
  document.getElementById('dot2').classList.toggle('done', step >= 2);
  document.getElementById('dot3').classList.toggle('done', step >= 3);
}

function goToStep1() { setStep(1); }
function goToStep2() {
  accessToken = document.getElementById('tokenInput').value.trim();

  if (!accessToken) {
    showMessage('step1Message', 'Informe um token válido.', 'error');
    return;
  }

  hideMessage('step1Message');
  setStep(2);
}

async function findMember() {
  const matricula = document.getElementById('matriculaInput').value.trim();

  if (!matricula) {
    showMessage('step2Message', 'Digite a matrícula do aluno.', 'error');
    return;
  }

  try {
    const response = await fetch('/api/members?matricula=' + encodeURIComponent(matricula), {
      headers: {
        'Accept': 'application/json',
        'Authorization': 'Bearer ' + accessToken,
      },
    });

    if (!response.ok) {
      showMessage('step2Message', 'Não foi possível buscar o aluno. Verifique o token.', 'error');
      return;
    }

    const payload = await response.json();
    const member = payload.data[0];

    if (!member) {
      showMessage('step2Message', 'Nenhum aluno encontrado com essa matrícula.', 'error');
      return;
    }

    currentMember = member;
    document.getElementById('memberName').textContent = member.name;
    document.getElementById('memberMatricula').textContent = 'Matrícula ' + member.matricula;
    hideMessage('step2Message');
    setStep(3);
    startEnrollmentCamera().catch(() => showMessage('step3Message', 'Não foi possível abrir a câmera. Confira a permissão e sua conexão.', 'error'));
  } catch (error) {
    showMessage('step2Message', 'Erro de conexão com a API.', 'error');
  }
}

async function startEnrollmentCamera() {
  if (!modelsLoaded) {
    await faceapi.nets.tinyFaceDetector.loadFromUri(modelUrl);
    await faceapi.nets.faceLandmark68Net.loadFromUri(modelUrl);
    await faceapi.nets.faceRecognitionNet.loadFromUri(modelUrl);
    modelsLoaded = true;
  }

  const video = document.getElementById('video');
  const stream = await navigator.mediaDevices.getUserMedia({ video: { width: 480, height: 360 } });
  video.srcObject = stream;

  captureLoop(video, captureGeneration);
}

async function captureLoop(video, generation) {
  if (generation !== captureGeneration) return;
  const detection = await faceapi
    .detectAllFaces(video, new faceapi.TinyFaceDetectorOptions())
    .withFaceLandmarks()
    .withFaceDescriptor();

  const hint = document.getElementById('captureHint');
  const saveButton = document.getElementById('saveButton');

  if (generation !== captureGeneration) return;
  if (detection.length === 1) {
    currentDescriptor = detection[0].descriptor;
    hint.textContent = 'Rosto detectado — pode salvar';
    saveButton.disabled = false;
  } else {
    currentDescriptor = null;
    hint.textContent = 'Procurando rosto...';
    saveButton.disabled = true;
  }

  cameraTimer = setTimeout(() => captureLoop(video, generation).catch(() => { currentDescriptor = null; saveButton.disabled = true; }), 600);
}

async function saveFace() {
  if (!currentDescriptor || !currentMember) return;

  try {
    const response = await fetch('/api/members/' + currentMember.id + '/face', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': 'Bearer ' + accessToken,
      },
      body: JSON.stringify({ descriptor: Array.from(currentDescriptor) }),
    });

    if (response.ok) {
      showMessage('step3Message', 'Rosto cadastrado com sucesso!', 'success');
      return;
    }

    const payload = await response.json();
    const message = payload.errors ? Object.values(payload.errors)[0][0] : 'Não foi possível salvar o rosto.';
    showMessage('step3Message', message, 'error');
  } catch (error) {
    showMessage('step3Message', 'Erro de conexão com a API.', 'error');
  }
}
if (accessToken) {
  document.getElementById('tokenInput').value = accessToken;
  goToStep2();
  document.getElementById('matriculaInput').value = new URLSearchParams(location.search).get('matricula') || '';
}
window.addEventListener('pagehide', () => {captureGeneration++;clearTimeout(cameraTimer);document.getElementById('video').srcObject?.getTracks().forEach(track=>track.stop());});
