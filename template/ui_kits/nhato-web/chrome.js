/* NHATO Collection — shared header and footer.
   Plain JS injection so every page in the kit stays a single static file.
   In WordPress these become header.php / footer.php template parts. */
(function () {
  var A = "../../assets";
  var nav = [
    ["network", "Network", "network.html"],
    ["home", "Home", "index.html"],
    ["art", "Art", "art.html"],
    ["taste", "Taste", "taste.html"],
    ["original", "Original", "original.html"]
  ];
  function icon(id, cls) {
    return '<svg class="' + (cls || "nh-icon") + '"><use href="#nh-' + id + '"></use></svg>';
  }
  function social(ids, extraClass) {
    return '<div class="nh-social ' + (extraClass || "") + '" aria-label="Mạng xã hội">' + ids.map(function (i) {
      return '<a class="nh-social__link" href="#" aria-label="' + i + '">' + icon(i) + "</a>";
    }).join("") + "</div>";
  }
  var brand = '<a class="nh-brand" href="index.html" aria-label="NHATO Collection — Trang chủ"><span class="nh-brand__mark"><img src="' + A + '/logo/nhato-collection-lockup.png" alt="NHATO Collection" width="2326" height="796"></span></a>';
  var footerBrand = '<a class="nh-footer__brand-box" href="index.html" aria-label="NHATO Collection — Trang chủ"><img src="' + A + '/logo/nhato-footer-logo.png" alt="NHATO Collection"></a>';

  var header =
    '<header class="nh-header" data-nh-header>' +
      '<div class="nh-header__inner nh-container">' + brand +
        '<nav class="nh-header__nav" data-nh-nav aria-label="Điều hướng chính"><ul class="nh-header__list">' +
          '<li class="nh-header__item"><button class="nh-header__menu" type="button" aria-label="Mở danh mục">' + icon('menu', '') + '</button></li>' +
          nav.map(function (n) {
            return '<li class="nh-header__item"><a class="nh-header__link" data-nav-link data-page="' + n[0] + '" href="' + n[2] + '">' + n[1] + "</a></li>";
          }).join("") +
        "</ul></nav>" +
        '<div class="nh-header__utilities">' + social(["facebook", "tiktok", "youtube"], "nh-social--header") +
          '<a class="nh-header__call" href="tel:+84968677337">Call us: 0968 677 337</a>' +
          '<button class="nh-header__search" type="button" aria-label="Tìm kiếm">' + icon('search', '') + '</button>' +
          '<button class="nh-header__toggle" type="button" data-nh-menu-toggle aria-controls="nh-nav" aria-expanded="false" aria-label="Mở menu"><span></span><span></span></button>' +
        "</div>" +
      "</div>" +
    "</header>";

  var footer =
    '<footer class="nh-footer"><div class="nh-footer__main nh-container">' +
      '<div class="nh-footer__intro">' + footerBrand +
        '<p class="nh-footer__description">NHATO Collection là không gian kết nối giữa kiến trúc, nội thất, nghệ thuật và phong cách sống, hướng đến việc tạo nên những giá trị bền vững và truyền cảm hứng cho những trải nghiệm sống.</p>' +
        '<div class="nh-footer__follow"><p class="nh-footer__label">Follow us on</p>' + social(["facebook", "x", "tiktok", "youtube"], "nh-social--tight") + "</div>" +
      "</div>" +
      '<div class="nh-footer__column"><h2 class="nh-footer__heading">Khám phá</h2><ul class="nh-footer__links">' +
        ["Về NHATO|about.html", "Kết nối|news.html", "Không gian sống|space.html", "Nghệ thuật|art.html", "Phong vị|taste.html", "Bản sắc|original.html"].map(function (s) {
          var p = s.split("|"); return '<li><a href="' + p[1] + '">' + p[0] + "</a></li>";
        }).join("") + "</ul></div>" +
      '<div class="nh-footer__column"><h2 class="nh-footer__heading">Hỗ trợ</h2><ul class="nh-footer__links">' +
        ["Câu hỏi thường gặp", "Chính sách bảo mật", "Điều khoản sử dụng", "Chính sách hoàn tiền", "Tham quan Showroom"].map(function (s) {
          return '<li><a href="#">' + s + "</a></li>";
        }).join("") + "</ul></div>" +
      '<address class="nh-footer__column nh-footer__contact"><h2 class="nh-footer__heading">Contact us</h2>' +
        "<p>R4 Royal City, Nguyễn Trãi,<br>Thanh Xuân, Hà Nội</p>" +
        '<p><a href="mailto:info@nhatoreview.com">info@nhatoreview.com</a></p>' +
        '<p><a href="tel:+84968677337">0968 677 337</a></p>' +
        "<p>Monday – Friday: 09:00 – 18:00</p></address>" +
      '<div class="nh-footer__newsletter"><h2 class="nh-footer__newsletter-title">Giữ nguồn cảm hứng cùng NHATO</h2>' +
        '<p class="nh-footer__newsletter-copy">Đăng ký để nhận những cập nhật mới nhất về không gian, nghệ thuật và các trải nghiệm.</p>' +
        '<form class="nh-newsletter" data-nh-newsletter novalidate><label class="sr-only" for="nl">Địa chỉ email</label><input id="nl" name="email" type="email" placeholder="Your email address" autocomplete="email" required>' +
        '<button class="nh-newsletter__submit" type="submit" aria-label="Đăng ký nhận tin">' + icon("arrow-right") + "</button></form>" +
        '<p class="nh-newsletter__status" data-nh-newsletter-status role="status" aria-live="polite"></p>' +
        '<p class="nh-footer__credit">Designed by NHATO Design</p></div>' +
    "</div></footer>";

  function mount() {
    var h = document.querySelector("[data-nh-mount=header]");
    var f = document.querySelector("[data-nh-mount=footer]");
    if (h) h.outerHTML = header;
    if (f) f.outerHTML = footer;
  }
  if (document.readyState !== "loading") mount();
  else document.addEventListener("DOMContentLoaded", mount);
})();
