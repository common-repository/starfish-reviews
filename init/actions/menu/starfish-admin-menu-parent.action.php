<?php

add_action('admin_menu', 'srm_main_menu');
function srm_main_menu()
{
    add_menu_page('Starfish Reviews', 'Starfish Reviews', 'administrator', 'starfish-parent', null, 'dashicons-star-filled', 6);
}
