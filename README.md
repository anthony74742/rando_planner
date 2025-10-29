# 🏔️ Randonnée Planner

> **Application Symfony** permettant de planifier, organiser et partager des randonnées entre utilisateurs.  
> Ce projet sert de support d’apprentissage pour approfondir les bonnes pratiques de développement backend avec **Symfony 7** et **Doctrine ORM**.

---

## 🎯 Objectif d’apprentissage

Développer une application Symfony complète et modulaire autour de la planification de randonnées, tout en apprenant à :

- Concevoir une architecture claire et maintenable avec entités, services et repositories.
- Mettre en place une authentification sécurisée et une gestion des rôles utilisateurs.
- Gérer les relations complexes entre utilisateurs, randonnées, sessions et invitations via Doctrine ORM.
- Intégrer des services externes (API météo, cartes interactives) dans la logique applicative.
- Créer une interface utilisateur cohérente et fonctionnelle avec Twig.

---

## 🧾 Brief du projet

**Nom du projet :** Randonnée Planner  
**Description :**  
Une application web permettant aux utilisateurs de :
- Créer des **randonnées** (titre, lieu, description, difficulté, distance).
- Planifier des **sessions de randonnée** (dates précises, organisateur, remarques).
- Inviter d’autres utilisateurs à participer à une **session** donnée.
- Gérer leur profil et consulter l’historique de leurs activités.

**Technologies principales :**
- 🧩 Symfony 7
- 🐘 PHP 8.2+
- 🐘 PostgreSQL
- ⚙️ Doctrine ORM
- 🎨 Twig
- 🔐 Symfony Security

---

## 🗺️ Roadmap du projet

### 🧱 **Phase 1 — Base du projet**
> **Objectif :** Mise en place du socle technique

- [x] Installation du projet Symfony
- [x] Configuration de la base PostgreSQL
- [x] Création de l’entité `User`
- [x] Système d’authentification (inscription / connexion / profil)
- [x] Sécurité et fixtures de test

✅ **Livrable :**  
Projet fonctionnel avec gestion des comptes utilisateurs et base connectée.

---

### 🏞️ **Phase 2 — Gestion des randonnées (`Hike`)**
> **Objectif :** Permettre aux utilisateurs de créer et gérer leurs randonnées

- [x] Création de l’entité `Hike`
- [x] CRUD complet (ajout, édition, suppression, affichage)
- [x] Lien automatique entre la randonnée et son créateur (`User`)
- [x] Validation des données et affichage avec Twig
- [x] Introduction d’un service `HikeService` pour isoler la logique métier

✅ **Livrable :**  
Un utilisateur connecté peut créer, modifier et consulter ses randonnées.

---

### 🗓️ **Phase 3 — Gestion des sessions de randonnée (`HikeSession`)**
> **Objectif :** Permettre de planifier plusieurs dates pour une même randonnée

- [x] Création de l’entité `HikeSession`
- [x] Lien avec `Hike` (ManyToOne) et `User` (créateur)
- [x] Création d’un CRUD pour les sessions
- [ ] Association automatique d’une session à la randonnée choisie
- [ ] Affichage des sessions sur la page d’une randonnée

✅ **Livrable :**  
Chaque randonnée peut avoir plusieurs sessions planifiées avec date et organisateur.

---

### 🫂 **Phase 4 — Invitations et participants**
> **Objectif :** Inviter des utilisateurs à rejoindre une session de randonnée

- [ ] Création de l’entité `Invitation`
- [ ] Création de la table `session_participants` (ManyToMany entre `User` et `HikeSession`)
- [ ] Service métier `InvitationService` (envoi, acceptation, refus)
- [ ] Gestion du statut (`pending`, `accepted`, `declined`)
- [ ] Affichage des participants et des invitations reçues/envoyées

✅ **Livrable :**  
Les utilisateurs peuvent inviter d’autres à participer à une session spécifique.

---

### ✨ **Phase 5 — Améliorations et intégrations externes**
> **Objectif :** Enrichir l’expérience et connecter des services externes

- [ ] Intégration d’une **carte interactive** (Leaflet / Mapbox)
- [ ] Connexion à une **API météo** (OpenWeatherMap) pour les sessions
- [ ] Statistiques utilisateur (km parcourus, randos effectuées)
- [ ] Interface améliorée et messages utilisateurs (feedback, alertes, notifications)

✅ **Livrable :**  
Une application complète, ergonomique et connectée à des services externes.

---

## 🧠 Compétences visées

| Domaine | Compétence acquise |
|----------|-------------------|
| 🧩 **Symfony avancé** | Structuration modulaire du code (entités, services, repository) |
| 🗄️ **Doctrine ORM** | Gestion des relations 1–*, *–*, migrations et requêtes optimisées |
| 🔐 **Sécurité** | Authentification, rôles et gestion d’accès sécurisée |
| ⚙️ **Architecture logicielle** | Séparation des responsabilités et logique métier claire |
| 🌐 **Intégration externe** | Connexion à une API (météo, cartes) et gestion des données externes |
| 🎨 **Interface utilisateur** | Création de vues cohérentes et fonctionnelles avec Twig |

---
