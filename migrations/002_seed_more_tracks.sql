-- Migration 002 — Ticket T5 (US-06 : tester recherche, filtres, tri et pagination)
-- Ajoute 20 morceaux de test (2 par genre). À exécuter une fois dans phpMyAdmin (onglet SQL)
-- sur une base créée avant le ticket T5. INSERT IGNORE : sans effet si les morceaux existent déjà.
INSERT IGNORE INTO `tracks` (`id`, `title`, `artist`, `cover_url`, `preview_url`, `genre_id`, `created_at`) VALUES
(9, 'Save Your Tears', 'The Weeknd', 'https://picsum.photos/seed/weeknd2/500/500', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-9.mp3', 1, '2026-11-26 21:30:00'),
(10, 'Levitating', 'Dua Lipa', 'https://picsum.photos/seed/dualipa/500/500', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-10.mp3', 1, '2026-11-26 21:30:00'),
(11, 'HUMBLE.', 'Kendrick Lamar', 'https://picsum.photos/seed/kendrick/500/500', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-11.mp3', 2, '2026-11-26 21:30:00'),
(12, 'Bolide allemand', 'SDM', 'https://picsum.photos/seed/sdm/500/500', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-12.mp3', 2, '2026-11-26 21:30:00'),
(13, 'Kiss of Life', 'Sade', 'https://picsum.photos/seed/sade/500/500', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-13.mp3', 3, '2026-11-26 21:30:00'),
(14, 'Thinkin Bout You', 'Frank Ocean', 'https://picsum.photos/seed/frank2/500/500', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-14.mp3', 3, '2026-11-26 21:30:00'),
(15, 'Seven Nation Army', 'The White Stripes', 'https://picsum.photos/seed/stripes/500/500', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-15.mp3', 4, '2026-11-26 21:30:00'),
(16, 'Mr. Brightside', 'The Killers', 'https://picsum.photos/seed/killers/500/500', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-16.mp3', 4, '2026-11-26 21:30:00'),
(17, 'One More Time', 'Daft Punk', 'https://picsum.photos/seed/daftpunk/500/500', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-17.mp3', 5, '2026-11-26 21:30:00'),
(18, 'Opus', 'Eric Prydz', 'https://picsum.photos/seed/prydz/500/500', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3', 5, '2026-11-26 21:30:00'),
(19, 'So What', 'Miles Davis', 'https://picsum.photos/seed/miles/500/500', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-2.mp3', 6, '2026-11-26 21:30:00'),
(20, 'Feeling Good', 'Nina Simone', 'https://picsum.photos/seed/nina/500/500', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-3.mp3', 6, '2026-11-26 21:30:00'),
(21, 'Calm Down', 'Rema', 'https://picsum.photos/seed/rema/500/500', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-4.mp3', 7, '2026-11-26 21:30:00'),
(22, 'Ye', 'Burna Boy', 'https://picsum.photos/seed/burna/500/500', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-5.mp3', 7, '2026-11-26 21:30:00'),
(23, 'Clair de lune', 'Claude Debussy', 'https://picsum.photos/seed/debussy/500/500', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-6.mp3', 8, '2026-11-26 21:30:00'),
(24, 'Les Quatre Saisons : Printemps', 'Antonio Vivaldi', 'https://picsum.photos/seed/vivaldi/500/500', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-7.mp3', 8, '2026-11-26 21:30:00'),
(25, 'Space Song', 'Beach House', 'https://picsum.photos/seed/beachhouse/500/500', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-8.mp3', 9, '2026-11-26 21:30:00'),
(26, 'Notion', 'The Rare Occasions', 'https://picsum.photos/seed/rare/500/500', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-9.mp3', 9, '2026-11-26 21:30:00'),
(27, 'Despacito', 'Luis Fonsi', 'https://picsum.photos/seed/fonsi/500/500', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-10.mp3', 10, '2026-11-26 21:30:00'),
(28, 'Tusa', 'Karol G', 'https://picsum.photos/seed/karolg/500/500', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-11.mp3', 10, '2026-11-26 21:30:00');
