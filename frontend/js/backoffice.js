// backoffice.js
// frontend/js/backoffice.js
const u = JSON.parse(localStorage.getItem('fans_user_data') || 'null');
if (!u || u.role !== 'admin') window.location.href = 'login.html';

document.addEventListener('DOMContentLoaded', () => {
  const user = JSON.parse(localStorage.getItem('fans_user_data') || 'null');
  if (!user || user.rol !== 'admin') {
    window.location.href = 'login.html';
    return;
  }
  // ... tu código actual del backoffice ...
});

document.addEventListener('DOMContentLoaded', () => {
  const usersList =
    document.getElementById('pendingUsersList') ||
    document.getElementById('pendingUserList') ||
    document.getElementById('usuariosPendientes');

  const usersLoading =
    document.getElementById('usersLoading') ||
    document.getElementById('noPendingUsersMessage');

  const receiptsList = document.getElementById('receiptsList');
  const estadoFiltro = document.getElementById('estadoFiltro');
  const residenteFiltro = document.getElementById('residenteFiltro');
  const aplicarFiltros = document.getElementById('aplicarFiltros');

  // Derivar base para archivos subidos (uploads/...)
  const FRONT_BASE = (typeof API_BASE_URL === 'string')
    ? API_BASE_URL.replace('/api/api.php', '')
    : '';

  if (typeof window.showNotification !== 'function') {
    window.showNotification = (m, t) => alert(`${t ? `[${t}] ` : ''}${m}`);
  }

  const parseJSON = async (res) => {
    const text = await res.text();
    try { return [res.ok, JSON.parse(text)]; }
    catch { return [res.ok, { message: text }]; }
  };

  // ===== Usuarios pendientes =====
  async function loadPendingUsers() {
    if (!usersList) return;
    usersLoading && (usersLoading.style.display = 'block');
    usersList.innerHTML = '';

    try {
      const [ok, data] = await fetch(`${API_BASE_URL}?action=pending_users`).then(parseJSON);
      usersLoading && (usersLoading.style.display = 'none');

      if (!ok) throw new Error(data.message || 'Error al obtener usuarios pendientes');
      const arr = data.users || [];
      if (arr.length === 0) {
        usersList.innerHTML = '<p class="muted">No hay usuarios pendientes.</p>';
        return;
      }
      usersList.innerHTML = arr.map(u => `
        <li class="user-item">
          <div class="user-details">
            <span class="user-name">${u.Nombre} ${u.Apellido}</span>
            <span class="user-email">${u.Correo || ''}</span>
            <span class="user-date">${u.fecha_registro || u.Fecha_Ingreso || ''}</span>
          </div>
          <div class="actions">
            <button class="btn-approve btn-approve-user" data-id="${u.id_Residente}">Aprobar</button>
          </div>
        </li>
      `).join('');
    } catch (e) {
      console.error('[pending_users]', e);
      usersLoading && (usersLoading.style.display = 'none');
      usersList.innerHTML = '<p class="muted">Error al cargar usuarios.</p>';
      showNotification(`Error al obtener usuarios pendientes: ${e.message}`, 'error');
    }
  }

  usersList?.addEventListener('click', async (e) => {
    const el = e.target.closest('.btn-approve-user');
    if (!el) return;
    const id = el.getAttribute('data-id');
    if (!id) return;
    if (!confirm(`¿Aprobar usuario #${id}?`)) return;

    try {
      const [ok, data] = await fetch(`${API_BASE_URL}?action=approve_user&id=${id}`, { method: 'PUT' }).then(parseJSON);
      if (!ok) throw new Error(data.message || 'No se pudo aprobar');
      showNotification('Usuario aprobado.', 'success');
      loadPendingUsers();
    } catch (e) {
      console.error('[approve_user]', e);
      showNotification(e.message || 'Error de red', 'error');
    }
  });

  // ===== Comprobantes (filtros + aprobar) =====
  async function loadReceipts() {
    if (!receiptsList) return;

    const params = new URLSearchParams({ action: 'list_receipts' });
    const estado = estadoFiltro?.value || 'Todos';
    const idRes = (residenteFiltro?.value || '').trim();
    if (estado !== 'Todos') params.set('estado', estado);
    if (idRes) params.set('id_residente', idRes);

    try {
      const [ok, data] = await fetch(`${API_BASE_URL}?${params.toString()}`).then(parseJSON);
      if (!ok) throw new Error(data.message || 'Error al obtener comprobantes');

      const arr = data.receipts || [];
      if (arr.length === 0) {
        receiptsList.innerHTML = '<p class="muted">Sin resultados.</p>';
        return;
      }

      receiptsList.innerHTML = arr.map(r => {
        const fileUrl = (r.Archivo || '').startsWith('http')
          ? r.Archivo
          : `${FRONT_BASE}/${r.Archivo}`.replace(/\/+/g,'/');

        const isPend = (r.Estado || '').toLowerCase() === 'pendiente';
        return `
          <li class="user-item">
            <div class="user-details">
              <span class="user-name">#${r.id_Comprobante} – ${r.Tipo} – ${r.Fecha}</span>
              <span class="user-email">Monto: ${r.Monto ?? '-'}</span>
              <span class="user-date">Residente: ${r.id_Residente} · Estado: <strong>${r.Estado}</strong></span>
            </div>
            <div class="actions">
              <a href="${fileUrl}" target="_blank" class="btn-secondary">Ver</a>
              ${isPend ? `<button class="btn-approve btn-approve-receipt" data-id="${r.id_Comprobante}">Aprobar</button>` : ''}
            </div>
          </li>
        `;
      }).join('');
    } catch (e) {
      console.error('[list_receipts]', e);
      receiptsList.innerHTML = '<p class="muted">Error al cargar comprobantes.</p>';
      showNotification(`Error al obtener comprobantes: ${e.message}`, 'error');
    }
  }

  receiptsList?.addEventListener('click', async (e) => {
    const el = e.target.closest('.btn-approve-receipt');
    if (!el) return;
    const id = el.getAttribute('data-id');
    if (!id) return;
    if (!confirm(`¿Aprobar comprobante #${id}?`)) return;

    try {
      const [ok, data] = await fetch(`${API_BASE_URL}?action=approve_receipt&id=${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({})
      }).then(parseJSON);

      if (!ok) throw new Error(data.message || 'No se pudo aprobar el comprobante');
      showNotification('Comprobante aprobado.', 'success');
      loadReceipts();
    } catch (e) {
      console.error('[approve_receipt]', e);
      showNotification(e.message || 'Error de red', 'error');
    }
  });

  // Eventos de filtros
  aplicarFiltros?.addEventListener('click', loadReceipts);
  estadoFiltro?.addEventListener('change', loadReceipts);
  residenteFiltro?.addEventListener('keydown', (e) => { if (e.key === 'Enter') loadReceipts(); });

  // Carga inicial
  loadPendingUsers();
  loadReceipts();
});
//limite de horas s emanalas por personas