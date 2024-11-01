ALTER TABLE `%1$ssrm_reviews_profiles` DROP INDEX `srm_rp_url`;
#new_query
ALTER TABLE `%1$ssrm_reviews_profiles` DROP INDEX `srm_rp_name_url`;
#new_query
ALTER TABLE `%1$ssrm_reviews_profiles` MODIFY `url` VARCHAR(255) NULL;
#new_query
ALTER TABLE `%1$ssrm_reviews_profiles` ADD INDEX `srm_rp_url` (`url` ASC);
#new_query
ALTER TABLE `%1$ssrm_reviews_profiles` ADD UNIQUE INDEX `srm_rp_name_url` (`name` ASC, `url` ASC);