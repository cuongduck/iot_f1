// Biến lưu đối tượng biểu đồ
let co2Chart = null;

// Hàm cập nhật dữ liệu biểu đồ CO2
async function updateCO2Chart(period = 'today') {
    try {
        const response = await fetch(`api/get_csd_trend.php?period=${period}&type=co2`);
        const data = await response.json();
        
        // Tạo hoặc cập nhật biểu đồ
        renderCO2Chart(data);
        
        console.log('Biểu đồ CO2 đã được cập nhật');
    } catch (error) {
        console.error('Lỗi khi cập nhật biểu đồ CO2:', error);
    }
}

// Hàm tạo và cập nhật biểu đồ CO2
function renderCO2Chart(data) {
    const ctx = document.getElementById('co2Chart').getContext('2d');
    
    // Tạo dữ liệu cho đường target
    const targetData = new Array(data.times.length).fill(data.co2.target);
    
    // Tạo dữ liệu giới hạn trên/dưới (±0.3)
    const upperLimitData = new Array(data.times.length).fill(data.co2.target + 0.3);
    const lowerLimitData = new Array(data.times.length).fill(data.co2.target - 0.3);
    
    // Hủy biểu đồ cũ nếu đã tồn tại
    if (co2Chart) {
        co2Chart.destroy();
    }
    
    // Tạo biểu đồ mới
    co2Chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.times,
            datasets: [
                {
                    label: 'CO2 (g/l)',
                    data: data.co2.values,
                    borderColor: 'rgb(16, 185, 129)',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: false,
                    tension: 0.4,
                    borderWidth: 2,
                    pointRadius: 3,
                    pointBackgroundColor: 'rgb(16, 185, 129)'
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
                                // Chỉ hiển thị 2 chữ số thập phân cho giá trị CO2
                                label += context.parsed.y.toFixed(2);
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
                    min: data.co2.min,
                    max: data.co2.max,
                    title: {
                        display: true,
                        text: 'Nồng độ CO2 (g/l)'
                    },
                    ticks: {
                        stepSize: 0.2,
                        callback: function(value) {
                            return value.toFixed(1);
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
function setupCO2ChartPeriodListeners() {
    const buttons = document.querySelectorAll('.date-filter .btn');
    buttons.forEach(button => {
        button.addEventListener('click', function() {
            const period = this.dataset.period;
            updateCO2Chart(period);
        });
    });
}

// Khởi tạo biểu đồ khi trang tải xong
document.addEventListener('DOMContentLoaded', () => {
    setupCO2ChartPeriodListeners();
    updateCO2Chart('today');
    
    // Cập nhật biểu đồ mỗi phút
    setInterval(() => {
        const activePeriod = document.querySelector('.date-filter .btn.active').dataset.period;
        updateCO2Chart(activePeriod);
    }, 60000);
});