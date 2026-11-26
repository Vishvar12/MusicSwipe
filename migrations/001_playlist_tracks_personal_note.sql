-- Migration 001 — Ticket T4 (US-07)
-- Ajoute la note personnelle d'un morceau liké (Update du CRUD « Mes likes »).
-- À exécuter une seule fois dans phpMyAdmin (onglet SQL) sur une base créée avant T4.
-- Une installation neuve via database.sql contient déjà cette colonne.

ALTER TABLE `playlist_tracks`
  ADD COLUMN `personal_note` VARCHAR(255) COLLATE utf8mb4_general_ci NULL DEFAULT NULL AFTER `added_at`;
