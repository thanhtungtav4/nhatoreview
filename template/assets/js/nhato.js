/* NHATO Collection — the small amount of behaviour the site needs.
   Ported from thanhtungtav4/nhatoreview template/assets/js/common.js. */
(function () {
  function onReady(fn) {
    if (document.readyState !== "loading") fn();
    else document.addEventListener("DOMContentLoaded", fn);
  }

  /* Everything is delegated from the document: in a Design Component the
     React pass can replace the header after DOMContentLoaded, so a listener
     bound to a node found at ready time lands on a stale element. */
  function navEls() {
    return {
      nav: document.querySelector("[data-nh-nav]"),
      toggle: document.querySelector("[data-nh-menu-toggle]")
    };
  }

  function closeNav() {
    var el = navEls();
    if (!el.nav) return;
    el.nav.classList.remove("is-open");
    if (el.toggle) el.toggle.setAttribute("aria-expanded", "false");
  }

  function filterPortfolio(tab) {
    var tabList = tab.closest("[role=\"tablist\"]");
    var section = tabList && tabList.closest("section");
    var grid = section && section.querySelector("[data-portfolio-grid]");
    var mosaic = section && section.querySelector("[data-portfolio-mosaic]");
    if (!tabList || !grid) return false;

    var term = tab.getAttribute("data-portfolio-term") || "";
    tabList.querySelectorAll("[data-portfolio-term]").forEach(function (item) {
      item.setAttribute("aria-selected", item === tab ? "true" : "false");
    });

    if (mosaic) {
      mosaic.hidden = term !== "";
      grid.hidden = term === "";
    }

    grid.querySelectorAll("[data-portfolio-terms]").forEach(function (card) {
      var terms = (card.getAttribute("data-portfolio-terms") || "").split(/\s+/);
      card.hidden = term !== "" && terms.indexOf(term) === -1;
    });

    return true;
  }

  function bindOnce() {
    if (window.__nhBound) return;
    window.__nhBound = true;

    document.addEventListener("click", function (e) {
      var t = e.target.closest && e.target.closest("[data-nh-menu-toggle]");
      if (t) {
        var nav = document.querySelector("[data-nh-nav]");
        if (!nav) return;
        e.preventDefault();
        t.setAttribute("aria-expanded", String(nav.classList.toggle("is-open")));
        return;
      }
      var portfolioTab = e.target.closest && e.target.closest("[data-portfolio-term]");
      if (portfolioTab) {
        if (!filterPortfolio(portfolioTab)) return;
        e.preventDefault();
        return;
      }

      /* Article outline: the Figma head carries an [Ẩn] toggle. */
      var toc = e.target.closest && e.target.closest("[data-nh-toc-toggle]");
      if (toc) {
        var box = toc.closest(".article__toc");
        var list = box && box.querySelector("[data-nh-toc]");
        if (!list) return;
        var hidden = list.hasAttribute("hidden");
        if (hidden) list.removeAttribute("hidden");
        else list.setAttribute("hidden", "");
        toc.textContent = hidden ? "[Ẩn]" : "[Hiện]";
        return;
      }
      var vid = e.target.closest && e.target.closest("[data-nh-video]");
      if (vid) {
        e.preventDefault();
        openVideo(vid.getAttribute("data-nh-video") || vid.getAttribute("href"));
        return;
      }
      var slideBtn = e.target.closest && e.target.closest("[data-nh-slider-prev],[data-nh-slider-next]");
      if (slideBtn && !slideBtn.disabled) {
        var root = slideBtn.closest("[data-nh-slider]");
        var track = root && root.querySelector("[data-nh-slider-track]");
        if (!track) return;
        e.preventDefault();
        var card = track.querySelector(".nh-team-card");
        var gap = parseFloat(getComputedStyle(track).gap);
        if (isNaN(gap)) gap = 0;
        var step = card ? card.getBoundingClientRect().width + gap : track.clientWidth;
        track.scrollLeft += slideBtn.hasAttribute("data-nh-slider-next") ? step : -step;
      }
    });

    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape") {
        closeVideo();
        closeNav();
      }
    });

    var lastY = 0;
    var ticking = false;
    var read = function () {
      if (ticking) return;
      ticking = true;
      requestAnimationFrame(function () {
        ticking = false;
        var header = document.querySelector("[data-nh-header]");
        if (!header) return;
        var y = window.scrollY;
        var nav = document.querySelector("[data-nh-nav]");
        var menuOpen = nav && nav.classList.contains("is-open");
        var searchOpen = document.body.classList.contains("nh-search-locked");
        var videoOpen = document.body.classList.contains("nh-video-locked");
        header.classList.toggle("is-scrolled", y > 24);
        if (menuOpen || searchOpen || videoOpen || y <= 48) {
          header.classList.remove("is-hidden");
        } else if (y > lastY + 8) {
          header.classList.add("is-hidden");
        } else if (y < lastY - 8) {
          header.classList.remove("is-hidden");
        }
        lastY = y;
      });
    };
    window.addEventListener("scroll", read, { passive: true });
    read();

    initSearch();
    initSliders();
    initVideo();
  }

  function initSliders() {
    document.querySelectorAll("[data-nh-slider]").forEach(function (root) {
      if (root.__nhSlider) return;
      var track = root.querySelector("[data-nh-slider-track]");
      var prev = root.querySelector("[data-nh-slider-prev]");
      var next = root.querySelector("[data-nh-slider-next]");
      if (!track) return;
      root.__nhSlider = true;
      function sync() {
        var max = Math.max(0, track.scrollWidth - track.clientWidth);
        var x = track.scrollLeft;
        if (prev) prev.disabled = x <= 1;
        if (next) next.disabled = x >= max - 1;
      }
      track.addEventListener("scroll", sync, { passive: true });
      window.addEventListener("resize", sync);
      sync();
    });
  }

  var videoApi = null;

  function youtubeId(value) {
    if (!value || value === "#") return "";
    var m = String(value).match(/(?:youtu\.be\/|v=|embed\/)([\w-]{11})/);
    if (m) return m[1];
    return /^[\w-]{11}$/.test(value) ? value : "";
  }

  function closeVideo() {
    if (videoApi && videoApi.close) videoApi.close();
  }

  function openVideo(value) {
    var id = youtubeId(value);
    if (!id) return;
    initVideo().open(id);
  }

  function initVideo() {
    if (videoApi && videoApi.root && videoApi.root.isConnected) return videoApi;
    document.querySelectorAll(".nh-video-modal").forEach(function (el) { el.remove(); });
    var root = document.createElement("div");
    root.className = "nh-video-modal";
    root.setAttribute("role", "dialog");
    root.setAttribute("aria-modal", "true");
    root.setAttribute("aria-label", "Xem video");
    root.innerHTML =
      '<div class="nh-video-modal__scrim" data-nh-video-close></div>' +
      '<div class="nh-video-modal__dialog">' +
        '<button class="nh-video-modal__close" type="button" data-nh-video-close>Esc</button>' +
        '<div class="nh-video-modal__frame"><iframe allow="autoplay; fullscreen; picture-in-picture" title="Video"></iframe></div>' +
      "</div>";
    document.body.appendChild(root);
    var frame = root.querySelector("iframe");
    var last = null;

    function open(id) {
      last = document.activeElement;
      frame.src = "https://www.youtube-nocookie.com/embed/" + encodeURIComponent(id) + "?autoplay=1";
      root.classList.add("is-open");
      document.body.classList.add("nh-video-locked");
      var closeBtn = root.querySelector("[data-nh-video-close]");
      if (closeBtn && closeBtn.focus) closeBtn.focus();
    }

    function close() {
      root.classList.remove("is-open");
      document.body.classList.remove("nh-video-locked");
      frame.src = "";
      if (last && last.focus) last.focus();
    }

    root.addEventListener("click", function (e) {
      if (e.target.closest("[data-nh-video-close]")) close();
    });

    videoApi = { root: root, open: open, close: close };
    return videoApi;
  }

  onReady(function () {
    bindOnce();

    /* Mark the current nav item from body[data-page]. */
    var page = document.body.getAttribute("data-page");
    if (page) {
      document.querySelectorAll("[data-nav-link]").forEach(function (a) {
        if (a.getAttribute("data-page") === page) a.setAttribute("aria-current", "page");
      });
    }

    document.querySelectorAll("[data-nh-year]").forEach(function (el) {
      el.textContent = String(new Date().getFullYear());
    });
  });

  /* ── Header search lightbox ─────────────────────────────────────
     Live-filters an index built from the page itself (nav, cards,
     headings) so the results always point at links that exist. Pages
     with real content can set window.NHATO_SEARCH_INDEX = [{title,
     kind, url}] to replace the derived set. */
  function fold(s) {
    return s.normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/đ/g, "d").replace(/Đ/g, "D").toLowerCase();
  }

  function collect() {
    if (Array.isArray(window.NHATO_SEARCH_INDEX)) return window.NHATO_SEARCH_INDEX;
    var out = [];
    var seen = {};
    var add = function (title, kind, url) {
      title = (title || "").replace(/\s+/g, " ").trim();
      if (!title || !url) return;
      var key = kind + "|" + title;
      if (seen[key]) return;
      seen[key] = 1;
      out.push({ title: title, kind: kind, url: url });
    };
    var href = function (el) {
      var a = el.closest("a[href]");
      var h = a && a.getAttribute("href");
      return h && h !== "#" ? h : null;
    };
    document.querySelectorAll("[data-nh-nav] .nh-header__link").forEach(function (a) {
      add(a.textContent, "Trang", href(a));
    });
    var sources = [
      [".nh-tile__title", "Chuyên mục"],
      [".nh-style-tile span", "Phong cách"],
      [".nh-project-card strong", "Dự án"],
      [".nh-service-card__title", "Dịch vụ"],
      [".nh-article-card h3", "Bài viết"],
      [".nh-event-card__title", "Sự kiện"],
      [".nh-taste-tile__label", "Phong vị"]
    ];
    sources.forEach(function (pair) {
      document.querySelectorAll(pair[0]).forEach(function (el) {
        add(el.textContent, pair[1], href(el));
      });
    });
    var n = 0;
    document.querySelectorAll("main h2, main h3").forEach(function (h) {
      if (h.closest(".sr-only") || h.classList.contains("sr-only")) return;
      if (!h.id) h.id = "nh-s-" + ++n;
      add(h.innerText || h.textContent, "Trên trang", "#" + h.id);
    });
    return out;
  }

  function mark(title, q) {
    var i = fold(title).indexOf(fold(q));
    if (!q || i < 0) return escapeHtml(title);
    return escapeHtml(title.slice(0, i)) + "<mark>" + escapeHtml(title.slice(i, i + q.length)) + "</mark>" + escapeHtml(title.slice(i + q.length));
  }

  function escapeHtml(s) {
    return s.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
  }

  var searchApi = null;

  function initSearch() {
    if (window.__nhSearchBound) return;
    window.__nhSearchBound = true;
    /* Delegated from the document so a re-rendered header keeps working. */
    document.addEventListener("click", function (e) {
      var t = e.target.closest && e.target.closest(".nh-header__search,[data-nh-search-open]");
      if (!t) return;
      e.preventDefault();
      buildSearch().open();
    });
    document.addEventListener("keydown", function (e) {
      if ((e.key === "k" || e.key === "K") && (e.metaKey || e.ctrlKey)) {
        e.preventDefault();
        buildSearch().open();
      }
    });
  }

  function buildSearch() {
    if (searchApi && searchApi.root.isConnected) return searchApi;
    document.querySelectorAll(".nh-search").forEach(function (el) { el.remove(); });

    var root = document.createElement("div");
    root.className = "nh-search";
    root.setAttribute("role", "dialog");
    root.setAttribute("aria-modal", "true");
    root.setAttribute("aria-label", "Tìm kiếm");
    var headerSearchIcon = document.querySelector(".nh-header__search .nh-icon-mask");
    var searchIconStyle = headerSearchIcon ? ' style="' + headerSearchIcon.getAttribute("style") + '"' : "";
    root.innerHTML =
      '<div class="nh-search__scrim" data-nh-search-close></div>' +
      '<div class="nh-search__dialog">' +
        '<label class="nh-search__field">' +
          '<span class="nh-icon-mask"' + searchIconStyle + ' aria-hidden="true"></span>' +
          '<input class="nh-search__input" type="search" autocomplete="off" placeholder="Tìm không gian, nghệ thuật, bài viết…" aria-label="Từ khoá tìm kiếm">' +
          '<button class="nh-search__close" type="button" data-nh-search-close>Esc</button>' +
        "</label>" +
        '<p class="nh-search__group" data-nh-search-group>Gợi ý</p>' +
        '<ul class="nh-search__list" data-nh-search-list></ul>' +
        '<div class="nh-search__hint"><span><kbd>↑↓</kbd>chọn</span><span><kbd>↵</kbd>mở</span><span><kbd>Esc</kbd>đóng</span></div>' +
      "</div>";
    document.body.appendChild(root);

    var input = root.querySelector(".nh-search__input");
    var list = root.querySelector("[data-nh-search-list]");
    var group = root.querySelector("[data-nh-search-group]");
    var index = [];
    var rows = [];
    var active = -1;
    var last = null;

    function render() {
      var q = input.value.trim();
      rows = q ? index.filter(function (r) { return fold(r.title).indexOf(fold(q)) > -1; }) : index.slice(0, 8);
      group.textContent = q ? rows.length + " kết quả cho “" + q + "”" : "Gợi ý";
      active = rows.length ? 0 : -1;
      if (!rows.length) {
        list.innerHTML = '<li><p class="nh-search__empty">Không tìm thấy nội dung nào khớp với “' + escapeHtml(q) + "”.</p></li>";
        return;
      }
      list.innerHTML = rows.map(function (r, i) {
        return '<li><a class="nh-search__item' + (i === 0 ? " is-active" : "") + '" href="' + escapeHtml(r.url) + '"><span>' + mark(r.title, q) + '</span><span class="nh-search__kind">' + escapeHtml(r.kind) + "</span></a></li>";
      }).join("");
    }

    function setActive(next) {
      var items = list.querySelectorAll(".nh-search__item");
      if (!items.length) return;
      active = (next + items.length) % items.length;
      items.forEach(function (el, i) { el.classList.toggle("is-active", i === active); });
      var el = items[active];
      var top = el.offsetTop - list.offsetTop;
      if (top < list.scrollTop) list.scrollTop = top;
      else if (top + el.offsetHeight > list.scrollTop + list.clientHeight) list.scrollTop = top + el.offsetHeight - list.clientHeight;
    }

    function open() {
      last = document.activeElement;
      index = collect();
      root.classList.add("is-open");
      document.body.classList.add("nh-search-locked");
      input.value = "";
      render();
      input.focus();
    }

    function close() {
      root.classList.remove("is-open");
      document.body.classList.remove("nh-search-locked");
      if (last && last.focus) last.focus();
    }

    root.querySelectorAll("[data-nh-search-close]").forEach(function (el) {
      el.addEventListener("click", close);
    });
    input.addEventListener("input", render);
    root.addEventListener("keydown", function (e) {
      if (e.key === "Escape") { e.preventDefault(); close(); }
      else if (e.key === "ArrowDown") { e.preventDefault(); setActive(active + 1); }
      else if (e.key === "ArrowUp") { e.preventDefault(); setActive(active - 1); }
      else if (e.key === "Enter") {
        var el = list.querySelectorAll(".nh-search__item")[active];
        if (el) { e.preventDefault(); el.click(); close(); }
      }
    });
    list.addEventListener("click", function (e) {
      if (e.target.closest(".nh-search__item")) close();
    });

    searchApi = { root: root, open: open, close: close };
    return searchApi;
  }
})();
