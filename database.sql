-- =============================================
-- Premmarg Blog - Spiritual Kathā Platform
-- Database Schema + Seed Data
-- =============================================

DROP DATABASE IF EXISTS premmarg_blog;
CREATE DATABASE premmarg_blog
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE premmarg_blog;

-- -----------------------------------------
-- Table: admin_users
-- -----------------------------------------
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------
-- Table: saptah
-- -----------------------------------------
CREATE TABLE IF NOT EXISTS saptah (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    title_hi VARCHAR(255) DEFAULT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    location VARCHAR(255) DEFAULT NULL,
    year INT DEFAULT NULL,
    description TEXT DEFAULT NULL,
    description_hi TEXT DEFAULT NULL,
    image_url VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------
-- Table: days
-- -----------------------------------------
CREATE TABLE IF NOT EXISTS days (
    id INT AUTO_INCREMENT PRIMARY KEY,
    saptah_id INT NOT NULL,
    day_number INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    title_hi VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (saptah_id) REFERENCES saptah(id) ON DELETE CASCADE,
    UNIQUE KEY unique_saptah_day (saptah_id, day_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------
-- Table: posts
-- -----------------------------------------
CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    day_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    title_hi VARCHAR(255) DEFAULT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    content LONGTEXT DEFAULT NULL,
    content_hi LONGTEXT DEFAULT NULL,
    meta_description TEXT DEFAULT NULL,
    keywords TEXT DEFAULT NULL,
    featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (day_id) REFERENCES days(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------
-- Indexes for performance
-- -----------------------------------------
ALTER TABLE posts ADD INDEX idx_posts_featured (featured);
ALTER TABLE posts ADD INDEX idx_posts_created (created_at);
ALTER TABLE days ADD INDEX idx_days_saptah (saptah_id);

-- =============================================
-- SEED DATA
-- =============================================

INSERT INTO admin_users (username, password_hash) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'); -- Password is 'password'

-- Saptah 1
INSERT INTO saptah (id, title, title_hi, slug, location, year, description, description_hi, image_url) VALUES
(1, 'Shrimad Bhagwat Kathā', 'श्रीमद् भागवत कथा', 'shrimad-bhagwat-katha-chandigarh-2026', 'Chandigarh', 2026,
 'A divine seven-day discourse on the Shrimad Bhagwat Puran, exploring the eternal pastimes of Lord Krishna and the path of devotion. This Saptah brings together seekers from across the region to immerse in the nectar of Bhagwat Ras.',
 'भगवान श्रीकृष्ण की अमर लीलाओं और भक्ति-मार्ग की खोज में श्रीमद् भागवत पुराण पर यह एक दिव्य सात-दिवसीय प्रवचन है।', NULL),
(2, 'Sunderkand Path Saptah', 'सुंदरकांड पाठ सप्ताह', 'sunderkand-path-saptah-delhi-2025', 'Delhi', 2025,
 'A week-long recitation and exposition of the glorious Sunderkand from the Ramcharitmanas, celebrating the devotion of Hanumanji and the triumph of dharma.',
 'रामचरितमानस के गौरवशाली सुंदरकांड का एक सप्ताह का पाठ और प्रवचन, हनुमान जी की भक्ति और धर्म की विजय का उत्सव मनाते हुए।', NULL);

-- Days for Saptah 1 (Shrimad Bhagwat)
INSERT INTO days (id, saptah_id, day_number, title, title_hi) VALUES
(1, 1, 1, 'Mangalacharan & Introduction to Bhagwat', 'मंगलाचरण एवं भागवत परिचय'),
(2, 1, 2, 'Creation & The Story of Dhruv', 'सृष्टि एवं ध्रुव चरित्र'),
(3, 1, 3, 'Kapil Dev & Devahuti', 'कपिल देव एवं देवहूति'),
(4, 1, 4, 'Story of King Bharat', 'राजा भरत की कथा'),
(5, 1, 5, 'Ajamil Updesh & Vritrasur', 'अजामिल उपदेश एवं वृत्रासुर'),
(6, 1, 6, 'Prahlad Charitra & Gajendra Moksha', 'प्रह्लाद चरित्र एवं गजेन्द्र मोक्ष'),
(7, 1, 7, 'Krishna Leela & Maha Ras', 'कृष्ण लीला एवं महा रास');

-- Days for Saptah 2 (Sunderkand)
INSERT INTO days (id, saptah_id, day_number, title, title_hi) VALUES
(8, 2, 1, 'Hanuman\'s Resolve & Lanka Departure', 'हनुमान जी का संकल्प एवं लंका प्रस्थान'),
(9, 2, 2, 'Surasa & Singhika Episodes', 'सुरसा एवं सिंहिका प्रसंग'),
(10, 2, 3, 'Lanka Entry & Ashok Vatika', 'लंका प्रवेश एवं अशोक वाटिका');

-- Posts for Saptah 1, Day 1
INSERT INTO posts (id, day_id, title, title_hi, slug, content, content_hi, meta_description, keywords, featured) VALUES
(1, 1, 'The Sacred Beginning – Mangalacharan', 'पवित्र आरम्भ – मंगलाचरण', 'the-sacred-beginning-mangalacharan',
'<h2>The Invocation of the Divine</h2>
<p>Every great spiritual journey begins with an invocation — a humble prayer to the Lord, the Guru, and the sacred tradition. The Mangalacharan sets the tone for the entire Saptah, creating an atmosphere of reverence and surrender.</p>
<blockquote>नमामीशमीशान निर्वाणरूपं विभुं व्यापकं ब्रह्मवेदस्वरूपम्।</blockquote>',
'<h2>दिव्य आवाहन</h2>
<p>प्रत्येक महान आध्यात्मिक यात्रा एक आवाहन से आरम्भ होती है — भगवान, गुरु और पवित्र परम्परा के प्रति एक विनम्र प्रार्थना।</p>
<blockquote>नमामीशमीशान निर्वाणरूपं विभुं व्यापकं ब्रह्मवेदस्वरूपम्।</blockquote>',
'Discover the sacred beginning of Shrimad Bhagwat Kathā.', 'mangalacharan, bhagwat katha', 1),
(2, 1, 'Understanding Bhagwat Mahapuran', 'भागवत महापुराण का परिचय', 'understanding-bhagwat-mahapuran',
'<h2>What is the Bhagwat Mahapuran?</h2>
<p>The Shrimad Bhagwat Mahapuran is one of the eighteen Puranas composed by Maharishi Ved Vyas. It is considered the ripened fruit of the Vedic tree of knowledge.</p>',
'<h2>भागवत महापुराण क्या है?</h2>
<p>श्रीमद् भागवत महापुराण महर्षि वेद व्यास द्वारा रचित अठारह पुराणों में से एक है। इसे वैदिक ज्ञान-वृक्ष का परिपक्व फल माना गया है।</p>',
'Learn about the Shrimad Bhagwat Mahapuran.', 'bhagwat puran, ved vyas', 1);
