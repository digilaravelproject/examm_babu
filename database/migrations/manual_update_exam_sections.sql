-- Add micro_category_id column
ALTER TABLE `exam_sections` ADD COLUMN `micro_category_id` BIGINT UNSIGNED NULL AFTER `section_id`;

-- Make section_id nullable
ALTER TABLE `exam_sections` MODIFY COLUMN `section_id` BIGINT UNSIGNED NULL;

-- Optional: Add foreign key constraint if needed (recommended)
-- ALTER TABLE `exam_sections` ADD CONSTRAINT `exam_sections_micro_category_id_foreign` FOREIGN KEY (`micro_category_id`) REFERENCES `micro_categories` (`id`) ON DELETE SET NULL;
