from reportlab.lib import colors
from reportlab.lib.colors import HexColor
from reportlab.lib.enums import TA_CENTER, TA_LEFT
from reportlab.lib.pagesizes import letter
from reportlab.lib.styles import ParagraphStyle, getSampleStyleSheet
from reportlab.lib.units import mm
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont
from reportlab.platypus import (
    BaseDocTemplate,
    Frame,
    PageTemplate,
    Paragraph,
    Spacer,
    Table,
    TableStyle,
    PageBreak,
)


OUT = "/Volumes/Manager Data/Tool/nhatoreview/output/pdf/bao-gia-NHATO-2-Figma-WordPress.pdf"
FONT_ROOT = "/Users/macbook/.cache/codex-runtimes/codex-primary-runtime/dependencies/native/libreoffice-headless/libreoffice/LibreOfficeDev.app/Contents/Resources/fonts/truetype"

pdfmetrics.registerFont(TTFont("DV", f"{FONT_ROOT}/DejaVuSans.ttf"))
pdfmetrics.registerFont(TTFont("DV-Bold", f"{FONT_ROOT}/DejaVuSans-Bold.ttf"))
pdfmetrics.registerFont(TTFont("DV-Serif", f"{FONT_ROOT}/DejaVuSerif.ttf"))
pdfmetrics.registerFont(TTFont("DV-Serif-Bold", f"{FONT_ROOT}/DejaVuSerif-Bold.ttf"))

NAVY = HexColor("#102E63")
PURPLE = HexColor("#7411D4")
MAGENTA = HexColor("#F20E86")
CYAN = HexColor("#14AEDD")
BLUE = HexColor("#2165B9")
ORANGE = HexColor("#FF8A1D")
GOLD = HexColor("#D9A52E")
INK = HexColor("#161616")
MUTED = HexColor("#5D6470")
LIGHT = HexColor("#F7F8FA")
LINE = HexColor("#B7BDC7")


styles = getSampleStyleSheet()
styles.add(ParagraphStyle(
    name="DocTitle", fontName="DV", fontSize=16, leading=21,
    textColor=PURPLE, alignment=TA_CENTER, spaceBefore=2 * mm, spaceAfter=6 * mm,
))
styles.add(ParagraphStyle(
    name="Section", fontName="DV-Bold", fontSize=11.2, leading=15,
    textColor=ORANGE, spaceBefore=4 * mm, spaceAfter=2 * mm,
))
styles.add(ParagraphStyle(
    name="Subsection", fontName="DV-Bold", fontSize=9.6, leading=13,
    textColor=PURPLE, spaceBefore=2 * mm, spaceAfter=1.5 * mm,
))
styles.add(ParagraphStyle(
    name="Body", fontName="DV", fontSize=8.8, leading=13.2,
    textColor=INK, spaceAfter=2.2 * mm,
))
styles.add(ParagraphStyle(
    name="BodyTight", fontName="DV", fontSize=8.35, leading=11.4,
    textColor=INK, spaceAfter=1.1 * mm,
))
styles.add(ParagraphStyle(
    name="Small", fontName="DV", fontSize=7.7, leading=10.5,
    textColor=INK,
))
styles.add(ParagraphStyle(
    name="SmallMuted", fontName="DV", fontSize=7.5, leading=10.2,
    textColor=MUTED,
))
styles.add(ParagraphStyle(
    name="TableHead", fontName="DV-Bold", fontSize=8.3, leading=10.5,
    textColor=colors.white, alignment=TA_CENTER,
))
styles.add(ParagraphStyle(
    name="TableText", fontName="DV", fontSize=7.65, leading=10.3,
    textColor=INK,
))
styles.add(ParagraphStyle(
    name="TableTextBold", fontName="DV-Bold", fontSize=7.7, leading=10.3,
    textColor=INK,
))
styles.add(ParagraphStyle(
    name="TableWhite", fontName="DV-Bold", fontSize=8.3, leading=10.5,
    textColor=colors.white,
))
styles.add(ParagraphStyle(
    name="Callout", fontName="DV", fontSize=8.2, leading=12,
    textColor=INK, leftIndent=3 * mm, rightIndent=3 * mm,
    spaceBefore=2 * mm, spaceAfter=2 * mm,
))
styles.add(ParagraphStyle(
    name="Closing", fontName="DV-Bold", fontSize=10, leading=15,
    textColor=PURPLE, alignment=TA_CENTER, spaceBefore=6 * mm,
))


