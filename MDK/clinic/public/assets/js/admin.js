document.addEventListener("DOMContentLoaded",function(){
 if(!document.querySelector(".admin-shell"))return;
 document.addEventListener("submit",function(event){
  const form=event.target.closest("form[data-confirm]");
  if(form&&!window.confirm(form.dataset.confirm))event.preventDefault();
 });
 const area=document.createElement("div");area.className="admin-toast-area";area.setAttribute("aria-live","polite");document.body.appendChild(area);
 const seen=new Set();
 function poll(){
  const shell=document.querySelector(".admin-shell");
  fetch(shell.dataset.notificationsUrl,{credentials:"same-origin",headers:{"Accept":"application/json"}})
   .then(r=>r.ok?r.json():null).then(data=>{
    if(!data||!Array.isArray(data.items))return;
    data.items.forEach(item=>{
     const id=String(item.id);if(seen.has(id))return;seen.add(id);
     const a=document.createElement("a");a.className="admin-toast";a.href=shell.dataset.scheduleUrl;a.dataset.notificationId=id;
     a.addEventListener("click",function(event){
      event.preventDefault();
      const body=new URLSearchParams();body.set("csrf_token",shell.dataset.csrf);
      fetch(shell.dataset.notificationsUrl+"/"+id+"/read",{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/x-www-form-urlencoded","Accept":"application/json"},body})
       .catch(()=>{}).finally(()=>{location.href=a.href;});
     });
     const strong=document.createElement("strong");strong.textContent="Новая заявка на приём";
     const span=document.createElement("span");span.textContent=item.last_name+" "+item.first_name+" · "+item.slot_date+" "+String(item.slot_time).slice(0,5);
     a.appendChild(strong);a.appendChild(span);area.appendChild(a);
     setTimeout(()=>{a.remove();},9000);
    });
   }).catch(()=>{});
 }
 poll();setInterval(poll,20000);
});
