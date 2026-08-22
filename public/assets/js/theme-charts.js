// Updates existing Chart.js canvases when the interface theme changes.
document.addEventListener("DOMContentLoaded", () => {
  const applyChartTheme = () => {
    if (typeof Chart === "undefined" || !Chart.instances) return;
    const styles = getComputedStyle(document.documentElement);
    const text = styles.getPropertyValue("--sro-text").trim();
    const muted = styles.getPropertyValue("--sro-muted").trim();
    const border = styles.getPropertyValue("--sro-border").trim();

    Object.values(Chart.instances).forEach((chart) => {
      chart.options.color = muted;
      chart.options.plugins ??= {};
      chart.options.plugins.legend ??= {};
      chart.options.plugins.legend.labels ??= {};
      chart.options.plugins.legend.labels.color = text;
      chart.options.plugins.tooltip ??= {};
      chart.options.plugins.tooltip.backgroundColor = styles
        .getPropertyValue("--sro-surface")
        .trim();
      chart.options.plugins.tooltip.titleColor = text;
      chart.options.plugins.tooltip.bodyColor = muted;
      Object.values(chart.options.scales ?? {}).forEach((scale) => {
        scale.ticks ??= {};
        scale.grid ??= {};
        scale.ticks.color = muted;
        scale.grid.color = border;
      });
      chart.update();
    });
  };

  applyChartTheme();
  document.addEventListener("sro:themechange", applyChartTheme);
});
