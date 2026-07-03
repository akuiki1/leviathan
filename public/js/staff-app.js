// Generic expand/collapse handler shared by Beranda team cards and PTATK rows.
// Any element with [data-toggle-target="<id>"] toggles the [hidden] attribute
// of the element with that id, and rotates a sibling `.chevron` (if present).
document.addEventListener('click', function (e) {
  const trigger = e.target.closest('[data-toggle-target]');
  if (!trigger) return;

  const target = document.getElementById(trigger.dataset.toggleTarget);
  if (!target) return;

  const nowHidden = !target.hasAttribute('hidden');
  target.toggleAttribute('hidden', nowHidden);
  trigger.setAttribute('aria-expanded', String(!nowHidden));

  const chevron = trigger.querySelector('.chevron');
  if (chevron) chevron.classList.toggle('open', !nowHidden);
});
