INSERT INTO `modules` (`id`, `name`, `actions`, `created_at`, `updated_at`) VALUES 
(NULL, 'referrals', '{\"index\":\"backend.referral.index\",\"create\":\"backend.referral.create\",\"edit\":\"backend.referral.edit\",\"destroy\":\"backend.referral.destroy\"}', '2025-12-03 08:42:53', '2025-12-03 08:42:53'),
(NULL, 'chats', '{\"index\":\"backend.chat.index\",\"send\":\"backend.chat.send\",\"reply\":\"backend.chat.replay\",\"destroy\":\"backend.chat.destroy\"}', '2025-12-03 08:42:53', '2025-12-03 08:42:53'),
(NULL, 'wallet_bonuses', '{\"index\":\"backend.wallet_bonus.index\",\"create\":\"backend.wallet_bonus.create\",\"edit\":\"backend.wallet_bonus.edit\",\"destroy\":\"backend.wallet_bonus.destroy\"}', '2025-12-03 08:42:53', '2025-12-03 08:42:53'),
(NULL, 'custom_ai_model', '{\"index\":\"backend.custom_ai_model.index\",\"create\":\"backend.custom_ai_model.create\",\"edit\":\"backend.custom_ai_model.edit\",\"destroy\":\"backend.custom_ai_model.destroy\"}', '2025-12-31 12:18:47', '2025-12-31 12:18:47'),
(NULL, 'zone_managers', '{\"index\":\"backend.zone_manager.index\",\"create\":\"backend.zone_manager.create\",\"edit\":\"backend.zone_manager.edit\",\"destroy\":\"backend.zone_manager.destroy\"}', '2025-12-31 12:18:47', '2025-12-31 12:18:47');
COMMIT;

