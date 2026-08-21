/* ============================================================
   PREMMARG BLOG — Core App Logic
   ============================================================ */

/* ===== LOTUS PARTICLES ===== */
function initLotusParticles() {
  const container = document.querySelector('.lotus-particles');
  if (!container) return;
  const symbols = ['🪷'];
  const count = window.innerWidth < 640 ? 8 : 14;
  for (let i = 0; i < count; i++) {
    const el = document.createElement('span');
    el.className = 'lotus-p';
    el.textContent = symbols[Math.floor(Math.random() * symbols.length)];
    el.style.cssText = `
      left: ${Math.random() * 100}%;
      font-size: ${0.7 + Math.random() * 0.8}rem;
      animation-duration: ${18 + Math.random() * 22}s;
      animation-delay: ${Math.random() * -30}s;
    `;
    container.appendChild(el);
  }
}

/* ===== NAVBAR SCROLL ===== */
function initNavbar() {
  const navbar = document.querySelector('.navbar');
  if (!navbar) return;

  window.addEventListener('scroll', () => {
    navbar.classList.toggle('scrolled', window.scrollY > 40);
  }, { passive: true });

  // Mobile hamburger
  const hamburger = document.querySelector('.nav-hamburger');
  const navLinks  = document.querySelector('.nav-links');
  if (hamburger && navLinks) {
    hamburger.addEventListener('click', () => {
      navLinks.classList.toggle('open');
      const isOpen = navLinks.classList.contains('open');
      hamburger.setAttribute('aria-expanded', isOpen);
    });
    navLinks.addEventListener('click', (e) => {
      if (e.target.tagName === 'A') navLinks.classList.remove('open');
    });
  }

  // Active link
  const path = window.location.pathname.split('/').pop() || 'index.html';
  document.querySelectorAll('.nav-links a').forEach(a => {
    const href = a.getAttribute('href');
    if (href === path || (path === '' && href === 'index.html')) {
      a.classList.add('active');
    }
  });
}

/* ===== SCROLL REVEAL ===== */
function initScrollReveal() {
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.classList.add('visible');
        observer.unobserve(e.target);
      }
    });
  }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

  document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
}

/* ===== TOAST ===== */
function showToast(message, duration = 2800) {
  let toast = document.getElementById('toast');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'toast';
    document.body.appendChild(toast);
  }
  toast.textContent = message;
  toast.classList.add('show');
  clearTimeout(toast._timer);
  toast._timer = setTimeout(() => toast.classList.remove('show'), duration);
}

/* ===== URL PARAMS ===== */
function getParam(name) {
  return new URLSearchParams(window.location.search).get(name);
}

/* ===== COPY TO CLIPBOARD ===== */
function copyLink(text) {
  if (navigator.clipboard) {
    navigator.clipboard.writeText(text).then(() => showToast('🔗 Link copied to clipboard!'));
  } else {
    const el = document.createElement('textarea');
    el.value = text;
    document.body.appendChild(el);
    el.select();
    document.execCommand('copy');
    document.body.removeChild(el);
    showToast('🔗 Link copied!');
  }
}

/* ===== WHATSAPP SHARE ===== */
function shareWhatsApp(title, url) {
  const text = encodeURIComponent(`🙏 *${title}*\n\nRead the Bhagwat Kathā summary:\n${url}\n\n~ Premmarg`);
  window.open(`https://wa.me/?text=${text}`, '_blank');
}

/* ===== RENDER HELPERS ===== */

