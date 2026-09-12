CREATE DATABASE IF NOT EXISTS evoting_osis_gedeg CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE evoting_osis_gedeg;

DROP TABLE IF EXISTS votes;
DROP TABLE IF EXISTS candidates;
DROP TABLE IF EXISTS admin;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(20) NOT NULL UNIQUE,
    status_vote ENUM('belum', 'sudah') NOT NULL DEFAULT 'belum',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE candidates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    number TINYINT UNSIGNED NOT NULL UNIQUE,
    photo VARCHAR(255) NOT NULL,
    chair_name VARCHAR(120) NOT NULL,
    vision TEXT NOT NULL,
    mission TEXT NOT NULL,
    total_votes INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE votes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    candidate_id INT UNSIGNED NOT NULL,
    voted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_votes_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_votes_candidate FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE admin (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(60) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO admin (username, password) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT INTO candidates (number, photo, chair_name, vision, mission, total_votes) VALUES
(1, 'assets/images/candidate-1.svg', 'Aldi Pratama', 'Mewujudkan OSIS SMAN 1 Gedeg yang aktif, transparan, dan peduli pada aspirasi seluruh siswa.', '1. Membuka kanal aspirasi siswa secara berkala.\n2. Menghidupkan program literasi, seni, olahraga, dan kewirausahaan.\n3. Membangun budaya disiplin, ramah, dan saling menghargai.', 0),
(2, 'assets/images/candidate-2.svg', 'Raka Aditya', 'Menjadikan OSIS sebagai ruang kolaborasi kreatif yang berprestasi dan bermanfaat bagi warga sekolah.', '1. Membentuk tim minat bakat yang inklusif.\n2. Mengadakan kegiatan sekolah berbasis karya dan prestasi.\n3. Memperkuat kerja sama OSIS dengan ekstrakurikuler.', 0),
(3, 'assets/images/candidate-3.svg', 'Farel Maulana', 'Membangun lingkungan sekolah yang harmonis, inovatif, dan berkarakter untuk meningkatkan mutu SMAN 1 Gedeg.', '1. Mengembangkan program kebersihan dan kepedulian sosial.\n2. Mendorong digitalisasi informasi kegiatan siswa.\n3. Menyelenggarakan forum evaluasi kegiatan OSIS setiap bulan.', 0);

-- Setelah menjalankan file ini, impor workbook kode peserta:
-- php database/import_codes.php "C:\\Users\\ACER\\Downloads\\1360_kode_acak_20_digit.xlsx"