def P(text, style="Body"):
    return Paragraph(text, styles[style])


def bullet(text, style="BodyTight"):
    return P(f"- {text}", style)


def money(value):
    return f"{value:,.0f}".replace(",", ".")


def cell(text, style="TableText"):
    if isinstance(text, Paragraph):
        return text
    return P(str(text), style)


def cost_table():
    rows = [
        [cell("HẠNG MỤC THỰC HIỆN", "TableHead"), cell("KINH PHÍ (VNĐ)", "TableHead")],
        [cell("1. Phân tích Figma và lập kế hoạch triển khai", "TableTextBold"), cell(money(800000), "TableText")],
        [cell("2. Cài đặt WordPress và xây dựng cấu trúc theme", "TableTextBold"), cell(money(1700000), "TableText")],
        [cell("3. Xây dựng thành phần chung: header, menu, footer, CTA, typography", "TableTextBold"), cell(money(1500000), "TableText")],
        [cell("4. Dựng giao diện 10 màn hình theo Figma", "TableTextBold"), cell(money(6500000), "TableText")],
        [cell("5. Xây dựng nội dung động và khu vực quản trị WordPress", "TableTextBold"), cell(money(2000000), "TableText")],
        [cell("6. Responsive và hiệu ứng tương tác", "TableTextBold"), cell(money(1500000), "TableText")],
        [cell("7. Tối ưu SEO on-page và technical SEO cơ bản", "TableTextBold"), cell(money(1500000), "TableText")],
        [cell("8. Kiểm thử, bàn giao và hướng dẫn quản trị", "TableTextBold"), cell(money(1500000), "TableText")],
        [cell("TỔNG DỰ ÁN", "TableWhite"), cell(money(17000000), "TableWhite")],
    ]
    t = Table(rows, colWidths=[143 * mm, 38 * mm], repeatRows=1)
    t.setStyle(TableStyle([
        ("BACKGROUND", (0, 0), (-1, 0), BLUE),
        ("BACKGROUND", (0, -1), (-1, -1), CYAN),
        ("TEXTCOLOR", (0, -1), (-1, -1), colors.white),
        ("GRID", (0, 0), (-1, -1), 0.55, colors.black),
        ("VALIGN", (0, 0), (-1, -1), "MIDDLE"),
        ("ALIGN", (1, 1), (1, -1), "RIGHT"),
        ("LEFTPADDING", (0, 0), (-1, -1), 6),
        ("RIGHTPADDING", (0, 0), (-1, -1), 6),
        ("TOPPADDING", (0, 0), (-1, -1), 5),
        ("BOTTOMPADDING", (0, 0), (-1, -1), 5),
    ]))
    return t


def info_table():
    rows = [
        [cell("Kính gửi: <b>NHATO COLLECTION</b>", "TableText"), cell("Nơi gửi: <b>ĐƠN VỊ TRIỂN KHAI WEBSITE</b>", "TableText")],
        [cell("Người nhận: [Tên người nhận]<br/>Điện thoại: [Số điện thoại]<br/>Email: [Email khách hàng]", "TableText"),
         cell("Phụ trách: [Tên phụ trách]<br/>Điện thoại: [Số điện thoại]<br/>Email: [Email đơn vị]", "TableText")],
        [cell("Job Code: NHATO-WP-20260911", "TableText"), cell("Ngày gửi: 11/09/2026", "TableText")],
    ]
    t = Table(rows, colWidths=[90 * mm, 91 * mm])
    t.setStyle(TableStyle([
        ("BACKGROUND", (0, 0), (-1, 0), BLUE),
        ("TEXTCOLOR", (0, 0), (-1, 0), colors.white),
        ("GRID", (0, 0), (-1, -1), 0.55, colors.black),
        ("VALIGN", (0, 0), (-1, -1), "TOP"),
        ("SPAN", (0, 2), (1, 2)),
        ("ALIGN", (0, 2), (1, 2), "RIGHT"),
        ("LEFTPADDING", (0, 0), (-1, -1), 6),
        ("RIGHTPADDING", (0, 0), (-1, -1), 6),
        ("TOPPADDING", (0, 0), (-1, -1), 6),
        ("BOTTOMPADDING", (0, 0), (-1, -1), 6),
    ]))
    return t