/** Builds a Saptah card element */
function buildSaptahCard(saptah) {
  const days = Data.getDays(saptah.id);
  const posts = Data.getPosts({ saptah_id: saptah.id });

  const dots = days.map(d => {
    const hasPosts = Data.getPostsForDay(d.id).length > 0;
    return `<span class="day-dot ${hasPosts ? 'filled' : ''}" title="Day ${d.day_number}">${d.day_number}</span>`;
  }).join('');

  return `
    <a href="katha.html?slug=${saptah.slug}" class="glass-card glass-card-link">
      <div class="saptah-card">
        <div class="saptah-year-tag">📍 ${saptah.location} &nbsp;·&nbsp; ${saptah.year}</div>
        <h3 class="saptah-card-title">${saptah.title}</h3>
        <p class="saptah-card-location font-hindi">${saptah.title_hi}</p>
        <p class="saptah-card-desc">${saptah.description}</p>
        <div class="saptah-card-footer">
          <div class="day-dots">${dots}</div>
          <span class="btn-ghost">Read Kathā <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg></span>
        </div>
      </div>
    </a>`;
}

/** Builds a Post card element */
function buildPostCard(post, options = {}) {
  const day  = Data.getDayForPost(post);
  const saptah = Data.getSaptahForPost(post);
  const excerpt = post.meta_description || stripHtml(post.content).slice(0, 140) + '…';

  return `
    <a href="post.html?slug=${post.slug}" class="glass-card glass-card-link post-card ${options.className || ''}">
      <div class="post-card-tags">
        ${day   ? `<span class="tag tag-day">Day ${day.day_number}</span>` : ''}
        ${post.featured ? `<span class="tag tag-featured">⭐ Featured</span>` : ''}
        ${post.content_hi ? `<span class="tag tag-hindi">हिंदी</span>` : ''}
      </div>
      <h3 class="post-card-title">${post.title}</h3>
      <p class="post-card-excerpt">${excerpt}</p>
      <span class="post-card-read">
        Read Summary
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </span>
    </a>`;
}

/** Strip HTML tags */
function stripHtml(html) {
  const d = document.createElement('div');
  d.innerHTML = html || '';
  return d.textContent || '';
}

/* ===== PAGE INITIALISERS ===== */

/* --- INDEX PAGE --- */
const DB_QUOTES = [
  {
    hi: "कमल के समान बनो—संसार के कीचड़ में रहकर भी अपनी पवित्रता और सुगंध को कभी मत खोना।",
    en: "Be like a lotus—even when living in the mud of the world, never lose your purity and fragrance.",
    source: "श्री गुरुदेव भगवान (Gurudev Bhagwan)"
  },
  {
    hi: "भक्ति के बिना ज्ञान केवल एक भार है, और प्रेम के बिना जीवन केवल एक मरुस्थल है।",
    en: "Wisdom without devotion is merely a burden, and life without love is nothing but a desert.",
    source: "श्री गुरुदेव भगवान (Gurudev Bhagwan)"
  },
  {
    hi: "भगवान नाम का आश्रय ही इस कलियुग में परम कल्याणकारी और दुःख नाशक है।",
    en: "Taking shelter of the Lord's holy name is the ultimate source of welfare and destroyer of all grief in this age of Kali.",
    source: "श्रीमद् भागवत महापुराण (Shrimad Bhagwat Mahapuran)"
  },
  {
    hi: "प्रसन्नता केवल परमात्मा के चरणों में पूर्ण आत्म-समर्पण से ही प्राप्त हो सकती है।",
    en: "True happiness can only be attained through complete surrender at the lotus feet of the Divine.",
    source: "श्री गुरुदेव भगवान (Gurudev Bhagwan)"
  }
];

function initQuotesCarousel() {
  const nextBtn = document.getElementById('next-quote-btn');
  if (!nextBtn) return;

  const quoteHi = document.getElementById('quote-hi');
  const quoteEn = document.getElementById('quote-en');
  const quoteSource = document.getElementById('quote-source');
  
  let currentIndex = 0;

  nextBtn.addEventListener('click', () => {
    // Fade out
    quoteHi.style.opacity = 0;
    quoteEn.style.opacity = 0;
    quoteSource.style.opacity = 0;

    setTimeout(() => {
      currentIndex = (currentIndex + 1) % DB_QUOTES.length;
      const q = DB_QUOTES[currentIndex];
      
      quoteHi.textContent = q.hi;
      quoteEn.textContent = q.en;
      quoteSource.textContent = "— " + q.source;

      // Fade in
      quoteHi.style.opacity = 1;
      quoteEn.style.opacity = 1;
      quoteSource.style.opacity = 1;
    }, 250);
  });
}

