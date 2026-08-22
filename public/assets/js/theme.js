// Applies Light, Dark, and System preferences without reloading the page.
document.addEventListener("DOMContentLoaded", () => {
  const root = document.documentElement;
  const body = document.body;
  const choices = document.querySelectorAll("[data-theme-choice]");

  const resolvedTheme = (preference) =>
    preference === "system"
      ? window.matchMedia("(prefers-color-scheme: dark)").matches
        ? "dark"
        : "light"
      : preference;

  const applyTheme = (preference) => {
    root.dataset.themePreference = preference;
    root.dataset.theme = resolvedTheme(preference);
    localStorage.setItem("sro-theme-preference", preference);
    document.dispatchEvent(new CustomEvent("sro:themechange"));
  };

  choices.forEach((choice) => {
    choice.addEventListener("click", () => {
      const preference = choice.dataset.themeChoice;
      if (!preference) return;
      applyTheme(preference);

      if (body.dataset.themeEndpoint) {
        fetch(body.dataset.themeEndpoint, {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: new URLSearchParams({
            _csrf: body.dataset.themeCsrf || "",
            theme: preference,
          }),
        }).catch(() => undefined);
      }
    });
  });

  window
    .matchMedia("(prefers-color-scheme: dark)")
    .addEventListener("change", () => {
      if (root.dataset.themePreference === "system") {
        applyTheme("system");
      }
    });
});
