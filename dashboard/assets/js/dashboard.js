// Dashboard IoT - JavaScript para atualização em tempo real

class IoTDashboard {
    constructor() {
        this.chart = null;
        this.updateInterval = 3000; // 3 segundos para atualizações mais frequentes
        this.lastUpdate = Date.now();
        this.init();
    }
    
    init() {
        this.initChart();
        this.startRealTimeUpdates();
        this.updateSystemStatus();
    }
    
    initChart() {
        const canvas = document.getElementById('metricsChart');
        if (!canvas) {
            console.error('❌ Elemento metricsChart não encontrado');
            return;
        }
        
        const ctx = canvas.getContext('2d');
        console.log('🎨 Inicializando gráfico...');
        
        this.chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [
                    {
                        label: 'CPU (%)',
                        data: [],
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        tension: 0.4,
                        fill: false
                    },
                    {
                        label: 'Memória (%)',
                        data: [],
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4,
                        fill: false
                    },
                    {
                        label: 'Disco (%)',
                        data: [],
                        borderColor: '#ffc107',
                        backgroundColor: 'rgba(255, 193, 7, 0.1)',
                        tension: 0.4,
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Métricas do Sistema em Tempo Real'
                    }
                },
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Tempo'
                        }
                    },
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Percentual (%)'
                        },
                        beginAtZero: true,
                        max: 100
                    }
                },
                animation: {
                    duration: 750,
                    easing: 'easeInOutQuart'
                }
            }
        });
        
        // Carregar dados iniciais
        this.loadChartData();
        
        console.log('🎯 Gráfico inicializado com sucesso');
    }
    
    async loadChartData() {
        try {
            console.log('🔄 Carregando dados do gráfico...');
            const response = await fetch('api/metrics.php?action=chart&limit=30');
            const result = await response.json();
            
            console.log('📊 Dados recebidos:', result);
            
            if (result.success && result.data && result.data.length > 0) {
                this.updateChart(result.data);
                console.log('✅ Gráfico atualizado com', result.data.length, 'pontos');
            } else {
                console.warn('⚠️ Nenhum dado recebido para o gráfico');
            }
        } catch (error) {
            console.error('❌ Erro ao carregar dados do gráfico:', error);
        }
    }
    
    updateChart(data) {
        console.log('📈 Atualizando gráfico com', data.length, 'pontos');
        
        const labels = [];
        const cpuData = [];
        const memoryData = [];
        const diskData = [];
        
        data.forEach((item, index) => {
            const time = new Date(item.timestamp * 1000).toLocaleTimeString('pt-BR');
            labels.push(time);
            cpuData.push(parseFloat(item.cpu_usage));
            memoryData.push(parseFloat(item.memory_usage));
            diskData.push(parseFloat(item.disk_usage));
            
            console.log(`Ponto ${index + 1}: ${time} - CPU: ${item.cpu_usage}%, RAM: ${item.memory_usage}%, Disco: ${item.disk_usage}%`);
        });
        
        this.chart.data.labels = labels;
        this.chart.data.datasets[0].data = cpuData;
        this.chart.data.datasets[1].data = memoryData;
        this.chart.data.datasets[2].data = diskData;
        
        this.chart.update('none');
        console.log('✅ Gráfico atualizado com sucesso');
    }
    
    async updateMetrics() {
        try {
            const response = await fetch('api/metrics.php?action=latest');
            const result = await response.json();
            
            if (result.success && result.data) {
                const metrics = result.data;
                
                // Atualizar valores principais com animação
                this.animateValue('cpu-usage', parseFloat(metrics.cpu_usage).toFixed(1) + '%');
                this.animateValue('memory-usage', parseFloat(metrics.memory_usage).toFixed(1) + '%');
                this.animateValue('disk-usage', metrics.disk_usage + '%');
                this.animateValue('network-usage', parseFloat(metrics.network_rx_mb).toFixed(1));
                
                // Atualizar barras de progresso
                document.getElementById('cpu-progress').style.width = metrics.cpu_usage + '%';
                document.getElementById('memory-progress').style.width = metrics.memory_usage + '%';
                document.getElementById('disk-progress').style.width = metrics.disk_usage + '%';
                
                // Atualizar informações do sistema
                document.getElementById('uptime').textContent = this.formatUptime(metrics.uptime);
                document.getElementById('load-1min').textContent = parseFloat(metrics.load_1min).toFixed(2);
                document.getElementById('load-5min').textContent = parseFloat(metrics.load_5min).toFixed(2);
                document.getElementById('load-15min').textContent = parseFloat(metrics.load_15min).toFixed(2);
                
                // Atualizar status do sistema
                this.updateSystemStatus(metrics);
                
                // Adicionar dados ao gráfico
                this.addDataPoint(metrics);
                
            }
        } catch (error) {
            console.error('Erro ao atualizar métricas:', error);
        }
    }
    
    addDataPoint(metrics) {
        const time = new Date(metrics.timestamp * 1000).toLocaleTimeString('pt-BR');
        
        // Adicionar novo ponto ao gráfico
        this.chart.data.labels.push(time);
        this.chart.data.datasets[0].data.push(parseFloat(metrics.cpu_usage));
        this.chart.data.datasets[1].data.push(parseFloat(metrics.memory_usage));
        this.chart.data.datasets[2].data.push(parseFloat(metrics.disk_usage));
        
        // Manter apenas os últimos 30 pontos
        if (this.chart.data.labels.length > 30) {
            this.chart.data.labels.shift();
            this.chart.data.datasets.forEach(dataset => dataset.data.shift());
        }
        
        this.chart.update('none');
    }
    
    updateSystemStatus(metrics = null) {
        const statusContainer = document.getElementById('system-status');
        
        if (!metrics) {
            // Status inicial
            return;
        }
        
        const statuses = [
            {
                name: 'CPU',
                value: parseFloat(metrics.cpu_usage),
                warning: 70,
                critical: 90
            },
            {
                name: 'Memória',
                value: parseFloat(metrics.memory_usage),
                warning: 80,
                critical: 95
            },
            {
                name: 'Disco',
                value: parseFloat(metrics.disk_usage),
                warning: 85,
                critical: 95
            },
            {
                name: 'Rede',
                value: parseFloat(metrics.network_rx_mb),
                warning: 100,
                critical: 500
            }
        ];
        
        let statusHtml = '';
        
        statuses.forEach(status => {
            let statusClass = 'status-ok';
            let statusText = 'Normal';
            
            if (status.value >= status.critical) {
                statusClass = 'status-critical';
                statusText = 'Crítico';
            } else if (status.value >= status.warning) {
                statusClass = 'status-warning';
                statusText = 'Atenção';
            }
            
            statusHtml += `
                <div class="mb-2">
                    <span class="status-indicator ${statusClass}"></span>
                    <span>${status.name}: ${statusText}</span>
                </div>
            `;
        });
        
        statusContainer.innerHTML = statusHtml;
    }
    
    formatUptime(seconds) {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = Math.floor(seconds % 60);
        
        return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }
    
    startRealTimeUpdates() {
        // Atualização inicial
        this.updateMetrics();
        
        // Atualização periódica mais frequente
        setInterval(() => {
            this.updateMetrics();
        }, this.updateInterval);
        
        // Atualizar dados do gráfico a cada 15 segundos
        setInterval(() => {
            this.loadChartData();
        }, 15000);
        
        // Indicador de atividade
        this.showActivityIndicator();
    }
    
    showActivityIndicator() {
        const indicator = document.createElement('div');
        indicator.id = 'activity-indicator';
        indicator.style.cssText = 'position: fixed; bottom: 20px; right: 20px; background: #28a745; color: white; padding: 8px 12px; border-radius: 20px; font-size: 12px; z-index: 9999; display: none;';
        indicator.innerHTML = '🔄 Atualizando...';
        document.body.appendChild(indicator);
        
        // Mostrar indicador durante atualizações
        setInterval(() => {
            indicator.style.display = 'block';
            setTimeout(() => {
                indicator.style.display = 'none';
            }, 1000);
        }, this.updateInterval);
    }
    
    animateValue(elementId, newValue) {
        const element = document.getElementById(elementId);
        if (!element) return;
        
        const oldValue = element.textContent;
        if (oldValue === newValue) return;
        
        // Adicionar classe de animação
        element.style.transition = 'all 0.3s ease';
        element.style.transform = 'scale(1.1)';
        element.style.color = '#ff6b6b';
        
        // Atualizar valor
        element.textContent = newValue;
        
        // Remover animação após 300ms
        setTimeout(() => {
            element.style.transform = 'scale(1)';
            element.style.color = '';
        }, 300);
    }
}

// Inicializar dashboard quando a página carregar
document.addEventListener('DOMContentLoaded', () => {
    new IoTDashboard();
});

// Função para mostrar notificações
function showNotification(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    toast.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(toast);
    
    // Remover automaticamente após 5 segundos
    setTimeout(() => {
        if (toast.parentNode) {
            toast.parentNode.removeChild(toast);
        }
    }, 5000);
}
