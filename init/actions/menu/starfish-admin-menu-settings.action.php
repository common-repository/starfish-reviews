<?php

use Starfish\Freemius as SRM_FREEMIUS;

add_action('admin_menu', 'settings_submenu');

/**
 * Add Settings Sub-menu page
 */
function settings_submenu()
{
    add_submenu_page(
        'starfish-parent', // Parent Slug
        esc_html__('Starfish Settings', 'starfish'), // Page Title
        esc_html__('Settings', 'starfish'), // Menu Title
        'administrator', // Capability required
        'starfish-settings', // Menu Slug
        'srm_admin_settings_page', // Content Call Back
        5
    );
    if (SRM_FREEMIUS::starfish_fs()->is_not_paying() || SRM_PLAN_TITLE == 'FREE') {
        global $submenu;
        $submenu['starfish_reviews'][] = array( '<span style="color: indianred">&#8627; Go Premium</span>', 'manage_options', SRM_PLANS_URL, 'Upgrade' );
    }
}

/**
 * Add Settings Page (callback)
 */
function srm_admin_settings_page()
{    ?>
	<div class="wrap">
		<h1><?php _e('Starfish Settings', 'starfish'); ?></h1>
		<?php settings_errors(); ?>
		<div
				id="description"><?php esc_html_e('Options for the basic operation of Starfish Reviews', 'starfish'); ?></div>
		<?php
            $active_tab     = isset($_GET['tab']) ? $_GET['tab'] : 'general_options';
    $active_section = isset($_GET['section']) ? $_GET['section'] : '';
    ?>
		<h2 class="nav-tab-wrapper">
			<a href="admin.php?page=starfish-settings&tab=general_options&section=general"
			   class="nav-tab <?php echo $active_tab == 'general_options' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('General', 'starfish'); ?></a>
			<a href="admin.php?page=starfish-settings&tab=funnel_options&section=general"
			   class="nav-tab <?php echo $active_tab == 'funnel_options' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Funnels', 'starfish'); ?></a>
			<a href="admin.php?page=starfish-settings&tab=reviews_options"
			   class="nav-tab <?php echo $active_tab == 'reviews_options' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Reviews', 'starfish'); ?></a>
            <a href="admin.php?page=starfish-settings&tab=testimonial_options&section=general"
               class="nav-tab <?php echo $active_tab == 'testimonial_options' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Testimonials', 'starfish'); ?></a>
			<a href="admin.php?page=starfish-settings&tab=support_options&section=general"
			   class="nav-tab <?php echo $active_tab == 'support_options' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Support', 'starfish'); ?></a>
		</h2>
		<br/>
		<form class="srm-settings-form" method="post" action="options.php">
			<?php
        // This prints out all hidden setting fields
        if ($active_tab === 'general_options') {
            echo '<p><ul class="srm-nav-tab-section-wrapper">
                        <li> <a class="' . (($active_section === 'general') ? 'srm-nav-tab-section-active' : '') . '" href="admin.php?page=starfish-settings&tab=general_options&section=general">' . __('General', 'starfish') . '</a>&nbsp;|&nbsp;</li>
                        <li> <a class="' . (($active_section === 'migrations') ? 'srm-nav-tab-section-active' : '') . '" href="admin.php?page=starfish-settings&tab=general_options&section=migrations">' . __('Migrations', 'starfish') . '</a></li>
                    </ul></p><p>&nbsp;</p><p>&nbsp;</p>';
            if ($active_section === 'general') {
                settings_fields(SRM_OPTION_GROUP_GENERAL);
                do_settings_sections(SRM_OPTION_GROUP_GENERAL);
            } elseif ($active_section === 'migrations') {
                settings_fields(SRM_OPTION_GROUP_GENERAL_MIGRATIONS);
                do_settings_sections(SRM_OPTION_GROUP_GENERAL_MIGRATIONS);
            } else {
                settings_fields(SRM_OPTION_GROUP_GENERAL);
                do_settings_sections(SRM_OPTION_GROUP_GENERAL);
            }
        } elseif ($active_tab === 'funnel_options') {
            echo '<p><ul class="srm-nav-tab-section-wrapper">
                        <li> <a class="' . (($active_section === 'general') ? 'srm-nav-tab-section-active' : '') . '" href="admin.php?page=starfish-settings&tab=funnel_options&section=general">' . __('General', 'starfish') . '</a>&nbsp;|&nbsp;</li>
                        <li> <a class="' . (($active_section === 'email') ? 'srm-nav-tab-section-active' : '') . '" href="admin.php?page=starfish-settings&tab=funnel_options&section=email">' . __('Email', 'starfish') . '</a></li>
                    </ul></p><p>&nbsp;</p><p>&nbsp;</p>';
            if ($active_section === 'general') {
                settings_fields(SRM_OPTION_GROUP_FUNNEL_GENERAL);
                do_settings_sections(SRM_OPTION_GROUP_FUNNEL_GENERAL);
            } elseif ($active_section === 'email') {
                settings_fields(SRM_OPTION_GROUP_FUNNEL_EMAIL);
                do_settings_sections(SRM_OPTION_GROUP_FUNNEL_EMAIL);
            } else {
                settings_fields(SRM_OPTION_GROUP_FUNNEL_GENERAL);
                do_settings_sections(SRM_OPTION_GROUP_FUNNEL_GENERAL);
            }
        } elseif ($active_tab === 'reviews_options') {
            settings_fields(SRM_OPTION_GROUP_REVIEWS);
            do_settings_sections(SRM_OPTION_GROUP_REVIEWS);
        } elseif ($active_tab === 'testimonial_options') {
            echo '<p><ul class="srm-nav-tab-section-wrapper">
                        <li> <a class="' . (($active_section === 'general') ? 'srm-nav-tab-section-active' : '') . '" href="admin.php?page=starfish-settings&tab=testimonial_options&section=general">' . __('General', 'starfish') . '</a>&nbsp;|&nbsp;</li>
                        <li> <a class="' . (($active_section === 'form') ? 'srm-nav-tab-section-active' : '') . '" href="admin.php?page=starfish-settings&tab=testimonial_options&section=form">' . __('Form', 'starfish') . '</a>&nbsp;|&nbsp;</li>
                        <li> <a class="' . (($active_section === 'email') ? 'srm-nav-tab-section-active' : '') . '" href="admin.php?page=starfish-settings&tab=testimonial_options&section=email">' . __('Email', 'starfish') . '</a>&nbsp;|&nbsp;</li>
                        <li> <a class="' . (($active_section === 'help') ? 'srm-nav-tab-section-active' : '') . '" href="admin.php?page=starfish-settings&tab=testimonial_options&section=help">' . __('Help', 'starfish') . '</a> </li>
                    </ul></p><p>&nbsp;</p><p>&nbsp;</p>';
            if ($active_section === 'general') {
                settings_fields(SRM_OPTION_GROUP_TESTIMONIAL_GENERAL);
                do_settings_sections(SRM_OPTION_GROUP_TESTIMONIAL_GENERAL);
            } elseif ($active_section === 'form') {
                settings_fields(SRM_OPTION_GROUP_TESTIMONIAL_FORM);
                do_settings_sections(SRM_OPTION_GROUP_TESTIMONIAL_FORM);
            } elseif ($active_section === 'email') {
                settings_fields(SRM_OPTION_GROUP_TESTIMONIAL_EMAIL);
                do_settings_sections(SRM_OPTION_GROUP_TESTIMONIAL_EMAIL);
            } elseif ($active_section === 'help') {
                settings_fields(SRM_OPTION_GROUP_TESTIMONIAL_HELP);
                do_settings_sections(SRM_OPTION_GROUP_TESTIMONIAL_HELP);
            } else {
                settings_fields(SRM_OPTION_GROUP_TESTIMONIAL_GENERAL);
                do_settings_sections(SRM_OPTION_GROUP_TESTIMONIAL_GENERAL);
            }
        } elseif ($active_tab === 'support_options') {
            echo '<p><ul class="srm-nav-tab-section-wrapper">
                        <li> <a class="' . (($active_section === 'general') ? 'srm-nav-tab-section-active' : '') . '" href="admin.php?page=starfish-settings&tab=support_options&section=general">' . __('General', 'starfish') . '</a>&nbsp;|&nbsp;</li>
                        <li> <a class="' . (($active_section === 'console') ? 'srm-nav-tab-section-active' : '') . '" href="admin.php?page=starfish-settings&tab=support_options&section=console">' . __('Log Console', 'starfish') . '</a>&nbsp;|&nbsp;</li>
                        <li> <a class="' . (($active_section === 'info') ? 'srm-nav-tab-section-active' : '') . '" href="admin.php?page=starfish-settings&tab=support_options&section=info">' . __('Site Info', 'starfish') . '</a></li>
                    </ul></p><p>&nbsp;</p><p>&nbsp;</p>';
            if ($active_section === 'general') {
                settings_fields(SRM_OPTION_GROUP_SUPPORT_GENERAL);
                do_settings_sections(SRM_OPTION_GROUP_SUPPORT_GENERAL);
            } elseif ($active_section === 'console') {
                settings_fields(SRM_OPTION_GROUP_SUPPORT_CONSOLE);
                do_settings_sections(SRM_OPTION_GROUP_SUPPORT_CONSOLE);
            } elseif ($active_section === 'info') {
                settings_fields(SRM_OPTION_GROUP_SUPPORT_INFO);
                do_settings_sections(SRM_OPTION_GROUP_SUPPORT_INFO);
            } else {
                settings_fields(SRM_OPTION_GROUP_SUPPORT_GENERAL);
                do_settings_sections(SRM_OPTION_GROUP_SUPPORT_GENERAL);
            }
        } else {
            settings_fields(SRM_OPTION_GROUP_GENERAL);
            do_settings_sections(SRM_OPTION_GROUP_GENERAL);
        } // end if/else
        submit_button();
    ?>
		</form>
	</div>
	<?php
}
