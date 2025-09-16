# DATABASE_SCHEMA_V2.md

# Mim Platform - Database Schema V2
*Optimized database structure based on INSTALLATION_GUIDE.md and DIRECTORY_STRUCTURE.md*

## 📋 Schema Overview V2

### Database Information
- **Database Engine**: MySQL 8.0+ / MariaDB 10.6+
- **Character Set**: utf8mb4_unicode_ci
- **Collation**: utf8mb4_unicode_ci
- **Time Zone**: UTC (all timestamps)
- **Storage Engine**: InnoDB

### Enhanced Schema Categories (V2)
1. **Core Laravel Tables** (Enhanced Users)
2. **Challenge Management System**
3. **Payment & Subscription System**
4. **Gamification & Reputation**
5. **Role-Based Access Control (RBAC)**
6. **Notification System**
7. **Content Management**
8. **Analytics & System Administration**

---

## 🔧 Core Laravel Tables (Enhanced)

### 1. Enhanced Users Table
```sql
-- Add fields to existing Laravel users table
ALTER TABLE `users` ADD COLUMN `uuid` char(36) UNIQUE AFTER `id`;
ALTER TABLE `users` ADD COLUMN `username` varchar(50) UNIQUE AFTER `uuid`;
ALTER TABLE `users` ADD COLUMN `first_name` varchar(100) AFTER `password`;
ALTER TABLE `users` ADD COLUMN `last_name` varchar(100) AFTER `first_name`;
ALTER TABLE `users` ADD COLUMN `avatar` varchar(255) NULL AFTER `last_name`;
ALTER TABLE `users` ADD COLUMN `bio` text NULL AFTER `avatar`;
ALTER TABLE `users` ADD COLUMN `nationality` varchar(2) NULL AFTER `bio`;
ALTER TABLE `users` ADD COLUMN `preferred_language` varchar(5) DEFAULT 'en' AFTER `nationality`;
ALTER TABLE `users` ADD COLUMN `timezone` varchar(50) DEFAULT 'UTC' AFTER `preferred_language`;
ALTER TABLE `users` ADD COLUMN `status` enum('active','inactive','suspended','banned') DEFAULT 'active' AFTER `timezone`;
ALTER TABLE `users` ADD COLUMN `last_login_at` timestamp NULL AFTER `status`;
ALTER TABLE `users` ADD COLUMN `last_activity_at` timestamp NULL AFTER `last_login_at`;
ALTER TABLE `users` ADD COLUMN `two_factor_secret` text NULL AFTER `remember_token`;
ALTER TABLE `users` ADD COLUMN `two_factor_recovery_codes` text NULL AFTER `two_factor_secret`;
ALTER TABLE `users` ADD COLUMN `two_factor_confirmed_at` timestamp NULL AFTER `two_factor_recovery_codes`;

-- Add indexes
ALTER TABLE `users` ADD INDEX `idx_users_status` (`status`);
ALTER TABLE `users` ADD INDEX `idx_users_last_activity` (`last_activity_at`);
ALTER TABLE `users` ADD INDEX `idx_users_username` (`username`);
```

---

## 🎯 Challenge Management System

### 2. Categories Table
```sql
CREATE TABLE `categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) UNIQUE NOT NULL,
  `description` text,
  `parent_id` bigint(20) unsigned NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `icon` varchar(100) NULL,
  `color` varchar(7) DEFAULT '#3B82F6',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_uuid_unique` (`uuid`),
  UNIQUE KEY `categories_slug_unique` (`slug`),
  KEY `idx_categories_parent` (`parent_id`),
  KEY `idx_categories_active` (`is_active`),
  CONSTRAINT `fk_categories_parent` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 3. Challenges Table
```sql
CREATE TABLE `challenges` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `category_id` bigint(20) unsigned NOT NULL,
  `submitted_by` bigint(20) unsigned NOT NULL,
  `assigned_expert_id` bigint(20) unsigned NULL,
  `resolved_by` bigint(20) unsigned NULL,
  `status` enum('pending','under_review','expert_assigned','resolved','rejected') DEFAULT 'pending',
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `difficulty_level` int(11) DEFAULT 1,
  `votes_up` int(11) DEFAULT 0,
  `votes_down` int(11) DEFAULT 0,
  `views_count` int(11) DEFAULT 0,
  `responses_count` int(11) DEFAULT 0,
  `resolution` longtext NULL,
  `resolution_date` timestamp NULL,
  `reward_amount` decimal(15,2) DEFAULT 999.00,
  `is_rewarded` tinyint(1) DEFAULT 0,
  `metadata` json NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `challenges_uuid_unique` (`uuid`),
  KEY `idx_challenges_status` (`status`),
  KEY `idx_challenges_category` (`category_id`),
  KEY `idx_challenges_submitter` (`submitted_by`),
  KEY `idx_challenges_expert` (`assigned_expert_id`),
  KEY `idx_challenges_created` (`created_at`),
  FULLTEXT KEY `ft_challenges_search` (`title`,`content`),
  CONSTRAINT `fk_challenges_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_challenges_submitter` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_challenges_expert` FOREIGN KEY (`assigned_expert_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_challenges_resolver` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 4. Challenge Responses Table
```sql
CREATE TABLE `challenge_responses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL,
  `challenge_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `parent_id` bigint(20) unsigned NULL,
  `content` longtext NOT NULL,
  `response_type` enum('public_comment','expert_analysis','admin_note') DEFAULT 'public_comment',
  `votes_up` int(11) DEFAULT 0,
  `votes_down` int(11) DEFAULT 0,
  `is_accepted` tinyint(1) DEFAULT 0,
  `is_flagged` tinyint(1) DEFAULT 0,
  `metadata` json NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `challenge_responses_uuid_unique` (`uuid`),
  KEY `idx_responses_challenge` (`challenge_id`),
  KEY `idx_responses_user` (`user_id`),
  KEY `idx_responses_parent` (`parent_id`),
  KEY `idx_responses_type` (`response_type`),
  FULLTEXT KEY `ft_responses_search` (`content`),
  CONSTRAINT `fk_responses_challenge` FOREIGN KEY (`challenge_id`) REFERENCES `challenges` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_responses_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_responses_parent` FOREIGN KEY (`parent_id`) REFERENCES `challenge_responses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 5. Challenge Votes Table