def detail_table(headers, rows, widths, total_row=None):
    data = [[cell(h, "TableHead") for h in headers]]
    for row in rows:
        data.append([cell(value, "TableText") for value in row])
    if total_row:
        data.append([cell(value, "TableWhite") for value in total_row])
    t = Table(data, colWidths=widths, repeatRows=1)
    commands = [
        ("BACKGROUND", (0, 0), (-1, 0), PURPLE),
        ("GRID", (0, 0), (-1, -1), 0.5, colors.black),
        ("VALIGN", (0, 0), (-1, -1), "TOP"),
        ("LEFTPADDING", (0, 0), (-1, -1), 5),
        ("RIGHTPADDING", (0, 0), (-1, -1), 5),
        ("TOPPADDING", (0, 0), (-1, -1), 5),
        ("BOTTOMPADDING", (0, 0), (-1, -1), 5),
    ]
    if total_row:
        commands += [("BACKGROUND", (0, -1), (-1, -1), CYAN), ("TEXTCOLOR", (0, -1), (-1, -1), colors.white)]
    t.setStyle(TableStyle(commands))
    return t


def on_page(canvas, doc):
    width, height = letter
    canvas.saveState()
    # Repeating header inspired by the supplied reference PDF.
    canvas.setFillColor(NAVY)
    canvas.rect(0, height - 150, width, 150, stroke=0, fill=1)
    canvas.setStrokeColor(ORANGE)
    canvas.setLineWidth(28)
    canvas.line(-15, height - 111, 130, height - 145)
    canvas.setStrokeColor(MAGENTA)
    canvas.line(115, height - 132, 236, height - 100)
    canvas.setStrokeColor(PURPLE)
    canvas.line(232, height - 100, 360, height - 130)
    canvas.setStrokeColor(CYAN)
    canvas.line(360, height - 130, 500, height - 83)
    canvas.setStrokeColor(BLUE)
    canvas.line(500, height - 83, 630, height - 115)
    canvas.setLineWidth(1)
    canvas.setFillColor(colors.white)
    canvas.setFont("DV-Bold", 16)
    canvas.drawString(36, height - 38, "NHATO 2")
    canvas.setFont("DV", 7.7)
    canvas.setFillColor(HexColor("#D9E6FF"))
    canvas.drawString(36, height - 51, "Figma - WordPress")
    canvas.setFillColor(colors.white)
    canvas.setFont("DV-Bold", 11)
    canvas.drawRightString(width - 36, height - 38, "BÁO GIÁ TRIỂN KHAI WEBSITE")
    canvas.setFont("DV", 7.5)
    canvas.drawRightString(width - 36, height - 52, "Thiết kế nội thất và phong cách sống")
    canvas.drawRightString(width - 36, height - 65, "[Số điện thoại]  |  [Email đơn vị]")

    # Bottom color wave and page number.
    canvas.setFillColor(BLUE)
    canvas.saveState()
    canvas.setFillAlpha(0.98)
    path = canvas.beginPath()
    path.moveTo(0, 0)
    path.lineTo(0, 28)
    path.lineTo(170, 7)
    path.lineTo(350, 0)
    path.close()
    canvas.drawPath(path, fill=1, stroke=0)
    canvas.setFillColor(CYAN)
    path = canvas.beginPath()
    path.moveTo(305, 0)
    path.lineTo(612, 0)
    path.lineTo(612, 30)
    path.lineTo(420, 6)
    path.close()
    canvas.drawPath(path, fill=1, stroke=0)
    canvas.restoreState()
    canvas.setFillColor(INK)
    canvas.setFont("DV", 7.5)
    canvas.drawCentredString(width / 2, 14, f"Trang {doc.page}")
    canvas.restoreState()


doc = BaseDocTemplate(
    OUT,
    pagesize=letter,
    leftMargin=15 * mm,
    rightMargin=15 * mm,
    topMargin=58 * mm,
    bottomMargin=17 * mm,
    title="Báo giá chuyển Figma sang WordPress - NHATO 2",
    author="Đơn vị triển khai website",
)
frame = Frame(doc.leftMargin, doc.bottomMargin, doc.width, doc.height, id="normal")
doc.addPageTemplates([PageTemplate(id="quote", frames=frame, onPage=on_page)])

story = []

