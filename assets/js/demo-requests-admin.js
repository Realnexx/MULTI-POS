(function(){'use strict';document.addEventListener('DOMContentLoaded',function(){
const cfg=window.demoRequestsConfig;if(!cfg||!window.jQuery||!jQuery.fn.DataTable)return;
const escape=v=>String(v??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const text=jQuery.fn.dataTable.render.text();
const table=jQuery('#demo-requests-table').DataTable({processing:true,serverSide:true,searchDelay:400,pageLength:10,lengthMenu:[10,25,50,100],order:[[0,'desc']],ajax:{url:cfg.endpoint,data:d=>{d.status=document.getElementById('demo-status-filter').value;},error:()=>{document.getElementById('demo-table-error').hidden=false;}},columns:[
{data:'id',render:d=>'#'+Number(d)},
{data:'business_name',render:text},
{data:'full_name',render:(v,t,r)=>t==='display'?'<strong>'+escape(v)+'</strong><div class="small text-muted">'+escape(r.email)+'<br>'+escape(r.phone)+'</div>':v},
{data:'business_type',render:text},{data:'branch_count',render:d=>Number(d)},
{data:'status',render:v=>{const status=['new','contacted','closed'].includes(v)?v:'new';const icon=status==='new'?'bell':status==='contacted'?'message-circle':'circle-check';return '<span class="demo-request-status status-'+status+'"><i class="ti ti-'+icon+'" aria-hidden="true"></i>'+escape(status[0].toUpperCase()+status.slice(1))+'</span>';}},
{data:'created_at',render:(v,t)=>{if(t!=='display')return v;const date=new Date(v.replace(' ','T')+'Z');return Number.isNaN(date.getTime())?escape(v):escape(date.toLocaleString('en-PH',{timeZone:'Asia/Manila',dateStyle:'medium',timeStyle:'short'}));}},
{data:null,orderable:false,searchable:false,render:()=>'<button type="button" class="btn btn-sm btn-outline-primary demo-view-request"><i class="ti ti-eye" aria-hidden="true"></i> View request</button>'}
],language:{emptyTable:'No demo requests yet.',zeroRecords:'No requests match your search.'}});
const modalElement=document.getElementById('demo-request-modal');
jQuery('#demo-requests-table').on('click','.demo-view-request',function(){
 const request=table.row(jQuery(this).closest('tr')).data();if(!request)return;
 const set=(field,value)=>{modalElement.querySelector('[data-request-field="'+field+'"]').textContent=value??'';};
 set('id','Request #'+Number(request.id));set('business',request.business_name);set('name',request.full_name);set('email',request.email);set('phone',request.phone);set('type',request.business_type);set('branches',Number(request.branch_count)+' '+(Number(request.branch_count)===1?'branch':'branches'));set('message',request.message||'No additional details provided.');
 const date=new Date(request.created_at.replace(' ','T')+'Z');set('submitted',Number.isNaN(date.getTime())?request.created_at:date.toLocaleString('en-PH',{timeZone:'Asia/Manila',dateStyle:'medium',timeStyle:'short'})+' (Philippine time)');
 const status=['new','contacted','closed'].includes(request.status)?request.status:'new';
 const idField=modalElement.querySelector('[name=id]'),statusField=modalElement.querySelector('[name=status]');if(idField)idField.value=Number(request.id);if(statusField)statusField.value=status;
 const badge=modalElement.querySelector('[data-request-field=status]');badge.textContent=status[0].toUpperCase()+status.slice(1);badge.className='demo-request-status status-'+status;
 bootstrap.Modal.getOrCreateInstance(modalElement).show();
});
jQuery('#demo-status-filter').on('change',()=>table.ajax.reload());
jQuery('#demo-requests-table').on('xhr.dt',function(e,settings,json){if(!json)return;document.getElementById('demo-table-error').hidden=true;const stats=document.querySelectorAll('.demo-admin-stat strong');const s=json.summary;if(s)[s.total,s.new,s.contacted,s.closed].forEach((n,i)=>{if(stats[i])stats[i].textContent=n;});});
});})();
