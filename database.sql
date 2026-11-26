-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:8889
-- Généré le : jeu. 26 nov. 2026 à 18:09
-- Version du serveur : 8.0.40
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `musicswipe`
--

-- --------------------------------------------------------

--
-- Structure de la table `genres`
--

CREATE TABLE `genres` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `genres`
--

INSERT INTO `genres` (`id`, `name`) VALUES
(7, 'Afro'),
(8, 'Classique'),
(5, 'Électro'),
(9, 'Indie'),
(6, 'Jazz'),
(1, 'Pop'),
(3, 'R&B'),
(2, 'Rap'),
(10, 'Reggaeton'),
(4, 'Rock');

-- --------------------------------------------------------

--
-- Structure de la table `playlists`
--

CREATE TABLE `playlists` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Mes likes',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `playlist_tracks`
--

CREATE TABLE `playlist_tracks` (
  `playlist_id` int UNSIGNED NOT NULL,
  `track_id` int UNSIGNED NOT NULL,
  `added_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `personal_note` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `swipes`
--

CREATE TABLE `swipes` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `track_id` int UNSIGNED NOT NULL,
  `action` enum('like','dislike') COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `tracks`
--

CREATE TABLE `tracks` (
  `id` int UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `artist` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `cover_url` varchar(500) COLLATE utf8mb4_general_ci NOT NULL,
  `preview_url` varchar(500) COLLATE utf8mb4_general_ci NOT NULL,
  `genre_id` int UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `tracks`
--

INSERT INTO `tracks` (`id`, `title`, `artist`, `cover_url`, `preview_url`, `genre_id`, `created_at`) VALUES
(1, 'Midnight City', 'M83', 'https://picsum.photos/seed/m83/500/500', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3', 5, '2026-11-26 18:09:02'),
(2, 'Blinding Lights', 'The Weeknd', 'https://picsum.photos/seed/weeknd/500/500', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-2.mp3', 1, '2026-11-26 18:09:02'),
(3, 'Nikes', 'Frank Ocean', 'https://picsum.photos/seed/frank/500/500', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-3.mp3', 3, '2026-11-26 18:09:02'),
(4, 'Do I Wanna Know?', 'Arctic Monkeys', 'https://picsum.photos/seed/arctic/500/500', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-4.mp3', 4, '2026-11-26 18:09:02'),
(5, 'Essence', 'Wizkid ft. Tems', 'https://picsum.photos/seed/wizkid/500/500', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-5.mp3', 7, '2026-11-26 18:09:02'),
(6, 'Les Étoiles Vagabondes', 'Nekfeu', 'https://picsum.photos/seed/nekfeu/500/500', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-6.mp3', 2, '2026-11-26 18:09:02'),
(7, 'Take Five', 'Dave Brubeck', 'https://picsum.photos/seed/brubeck/500/500', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-7.mp3', 6, '2026-11-26 18:09:02'),
(8, 'The Less I Know The Better', 'Tame Impala', 'https://picsum.photos/seed/tame/500/500', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-8.mp3', 9, '2026-11-26 18:09:02'),
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

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int UNSIGNED NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `pseudo` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `avatar_url` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `user_genres`
--

CREATE TABLE `user_genres` (
  `user_id` int UNSIGNED NOT NULL,
  `genre_id` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `genres`
--
ALTER TABLE `genres`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Index pour la table `playlists`
--
ALTER TABLE `playlists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_playlists_user` (`user_id`);

--
-- Index pour la table `playlist_tracks`
--
ALTER TABLE `playlist_tracks`
  ADD PRIMARY KEY (`playlist_id`,`track_id`),
  ADD KEY `fk_playlist_tracks_track` (`track_id`);

--
-- Index pour la table `swipes`
--
ALTER TABLE `swipes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_user_track_swipe` (`user_id`,`track_id`),
  ADD KEY `fk_swipes_track` (`track_id`);

--
-- Index pour la table `tracks`
--
ALTER TABLE `tracks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_tracks_genre` (`genre_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `user_genres`
--
ALTER TABLE `user_genres`
  ADD PRIMARY KEY (`user_id`,`genre_id`),
  ADD KEY `fk_user_genres_genre` (`genre_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `genres`
--
ALTER TABLE `genres`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `playlists`
--
ALTER TABLE `playlists`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `swipes`
--
ALTER TABLE `swipes`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `tracks`
--
ALTER TABLE `tracks`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `playlists`
--
ALTER TABLE `playlists`
  ADD CONSTRAINT `fk_playlists_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `playlist_tracks`
--
ALTER TABLE `playlist_tracks`
  ADD CONSTRAINT `fk_playlist_tracks_playlist` FOREIGN KEY (`playlist_id`) REFERENCES `playlists` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_playlist_tracks_track` FOREIGN KEY (`track_id`) REFERENCES `tracks` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `swipes`
--
ALTER TABLE `swipes`
  ADD CONSTRAINT `fk_swipes_track` FOREIGN KEY (`track_id`) REFERENCES `tracks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_swipes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `tracks`
--
ALTER TABLE `tracks`
  ADD CONSTRAINT `fk_tracks_genre` FOREIGN KEY (`genre_id`) REFERENCES `genres` (`id`) ON DELETE RESTRICT;

--
-- Contraintes pour la table `user_genres`
--
ALTER TABLE `user_genres`
  ADD CONSTRAINT `fk_user_genres_genre` FOREIGN KEY (`genre_id`) REFERENCES `genres` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user_genres_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
