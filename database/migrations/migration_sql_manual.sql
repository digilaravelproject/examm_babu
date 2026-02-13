-- =====================================================
-- SQL Migration: Change Skill Mapping from Section to MicroCategory
-- File: 2026_02_12_100000_change_skill_mapping_from_section_to_micro_category
-- =====================================================

-- STEP 1: Add micro_category_id column to skills table
ALTER TABLE `skills`
ADD COLUMN `micro_category_id` BIGINT UNSIGNED NULL AFTER `section_id`,
ADD CONSTRAINT `skills_micro_category_id_foreign`
    FOREIGN KEY (`micro_category_id`)
    REFERENCES `micro_categories` (`id`)
    ON DELETE CASCADE;

-- STEP 2: Migrate data - Assign first active MicroCategory to all skills
-- (You may want to customize this based on your data structure)
UPDATE `skills`
SET `micro_category_id` = (
    SELECT `id` FROM `micro_categories`
    WHERE `is_active` = 1
    LIMIT 1
)
WHERE `micro_category_id` IS NULL;

-- STEP 3: Make micro_category_id NOT NULL
ALTER TABLE `skills`
MODIFY COLUMN `micro_category_id` BIGINT UNSIGNED NOT NULL;

-- STEP 4: Drop section_id foreign key and column
ALTER TABLE `skills`
DROP FOREIGN KEY `skills_section_id_foreign`;

ALTER TABLE `skills`
DROP COLUMN `section_id`;

-- =====================================================
-- ROLLBACK (if needed):
-- =====================================================
-- ALTER TABLE `skills`
-- ADD COLUMN `section_id` BIGINT UNSIGNED NULL AFTER `id`,
-- ADD CONSTRAINT `skills_section_id_foreign`
--     FOREIGN KEY (`section_id`)
--     REFERENCES `sections` (`id`)
--     ON DELETE CASCADE;

-- ALTER TABLE `skills`
-- DROP FOREIGN KEY `skills_micro_category_id_foreign`;

-- ALTER TABLE `skills`
-- DROP COLUMN `micro_category_id`;
