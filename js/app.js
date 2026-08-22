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
async function initQuotesCarousel() {
  const nextBtn = document.getElementById('next-quote-btn');
  if (!nextBtn) return;

  const quoteHi = document.getElementById('quote-hi');
  const quoteEn = document.getElementById('quote-en');
  const quoteSource = document.getElementById('quote-source');
  
  let quotes = [];
  try {
    const res = await fetch(`api/get_quotes.php?_=${Date.now()}`);
    quotes = await res.json();
  } catch (err) {
    console.error("Failed to fetch quotes", err);
    return;
  }

  if (quotes.length === 0) return;

  let currentIndex = 0;
  
  // Display initial quote (today's featured quote is ordered first by API)
  quoteHi.textContent = quotes[0].text_hi;
  quoteEn.textContent = quotes[0].text_en;
  quoteSource.textContent = "— " + quotes[0].source;

  nextBtn.addEventListener('click', () => {
    // Fade out
    quoteHi.style.opacity = 0;
    quoteEn.style.opacity = 0;
    quoteSource.style.opacity = 0;

    setTimeout(() => {
      currentIndex = (currentIndex + 1) % quotes.length;
      const q = quotes[currentIndex];
      
      quoteHi.textContent = q.text_hi;
      quoteEn.textContent = q.text_en;
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
  if (!saptah) return;
  const days   = Data.getDays(saptah.id);

  wrap.querySelector('.strip-saptah-title').textContent  = saptah.title;
  wrap.querySelector('.strip-saptah-title-hi').textContent = saptah.title_hi;
  wrap.querySelector('.strip-saptah-loc').innerHTML      = `<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg> ` + saptah.location;
  wrap.querySelector('.strip-saptah-year').textContent     = saptah.year;
  
  const urlParams = new URLSearchParams(window.location.search);
  const isPreview = urlParams.get('preview') === '1';
  const previewParam = isPreview ? '&preview=1' : '';

  const link = wrap.querySelector('.strip-saptah-link');
  if (link) link.href = `katha.html?slug=${saptah.slug}${previewParam}`;

  const daysEl = document.getElementById('strip-days');
  if (!daysEl) return;

  // Filter out days that do not have active posts (drafts are only in DB if preview=1)
  const visibleDays = days.filter(d => Data.getPostsForDay(d.id).length > 0);

  daysEl.innerHTML = visibleDays.map(d => {
    const post = Data.getPostsForDay(d.id)[0];
    const href = `post.html?slug=${post.slug}${previewParam}`;
    return `
      <a href="${href}" class="strip-day has-post">
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
  const urlParams = new URLSearchParams(window.location.search);
  const isPreview = urlParams.get('preview') === '1';
  const previewParam = isPreview ? '&preview=1' : '';

  const days = Data.getDays(saptah.id);
  const tlWrap = document.getElementById('timeline-wrap');
  if (tlWrap) {
    // Filter out timeline days that do not have active posts (drafts are only in DB if preview=1)
    const visibleDays = days.filter(d => Data.getPostsForDay(d.id).length > 0);
    
    tlWrap.innerHTML = visibleDays.map(d => {
      const posts = Data.getPostsForDay(d.id);
      const href  = `post.html?slug=${posts[0].slug}${previewParam}`;
      return `
        <a href="${href}" class="timeline-item active reveal">
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
    const urlParams = new URLSearchParams(window.location.search);
    const isPreview = urlParams.get('preview') === '1';
    const previewParam = isPreview ? '&preview=1' : '';

    bc.innerHTML = `
      <a href="index.html">Home</a><span class="sep">›</span>
      <a href="kathas.html">Kathas</a><span class="sep">›</span>
      <a href="katha.html?slug=${saptah.slug}${previewParam}">${saptah.title}</a><span class="sep">›</span>
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

/* --- QUOTES ARCHIVE PAGE --- */
let allQuotes = [];

async function initQuotesPage() {
  const wrap = document.getElementById('quotes-wrap');
  if (!wrap) return;

  try {
    const res = await fetch(`api/get_quotes.php?_=${Date.now()}`);
    allQuotes = await res.json();
    
    renderQuotesList(allQuotes);
    
    // Setup search
    const searchInput = document.getElementById('quotes-search');
    if (searchInput) {
      searchInput.addEventListener('input', (e) => {
        const query = e.target.value.toLowerCase().trim();
        const filtered = allQuotes.filter(q => 
          q.text_hi.toLowerCase().includes(query) ||
          q.text_en.toLowerCase().includes(query) ||
          q.source.toLowerCase().includes(query)
        );
        renderQuotesList(filtered);
      });
    }
  } catch (err) {
    console.error("Failed to load quotes:", err);
    wrap.innerHTML = `<div class="text-center" style="grid-column: 1/-1; padding: 3rem 0; color: var(--saffron);">Failed to load Amrit Vachan.</div>`;
  }
}

function renderQuotesList(list) {
  const wrap = document.getElementById('quotes-wrap');
  if (!wrap) return;

  if (list.length === 0) {
    wrap.innerHTML = `<div class="text-center" style="grid-column: 1/-1; padding: 3rem 0; color: var(--text-secondary);">No quotes found matching your search.</div>`;
    return;
  }

  wrap.innerHTML = list.map(q => {
    const isToday = q.is_today && q.is_today == 1 ? 'today-highlight' : '';
    const shareText = encodeURIComponent(`🪷 *अमृत वचन* 🪷\n\n"${q.text_hi}"\n\n"${q.text_en}"\n\n— ${q.source}\n\nRead more at: ${window.location.origin}`);
    
    return `
      <div class="glass-card quote-archive-card ${isToday} reveal">
        <div class="quote-archive-hi font-hindi">${q.text_hi}</div>
        <div class="quote-archive-en">${q.text_en}</div>
        <div class="quote-archive-footer">
          <div class="quote-archive-source">— ${q.source}</div>
          <div class="quote-actions">
            <button class="quote-action-btn" title="Copy Thought" onclick="copyQuoteToClipboard(this, '${encodeURIComponent(q.text_hi + '\n\n' + q.text_en + '\n\n— ' + q.source)}')">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
            </button>
            <a href="https://api.whatsapp.com/send?text=${shareText}" target="_blank" class="quote-action-btn" title="Share to WhatsApp">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            </a>
          </div>
        </div>
      </div>
    `;
  }).join('');

  if (typeof initScrollReveal === 'function') {
    initScrollReveal();
  }
}

window.copyQuoteToClipboard = function(btn, encodedText) {
  const text = decodeURIComponent(encodedText);
  navigator.clipboard.writeText(text).then(() => {
    showToast('अमृत वचन कॉपी हो गया! (Copied!)');
  }).catch(() => {
    showToast('Failed to copy.');
  });
};

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
  if (body === 'quotes') initQuotesPage();

  // Init scroll reveal after DOM elements are created
  initScrollReveal();
});
