/**
 * PPMMIS Dashboard JavaScript - Complete Version
 */

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard JS loaded');
    
    // Initialize chart if it exists
    initChart();
    
    // Initialize search functionality
    initSearch();
});

/**
 * Initialize Chart.js
 */
function initChart() {
    const chartCanvas = document.getElementById('requestsChart');
    
    if (!chartCanvas) {
        console.log('Chart canvas not found - skipping chart initialization');
        return;
    }
    
    // Check if Chart is available
    if (typeof Chart === 'undefined') {
        console.error('Chart.js library not loaded');
        return;
    }
    
    try {
        const ctx = chartCanvas.getContext('2d');
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Maintenance Requests',
                    data: [12, 19, 15, 17, 22, 24, 28, 26, 30, 32, 35, 38],
                    backgroundColor: 'rgba(22, 163, 74, 0.8)',
                    borderColor: 'rgb(22, 163, 74)',
                    borderWidth: 1,
                    borderRadius: 8,
                    hoverBackgroundColor: 'rgba(22, 163, 74, 1)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: {
                                family: 'Inter',
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: '#1f2937',
                        titleFont: {
                            family: 'Inter',
                            size: 13
                        },
                        bodyFont: {
                            family: 'Inter',
                            size: 12
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#e5e7eb'
                        },
                        ticks: {
                            stepSize: 5,
                            font: {
                                family: 'Inter'
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                family: 'Inter'
                            }
                        }
                    }
                }
            }
        });
        
        console.log('Chart initialized successfully');
    } catch (error) {
        console.error('Error initializing chart:', error);
    }
}

/**
 * Initialize search functionality
 */
function initSearch() {
    const searchInput = document.getElementById('tableSearch');
    
    if (!searchInput) {
        console.log('Search input not found');
        return;
    }
    
    // Add event listener for search
    searchInput.addEventListener('keyup', function() {
        filterTable();
    });
    
    console.log('Search functionality initialized');
}

/**
 * Filter table rows based on search input
 */
function filterTable() {
    const input = document.getElementById('tableSearch');
    if (!input) return;
    
    const filter = input.value.toLowerCase().trim();
    const table = document.querySelector('.data-table table');
    
    if (!table) {
        console.log('Table not found');
        return;
    }
    
    const rows = table.getElementsByTagName('tr');
    let visibleCount = 0;
    
    for (let i = 1; i < rows.length; i++) { // Start from 1 to skip header
        const cells = rows[i].getElementsByTagName('td');
        let found = false;
        
        for (let j = 0; j < cells.length; j++) {
            const cellText = cells[j].textContent || cells[j].innerText;
            if (cellText.toLowerCase().indexOf(filter) > -1) {
                found = true;
                break;
            }
        }
        
        rows[i].style.display = found ? '' : 'none';
        if (found) visibleCount++;
    }
    
    // Show/hide "no results" message
    const noResultsMsg = document.getElementById('noResultsMessage');
    if (visibleCount === 0 && rows.length > 1) {
        if (!noResultsMsg) {
            const tbody = table.querySelector('tbody') || table;
            const tr = document.createElement('tr');
            tr.id = 'noResultsMessage';
            const td = document.createElement('td');
            td.colSpan = document.querySelectorAll('.data-table th').length || 5;
            td.textContent = 'No matching requests found';
            td.className = 'text-center';
            td.style.padding = '2rem';
            td.style.color = '#6b7280';
            tr.appendChild(td);
            tbody.appendChild(tr);
        }
    } else if (noResultsMsg) {
        noResultsMsg.remove();
    }
}

/**
 * Fetch and update dashboard statistics via AJAX
 */
async function refreshStats() {
    try {
        const response = await fetch('/api/dashboard/stats', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });
        
        if (!response.ok) throw new Error('Network response was not ok');
        
        const data = await response.json();
        
        if (data.success) {
            updateStatCards(data.data);
        }
    } catch (error) {
        console.log('Error refreshing stats:', error);
    }
}

/**
 * Update stat cards with new values
 */
function updateStatCards(stats) {
    // For admin dashboard
    if (stats.totalRequests !== undefined) {
        const totalCard = document.querySelector('.stat-card-green .stat-value');
        const pendingCard = document.querySelector('.stat-card-yellow .stat-value');
        const completedCard = document.querySelector('.stat-card-blue .stat-value');
        const personnelCard = document.querySelector('.stat-card-indigo .stat-value');
        
        if (totalCard) totalCard.textContent = stats.totalRequests;
        if (pendingCard) pendingCard.textContent = stats.pendingRequests;
        if (completedCard) completedCard.textContent = stats.completedRequests;
        if (personnelCard && stats.activePersonnel) personnelCard.textContent = stats.activePersonnel;
    }
    // For user dashboard
    else if (stats.myRequests !== undefined) {
        const cards = document.querySelectorAll('.stat-card');
        if (cards[0]) cards[0].querySelector('.stat-value').textContent = stats.myRequests;
        if (cards[1]) cards[1].querySelector('.stat-value').textContent = stats.myPendingRequests;
        if (cards[2]) cards[2].querySelector('.stat-value').textContent = stats.myCompletedRequests;
    }
}

// Make filterTable available globally
window.filterTable = filterTable;

// Optional: Auto-refresh stats every 30 seconds
// setInterval(refreshStats, 30000);