# Page 1 - quotation summary.
story += [
    P("BẢNG BÁO GIÁ CHUYỂN FIGMA SANG WORDPRESS", "DocTitle"),
    info_table(), Spacer(1, 4 * mm),
    P("Kính gửi Quý khách hàng,", "Body"),
    P("Căn cứ theo thiết kế Figma của dự án NHATO 2, chúng tôi xin gửi báo giá triển khai website trên nền tảng WordPress với phạm vi công việc và chi phí dự kiến như sau:", "Body"),
    P("PHẠM VI VÀ KINH PHÍ DỰ KIẾN", "Section"),
    cost_table(),
    Spacer(1, 3 * mm),
    P("Tổng giá trị dự án: <b>17.000.000 VNĐ</b> (Bằng chữ: Mười bảy triệu đồng), chưa bao gồm VAT, tên miền, hosting và các dịch vụ bên thứ ba.", "Callout"),
    PageBreak(),
]

# Page 2 - scope and screens.
story += [
    P("KẾ HOẠCH CHI TIẾT", "DocTitle"),
    P("1. Phân tích Figma và lập kế hoạch triển khai", "Section"),
    bullet("Đọc và phân tích toàn bộ file Figma NHATO 2; xác định layout, component, trạng thái và hành vi tương tác."),
    bullet("Chuẩn hóa cấu trúc trang, danh mục nội dung và các thành phần có thể tái sử dụng trong WordPress."),
    bullet("Xác định các nội dung tĩnh và nội dung động cần quản trị từ Admin."),
    P("2. Danh sách màn hình triển khai", "Section"),
    detail_table(
        ["STT", "Màn hình / template", "Nội dung chính"],
        [
            ["01", "Trang chủ landing", "Hero slider, 5 nhóm nội dung, phong cách, portfolio, CTA đặt lịch"],
            ["02", "Kết nối", "Hệ sinh thái kiến trúc sư, nội thất, nghệ sĩ, thương hiệu, chuyên gia, khách hàng"],
            ["03", "Không gian", "Danh mục không gian và lưới dự án tiêu biểu"],
            ["04", "Nghệ thuật", "Nghệ thuật đương đại, Nghệ thuật Đông Dương và bài viết liên quan"],
            ["05", "Phong vị", "Không gian, nghi thức, cảm nhận, kết nối; Trà, Cigar, Rượu"],
            ["06", "Bản sắc", "Bản sắc Việt, Tinh hoa thế giới và nội dung liên quan"],
            ["07", "Liên hệ", "Thông tin liên hệ, form, chọn dịch vụ và bản đồ"],
            ["08", "Về chúng tôi", "Câu chuyện, giá trị cốt lõi, đội ngũ và video"],
            ["09", "Danh mục tin tức", "Bài nổi bật, danh sách bài mới, danh mục và thẻ"],
            ["10", "Chi tiết tin tức", "Breadcrumb, mục lục, nội dung bài và bài viết liên quan"],
        ],
        [12 * mm, 39 * mm, 122 * mm],
    ),
    P("3. Thành phần dùng chung", "Section"),
    bullet("Header nền tối, logo NHATO, menu điều hướng, mạng xã hội và nút gọi điện."),
    bullet("Footer, form đăng ký email, CTA dạng viền và icon mũi tên tròn."),
    bullet("Hệ thống màu kem, charcoal, trắng và điểm nhấn gold; typography serif cho tiêu đề và sans-serif cho nội dung."),
    PageBreak(),
]

