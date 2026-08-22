// Controls Money Tracker filters, quick actions, and financial charts.
document.addEventListener("DOMContentLoaded", () => {
  if (!/\/money\/?$/.test(window.location.pathname)) return;

  const filter = document.getElementById("moneyFilter");
  const range = document.getElementById("range");
  const type = document.getElementById("type");
  const category = document.getElementById("category");
  const customRange = document.getElementById("customDateRange");
  const startDate = document.getElementById("start_date");
  const endDate = document.getElementById("end_date");
  const search = document.getElementById("search");
  let searchTimer;

  if (
    filter &&
    range &&
    type &&
    category &&
    customRange &&
    startDate &&
    endDate
  ) {
    [type, category].forEach((input) =>
      input.addEventListener("change", () => filter.requestSubmit()),
    );
    range.addEventListener("change", () => {
      const isCustom = range.value === "custom";
      customRange.classList.toggle("d-none", !isCustom);
      if (!isCustom) filter.requestSubmit();
    });
    [startDate, endDate].forEach((input) =>
      input.addEventListener("change", () => {
        if (startDate.value && endDate.value) filter.requestSubmit();
      }),
    );
    search?.addEventListener("input", () => {
      clearTimeout(searchTimer);
      searchTimer = window.setTimeout(() => filter.requestSubmit(), 450);
    });

    document.querySelectorAll("[data-money-type]").forEach((button) => {
      button.addEventListener("click", () => {
        type.value = button.dataset.moneyType || "";
        filter.requestSubmit();
      });
    });
    document.querySelectorAll("[data-money-range]").forEach((button) => {
      button.addEventListener("click", () => {
        range.value = button.dataset.moneyRange || "month";
        filter.requestSubmit();
      });
    });
  }

  const chartRoot = document.getElementById("moneyCharts");
  if (!chartRoot || typeof Chart === "undefined") return;

  const chartData = JSON.parse(chartRoot.dataset.chart || "{}");
  const palette = [
    "#6547e8",
    "#3182ce",
    "#28a96b",
    "#f5a623",
    "#e84a5f",
    "#7c6ea8",
    "#5d9caa",
  ];
  const currency = (value) =>
    `RM ${Number(value || 0).toLocaleString(undefined, {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    })}`;

  const cashFlowCanvas = document.getElementById("moneyCashFlowChart");
  if (cashFlowCanvas) {
    new Chart(cashFlowCanvas, {
      data: {
        labels: chartData.cashFlow.labels,
        datasets: [
          {
            type: "bar",
            label: "Income",
            data: chartData.cashFlow.income,
            backgroundColor: "#28a96b",
            borderRadius: 6,
            maxBarThickness: 34,
          },
          {
            type: "bar",
            label: "Expenses",
            data: chartData.cashFlow.expense,
            backgroundColor: "#e84a5f",
            borderRadius: 6,
            maxBarThickness: 34,
          },
          {
            type: "line",
            label: "Net cash flow",
            data: chartData.cashFlow.net,
            borderColor: "#6547e8",
            backgroundColor: "#6547e8",
            tension: 0.35,
            pointRadius: 3,
            borderWidth: 3,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "top",
            align: "end",
            labels: { usePointStyle: true },
          },
          tooltip: {
            callbacks: {
              label: (context) =>
                `${context.dataset.label}: ${currency(context.raw)}`,
            },
          },
        },
        scales: {
          x: { grid: { display: false } },
          y: {
            ticks: { callback: (value) => `RM ${value}` },
            grid: { color: "#edf0f5" },
          },
        },
      },
    });
  }

  const categoryCanvas = document.getElementById("moneyCategoryChart");
  if (categoryCanvas) {
    new Chart(categoryCanvas, {
      type: "doughnut",
      data: {
        labels: chartData.category.expenseLabels,
        datasets: [
          {
            data: chartData.category.expenseValues,
            backgroundColor: palette,
            borderColor: "#ffffff",
            borderWidth: 3,
          },
        ],
      },
      options: {
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "bottom",
            labels: { usePointStyle: true, padding: 15 },
          },
          tooltip: {
            callbacks: {
              label: (context) => `${context.label}: ${currency(context.raw)}`,
            },
          },
        },
      },
    });
  }

  const comparisonCanvas = document.getElementById("moneyComparisonChart");
  if (comparisonCanvas) {
    new Chart(comparisonCanvas, {
      type: "bar",
      data: {
        labels: chartData.category.labels,
        datasets: [
          {
            label: "Income",
            data: chartData.category.income,
            backgroundColor: "#28a96b",
            borderRadius: 6,
            maxBarThickness: 44,
          },
          {
            label: "Expenses",
            data: chartData.category.expense,
            backgroundColor: "#e84a5f",
            borderRadius: 6,
            maxBarThickness: 44,
          },
        ],
      },
      options: {
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "top",
            align: "end",
            labels: { usePointStyle: true },
          },
          tooltip: {
            callbacks: {
              label: (context) =>
                `${context.dataset.label}: ${currency(context.raw)}`,
            },
          },
        },
        scales: {
          x: { grid: { display: false } },
          y: {
            beginAtZero: true,
            ticks: { callback: (value) => `RM ${value}` },
            grid: { color: "#edf0f5" },
          },
        },
      },
    });
  }
});
