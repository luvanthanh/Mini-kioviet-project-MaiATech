(async function(){
  const currency = n => new Intl.NumberFormat('vi-VN',{style:'currency',currency:'VND',maximumFractionDigits:0}).format(Number(n)||0);
  const integer = n => new Intl.NumberFormat('vi-VN').format(Number(n)||0);
  function renderList(id,rows,format){
    const el=document.getElementById(id); el.replaceChildren();
    if(!rows.length){el.textContent='Chưa có dữ liệu';return;}
    const ul=document.createElement('ul');ul.className='mkv-list';
    rows.forEach(row=>{const li=document.createElement('li');const left=document.createElement('span');const right=document.createElement('strong');const pair=format(row);left.textContent=pair[0];right.textContent=pair[1];li.append(left,right);ul.append(li)});
    el.append(ul);
  }
  try{
    const response=await fetch(mkvSettings.endpoint,{headers:{'X-WP-Nonce':mkvSettings.nonce},credentials:'same-origin'});
    if(!response.ok)throw new Error('REST API trả về '+response.status);
    const d=await response.json();
    for(const key of ['today_revenue','today_orders','total_products','total_customers','total_revenue']){
      document.querySelector(`[data-stat="${key}"]`).textContent=key.includes('revenue')?currency(d[key]):integer(d[key]);
    }
    renderList('mkv-top-products',d.top_products,r=>[r.name,integer(r.qty)+' đã bán']);
    renderList('mkv-top-customers',d.top_customers,r=>[r.name,currency(r.spent)]);
    renderList('mkv-recent',d.recent,r=>['Đơn #'+r.id+' — '+r.status,currency(r.total)]);
    renderList('mkv-low-stock',d.low_stock,r=>[r.name,integer(r.quantity)+' còn lại']);
    if(typeof Chart==='undefined')throw new Error('Không tải được Chart.js. Kiểm tra kết nối Internet.');
    new Chart(document.getElementById('mkv-revenue-chart'),{
      type:'line',data:{labels:d.chart.map(x=>x.date.slice(5)),datasets:[{label:'Doanh thu (VND)',data:d.chart.map(x=>x.revenue),borderColor:'#2271b1',backgroundColor:'rgba(34,113,177,.12)',fill:true,tension:.3}]},
      options:{responsive:true,maintainAspectRatio:false,scales:{y:{beginAtZero:true}},plugins:{tooltip:{callbacks:{label:c=>currency(c.parsed.y)}}}}
    });
  }catch(e){document.getElementById('mkv-error').textContent='Không tải được Dashboard: '+e.message;}
})();
