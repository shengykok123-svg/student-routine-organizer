document.addEventListener('DOMContentLoaded', () => {
    const nav = document.querySelector('.landing-nav');
    const links = [...document.querySelectorAll('[data-landing-nav-link]')];
    if (!nav || links.length === 0) return;

    const sections = links
        .map(link => ({ link, section: document.querySelector(link.getAttribute('href')) }))
        .filter(item => item.section);
    if (sections.length === 0) return;

    const setActive = active => {
        sections.forEach(item => {
            const selected = item === active;
            item.link.classList.toggle('is-active', selected);
            if (selected) item.link.setAttribute('aria-current', 'page');
            else item.link.removeAttribute('aria-current');
        });
    };
    const updateActive = () => {
        const activationLine = nav.getBoundingClientRect().height + 28;
        let active = sections[0];
        sections.forEach(item => {
            if (item.section.getBoundingClientRect().top <= activationLine) active = item;
        });
        if (window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 2) active = sections.at(-1);
        setActive(active);
    };

    let frame;
    window.addEventListener('scroll', () => {
        window.cancelAnimationFrame(frame);
        frame = window.requestAnimationFrame(updateActive);
    }, { passive: true });
    window.addEventListener('resize', updateActive);
    links.forEach(link => link.addEventListener('click', () => setActive(sections.find(item => item.link === link) || sections[0])));
    updateActive();
});
