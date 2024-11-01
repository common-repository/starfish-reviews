<?php

use Starfish\Premium\Reviews as SRM_REVIEWS;
use Starfish\Premium\ReviewsProfiles as SRM_REVIEWS_PROFILES;
use Starfish\Premium\UtilsReviews as SRM_UTILS_REVIEWS;

if ( current_user_can( 'manage_options' ) ) {
	$review_data = null;
	if ( isset( $_GET['action'] ) && sanitize_text_field( $_GET['action'] ) === 'details' ) {
		if ( isset( $_GET['srm_review'] ) && ! empty( $_GET['srm_review'] ) ) {
			$review_id    = sanitize_text_field( $_GET['srm_review'] );
			$review_data  = SRM_REVIEWS::get_review( $review_id );
			$profile      = SRM_REVIEWS_PROFILES::get_profile( $review_data->profile_id );
			$profile_host = wp_parse_url( $profile->url, PHP_URL_HOST );
			foreach ( SRM_UTILS_REVIEWS::get_review_sites() as $site ) {
				$site_host = wp_parse_url( $site->url, PHP_URL_HOST );
				if ( $site_host === $profile_host ) {
					$reviews_site_logo = $site->logo;
					$site_name         = $site->name;
					break;
				}
			}
		}
	}
	?>
	<div id="srm-review-details-dialogue" class="srm-dialogue srm-help-dialogue" style="display:none">
		<?php if ( ! empty( $review_data ) ) {
            // Set default Profile Image
		    $profile_picture = SRM_PLUGIN_URL . '/assets/default_profile_image.png';
            $avatar          = 'srm_review_avatar_' . $review_data->uuid . '.png';
            $avatar_path     = SRM_UPLOADS_DIR . $avatar;
            $avatar_url      = SRM_UPLOADS_URL . $avatar;
            if(file_exists($avatar_path)) {
                $profile_picture = $avatar_url;
            }
            // Set default Location
            $review_location = 'Not Available';
            if( ! empty( $review_data->location ) ) {
                $review_location = $review_data->location;
            }
		    ?>
			<div class="srm-review-details-reviewer">
				<div class="srm-review-profile-image" title="<?php print $review_data->name; ?>">
					<img src="<?php print $profile_picture; ?>" alt="Reviewer Profile Image"
					     width="75"/>
				</div>
				<div class="srm-review-rating" title="Rating of <?php print $review_data->rating_value; ?>">
					<?php
					for ( $s = 1; $s <= $review_data->rating_value; $s ++ ) {
						print '<span class="fa fa-star srm-reviews-star"></span>';
					}
					?>
				</div>
				<div class="srm-review-reviewer">
					<?php print $review_data->name; ?>
				</div>
			</div>
			<div class="srm-review-details-summary">
				<table class="table table-sm table-hover" aria-describedby="Review Details Summary">
					<tbody>
					<tr>
						<th scope="row">Date</th>
						<td><?php print gmdate( 'm/d/Y', strtotime( $review_data->date ) ); ?></td>
					</tr>
					<tr>
						<th scope="row">Location</th>
						<td colspan="3"><?php print $review_location; ?></td>
					</tr>
					<tr>
						<th scope="row">Review URL</th>
						<td><a target="_blank" href="<?php print $review_data->url; ?>"
						       title="Click to view original review" rel="noopener">View Original</a> <span
								class="fas fa-external-link-alt"></span></td>
					</tr>
					<tr>
						<th scope="row">Profile</th>
						<td>
							<a href="/wp-admin/edit.php?post_type=starfish_feedback&page=starfish-reviews-profiles&action=details&id=
							<?php
							print $review_data->profile_id;
							?>
							&site=<?php print $site_name; ?>" title="Click to view original review">View Profile</a>
						</td>
					</tr>
					</tbody>
				</table>
			</div>
			<div class="srm-review-details-content">
				<div class="srm-review-details-title"><?php print $review_data->review_title; ?></div>
				<div class="srm-review-details-text"><?php print $review_data->review_text; ?></div>
			</div>
			<div class="srm-review-details-meta">
				<small>
					uuid: <?php print $review_data->uuid; ?> | ID: <?php print $review_data->id; ?>
				</small>
			</div>
		<?php } else { ?>
			<div id="srm-reviews-profile-summary">Something went wrong, no data could be found for the following Review
				ID (<?php print $review_id; ?>)
			</div>
		<?php } ?>
	</div>
	<?php
}
