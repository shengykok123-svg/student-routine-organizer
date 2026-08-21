document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('categoryChart');
    if (!canvas || typeof Chart === 'undefined') return;
    const card = canvas.closest('.content-card');
    const chart = Chart.getChart(canvas);
    if (!card || !chart) return;

    card.classList.add('expense-category-card');
    const title = card.querySelector('h2');
    const kicker = card.querySelector('.card-kicker');
    if (title) title.textContent = 'Spending by Category';
    if (kicker) kicker.textContent = 'Expense analysis';

    const total = chart.data.datasets[0].data.reduce((sum, amount) => sum + Number(amount || 0), 0);
    const summary = document.createElement('p');
    summary.className = 'spending-summary';
    summary.textContent = `Total expenses: RM ${total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
    title?.insertAdjacentElement('afterend', summary);

    const trackerLink = document.createElement('a');
    trackerLink.className = 'small expense-category-link';
    trackerLink.href = window.location.pathname.replace(/\/dashboard\/?$/, '/money');
    trackerLink.textContent = 'View all';
    card.appendChild(trackerLink);

    chart.data.labels = chart.data.labels.map((label, index) => `${label} · RM ${Number(chart.data.datasets[0].data[index] || 0).toFixed(2)}`);
    chart.update();
});
