document.addEventListener('DOMContentLoaded',()=>{
  let openMenu=null;
  document.querySelectorAll('.action-btn').forEach(btn=>{
    const menu = btn.parentElement.querySelector('.actions-menu');
    btn.addEventListener('click',e=>{
      e.stopPropagation();
      if(openMenu && openMenu!==menu){openMenu.classList.remove('show');}
      menu.classList.toggle('show');
      openMenu = menu.classList.contains('show') ? menu : null;
    });
  });
  document.addEventListener('click',()=>{ if(openMenu){openMenu.classList.remove('show');openMenu=null;}});
});