```sql
CREATE TABLE `challenge_votes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `votable_type` varchar(255) NOT NULL,
  `votable_id` bigint(20) unsigned NOT NULL,
  `vote_type` enum('up','down') NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_vote` (`user_id`,`votable_type`,`votable_id`),
  KEY `idx_votes_votable` (`votable_type`,`votable_id`),
  CONSTRAINT `fk_votes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 💰 Payment & Subscription System

### 6. User Subscriptions Table
```sql
CREATE TABLE `user_subscriptions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `subscription_type` enum('monthly','yearly') DEFAULT 'monthly',
  `status` enum('active','inactive','suspended','cancelled','expired') DEFAULT 'inactive',
  `price` decimal(10,2) DEFAULT 99.00,
  `currency` varchar(3) DEFAULT 'USDT',
  `start_date` timestamp NULL,
  `end_date` timestamp NULL,
  `next_billing_date` timestamp NULL,
  `auto_renew` tinyint(1) DEFAULT 1,
  `grace_period_until` timestamp NULL,
  `cancelled_at` timestamp NULL,
  `metadata` json NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_subscriptions_user_unique` (`user_id`),
  KEY `idx_subscriptions_status` (`status`),
  KEY `idx_subscriptions_billing` (`next_billing_date`),
  CONSTRAINT `fk_subscriptions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 7. Wallet Addresses Table
```sql
CREATE TABLE `wallet_addresses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `currency` varchar(10) NOT NULL,
  `address` varchar(255) NOT NULL,
  `network` varchar(50) DEFAULT 'BSC',
  `is_primary` tinyint(1) DEFAULT 0,
  `is_verified` tinyint(1) DEFAULT 0,
  `verified_at` timestamp NULL,
  `last_used_at` timestamp NULL,
  `metadata` json NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_currency` (`user_id`,`currency`),
  KEY `idx_wallets_address` (`address`),
  KEY `idx_wallets_currency` (`currency`),
  CONSTRAINT `fk_wallets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 8. Payment Transactions Table
```sql
CREATE TABLE `payment_transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL,
  `user_id` bigint(20) unsigned NULL,
  `transaction_type` enum('subscription','reward','refund','fee') NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `currency` varchar(3) NOT NULL,
  `status` enum('pending','completed','failed','cancelled','refunded') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT 'binance',
  `external_id` varchar(255) NULL,
  `binance_order_id` varchar(255) NULL,
  `from_address` varchar(255) NULL,
  `to_address` varchar(255) NULL,
  `network` varchar(50) NULL,
  `tx_hash` varchar(255) NULL,
  `confirmations` int(11) DEFAULT 0,
  `required_confirmations` int(11) DEFAULT 6,
  `fee_amount` decimal(15,8) DEFAULT 0,
  `processed_at` timestamp NULL,
  `metadata` json NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `transactions_uuid_unique` (`uuid`),
  KEY `idx_transactions_user` (`user_id`),
  KEY `idx_transactions_status` (`status`),
  KEY `idx_transactions_type` (`transaction_type`),
  KEY `idx_transactions_external` (`external_id`),
  KEY `idx_transactions_binance` (`binance_order_id`),
  CONSTRAINT `fk_transactions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 9. Reward Claims Table
```sql
CREATE TABLE `reward_claims` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `challenge_id` bigint(20) unsigned NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'USDT',
  `status` enum('pending','approved','paid','rejected') DEFAULT 'pending',
  `reviewed_by` bigint(20) unsigned NULL,
  `reviewed_at` timestamp NULL,
  `payment_transaction_id` bigint(20) unsigned NULL,
  `claim_reason` text NULL,
  `admin_notes` text NULL,
  `metadata` json NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reward_claims_uuid_unique` (`uuid`),
  UNIQUE KEY `unique_user_challenge_reward` (`user_id`,`challenge_id`),
  KEY `idx_claims_status` (`status`),
  KEY `idx_claims_challenge` (`challenge_id`),
  CONSTRAINT `fk_claims_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_claims_challenge` FOREIGN KEY (`challenge_id`) REFERENCES `challenges` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_claims_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_claims_transaction` FOREIGN KEY (`payment_transaction_id`) REFERENCES `payment_transactions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 🏆 Gamification & Reputation System

### 10. Reputation Points Table
```sql
CREATE TABLE `reputation_points` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `action_type` varchar(50) NOT NULL,
  `points` int(11) NOT NULL,
  `source_type` varchar(255) NULL,
  `source_id` bigint(20) unsigned NULL,
  `description` varchar(255) NULL,
  `multiplier` decimal(3,2) DEFAULT 1.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_reputation_user` (`user_id`),
  KEY `idx_reputation_action` (`action_type`),
  KEY `idx_reputation_source` (`source_type`,`source_id`),
  CONSTRAINT `fk_reputation_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 11. Achievement Badges Table
