import './http';
import {
    ArrowLeft,
    ArrowRight,
    ArrowUpDown,
    Book,
    Building2,
    Calendar,
    ChartNoAxesCombined,
    ChevronDown,
    ChevronRight,
    CircleAlert,
    CircleCheckBig,
    CircleHelp,
    CircleUserRound,
    CloudUpload,
    createIcons,
    Database,
    DatabaseBackup,
    Download,
    ExternalLink,
    Eye,
    FileSpreadsheet,
    Files,
    FileText,
    FileUp,
    FolderOpen,
    Filter,
    Globe2,
    GraduationCap,
    Hash,
    History,
    Home,
    Info,
    Key,
    LayoutDashboard,
    List,
    ListChecks,
    Lock,
    LockOpen,
    LogIn,
    LogOut,
    MapPin,
    Mail,
    Maximize,
    MessageSquareText,
    Newspaper,
    Pencil,
    Phone,
    Plus,
    RefreshCw,
    RotateCcw,
    Save,
    Search,
    SearchX,
    Send,
    Settings,
    Sheet,
    ShieldCheck,
    Tags,
    Trash2,
    TrendingDown,
    TrendingUp,
    TriangleAlert,
    User,
    UserPlus,
    Users,
    X,
} from 'lucide';

const portalIcons = {
    ArrowLeft,
    ArrowRight,
    ArrowUpDown,
    Book,
    Building2,
    Calendar,
    ChartNoAxesCombined,
    ChevronDown,
    ChevronRight,
    CircleAlert,
    CircleCheckBig,
    CircleHelp,
    CircleUserRound,
    CloudUpload,
    Database,
    DatabaseBackup,
    Download,
    ExternalLink,
    Eye,
    FileSpreadsheet,
    Files,
    FileText,
    FileUp,
    FolderOpen,
    Filter,
    Globe2,
    GraduationCap,
    Hash,
    History,
    Home,
    Info,
    Key,
    LayoutDashboard,
    List,
    ListChecks,
    Lock,
    LockOpen,
    LogIn,
    LogOut,
    MapPin,
    Mail,
    Maximize,
    MessageSquareText,
    Newspaper,
    Pencil,
    Phone,
    Plus,
    RefreshCw,
    RotateCcw,
    Save,
    Search,
    SearchX,
    Send,
    Settings,
    Sheet,
    ShieldCheck,
    Tags,
    Trash2,
    TrendingDown,
    TrendingUp,
    TriangleAlert,
    User,
    UserPlus,
    Users,
    X,
};

const iconAliases = {
    'arrow-counterclockwise': 'rotate-ccw',
    'arrow-repeat': 'refresh-cw',
    'arrows-fullscreen': 'maximize',
    'box-arrow-in-right': 'log-in',
    'box-arrow-right': 'log-out',
    'box-arrow-up-right': 'external-link',
    building: 'building-2',
    'building-gear': 'settings',
    'chat-left-text': 'message-square-text',
    'check-circle-fill': 'circle-check-big',
    'clock-history': 'history',
    'cloud-arrow-up': 'cloud-upload',
    'database-add': 'database',
    'database-check': 'database-backup',
    envelope: 'mail',
    'exclamation-circle-fill': 'circle-alert',
    'exclamation-triangle': 'triangle-alert',
    'file-earmark-arrow-up': 'file-up',
    'file-earmark-excel': 'sheet',
    'file-earmark-spreadsheet': 'sheet',
    'file-earmark-text': 'file-text',
    files: 'files',
    'filetype-csv': 'file-spreadsheet',
    'folder2-open': 'folder-open',
    funnel: 'filter',
    'graph-up-arrow': 'chart-no-axes-combined',
    'grid-1x2': 'layout-dashboard',
    house: 'home',
    mortarboard: 'graduation-cap',
    'info-circle': 'info',
    'map-pin': 'map-pin',
    'person-circle': 'circle-user-round',
    person: 'user',
    phone: 'phone',
    'person-plus': 'user-plus',
    people: 'users',
    'plus-lg': 'plus',
    'question-circle': 'circle-help',
    'ui-checks-grid': 'list-checks',
    trash: 'trash-2',
    unlock: 'lock-open',
    'x-lg': 'x',
};

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('i.bi').forEach((element) => {
        const iconClass = [...element.classList].find((className) => className.startsWith('bi-'));
        if (!iconClass) return;

        const legacyName = iconClass.slice(3);
        element.dataset.lucide = iconAliases[legacyName] || legacyName;
        element.classList.remove('bi', iconClass);
    });

    createIcons({ icons: portalIcons });
});

const closeElement = (element) => {
    element.classList.remove('show');
    document.body.classList.remove('overflow-hidden');
    document.querySelectorAll('.ui-backdrop').forEach((backdrop) => backdrop.remove());
};

const openOverlay = (element) => {
    element.classList.add('show');
    document.body.classList.add('overflow-hidden');

    if (!element.classList.contains('modal')) {
        const backdrop = document.createElement('button');
        backdrop.type = 'button';
        backdrop.className = 'ui-backdrop';
        backdrop.setAttribute('aria-label', 'Tutup menu');
        backdrop.addEventListener('click', () => closeElement(element));
        document.body.appendChild(backdrop);
    }
};

document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-ui-toggle]');

    if (toggle) {
        const type = toggle.dataset.uiToggle;

        if (type === 'dropdown') {
            event.preventDefault();
            const menu = toggle.parentElement?.querySelector('.dropdown-menu');
            document.querySelectorAll('.dropdown-menu.show').forEach((item) => {
                if (item !== menu) item.classList.remove('show');
            });
            menu?.classList.toggle('show');
            return;
        }

        if (type === 'collapse') {
            event.preventDefault();
            const selector = toggle.dataset.uiTarget;
            const target = selector ? document.querySelector(selector) : null;
            target?.classList.toggle('show');
            toggle.classList.toggle('collapsed');
            toggle.setAttribute('aria-expanded', target?.classList.contains('show') ? 'true' : 'false');
            return;
        }

        if (type === 'offcanvas' || type === 'modal') {
            event.preventDefault();
            const selector = toggle.dataset.uiTarget;
            const target = selector ? document.querySelector(selector) : null;
            if (target) openOverlay(target);
            return;
        }

        if (type === 'tab') {
            event.preventDefault();
            const selector = toggle.dataset.uiTarget;
            const target = selector ? document.querySelector(selector) : null;
            const tabs = toggle.closest('.nav-tabs');
            tabs?.querySelectorAll('.nav-link').forEach((item) => item.classList.remove('active'));
            target?.parentElement?.querySelectorAll('.tab-pane').forEach((item) => item.classList.remove('active', 'show'));
            toggle.classList.add('active');
            target?.classList.add('active', 'show');
            return;
        }
    }

    const dismiss = event.target.closest('[data-ui-dismiss]');
    if (dismiss) {
        event.preventDefault();
        const type = dismiss.dataset.uiDismiss;
        closeElement(dismiss.closest(type === 'modal' ? '.modal' : '.offcanvas'));
        return;
    }

    if (!event.target.closest('.dropdown')) {
        document.querySelectorAll('.dropdown-menu.show').forEach((menu) => menu.classList.remove('show'));
    }

    if (event.target.classList.contains('modal')) {
        closeElement(event.target);
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    document.querySelectorAll('.modal.show, .offcanvas.show').forEach(closeElement);
    document.querySelectorAll('.dropdown-menu.show').forEach((menu) => menu.classList.remove('show'));
});
