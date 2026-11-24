import { api, $, $$, esc, toast, firstError, confirmDlg, applyStatus, refreshStatus } from './common.js';
import { createPreview } from './preview.js';

// Grup folder unggahan yang diizinkan backend (config invitation.uploads.groups)
const GROUP = { cover: 'cover', couple: 'couple', couple_intro: 'couple', akad: 'events', resepsi: 'events', gallery: 'gallery', love_story: 'story', story_intro: 'story', gift: 'gift', dress_code: 'dress' };
const clone = (o) => JSON.parse(JSON.stringify(o));
const getPath = (o, path) => path.reduce((a, k) => (a == null ? undefined : a[k]), o);

export function initEditor() {
    const root = $('#editor');
    const boot = JSON.parse($('#ed-boot').textContent);
    const cfg = root.dataset;
    let data = clone(boot.data);
    let saved = JSON.stringify(data);
    let version = +cfg.version;
    const defaults = boot.default;
    const hasVisual = cfg.section !== 'music';

    const urlOf = (ref) => {
        if (!ref || !ref.path) return null;
        return ref.source === 'upload' ? cfg.baseUpload + ref.path : `${cfg.baseDefault}/${ref.path}`;
    };

    // ---------------- dirty / preview ----------------
    const dirtyPill = $('#dirty-pill'); const btnSave = $('#btn-save'); const btnCancel = $('#btn-cancel');
    const isDirty = () => JSON.stringify(data) !== saved;
    const syncDirty = () => {
        const d = isDirty(); window.__edDirty = d;
        dirtyPill.dataset.state = d ? 'dirty' : 'clean'; dirtyPill.textContent = d ? 'Ada perubahan belum disimpan' : 'Tersimpan sebagai draft';
        btnSave.disabled = !d; btnCancel.disabled = !d;
    };
    window.addEventListener('beforeunload', (e) => { if (isDirty()) { e.preventDefault(); e.returnValue = ''; } });

    let preview = null; let pTimer = null; let pSeq = 0;
    if (hasVisual) preview = createPreview({ view: $('#prev-view'), stage: $('#prev-stage'), frame: $('#prev-frame'), onState: () => { $('#prev-state').textContent = ''; } });
    else { $('.ed-prev').hidden = true; $('#prev-none').hidden = false; $('.ed-grid').style.gridTemplateColumns = '1fr'; $('#btn-full').hidden = true; }

    const renderPreview = async () => {
        if (!hasVisual) return;
        const seq = ++pSeq; $('#prev-state').textContent = 'Memperbarui preview…';
        const r = await api(cfg.urlRender, { method: 'POST', body: { section: cfg.section, data } });
        if (seq !== pSeq) return;
        if (r.ok) preview.show(r.data.html); else $('#prev-state').textContent = 'Preview gagal dimuat';
    };
    const changed = () => { syncDirty(); clearTimeout(pTimer); pTimer = setTimeout(renderPreview, 220); };

    // ---------------- form builder ----------------
    const formEl = $('#ed-form');
    const pathStr = (p) => p.join('.');

    function field(f, obj, path, host) {
        const wrap = document.createElement('div'); wrap.className = 'fld';
        const key = f.k; const p = key == null ? path : [...path, key];
        wrap.dataset.path = pathStr(p);
        const lbl = (extra = '') => `<span class="lbl">${esc(f.l)}${f.req ? ' <span class="muted">*</span>' : ''}${extra}</span>${f.hint ? `<span class="hint">${esc(f.hint)}</span>` : ''}`;
        const errBox = () => { const e = document.createElement('div'); e.className = 'err'; e.setAttribute('role', 'alert'); wrap.append(e); };

        switch (f.t) {
            case 'note': { wrap.className = 'note'; wrap.textContent = f.l; break; }
            case 'text': case 'url': {
                wrap.innerHTML = `<label>${lbl(f.max ? '<span class="cnt"></span>' : '')}<input type="${f.t === 'url' ? 'url' : 'text'}" maxlength="${f.max || 255}" autocomplete="off"></label>`;
                const inp = $('input', wrap); const cnt = $('.cnt', wrap); inp.value = obj[key] ?? '';
                const upd = () => { if (cnt) cnt.textContent = `${inp.value.length}/${f.max}`; };
                upd();
                inp.addEventListener('input', () => { obj[key] = inp.value === '' && !f.req ? null : inp.value; upd(); inp.classList.remove('invalid'); changed(); });
                errBox(); break;
            }
            case 'area': {
                wrap.innerHTML = `<label>${lbl(f.max ? '<span class="cnt"></span>' : '')}<textarea rows="${f.rows || 3}" maxlength="${f.max || 1000}"></textarea></label>`;
                const ta = $('textarea', wrap); const cnt = $('.cnt', wrap); ta.value = obj[key] ?? '';
                const upd = () => { if (cnt) cnt.textContent = `${ta.value.length}/${f.max}`; }; upd();
                ta.addEventListener('input', () => { obj[key] = ta.value === '' && !f.req ? null : ta.value; upd(); changed(); });
                errBox(); break;
            }
            case 'bool': {
                wrap.innerHTML = `<label class="switch"><input type="checkbox"><span>${esc(f.l)}${f.hint ? `<span class="hint">${esc(f.hint)}</span>` : ''}</span></label>`;
                const c = $('input', wrap); c.checked = !!obj[key];
                c.addEventListener('change', () => { obj[key] = c.checked; changed(); }); break;
            }
            case 'num': {
                wrap.innerHTML = `<label>${lbl()}<input type="number" ${f.min != null ? `min="${f.min}"` : ''} ${f.max != null ? `max="${f.max}"` : ''} step="${f.step || 1}"></label>`;
                const inp = $('input', wrap); inp.value = obj[key] ?? '';
                inp.addEventListener('input', () => { const v = inp.value; obj[key] = v === '' ? null : (f.int ? parseInt(v, 10) : parseFloat(v)); changed(); });
                errBox(); break;
            }
            case 'dt': {
                wrap.innerHTML = `<label>${lbl()}<input type="datetime-local"></label>`;
                const inp = $('input', wrap); inp.value = obj[key] ?? '';
                inp.addEventListener('input', () => { obj[key] = inp.value || null; changed(); }); errBox(); break;
            }
            case 'select': {
                const cur = obj[key]; const opts = f.opts.some((o) => o[0] === cur) || cur == null ? f.opts : [...f.opts, [cur, cur]];
                wrap.innerHTML = `<label>${lbl()}<select>${opts.map((o) => `<option value="${esc(o[0])}">${esc(o[1])}</option>`).join('')}</select></label>`;
                const s = $('select', wrap); s.value = cur ?? f.opts[0][0]; if (cur == null) obj[key] = s.value;
                s.addEventListener('change', () => { obj[key] = s.value; changed(); }); break;
            }
            case 'color': {
                wrap.innerHTML = `<label>${lbl()}<input type="color"></label>`;
                const c = $('input', wrap); c.value = obj[key] || '#2e486d'; c.addEventListener('input', () => { obj[key] = c.value; changed(); }); break;
            }
            case 'group': {
                const fs = document.createElement('fieldset'); fs.className = 'fgroup'; fs.innerHTML = `<legend>${esc(f.l)}</legend>`;
                const sub = key == null ? obj : (obj[key] ??= {});
                f.f.forEach((c) => field(c, sub, key == null ? path : [...path, key], fs));
                host.append(fs); return;
            }
            case 'photo': photoWidget(wrap, f, obj, key, p); break;
            case 'photos': photosWidget(wrap, f, obj, key, p); break;
            case 'audio': audioWidget(wrap, f, obj, key); break;
            case 'list': listWidget(wrap, f, obj, key, p); break;
        }
        host.append(wrap);
    }

    // ---- unggah foto ----
    async function uploadImage(file) {
        const fd = new FormData(); fd.append('file', file); fd.append('group', GROUP[cfg.section] || 'misc');
        const r = await api(cfg.urlImage, { method: 'POST', form: fd });
        if (!r.ok) { toast(firstError(r.data, 'Foto gagal diunggah.'), 'err'); return null; }
        return r.data.ref;
    }
    function pickFile(accept, multiple = false) {
        return new Promise((resolve) => {
            const i = document.createElement('input'); i.type = 'file'; i.accept = accept; i.multiple = multiple;
            i.addEventListener('change', () => resolve([...i.files])); i.click();
        });
    }

    // ---- pustaka foto default ----
    const libDlg = $('#lib-dlg'); const libGrid = $('#lib-grid');
    libGrid.innerHTML = boot.library.map((p) => `<button type="button" data-p="${esc(p)}" title="${esc(p)}"><img loading="lazy" alt="${esc(p)}" src="${cfg.baseDefault}/${esc(p)}"></button>`).join('');
    $('#lib-close').addEventListener('click', () => libDlg.close());
    function chooseDefault() {
        return new Promise((resolve) => {
            const onClick = (e) => { const b = e.target.closest('[data-p]'); if (!b) return; cleanup(); libDlg.close(); resolve({ source: 'default', path: b.dataset.p, crop: null, alt: null }); };
            const onClose = () => { cleanup(); resolve(null); };
            const cleanup = () => { libGrid.removeEventListener('click', onClick); libDlg.removeEventListener('close', onClose); };
            libGrid.addEventListener('click', onClick); libDlg.addEventListener('close', onClose, { once: true }); libDlg.showModal();
        });
    }

    // ---- widget satu foto ----
    // opsi: uploadOnly = tanpa tombol "Foto default"; fit = 'contain' untuk QRIS/logo (gambar utuh, tanpa crop)
    function photoUI(ref, ratio, { required, onChange, resetFn, uploadOnly = false, fit = null }) {
        const el = document.createElement('div'); el.className = 'photo';
        const draw = () => {
            const u = urlOf(ref);
            const c = ref?.crop;
            el.innerHTML = `<div class="th ${u ? '' : 'empty'}" style="aspect-ratio:${ratio || 1}">${u ? `<img alt="" src="${esc(u)}" style="${fit === 'contain' ? 'object-fit:contain;background:#fff;' : ''}${c ? `object-position:${c.x}% ${c.y}%;transform:scale(${c.zoom || 1});transform-origin:${c.x}% ${c.y}%` : ''}">` : 'Belum ada foto'}</div>
              <div><div class="src">${ref ? (ref.source === 'upload' ? 'Foto unggahan sendiri' : 'Foto default') : 'Kosong (memakai tampilan polos)'}${c ? ' · crop diatur' : ''}</div>
              <div class="acts"><button type="button" class="btn ghost sm" data-a="up">${uploadOnly && ref ? 'Ganti foto' : 'Unggah foto'}</button>${uploadOnly ? '' : '<button type="button" class="btn ghost sm" data-a="lib">Foto default</button>'}
              ${fit === 'contain' ? '' : `<button type="button" class="btn ghost sm" data-a="crop" ${u ? '' : 'disabled'}>Crop / posisi</button>`}
              ${resetFn ? '<button type="button" class="btn ghost sm" data-a="reset">Reset ke default</button>' : ''}${!required && ref ? '<button type="button" class="btn ghost sm" data-a="rm" style="color:var(--bad)">Hapus</button>' : ''}</div>
              ${ref ? `<label style="margin:8px 0 0;font-weight:500">Teks alternatif<input data-alt maxlength="150" value="${esc(ref.alt || '')}" placeholder="Deskripsi singkat foto"></label>` : ''}</div>`;
        };
        const set = (v) => { ref = v; onChange(v); draw(); };
        el.addEventListener('click', async (e) => {
            const b = e.target.closest('[data-a]'); if (!b) return;
            if (b.dataset.a === 'up') { const [f] = await pickFile('image/jpeg,image/png,image/webp'); if (f) { b.disabled = true; const r = await uploadImage(f); b.disabled = false; if (r) set({ ...r, alt: ref?.alt ?? null, crop: null }); } }
            if (b.dataset.a === 'lib') { const r = await chooseDefault(); if (r) set({ ...r, alt: ref?.alt ?? null }); }
            if (b.dataset.a === 'crop') { const c = await openCrop(urlOf(ref), ratio || 1, ref.crop); if (c !== undefined) set({ ...ref, crop: c }); }
            if (b.dataset.a === 'reset') { const d = resetFn(); set(d ? clone(d) : null); toast('Foto dikembalikan ke default (belum disimpan).', 'ok'); }
            if (b.dataset.a === 'rm') set(null);
        });
        el.addEventListener('input', (e) => { if (e.target.matches('[data-alt]')) { ref.alt = e.target.value || null; onChange(ref, true); } });
        draw();
        return el;
    }
    function photoWidget(wrap, f, obj, key, p) {
        wrap.innerHTML = `<span class="lbl">${esc(f.l)}${f.req ? ' *' : ''}</span>${f.hint ? `<span class="hint">${esc(f.hint)}</span>` : ''}`;
        const numeric = p.some((s) => /^\d+$/.test(s));
        wrap.append(photoUI(obj[key] ?? null, f.r, {
            required: f.req, onChange: (v) => { obj[key] = v; changed(); },
            resetFn: numeric || f.upload_only ? null : () => getPath(defaults, p),
            uploadOnly: !!f.upload_only, fit: f.fit || null,
        }));
        const e = document.createElement('div'); e.className = 'err'; wrap.append(e);
    }
    function photosWidget(wrap, f, obj, key, p) {
        wrap.innerHTML = `<span class="lbl">${esc(f.l)}</span>`;
        const list = (obj[key] ??= []);
        const box = document.createElement('div'); box.className = 'thumbs'; wrap.append(box);
        const draw = () => {
            box.innerHTML = '';
            const n = f.fixed || list.length;
            for (let i = 0; i < n; i++) {
                const row = document.createElement('div'); row.dataset.path = pathStr([...p, i]);
                if (f.slots) row.innerHTML = `<span class="hint" style="margin:4px 0">${esc(f.slots[i][0])}</span>`;
                const ratio = f.slots ? f.slots[i][1] : f.r;
                const ui = photoUI(list[i] ?? null, ratio, { required: false, onChange: (v) => { list[i] = v; if (f.fixed) { while (list.length < f.fixed) list.push(null); } changed(); if (!v && !f.fixed) { list.splice(i, 1); draw(); } },
                    resetFn: f.fixed ? () => getPath(defaults, [...p, i]) : null });
                row.append(ui);
                if (!f.fixed) { const m = document.createElement('div'); m.className = 'acts'; m.style.margin = '4px 0 8px'; m.innerHTML = `<button type="button" class="btn ghost sm" data-m="-1" ${i === 0 ? 'disabled' : ''}>↑ Naik</button><button type="button" class="btn ghost sm" data-m="1" ${i === list.length - 1 ? 'disabled' : ''}>↓ Turun</button>`;
                    m.addEventListener('click', (e) => { const b = e.target.closest('[data-m]'); if (!b) return; const d = +b.dataset.m; [list[i], list[i + d]] = [list[i + d], list[i]]; changed(); draw(); }); row.append(m); }
                box.append(row);
            }
            if (!f.fixed && list.length < (f.max || 6)) {
                const add = document.createElement('div'); add.className = 'acts'; add.innerHTML = '<button type="button" class="btn ghost sm" data-a="add">+ Unggah foto</button><button type="button" class="btn ghost sm" data-a="addlib">+ Foto default</button>';
                add.addEventListener('click', async (e) => { const b = e.target.closest('[data-a]'); if (!b) return;
                    let ref = null;
                    if (b.dataset.a === 'add') { const [fl] = await pickFile('image/jpeg,image/png,image/webp'); if (fl) ref = await uploadImage(fl); } else ref = await chooseDefault();
                    if (ref) { list.push({ ...ref, crop: null, alt: null }); changed(); draw(); } });
                box.append(add);
            }
        };
        if (f.fixed) while (list.length < f.fixed) list.push(null);
        draw();
    }
    function audioWidget(wrap, f, obj, key) {
        const draw = () => {
            const ref = obj[key];
            wrap.innerHTML = `<span class="lbl">${esc(f.l)}</span>${ref ? `<audio controls preload="none" style="width:100%;margin:6px 0" src="${esc(urlOf(ref))}"></audio>` : '<div class="hint">Belum ada lagu.</div>'}
            <div class="acts" style="justify-content:flex-start"><button type="button" class="btn ghost sm" data-a="up">${ref ? 'Ganti lagu' : 'Unggah lagu'}</button>${ref ? '<button type="button" class="btn ghost sm" data-a="rm" style="color:var(--bad)">Hapus lagu</button>' : ''}</div><div class="err"></div>`;
        };
        wrap.addEventListener('click', async (e) => { const b = e.target.closest('[data-a]'); if (!b) return;
            if (b.dataset.a === 'up') { const [fl] = await pickFile('audio/*,.mp3,.m4a,.ogg,.wav'); if (!fl) return; const fd = new FormData(); fd.append('file', fl); b.disabled = true; const r = await api(cfg.urlAudio, { method: 'POST', form: fd }); b.disabled = false;
                if (!r.ok) { toast(firstError(r.data, 'Lagu gagal diunggah.'), 'err'); return; } obj[key] = r.data.ref; draw(); changed(); }
            if (b.dataset.a === 'rm') { obj[key] = null; draw(); changed(); } });
        draw();
    }

    // ---- daftar berulang (galeri, cerita, rekening, warna) ----
    function listWidget(wrap, f, obj, key, p) {
        const list = (obj[key] ??= []);
        wrap.innerHTML = `<span class="lbl">${esc(f.l)} <span class="muted" data-count></span></span>${f.note ? `<div class="note">${esc(f.note)}</div>` : ''}`;
        const box = document.createElement('div'); box.className = 'items'; wrap.append(box);
        const bar = document.createElement('div'); bar.className = 'acts'; bar.style.cssText = 'justify-content:flex-start;margin-top:10px'; wrap.append(bar);
        const title = (it, i) => f.kind === 'gallery' ? (it.caption || `Foto ${i + 1}`) : f.kind === 'story' ? `${it.year || '—'} · ${it.title || 'Cerita baru'}` : (it.bank_name ? `${it.bank_name} · ${it.account_number || ''}` : (it.name || `${f.item} ${i + 1}`));
        const draw = (openIdx = -1) => {
            $('[data-count]', wrap).textContent = `(${list.length}${f.max ? '/' + f.max : ''})`;
            box.innerHTML = '';
            list.forEach((it, i) => {
                const d = document.createElement('details'); d.className = 'item' + (it.is_active === false ? ' inactive' : ''); if (i === openIdx) d.open = true;
                const th = f.kind === 'gallery' && it.photo ? `<img alt="" src="${esc(urlOf(it.photo))}" style="width:40px;height:40px;object-fit:cover;border-radius:6px">` : '';
                d.innerHTML = `<summary>${th}<span class="tt">${esc(title(it, i))}</span>${it.is_active === false ? '<span class="off">Nonaktif</span>' : ''}
                  <button type="button" class="btn ghost sm" data-m="-1" ${i === 0 ? 'disabled' : ''} aria-label="Naikkan">↑</button><button type="button" class="btn ghost sm" data-m="1" ${i === list.length - 1 ? 'disabled' : ''} aria-label="Turunkan">↓</button>
                  <button type="button" class="btn ghost sm" data-del style="color:var(--bad)" aria-label="Hapus">✕</button></summary><div class="body"></div>`;
                const body = $('.body', d);
                f.f.forEach((c) => field(c, it, [...p, i], body));
                d.addEventListener('click', async (e) => {
                    const m = e.target.closest('[data-m]'); const del = e.target.closest('[data-del]');
                    if (m) { e.preventDefault(); const dd = +m.dataset.m; [list[i], list[i + dd]] = [list[i + dd], list[i]]; changed(); draw(i + dd); }
                    if (del) { e.preventDefault(); if (await confirmDlg('Hapus item ini?', 'Item dihapus dari draft (belum permanen sampai Anda menyimpan).', 'Hapus', true)) { list.splice(i, 1); changed(); draw(); } }
                });
                d.addEventListener('input', () => { const t = $('.tt', d); if (t) t.textContent = title(it, i); });
                box.append(d);
            });
        };
        const full = () => f.max && list.length >= f.max;
        const addBtn = (label, fn) => { const b = document.createElement('button'); b.type = 'button'; b.className = 'btn ghost sm'; b.textContent = label; b.addEventListener('click', async () => { if (full()) { toast(`Maksimal ${f.max} item.`, 'err'); return; } await fn(); }); bar.append(b); };
        if (f.kind === 'gallery') {
            addBtn('+ Unggah foto', async () => { const files = await pickFile('image/jpeg,image/png,image/webp', true); for (const fl of files) { if (full()) break; const r = await uploadImage(fl); if (r) list.push({ ...clone(f.new), photo: { ...r, crop: null, alt: null } }); } if (files.length) { changed(); draw(list.length - 1); } });
            addBtn('+ Pilih foto default', async () => { const r = await chooseDefault(); if (r) { list.push({ ...clone(f.new), photo: r }); changed(); draw(list.length - 1); } });
        } else {
            addBtn(`+ Tambah ${f.item.toLowerCase()}`, async () => { list.push(clone(f.new)); changed(); draw(list.length - 1); });
        }
        draw();
    }

    // ---------------- crop ----------------
    const cropDlg = $('#crop-dlg'); const cropBox = $('#crop-box'); const cropImg = $('#crop-img'); const zoomIn = $('#crop-zoom');
    function openCrop(url, ratio, current) {
        return new Promise((resolve) => {
            let st = { x: current?.x ?? 50, y: current?.y ?? 50, zoom: current?.zoom ?? 1 };
            const W = Math.min(420, window.innerWidth - 80); cropBox.style.width = W + 'px'; cropBox.style.height = (W / ratio) + 'px';
            cropImg.style.cssText = 'left:0;top:0;width:100%;height:100%;object-fit:cover';
            cropImg.src = url; zoomIn.value = st.zoom;
            const paint = () => { cropImg.style.objectPosition = `${st.x}% ${st.y}%`; cropImg.style.transformOrigin = `${st.x}% ${st.y}%`; cropImg.style.transform = `scale(${st.zoom})`; };
            const dims = () => { const H = W / ratio; const nw = cropImg.naturalWidth || 1; const nh = cropImg.naturalHeight || 1; const s0 = Math.max(W / nw, H / nh); return { W, H, iw: nw * s0, ih: nh * s0 }; };
            paint();
            let drag = null;
            const down = (e) => { drag = { x: e.clientX, y: e.clientY, sx: st.x, sy: st.y }; cropBox.classList.add('drag'); cropBox.setPointerCapture(e.pointerId); };
            const move = (e) => { if (!drag) return; const d = dims(); const ox = d.iw * st.zoom - d.W; const oy = d.ih * st.zoom - d.H;
                if (ox > 1) st.x = Math.min(100, Math.max(0, drag.sx - ((e.clientX - drag.x) / ox) * 100));
                if (oy > 1) st.y = Math.min(100, Math.max(0, drag.sy - ((e.clientY - drag.y) / oy) * 100)); paint(); };
            const up = () => { drag = null; cropBox.classList.remove('drag'); };
            const key = (e) => { const s = 2; if (e.key === 'ArrowLeft') st.x = Math.max(0, st.x - s); else if (e.key === 'ArrowRight') st.x = Math.min(100, st.x + s); else if (e.key === 'ArrowUp') st.y = Math.max(0, st.y - s); else if (e.key === 'ArrowDown') st.y = Math.min(100, st.y + s); else return; e.preventDefault(); paint(); };
            const zoom = () => { st.zoom = +zoomIn.value; paint(); };
            const done = (val) => { cleanup(); cropDlg.close(); resolve(val); };
            const ok = () => done(st.zoom <= 1.001 && Math.abs(st.x - 50) < .5 && Math.abs(st.y - 50) < .5 ? null : { x: +st.x.toFixed(2), y: +st.y.toFixed(2), zoom: +st.zoom.toFixed(3) });
            const cancel = () => done(undefined);
            const reset = () => { st = { x: 50, y: 50, zoom: 1 }; zoomIn.value = 1; paint(); };
            cropBox.addEventListener('pointerdown', down); cropBox.addEventListener('pointermove', move); cropBox.addEventListener('pointerup', up); cropBox.addEventListener('keydown', key); zoomIn.addEventListener('input', zoom);
            $('#crop-ok').addEventListener('click', ok); $('#crop-cancel').addEventListener('click', cancel); $('#crop-reset').addEventListener('click', reset); cropDlg.addEventListener('cancel', cancel, { once: true });
            function cleanup() { cropBox.removeEventListener('pointerdown', down); cropBox.removeEventListener('pointermove', move); cropBox.removeEventListener('pointerup', up); cropBox.removeEventListener('keydown', key); zoomIn.removeEventListener('input', zoom); $('#crop-ok').removeEventListener('click', ok); $('#crop-cancel').removeEventListener('click', cancel); $('#crop-reset').removeEventListener('click', reset); }
            cropDlg.showModal();
        });
    }

    // ---------------- render ----------------
    function renderForm() { formEl.innerHTML = ''; boot.fields.forEach((f) => field(f, data, [], formEl)); }

    // ---------------- aksi ----------------
    function showErrors(errors) {
        $$('.err', formEl).forEach((e) => { e.textContent = ''; });
        $$('.invalid', formEl).forEach((e) => e.classList.remove('invalid'));
        let first = null;
        for (const [k, msgs] of Object.entries(errors || {})) {
            const parts = k.replace(/^data\./, '').split('.');
            let el = null;
            for (let n = parts.length; n > 0 && !el; n--) el = formEl.querySelector(`[data-path="${parts.slice(0, n).join('.')}"]`);
            const box = el && (el.querySelector(':scope > .err') || el.querySelector('.err'));
            if (box) { box.textContent = msgs[0]; $('input,textarea,select', el)?.classList.add('invalid'); first ??= el; }
        }
        first?.scrollIntoView({ block: 'center', behavior: 'smooth' });
    }

    btnSave.addEventListener('click', async () => {
        btnSave.disabled = true; showErrors({});
        const r = await api(cfg.urlSave, { method: 'PUT', body: { data, expected_version: version } });
        if (r.ok) { version = r.data.version; saved = JSON.stringify(data); syncDirty(); applyStatus(r.data.content); toast('Draft tersimpan. Klik Publish agar tampil ke tamu.', 'ok'); }
        else if (r.status === 422) { const n = Object.keys(r.data?.errors || {}).length; showErrors(r.data?.errors); toast(`Ada ${n} isian yang perlu diperbaiki.`, 'err'); syncDirty(); }
        else if (r.status === 409) { syncDirty(); if (await confirmDlg('Draft berubah di tempat lain', 'Section ini sudah diubah dari tab/perangkat lain. Muat ulang untuk mengambil versi terbaru (perubahan Anda di halaman ini akan hilang).', 'Muat ulang', true)) { window.__edDirty = false; location.reload(); } }
        else { syncDirty(); toast(firstError(r.data), 'err'); }
    });

    btnCancel.addEventListener('click', async () => {
        if (!(await confirmDlg('Batalkan perubahan?', 'Isian kembali ke versi draft yang terakhir disimpan.', 'Batalkan perubahan', true))) return;
        data = JSON.parse(saved); renderForm(); syncDirty(); renderPreview(); showErrors({});
    });

    $('#btn-reset').addEventListener('click', async () => {
        if (!(await confirmDlg('Reset section ke default?', 'Seluruh isi section ini (teks dan foto) kembali ke konten awal Ayu & Kevin pada draft. Section lain, data tamu, RSVP, dan ucapan tidak berubah.', 'Reset section', true))) return;
        const r = await api(cfg.urlReset, { method: 'POST' });
        if (!r.ok) { toast(firstError(r.data), 'err'); return; }
        const s = await api(`/admin/sections/${cfg.section}`);
        if (s.ok) { data = clone(s.data.data); version = s.data.version; saved = JSON.stringify(data); renderForm(); syncDirty(); renderPreview(); applyStatus(r.data.content); toast('Section dikembalikan ke default (draft).', 'ok'); }
    });

    // tab mobile
    $$('.ed-tabs button').forEach((b) => b.addEventListener('click', () => {
        $$('.ed-tabs button').forEach((x) => x.setAttribute('aria-selected', String(x === b)));
        $('.ed-grid').dataset.show = b.dataset.tab; if (b.dataset.tab === 'preview') setTimeout(() => preview?.refresh(), 50);
    }));

    renderForm(); syncDirty(); refreshStatus();
    if (hasVisual) $('#prev-frame').addEventListener('load', () => renderPreview());
}