INSERT INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`) VALUES
('backend.referral.index', 'web', NOW(), NOW()),
('backend.referral.create', 'web', NOW(), NOW()),
('backend.referral.edit', 'web', NOW(), NOW()),
('backend.referral.destroy', 'web', NOW(), NOW()),
('backend.chat.index', 'web', NOW(), NOW()),
('backend.chat.send', 'web', NOW(), NOW()),
('backend.chat.replay', 'web', NOW(), NOW()),
('backend.chat.destroy', 'web', NOW(), NOW()),
('backend.wallet_bonus.index', 'web', NOW(), NOW()),
('backend.wallet_bonus.create', 'web', NOW(), NOW()),
('backend.wallet_bonus.edit', 'web', NOW(), NOW()),
('backend.wallet_bonus.destroy', 'web', NOW(), NOW()),
('backend.custom_ai_model.index', 'web', NOW(), NOW()),
('backend.custom_ai_model.create', 'web', NOW(), NOW()),
('backend.custom_ai_model.edit', 'web', NOW(), NOW()),
('backend.custom_ai_model.destroy', 'web', NOW(), NOW()),
('backend.zone_manager.index', 'web', NOW(), NOW()),
('backend.zone_manager.create', 'web', NOW(), NOW()),
('backend.zone_manager.edit', 'web', NOW(), NOW()),
('backend.zone_manager.destroy', 'web', NOW(), NOW());

-- Admin gets all referral permissions
INSERT INTO role_has_permissions (permission_id, role_id)
SELECT id, 1
FROM permissions
WHERE name IN (
    'backend.referral.index',
    'backend.referral.create',
    'backend.referral.edit',
    'backend.referral.destroy'
);

-- Consumer gets only referral index
INSERT INTO role_has_permissions (permission_id, role_id)
SELECT id, 2
FROM permissions
WHERE name = 'backend.referral.index';

-- Provider gets only referral index
INSERT INTO role_has_permissions (permission_id, role_id)
SELECT id, 3
FROM permissions
WHERE name = 'backend.referral.index';

-- Admin gets all chat permissions
INSERT INTO role_has_permissions (permission_id, role_id)
SELECT id, 1
FROM permissions
WHERE name IN (
    'backend.chat.index',
    'backend.chat.send',
    'backend.chat.replay',
    'backend.chat.destroy'
);

-- Consumer gets all chat permissions
INSERT INTO role_has_permissions (permission_id, role_id)
SELECT id, 2
FROM permissions
WHERE name IN (
    'backend.chat.index',
    'backend.chat.send',
    'backend.chat.replay',
    'backend.chat.destroy'
);

-- Provider gets all chat permissions
INSERT INTO role_has_permissions (permission_id, role_id)
SELECT id, 3
FROM permissions
WHERE name IN (
    'backend.chat.index',
    'backend.chat.send',
    'backend.chat.replay',
    'backend.chat.destroy'
);

-- Serviceman gets all chat permissions
INSERT INTO role_has_permissions (permission_id, role_id)
SELECT id, 4
FROM permissions
WHERE name IN (
    'backend.chat.index',
    'backend.chat.send',
    'backend.chat.replay',
    'backend.chat.destroy'
);

-- Admin gets all wallet bonuses permissions
INSERT INTO role_has_permissions (permission_id, role_id)
SELECT id, 1
FROM permissions
WHERE name IN (
    'backend.wallet_bonus.index',
    'backend.wallet_bonus.create',
    'backend.wallet_bonus.edit',
    'backend.wallet_bonus.destroy'
);

-- Consumer gets only wallet bonuses index
INSERT INTO role_has_permissions (permission_id, role_id)
SELECT id, 2
FROM permissions
WHERE name = 'backend.wallet_bonus.index';

-- Admin gets all custom_ai_model permissions
INSERT INTO role_has_permissions (permission_id, role_id)
SELECT id, 1
FROM permissions
WHERE name IN (
    'backend.custom_ai_model.index',
    'backend.custom_ai_model.create',
    'backend.custom_ai_model.edit',
    'backend.custom_ai_model.destroy'
);

-- Admin gets all zone_manager permissions
INSERT INTO role_has_permissions (permission_id, role_id)
SELECT id, 1
FROM permissions
WHERE name IN (
    'backend.zone_manager.index',
    'backend.zone_manager.create',
    'backend.zone_manager.edit',
    'backend.zone_manager.destroy'
);

--
-- Table structure for table `referral_bonuses`
--

CREATE TABLE `referral_bonuses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `referrer_id` bigint(20) UNSIGNED NOT NULL,
  `referred_id` bigint(20) UNSIGNED NOT NULL,
  `bonus_amount` decimal(8,2) NOT NULL DEFAULT 0.00,
  `referrer_type` enum('user','provider') NOT NULL DEFAULT 'user',
  `referred_type` enum('user','provider') NOT NULL DEFAULT 'user',
  `status` varchar(191) NOT NULL DEFAULT 'pending',
  `booking_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `referrer_percentage` decimal(5,2) NOT NULL DEFAULT 0.00,
  `referred_percentage` decimal(5,2) NOT NULL DEFAULT 0.00,
  `referred_bonus_amount` decimal(8,2) NOT NULL DEFAULT 0.00,
  `referrer_bonus_amount` decimal(8,2) NOT NULL DEFAULT 0.00,
  `currency_symbol` varchar(191) DEFAULT NULL,
  `credited_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `referral_bonuses`
--
ALTER TABLE `referral_bonuses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `referral_bonuses_referrer_id_index` (`referrer_id`),
  ADD KEY `referral_bonuses_referred_id_index` (`referred_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `referral_bonuses`