function initHomePage() {
  renderHeroLatestSaptah();
  renderFeaturedPosts();
  initQuotesCarousel();
  renderAllSaptahs();
}

function renderHeroLatestSaptah() {
  const wrap = document.getElementById('latest-saptah-wrap');
  if (!wrap) return;

  const saptah = Data.getSaptahs()[0]; // Most recent
  const days   = Data.getDays(saptah.id);

  wrap.querySelector('.strip-saptah-title').textContent  = saptah.title;
  wrap.querySelector('.strip-saptah-title-hi').textContent = saptah.title_hi;
  wrap.querySelector('.strip-saptah-loc').textContent    = `📍 ${saptah.location}`;
  wrap.querySelector('.strip-saptah-year').textContent   = saptah.year;
  wrap.querySelector('.strip-saptah-link').href          = `katha.html?slug=${saptah.slug}`;

  const daysEl = wrap.querySelector('.strip-days');
  daysEl.innerHTML = days.map(d => {
    const hasPosts = Data.getPostsForDay(d.id).length > 0;
    const post     = hasPosts ? Data.getPostsForDay(d.id)[0] : null;
    const href     = post ? `post.html?slug=${post.slug}` : `katha.html?slug=${saptah.slug}`;
    return `
      <a href="${href}" class="strip-day ${hasPosts ? 'has-post' : ''}">
        <div class="strip-day-num">${d.day_number}</div>
        <span class="strip-day-label">${d.title_hi || d.title}</span>
        <span class="strip-day-status"></span>
      </a>`;
  }).join('');
}

function renderFeaturedPosts() {
  const wrap = document.getElementById('featured-posts-wrap');
  if (!wrap) return;

  const featured = Data.getFeatured();
  if (featured.length === 0) {
    wrap.innerHTML = `<div class="empty-state"><div class="empty-icon">🪷</div><h3>Coming Soon</h3><p>Kathā summaries will appear here.</p></div>`;
    return;
  }

  const [main, side, ...rest] = featured;
  const duoClass = side ? 'featured-duo' : 'featured-single';
  wrap.innerHTML = `
    <div class="${duoClass} reveal">
      ${buildPostCard(main, { className: side ? 'featured-main' : 'featured-full' })}
      ${side ? buildPostCard(side, { className: 'featured-side' }) : ''}
    </div>
    <div class="post-grid">
      ${rest.map((p, i) => `<div class="reveal reveal-delay-${i+1}">${buildPostCard(p)}</div>`).join('')}
    </div>`;

  initScrollReveal();
}

function renderAllSaptahs() {
  const wrap = document.getElementById('all-saptahs-wrap');
  if (!wrap) return;

  const saptahs = Data.getSaptahs();
  wrap.innerHTML = `<div class="saptah-grid">
    ${saptahs.map((s, i) => `<div class="reveal reveal-delay-${Math.min(i+1,4)}">${buildSaptahCard(s)}</div>`).join('')}
  </div>`;
  initScrollReveal();
}

