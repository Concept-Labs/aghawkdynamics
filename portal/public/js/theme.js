document.addEventListener('DOMContentLoaded', () => {
  const themes = {
    brand: '#7c8033',
    light:'#d0d0d0',
    dark:'#444',
    turquoise:'#41bfbf',
    purple:'#7f4bc9',
    green:'#4ba35c',
    blue:'#3b6cf0',
    red:'#d94141'
  };

  const body = document.body;
  const toggle = document.getElementById('themeToggle');
  if(!toggle) return;
  const menu = document.getElementById('themeMenu');
  const current = toggle.querySelector('.theme-current');
  const buttons = menu.querySelectorAll('.theme-btn');

  function apply(t){
    // remove previous theme-* classes
    body.classList.forEach(cls=>{ if(cls.startsWith('theme-')) body.classList.remove(cls);});
    body.classList.add('theme-'+t);
    localStorage.setItem('theme',t);
    buttons.forEach(b=>{
      const active=b.dataset.theme===t;
      b.classList.toggle('active',active);
      if(active) current.style.background=themes[t];
    });
  }

  // set colors
  buttons.forEach(b=>{
    const t=b.dataset.theme;
    b.style.background=themes[t];
  });

  const saved=localStorage.getItem('theme')||'brand';
  apply(saved);

  buttons.forEach(btn=>{
    btn.addEventListener('click',e=>{
      apply(btn.dataset.theme);
      menu.classList.remove('show');
      e.stopPropagation();
    });
  });
  current.addEventListener('click',e=>{
    menu.classList.toggle('show');e.stopPropagation();
  });
  document.addEventListener('click',()=>menu.classList.remove('show'));
  // hamburger
  const ham=document.querySelector('.hamburger');const nav=document.querySelector('nav');
  if(ham&&nav){ham.addEventListener('click',e=>{ham.classList.toggle('open');nav.classList.toggle('open');e.stopPropagation();});}
});
