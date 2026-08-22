// Keeps category options aligned with the selected transaction type.
document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("moneyForm");
  const type = document.getElementById("transaction_type");
  const category = document.getElementById("category");
  if (!form || !type || !category) return;

  const categories = JSON.parse(form.dataset.categories || "{}");
  type.addEventListener("change", () => {
    const options = categories[type.value] || [];
    category.replaceChildren(...options.map((name) => new Option(name, name)));
  });
});
