// frontend/js/hours.js
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('formHoras');
  const list = document.getElementById('listaHoras');
  if (!form) return;

  const getUserId = () => {
    const user = JSON.parse(localStorage.getItem('fans_user_data') || '{}');
    return user?.id_Residente ?? user?.id;
  };

  async function loadHours() {
    const userId = getUserId();
    if (!userId) return; // Podés mostrar mensaje si querés
    const res = await fetch(`${API_BASE_URL}?action=list_hours&id_residente=${userId}`);
    const data = await res.json().catch(() => ({}));
    list.innerHTML = (data.hours || []).map(h => `
      <div class="card">
        <div><strong>${h.Fecha}</strong> – ${h.Cantidad} hs</div>
        <div class="muted">${h.Descripcion || ''}</div>
      </div>
    `).join('') || '<p class="muted">Sin horas registradas.</p>';
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const userId = getUserId();
    if (!userId) return showNotification('Sesión inválida. Volvé a iniciar sesión.', 'error');

    const fecha = document.getElementById('horaFecha')?.value;
    const cantidad = parseFloat(document.getElementById('horaCantidad')?.value);
    const descripcion = document.getElementById('horaDesc')?.value || '';
    if (!fecha || isNaN(cantidad)) return showNotification('Completá fecha y cantidad', 'error');

    const res = await fetch(`${API_BASE_URL}?action=add_hour`, {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({ id_residente: userId, fecha, cantidad, descripcion })
    });
    const data = await res.json().catch(() => ({}));

    if (res.ok) {
      showNotification(data.message || 'Horas registradas', 'success');
      form.reset();
      loadHours();
    } else {
      showNotification(data.message || 'No se pudo registrar', 'error');
    }
  });

  loadHours();
});
