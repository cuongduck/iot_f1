// Biến lưu đối tượng biểu đồ
let brixChart = null;

// Hàm cập nhật dữ liệu biểu đồ BRIX
async function updateBrixChart(period = 'today') {
    try {
        const response = await fetch(`api/get_csd_trend.php?period=${period}&type=brix`);
        const data = await response.json();
        
        // Tạo hoặc cập nhật biểu đồ
        renderBrixChart(data);
        
        console.log('Biểu đồ BRIX đã được cập nhật');
    } catch (error) {
        console.error('Lỗi khi cập nhật biểu đồ BRIX:', error);
    }
}

// Hàm tạo và cập nhật biểu đồ BRIX
function renderBrixChart(data) {
    const ctx = document.getElementById('brixChart').getContext('2d');
    
    // Tạo dữ liệu cho đường target
    const targetData = new Array(data.times.length).fill(data.brix.target);
    
    // Tạo dữ liệu giới hạn trên/dưới (±0.5)
    const upperLimitData = new Array(data.times.length).fill(data.brix.target + 0.5);
    const lowerLimitData = new Array(data.times.length).fill(data.brix.target - 0.5);
    
    // Hủy biểu đồ cũ nếu đã tồn tại
    if (brixChart) {
        brixChart.destroy();
    }
    
    // Tạo biểu đồ mới
    brixChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.times,
            datasets: [
                {
                    label: 'BRIX (%)',
                    data: data.brix.values,
                    borderColor: 'rgb(147, 51, 234)',
                    backgroundColor: 'rgba(147, 51, 234, 0.1)',
                    fill: false,
                    tension: 0.4,
                    borderWidth: 2,
                    pointRadius: 3,
                    pointBackgroundColor: 'rgb(147, 51, 234)'
                },
                {
                    label: 'Target',
                    data: targetData,
                    borderColor: 'rgb(59, 130, 246)',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    fill: false,
                    pointRadius: 0
                },
                {
                    label: 'Giới hạn trên',
                    data: upperLimitData,
                    borderColor: 'rgba(239, 68, 68, 0.5)',
                    borderWidth: 1,
                    borderDash: [3, 3],
                    fill: false,
                    pointRadius: 0,
                    hidden: false
                },
                {
                    label: 'Giới hạn dưới',
                    data: lowerLimitData,
                    borderColor: 'rgba(239, 68, 68, 0.5)',
                    borderWidth: 1,
                    borderDash: [3, 3],
                    fill: false,
                    pointRadius: 0,
                    hidden: false
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    align: 'end',
                    labels: {
                        boxWidth: 15,
                        usePointStyle: true,
                        padding: 15
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                // Chỉ hiển thị 1 chữ số thập phân cho giá trị BRIX
                                label += context.parsed.y.toFixed(1) + '%';
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45,
                        font: {
                            size: 10
                        }
                    }
                },
                y: {
                    min: data.brix.min,
                    max: data.brix.max,
                    title: {
                        display: true,
                        text: 'Nồng độ BRIX (%)'
                    },
                    ticks: {
                        stepSize: 0.5,
                        callback: function(value) {
                            return value.toFixed(1) + '%';
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            },
            animations: {
                radius: {
                    duration: 400,
                    easing: 'linear',
                    loop: (context) => context.active
                }
            },
            elements: {
                point: {
                    radius: 3,
                    hoverRadius: 6,
                    hitRadius: 6
                },
                line: {
                    tension: 0.4
                }
            }
        }
    });
}

// Thiết lập lắng nghe sự kiện thay đổi period
function setupBrixChartPeriodListeners() {
    const buttons = document.querySelectorAll('.date-filter .btn');
    buttons.forEach(button => {
        button.addEventListener('click', function() {
            const period = this.dataset.period;
            updateBrixChart(period);
        });
    });
}

// Khởi tạo biểu đồ khi trang tải xong
document.addEventListener('DOMContentLoaded', () => {
    setupBrixChartPeriodListeners();
    updateBrixChart('today');
    
    // Cập nhật biểu đồ mỗi phút
    setInterval(() => {
        const activePeriod = document.querySelector('.date-filter .btn.active').dataset.period;
        updateBrixChart(activePeriod);
    }, 60000);
});