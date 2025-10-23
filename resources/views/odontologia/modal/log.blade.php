<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

{{-- Modal --}}
<div class="modal fade" id="auditModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          Auditoria ID: <span id="audit-pid" class="text-muted"></span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <div id="audit-loading" class="text-center py-4 d-none">Carregando…</div>
        <div id="audit-empty" class="text-muted d-none">Sem registros.</div>
        <div id="audit-list"></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>

@once
  @push('scripts')
    <script>
    // Abre modal e busca os logs ao clicar no botão .btn-log (delegado)
    document.addEventListener('click', async (e) => {
      const btn = e.target.closest('.btn-log');
      if (!btn) return;
      const url = btn.dataset.url;
      const pid = btn.dataset.pacienteId;

      const modalEl = document.getElementById('auditModal');
      const bsModal = new bootstrap.Modal(modalEl);
      const loading = modalEl.querySelector('#audit-loading');
      const list = modalEl.querySelector('#audit-list');
      const empty = modalEl.querySelector('#audit-empty');
      modalEl.querySelector('#audit-pid').textContent = pid;

      list.innerHTML = '';
      empty.classList.add('d-none');
      loading.classList.remove('d-none');
      bsModal.show();

      try {
        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const rows = await res.json();

        loading.classList.add('d-none');
        if (!rows || rows.length === 0) { empty.classList.remove('d-none'); return; }

        list.innerHTML = rows.map(r => renderAuditItem(r)).join('');
      } catch (err) {
        loading.classList.add('d-none');
        list.innerHTML = `<div class="alert alert-danger">Erro ao carregar histórico: ${err.message}</div>`;
      }
    });

    function renderAuditItem(r) {
      const when = new Date(r.created_at).toLocaleString('pt-BR');
      const user = r.AUDITABLE_ID || '—';
      const oldVals = safeParse(r.OLD_VALUES);
      const newVals = safeParse(r.NEW_VALUES);
      
      const changes = buildChangesTable(oldVals, newVals);

      return `
        <div class="card mb-3">
          <div class="card-header d-flex justify-content-between align-items-center">
            <div>
              <span class="badge bg-${user} text-uppercase">${r.EVENT}</span>
              <small class="text-muted ms-2">${when}</small>
            </div>
            <!--<div><small class="text-muted">ID: ${escapeHtml(user)}</small></div>-->
          </div>
          <div class="card-body">
            ${changes}
          </div>
        </div>`;
    }

    function buildChangesTable(oldVals, newVals) {
      const keys = new Set([
        ...(newVals ? Object.keys(newVals) : []),
        ...(oldVals ? Object.keys(oldVals) : []),
      ]);
      if (keys.size === 0) return '<div class="text-muted">Sem detalhes.</div>';

      let rows = '';
      keys.forEach(k => {
        const ov = oldVals ? oldVals[k] : undefined;
        const nv = newVals ? newVals[k] : undefined;
        if (ov === nv && ov !== undefined) return; // pula iguais
        rows += `
          <tr>
            <td class="fw-semibold">${escapeHtml(k)}</td>
            <td>${renderValue(ov)}</td>
            <td>${renderValue(nv)}</td>
          </tr>`;
      });

      return `
        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead><tr><th>Campo</th><th>Antes</th><th>Depois</th></tr></thead>
            <tbody>${rows || '<tr><td colspan="3" class="text-muted">Sem alterações relevantes.</td></tr>'}</tbody>
          </table>
        </div>`;
    }

    function safeParse(v){ if(!v) return null; try{ return typeof v==='string'? JSON.parse(v): v; }catch{ return null; } }
    function renderValue(v){
      if (v === null || v === undefined || v === '') return '<span class="text-muted"><em>vazio</em></span>';
      if (typeof v === 'object') return `<pre class="mb-0 small">${escapeHtml(JSON.stringify(v, null, 2))}</pre>`;
      return escapeHtml(String(v));
    }
    function escapeHtml(s){ return String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }
    </script>
  @endpush
@endonce
@stack('scripts')