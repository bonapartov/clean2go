ALTER TABLE booking_additional_services
ADD COLUMN qty INT UNSIGNED NOT NULL DEFAULT 1 AFTER additional_service_id;

ALTER TABLE booking_additional_services 
ADD COLUMN total_price DECIMAL(10,2) AFTER price;

ALTER TABLE `bookings`
ADD COLUMN `video_consultation_id` BIGINT UNSIGNED NULL AFTER `id`;

ALTER TABLE `bookings`
ADD CONSTRAINT `bookings_video_consultation_id_foreign`
FOREIGN KEY (`video_consultation_id`)
REFERENCES `video_consultations` (`id`)
ON DELETE SET NULL;

INSERT INTO `modules` (`name`, `actions`, `created_at`, `updated_at`)
VALUES (
    'serviceman_documents',
    '{
        "index": "backend.serviceman_document.index",
        "create": "backend.serviceman_document.create",
        "edit": "backend.serviceman_document.edit",
        "destroy": "backend.serviceman_document.destroy"
    }',
    NOW(),
    NOW()
);

INSERT INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`) VALUES
('backend.serviceman_document.index', 'web', NOW(), NOW()),
('backend.serviceman_document.create', 'web', NOW(), NOW()),
('backend.serviceman_document.edit', 'web', NOW(), NOW()),
('backend.serviceman_document.destroy', 'web', NOW(), NOW());

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) 
SELECT id, 1 FROM permissions WHERE name LIKE 'backend.serviceman_document.%';

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) 
SELECT id, 4 FROM permissions WHERE name IN (
    'backend.serviceman_document.index',
    'backend.serviceman_document.create',
    'backend.serviceman_document.edit'
);

DELETE FROM role_has_permissions
WHERE role_id = 4
AND permission_id IN (
    SELECT id FROM permissions
    WHERE name LIKE 'backend.withdraw_request.%'
);

ALTER TABLE services 
DROP COLUMN IF EXISTS destination_location;

ALTER TABLE services 
ADD COLUMN address_id BIGINT UNSIGNED NULL AFTER id;

ALTER TABLE services 
ADD CONSTRAINT fk_services_address_id
FOREIGN KEY (address_id) REFERENCES addresses(id)
ON DELETE SET NULL
ON UPDATE CASCADE;

ALTER TABLE `payment_gateways_transactions` DROP INDEX `payment_gateways_transactions_item_id_unique`;