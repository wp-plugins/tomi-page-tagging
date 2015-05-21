<?php
/*
Plugin Name: Tomi Page Tagging
Author: Tomi Novak
Description: Allow pages to be given tags just as if they were posts.
Version: 0.1
*/

/*  Copyright 2015  Tomi Novak (email : dev.tomi33@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define('TOMI_PAGE_TAGS', __FILE__);

class TomiPageTags {


    function __construct() {

        register_activation_hook(TOMI_PAGE_TAGS, array($this, 'activate'));
        register_deactivation_hook(TOMI_PAGE_TAGS, array($this, 'deactivate'));

        add_action( 'init', array($this, 'add_tags_to_pages'));
        add_action('admin_init', array($this, 'page_tagging_settings'));
        add_action('admin_menu', array($this, 'add_to_admin_menu'));
    }


    protected $setting_name = 'tomi_page_tagging_options';

    protected $default_values = array(
        'display-tagged-pages-with-tagged-posts' => 'no'
    );

    function add_tags_to_pages() {
        register_taxonomy_for_object_type('post_tag', 'page');

        $display_option = get_option($this->setting_name);

        if ($display_option['display-tagged-pages-with-tagged-posts'] == 'yes') {
            add_action('pre_get_posts', array($this, 'override_tag_query_post_type'));
        }
    }

    function page_tagging_settings(){
        register_setting('tomi_page_tagging_options', $this->setting_name, array($this, 'validate_setting'));
    }

    function validate_setting($input) {

        $valid = array();

        $valid['display-tagged-pages-with-tagged-posts'] = sanitize_text_field($input['display-tagged-pages-with-tagged-posts']);

        if ((($valid['display-tagged-pages-with-tagged-posts']) != 'yes') && (($valid['display-tagged-pages-with-tagged-posts']) != 'no' )) {
            add_settings_error(
                'display-tagged-pages-with-tagged-posts',   // Setting title
                'page_tagging_error',                       // Error ID
                'Please enter a valid option (yes or no)',  // Error message
                'error'                                     // Type of message
            );

            // Set it to the default value
            $valid['display-tagged-pages-with-tagged-posts'] = $this->default_values['display-tagged-pages-with-tagged-posts'];
        }

        return $valid;
    }

    function add_to_admin_menu(){
        add_options_page('tomi_page_tagging_options', 'Tomi Page Tagging Settings', 'manage_options', 'tomi_page_tagging_options', array($this, 'menu_tagging_settings_page'));
    }

    function menu_tagging_settings_page(){
        $options = get_option($this->setting_name);
        ?>
        <div class="wrap">
            <h2>Tomi Page Tagging Option</h2>
            <form method="post" action="options.php">
                <?php settings_fields('tomi_page_tagging_options'); ?>
                <table class="form-table">
                    <tr valign="top"><th>Display tagged pages with tagged posts:</th>
                        <td>
                            <select name="<?php echo $this->setting_name?>[display-tagged-pages-with-tagged-posts]">
                                <option value="no"  <?php if( $options['display-tagged-pages-with-tagged-posts'] == 'no') : echo "selected"; endif ?>> No</option>
                                <option value="yes" <?php if( $options['display-tagged-pages-with-tagged-posts'] == 'yes') : echo "selected"; endif ?>>Yes</option>
                            </select>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
                </p>
            </form>
        </div>
    <?php
    }

    function override_tag_query_post_type($query) {

        if ( $query->is_archive && $query->is_tag ) {
            $q = &$query->query_vars;
            $q['post_type'] = 'any';
        }

    }

    public function activate() {
        update_option($this->setting_name, $this->default_values);
    }

    public function deactivate() {
        delete_option($this->setting_name);
    }
}

$tomi_page_tags = new TomiPageTags();