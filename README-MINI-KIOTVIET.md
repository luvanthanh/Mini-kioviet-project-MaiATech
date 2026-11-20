# Mini KiotViet — bản chuyển đổi từ Assignment 04

## Mục tiêu
Bản chuyển đổi sử dụng **mã nguồn WordPress của Assignment_04_Internship** làm nền, bổ sung plugin `mini-kiotviet` dành cho quản trị bán hàng. Theme và plugin nội thất cũ được giữ để tham khảo, **không được dùng làm nguồn dữ liệu Mini KiotViet**. Không dùng WooCommerce cho luồng đơn hàng mới.

## Cài trên XAMPP
1. Giải nén toàn bộ thư mục `mini-kiotviet-project` vào `C:\xampp\htdocs\`.
2. Tạo database MySQL **mới, rỗng** tên `mini_kiotviet_converted` bằng phpMyAdmin. **Không import `furniture_db.sql` của Assignment 04** vào DB mới.
3. Mở `http://localhost/mini-kiotviet-project/` và cài WordPress; DB user XAMPP mặc định `root`, mật khẩu trống nếu chưa thay đổi.
4. Đăng nhập `/wp-admin/` → Plugins → kích hoạt **Mini KiotViet**. Không cần kích hoạt WooCommerce hay furnitureecommerse-core.
5. Vào Mini KiotViet → Sản phẩm để tạo sản phẩm, tiếp theo Khách hàng, Quản lý kho, Đơn hàng, Dashboard, Báo cáo.
6. Nếu đã cài WordPress khác trước đó, **sao lưu DB và files** trước khi thay đổi. Không chép đè lên dự án đang chạy.

## Mức độ hoàn thiện
- GĐ1: Dashboard 5 số liệu, Chart.js, 4 widget từ REST API, CSS responsive (cần kiểm thử trực tiếp).
- GĐ2: CPT sản phẩm, meta SKU/barcode/giá, ảnh, danh mục, tìm kiếm, lọc, sắp xếp, pagination, bulk delete, CSV; xem `includes/products.php`.
- GĐ3: Tạo kho, nhập/xuất/chuyển kho, bảng inventory và lịch sử movement.
- GĐ4: CRUD khách hàng, tìm kiếm, điểm; lịch sử mua chưa có màn hình chi tiết riêng.
- GĐ5: Tạo đơn **một sản phẩm/đơn**, trạng thái draft/pending/paid/shipping/completed/cancelled, trừ kho khi paid, hoàn kho khi hủy paid/shipping. Chưa hỗ trợ đơn nhiều sản phẩm hoặc chỉnh sửa chi tiết đơn; không tích hợp cổng thanh toán.
- GĐ6: Báo cáo ngày/tuần/tháng/năm, doanh thu, đơn, khách mới, sản phẩm bán chạy.
- GĐ7: Đăng ký roles Manager/Sales/Warehouse, capability cơ bản; **chưa hoàn thiện phân quyền CPT sản phẩm và mọi endpoint**.
- GĐ8: Settings logo URL, tên cửa hàng, VAT, tiền tệ, địa chỉ, email; chưa áp dụng VAT vào phép tính đơn hàng.
- GĐ9: Trang thông báo dạng danh sách khi truy cập, chưa có thông báo đẩy hoặc tự động theo sự kiện.

**Đây là bản prototype chuyển đổi có code thực thi, không phải bản đã nghiệm thu 100% Test 3.** Chưa chạy kiểm thử tích hợp trên XAMPP/MySQL thực tế. Không sử dụng cho bán hàng thật trước khi kiểm thử giao dịch, phân quyền, tính nhất quán kho, bảo mật và sao lưu.

## Kiểm tra nhanh
- Tạo sản phẩm với SKU/barcode/giá hợp lệ; thử SKU trùng.
- Tạo kho A/B, nhập 10 sản phẩm vào A, chuyển 3 sang B; xác nhận A=7, B=3.
- Tạo khách hàng; tạo đơn 1 sản phẩm; chuyển Draft→Pending→Paid; xác nhận trừ kho đúng 1 lần.
- Hủy đơn Paid; xác nhận hoàn kho.
- Xem Dashboard và Reports sau khi thanh toán.
