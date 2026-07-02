/*
 |--------------------------------------------------------------------------
 | Diagnostics — log các "điểm quan trọng" ra console để soát lỗi
 |--------------------------------------------------------------------------
 | Module này tập trung toàn bộ việc ghi log gỡ lỗi ở frontend vào một chỗ,
 | thay vì rải console.log khắp nơi. Nó bắt:
 |   • Lỗi JS chưa bắt (window error) & promise bị reject
 |   • Vòng đời Livewire: init, mỗi lần commit (request tới server) & lỗi commit
 |   • Điều hướng wire:navigate (SPA) và HMR của Vite
 |   • Lỗi mạng qua axios (nếu dùng)
 |
 | Bật/tắt: mặc định BẬT. Muốn tắt, mở Console gõ:
 |     localStorage.setItem('diagnostics', 'off'); location.reload();
 | Bật lại:
 |     localStorage.removeItem('diagnostics'); location.reload();
 */

const ENABLED = (() => {
    try {
        return localStorage.getItem('diagnostics') !== 'off';
    } catch (e) {
        return true;
    }
})();

const T0 = performance.now();
const stamp = () => `+${(performance.now() - T0).toFixed(0)}ms`;

const styles = {
    ok: 'color:#059669;font-weight:600',
    info: 'color:#2563eb;font-weight:600',
    warn: 'color:#d97706;font-weight:600',
    err: 'color:#dc2626;font-weight:700',
    muted: 'color:#64748b',
};

function tag(kind, label) {
    const map = { ok: styles.ok, info: styles.info, warn: styles.warn, err: styles.err };
    return [`%c[DIAG]%c ${label} %c${stamp()}`, map[kind] || styles.info, 'color:inherit', styles.muted];
}

const diag = {
    ok: (label, ...rest) => ENABLED && console.log(...tag('ok', label), ...rest),
    info: (label, ...rest) => ENABLED && console.log(...tag('info', label), ...rest),
    warn: (label, ...rest) => ENABLED && console.warn(...tag('warn', label), ...rest),
    error: (label, ...rest) => ENABLED && console.error(...tag('err', label), ...rest),
};

export function installDiagnostics() {
    if (!ENABLED) {
        console.log('%c[DIAG]%c đang TẮT — bật lại: localStorage.removeItem("diagnostics")', styles.muted, 'color:inherit');
        return;
    }

    // ── Thông tin môi trường khi khởi động ────────────────────────────────
    diag.ok('Khởi động', {
        url: location.href,
        viewport: `${window.innerWidth}×${window.innerHeight}`,
        userAgent: navigator.userAgent,
    });

    // ── Lỗi JS chưa bắt ───────────────────────────────────────────────────
    window.addEventListener('error', (e) => {
        // Lỗi tải tài nguyên (img/script/css) có target là element, không có message.
        if (e.target && e.target !== window && (e.target.src || e.target.href)) {
            diag.error('Lỗi tải tài nguyên', e.target.src || e.target.href);
            return;
        }
        diag.error('Uncaught error', e.message, e.error || '', `@ ${e.filename}:${e.lineno}:${e.colno}`);
    }, true);

    window.addEventListener('unhandledrejection', (e) => {
        diag.error('Promise chưa xử lý (unhandledrejection)', e.reason);
    });

    // ── Vòng đời Livewire (API của Livewire v4) ───────────────────────────
    document.addEventListener('livewire:init', () => {
        diag.ok('Livewire: init');

        if (!window.Livewire || typeof window.Livewire.hook !== 'function') {
            diag.warn('Không tìm thấy Livewire.hook — bỏ qua log Livewire');
            return;
        }

        // 'commit' cho biết component nào chuẩn bị gửi lên server (ý định).
        // Lưu ý: ở Livewire v4, succeed/fail của hook 'commit' KHÔNG kích hoạt,
        // nên phần thành công/thất bại được ghi ở hook 'request' bên dưới.
        window.Livewire.hook('commit', ({ component, commit }) => {
            const name = component?.name || component?.id || 'unknown';
            diag.info(`Livewire commit → ${name}`, commit?.calls || commit?.updates || {});
        });

        // 'request' bọc trọn vòng đời request mạng (một request có thể gộp nhiều component).
        window.Livewire.hook('request', ({ url, succeed, fail }) => {
            const started = performance.now();
            const path = (() => { try { return new URL(url).pathname; } catch (e) { return url; } })();

            succeed(({ status }) => {
                diag.ok(`Livewire request ✓ ${status} ${path} (${(performance.now() - started).toFixed(0)}ms)`);
            });

            fail(({ status, content } = {}) => {
                diag.error(`Livewire request ✗ ${status || 'network'} ${path} (${(performance.now() - started).toFixed(0)}ms) — xem tab Network để biết chi tiết`, content || '');
            });
        });
    });

    document.addEventListener('livewire:initialized', () => diag.ok('Livewire: initialized (đã gắn xong component)'));
    document.addEventListener('livewire:navigating', () => diag.info('Livewire: đang điều hướng (wire:navigate)…'));
    document.addEventListener('livewire:navigated', () => diag.ok(`Livewire: đã điều hướng → ${location.pathname}`));

    // ── Alpine ────────────────────────────────────────────────────────────
    document.addEventListener('alpine:init', () => diag.ok('Alpine: init'));
    document.addEventListener('alpine:initialized', () => diag.ok('Alpine: initialized'));

    // ── Lỗi mạng qua axios (nếu có) ───────────────────────────────────────
    if (window.axios && window.axios.interceptors) {
        window.axios.interceptors.response.use(
            (res) => res,
            (error) => {
                const { response, config } = error;
                if (response) {
                    diag.error(`HTTP ${response.status} ${config?.method?.toUpperCase() || ''} ${config?.url || ''}`, response.data);
                } else {
                    diag.error('Lỗi mạng (không nhận được phản hồi)', config?.url || '', error.message);
                }
                return Promise.reject(error);
            }
        );
    }

    // ── HMR của Vite (chỉ ở chế độ dev) ───────────────────────────────────
    if (import.meta && import.meta.hot) {
        import.meta.hot.on('vite:beforeUpdate', () => diag.info('Vite HMR: cập nhật…'));
        import.meta.hot.on('vite:error', (payload) => diag.error('Vite HMR: lỗi biên dịch', payload?.err?.message || payload));
    }

    // Cho phép gọi tay từ console: window.__diag.info('...')
    window.__diag = diag;
}
