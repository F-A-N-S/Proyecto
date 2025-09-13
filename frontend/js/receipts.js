document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('formComprobante');
  const list = document.getElementById('listaComprobantes');
  if (!form) return;

  const getUserId = () => {
    const user = JSON.parse(localStorage.getItem('fans_user_data') || '{}');
    return user?.id_Residente ?? user?.id;
  };

  async function loadReceipts() {
    const userId = getUserId();
    if (!userId) return;
    const res = await fetch(`${API_BASE_URL}?action=list_receipts&id_residente=${userId}`);
    const data = await res.json().catch(() => ({}));
    list.innerHTML = (data.receipts || []).map(c => `
      <div class="card">
        <div><strong>${c.Tipo}</strong> – ${c.Fecha}${c.Monto ? ` – $${c.Monto}` : ''}</div>
        <div class="muted">Estado: ${c.Estado}</div>
        ${c.Archivo ? `<div><a href="${c.Archivo}" target="_blank" rel="noopener">Ver archivo</a></div>` : ''}
      </div>
    `).join('') || '<p class="muted">Sin comprobantes.</p>';
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const userId = getUserId();
    if (!userId) return showNotification('Sesión inválida. Volvé a iniciar sesión.', 'error');

    const tipo  = document.getElementById('compTipo')?.value;
    const fecha = document.getElementById('compFecha')?.value;
    const monto = document.getElementById('compMonto')?.value;
    const file  = document.getElementById('compArchivo')?.files?.[0];
    if (!tipo || !fecha || !file) return showNotification('Completá tipo, fecha y archivo', 'error');

    const fd = new FormData();
    fd.append('id_residente', userId);
    fd.append('tipo', tipo);
    fd.append('fecha', fecha);
    if (monto) fd.append('monto', monto);
    fd.append('archivo', file);

    const res = await fetch(`${API_BASE_URL}?action=upload_receipt`, { method:'POST', body: fd });
    const data = await res.json().catch(() => ({}));

    if (res.ok) {
      showNotification(data.message || 'Comprobante subido', 'success');
      form.reset();
      loadReceipts();
    } else {
      showNotification(data.message || 'No se pudo subir', 'error');
    }
  });

  loadReceipts();
});
