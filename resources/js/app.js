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
    EyeOff,
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
    Menu,
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
    ShieldOff,
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
    EyeOff,
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
    Menu,
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
    ShieldOff,
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

    const updateRequiredIndicators = () => {
        document.querySelectorAll('.required-mark').forEach((mark) => mark.remove());

        document.querySelectorAll('input[required], select[required], textarea[required]').forEach((control) => {
            if (control.type === 'hidden' || control.disabled || !control.id) return;

            const label = document.querySelector(`label[for="${CSS.escape(control.id)}"]`);
            if (!label || label.querySelector('.required-mark')) return;

            const mark = document.createElement('span');
            mark.className = 'required-mark';
            mark.textContent = '*';
            mark.setAttribute('aria-hidden', 'true');
            label.appendChild(mark);
        });
    };

    updateRequiredIndicators();
    window.Pusaka = { ...(window.Pusaka || {}), updateRequiredIndicators };

    const closeAdminSelects = (except = null) => {
        document.querySelectorAll('.admin-select.is-open').forEach((select) => {
            if (select === except) return;
            select.classList.remove('is-open');
            select.querySelector('.admin-select-button')?.setAttribute('aria-expanded', 'false');
        });
    };

    const updateAdminSelect = (select) => {
        const custom = select.nextElementSibling;
        if (!custom?.classList.contains('admin-select')) return;

        const selectedOption = select.selectedOptions[0];
        const buttonText = custom.querySelector('[data-admin-select-text]');
        const optionButtons = custom.querySelectorAll('[data-admin-select-option]');

        if (buttonText) {
            buttonText.textContent = selectedOption?.textContent?.trim() || 'Pilih';
        }

        optionButtons.forEach((optionButton) => {
            const isSelected = optionButton.dataset.value === select.value;
            optionButton.classList.toggle('is-selected', isSelected);
            optionButton.setAttribute('aria-selected', isSelected ? 'true' : 'false');
        });
    };

    document.querySelectorAll('.admin-body select.form-select').forEach((select) => {
        if (select.multiple || select.dataset.adminSelectEnhanced === 'true') return;

        select.dataset.adminSelectEnhanced = 'true';
        select.classList.add('admin-native-select');
        select.setAttribute('tabindex', '-1');

        const custom = document.createElement('div');
        custom.className = 'admin-select';

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'admin-select-button';
        button.disabled = select.disabled;
        button.setAttribute('aria-haspopup', 'listbox');
        button.setAttribute('aria-expanded', 'false');
        button.innerHTML = '<span data-admin-select-text></span><i data-lucide="chevron-down" aria-hidden="true"></i>';

        const menu = document.createElement('div');
        menu.className = 'admin-select-menu';
        menu.setAttribute('role', 'listbox');

        [...select.options].forEach((option) => {
            const optionButton = document.createElement('button');
            optionButton.type = 'button';
            optionButton.className = 'admin-select-option';
            optionButton.dataset.value = option.value;
            optionButton.textContent = option.textContent;
            optionButton.disabled = option.disabled;
            optionButton.setAttribute('role', 'option');

            optionButton.addEventListener('click', () => {
                select.value = option.value;
                select.dispatchEvent(new Event('input', { bubbles: true }));
                select.dispatchEvent(new Event('change', { bubbles: true }));
                updateAdminSelect(select);
                closeAdminSelects();
                button.focus();
            });

            menu.appendChild(optionButton);
        });

        button.addEventListener('click', () => {
            const willOpen = !custom.classList.contains('is-open');
            closeAdminSelects(custom);
            custom.classList.toggle('is-open', willOpen);
            button.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        });

        select.addEventListener('change', () => updateAdminSelect(select));

        custom.append(button, menu);
        select.insertAdjacentElement('afterend', custom);
        updateAdminSelect(select);
    });

    createIcons({ icons: portalIcons });

    const applyAdminSidebarState = (collapsed) => {
        document.body.classList.toggle('admin-sidebar-collapsed', collapsed);
        document.querySelectorAll('[data-ui-toggle="admin-sidebar"]').forEach((button) => {
            button.setAttribute('aria-pressed', collapsed ? 'true' : 'false');
            button.setAttribute('title', collapsed ? 'Tampilkan navigasi' : 'Sembunyikan navigasi');
        });
    };

    try {
        applyAdminSidebarState(localStorage.getItem('sipakemAdminSidebarCollapsed') === '1');
    } catch {
        applyAdminSidebarState(false);
    }

    document.querySelectorAll('.admin-toast').forEach((toast) => {
        window.setTimeout(() => {
            toast.classList.add('is-hiding');
            window.setTimeout(() => toast.remove(), 300);
        }, 3500);
    });

    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        const selector = button.dataset.passwordTarget;
        const input = selector ? document.querySelector(selector) : null;

        if (!(input instanceof HTMLInputElement)) return;

        button.addEventListener('click', () => {
            const willShow = input.type === 'password';
            input.type = willShow ? 'text' : 'password';
            button.setAttribute('aria-label', willShow ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
            button.innerHTML = `<i data-lucide="${willShow ? 'eye-off' : 'eye'}"></i>`;
            createIcons({ icons: portalIcons });
            input.focus();
        });
    });

    document.querySelectorAll('[data-auto-open-modal]').forEach((modal) => {
        openOverlay(modal);
        createIcons({ icons: portalIcons });
    });

    const requiredObserver = new MutationObserver((mutations) => {
        if (mutations.some((mutation) => mutation.type === 'attributes' && mutation.attributeName === 'required')) {
            updateRequiredIndicators();
        }
    });

    requiredObserver.observe(document.body, {
        attributes: true,
        attributeFilter: ['required'],
        subtree: true,
    });
});

