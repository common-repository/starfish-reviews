<?php

// Get the latest post from starfish.reviews.
$latest_post = null;
$response = wp_remote_get('https://starfish.reviews/wp-json/wp/v2/posts?per_page=1');
if (!is_wp_error($response)) {
	$posts = json_decode(wp_remote_retrieve_body($response));
	if (!empty($posts)) {
		$latest_post = $posts[0];
	}
}
?>

<div id="srm-settings-communitybar">
    <div id="srm-follow-tile" class="srm-community-bar-tile">
        <a class="srm-community-bar-link" href="https://bit.ly/3y9R82H" target="_blank" title="Follow Us">
            <span style="color: Dodgerblue;"><i class="fa-brands fa-facebook-square" aria-hidden="true"></i></span>
            <h2 class="srm-community-bar-title">Follow Us</h2>
            <div class="srm-community-bar-text">Articles, Updates, Help, & more.</div>
        </a>
    </div>
    <div id="srm-roadmap-tile" class="srm-community-bar-tile">
        <a class="srm-community-bar-link" href="https://bit.ly/3FaQlzP" target="_blank" title="Feature Roadmap">
            <span style="color: green;"><i class="fa-solid fa-map" aria-hidden="true"></i></span>
            <h2 class="srm-community-bar-title">Feature Roadmap</h2>
            <div class="srm-community-bar-text">Current, Next, & Future features coming to Starfish.</div>
        </a>
    </div>
    <div id="srm-ideas-tile" class="srm-community-bar-tile">
        <a class="srm-community-bar-link" href="https://bit.ly/3LxHSZX" target="_blank" title="Have an Idea?">
            <span style="color: #FFD249;"><i class="fa-regular fa-lightbulb" aria-hidden="true"></i></span>
            <h2 class="srm-community-bar-title">Have an Idea?</h2>
            <div class="srm-community-bar-text">Submit your ideas & vote on others.</div>
        </a>
    </div>
    <div id="srm-feedback-tile" class="srm-community-bar-tile">
        <a class="srm-community-bar-link" href="https://bit.ly/3LGt1ML" target="_blank" title="Feedback">
            <span style="color: darkred;"><i class="fa-solid fa-message" aria-hidden="true"></i></span>
            <h2 class="srm-community-bar-title">Feedback</h2>
            <div class="srm-community-bar-text">Let us know how we are doing & could improve.</div>
        </a>
    </div>
	<?php if (!empty($latest_post)) { ?>
        <div id="srm-post-tile" class="srm-community-bar-tile">
            <a class="srm-community-bar-link" href="<?php echo esc_url($latest_post->link); ?>" target="_blank"
               title="<?php echo $latest_post->title->rendered; ?>">
                <span style="color: saddlebrown;"><i class="fa-regular fa-newspaper" aria-hidden="true"></i></span>
                <h2 class="srm-community-bar-title"><?php echo $latest_post->title->rendered; ?></h2>
                <div class="srm-community-bar-text"></div>
            </a>
        </div>
	<?php } ?>
    <div id="srm-group-tile" class="srm-community-bar-tile">
        <a class="srm-community-bar-link" href="https://bit.ly/3MNd2ww" target="_blank" title="Join The Community">
            <span style="color: #686868;"><i class="fa-solid fa-user-group" aria-hidden="true"></i></span>
            <h2 class="srm-community-bar-title">Join The Community</h2>
            <div class="srm-community-bar-text">Review Marketing Facebook Group</div>
        </a>
    </div>
</div>