--
ALTER TABLE `referral_bonuses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `referral_bonuses`
--
ALTER TABLE `referral_bonuses`
  ADD CONSTRAINT `referral_bonuses_referred_id_foreign` FOREIGN KEY (`referred_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `referral_bonuses_referrer_id_foreign` FOREIGN KEY (`referrer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

--
-- Table structure for table `wallet_bonuses`
--

CREATE TABLE `wallet_bonuses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `type` enum('fixed','percentage') DEFAULT 'fixed',
  `bonus` decimal(12,2) DEFAULT 0.00,
  `min_top_up_amount` decimal(12,2) DEFAULT 0.00,
  `max_bonus` decimal(12,2) DEFAULT 0.00,
  `status` int(11) DEFAULT 1,
  `is_admin_funded` int(11) DEFAULT 0,
  `created_by_id` bigint(20) UNSIGNED DEFAULT NULL,
  `usage_limit_per_user` int DEFAULT NULL,
  `total_usage_limit` int DEFAULT NULL,
  `is_unlimited` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `wallet_bonuses`
--
ALTER TABLE `wallet_bonuses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `wallet_bonuses_created_by_id_foreign` (`created_by_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `wallet_bonuses`
--
ALTER TABLE `wallet_bonuses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `wallet_bonuses`
--
ALTER TABLE `wallet_bonuses`
  ADD CONSTRAINT `wallet_bonuses_created_by_id_foreign` FOREIGN KEY (`created_by_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

ALTER TABLE `transactions`
ADD COLUMN `wallet_bonus_id` BIGINT UNSIGNED NULL AFTER `id`,
ADD COLUMN `wallet_bonus_amount` DECIMAL(8,2) NULL AFTER `wallet_bonus_id`,
ADD COLUMN `is_admin_funded` TINYINT(1) NOT NULL DEFAULT 0 AFTER `wallet_bonus_amount`,
ADD COLUMN `max_bonus` DECIMAL(8,2) NOT NULL DEFAULT 0 AFTER `is_admin_funded`;

ALTER TABLE `transactions`
ADD CONSTRAINT `transactions_wallet_bonus_id_foreign`
FOREIGN KEY (`wallet_bonus_id`)
REFERENCES `wallet_bonuses` (`id`)
ON DELETE CASCADE;

INSERT INTO `modules` (`id`, `name`, `actions`, `created_at`, `updated_at`) VALUES 
(NULL, 'seo_settings', '{\"index\":\"backend.seo_setting.index\",\"create\":\"backend.seo_setting.create\",\"edit\":\"backend.seo_setting.edit\",\"destroy\":\"backend.seo_setting.destroy\"}', '2025-12-21 11:50:27', '2025-12-21 11:50:27');
COMMIT;

INSERT INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`) VALUES
('backend.seo_setting.index', 'web', NOW(), NOW()),
('backend.seo_setting.create', 'web', NOW(), NOW()),
('backend.seo_setting.edit', 'web', NOW(), NOW()),
('backend.seo_setting.destroy', 'web', NOW(), NOW());

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT id, 1
FROM permissions
WHERE name IN (
    'backend.seo_setting.index',
    'backend.seo_setting.create',
    'backend.seo_setting.edit',
    'backend.seo_setting.destroy'
);


--
-- Table structure for table `seo_settings`
--

CREATE TABLE `seo_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `page_name` varchar(191) NOT NULL,
  `page_slug` varchar(191) NOT NULL,
  `meta_title` varchar(191) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL,
  `og_title` varchar(191) DEFAULT NULL,
  `og_description` text DEFAULT NULL,
  `twitter_title` varchar(191) DEFAULT NULL,
  `twitter_description` text DEFAULT NULL,
  `canonical_url` varchar(191) DEFAULT NULL,
  `robots` varchar(191) NOT NULL DEFAULT 'index,follow',
  `schema_markup` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`schema_markup`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `seo_settings`
--
ALTER TABLE `seo_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `seo_settings_page_slug_unique` (`page_slug`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `seo_settings`
--
ALTER TABLE `seo_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

INSERT INTO `seo_settings` (`id`, `page_name`, `page_slug`, `meta_title`, `meta_description`, `meta_keywords`, `og_title`, `og_description`, `twitter_title`, `twitter_description`, `canonical_url`, `robots`, `schema_markup`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Home Page', 'home-page', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'index,follow', NULL, 1, '2025-12-24 08:00:03', '2025-12-24 08:00:03'),
(2, 'Service List Page', 'service-list', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'index,follow', NULL, 1, '2025-12-24 08:00:03', '2025-12-24 08:00:03'),
(3, 'Service Detail Page', 'service-detail', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'index,follow', NULL, 1, '2025-12-24 08:00:03', '2025-12-24 08:00:03'),
(4, 'Category List Page', 'category-list', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'index,follow', NULL, 1, '2025-12-24 08:00:03', '2025-12-24 08:00:03'),
(5, 'Blog List Page', 'blog-list', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'index,follow', NULL, 1, '2025-12-24 08:00:03', '2025-12-24 08:00:03'),
(6, 'Blog Detail Page', 'blog-detail', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'index,follow', NULL, 1, '2025-12-24 08:00:03', '2025-12-24 08:00:03'),
(7, 'Provider List Page', 'provider-list', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'index,follow', NULL, 1, '2025-12-24 08:00:03', '2025-12-24 08:00:03'),
(8, 'Provider Detail Page', 'provider-detail', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'index,follow', NULL, 1, '2025-12-24 08:00:03', '2025-12-24 08:00:03'),
(9, 'Service Package List Page', 'service-package-list', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'index,follow', NULL, 1, '2025-12-24 08:00:03', '2025-12-24 08:00:03'),
(10, 'Service Package Detail Page', 'service-package-detail', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'index,follow', NULL, 1, '2025-12-24 08:00:03', '2025-12-24 08:00:03'),
(11, 'Privacy Policy Page', 'privacy-policy', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'index,follow', NULL, 1, '2025-12-24 08:00:03', '2025-12-24 08:00:03'),
(12, 'Terms & Conditions Page', 'terms-conditions', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'index,follow', NULL, 1, '2025-12-24 08:00:03', '2025-12-24 08:00:03'),
(13, 'Contact Us Page', 'contact-us', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'index,follow', NULL, 1, '2025-12-24 08:00:03', '2025-12-24 08:00:03'),
(14, 'About Us Page', 'about-us', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'index,follow', NULL, 1, '2025-12-24 08:00:03', '2025-12-24 08:00:03'),
(15, 'Provider Sign Up', 'become-provider', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'index,follow', NULL, 1, '2025-12-24 08:00:03', '2025-12-24 08:00:03');
COMMIT;


--
-- Table structure for table `custom_ai_models`
--

CREATE TABLE `custom_ai_models` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `provider` varchar(191) NOT NULL,
  `model_name` varchar(191) DEFAULT NULL,
  `api_key` varchar(191) DEFAULT NULL,
  `base_url` varchar(191) DEFAULT NULL,
  `headers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`headers`)),
  `params` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`params`)),
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `custom_ai_models`
--
ALTER TABLE `custom_ai_models`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `custom_ai_models`
--
ALTER TABLE `custom_ai_models`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Update for migration: 2025_11_21_122814_add_referral_columns_to_users_table
-- Add referral columns to users table
--
ALTER TABLE `users`
  ADD COLUMN `referral_code` VARCHAR(255) NULL AFTER `updated_at`,
  ADD COLUMN `referred_by_id` BIGINT(20) UNSIGNED NULL AFTER `referral_code`;

-- Add unique index on referral_code
ALTER TABLE `users`
  ADD UNIQUE KEY `users_referral_code_unique` (`referral_code`);

-- Add foreign key constraint on referred_by_id
ALTER TABLE `users`
  ADD CONSTRAINT `users_referred_by_id_foreign` FOREIGN KEY (`referred_by_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;


  -- --------------------------------------------------------

--
-- Table structure for table `user_zone_permissions`
--

CREATE TABLE `user_zone_permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `zone_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `user_zone_permissions`
--
ALTER TABLE `user_zone_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_zone_permissions_user_id_zone_id_unique` (`user_id`,`zone_id`),
  ADD KEY `user_zone_permissions_zone_id_foreign` (`zone_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `user_zone_permissions`
--
ALTER TABLE `user_zone_permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `user_zone_permissions`
--
ALTER TABLE `user_zone_permissions`
  ADD CONSTRAINT `user_zone_permissions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_zone_permissions_zone_id_foreign` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`id`) ON DELETE CASCADE;
COMMIT;

ALTER TABLE `services`
    ADD COLUMN `is_advance_payment_enabled` TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN `advance_payment_percentage` DECIMAL(5,2) NULL AFTER `is_advance_payment_enabled`;

ALTER TABLE `bookings`
    ADD COLUMN `advance_payment_amount` DECIMAL(10,2) NULL AFTER `total`,
    ADD COLUMN `remaining_payment_amount` DECIMAL(10,2) NULL AFTER `advance_payment_amount`,
    ADD COLUMN `advance_payment_status` ENUM('PENDING','PAID','REFUNDED') NOT NULL DEFAULT 'PENDING' AFTER `remaining_payment_amount`,
    ADD COLUMN `remaining_payment_status` ENUM('PENDING','PAID','REFUNDED') NOT NULL DEFAULT 'PENDING' AFTER `advance_payment_status`,
    ADD COLUMN `is_advance_payment_enabled` TINYINT(1) NOT NULL DEFAULT 0 AFTER `remaining_payment_status`,
    ADD COLUMN `advance_payment_percentage` DECIMAL(5,2) NULL AFTER `is_advance_payment_enabled`,
    ADD COLUMN `transaction_ids` JSON NULL AFTER `advance_payment_percentage`;

ALTER TABLE `users`
    ADD COLUMN `allow_all_zones` TINYINT(1) NOT NULL DEFAULT 0 AFTER `status`;

--
-- Table structure for table `booking_payment_transactions`
--

CREATE TABLE `booking_payment_transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `booking_id` bigint(20) UNSIGNED NOT NULL,
  `payment_transaction_id` bigint(20) UNSIGNED NOT NULL,
  `payment_type` varchar(191) NOT NULL DEFAULT 'full' COMMENT 'advance, remaining, full',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `booking_payment_transactions`
--
ALTER TABLE `booking_payment_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_payment_transactions_payment_transaction_id_foreign` (`payment_transaction_id`),
  ADD KEY `idx_booking_payment_trans` (`booking_id`,`payment_transaction_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `booking_payment_transactions`
--
ALTER TABLE `booking_payment_transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `booking_payment_transactions`
--
ALTER TABLE `booking_payment_transactions`
  ADD CONSTRAINT `booking_payment_transactions_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `booking_payment_transactions_payment_transaction_id_foreign` FOREIGN KEY (`payment_transaction_id`) REFERENCES `payment_gateways_transactions` (`id`) ON DELETE CASCADE;
COMMIT;

ALTER TABLE `services` CHANGE `type` `type` SET('fixed','provider_site','remotely','scheduled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'fixed';

ALTER TABLE `bookings`
  ADD `is_scheduled_booking` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_advance_payment_enabled`,
  ADD `booking_frequency` VARCHAR(191) NULL DEFAULT NULL COMMENT 'daily, weekly, monthly, yearly, custom' AFTER `is_scheduled_booking`,
  ADD `schedule_start_date` DATE NULL DEFAULT NULL AFTER `booking_frequency`,
  ADD `schedule_end_date` DATE NULL DEFAULT NULL AFTER `schedule_start_date`,
  ADD `schedule_time` TIME NULL DEFAULT NULL AFTER `schedule_end_date`,
  ADD `selected_weekdays` JSON NULL DEFAULT NULL COMMENT 'Array of selected weekdays for daily frequency' AFTER `schedule_time`,
  ADD `scheduled_dates_json` JSON NULL DEFAULT NULL COMMENT 'JSON array of all scheduled dates and times' AFTER `selected_weekdays`,
  ADD `scheduled_services_count` INT NULL DEFAULT NULL COMMENT 'Total number of scheduled service instances' AFTER `scheduled_dates_json`;