let pendingConfirmTarget = null;

const closeElement = (element) => {
    if (!element) return;

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

const openConfirmation = (target) => {
    const modal = document.getElementById('adminConfirmModal');
    if (!modal) return false;

    pendingConfirmTarget = target;

    const hasReplacementFile = target instanceof HTMLFormElement
        && Boolean(target.dataset.confirmFile)
        && [...target.querySelectorAll('input[type="file"]')].some((input) => input.files?.length);
    const title = hasReplacementFile
        ? target.dataset.confirmFileTitle || target.dataset.confirmTitle || 'Konfirmasi Aksi'
        : target.dataset.confirmTitle || 'Konfirmasi Aksi';
    const text = hasReplacementFile
        ? target.dataset.confirmFile
        : target.dataset.confirm || 'Apakah Anda yakin ingin melanjutkan aksi ini?';
    const label = target.dataset.confirmLabel || 'Ya, Lanjutkan';
    const variant = target.dataset.confirmVariant || 'primary';
    const iconName = variant === 'danger' ? 'triangle-alert' : 'circle-check-big';

    modal.querySelector('[data-confirm-title]').textContent = title;
    modal.querySelector('[data-confirm-text]').textContent = text;

    const confirmButton = modal.querySelector('[data-confirm-approve]');
    confirmButton.textContent = label;
    confirmButton.className = variant === 'danger' ? 'btn btn-danger' : 'btn btn-primary';

    const icon = modal.querySelector('[data-confirm-icon]');
    icon.classList.toggle('is-danger', variant === 'danger');
    icon.innerHTML = `<i data-lucide="${iconName}"></i>`;
    createIcons({ icons: portalIcons });

    openOverlay(modal);
    return true;
};

document.addEventListener('submit', (event) => {
    const form = event.target.closest('form[data-confirm]');
    if (!form || form.dataset.confirmed === 'true') return;

    event.preventDefault();
    openConfirmation(form);
});

document.addEventListener('click', (event) => {
    const confirmApprove = event.target.closest('[data-confirm-approve]');
    if (confirmApprove) {
        event.preventDefault();
        const modal = confirmApprove.closest('.modal');
        const target = pendingConfirmTarget;
        pendingConfirmTarget = null;
        closeElement(modal);

        if (target instanceof HTMLFormElement) {
            target.dataset.confirmed = 'true';
            target.requestSubmit();
            return;
        }

        if (target instanceof HTMLAnchorElement) {
            window.location.href = target.href;
        }
        return;
    }

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
            toggle.closest('.admin-nav-section')?.classList.toggle('is-open', target?.classList.contains('show'));
            toggle.setAttribute('aria-expanded', target?.classList.contains('show') ? 'true' : 'false');
            if (target?.classList.contains('show')) {
                target.querySelectorAll('.dropdown-menu.show').forEach((menu) => menu.classList.remove('show'));
            }
            return;
        }

        if (type === 'admin-sidebar') {
            event.preventDefault();
            const collapsed = !document.body.classList.contains('admin-sidebar-collapsed');
            document.body.classList.toggle('admin-sidebar-collapsed', collapsed);
            toggle.setAttribute('aria-pressed', collapsed ? 'true' : 'false');
            toggle.setAttribute('title', collapsed ? 'Tampilkan navigasi' : 'Sembunyikan navigasi');

            try {
                localStorage.setItem('sipakemAdminSidebarCollapsed', collapsed ? '1' : '0');
            } catch {
                // Browser storage may be disabled; the current page state still works.
            }
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

        if (type === 'alert') {
            dismiss.closest('.admin-toast, .alert')?.remove();
            return;
        }

        closeElement(dismiss.closest(type === 'modal' ? '.modal' : '.offcanvas'));
        return;
    }

    if (!event.target.closest('.dropdown')) {
        document.querySelectorAll('.dropdown-menu.show').forEach((menu) => menu.classList.remove('show'));
    }

    if (!event.target.closest('.admin-select')) {
        document.querySelectorAll('.admin-select.is-open').forEach((select) => {
            select.classList.remove('is-open');
            select.querySelector('.admin-select-button')?.setAttribute('aria-expanded', 'false');
        });
    }

    if (event.target.classList.contains('modal')) {
        closeElement(event.target);
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    document.querySelectorAll('.modal.show, .offcanvas.show').forEach(closeElement);
    document.querySelectorAll('.dropdown-menu.show').forEach((menu) => menu.classList.remove('show'));
    document.querySelectorAll('.admin-select.is-open').forEach((select) => {
        select.classList.remove('is-open');
        select.querySelector('.admin-select-button')?.setAttribute('aria-expanded', 'false');
    });
});
