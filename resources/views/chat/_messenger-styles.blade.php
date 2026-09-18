<style>
    .messenger-page {
        --messenger-blue: #2563eb;
        --messenger-ink: #172033;
        --messenger-muted: #8b96a8;
        min-height: calc(100vh - 150px);
        padding: 0 0 24px;
    }
    .messenger-shell {
        display: grid;
        grid-template-columns: 270px minmax(0, 1fr);
        /*
         * Keep the composer inside the first viewport. The shared layout has
         * a navbar and footer, so using the full viewport height clips it.
         */
        height: min(720px, calc(100vh - 250px));
        min-height: 420px;
        overflow: hidden;
        background: #fff;
        border: 1px solid #e5eaf1;
        border-radius: 14px;
        box-shadow: 0 14px 35px rgba(24, 39, 75, .1);
    }
    .messenger-sidebar {
        display: flex;
        min-width: 0;
        flex-direction: column;
        border-right: 1px solid #e7ebf1;
        background: #fff;
    }
    .messenger-sidebar-header { padding: 20px 16px 12px; }
    .messenger-sidebar-title {
        margin: 0 0 12px;
        color: var(--messenger-ink);
        font-size: 14px;
        font-weight: 800;
    }
    .messenger-search {
        display: flex;
        align-items: center;
        gap: 7px;
        padding: 8px 11px;
        color: #a7b0bd;
        background: #f1f4f8;
        border-radius: 20px;
        font-size: 11px;
    }
    .messenger-conversations { padding: 8px; overflow-y: auto; }
    .messenger-conversation {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 11px 9px;
        color: #526075;
        text-decoration: none;
        border-radius: 11px;
    }
    .messenger-conversation:hover, .messenger-conversation.active {
        color: #fff;
        background: #111a2b;
    }
    .messenger-avatar {
        display: grid;
        flex: 0 0 34px;
        width: 34px;
        height: 34px;
        place-items: center;
        overflow: hidden;
        color: #fff;
        background: #354258;
        border-radius: 50%;
        font-size: 12px;
        font-weight: 700;
    }
    .messenger-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .messenger-conversation-copy { min-width: 0; flex: 1; }
    .messenger-conversation-name, .messenger-conversation-preview {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .messenger-conversation-name { font-size: 12px; font-weight: 700; }
    .messenger-conversation-preview { margin-top: 3px; color: #91a0b3; font-size: 10px; }
    .messenger-conversation.active .messenger-conversation-preview { color: #b8c2d1; }
    .messenger-sidebar-note {
        margin: auto 8px 50px;
        padding: 12px;
        color: #92501c;
        background: #fff0df;
        border: 1px solid #ffd4a8;
        border-radius: 10px;
        font-size: 10px;
        line-height: 1.5;
    }
    .messenger-main {
        display: flex;
        min-width: 0;
        min-height: 0;
        height: 100%;
        flex-direction: column;
        background: #fbfcfe;
    }
    .messenger-header {
        display: flex;
        align-items: center;
        gap: 10px;
        min-height: 62px;
        padding: 10px 18px;
        background: #fff;
        border-bottom: 1px solid #e7ebf1;
    }
    .messenger-header h1 { margin: 0; color: var(--messenger-ink); font-size: 14px; font-weight: 800; }
    .messenger-header small { display: block; margin-top: 3px; color: var(--messenger-muted); font-size: 10px; }
    .messenger-status { color: #18ae72; font-size: 9px; }
    .messenger-actions { margin-left: auto; display: flex; gap: 16px; color: #8b98aa; }
    .messenger-body {
        flex: 1;
        flex-basis: 0;
        min-height: 0 !important;
        max-height: none !important;
        padding: 24px 8% !important;
        overflow-y: auto;
        overscroll-behavior: contain;
        -webkit-overflow-scrolling: touch;
        background: linear-gradient(145deg, #fff 0%, #f6f8fc 100%);
    }
    .messenger-body .mb-3 { margin-bottom: 18px !important; }
    .messenger-body .bg-primary, .messenger-body .bg-light {
        border-radius: 12px !important;
        box-shadow: 0 2px 5px rgba(35, 49, 75, .06);
    }
    .messenger-body .bg-primary { background: var(--messenger-blue) !important; }
    .messenger-body .bg-light { background: #fff !important; border: 1px solid #e5eaf1; }
    .messenger-body .p-3 { padding: 10px 13px !important; font-size: 12px; }
    .messenger-body img.rounded-circle { width: 32px !important; height: 32px !important; }
    .messenger-body .text-xs { color: #a0abba !important; font-size: 9px; }
    .messenger-compose { flex: 0 0 auto; padding: 10px 9%; background: #fff; border-top: 1px solid #e7ebf1; }
    .messenger-compose .input-group { gap: 8px; }
    .messenger-compose .input-group > * { border-radius: 8px !important; }
    .messenger-compose .form-control { border-color: #dbe2eb; font-size: 12px; }
    .messenger-compose .btn-primary { border: 0; padding-inline: 17px; }
    .messenger-compose .btn-outline-secondary { border-color: #dbe2eb; color: #8390a1; }
    @media (max-width: 767px) {
        .messenger-page { min-height: calc(100vh - 90px); padding: 0; }
        .messenger-shell { display: block; height: calc(100vh - 115px); min-height: 500px; border-radius: 10px; }
        .messenger-sidebar { display: none; }
        .messenger-main { height: 100%; }
        .messenger-header { padding: 10px 13px; }
        .messenger-body { padding: 18px 12px !important; }
        .messenger-body .bg-primary, .messenger-body .bg-light { max-width: 86% !important; }
        .messenger-compose { padding: 9px 10px; }
        .messenger-compose .btn { padding-inline: 10px; }
        .messenger-actions { gap: 10px; }
    }
</style>
