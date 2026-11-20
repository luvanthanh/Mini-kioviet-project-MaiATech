<?php
if (!defined('ABSPATH')) exit;
function mkv_cap($area='view') { return current_user_can('manage_options') || current_user_can('mkv_'.$area); }
function mkv_require($area='view') { if(!mkv_cap($area)) wp_die('Không có quyền truy cập.'); }
function mkv_roles_install() {
  foreach(['mkv_manager'=>'Manager','mkv_sales'=>'Sales','mkv_warehouse'=>'Warehouse'] as $key=>$label) {
    $caps=['read'=>true,'mkv_view'=>true];
    if($key==='mkv_manager') $caps+=['mkv_customers'=>true,'mkv_inventory'=>true,'mkv_orders'=>true,'mkv_reports'=>true,'mkv_settings'=>true];
    if($key==='mkv_sales') $caps+=['mkv_customers'=>true,'mkv_orders'=>true];
    if($key==='mkv_warehouse') $caps+=['mkv_inventory'=>true];
    add_role($key,$label,$caps);
    $role=get_role($key); if($role) foreach($caps as $c=>$v) $role->add_cap($c,$v);
  }
}
add_action('admin_menu',function(){
  $menus=['customers'=>['Khách hàng','mkv_customers'],'inventory'=>['Quản lý kho','mkv_inventory'],'orders'=>['Đơn hàng','mkv_orders'],'reports'=>['Báo cáo','mkv_reports'],'settings'=>['Cài đặt cửa hàng','mkv_settings'],'notifications'=>['Thông báo','mkv_view']];
  foreach($menus as $slug=>$row) add_submenu_page('mkv-dashboard',$row[0],$row[0],current_user_can('manage_options')?'manage_options':$row[1],'mkv-'.$slug,'mkv_page_'.$slug);
},20);
function mkv_notice($message,$type='success') { echo '<div class="notice notice-'.esc_attr($type).'"><p>'.esc_html($message).'</p></div>'; }
function mkv_post($key) {return sanitize_text_field(wp_unslash($_POST[$key]??''));}
function mkv_action($scope) {return $_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['mkv_action']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_mkv_nonce']??'')),'mkv_'.$scope);}
function mkv_form_start($scope,$action) {echo '<form method="post">';wp_nonce_field('mkv_'.$scope,'_mkv_nonce');echo '<input type="hidden" name="mkv_action" value="'.esc_attr($action).'">';}
function mkv_page_customers() {
  mkv_require('customers'); global $wpdb; $t=$wpdb->prefix.'mkv_customers';echo '<div class="wrap"><h1>Khách hàng</h1>';
  if(mkv_action('customers')) {
    $id=absint($_POST['id']??0);$action=mkv_post('mkv_action');
    if($action==='delete'){$wpdb->delete($t,['id'=>$id],['%d']);mkv_notice('Đã xóa khách hàng.');}
    else {
      $name=mkv_post('name');$email=sanitize_email(mkv_post('email'));$phone=mkv_post('phone');$address=sanitize_textarea_field(wp_unslash($_POST['address']??''));$points=max(0,intval($_POST['points']??0));
      if(!$name || ($email && !is_email($email))) mkv_notice('Tên bắt buộc; email phải hợp lệ.','error');
      else {$data=compact('name','email','phone','address','points');if($id)$wpdb->update($t,$data,['id'=>$id]);else {$data['created_at']=current_time('mysql');$wpdb->insert($t,$data);}mkv_notice('Đã lưu khách hàng.');}
    }
  }
  $edit=absint($_GET['edit']??0);$c=$edit?$wpdb->get_row($wpdb->prepare("SELECT * FROM $t WHERE id=%d",$edit),ARRAY_A):[];
  echo '<h2>'.($edit?'Sửa':'Thêm').' khách hàng</h2>';mkv_form_start('customers','save');echo '<input type="hidden" name="id" value="'.esc_attr($edit).'">';
  foreach(['name'=>'Họ tên','email'=>'Email','phone'=>'Số điện thoại','address'=>'Địa chỉ','points'=>'Điểm'] as $key=>$label) echo '<p><label>'.esc_html($label).' <input class="regular-text" name="'.esc_attr($key).'" value="'.esc_attr($c[$key]??'').'"></label></p>';
  submit_button('Lưu khách hàng');echo '</form><h2>Danh sách</h2>';
  $search=sanitize_text_field(wp_unslash($_GET['s']??''));$where='';if($search){$like='%'.$wpdb->esc_like($search).'%';$where=$wpdb->prepare(' WHERE name LIKE %s OR phone LIKE %s OR email LIKE %s',$like,$like,$like);}
  $page=max(1,absint($_GET['paged']??1));$offset=($page-1)*20;$rows=$wpdb->get_results("SELECT * FROM $t $where ORDER BY id DESC LIMIT 20 OFFSET $offset",ARRAY_A);$total=(int)$wpdb->get_var("SELECT COUNT(*) FROM $t $where");
  echo '<form method="get"><input type="hidden" name="page" value="mkv-customers"><input name="s" value="'.esc_attr($search).'" placeholder="Tìm tên, email, số điện thoại"><button class="button">Tìm</button></form><table class="widefat striped"><thead><tr><th>ID</th><th>Tên</th><th>Email</th><th>Phone</th><th>Điểm</th><th>Thao tác</th></tr></thead><tbody>';
  foreach($rows as $r){echo '<tr><td>'.esc_html($r['id']).'</td><td>'.esc_html($r['name']).'</td><td>'.esc_html($r['email']).'</td><td>'.esc_html($r['phone']).'</td><td>'.esc_html($r['points']).'</td><td><a href="'.esc_url(admin_url('admin.php?page=mkv-customers&edit='.$r['id'])).'">Sửa</a> ';mkv_form_start('customers','delete');echo '<input type="hidden" name="id" value="'.esc_attr($r['id']).'"><button class="button-link" onclick="return confirm(\'Xóa khách hàng?\')">Xóa</button></form></td></tr>';}
  echo '</tbody></table>';echo paginate_links(['base'=>add_query_arg('paged','%#%'),'current'=>$page,'total'=>max(1,ceil($total/20))]);echo '</div>';
}
function mkv_stock_change($product,$warehouse,$delta,$type,$reference=0) {
 global $wpdb;$inv=$wpdb->prefix.'mkv_inventory';$mov=$wpdb->prefix.'mkv_stock_movements';
 $wpdb->query($wpdb->prepare("INSERT INTO $inv (warehouse_id,product_id,quantity) VALUES (%d,%d,0) ON DUPLICATE KEY UPDATE id=id",$warehouse,$product));
 $current=$wpdb->get_var($wpdb->prepare("SELECT quantity FROM $inv WHERE warehouse_id=%d AND product_id=%d FOR UPDATE",$warehouse,$product));
 if($current===null || (int)$current+$delta<0){return false;}
 $ok=$wpdb->update($inv,['quantity'=>(int)$current+$delta],['warehouse_id'=>$warehouse,'product_id'=>$product]);
 $logged=$wpdb->insert($mov,['product_id'=>$product,'warehouse_id'=>$warehouse,'quantity_change'=>$delta,'movement_type'=>$type,'reference_id'=>$reference?:null,'created_at'=>current_time('mysql')]);
 if($ok===false || !$logged){return false;}$wpdb->query('COMMIT');return true;
}
function mkv_page_inventory(){
 mkv_require('inventory');global $wpdb;$wh=$wpdb->prefix.'mkv_warehouses';$inv=$wpdb->prefix.'mkv_inventory';echo '<div class="wrap"><h1>Quản lý kho</h1>';
 if(mkv_action('inventory')){
 $action=mkv_post('mkv_action');if($action==='warehouse'){$name=mkv_post('name');if($name){$wpdb->insert($wh,['name'=>$name]);mkv_notice('Đã tạo kho.');}}
 if($action==='movement'){$product=absint($_POST['product']??0);$source=absint($_POST['source']??0);$target=absint($_POST['target']??0);$qty=absint($_POST['quantity']??0);$type=mkv_post('type');
 if(!$qty||!get_post($product)||get_post_type($product)!=='mkv_product'||!$source||!in_array($type,['in','out','transfer'],true)||($type==='transfer'&&(!$target||$target===$source)))mkv_notice('Dữ liệu không hợp lệ.','error');
 elseif($type==='transfer'){
 $wpdb->query('START TRANSACTION');$a=mkv_stock_change($product,$source,-$qty,'transfer_out');$b=$a?mkv_stock_change($product,$target,$qty,'transfer_in'):false;
 if($a&&$b){$wpdb->query('COMMIT');mkv_notice('Đã chuyển kho.');}else{$wpdb->query('ROLLBACK');mkv_notice('Chuyển kho thất bại.','error');}
 }else{$wpdb->query('START TRANSACTION');$ok=mkv_stock_change($product,$source,$type==='in'?$qty:-$qty,$type);$wpdb->query($ok?'COMMIT':'ROLLBACK');mkv_notice($ok?'Đã cập nhật tồn kho.':'Không đủ tồn kho hoặc có lỗi.',$ok?'success':'error');}
 }
 }
 echo '<h2>Thêm kho</h2>';mkv_form_start('inventory','warehouse');echo '<input name="name" required placeholder="Tên kho"> ';submit_button('Tạo kho','secondary','submit',false);echo '</form>';
 $warehouses=$wpdb->get_results("SELECT * FROM $wh ORDER BY id",ARRAY_A);$products=get_posts(['post_type'=>'mkv_product','post_status'=>'publish','numberposts'=>-1]);
 echo '<h2>Nhập / Xuất / Chuyển kho</h2>';mkv_form_start('inventory','movement');echo '<p><select name="type"><option value="in">Nhập kho</option><option value="out">Xuất kho</option><option value="transfer">Chuyển kho</option></select> <select name="product">';foreach($products as $p)echo '<option value="'.esc_attr($p->ID).'">'.esc_html($p->post_title).'</option>';echo '</select></p><p>Kho nguồn/nhập: <select name="source">';foreach($warehouses as $w)echo '<option value="'.esc_attr($w['id']).'">'.esc_html($w['name']).'</option>';echo '</select> Kho đích: <select name="target"><option value="0">Không áp dụng</option>';foreach($warehouses as $w)echo '<option value="'.esc_attr($w['id']).'">'.esc_html($w['name']).'</option>';echo '</select> Số lượng: <input type="number" name="quantity" min="1" required></p>';submit_button('Thực hiện');echo '</form>';
 $rows=$wpdb->get_results("SELECT i.*,w.name AS warehouse,p.post_title AS product FROM $inv i JOIN $wh w ON w.id=i.warehouse_id JOIN {$wpdb->posts} p ON p.ID=i.product_id ORDER BY w.name,p.post_title",ARRAY_A);
 echo '<h2>Tồn kho</h2><table class="widefat striped"><tr><th>Kho</th><th>Sản phẩm</th><th>Số lượng</th></tr>';foreach($rows as $r)echo '<tr><td>'.esc_html($r['warehouse']).'</td><td>'.esc_html($r['product']).'</td><td>'.esc_html($r['quantity']).'</td></tr>';echo '</table></div>';
}
function mkv_page_orders(){
 mkv_require('orders');global $wpdb;$orders=$wpdb->prefix.'mkv_orders';$items=$wpdb->prefix.'mkv_order_items';$customers=$wpdb->prefix.'mkv_customers';$inv=$wpdb->prefix.'mkv_inventory';echo '<div class="wrap"><h1>Đơn hàng</h1>';
 if(mkv_action('orders')){
 $action=mkv_post('mkv_action');$id=absint($_POST['id']??0);
 if($action==='create'){
 $customer=absint($_POST['customer']??0);$product=absint($_POST['product']??0);$qty=absint($_POST['quantity']??0);$warehouse=absint($_POST['warehouse']??0);$price=(float)get_post_meta($product,'_mkv_sale_price',true);
 if(!$product||get_post_type($product)!=='mkv_product'||!$qty||!$warehouse||$price<=0)mkv_notice('Sản phẩm, kho, số lượng hoặc giá không hợp lệ.','error');
 else{$wpdb->query('START TRANSACTION');$ok=$wpdb->insert($orders,['customer_id'=>$customer?:null,'status'=>'draft','total'=>$price*$qty,'created_at'=>current_time('mysql')]);$id=$wpdb->insert_id;$ok2=$ok?$wpdb->insert($items,['order_id'=>$id,'product_id'=>$product,'quantity'=>$qty,'unit_price'=>$price]):false;if($ok&&$ok2){$wpdb->query('COMMIT');mkv_notice('Đã tạo đơn nháp #'.$id.'. Chọn kho khi thanh toán.');}else{$wpdb->query('ROLLBACK');mkv_notice('Không tạo được đơn.','error');}}
 }
 if($action==='status'){
 $new=mkv_post('status');$warehouse=absint($_POST['warehouse']??0);$order=$wpdb->get_row($wpdb->prepare("SELECT * FROM $orders WHERE id=%d",$id));$valid=['draft'=>['pending','cancelled'],'pending'=>['paid','cancelled'],'paid'=>['shipping','completed','cancelled'],'shipping'=>['completed','cancelled'],'completed'=>[],'cancelled'=>[]];
 if(!$order||!in_array($new,$valid[$order->status]??[],true))mkv_notice('Chuyển trạng thái không hợp lệ.','error');
 elseif($new==='paid'){
 $lines=$wpdb->get_results($wpdb->prepare("SELECT * FROM $items WHERE order_id=%d",$id));$wpdb->query('START TRANSACTION');$ok=$warehouse>0;
 foreach($lines as $line){if(!$ok)break;$ok=mkv_stock_change((int)$line->product_id,$warehouse,-(int)$line->quantity,'sale',$id);}
 if($ok){$wpdb->update($orders,['status'=>'paid','paid_at'=>current_time('mysql')],['id'=>$id]);$wpdb->query('COMMIT');mkv_notice('Đã thanh toán và trừ kho.');}else{$wpdb->query('ROLLBACK');mkv_notice('Không đủ tồn kho.','error');}
 }elseif($new==='cancelled'&&in_array($order->status,['paid','shipping'],true)){
 $mov=$wpdb->prefix.'mkv_stock_movements';$lines=$wpdb->get_results($wpdb->prepare("SELECT product_id,warehouse_id,quantity_change FROM $mov WHERE reference_id=%d AND movement_type='sale'",$id));$wpdb->query('START TRANSACTION');$ok=true;foreach($lines as $line){if(!mkv_stock_change((int)$line->product_id,(int)$line->warehouse_id,abs((int)$line->quantity_change),'return',$id)){$ok=false;break;}}if($ok){$wpdb->update($orders,['status'=>'cancelled'],['id'=>$id]);$wpdb->query('COMMIT');mkv_notice('Đã hủy và hoàn kho.');}else{$wpdb->query('ROLLBACK');mkv_notice('Không thể hoàn kho.','error');}
 }else{$wpdb->update($orders,['status'=>$new],['id'=>$id]);mkv_notice('Đã cập nhật trạng thái.');}
 }
 }
 $customers_rows=$wpdb->get_results("SELECT id,name FROM $customers ORDER BY id DESC LIMIT 500",ARRAY_A);$warehouses=$wpdb->get_results("SELECT id,name FROM {$wpdb->prefix}mkv_warehouses",ARRAY_A);$products=get_posts(['post_type'=>'mkv_product','post_status'=>'publish','numberposts'=>-1]);
 echo '<h2>Tạo đơn hàng (một sản phẩm mỗi lần)</h2>';mkv_form_start('orders','create');echo '<p>Khách: <select name="customer"><option value="0">Khách lẻ</option>';foreach($customers_rows as $r)echo '<option value="'.esc_attr($r['id']).'">'.esc_html($r['name']).'</option>';echo '</select> Sản phẩm: <select name="product">';foreach($products as $p)echo '<option value="'.esc_attr($p->ID).'">'.esc_html($p->post_title).'</option>';echo '</select> Số lượng: <input name="quantity" type="number" min="1" value="1"> Kho: <select name="warehouse">';foreach($warehouses as $w)echo '<option value="'.esc_attr($w['id']).'">'.esc_html($w['name']).'</option>';echo '</select></p>';submit_button('Tạo đơn nháp');echo '</form>';
 $rows=$wpdb->get_results("SELECT o.*,c.name AS customer FROM $orders o LEFT JOIN $customers c ON c.id=o.customer_id ORDER BY o.id DESC LIMIT 100",ARRAY_A);
 echo '<h2>Danh sách đơn</h2><table class="widefat striped"><tr><th>ID</th><th>Khách</th><th>Trạng thái</th><th>Tổng tiền</th><th>Ngày tạo</th><th>Cập nhật</th></tr>';
 foreach($rows as $r){echo '<tr><td>#'.esc_html($r['id']).'</td><td>'.esc_html($r['customer']?:'Khách lẻ').'</td><td>'.esc_html($r['status']).'</td><td>'.esc_html(number_format((float)$r['total'])).' đ</td><td>'.esc_html($r['created_at']).'</td><td>';mkv_form_start('orders','status');echo '<input type="hidden" name="id" value="'.esc_attr($r['id']).'"><select name="status">';foreach(['draft','pending','paid','shipping','completed','cancelled'] as $s)echo '<option value="'.esc_attr($s).'" '.selected($r['status'],$s,false).'>'.esc_html(ucfirst($s)).'</option>';echo '</select> Kho: <select name="warehouse">';foreach($warehouses as $w)echo '<option value="'.esc_attr($w['id']).'">'.esc_html($w['name']).'</option>';echo '</select> <button class="button">Lưu</button></form></td></tr>';}
 echo '</table></div>';
}
function mkv_page_reports(){mkv_require('reports');global $wpdb;$t=$wpdb->prefix.'mkv_orders';$period=sanitize_text_field($_GET['period']??'month');$period=in_array($period,['day','week','month','year'],true)?$period:'month';$start=['day'=>date('Y-m-d 00:00:00',current_time('timestamp')),'week'=>date('Y-m-d 00:00:00',strtotime('monday this week',current_time('timestamp'))),'month'=>date('Y-m-01 00:00:00',current_time('timestamp')),'year'=>date('Y-01-01 00:00:00',current_time('timestamp'))][$period];$stats=$wpdb->get_row($wpdb->prepare("SELECT COUNT(*) AS orders,COALESCE(SUM(total),0) AS revenue FROM $t WHERE status IN ('paid','shipping','completed') AND paid_at >= %s",$start));$customers=(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}mkv_customers WHERE created_at >= %s",$start));echo '<div class="wrap"><h1>Báo cáo</h1><form><input type="hidden" name="page" value="mkv-reports"><select name="period">';foreach(['day'=>'Ngày','week'=>'Tuần','month'=>'Tháng','year'=>'Năm'] as $k=>$v)echo '<option value="'.esc_attr($k).'" '.selected($period,$k,false).'>'.esc_html($v).'</option>';echo '</select> <button class="button">Xem</button></form><p>Doanh thu: <strong>'.esc_html(number_format((float)$stats->revenue)).' đ</strong></p><p>Số đơn đã thanh toán: '.esc_html($stats->orders).'</p><p>Khách hàng mới: '.esc_html($customers).'</p><h2>Sản phẩm bán chạy</h2>';$top=$wpdb->get_results($wpdb->prepare("SELECT p.post_title,SUM(i.quantity) qty FROM {$wpdb->prefix}mkv_order_items i JOIN $t o ON o.id=i.order_id JOIN {$wpdb->posts} p ON p.ID=i.product_id WHERE o.status IN ('paid','shipping','completed') AND o.paid_at >= %s GROUP BY i.product_id,p.post_title ORDER BY qty DESC LIMIT 10",$start));foreach($top as $r)echo '<p>'.esc_html($r->post_title).' — '.esc_html($r->qty).'</p>';echo '</div>';}
function mkv_page_settings(){mkv_require('settings');echo '<div class="wrap"><h1>Cài đặt cửa hàng</h1>';if(mkv_action('settings')){foreach(['name','logo','vat','currency','address','email'] as $k){$v=mkv_post($k);if($k==='email')$v=sanitize_email($v);if($k==='logo')$v=esc_url_raw($v);if($k==='vat')$v=(string)max(0,(float)$v);update_option('mkv_store_'.$k,$v);}mkv_notice('Đã lưu cài đặt.');}mkv_form_start('settings','save');foreach(['name'=>'Tên cửa hàng','logo'=>'URL logo (Media Library)','vat'=>'VAT (%)','currency'=>'Tiền tệ','address'=>'Địa chỉ','email'=>'Email'] as $k=>$label)echo '<p><label>'.esc_html($label).' <input class="regular-text" name="'.esc_attr($k).'" value="'.esc_attr(get_option('mkv_store_'.$k,$k==='currency'?'VND':'')).'"></label></p>';submit_button('Lưu');echo '</form></div>';}
function mkv_page_notifications(){mkv_require('view');global $wpdb;$inv=$wpdb->prefix.'mkv_inventory';$orders=$wpdb->prefix.'mkv_orders';$low=$wpdb->get_results("SELECT p.post_title,COALESCE(SUM(i.quantity),0) qty FROM {$wpdb->posts} p LEFT JOIN $inv i ON i.product_id=p.ID WHERE p.post_type='mkv_product' AND p.post_status='publish' GROUP BY p.ID,p.post_title HAVING qty<=5 LIMIT 50");$new=$wpdb->get_results("SELECT id,created_at FROM $orders WHERE created_at >= DATE_SUB(NOW(),INTERVAL 1 DAY) ORDER BY id DESC LIMIT 20");echo '<div class="wrap"><h1>Thông báo</h1><h2>Đơn mới (24 giờ)</h2>';foreach($new as $r)echo '<p>Đơn #'.esc_html($r->id).' — '.esc_html($r->created_at).'</p>';echo '<h2>Hết hàng / tồn kho thấp (≤ 5)</h2>';foreach($low as $r)echo '<p>'.esc_html($r->post_title).' — '.esc_html($r->qty).'</p>';echo '</div>';}