/* --- KATHAS PAGE --- */
function initKathasPage() {
  const wrap    = document.getElementById('kathas-wrap');
  const search  = document.getElementById('kathas-search');
  const tabs    = document.querySelectorAll('#kathas-tabs .ftab');
  let activeYear = 'all';
  let query = '';

  function render() {
    let saptahs = Data.getSaptahs();
    if (activeYear !== 'all') saptahs = saptahs.filter(s => String(s.year) === activeYear);
    if (query) {
      const q = query.toLowerCase();
      saptahs = saptahs.filter(s =>
        s.title.toLowerCase().includes(q) ||
        s.location.toLowerCase().includes(q) ||
        s.description.toLowerCase().includes(q)
      );
    }
    if (saptahs.length === 0) {
      wrap.innerHTML = `<div class="empty-state"><div class="empty-icon">🔍</div><h3>No Kathas Found</h3><p>Try a different search or filter.</p></div>`;
    } else {
      wrap.innerHTML = `<div class="saptah-grid">${saptahs.map(s => buildSaptahCard(s)).join('')}</div>`;
    }
  }

  // Build year filter tabs dynamically
  const years = [...new Set(DB.saptahs.map(s => s.year))].sort((a,b) => b - a);
  const tabsContainer = document.getElementById('kathas-tabs');
  if (tabsContainer) {
    tabsContainer.innerHTML = `<button class="ftab active" data-year="all">All</button>` +
      years.map(y => `<button class="ftab" data-year="${y}">${y}</button>`).join('');
    tabsContainer.querySelectorAll('.ftab').forEach(btn => {
      btn.addEventListener('click', () => {
        tabsContainer.querySelectorAll('.ftab').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        activeYear = btn.dataset.year;
        render();
      });
    });
  }

  if (search) {
    search.addEventListener('input', () => { query = search.value.trim(); render(); });
  }

  render();
}

/* --- KATHA (SAPTAH DETAIL) PAGE --- */
function initKathaPage() {
  const slug = getParam('slug');
  if (!slug) { window.location.href = 'kathas.html'; return; }

  const saptah = Data.getSaptah(slug);
  if (!saptah) { window.location.href = 'kathas.html'; return; }

  // Meta
  document.title = `${saptah.title} – Premmarg`;

  // Header
  document.getElementById('katha-title').textContent    = saptah.title;
  document.getElementById('katha-title-hi').textContent = saptah.title_hi;
  document.getElementById('katha-location').textContent = saptah.location;
  document.getElementById('katha-year').textContent     = saptah.year;
  document.getElementById('katha-desc').textContent     = saptah.description;

  // Breadcrumbs & tags
  const bcTitle = document.getElementById('katha-breadcrumb-title');
  if (bcTitle) bcTitle.textContent = saptah.title;
  const yearTag = document.getElementById('katha-year-tag');
  if (yearTag) yearTag.textContent = saptah.year;
  const locTag = document.getElementById('katha-loc-tag');
  if (locTag) locTag.textContent = '📍 ' + saptah.location;

  // Days timeline
  const days = Data.getDays(saptah.id);
  const tlWrap = document.getElementById('timeline-wrap');
  if (tlWrap) {
    tlWrap.innerHTML = days.map(d => {
      const posts = Data.getPostsForDay(d.id);
      const href  = posts.length ? `post.html?slug=${posts[0].slug}` : '#';
      const cls   = posts.length ? 'active' : '';
      return `
        <a href="${href}" class="timeline-item ${cls} reveal">
          <div class="timeline-num">
            <span class="timeline-num-n">${d.day_number}</span>
            <span class="timeline-num-l">Day</span>
          </div>
          <div class="timeline-info">
            <div class="t-title">${d.title}</div>
            <div class="t-count font-hindi">${d.title_hi} · ${posts.length} post${posts.length !== 1 ? 's' : ''}</div>
          </div>
          <svg class="timeline-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>`;
    }).join('');
    initScrollReveal();
  }

  // Sidebar posts
  const sideWrap = document.getElementById('sidebar-posts');
  if (sideWrap) {
    const posts = Data.getPosts({ saptah_id: saptah.id });
    if (posts.length) {
      sideWrap.innerHTML = posts.slice(0, 5).map(p => {
        const day = Data.getDayForPost(p);
        return `
          <a href="post.html?slug=${p.slug}" style="text-decoration:none;color:inherit;display:block;padding:0.9rem 0;border-bottom:1px solid rgba(212,135,42,0.08);">
            <div style="font-size:0.7rem;color:var(--saffron);margin-bottom:0.3rem;">Day ${day?.day_number || ''}</div>
            <div style="font-family:var(--font-heading);font-size:0.92rem;color:var(--text-primary);line-height:1.35;">${p.title}</div>
          </a>`;
      }).join('');
    } else {
      sideWrap.innerHTML = `<p style="font-size:0.85rem;color:var(--text-muted);">Posts coming soon…</p>`;
    }
  }
}

