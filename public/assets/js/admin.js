// Titik masuk JavaScript dashboard admin: menjalankan modul sesuai halaman (data-page pada <body>).
import { initChrome } from './admin/common.js';
import { initDashboard } from './admin/dashboard.js';
import { initEditor } from './admin/editor.js';
import { initGuests } from './admin/guests.js';
import { initResponses } from './admin/responses.js';
import { initPreviewPage } from './admin/preview.js';

initChrome();
const page = document.body.dataset.page;
if (page === 'dashboard') initDashboard();
if (page === 'editor') initEditor();
if (page === 'guests') initGuests();
if (page === 'responses') initResponses();
if (page === 'preview') initPreviewPage();