# Page 3 - WordPress build details.
story += [
    P("KẾ HOẠCH CHI TIẾT", "DocTitle"),
    P("4. Cài đặt WordPress và xây dựng theme", "Section"),
    bullet("Cài đặt và cấu hình WordPress trên hosting do khách hàng cung cấp."),
    bullet("Xây dựng theme riêng hoặc child theme theo cấu trúc dễ mở rộng và hạn chế phụ thuộc page builder nặng."),
    bullet("Tạo template cho trang, bài viết, dự án, danh mục và các khu vực nội dung theo Figma."),
    bullet("Cấu hình menu, widget/footer, media library và các thiết lập cơ bản cho Admin."),
    P("5. Xây dựng nội dung động và khu vực quản trị", "Section"),
    detail_table(
        ["Khu vực", "Chức năng quản trị", "Phạm vi"],
        [
            ["Dự án / Không gian", "Thêm, sửa, xóa dự án; ảnh đại diện; mô tả; loại hình; địa điểm", "Có"],
            ["Bài viết", "Tiêu đề, nội dung, ảnh đại diện, danh mục, ngày đăng, bài liên quan", "Có"],
            ["Phong cách", "Minimalist, Modern, Classic, Indochine, Rustic, Retro, Eco, Art Deco", "Có"],
            ["Đội ngũ", "Tên, chức danh, ảnh và thứ tự hiển thị", "Có"],
            ["Form liên hệ", "Họ tên, điện thoại, email, dịch vụ, ghi chú; gửi về email Admin", "Có"],
            ["Thông tin hệ thống", "Logo, hotline, email, địa chỉ, social link, footer", "Có"],
        ],
        [38 * mm, 108 * mm, 27 * mm],
    ),
    P("6. Responsive và tương tác", "Section"),
    bullet("Tối ưu hiển thị cho desktop, tablet và mobile theo cấu trúc responsive của Figma."),
    bullet("Triển khai hero slider, tab portfolio, hover card, carousel đội ngũ, CTA và các trạng thái menu."),
    bullet("Tối ưu ảnh theo kích thước hiển thị, lazy loading và chuyển đổi WebP khi phù hợp."),
    P("Lưu ý: mức giá 17 triệu phù hợp với website giới thiệu thương hiệu và tin tức; các chức năng đặt lịch phức tạp hoặc kết nối CRM/API sẽ được báo giá riêng.", "Callout"),
    PageBreak(),
]

# Page 4 - SEO and quality.
story += [
    P("TỐI ƯU SEO VÀ CHẤT LƯỢNG WEBSITE", "DocTitle"),
    P("7. Tối ưu SEO on-page và technical SEO cơ bản", "Section"),
    detail_table(
        ["Nhóm", "Công việc thực hiện"],
        [
            ["Cấu trúc URL", "Thiết lập permalink thân thiện; slug rõ ràng cho trang, dự án, danh mục và bài viết."],
            ["Tiêu đề và mô tả", "Cấu hình title, meta description, focus keyword cơ bản và mẫu SEO cho từng loại nội dung."],
            ["Heading", "Chuẩn hóa H1 duy nhất cho mỗi trang; sắp xếp H2/H3 theo thứ tự nội dung."],
            ["Hình ảnh", "Đặt tên file có ý nghĩa, ALT text, caption khi cần; tối ưu kích thước và định dạng WebP."],
            ["Liên kết", "Kiểm tra internal link, liên kết điều hướng, breadcrumb và trang 404."],
            ["Indexing", "Cấu hình sitemap XML, robots.txt, canonical và kiểm tra khả năng crawl cơ bản."],
            ["Social sharing", "Thiết lập Open Graph và Twitter Card cho trang và bài viết."],
            ["Schema", "Cấu hình schema cơ bản cho Organization, WebSite, Breadcrumb và Article."],
            ["Đo lường", "Kết nối Google Analytics và Google Search Console nếu khách hàng cung cấp quyền truy cập."],
        ],
        [38 * mm, 135 * mm],
    ),
    P("8. Hiệu năng và bảo mật cơ bản", "Section"),
    bullet("Cấu hình cache và tối ưu CSS/JS ở mức cơ bản theo khả năng của hosting."),
    bullet("Hạn chế plugin không cần thiết; kiểm tra lỗi console, link hỏng và hiển thị trên trình duyệt phổ biến."),
    bullet("Cấu hình tài khoản quản trị, cập nhật WordPress/plugin và các thiết lập bảo mật cơ bản."),
    bullet("Kiểm tra form liên hệ, email nhận thông tin và chống spam ở mức cơ bản."),
    P("SEO trong báo giá này là tối ưu nền tảng ban đầu. Không bao gồm viết bài SEO, SEO off-page, backlink, chạy quảng cáo hoặc cam kết thứ hạng từ khóa.", "Callout"),
    PageBreak(),
]

