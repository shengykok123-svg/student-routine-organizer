// Manages Money Tracker filtering, charts, and small UI refinements.
document.addEventListener("DOMContentLoaded", () => {
  if (!/\/money\/?$/.test(window.location.pathname)) return;

  const subtitle = document.querySelector(".page-heading .page-subtitle");
  if (subtitle)
    subtitle.textContent = "Keep your income, spending, and balance in view.";

  const filter = document.getElementById("moneyFilter");
  const range = document.getElementById("range");
  const customRange = document.getElementById("customDateRange");
  const startDate = document.getElementById("start_date");
  const endDate = document.getElementById("end_date");
  const search = document.getElementById("search");
  let searchTimer;
  if (filter && range && customRange && startDate && endDate && search) {
    ["type", "category"].forEach((id) =>
      document
        .getElementById(id)
        ?.addEventListener("change", () => filter.requestSubmit()),
    );
    range.addEventListener("change", () => {
      customRange.classList.toggle("d-none", range.value !== "custom");
      if (range.value !== "custom") filter.requestSubmit();
    });
    [startDate, endDate].forEach((input) =>
      input.addEventListener("change", () => {
        if (startDate.value && endDate.value) filter.requestSubmit();
      }),
    );
    search.addEventListener("input", () => {
      clearTimeout(searchTimer);
      searchTimer = window.setTimeout(() => filter.requestSubmit(), 450);
    });
  }

  const chartRoot = document.getElementById("moneyCharts");
  if (!chartRoot || typeof Chart === "undefined") return;
  const chartData = JSON.parse(
    chartRoot.dataset.chart || '{"labels":[],"income":[],"expense":[]}',
  );
  const palette = [
    "#6547e8",
    "#3182ce",
    "#28a96b",
    "#f5a623",
    "#e84a5f",
    "#7c6ea8",
    "#5d9caa",
    "#d16f94",
    "#4b7bd1",
  ];
  const currency = (value) =>
    `RM ${Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

  const categoryCanvas = document.getElementById("moneyCategoryChart");
  if (categoryCanvas) {
    new Chart(categoryCanvas, {
      type: "pie",
      data: {
        labels: chartData.labels,
        datasets: [
          {
            data: chartData.expense,
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
        labels: chartData.labels,
        datasets: [
          {
            label: "Income",
            data: chartData.income,
            backgroundColor: "#28a96b",
            borderRadius: 6,
            maxBarThickness: 44,
          },
          {
            label: "Expenses",
            data: chartData.expense,
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
          x: { stacked: true, grid: { display: false } },
          y: {
            stacked: true,
            beginAtZero: true,
            ticks: { callback: (value) => `RM ${value}` },
            grid: { color: "#edf0f5" },
          },
        },
      },
    });
  }
});
