<?php if (!defined('ABSPATH')) exit; ?>
<div class="wrap mkv-wrap">
  <h1>Mini KiotViet — Dashboard</h1>
  <p class="description">Thống kê dữ liệu thực từ hệ thống. Chưa có đơn hàng sẽ hiển thị 0.</p>
  <div class="mkv-stats" id="mkv-stats">
    <div class="mkv-card"><span>Doanh thu hôm nay</span><strong data-stat="today_revenue">—</strong></div>
    <div class="mkv-card"><span>Số đơn hôm nay</span><strong data-stat="today_orders">—</strong></div>
    <div class="mkv-card"><span>Tổng sản phẩm</span><strong data-stat="total_products">—</strong></div>
    <div class="mkv-card"><span>Tổng khách hàng</span><strong data-stat="total_customers">—</strong></div>
    <div class="mkv-card"><span>Tổng doanh thu</span><strong data-stat="total_revenue">—</strong></div>
  </div>
  <div class="mkv-panel"><h2>Doanh thu 7 ngày gần nhất</h2><div class="mkv-chart"><canvas id="mkv-revenue-chart"></canvas></div></div>
  <div class="mkv-widgets">
    <div class="mkv-panel"><h2>Top sản phẩm</h2><div id="mkv-top-products">Đang tải...</div></div>
    <div class="mkv-panel"><h2>Top khách hàng</h2><div id="mkv-top-customers">Đang tải...</div></div>
    <div class="mkv-panel"><h2>Hoạt động gần đây</h2><div id="mkv-recent">Đang tải...</div></div>
    <div class="mkv-panel"><h2>Sản phẩm sắp hết hàng</h2><div id="mkv-low-stock">Đang tải...</div></div>
  </div>
  <p id="mkv-error" role="alert"></p>
</div>
