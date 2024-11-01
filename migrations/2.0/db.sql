CREATE TABLE IF NOT EXISTS `%1$ssrm_reviews_profiles` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(45) NOT NULL,
    `url` VARCHAR(100) NULL,
    `place_id` VARCHAR(45) NULL,
    `query` VARCHAR(100) NULL,
    `job_id` VARCHAR(45) NOT NULL,
    `language_code` VARCHAR(4) NULL DEFAULT 'en',
    `allow_responses` BINARY(1) NOT NULL DEFAULT 0,
    `hide` BINARY(1) NOT NULL DEFAULT 0,
    `active` BINARY(1) NOT NULL DEFAULT 1,
    `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `srm_rp_url` (`url` ASC),
    INDEX `srm_rp_id` (`id` ASC),
    UNIQUE INDEX `srm_rp_name_url` (`name` ASC, `url` ASC))
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
#new_query
CREATE TABLE IF NOT EXISTS `%1$ssrm_reviews` (
     `id` INT NOT NULL AUTO_INCREMENT,
     `name` VARCHAR(100) NOT NULL,
     `date` DATETIME NOT NULL,
     `rating_value` INT NOT NULL,
     `review_text` LONGBLOB NULL,
     `url` LONGTEXT NOT NULL,
     `location` VARCHAR(100) NULL,
     `review_title` VARCHAR(100) NULL,
     `verified_order` BINARY(1) NULL,
     `language_code` VARCHAR(4) NULL,
     `reviewer_title` VARCHAR(45) NULL,
     `uuid` MEDIUMTEXT NULL,
     `meta_data` BLOB(250) NULL,
     `profile_id` INT NULL,
     `hide` BINARY(1) NOT NULL DEFAULT 0,
     PRIMARY KEY (`id`),
     INDEX `srm_r_date` (`date` ASC),
     INDEX `srm_r_rating` (`rating_value` ASC),
     INDEX `srm_r_date_rating` (`date` ASC, `rating_value` ASC),
     INDEX `srm_r_profile_id_idx` (`id` ASC, `profile_id` ASC))
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
#new_query
CREATE TABLE IF NOT EXISTS `%1$ssrm_reviews_last_summary` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `profile_id` INT NULL,
    `review_count` VARCHAR(45) NULL,
    `average_rating` VARCHAR(45) NULL,
    `last_crawl` DATE NULL,
    `crawl_status` VARCHAR(45) NULL,
    `percentage_complete` VARCHAR(45) NULL,
    `result_count` VARCHAR(45) NULL,
    `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated` TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `srm_rls_profile_id_idx` (`profile_id` ASC),
    CONSTRAINT `srm_rls_profile_id`
      FOREIGN KEY `srm_fk_summary_profile` (`profile_id`)
          REFERENCES `%1$ssrm_reviews_profiles` (`id`)
          ON DELETE CASCADE
          ON UPDATE NO ACTION)
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
#new_query
CREATE OR REPLACE VIEW `%1$ssrm_view_reviews_profiles` AS
SELECT
    `%1$ssrm_reviews_profiles`.*,
    `%1$ssrm_reviews_last_summary`.`review_count`,
    `%1$ssrm_reviews_last_summary`.`average_rating`,
    `%1$ssrm_reviews_last_summary`.`last_crawl`,
    `%1$ssrm_reviews_last_summary`.`crawl_status`,
    `%1$ssrm_reviews_last_summary`.`percentage_complete`,
    `%1$ssrm_reviews_last_summary`.`result_count`,
    `%1$ssrm_reviews_last_summary`.`updated` as summary_updated
FROM ((`%1$ssrm_reviews_profiles` LEFT JOIN `%1$ssrm_reviews_last_summary` ON `%1$ssrm_reviews_profiles`.id = `%1$ssrm_reviews_last_summary`.profile_id));