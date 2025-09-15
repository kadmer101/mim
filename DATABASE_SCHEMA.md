# DATABASE_SCHEMA.md

# Mim Platform - Database Schema Design
*Comprehensive database structure for Laravel SaaS platform*

---

## 📋 Schema Overview

### Database Information
- **Database Engine**: MySQL 8.0+ / MariaDB 10.6+
- **Character Set**: utf8mb4_unicode_ci
- **Collation**: utf8mb4_unicode_ci
- **Time Zone**: UTC (all timestamps)
- **Storage Engine**: InnoDB

### Schema Categories
1. **User Management & Authentication**
2. **Role-Based Access Control (RBAC)**
3. **Challenge Management System**
4. **Payment & Subscription System**
5. **Gamification & Reputation**
6. **Notification System**
7. **Content Management**
8. **Analytics & Reporting**
9. **System Administration**

---

## 👥 User Management & Authentication

### users
**Purpose**: Core user accounts and profiles

CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL,
  `username` varchar(50) UNIQUE NOT NULL,
  `email` varchar(255) UNIQUE NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `nationality` varchar(3) DEFAULT NULL,
  `preferred_language` varchar(5) DEFAULT 'en',
  `timezone` varchar(50) DEFAULT 'UTC',
  `status` enum('active','suspended','banned','pending') DEFAULT 'active',
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_activity_at` timestamp NULL DEFAULT NULL,
  `two_factor_secret` text DEFAULT NULL,
  `two_factor_recovery_codes` text DEFAULT NULL,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_uuid_unique` (`uuid`),
  UNIQUE KEY `users_username_unique` (`username`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `idx_users_status` (`status`),
  KEY `idx_users_last_activity` (`last_activity_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
user_profiles
Purpose: Extended user profile information

CopyCREATE TABLE `user_profiles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `education_level` varchar(50) DEFAULT NULL,
  `religious_knowledge_level` enum('beginner','intermediate','advanced','scholar') DEFAULT 'beginner',
  `website` varchar(255) DEFAULT NULL,
  `social_media` json DEFAULT NULL,
  `privacy_settings` json DEFAULT NULL,
  `notification_preferences` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_profiles_user_id_unique` (`user_id`),
  CONSTRAINT `user_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
password_reset_tokens
Purpose: Laravel default password reset tokens

CopyCREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
sessions
Purpose: Laravel session storage

CopyCREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
🔐 Role-Based Access Control (RBAC)
roles
Purpose: System roles definition

CopyCREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(125) NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `guard_name` varchar(125) NOT NULL,
  `level` tinyint(4) DEFAULT 0,
  `is_system` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`),
  KEY `idx_roles_guard_name` (`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
permissions
Purpose: System permissions definition

CopyCREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(125) NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `guard_name` varchar(125) NOT NULL,
  `group` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`),
  KEY `idx_permissions_guard_name` (`guard_name`),
  KEY `idx_permissions_group` (`group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
model_has_roles
Purpose: User-role assignments

CopyCREATE TABLE `model_has_roles` (
  `role_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  `assigned_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `assigned_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  KEY `idx_model_has_roles_assigned_by` (`assigned_by`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
model_has_permissions
Purpose: User-permission assignments

CopyCREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  `assigned_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `assigned_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  KEY `idx_model_has_permissions_assigned_by` (`assigned_by`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
role_has_permissions
Purpose: Role-permission assignments

CopyCREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
🎯 Challenge Management System
categories
Purpose: Challenge categories (Quran, Hadith, History, etc.)

CopyCREATE TABLE `categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) UNIQUE NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `color` varchar(7) DEFAULT '#3B82F6',
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`),
  KEY `idx_categories_active` (`is_active`),
  KEY `idx_categories_parent` (`parent_id`),
  KEY `idx_categories_sort` (`sort_order`),
  CONSTRAINT `categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
challenges
Purpose: Main challenge submissions

CopyCREATE TABLE `challenges` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL,
  `submitter_id` bigint(20) unsigned NOT NULL,
  `category_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` longtext NOT NULL,
  `sources` json DEFAULT NULL,
  `difficulty_level` enum('beginner','intermediate','advanced','expert') DEFAULT 'beginner',
  `priority` enum('low','normal','high','urgent') DEFAULT 'normal',
  `status` enum('pending','under_review','open_debate','expert_review','resolved','closed','invalid') DEFAULT 'pending',
  `resolution_status` enum('refuted','accepted_mistake','withdrawn','duplicate') DEFAULT NULL,
  `debate_ends_at` timestamp NULL DEFAULT NULL,
  `review_starts_at` timestamp NULL DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `resolved_by` bigint(20) unsigned DEFAULT NULL,
  `resolution_summary` text DEFAULT NULL,
  `reward_amount` decimal(10,2) DEFAULT NULL,
  `reward_paid` tinyint(1) DEFAULT 0,
  `reward_paid_at` timestamp NULL DEFAULT NULL,
  `views_count` int(11) DEFAULT 0,
  `upvotes_count` int(11) DEFAULT 0,
  `downvotes_count` int(11) DEFAULT 0,
  `responses_count` int(11) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_archived` tinyint(1) DEFAULT 0,
  `meta_data` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `challenges_uuid_unique` (`uuid`),
  KEY `idx_challenges_submitter` (`submitter_id`),
  KEY `idx_challenges_category` (`category_id`),
  KEY `idx_challenges_status` (`status`),
  KEY `idx_challenges_resolution` (`resolution_status`),
  KEY `idx_challenges_featured` (`is_featured`),
  KEY `idx_challenges_created` (`created_at`),
  KEY `idx_challenges_views` (`views_count`),
  FULLTEXT KEY `challenges_title_description_fulltext` (`title`,`description`),
  CONSTRAINT `challenges_submitter_id_foreign` FOREIGN KEY (`submitter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `challenges_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `challenges_resolved_by_foreign` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
challenge_responses
Purpose: Responses and counter-arguments to challenges

CopyCREATE TABLE `challenge_responses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `challenge_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `response_type` enum('answer','counter_argument','clarification','expert_opinion') DEFAULT 'answer',
  `content` longtext NOT NULL,
  `sources` json DEFAULT NULL,
  `is_official` tinyint(1) DEFAULT 0,
  `is_accepted` tinyint(1) DEFAULT 0,
  `upvotes_count` int(11) DEFAULT 0,
  `downvotes_count` int(11) DEFAULT 0,
  `replies_count` int(11) DEFAULT 0,
  `is_flagged` tinyint(1) DEFAULT 0,
  `flag_reason` varchar(255) DEFAULT NULL,
  `flagged_by` bigint(20) unsigned DEFAULT NULL,
  `flagged_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_responses_challenge` (`challenge_id`),
  KEY `idx_responses_user` (`user_id`),
  KEY `idx_responses_parent` (`parent_id`),
  KEY `idx_responses_official` (`is_official`),
  KEY `idx_responses_accepted` (`is_accepted`),
  KEY `idx_responses_flagged` (`is_flagged`),
  FULLTEXT KEY `challenge_responses_content_fulltext` (`content`),
  CONSTRAINT `challenge_responses_challenge_id_foreign` FOREIGN KEY (`challenge_id`) REFERENCES `challenges` (`id`) ON DELETE CASCADE,
  CONSTRAINT `challenge_responses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `challenge_responses_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `challenge_responses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `challenge_responses_flagged_by_foreign` FOREIGN KEY (`flagged_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
challenge_votes
Purpose: User votes on challenges and responses

CopyCREATE TABLE `challenge_votes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `votable_type` varchar(255) NOT NULL,
  `votable_id` bigint(20) unsigned NOT NULL,
  `vote_type` enum('up','down') NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `challenge_votes_user_votable_unique` (`user_id`,`votable_id`,`votable_type`),
  KEY `idx_votes_votable` (`votable_id`,`votable_type`),
  KEY `idx_votes_type` (`vote_type`),
  CONSTRAINT `challenge_votes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
challenge_assignments
Purpose: Expert assignments to challenges

CopyCREATE TABLE `challenge_assignments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `challenge_id` bigint(20) unsigned NOT NULL,
  `expert_id` bigint(20) unsigned NOT NULL,
  `assigned_by` bigint(20) unsigned NOT NULL,
  `assigned_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `status` enum('assigned','accepted','declined','completed') DEFAULT 'assigned',
  `due_date` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `challenge_assignments_challenge_expert_unique` (`challenge_id`,`expert_id`),
  KEY `idx_assignments_expert` (`expert_id`),
  KEY `idx_assignments_assigned_by` (`assigned_by`),
  KEY `idx_assignments_status` (`status`),
  CONSTRAINT `challenge_assignments_challenge_id_foreign` FOREIGN KEY (`challenge_id`) REFERENCES `challenges` (`id`) ON DELETE CASCADE,
  CONSTRAINT `challenge_assignments_expert_id_foreign` FOREIGN KEY (`expert_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `challenge_assignments_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
challenge_media
Purpose: Media attachments for challenges

CopyCREATE TABLE `challenge_media` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `challenge_id` bigint(20) unsigned NOT NULL,
  `media_type` enum('image','document','video','audio') NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` bigint(20) unsigned NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `uploaded_by` bigint(20) unsigned NOT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_media_challenge` (`challenge_id`),
  KEY `idx_media_type` (`media_type`),
  KEY `idx_media_uploaded_by` (`uploaded_by`),
  CONSTRAINT `challenge_media_challenge_id_foreign` FOREIGN KEY (`challenge_id`) REFERENCES `challenges` (`id`) ON DELETE CASCADE,
  CONSTRAINT `challenge_media_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
💰 Payment & Subscription System
user_subscriptions
Purpose: User subscription management

CopyCREATE TABLE `user_subscriptions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `subscription_type` enum('monthly','annual') DEFAULT 'monthly',
  `status` enum('active','cancelled','expired','suspended','trial') DEFAULT 'active',
  `starts_at` timestamp NOT NULL,
  `ends_at` timestamp NOT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `trial_ends_at` timestamp NULL DEFAULT NULL,
  `grace_period_ends_at` timestamp NULL DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'USD',
  `auto_renewal` tinyint(1) DEFAULT 1,
  `payment_method` enum('crypto','bank_transfer','paypal') DEFAULT 'crypto',
  `next_billing_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_subscriptions_user_id_unique` (`user_id`),
  KEY `idx_subscriptions_status` (`status`),
  KEY `idx_subscriptions_ends_at` (`ends_at`),
  KEY `idx_subscriptions_billing` (`next_billing_at`),
  CONSTRAINT `user_subscriptions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
wallet_addresses
Purpose: User cryptocurrency wallet addresses

CopyCREATE TABLE `wallet_addresses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `wallet_type` enum('binance','metamask','trust_wallet','other') NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'USDT',
  `address` varchar(255) NOT NULL,
  `network` varchar(50) DEFAULT 'BEP20',
  `is_verified` tinyint(1) DEFAULT 0,
  `is_primary` tinyint(1) DEFAULT 0,
  `verification_code` varchar(100) DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wallet_addresses_user_currency_unique` (`user_id`,`currency`),
  KEY `idx_wallets_verified` (`is_verified`),
  KEY `idx_wallets_primary` (`is_primary`),
  CONSTRAINT `wallet_addresses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
payment_transactions
Purpose: All payment transactions and history

CopyCREATE TABLE `payment_transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `transaction_type` enum('subscription','reward','refund','fee') NOT NULL,
  `transaction_method` enum('crypto','bank_transfer','paypal') NOT NULL,
  `status` enum('pending','processing','completed','failed','cancelled','refunded') DEFAULT 'pending',
  `amount` decimal(15,8) NOT NULL,
  `currency` varchar(10) NOT NULL,
  `exchange_rate` decimal(15,8) DEFAULT NULL,
  `amount_usd` decimal(10,2) DEFAULT NULL,
  `external_transaction_id` varchar(255) DEFAULT NULL,
  `wallet_address_from` varchar(255) DEFAULT NULL,
  `wallet_address_to` varchar(255) DEFAULT NULL,
  `network` varchar(50) DEFAULT NULL,
  `gas_fee` decimal(15,8) DEFAULT NULL,
  `description` varchar(500) DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `failed_reason` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_transactions_uuid_unique` (`uuid`),
  KEY `idx_transactions_user` (`user_id`),
  KEY `idx_transactions_status` (`status`),
  KEY `idx_transactions_type` (`transaction_type`),
  KEY `idx_transactions_external` (`external_transaction_id`),
  KEY `idx_transactions_processed` (`processed_at`),
  CONSTRAINT `payment_transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
reward_claims
Purpose: Reward claim management

CopyCREATE TABLE `reward_claims` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `challenge_id` bigint(20) unsigned NOT NULL,
  `claimant_id` bigint(20) unsigned NOT NULL,
  `claim_amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'USD',
  `status` enum('submitted','under_review','approved','rejected','paid') DEFAULT 'submitted',
  `reviewed_by` bigint(20) unsigned DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `review_notes` text DEFAULT NULL,
  `approved_amount` decimal(10,2) DEFAULT NULL,
  `payment_transaction_id` bigint(20) unsigned DEFAULT NULL,
  `rejection_reason` varchar(500) DEFAULT NULL,
  `evidence` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reward_claims_challenge_claimant_unique` (`challenge_id`,`claimant_id`),
  KEY `idx_reward_claims_status` (`status`),
  KEY `idx_reward_claims_claimant` (`claimant_id`),
  KEY `idx_reward_claims_reviewer` (`reviewed_by`),
  CONSTRAINT `reward_claims_challenge_id_foreign` FOREIGN KEY (`challenge_id`) REFERENCES `challenges` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reward_claims_claimant_id_foreign` FOREIGN KEY (`claimant_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reward_claims_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reward_claims_payment_transaction_id_foreign` FOREIGN KEY (`payment_transaction_id`) REFERENCES `payment_transactions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
🎮 Gamification & Reputation System
reputation_points
Purpose: User reputation tracking

CopyCREATE TABLE `reputation_points` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `action_type` enum('challenge_submit','response_post','upvote_received','downvote_received','expert_review','badge_earned','daily_login','streak_bonus','referral') NOT NULL,
  `points` int(11) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `related_type` varchar(255) DEFAULT NULL,
  `related_id` bigint(20) unsigned DEFAULT NULL,
  `awarded_by` bigint(20) unsigned DEFAULT NULL,
  `is_bonus` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_reputation_user` (`user_id`),
  KEY `idx_reputation_action` (`action_type`),
  KEY `idx_reputation_related` (`related_id`,`related_type`),
  KEY `idx_reputation_awarded_by` (`awarded_by`),
  CONSTRAINT `reputation_points_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reputation_points_awarded_by_foreign` FOREIGN KEY (`awarded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
user_reputation_summary
Purpose: Aggregated reputation data for performance

CopyCREATE TABLE `user_reputation_summary` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `total_points` int(11) DEFAULT 0,
  `rank_position` int(11) DEFAULT NULL,
  `level` int(11) DEFAULT 1,
  `points_to_next_level` int(11) DEFAULT 100,
  `monthly_points` int(11) DEFAULT 0,
  `weekly_points` int(11) DEFAULT 0,
  `daily_points` int(11) DEFAULT 0,
  `last_activity_points_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_reputation_summary_user_id_unique` (`user_id`),
  KEY `idx_reputation_summary_total` (`total_points`),
  KEY `idx_reputation_summary_rank` (`rank_position`),
  KEY `idx_reputation_summary_level` (`level`),
  CONSTRAINT `user_reputation_summary_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
achievement_badges
Purpose: Available achievement badges

CopyCREATE TABLE `achievement_badges` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) UNIQUE NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `color` varchar(7) DEFAULT '#3B82F6',
  `category` varchar(50) DEFAULT 'general',
  `requirements` json DEFAULT NULL,
  `points_reward` int(11) DEFAULT 0,
  `rarity` enum('common','rare','epic','legendary') DEFAULT 'common',
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `achievement_badges_slug_unique` (`slug`),
  KEY `idx_badges_category` (`category`),
  KEY `idx_badges_rarity` (`rarity`),
  KEY `idx_badges_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
user_achievement_badges
Purpose: User earned badges

CopyCREATE TABLE `user_achievement_badges` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `badge_id` bigint(20) unsigned NOT NULL,
  `earned_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `progress` json DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `notified` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_achievement_badges_user_badge_unique` (`user_id`,`badge_id`),
  KEY `idx_user_badges_earned` (`earned_at`),
  KEY `idx_user_badges_featured` (`is_featured`),
  CONSTRAINT `user_achievement_badges_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_achievement_badges_badge_id_foreign` FOREIGN KEY (`badge_id`) REFERENCES `achievement_badges` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
leaderboards
Purpose: Different leaderboard types

CopyCREATE TABLE `leaderboards` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `leaderboard_type` enum('reputation','challenges','responses','monthly','weekly','all_time') NOT NULL,
  `score` bigint(20) DEFAULT 0,
  `rank_position` int(11) NOT NULL,
  `previous_rank` int(11) DEFAULT NULL,
  `rank_change` int(11) DEFAULT 0,
  `period_start` timestamp NULL DEFAULT NULL,
  `period_end` timestamp NULL DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `leaderboards_user_type_period_unique` (`user_id`,`leaderboard_type`,`period_start`),
  KEY `idx_leaderboards_type_rank` (`leaderboard_type`,`rank_position`),
  KEY `idx_leaderboards_score` (`score`),
  CONSTRAINT `leaderboards_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
user_streaks
Purpose: User activity streaks

CopyCREATE TABLE `user_streaks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `streak_type` enum('daily_login','daily_activity','weekly_challenge','monthly_engagement') NOT NULL,
  `current_streak` int(11) DEFAULT 0,
  `longest_streak` int(11) DEFAULT 0,
  `last_activity_date` date DEFAULT NULL,
  `streak_started_at` timestamp NULL DEFAULT NULL,
  `streak_broken_at` timestamp NULL DEFAULT NULL,
  `bonus_points_earned` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_streaks_user_type_unique` (`user_id`,`streak_type`),
  KEY `idx_streaks_current` (`current_streak`),
  KEY `idx_streaks_longest` (`longest_streak`),
  CONSTRAINT `user_streaks_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
🔔 Notification System
notifications
Purpose: Laravel default notifications table

CopyCREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) unsigned NOT NULL,
  `data` json NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
notification_preferences
Purpose: User notification settings

CopyCREATE TABLE `notification_preferences` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `notification_type` varchar(100) NOT NULL,
  `channel` enum('database','mail','sms','push') NOT NULL,
  `is_enabled` tinyint(1) DEFAULT 1,
  `frequency` enum('immediate','daily','weekly','monthly') DEFAULT 'immediate',
  `quiet_hours_start` time DEFAULT NULL,
  `quiet_hours_end` time DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `notification_preferences_user_type_channel_unique` (`user_id`,`notification_type`,`channel`),
  KEY `idx_notification_preferences_enabled` (`is_enabled`),
  CONSTRAINT `notification_preferences_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
push_notification_tokens
Purpose: Push notification device tokens

CopyCREATE TABLE `push_notification_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `device_type` enum('ios','android','web') NOT NULL,
  `device_token` varchar(500) NOT NULL,
  `device_id` varchar(255) DEFAULT NULL,
  `app_version` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `push_notification_tokens_token_unique` (`device_token`),
  KEY `idx_push_tokens_user` (`user_id`),
  KEY `idx_push_tokens_active` (`is_active`),
  CONSTRAINT `push_notification_tokens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
📄 Content Management
pages
Purpose: Static pages (About, Terms, Privacy, etc.)

CopyCREATE TABLE `pages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) UNIQUE NOT NULL,
  `content` longtext DEFAULT NULL,
  `excerpt` text DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `is_published` tinyint(1) DEFAULT 0,
  `template` varchar(100) DEFAULT 'default',
  `author_id` bigint(20) unsigned DEFAULT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pages_slug_unique` (`slug`),
  KEY `idx_pages_published` (`is_published`),
  KEY `idx_pages_author` (`author_id`),
  CONSTRAINT `pages_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
faqs
Purpose: Frequently asked questions

CopyCREATE TABLE `faqs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint(20) unsigned DEFAULT NULL,
  `question` varchar(500) NOT NULL,
  `answer` longtext NOT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `is_published` tinyint(1) DEFAULT 1,
  `views_count` int(11) DEFAULT 0,
  `helpful_count` int(11) DEFAULT 0,
  `not_helpful_count` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_faqs_category` (`category_id`),
  KEY `idx_faqs_featured` (`is_featured`),
  KEY `idx_faqs_published` (`is_published`),
  FULLTEXT KEY `faqs_question_answer_fulltext` (`question`,`answer`),
  CONSTRAINT `faqs_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
announcements
Purpose: Platform announcements and news

CopyCREATE TABLE `announcements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `type` enum('info','success','warning','danger') DEFAULT 'info',
  `priority` enum('low','normal','high','urgent') DEFAULT 'normal',
  `target_audience` enum('all','members','experts','admins') DEFAULT 'all',
  `is_dismissible` tinyint(1) DEFAULT 1,
  `is_published` tinyint(1) DEFAULT 0,
  `show_from` timestamp NULL DEFAULT NULL,
  `show_until` timestamp NULL DEFAULT NULL,
  `author_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_announcements_published` (`is_published`),
  KEY `idx_announcements_priority` (`priority`),
  KEY `idx_announcements_show_period` (`show_from`,`show_until`),
  KEY `idx_announcements_author` (`author_id`),
  CONSTRAINT `announcements_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
user_announcement_dismissals
Purpose: Track dismissed announcements per user

CopyCREATE TABLE `user_announcement_dismissals` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `announcement_id` bigint(20) unsigned NOT NULL,
  `dismissed_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_announcement_dismissals_user_announcement_unique` (`user_id`,`announcement_id`),
  CONSTRAINT `user_announcement_dismissals_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_announcement_dismissals_announcement_id_foreign` FOREIGN KEY (`announcement_id`) REFERENCES `announcements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
📊 Analytics & Reporting
platform_statistics
Purpose: Daily platform statistics

CopyCREATE TABLE `platform_statistics` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `total_users` int(11) DEFAULT 0,
  `active_subscribers` int(11) DEFAULT 0,
  `new_registrations` int(11) DEFAULT 0,
  `challenges_submitted` int(11) DEFAULT 0,
  `challenges_resolved` int(11) DEFAULT 0,
  `responses_posted` int(11) DEFAULT 0,
  `total_votes_cast` int(11) DEFAULT 0,
  `rewards_paid` decimal(15,2) DEFAULT 0.00,
  `revenue_collected` decimal(15,2) DEFAULT 0.00,
  `page_views` bigint(20) DEFAULT 0,
  `unique_visitors` int(11) DEFAULT 0,
  `avg_session_duration` int(11) DEFAULT 0,
  `bounce_rate` decimal(5,2) DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `platform_statistics_date_unique` (`date`),
  KEY `idx_statistics_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
user_activity_logs
Purpose: Detailed user activity tracking

CopyCREATE TABLE `user_activity_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `activity_type` varchar(100) NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `url` varchar(500) DEFAULT NULL,
  `method` varchar(10) DEFAULT NULL,
  `related_type` varchar(255) DEFAULT NULL,
  `related_id` bigint(20) unsigned DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_activity_logs_user` (`user_id`),
  KEY `idx_activity_logs_type` (`activity_type`),
  KEY `idx_activity_logs_related` (`related_id`,`related_type`),
  KEY `idx_activity_logs_created` (`created_at`),
  CONSTRAINT `user_activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
api_usage_logs
Purpose: API usage tracking and rate limiting

CopyCREATE TABLE `api_usage_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `endpoint` varchar(255) NOT NULL,
  `method` varchar(10) NOT NULL,
  `response_status` int(11) NOT NULL,
  `response_time` int(11) DEFAULT NULL,
  `request_size` bigint(20) DEFAULT NULL,
  `response_size` bigint(20) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_api_usage_user` (`user_id`),
  KEY `idx_api_usage_ip` (`ip_address`),
  KEY `idx_api_usage_endpoint` (`endpoint`),
  KEY `idx_api_usage_status` (`response_status`),
  KEY `idx_api_usage_created` (`created_at`),
  CONSTRAINT `api_usage_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
⚙️ System Administration
settings
Purpose: System-wide settings and configurations

CopyCREATE TABLE `settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `group` varchar(100) NOT NULL DEFAULT 'general',
  `key` varchar(255) NOT NULL,
  `value` longtext DEFAULT NULL,
  `type` enum('string','integer','boolean','json','float') DEFAULT 'string',
  `is_public` tinyint(1) DEFAULT 0,
  `description` text DEFAULT NULL,
  `validation_rules` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_group_key_unique` (`group`,`key`),
  KEY `idx_settings_group` (`group`),
  KEY `idx_settings_public` (`is_public`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
failed_jobs
Purpose: Laravel failed jobs queue

CopyCREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
jobs
Purpose: Laravel jobs queue

CopyCREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
job_batches
Purpose: Laravel batch jobs

CopyCREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
cache
Purpose: Laravel database cache

CopyCREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
cache_locks
Purpose: Laravel cache locks

CopyCREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
🔧 Migration Commands
Core Laravel Migrations
Copy# Default Laravel tables
php artisan migrate

# Create custom migrations
php artisan make:migration create_user_profiles_table
php artisan make:migration create_categories_table
php artisan make:migration create_challenges_table
php artisan make:migration create_challenge_responses_table
php artisan make:migration create_challenge_votes_table
php artisan make:migration create_challenge_assignments_table
php artisan make:migration create_challenge_media_table
php artisan make:migration create_user_subscriptions_table
php artisan make:migration create_wallet_addresses_table
php artisan make:migration create_payment_transactions_table
php artisan make:migration create_reward_claims_table
php artisan make:migration create_reputation_points_table
php artisan make:migration create_user_reputation_summary_table
php artisan make:migration create_achievement_badges_table
php artisan make:migration create_user_achievement_badges_table
php artisan make:migration create_leaderboards_table
php artisan make:migration create_user_streaks_table
php artisan make:migration create_notification_preferences_table
php artisan make:migration create_push_notification_tokens_table
php artisan make:migration create_pages_table
php artisan make:migration create_faqs_table
php artisan make:migration create_announcements_table
php artisan make:migration create_user_announcement_dismissals_table
php artisan make:migration create_platform_statistics_table
php artisan make:migration create_user_activity_logs_table
php artisan make:migration create_api_usage_logs_table
php artisan make:migration create_settings_table

# Run all migrations
php artisan migrate

# Migration rollback commands (if needed)
php artisan migrate:rollback
php artisan migrate:rollback --step=5
php artisan migrate:reset
php artisan migrate:fresh
php artisan migrate:fresh --seed
Package Migrations
Copy# Spatie Permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate

# Laravel Cashier
php artisan vendor:publish --tag="cashier-migrations"
php artisan migrate

# Spatie Activity Log
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-migrations"
php artisan migrate

# Spatie Media Library
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="migrations"
php artisan migrate

# Laravel Telescope (Development)
php artisan telescope:install
php artisan migrate

# Laravel Scout
php artisan vendor:publish --provider="Laravel\Scout\ScoutServiceProvider"

# Create failed jobs table
php artisan queue:failed-table
php artisan migrate

# Create notifications table
php artisan notifications:table
php artisan migrate
🌱 Database Seeders
Create Seeder Files
Copyphp artisan make:seeder DatabaseSeeder
php artisan make:seeder UserRolesSeeder
php artisan make:seeder PermissionsSeeder
php artisan make:seeder AdminUserSeeder
php artisan make:seeder CategoriesSeeder
php artisan make:seeder AchievementBadgesSeeder
php artisan make:seeder SettingsSeeder
php artisan make:seeder PagesSeeder
php artisan make:seeder FaqsSeeder
php artisan make:seeder DemoDataSeeder
Run Seeders
Copy# Run all seeders
php artisan db:seed

# Run specific seeder
php artisan db:seed --class=UserRolesSeeder
php artisan db:seed --class=PermissionsSeeder
php artisan db:seed --class=AdminUserSeeder
php artisan db:seed --class=CategoriesSeeder
php artisan db:seed --class=AchievementBadgesSeeder
php artisan db:seed --class=SettingsSeeder

# Fresh migrate with seed
php artisan migrate:fresh --seed
📈 Database Indexes & Optimization
Key Indexes for Performance
Copy-- Users table indexes
CREATE INDEX idx_users_status ON users(status);
CREATE INDEX idx_users_last_activity ON users(last_activity_at);
CREATE INDEX idx_users_created ON users(created_at);

-- Challenges table indexes
CREATE INDEX idx_challenges_submitter_status ON challenges(submitter_id, status);
CREATE INDEX idx_challenges_category_status ON challenges(category_id, status);
CREATE INDEX idx_challenges_created_status ON challenges(created_at, status);
CREATE INDEX idx_challenges_featured_status ON challenges(is_featured, status);
CREATE INDEX idx_challenges_resolution_status ON challenges(resolution_status, status);

-- Challenge responses indexes
CREATE INDEX idx_responses_challenge_created ON challenge_responses(challenge_id, created_at);
CREATE INDEX idx_responses_user_created ON challenge_responses(user_id, created_at);

-- Payment transactions indexes
CREATE INDEX idx_transactions_user_status ON payment_transactions(user_id, status);
CREATE INDEX idx_transactions_type_status ON payment_transactions(transaction_type, status);
CREATE INDEX idx_transactions_created_status ON payment_transactions(created_at, status);

-- Reputation points indexes
CREATE INDEX idx_reputation_user_created ON reputation_points(user_id, created_at);
CREATE INDEX idx_reputation_user_action ON reputation_points(user_id, action_type);

-- Activity logs indexes (for cleanup)
CREATE INDEX idx_activity_logs_created ON user_activity_logs(created_at);
CREATE INDEX idx_api_usage_created ON api_usage_logs(created_at);
Database Maintenance Commands
Copy# Analyze table performance
php artisan tinker
>>> DB::select('SHOW TABLE STATUS');

# Check index usage
>>> DB::select('SHOW INDEX FROM challenges');

# Optimize tables
>>> DB::statement('OPTIMIZE TABLE challenges, challenge_responses, users');

# Create database backup
php artisan backup:run

# Clean old activity logs (custom command)
php artisan cleanup:old-logs --days=90
🔒 Database Security & Constraints
Foreign Key Constraints
All foreign keys have proper CASCADE/SET NULL constraints
User deletion cascades to related data appropriately
Challenge deletion maintains referential integrity
Payment data preserved even if user is deleted (for accounting)
Data Validation at Database Level
ENUM constraints for status fields
Decimal precision for monetary values
Proper character sets for international content
Unique constraints prevent duplicates
Database Backup Strategy
Copy# Daily backup command
php artisan backup:run --only-db

# Full backup with files
php artisan backup:run

# Restore from backup
php artisan backup:restore latest
📋 Schema Summary
Total Tables: 40+
Core Laravel: 6 tables (users, sessions, etc.)
Authentication & Roles: 5 tables
Challenge System: 7 tables
Payment System: 4 tables
Gamification: 6 tables
Notifications: 3 tables
Content Management: 4 tables
Analytics: 3 tables
System Administration: 8 tables
Database Size Estimates
Development: ~50MB
Production (1K users): ~500MB
Production (10K users): ~2GB
Production (100K users): ~10GB
Performance Considerations
Properly indexed for common queries
Partitioning strategy for large tables
Archive strategy for old data
Cache frequently accessed data
This comprehensive database schema supports all features of the Mim platform while maintaining performance, security, and scalability.