/* --- POST READER PAGE --- */
function initPostPage() {
  const slug = getParam('slug');
  if (!slug) { window.location.href = 'index.html'; return; }

  const post = Data.getPost(slug);
  if (!post) { window.location.href = 'index.html'; return; }

  const day    = Data.getDayForPost(post);
  const saptah = Data.getSaptahForPost(post);
  const { prev, next } = Data.getAdjacentPosts(post);

  // Meta
  document.title = `${post.title} – Premmarg`;
  document.querySelector('meta[name="description"]')?.setAttribute('content', post.meta_description || '');

  // Breadcrumb
  const bc = document.getElementById('post-breadcrumb');
  if (bc && saptah) {
    bc.innerHTML = `
      <a href="index.html">Home</a><span class="sep">›</span>
      <a href="kathas.html">Kathas</a><span class="sep">›</span>
      <a href="katha.html?slug=${saptah.slug}">${saptah.title}</a><span class="sep">›</span>
      <span style="color:var(--text-secondary)">Day ${day?.day_number}</span>`;
  }

  // Tags
  const tagsEl = document.getElementById('post-tags');
  if (tagsEl) {
    tagsEl.innerHTML = `
      ${day ? `<span class="tag tag-day">Day ${day.day_number} · ${day.title_hi || day.title}</span>` : ''}
      ${post.featured ? `<span class="tag tag-featured">⭐ Featured</span>` : ''}`;
  }

  // Title
  const titleEl = document.getElementById('post-title');
  if (titleEl) titleEl.textContent = post.title;

  // Date
  const dateEl = document.getElementById('post-date');
  if (dateEl) dateEl.textContent = Data.formatDate(post.created_at);

  // Content
  const enEl = document.getElementById('content-en');
  const hiEl = document.getElementById('content-hi');
  if (enEl) enEl.innerHTML = post.content || '<p>Content coming soon…</p>';
  if (hiEl) hiEl.innerHTML = post.content_hi || '<p>हिंदी सारांश जल्द आएगा…</p>';

  // Generate Table of Contents (TOC) dynamically
  function generateTOC(contentEl, lang) {
    contentEl.querySelector('.post-toc')?.remove(); // Clean up existing
    const headings = contentEl.querySelectorAll('h2');
    if (headings.length === 0) return;

    const tocEl = document.createElement('div');
    tocEl.className = 'post-toc reveal';
    const titleText = lang === 'hi' ? '🪷 विषय-सूची (त्वरित मार्ग)' : '🪷 Table of Contents';
    
    let html = `
      <div class="toc-header">
        <span class="toc-title">${titleText}</span>
      </div>
      <ul class="toc-links">
    `;
    headings.forEach((h2, idx) => {
      const id = `heading-${lang}-${idx}`;
      h2.id = id;
      html += `<li><a href="#${id}">${h2.textContent}</a></li>`;
    });
    html += `</ul>`;
    tocEl.innerHTML = html;

    const firstP = contentEl.querySelector('p');
    if (firstP) {
      firstP.parentNode.insertBefore(tocEl, firstP.nextSibling);
    } else {
      contentEl.insertBefore(tocEl, contentEl.firstChild);
    }
  }

  if (enEl && post.content) generateTOC(enEl, 'en');
  if (hiEl && post.content_hi) generateTOC(hiEl, 'hi');

  // Language toggle
  const langToggle = document.querySelector('.lang-toggle');
  if (langToggle) {
    const enBtn = document.getElementById('lang-en');
    const hiBtn = document.getElementById('lang-hi');

    if (!post.content_hi) {
      hiBtn?.setAttribute('disabled', true);
      hiBtn?.setAttribute('title', 'Hindi summary coming soon');
    }

    function setLang(lang) {
      enEl?.classList.toggle('show', lang === 'en');
      hiEl?.classList.toggle('show', lang === 'hi');
      enBtn?.classList.toggle('active', lang === 'en');
      hiBtn?.classList.toggle('active', lang === 'hi');
    }
    setLang('en');

    enBtn?.addEventListener('click', () => setLang('en'));
    hiBtn?.addEventListener('click', () => {
      if (post.content_hi) setLang('hi');
      else showToast('हिंदी सारांश जल्द आएगा 🙏');
    });
  }

  // Share buttons
  const pageUrl = window.location.href;
  document.getElementById('share-wa')?.addEventListener('click', () => shareWhatsApp(post.title, pageUrl));
  document.getElementById('share-copy')?.addEventListener('click', () => copyLink(pageUrl));

  // Prev / Next navigation
  const prevEl = document.getElementById('nav-prev');
  const nextEl = document.getElementById('nav-next');
  if (prevEl) {
    if (prev) {
      prevEl.href = `post.html?slug=${prev.slug}`;
      prevEl.querySelector('.post-nav-title').textContent = prev.title;
    } else {
      prevEl.style.visibility = 'hidden';
    }
  }
  if (nextEl) {
    if (next) {
      nextEl.href = `post.html?slug=${next.slug}`;
      nextEl.querySelector('.post-nav-title').textContent = next.title;
    } else {
      nextEl.style.visibility = 'hidden';
    }
  }
}

