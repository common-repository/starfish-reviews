<?php

namespace Starfish;

use Exception;

class Activation extends Freemius
{
    /**
     * @return void
     */
    public function license_key_auto_activation()
    {
        $fs = $this->starfish_fs();

        $option_key = "srm_auto_license_activation";

        if ('pending' != get_option($option_key, 'pending')) {
            return;
        }

        try {
            $next_page = $fs->activate_migrated_license(WP__STARFISH_FS__LICENSE_KEY);
        } catch (Exception $e) {
            update_option($option_key, 'unexpected_error');
            return;
        }

        if ($fs->can_use_premium_code()) {
            update_option($option_key, 'done');
            // TODO: Enable White-label mode via Freemius API


            if (is_string($next_page)) {
                fs_redirect($next_page);
            }
        } else {
            update_option($option_key, 'failed');
        }
    }
}
