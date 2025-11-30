// main.js — small UI helpers
document.addEventListener('DOMContentLoaded', function(){
const btn = document.getElementById('sidebarToggle');
const sidebar = document.getElementById('sidebar');
if(btn && sidebar){
btn.addEventListener('click', ()=>{
sidebar.classList.toggle('show');
});
}


// Add small animation when main content loads
const content = document.getElementById('content-area');
if(content){
content.style.opacity = 0;
setTimeout(()=> content.style.transition = 'opacity .35s ease-in-out', 50);
setTimeout(()=> content.style.opacity = 1, 80);
}
});