/* --- ABOUT PAGE --- */
function initAboutPage() {
  const totalSaptahs = DB.saptahs.length;
  const totalDays    = DB.days.filter(d => Data.getPostsForDay(d.id).length > 0).length;
  const totalPosts   = DB.posts.length;

  const s = document.getElementById('stat-saptahs');
  const d = document.getElementById('stat-days');
  const p = document.getElementById('stat-posts');
  if (s) animateCount(s, totalSaptahs);
  if (d) animateCount(d, totalDays);
  if (p) animateCount(p, totalPosts);
}

function animateCount(el, target) {
  let current = 0;
  const step = Math.ceil(target / 30);
  const timer = setInterval(() => {
    current = Math.min(current + step, target);
    el.textContent = current + '+';
    if (current >= target) clearInterval(timer);
  }, 50);
}

/* ===== THEME TOGGLE ===== */
function initThemeToggle() {
  const toggleBtn = document.getElementById('theme-toggle');
  if (!toggleBtn) return;

  // The inline script in HTML sets the initial data-theme on document.documentElement
  // We just sync the button icon here based on the current theme
  const currentTheme = document.documentElement.getAttribute('data-theme') || 'dark';
  updateThemeIcon(currentTheme);

  toggleBtn.addEventListener('click', () => {
    let theme = document.documentElement.getAttribute('data-theme');
    let newTheme = theme === 'light' ? 'dark' : 'light';
    
    document.documentElement.setAttribute('data-theme', newTheme);
    localStorage.setItem('premmarg-theme', newTheme);
    updateThemeIcon(newTheme);
  });

  function updateThemeIcon(theme) {
    toggleBtn.innerHTML = theme === 'light' 
      ? '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>'
      : '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>';
  }
}

/* ===== BOOTSTRAP ===== */
document.addEventListener('DOMContentLoaded', async () => {
  initThemeToggle();
  initNavbar();
  initLotusParticles();
  
  // Wait for dynamic API data
  await Data.load();

  // Populate footer saptahs if element exists
  const footerUl = document.getElementById('footer-saptahs');
  if (footerUl) {
    footerUl.innerHTML = Data.getSaptahs().slice(0,4).map(s =>
      `<li><a href="katha.html?slug=${s.slug}">${s.title} ${s.year}</a></li>`
    ).join('');
  }

  const body = document.body.dataset.page;
  if (body === 'home')   initHomePage();
  if (body === 'kathas') initKathasPage();
  if (body === 'katha')  initKathaPage();
  if (body === 'post')   initPostPage();
  if (body === 'about')  initAboutPage();

  // Init scroll reveal after DOM elements are created
  initScrollReveal();
});