```sql
CREATE TABLE `achievement_badges` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) UNIQUE NOT NULL,
  `description` text NOT NULL,
  `icon` varchar(255) NOT NULL,
  `color` varchar(7) DEFAULT '#FFD700',
  `category` varchar(50) NOT NULL,
  `points_required` int(11) DEFAULT 0,
  `challenges_required` int(11) DEFAULT 0,
  `special_conditions` json NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `badges_slug_unique` (`slug`),
  KEY `idx_badges_category` (`category`),
  KEY `idx_badges_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 12. User Achievement Badges Table
```sql
CREATE TABLE `user_achievement_badges` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `badge_id` bigint(20) unsigned NOT NULL,
  `earned_at` timestamp NULL DEFAULT NULL,
  `progress_data` json NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_badge` (`user_id`,`badge_id`),
  KEY `idx_user_badges_earned` (`earned_at`),
  CONSTRAINT `fk_user_badges_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_user_badges_badge` FOREIGN KEY (`badge_id`) REFERENCES `achievement_badges` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 📄 Content Management

### 13. Pages Table
```sql
CREATE TABLE `pages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) UNIQUE NOT NULL,
  `content` longtext NOT NULL,
  `meta_description` varchar(255) NULL,
  `meta_keywords` varchar(500) NULL,
  `is_published` tinyint(1) DEFAULT 0,
  `publish_at` timestamp NULL,
  `template` varchar(100) DEFAULT 'default',
  `language` varchar(5) DEFAULT 'en',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pages_slug_unique` (`slug`),
  KEY `idx_pages_published` (`is_published`),
  KEY `idx_pages_language` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 14. Settings Table
```sql
CREATE TABLE `settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `group` varchar(50) NOT NULL,
  `key` varchar(100) NOT NULL,
  `value` longtext NULL,
  `type` enum('string','integer','boolean','json','array') DEFAULT 'string',
  `is_encrypted` tinyint(1) DEFAULT 0,
  `description` text NULL,
  `validation_rules` varchar(500) NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_group_key` (`group`,`key`),
  KEY `idx_settings_group` (`group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## Database Relationships Summary

### Primary Relationships:
- `users` → `challenges` (one-to-many: submitted_by)
- `users` → `challenge_responses` (one-to-many)
- `users` → `user_subscriptions` (one-to-one)
- `users` → `wallet_addresses` (one-to-many)
- `users` → `payment_transactions` (one-to-many)
- `categories` → `challenges` (one-to-many)
- `challenges` → `challenge_responses` (one-to-many)
- `challenges` → `reward_claims` (one-to-many)
- `users` → `reputation_points` (one-to-many)
- `users` ↔ `achievement_badges` (many-to-many via user_achievement_badges)

### Polymorphic Relationships:
- `challenge_votes` → `challenges` OR `challenge_responses` (polymorphic)

---

## Indexes & Performance Optimization

### Critical Indexes:
1. **User lookups**: `uuid`, `username`, `email`, `status`
2. **Challenge performance**: `status`, `category_id`, `created_at`
3. **Payment tracking**: `status`, `transaction_type`, `external_id`
4. **Search functionality**: FULLTEXT on `challenges.title,content` and `challenge_responses.content`
5. **Reputation queries**: `user_id`, `action_type`
6. **Voting performance**: Composite unique key on votes

### Query Optimization:
- Use composite indexes for common query patterns
- FULLTEXT indexes for search functionality
- Proper foreign key constraints for referential integrity
- Strategic use of nullable fields to avoid unnecessary joins

---

*This schema supports the full Mim platform functionality as defined in the installation guide and directory structure, optimized for Laravel best practices and scalable SaaS operations.*
