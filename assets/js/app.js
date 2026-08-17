/* assets/js/app.js - JavaScript dinámico y PWA Android handler */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Filtrado dinámico de tablas en tiempo real
    const searchInputs = document.querySelectorAll('.live-search-input');
    searchInputs.forEach(input => {
        const targetTableId = input.getAttribute('data-table');
        const targetTable = document.getElementById(targetTableId);
        if (!targetTable) return;

        input.addEventListener('keyup', () => {
            const query = input.value.toLowerCase().trim();
            const rows = targetTable.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });

    // 2. Controladores de Modales
    window.openModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('active');
        }
    };

    window.closeModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('active');
        }
    };

    // Cerrar modal al hacer clic en el fondo
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                overlay.classList.remove('active');
            }
        });
    });

    // 3. Selección rápida de checkboxes en formulario de reporte
    const checklistItems = document.querySelectorAll('.checklist-item');
    checklistItems.forEach(item => {
        item.addEventListener('click', (e) => {
            if (e.target.tagName !== 'INPUT') {
                const checkbox = item.querySelector('input[type="checkbox"]');
                if (checkbox) {
                    checkbox.checked = !checkbox.checked;
                }
            }
        });
    });
});

// 4. Registro de Service Worker para Aplicación Android PWA
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('sw.js').then(reg => {
            console.log('Service Worker registrado con éxito.', reg);
        }).catch(err => {
            console.log('Error al registrar Service Worker:', err);
        });
    });
}

// Prompt de instalación en Android
let deferredPrompt;
window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    const btnInstall = document.getElementById('btnInstallPwa');
    if (btnInstall) {
        btnInstall.style.display = 'inline-flex';
        btnInstall.addEventListener('click', () => {
            btnInstall.style.display = 'none';
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then((choiceResult) => {
                if (choiceResult.outcome === 'accepted') {
                    console.log('El usuario aceptó instalar la App en Android');
                }
                deferredPrompt = null;
            });
        });
    }
});
