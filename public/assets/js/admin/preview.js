import { $, $$ } from './common.js';

/** Mengatur ukuran iframe preview: desktop = kanvas 1440px diskalakan, mobile = 390px (media query mobile aktif sungguhan). */
export function createPreview({ view, stage, frame, onState }) {
    let mode = 'desktop'; let h = 800; let html = null;
    const apply = () => {
        const avail = Math.max(280, view.clientWidth - (mode === 'mobile' ? 34 : 0));
        const w = mode === 'desktop' ? 1440 : 390;
        const scale = Math.min(1, avail / w);
        frame.style.width = w + 'px'; frame.style.height = h + 'px'; frame.style.transform = `scale(${scale})`;
        stage.style.width = Math.round(w * scale) + 'px'; stage.style.height = Math.round(h * scale) + 'px';
        stage.classList.toggle('mobile', mode === 'mobile');
    };
    window.addEventListener('message', (e) => {
        if (e.origin !== location.origin || e.source !== frame.contentWindow) return;
        if (e.data?.type === 'prev-height') { h = Math.max(200, e.data.h); apply(); }
        if (e.data?.type === 'prev-ready' && html !== null) frame.contentWindow.postMessage({ type: 'render', html }, location.origin);
    });
    new ResizeObserver(apply).observe(view);
    $$('[data-mode]').forEach((b) => b.addEventListener('click', () => {
        mode = b.dataset.mode; $$('[data-mode]').forEach((x) => x.classList.toggle('on', x === b)); apply();
        frame.contentWindow?.postMessage({ type: 'render', html }, location.origin);
        setTimeout(apply, 120);
    }));
    apply();
    return {
        show(newHtml) { html = newHtml; frame.contentWindow?.postMessage({ type: 'render', html }, location.origin); onState?.('ok'); },
        refresh: apply,
    };
}

export function initPreviewPage() {
    createPreview({ view: $('#prev-view'), stage: $('#prev-stage'), frame: $('#prev-frame') });
}
