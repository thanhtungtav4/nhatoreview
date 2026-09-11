(() => {
  let initialized = false;

  const initializeHeader = () => {
    const header = document.querySelector("[data-site-navigation]")?.closest(".site-header");
    const navigation = document.querySelector("[data-site-navigation]");
    const toggle = document.querySelector("[data-menu-toggle]");
    const backdrop = document.querySelector("[data-menu-backdrop]");

    if (!header || !navigation || !toggle || !backdrop) {
      return;
    }

    const mobileQuery = window.matchMedia("(max-width: 900px)");

    const focusableSelector = "a[href], button:not([disabled]), [tabindex]:not([tabindex='-1'])";

    const focusFirstMenuItem = () => {
      window.setTimeout(() => {
        navigation.querySelector(focusableSelector)?.focus();
      }, 120);
    };

    const setMenuState = (isOpen, { focusFirst = false, returnFocus = false } = {}) => {
      const open = Boolean(isOpen) && mobileQuery.matches;
      navigation.classList.toggle("is-open", open);
      header.classList.toggle("is-menu-open", open);
      toggle.setAttribute("aria-expanded", String(open));
      toggle.setAttribute("aria-label", open ? "Đóng menu" : "Mở menu");
      navigation.setAttribute("aria-hidden", String(mobileQuery.matches && !open));
      document.body.classList.toggle("menu-is-open", open);

      if (open && focusFirst) {
        focusFirstMenuItem();
      }

      if (!open && returnFocus) {
        window.setTimeout(() => {
          toggle.focus();
        }, 40);
      }
    };

    const updateScrollState = () => {
      header.classList.toggle("is-scrolled", window.scrollY > 16);
    };

    toggle.addEventListener("click", () => {
      setMenuState(toggle.getAttribute("aria-expanded") !== "true", { focusFirst: true });
    });

    backdrop.addEventListener("click", () => {
      setMenuState(false, { returnFocus: true });
    });

    backdrop.addEventListener("mousedown", (event) => {
      event.preventDefault();
    });

    navigation.addEventListener("click", (event) => {
      if (event.target.closest("a")) {
        setMenuState(false);
      }
    });

    document.addEventListener("keydown", (event) => {
      const isOpen = toggle.getAttribute("aria-expanded") === "true";

      if (event.key === "Escape" && isOpen) {
        setMenuState(false, { returnFocus: true });
        return;
      }

      if (event.key !== "Tab" || !isOpen) {
        return;
      }

      const focusableElements = [...navigation.querySelectorAll(focusableSelector)].filter((element) => element.offsetParent !== null);
      const firstElement = focusableElements[0];
      const lastElement = focusableElements[focusableElements.length - 1];

      if (!firstElement || !lastElement) {
        return;
      }

      if (event.shiftKey && document.activeElement === firstElement) {
        event.preventDefault();
        lastElement.focus();
      } else if (!event.shiftKey && document.activeElement === lastElement) {
        event.preventDefault();
        firstElement.focus();
      }
    });

    const handleViewportChange = () => {
      setMenuState(false);
    };

    if (typeof mobileQuery.addEventListener === "function") {
      mobileQuery.addEventListener("change", handleViewportChange);
    } else {
      mobileQuery.addListener(handleViewportChange);
    }

    window.addEventListener("scroll", updateScrollState, { passive: true });
    updateScrollState();
    setMenuState(false);

    const currentPage = document.body.dataset.page;
    document.querySelectorAll("[data-nav-link]").forEach((link) => {
      if (link.dataset.page === currentPage) {
        link.setAttribute("aria-current", "page");
      }
    });
  };

  const initializeNewsletter = () => {
    const form = document.querySelector("[data-newsletter-form]");
    const input = form?.querySelector("input[type='email']");
    const status = form?.parentElement?.querySelector("[data-newsletter-status]");

    if (!form || !input || !status) {
      return;
    }

    form.addEventListener("submit", (event) => {
      event.preventDefault();

      if (!input.value.trim() || !input.validity.valid) {
        status.textContent = "Vui lòng nhập địa chỉ email hợp lệ.";
        input.focus();
        return;
      }

      status.textContent = "Cảm ơn bạn đã đăng ký nhận tin từ NHATO.";
      form.reset();
    });
  };

  const initializeYear = () => {
    document.querySelectorAll("[data-current-year]").forEach((element) => {
      element.textContent = String(new Date().getFullYear());
    });
  };

  const initialize = () => {
    if (initialized) {
      return;
    }

    initialized = true;
    initializeHeader();
    initializeNewsletter();
    initializeYear();
  };

  document.addEventListener("template:partials-ready", initialize, { once: true });

  if (window.__templatePartialsReady) {
    initialize();
  }
})();