# Page 5 - staffing, timeline and process.
story += [
    P("NHÂN SỰ VÀ TIẾN ĐỘ DỰ KIẾN", "DocTitle"),
    detail_table(
        ["Nội dung công việc", "Nhân sự", "Thời gian", "Ghi chú"],
        [
            ["Phân tích Figma, cấu trúc nội dung và setup WordPress", "01", "2 ngày", "Chốt sitemap và phạm vi"],
            ["Dựng theme, header/footer và component dùng chung", "01", "3 ngày", "Theo hệ thống của Figma"],
            ["Dựng 10 template và nội dung động", "01", "8-10 ngày", "Bao gồm trang tin tức và dự án"],
            ["Responsive và hiệu ứng tương tác", "01", "3-4 ngày", "Desktop, tablet, mobile"],
            ["SEO, hiệu năng, form và bản đồ", "01", "2-3 ngày", "SEO technical cơ bản"],
            ["Testing, sửa lỗi và bàn giao", "01", "2-3 ngày", "Tối đa 02 vòng chỉnh sửa nhỏ"],
        ],
        [78 * mm, 22 * mm, 28 * mm, 45 * mm],
        total_row=["TỔNG THỜI GIAN DỰ KIẾN", "", "18-25 ngày", "Không tính thời gian chờ feedback hoặc nội dung từ khách hàng"],
    ),
    P("QUY TRÌNH THỰC HIỆN HỢP ĐỒNG", "Section"),
    detail_table(
        ["Giai đoạn", "Hạng mục", "Mô tả"],
        [
            ["Giai đoạn 1", "Ký hợp đồng và tạm ứng", "Thanh toán trước 50% giá trị hợp đồng; tiếp nhận nội dung, tài khoản và thông tin cần thiết."],
            ["Giai đoạn 2", "Phân tích và triển khai", "Phân tích Figma, dựng cấu trúc WordPress, theme, component và các template."],
            ["Giai đoạn 3", "Kiểm thử và feedback", "Đưa website lên môi trường test để khách hàng kiểm tra; tiếp nhận tối đa 02 vòng chỉnh sửa nhỏ."],
            ["Giai đoạn 4", "Nghiệm thu và bàn giao", "Thanh toán 50% còn lại; bàn giao source code, tài khoản và hướng dẫn quản trị."],
        ],
        [28 * mm, 40 * mm, 105 * mm],
    ),
    PageBreak(),
]

# Page 6 - warranty, exclusions and closing.
story += [
    P("BẢO HÀNH VÀ ĐIỀU KHOẢN", "DocTitle"),
    P("9. Chính sách bảo hành và hỗ trợ", "Section"),
    bullet("Bảo hành kỹ thuật 30 ngày kể từ ngày nghiệm thu và bàn giao website."),
    bullet("Sửa lỗi phát sinh do code hoặc hiển thị không đúng phạm vi Figma đã thống nhất."),
    bullet("Hỗ trợ hướng dẫn quản trị WordPress và cập nhật nội dung cơ bản sau bàn giao."),
    bullet("Không áp dụng bảo hành cho lỗi do bên thứ ba, thay đổi hosting, tự ý sửa code, plugin không tương thích hoặc nội dung khách hàng nhập sai."),
    P("10. Hạng mục chưa bao gồm", "Section"),
    detail_table(
        ["Hạng mục", "Ghi chú"],
        [
            ["Hosting, tên miền, SSL trả phí", "Khách hàng cung cấp hoặc mua riêng theo nhà cung cấp mong muốn."],
            ["Nội dung và hình ảnh", "Khách hàng cung cấp nội dung, ảnh dự án, logo, video và thông tin đội ngũ."],
            ["Viết bài SEO", "Báo giá riêng theo số lượng và độ dài bài viết."],
            ["Đa ngôn ngữ", "Không nằm trong phạm vi gói 17 triệu; có thể báo giá bổ sung."],
            ["Booking, CRM, API hoặc thanh toán online", "Không bao gồm; báo giá riêng sau khi có đặc tả kỹ thuật."],
            ["SEO duy trì và backlink", "Không bao gồm SEO off-page, backlink, quảng cáo và cam kết thứ hạng."],
        ],
        [50 * mm, 123 * mm],
    ),
    P("11. Điều kiện phối hợp", "Section"),
    bullet("Báo giá có giá trị trong 07 ngày kể từ ngày gửi."),
    bullet("Tiến độ được tính từ khi nhận đủ tạm ứng, nội dung, hình ảnh và quyền truy cập cần thiết."),
    bullet("Mọi thay đổi ngoài Figma hoặc ngoài phạm vi nêu trên sẽ được xác nhận và báo giá bổ sung trước khi thực hiện."),
    bullet("Chi phí chưa bao gồm VAT nếu khách hàng yêu cầu xuất hóa đơn."),
    P("Trên đây là toàn bộ nội dung báo giá chuyển Figma sang WordPress cho dự án NHATO 2. Rất mong có cơ hội được đồng hành cùng Quý khách hàng.", "Closing"),
    P("--- XIN CHÂN THÀNH CẢM ƠN ---", "Closing"),
]

doc.build(story)
print(OUT)
