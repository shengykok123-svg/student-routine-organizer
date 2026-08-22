// Renders the administrator dashboard's aggregate chart datasets.
document.addEventListener("DOMContentLoaded", () => {
  const chartRoot = document.getElementById("adminCharts");
  if (!chartRoot || typeof Chart === "undefined") return;

  const charts = JSON.parse(chartRoot.dataset.chart || "{}");
  const colors = ["#6547e8", "#3182ce", "#28a96b", "#f5a623"];
  const baseOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { position: "bottom" } },
  };

  const moduleUsage = document.getElementById("adminModuleUsageChart");
  if (moduleUsage) {
    new Chart(moduleUsage, {
      type: "bar",
      data: {
        labels: charts.module_usage.labels,
        datasets: [
          {
            label: "Records",
            data: charts.module_usage.values,
            backgroundColor: colors,
            borderRadius: 7,
            maxBarThickness: 48,
          },
        ],
      },
      options: {
        ...baseOptions,
        plugins: { legend: { display: false } },
        scales: {
          x: { grid: { display: false } },
          y: { beginAtZero: true, ticks: { precision: 0 } },
        },
      },
    });
  }

  const userStatus = document.getElementById("adminUserStatusChart");
  if (userStatus) {
    new Chart(userStatus, {
      type: "doughnut",
      data: {
        labels: charts.user_status.labels,
        datasets: [
          {
            data: charts.user_status.values,
            backgroundColor: ["#28a96b", "#e84a5f", "#6547e8", "#f5a623"],
            borderWidth: 0,
          },
        ],
      },
      options: { ...baseOptions, cutout: "62%" },
    });
  }

  const activityTrend = document.getElementById("adminActivityTrendChart");
  if (activityTrend) {
    const datasets = Object.entries(charts.activity_trend.datasets).map(
      ([label, data], index) => ({
        label,
        data,
        borderColor: colors[index],
        backgroundColor: colors[index],
        tension: 0.35,
        pointRadius: 3,
        fill: false,
      }),
    );
    new Chart(activityTrend, {
      type: "line",
      data: { labels: charts.activity_trend.labels, datasets },
      options: {
        ...baseOptions,
        scales: {
          x: { grid: { display: false } },
          y: { beginAtZero: true, ticks: { precision: 0 } },
        },
      },
    });
  }
});
