# Lazy-First — Code tối thiểu (triết lý "lazy senior dev")

Nguồn: [ponytail](https://github.com/DietrichGebert/ponytail) — *"The best code is the code you never wrote."*

Dòng code tốt nhất là dòng không phải viết. Trước khi viết code, agent đọc hiểu vấn đề + flow thật,
rồi **dừng ở nấc thang đầu tiên thoả mãn**. Lười về giải pháp, KHÔNG lười về đọc code.

## The Ladder

Dừng ở rung đầu tiên đúng:

```
1. Có cần tồn tại không?        → không: bỏ (YAGNI)
2. Codebase đã có sẵn?          → tái dùng, đừng viết lại
3. WordPress core / PHP làm được? → dùng (the_post, wp_*, get_template_part, native PHP)
4. Native platform feature?     → dùng (CSS > JS, DB constraint > app code, <input type=date>)
5. Dependency đã cài?           → dùng; đừng thêm dep mới cho việc vài dòng làm được
6. Một dòng?                    → một dòng
7. Chỉ khi đó: code tối thiểu đủ chạy
```

## Lazy ≠ ẩu — KHÔNG bao giờ cắt

- Validate ở trust boundary (sanitize `$_POST`/`$_GET`, escape output).
- Xử lý lỗi tránh mất data.
- Bảo mật (nonce AJAX, capability check, không SQL thô).
- Accessibility cơ bản (alt, label, focus).
- Bất cứ gì user yêu cầu rõ.

Lười là viết ÍT code, không phải chọn thuật toán flimsy hay bỏ guard.

## Áp dụng cho theme này

- **Không thêm thư viện JS** cho việc CSS/native làm được (accordion, date, lazy-load `loading="lazy"`).
- **Không bọc helper** quanh 1 dòng `get_field()`/`get_template_part()` (xem `data-rendering.md`).
- **Không abstraction đầu cơ**: không interface 1 implementation, không factory 1 sản phẩm, không config cho hằng số không đổi.
- **Không scaffold "để dành"**: file/hàm chỉ tạo khi dùng ngay.
- WordPress core trước: `wp_get_attachment_image`, `paginate_links`, `wp_kses_post`... thay vì tự viết.
- Native HTML/CSS trước JS: `<details>`, `<input type="date">`, `position: sticky`, `aspect-ratio`.

## Đánh dấu shortcut có chủ đích

Khi cố tình làm đơn giản (có trần đã biết), để 1 comment `ponytail:` nêu trần + đường nâng cấp:

```php
// ponytail: scan tuyến tính, đủ cho < vài trăm item; index nếu list phình to
// ponytail: global lock, tách lock theo account nếu cần throughput
```
→ Comment cho biết đây là **chủ đích**, không phải thiếu hiểu biết.

## Khi review

Hỏi: *thứ này có cần tồn tại không?* trước khi hỏi *viết thế nào cho đẹp?*
Diff ngắn nhất chạy được là diff đúng. Boring > clever (clever là thứ ai đó phải giải mã lúc 3h sáng).
