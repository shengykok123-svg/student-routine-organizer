// Applies Light, Dark, and System preferences without reloading the page.
document.addEventListener("DOMContentLoaded", () => {
  const root = document.documentElement;
  const body = document.body;
  const choices = document.querySelectorAll("[data-theme-choice]");
  const themeToggle = document.querySelector("[data-theme-toggle]");

  const resolvedTheme = (preference) =>
    preference === "system"
      ? window.matchMedia("(prefers-color-scheme: dark)").matches
        ? "dark"
        : "light"
      : preference;

  const savePreference = (preference) => {
    if (!body.dataset.themeEndpoint) return;

    fetch(body.dataset.themeEndpoint, {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: new URLSearchParams({
        _csrf: body.dataset.themeCsrf || "",
        theme: preference,
      }),
    }).catch(() => undefined);
  };

  const updateThemeToggle = () => {
    if (!themeToggle) return;

    const isDark = root.dataset.theme === "dark";
    themeToggle.setAttribute("aria-pressed", String(isDark));
    themeToggle.setAttribute(
      "aria-label",
      isDark ? "Switch to light theme" : "Switch to dark theme",
    );
  };

  const applyTheme = (preference) => {
    root.dataset.themePreference = preference;
    root.dataset.theme = resolvedTheme(preference);
    localStorage.setItem("sro-theme-preference", preference);
    updateThemeToggle();
    document.dispatchEvent(new CustomEvent("sro:themechange"));
  };

  choices.forEach((choice) => {
    choice.addEventListener("click", () => {
      const preference = choice.dataset.themeChoice;
      if (!preference) return;
      applyTheme(preference);
      savePreference(preference);
    });
  });

  themeToggle?.addEventListener("click", () => {
    const preference = root.dataset.theme === "dark" ? "light" : "dark";
    applyTheme(preference);
    savePreference(preference);
  });

  updateThemeToggle();

  window
    .matchMedia("(prefers-color-scheme: dark)")
    .addEventListener("change", () => {
      if (root.dataset.themePreference === "system") {
        applyTheme("system");
      }
    });
});
