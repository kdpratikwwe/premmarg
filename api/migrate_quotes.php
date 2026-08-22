<?php
require_once 'config.php';

header('Content-Type: text/html; charset=utf-8');

try {
    // Create quotes table
    $sql = "CREATE TABLE IF NOT EXISTS quotes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        text_hi TEXT NOT NULL,
        text_en TEXT NOT NULL,
        source VARCHAR(255) DEFAULT 'श्री गुरुदेव महाराज जी',
        publish_date DATE NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $pdo->exec($sql);
    echo "<h3>Success: Table 'quotes' created or already exists.</h3>";

    // Seed if empty
    $count = $pdo->query("SELECT COUNT(*) FROM quotes")->fetchColumn();
    if ($count == 0) {
        $quotes = [
            [
                "text_hi" => "जब तक जीवन में गुरुदेव की कृपा का अनुभव नहीं होता, तब तक भक्ति का मार्ग प्रशस्त नहीं होता।",
                "text_en" => "Until you experience the grace of Gurudev in life, the path of devotion is not cleared.",
                "source" => "श्री गुरुदेव महाराज जी",
                "publish_date" => date('Y-m-d')
            ],
            [
                "text_hi" => "भगवान के नाम संकीर्तन में वह शक्ति है जो मनुष्य के अंतःकरण को पल भर में पवित्र कर देती है।",
                "text_en" => "The chanting of Lord's name possesses the power to purify a person's inner self in a single moment.",
                "source" => "श्री गुरुदेव महाराज जी",
                "publish_date" => date('Y-m-d', strtotime('+1 day'))
            ],
            [
                "text_hi" => "प्रेम ही ईश्वर है और ईश्वर ही प्रेम है। प्रेम मार्ग ही भक्ति का सबसे सरल मार्ग है।",
                "text_en" => "Love is God and God is Love. The path of love is the simplest way of devotion.",
                "source" => "श्री गुरुदेव महाराज जी",
                "publish_date" => date('Y-m-d', strtotime('+2 day'))
            ]
        ];

        $stmt = $pdo->prepare("INSERT INTO quotes (text_hi, text_en, source, publish_date) VALUES (?, ?, ?, ?)");
        foreach ($quotes as $q) {
            $stmt->execute([$q['text_hi'], $q['text_en'], $q['source'], $q['publish_date']]);
        }
        echo "<p>Table 'quotes' seeded with initial quotes successfully.</p>";
    } else {
        echo "<p>Table already has data. Seeding skipped.</p>";
    }
    
    echo "<br><a href='../admin/index.php'>Go to Admin Panel</a>";
} catch (PDOException $e) {
    die("Database migration error: " . $e->getMessage());
}
