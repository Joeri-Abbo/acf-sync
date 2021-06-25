<?php

/**
 * Plugin Name: ACF Sync
 * Description: Adds Sync option to WP CLI for syncing ACF fields
 *
 * Author: Joeri Abbo
 * Author URI: https://www.linkedin.com/in/joeri-abbo-43a457144/
 *
 * Version: 1.0.0
 */


// File Security Check
defined('ABSPATH') or die("No script kiddies please!");


if (defined('WP_CLI') && WP_CLI && !class_exists('ACF_Commands')) :

    /**
     * ACF_Commands
     */
    class ACF_Commands extends WP_CLI_Command
    {

        /**
         * Sync ACF Fields
         *
         * ## OPTIONS
         *
         * @when init
         *
         * @example
         *
         *  wp acf sync
         *
         */
        function sync($args, $assoc_args)
        {

            // vars
            $groups = acf_get_field_groups();
            $sync = array();

            // bail early if no field groups
            if (empty($groups))
                return;

            // find JSON field groups which have not yet been imported
            foreach ($groups as $group) {

                // vars
                $local = acf_maybe_get($group, 'local', false);
                $modified = acf_maybe_get($group, 'modified', 0);
                $private = acf_maybe_get($group, 'private', false);

                // ignore DB / PHP / private field groups
                if ($local !== 'json' || $private) {

                    // do nothing

                } elseif (!$group['ID']) {

                    $sync[$group['key']] = $group;

                } elseif ($modified && $modified > get_post_modified_time('U', true, $group['ID'], true)) {

                    $sync[$group['key']] = $group;
                }
            }

            // bail if no sync needed
            if (empty($sync)) {
                WP_CLI::success("No ACF Sync Required");
                return;
            }

            if (!empty($sync)) { //if( ! empty( $keys ) ) {

                // vars
                $new_ids = array();

                foreach ($sync as $key => $v) { //foreach( $keys as $key ) {

                    // append fields
                    if (acf_have_local_fields($key)) {

                        $sync[$key]['fields'] = acf_get_local_fields($key);

                    }
                    // import
                    $field_group = acf_import_field_group($sync[$key]);
                }
            }

            WP_CLI::success('ACF SYNC SUCCESS!');
        }

    }

    WP_CLI::add_command('acf', 'ACF_Commands');

endif; // ACF_Commands
