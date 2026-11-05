/* ============================================================
   PREMMARG BLOG — Data Store (API Integrated)
   ============================================================ */

const API_BASE = 'api';

let DB = {
  saptahs: [],
  days: [],
  posts: []
};

const Data = {
  async load() {
    try {
      const urlParams = new URLSearchParams(window.location.search);
      const isPreview = urlParams.get('preview') === '1' ? '&preview=1' : '';

      const [saptahsRes, daysRes, postsRes] = await Promise.all([
        fetch(`${API_BASE}/get_saptahs.php?_=${Date.now()}`),
        fetch(`${API_BASE}/get_days.php?_=${Date.now()}`),
        fetch(`${API_BASE}/get_posts.php?_=${Date.now()}${isPreview}`)
      ]);
      const [rawSaptahs, rawDays, rawPosts] = await Promise.all([
        saptahsRes.json(),
        daysRes.json(),
        postsRes.json()
      ]);

      DB.saptahs = Array.isArray(rawSaptahs) ? rawSaptahs.map(s => ({
        ...s,
        slug: s.slug ? String(s.slug).trim() : '',
        title: s.title ? String(s.title).trim() : '',
        title_hi: s.title_hi ? String(s.title_hi).trim() : '',
        location: s.location ? String(s.location).trim() : ''
      })) : [];

      DB.days = Array.isArray(rawDays) ? rawDays.map(d => ({
        ...d,
        title: d.title ? String(d.title).trim() : '',
        title_hi: d.title_hi ? String(d.title_hi).trim() : ''
      })) : [];

      DB.posts = Array.isArray(rawPosts) ? rawPosts.map(p => ({
        ...p,
        slug: p.slug ? String(p.slug).trim() : '',
        title: p.title ? String(p.title).trim() : ''
      })) : [];
    } catch (e) {
      console.error("Failed to load data from API", e);
    }
  },

  getSaptahs: () => DB.saptahs,
  getSaptah: (slug) => {
    if (!slug) return null;
    const clean = decodeURIComponent(String(slug)).trim().toLowerCase();
    return DB.saptahs.find(s => 
      (s.slug && s.slug.trim().toLowerCase() === clean) || 
      String(s.id) === clean
    );
  },
  getDays: (saptahId) => DB.days.filter(d => parseInt(d.saptah_id) === parseInt(saptahId)).sort((a,b) => a.day_number - b.day_number),
  getPostsForDay: (dayId) => DB.posts.filter(p => parseInt(p.day_id) === parseInt(dayId)).sort((a,b) => new Date(a.created_at) - new Date(b.created_at)),
  getFeatured: () => DB.posts.filter(p => parseInt(p.featured) === 1).sort((a,b) => new Date(b.created_at) - new Date(a.created_at)),
  getPost: (slug) => {
    if (!slug) return null;
    const clean = decodeURIComponent(String(slug)).trim().toLowerCase();
    return DB.posts.find(p => 
      (p.slug && p.slug.trim().toLowerCase() === clean) || 
      String(p.id) === clean
    );
  },
  getLectures: () => DB.posts.filter(p => (p.post_type === 'lecture' || !p.day_id)).sort((a,b) => new Date(b.event_date || b.created_at) - new Date(a.event_date || a.created_at)),
  getKathaPosts: () => DB.posts.filter(p => p.post_type !== 'lecture' && p.day_id),
  
  getPosts: (filter) => {
    let posts = DB.posts;
    if (filter && filter.saptah_id) {
      const dayIds = DB.days.filter(d => parseInt(d.saptah_id) === parseInt(filter.saptah_id)).map(d => d.id);
      posts = posts.filter(p => dayIds.includes(p.day_id));
    }
    return posts;
  },
  
  getDayForPost: (post) => {
    if (!post || !post.day_id) return null;
    return DB.days.find(d => parseInt(d.id) === parseInt(post.day_id));
  },
  
  getSaptahForPost: (post) => {
    if (!post || !post.day_id) return null;
    const day = Data.getDayForPost(post);
    if (!day) return null;
    return DB.saptahs.find(s => parseInt(s.id) === parseInt(day.saptah_id));
  },
  
  getAdjacentPosts: (post) => {
    if (!post) return { prev: null, next: null };
    
    // If lecture, link adjacent lectures
    if (post.post_type === 'lecture' || !post.day_id) {
      const lectures = Data.getLectures();
      const idx = lectures.findIndex(p => p.id === post.id);
      return {
        prev: idx > 0 ? lectures[idx - 1] : null,
        next: idx < lectures.length - 1 ? lectures[idx + 1] : null
      };
    }

    const saptah = Data.getSaptahForPost(post);
    if (!saptah) return { prev: null, next: null };
    
    const allPosts = Data.getPosts({ saptah_id: saptah.id })
      .sort((a,b) => {
        const dayA = Data.getDayForPost(a);
        const dayB = Data.getDayForPost(b);
        if (dayA && dayB) {
          if (dayA.day_number !== dayB.day_number) {
            return dayA.day_number - dayB.day_number;
          }
        }
        return new Date(a.created_at) - new Date(b.created_at);
      });
      
    const idx = allPosts.findIndex(p => p.id === post.id);
    return {
      prev: idx > 0 ? allPosts[idx - 1] : null,
      next: idx < allPosts.length - 1 ? allPosts[idx + 1] : null
    };
  },
  
  formatDate: (dateString) => {
    const d = new Date(dateString);
    return isNaN(d) ? '' : d.toLocaleDateString('en-US', { day: 'numeric', month: 'long', year: 'numeric' });
  }
};
