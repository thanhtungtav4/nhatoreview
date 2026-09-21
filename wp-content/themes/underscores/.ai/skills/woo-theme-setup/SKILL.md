---
name: woo-theme-setup
description: "Use when bật/tích hợp WooCommerce vào theme classic này (declare support, woocommerce.php wrapper, mini-cart ajax, tối ưu cart-fragments). KHÔNG dùng cho override layout product hay build Woo extension/plugin."
---

# WooCommerce Theme Setup

Bật Woo chuẩn trong theme classic `underscores`. Theo `.ai/rules/woocommerce.md`.
Nguồn: https://developer.woocommerce.com/docs/theming/.

## Khi nào dùng

- Lần đầu tích hợp Woo vào theme, hoặc khi shop/product/cart hiển thị sai wrapper.
- Khi mini-cart không cập nhật, hoặc Woo asset làm chậm trang tin.

## Workflow

1. **Declare support** — thêm gallery support cạnh `add_theme_support('woocommerce')` ở `app/Hooks/CommonHook.php::register_menus()` (zoom/lightbox/slider).
2. **Wrapper** — tạo `woocommerce.php` root (`get_header()` + `woocommerce_content()` + `get_footer()`); unhook `woocommerce_output_content_wrapper`/`_end`, hook lại wrapper `<div class="underscores-woo container">` (KHÔNG mở `<main>` thứ 2 — `header.php` đã mở).
3. **Mini-cart** — ưu tiên Mini-Cart block. Classic: filter `woocommerce_add_to_cart_fragments` trả HTML count/total từ `WC()->cart`. Không tự viết ajax.
4. **Perf cart-fragments** — WC ≥ 7.8 không tự load fragments mọi trang; chỉ khi mini-cart classic nằm ở header mới cần `underscores_needs_cart_fragments()` (is_woocommerce/cart/checkout + shortcode + filter) để dequeue ngoài trang Woo. Dequeue Woo CSS/JS ngoài trang Woo.
5. **Guard** — mọi entrypoint Woo trong theme bọc `class_exists('WooCommerce')`.

## Test (bắt buộc)

- [ ] Shop archive: chỉ 1 `<main>` trong trang, wrapper `.underscores-woo` đúng, sản phẩm render.
- [ ] Single product: gallery zoom/lightbox/slider hoạt động.
- [ ] Cart + Checkout: trang load, không lỗi wrapper.
- [ ] My Account: render đúng.
- [ ] Trang tin (không shop): `wc-cart-fragments.js` KHÔNG load (DevTools Network).
- [ ] Trang shop: cart-fragments CÓ load, mini-cart cập nhật khi add-to-cart.
- [ ] Tắt plugin Woo: frontend KHÔNG fatal (class_exists guard).

## Checklist

- [ ] Gallery support khai báo
- [ ] `woocommerce.php` wrapper + unhook default
- [ ] Mini-cart fragment core
- [ ] cart-fragments conditional + dequeue Woo asset ngoài trang Woo
- [ ] `class_exists('WooCommerce')` guard
- [ ] Không override template product (out of scope)
