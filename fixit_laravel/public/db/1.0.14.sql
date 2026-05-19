INSERT INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, 3
FROM `permissions` p
WHERE p.name IN (
    'backend.provider_document.index',
    'backend.provider_document.create',
    'backend.provider_document.edit'
)
AND NOT EXISTS (
    SELECT 1
    FROM `role_has_permissions` rhp
    WHERE rhp.permission_id = p.id
      AND rhp.role_id = 3
);

Please backup your existing home_pages table before executing this query.
You can do so with the following SQL command:
CREATE TABLE home_pages_backup AS SELECT * FROM home_pages;

This ensures your current homepage configuration is saved in case you need to restore it.


UPDATE `home_pages`
SET
`content` = '{\"en\":{\"locale\":\"en\",\"home_banner\":{\"title\":\"One-Stop Solution For Your\",\"animate_text\":\"home service\",\"description\":\"We connect you with trusted servicemen for all your home and business needs! ðŸ ðŸ’¼ From repairs to installations, weâ€™ve got you covered. ðŸ”§âœ… Easy booking, clear pricing, and stress-free service! ðŸ˜Š.\",\"search_enable\":\"1\",\"service_ids\":[\"3\",\"4\",\"5\",\"6\",\"7\",\"8\",\"9\",\"10\",\"14\",\"15\",\"19\",\"24\",\"29\",\"36\",\"37\",\"43\",\"45\",\"47\",\"49\",\"52\",\"53\",\"58\",\"59\",\"60\"],\"status\":\"1\"},\"categories_icon_list\":{\"title\":\"Top Categories\",\"category_ids\":[\"7\",\"13\",\"15\",\"19\",\"25\",\"31\",\"37\",\"43\"],\"status\":\"1\"},\"value_banners\":{\"title\":\"Best Valuable Deals\",\"status\":\"1\",\"banners\":[{\"title\":\"Electrical service\",\"description\":\"If you want to have stunning look of your house.\",\"sale_tag\":\"Sale 40%\",\"button_text\":\"Book Now\",\"redirect_type\":\"service-page\",\"button_url\":null,\"image_url\":\"/storage/1047/1.png\"},{\"title\":\"Furniture service\",\"description\":\"If you want to have stunning look of your house.\",\"sale_tag\":\"Sale 50%\",\"button_text\":\"Book Now\",\"redirect_type\":\"category-page\",\"button_url\":null,\"image_url\":\"/storage/1048/2.png\"},{\"title\":\"Ac cleaning service\",\"description\":\"If you want to have stunning look of your house.\",\"sale_tag\":\"Sale 60%\",\"button_text\":\"Book Now\",\"redirect_type\":\"service-page\",\"button_url\":\"https://frozendeadguydays.com/\",\"image_url\":\"/storage/1049/3.png\"}]},\"service_list_1\":{\"title\":\"Featured Services\",\"service_ids\":[\"3\",\"4\",\"5\",\"6\",\"7\",\"9\",\"10\",\"11\"],\"status\":\"1\"},\"download\":{\"status\":\"0\",\"title\":\"FixitCustomer, Provider, Servicemen & Admin application for iOS & Android\",\"description\":\"Buyers can discover local services in a click! through our Google Map integration which enhances top level buyer experiences using their GPS locations\",\"image_url\":\"/storage/1050/app-gif.gif\"},\"providers_list\":{\"title\":\"Expert provider by rating\",\"provider_ids\":[\"3\",\"20\",\"21\",\"22\"],\"status\":\"1\"},\"special_offers_section\":{\"banner_section_title\":\"Limited Time Offers\",\"service_section_title\":\"Special Services Just For You\"},\"service_packages_list\":{\"title\":\"Top Service Packages\",\"service_packages_ids\":[\"1\",\"2\",\"3\",\"4\",\"6\",\"7\",\"8\",\"9\"],\"status\":\"1\"},\"blogs_list\":{\"title\":\"Latest blog\",\"description\":null,\"blog_ids\":[\"1\",\"2\",\"3\",\"4\",\"5\",\"9\"],\"status\":\"1\"},\"custom_job\":{\"status\":\"1\",\"title\":\"Can\'t Find the Right Service? Post a Custom Job Request!\",\"button_text\":\"+ Post New Job Request\",\"image_url\":\"/storage\\\\1848/job-request-img.png\"},\"become_a_provider\":{\"status\":\"1\",\"title\":\"Earn more and deliver your service to worldwide by become a Service Provider\",\"description\":\"Buyers can discover local services in a click! through our Google Map integration which.\",\"button_text\":\"Become a Provider\",\"button_url\":null,\"image_url\":\"/storage/1051/girl.png\",\"float_image_1_url\":\"/storage/1052/chart.png\",\"float_image_2_url\":\"/storage/1053/avatars.png\"},\"testimonial\":{\"title\":\"Testimonials\",\"status\":\"1\"},\"news_letter\":{\"title\":\"SUBSCRIBE TO OUR NEWSLETTER\",\"sub_title\":\"We promise not to spam you.\",\"button_text\":\"Subscribe Now\",\"status\":\"1\",\"bg_image_url\":\"/storage/1046/man.png\"}}}',
`slug` = 'default',
`status` = 1,
`created_at` = '2024-09-07 20:57:19',
`updated_at` = '2025-11-01 07:16:58'
WHERE `id` = 1;

ALTER TABLE `user_subscriptions` ADD COLUMN `product_id` VARCHAR(191) DEFAULT NULL AFTER `user_plan_id`;

ALTER TABLE `user_subscriptions`
ADD COLUMN `payment_method` VARCHAR(191) NULL;

ALTER TABLE `user_subscriptions`
ADD COLUMN `payment_status` VARCHAR(191) NULL DEFAULT 'PENDING';

ALTER TABLE `user_subscriptions`
ADD COLUMN `product_id` VARCHAR(191) NULL;

ALTER TABLE `user_subscriptions`
ADD COLUMN `in_app_status` VARCHAR(191) NULL;

ALTER TABLE `user_subscriptions`
ADD COLUMN `in_app_price` VARCHAR(191) NULL;
