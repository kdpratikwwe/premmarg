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
      const [saptahsRes, daysRes, postsRes] = await Promise.all([
        fetch(`${API_BASE}/get_saptahs.php?_=${Date.now()}`),
        fetch(`${API_BASE}/get_days.php?_=${Date.now()}`),
        fetch(`${API_BASE}/get_posts.php?_=${Date.now()}`)
      ]);
      DB.saptahs = await saptahsRes.json();
      DB.days = await daysRes.json();
      DB.posts = await postsRes.json();
    } catch (e) {
      console.error("Failed to load data from API", e);
    }
  },

  getSaptahs: () => DB.saptahs,
  getSaptah: (slug) => DB.saptahs.find(s => s.slug === slug),
  getDays: (saptahId) => DB.days.filter(d => parseInt(d.saptah_id) === parseInt(saptahId)).sort((a,b) => a.day_number - b.day_number),
  getPostsForDay: (dayId) => DB.posts.filter(p => parseInt(p.day_id) === parseInt(dayId)).sort((a,b) => new Date(a.created_at) - new Date(b.created_at)),
  getFeatured: () => DB.posts.filter(p => parseInt(p.featured) === 1).sort((a,b) => new Date(b.created_at) - new Date(a.created_at)),
  getPost: (slug) => DB.posts.find(p => p.slug === slug),
  
  getPosts: (filter) => {
    let posts = DB.posts;
    if (filter && filter.saptah_id) {
      const dayIds = DB.days.filter(d => parseInt(d.saptah_id) === parseInt(filter.saptah_id)).map(d => d.id);
      posts = posts.filter(p => dayIds.includes(p.day_id));
    }
    return posts;
  },
  
  getDayForPost: (post) => {
    if (!post) return null;
    return DB.days.find(d => parseInt(d.id) === parseInt(post.day_id));
  },
  
  getSaptahForPost: (post) => {
    const day = Data.getDayForPost(post);
    if (!day) return null;
    return DB.saptahs.find(s => parseInt(s.id) === parseInt(day.saptah_id));
  },
  
  getAdjacentPosts: (post) => {
    if (!post) return { prev: null, next: null };
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
