SET
FOREIGN_KEY_CHECKS=0;
#new_query
DROP TABLE IF EXISTS `%1$ssrm_reviews_profiles_new`
    #new_query
ALTER TABLE `%1$ssrm_reviews_profiles` MODIFY `url` VARCHAR (200);
#new_query
SET FOREIGN_KEY_CHECKS=1;