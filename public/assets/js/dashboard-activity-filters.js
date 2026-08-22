// Controls the Activity Overview chart datasets.
document.addEventListener("DOMContentLoaded", () => {
  const filters = document.getElementById("activityChartFilters");
  const canvas = document.getElementById("dashboardChart");
  if (!filters || !canvas || typeof Chart === "undefined") return;

  const chart = Chart.getChart(canvas);
  const allInput = filters.querySelector("[data-select-all]");
  const seriesInputs = [...filters.querySelectorAll("[data-dataset-index]")];
  if (!chart || !allInput || seriesInputs.length === 0) return;

  const syncAll = () => {
    allInput.checked = seriesInputs.every((input) => input.checked);
    allInput.indeterminate =
      !allInput.checked && seriesInputs.some((input) => input.checked);
  };
  const updateSeries = (input) => {
    chart.setDatasetVisibility(
      Number(input.dataset.datasetIndex),
      input.checked,
    );
    chart.update();
    syncAll();
  };

  allInput.addEventListener("change", () => {
    seriesInputs.forEach((input) => {
      input.checked = allInput.checked;
      chart.setDatasetVisibility(
        Number(input.dataset.datasetIndex),
        input.checked,
      );
    });
    allInput.indeterminate = false;
    chart.update();
  });
  seriesInputs.forEach((input) =>
    input.addEventListener("change", () => updateSeries(input)),
  );
});
