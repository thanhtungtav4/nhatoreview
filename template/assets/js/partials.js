(() => {
  const partialSlots = [...document.querySelectorAll("[data-partial]")];

  const loadPartial = async (slot) => {
    const source = slot.dataset.partial;

    if (!source) {
      return;
    }

    try {
      const response = await fetch(source, {
        headers: { Accept: "text/html" },
      });

      if (!response.ok) {
        throw new Error(`Unable to load ${source}`);
      }

      slot.innerHTML = await response.text();
      slot.removeAttribute("data-partial");
    } catch {
      slot.innerHTML = '<p class="partial-error" role="status">Không thể tải thành phần giao diện.</p>';
    }
  };

  const initialize = async () => {
    await Promise.all(partialSlots.map(loadPartial));
    window.__templatePartialsReady = true;
    document.dispatchEvent(new CustomEvent("template:partials-ready"));
  };

  window.__templatePartialsReady = false;

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initialize, { once: true });
  } else {
    initialize();
  }
})();
