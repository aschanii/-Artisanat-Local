// Dashboard functionality
class Dashboard {
    constructor() {
        this.init();
    }

    init() {
        this.initCharts();
        this.initDataTables();
        this.initEventListeners();
        this.initRealTimeUpdates();
    }

    initCharts() {
        // Revenue chart already initialized in the main dashboard
        this.initSalesChart();
        this.initTrafficChart();
    }

    initSalesChart() {
        const salesCtx = document.getElementById('salesChart');
        if (salesCtx) {
            new Chart(salesCtx, {
                type: 'bar',
                data: {
                    labels: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
                    datasets: [{
                        label: 'Ventes',
                        data: [12, 19, 8, 15, 12, 18, 22],
                        backgroundColor: 'rgba(52, 152, 219, 0.8)',
                        borderColor: 'rgba(52, 152, 219, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    }

    initTrafficChart() {
        const trafficCtx = document.getElementById('trafficChart');
        if (trafficCtx) {
            new Chart(trafficCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Direct', 'Réseaux sociaux', 'Recherche', 'Email'],
                    datasets: [{
                        data: [45, 25, 20, 10],
                        backgroundColor: [
                            '#e67e22',
                            '#3498db',
                            '#2ecc71',
                            '#9b59b6'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    }

    initDataTables() {
        // Initialize DataTables if the library is included
        if (typeof $.fn.DataTable === 'function') {
            $('.datatable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/French.json'
                },
                responsive: true
            });
        }
    }

    initEventListeners() {
        // Status toggles
        document.querySelectorAll('.status-toggle').forEach(toggle => {
            toggle.addEventListener('click', this.handleStatusToggle.bind(this));
        });

        // Bulk actions
        document.querySelectorAll('.bulk-action-form').forEach(form => {
            form.addEventListener('submit', this.handleBulkAction.bind(this));
        });

        // Export buttons
        document.querySelectorAll('.export-btn').forEach(btn => {
            btn.addEventListener('click', this.handleExport.bind(this));
        });

        // Quick stats refresh
        document.querySelectorAll('.refresh-stats').forEach(btn => {
            btn.addEventListener('click', this.refreshStats.bind(this));
        });
    }

    initRealTimeUpdates() {
        // Update stats every 30 seconds
        setInterval(() => {
            this.updateRealTimeStats();
        }, 30000);

        // Check for new orders
        setInterval(() => {
            this.checkNewOrders();
        }, 60000);
    }

    handleStatusToggle(event) {
        const form = event.target.closest('form');
        if (form) {
            // Show loading state
            const originalText = event.target.textContent;
            event.target.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            event.target.disabled = true;

            // Submit form
            fetch(form.action, {
                method: 'POST',
                body: new FormData(form)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update button state
                    const newStatus = data.new_status;
                    event.target.textContent = newStatus ? 'Actif' : 'Inactif';
                    event.target.className = `status-toggle ${newStatus ? 'active' : 'inactive'}`;
                    
                    // Update hidden input for next toggle
                    form.querySelector('input[name="status"]').value = newStatus ? 'inactive' : 'active';
                    
                    this.showNotification('Statut mis à jour avec succès', 'success');
                } else {
                    this.showNotification('Erreur lors de la mise à jour', 'error');
                    event.target.textContent = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.showNotification('Erreur lors de la mise à jour', 'error');
                event.target.textContent = originalText;
            })
            .finally(() => {
                event.target.disabled = false;
            });
        }
    }

    handleBulkAction(event) {
        event.preventDefault();
        const form = event.target;
        const action = form.querySelector('select[name="bulk_action"]').value;
        const selectedItems = form.querySelectorAll('input[name="selected_items[]"]:checked');

        if (selectedItems.length === 0) {
            this.showNotification('Veuillez sélectionner au moins un élément', 'warning');
            return;
        }

        if (!confirm(`Êtes-vous sûr de vouloir ${this.getActionText(action)} ${selectedItems.length} élément(s) ?`)) {
            return;
        }

        // Show loading
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement...';
        submitBtn.disabled = true;

        // Submit form
        fetch(form.action, {
            method: 'POST',
            body: new FormData(form)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showNotification(`Action réalisée sur ${selectedItems.length} élément(s)`, 'success');
                // Reload the page to reflect changes
                setTimeout(() => location.reload(), 1000);
            } else {
                this.showNotification('Erreur lors de l\'action', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            this.showNotification('Erreur lors de l\'action', 'error');
        })
        .finally(() => {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
    }

    handleExport(event) {
        event.preventDefault();
        const btn = event.target;
        const format = btn.dataset.format;
        
        // Show loading
        const originalText = btn.textContent;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Export...';
        btn.disabled = true;

        // Simulate export
        setTimeout(() => {
            this.showNotification(`Export ${format} généré avec succès`, 'success');
            btn.textContent = originalText;
            btn.disabled = false;
            
            // Simulate download
            this.downloadFile(`export_${new Date().toISOString().split('T')[0]}.${format}`);
        }, 2000);
    }

    refreshStats() {
        const btn = event.target;
        const originalText = btn.textContent;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        btn.disabled = true;

        fetch('api/stats.php?action=refresh')
            .then(response => response.json())
            .then(data => {
                this.updateStatsDisplay(data);
                this.showNotification('Statistiques mises à jour', 'success');
            })
            .catch(error => {
                console.error('Error:', error);
                this.showNotification('Erreur de mise à jour', 'error');
            })
            .finally(() => {
                btn.textContent = originalText;
                btn.disabled = false;
            });
    }

    updateRealTimeStats() {
        fetch('api/stats.php?action=realtime')
            .then(response => response.json())
            .then(data => {
                this.updateStatsDisplay(data);
            })
            .catch(error => {
                console.error('Error updating realtime stats:', error);
            });
    }

    checkNewOrders() {
        fetch('api/orders.php?action=check_new')
            .then(response => response.json())
            .then(data => {
                if (data.new_orders > 0) {
                    this.showNewOrderNotification(data.new_orders);
                }
            })
            .catch(error => {
                console.error('Error checking new orders:', error);
            });
    }

    updateStatsDisplay(stats) {
        // Update stat cards
        if (stats.total_orders) {
            document.querySelector('.stat-card:nth-child(4) h3').textContent = stats.total_orders;
        }
        if (stats.revenue) {
            document.querySelector('.stat-card:nth-child(5) h3').textContent = 
                `${parseFloat(stats.revenue).toLocaleString('fr-FR', {minimumFractionDigits: 2})} €`;
        }
        // Add more stat updates as needed
    }

    showNewOrderNotification(count) {
        if (Notification.permission === 'granted') {
            new Notification('Nouvelles commandes', {
                body: `${count} nouvelle(s) commande(s) en attente`,
                icon: '/favicon.ico'
            });
        } else if (Notification.permission !== 'denied') {
            Notification.requestPermission().then(permission => {
                if (permission === 'granted') {
                    this.showNewOrderNotification(count);
                }
            });
        }

        // Also show in-page notification
        this.showNotification(`${count} nouvelle(s) commande(s) en attente`, 'info');
    }

    showNotification(message, type = 'info') {
        // Remove existing notifications
        document.querySelectorAll('.dashboard-notification').forEach(notif => notif.remove());

        const notification = document.createElement('div');
        notification.className = `dashboard-notification alert alert-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${this.getNotificationIcon(type)}"></i>
                <span>${message}</span>
            </div>
            <button class="notification-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        `;

        // Add styles
        notification.style.cssText = `
            position: fixed;
            top: 100px;
            right: 20px;
            z-index: 10000;
            min-width: 300px;
            max-width: 500px;
            animation: slideInRight 0.3s ease;
        `;

        document.body.appendChild(notification);

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }

    getNotificationIcon(type) {
        const icons = {
            success: 'check-circle',
            error: 'exclamation-circle',
            warning: 'exclamation-triangle',
            info: 'info-circle'
        };
        return icons[type] || 'info-circle';
    }

    getActionText(action) {
        const actions = {
            delete: 'supprimer',
            activate: 'activer',
            deactivate: 'désactiver',
            export: 'exporter'
        };
        return actions[action] || action;
    }

    downloadFile(filename) {
        // Simulate file download
        const link = document.createElement('a');
        link.href = '#'; // In real implementation, this would be the file URL
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.dashboard = new Dashboard();
    
    // Request notification permission
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }
});

// Utility functions for dashboard
const DashboardUtils = {
    formatCurrency(amount) {
        return new Intl.NumberFormat('fr-FR', {
            style: 'currency',
            currency: 'EUR'
        }).format(amount);
    },

    formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('fr-FR', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    },

    formatDateTime(dateString) {
        return new Date(dateString).toLocaleDateString('fr-FR', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    },

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    async fetchData(url, options = {}) {
        try {
            const response = await fetch(url, {
                headers: {
                    'Content-Type': 'application/json',
                    ...options.headers
                },
                ...options
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return await response.json();
        } catch (error) {
            console.error('Fetch error:', error);
            throw error;
        }
    }
};

// Make utils globally available
window.DashboardUtils = DashboardUtils;