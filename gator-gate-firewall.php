/*
 * Code Snippet:       Gator-Gate-Firewall for bbPress
 * Description:        Advanced per-forum content filtration, clipboard copy-paste baggage reduction.
 * Version:            0.1.0-Alpa
 * AUTHOR:             Tin Who (https://tinfoilwho.com)
 * License:            GPL-2.0-or-later
 *
 * AI-generated/AI-assisted code provided AS-IS.
 * User assumes all risk and responsibility for use.
 */

/**
 * Plugin Name: Gator Gate Firewall - Part 1 (Forum Attributes UI)
 * Description: Registers isolated Gator Gate firewall port controls for bbPress Forums with forum-specific content permissions.
 * Version: 1.3.0
 * Author: AI Collaborator
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Block direct access
}

// Rem: Explicitly register an completely independent Meta Box container for Forums
add_action( 'add_meta_boxes', 'gator_gate_register_metabox' );

// Rem: Hook directly into the absolute WordPress core Custom Post Type save hook
add_action( 'save_post_forum', 'gator_gate_save_metabox_data', 10, 2 );

function gator_gate_register_metabox() {
    add_meta_box(
        'gator_gate_firewall_ports',          // Unique HTML element ID
        'Gator Gate Firewall Ports',          // Box Display Title
        'gator_gate_render_metabox_content',  // Inner Content Renderer Function
        'forum',                              // Targets explicitly the bbPress Forum CPT
        'side',                               // Forces position into the Right Sidebar
        'high'                                // Pushes block to top of dashboard panel stack
    );
}

function gator_gate_render_metabox_content( $post ) {
    // Rem: Nonce safety check token to protect form data packages
    wp_nonce_field( 'gator_gate_secure_save_action', 'gator_gate_secure_save_field' );

    $gator_gate_ports = [
    'port_1' => 'PORT 1: HEADINGS ALLOWED',
    'port_2' => 'PORT 2: IMAGES ALLOWED',
    'port_3' => 'PORT 3: VIDEOS ALLOWED',
    'port_4' => 'PORT 4: TABLES ALLOWED',
    'port_5' => 'PORT 5: COLOURS ALLOWED',
    'port_6' => 'PORT 6: FONTS ALLOWED',
];

    echo '<p style="font-size:12px; color:#646970; margin-bottom:12px;">Open specific isolated firewall ports for this forum container:</p>';

    foreach ( $gator_gate_ports as $key_id => $box_label ) {
        // Rem: Extract the metadata using the exact keys used by the MK2 Engine
        $is_checked = get_post_meta( $post->ID, '_gator_gate_' . $key_id, true );
        
        echo '<p style="margin: 6px 0;">';
        echo '<label style="display:inline-flex; align-items:center; cursor:pointer;">';
        echo '<input type="checkbox" name="gator_gate_port_' . esc_attr( $key_id ) . '" value="1" ' . checked( 1, $is_checked, false ) . ' style="margin-top:0; margin-right:8px;" /> ';
        echo '<span>' . esc_html( $box_label ) . '</span>';
        echo '</label>';
        echo '</p>';
    }
}

function gator_gate_save_metabox_data( $post_id, $post ) {
    // Rem: Verify data integrity against REST API background calls
    if ( ! isset( $_POST['gator_gate_secure_save_field'] ) || ! wp_verify_nonce( $_POST['gator_gate_secure_save_field'], 'gator_gate_secure_save_action' ) ) {
        return $post_id;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return $post_id;
    }

    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return $post_id;
    }

    $gator_gate_keys = [ 'port_1', 'port_2', 'port_3', 'port_4', 'port_5', 'port_6' ];

    foreach ( $gator_gate_keys as $key_id ) {
        $post_var_name = 'gator_gate_port_' . $key_id;
        
        // Rem: Safely write explicit integers to the database for engine scanning
        if ( isset( $_POST[ $post_var_name ] ) && '1' === $_POST[ $post_var_name ] ) {
            update_post_meta( $post_id, '_gator_gate_' . $key_id, 1 );
        } else {
            // Rem: Clean the entry completely if unchecked
            delete_post_meta( $post_id, '_gator_gate_' . $key_id );
        }
    }
    
    return $post_id;
}



/**
 * Plugin Name: Gator Gate Firewall - Part 2 (Context-Aware Engine)
 * Description: Text processing engine that pulls unique metadata configurations depending on the destination forum target.
 * Version: 1.2.0
 * Author: AI Collaborator
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Block direct access
}

add_filter( 'bbp_new_reply_pre_content',  'gator_gate_run_contextual_scrubber', 9, 1 );
add_filter( 'bbp_new_topic_pre_content',  'gator_gate_run_contextual_scrubber', 9, 1 );
add_filter( 'bbp_edit_reply_pre_content', 'gator_gate_run_contextual_scrubber', 9, 1 );
add_filter( 'bbp_edit_topic_pre_content', 'gator_gate_run_contextual_scrubber', 9, 1 );

function gator_gate_run_contextual_scrubber( $incoming_payload ) {
    // Rem: Dynamically extract where this message payload is physically headed
    $forum_id = 0;

    if ( function_exists( 'bbp_get_reply_forum_id' ) && bbp_is_reply_edit() ) {
        $forum_id = bbp_get_reply_forum_id( bbp_get_reply_id() );
    } elseif ( function_exists( 'bbp_get_topic_forum_id' ) && bbp_is_topic_edit() ) {
        $forum_id = bbp_get_topic_forum_id( bbp_get_topic_id() );
    } elseif ( isset( $_POST['bbp_forum_id'] ) ) {
        $forum_id = absint( $_POST['bbp_forum_id'] );
    } elseif ( isset( $_POST['bbp_topic_id'] ) ) {
        $topic_id = absint( $_POST['bbp_topic_id'] );
        $forum_id = function_exists( 'bbp_get_topic_forum_id' ) ? bbp_get_topic_forum_id( $topic_id ) : 0;
    }

    // Rem: Fetch specific post meta configuration exceptions tied to this forum container
    $active_ports = [];
    if ( $forum_id > 0 ) {
        for ( $i = 1; $i <= 6; $i++ ) {
            $active_ports[ 'port_' . $i ] = get_post_meta( $forum_id, '_gator_gate_port_' . $i, true );
        }
     
    }

    // Rem: Track initial raw character count trace
    $count_pre_firewall = mb_strlen( $incoming_payload, 'UTF-8' );

    // Rem: Enforce zero-trust default-deny array baseline tags
    $permitted_tags = [ '<p>', '<br>' ];

    if ( ! empty( $active_ports['port_1'] ) ) {
        $permitted_tags = array_merge( $permitted_tags, [ '<h1>', '<h2>', '<h3>', '<h4>', '<h5>', '<h6>', '<table>', '<tr>', '<td>', '<th>', '<thead>', '<tbody>' ] );
    }
    if ( ! empty( $active_ports['port_2'] ) ) { $permitted_tags[] = '<img>'; }
    if ( ! empty( $active_ports['port_3'] ) ) { $permitted_tags[] = '<iframe>'; }
    if ( ! empty( $active_ports['port_4'] ) ) { $permitted_tags[] = '<a>'; }

    $css_enabled = ( ! empty( $active_ports['port_5'] ) || ! empty( $active_ports['port_6'] ) );

    // Rem: Perform foundational strip tags layer
    $incoming_payload = strip_tags( $incoming_payload, implode( '', $permitted_tags ) );

    // Rem: Deep sweep tracking blocks and zero-width spaces
    $incoming_payload = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $incoming_payload );
    $incoming_payload = preg_replace( '/\x{200B}|\x{200C}|\x{200D}|\x{FEFF}/u', '', $incoming_payload );

  /*
============================================================================
[REM BLOCK] PORT 1 MODULE: HEADINGS GUARD
============================================================================
*/
if ( ! empty( $active_ports['port_1'] ) ) {

    // Clean opening heading tags
    $incoming_payload = preg_replace(
        '/<(h[1-6])[^>]*>/i',
        '<$1>',
        $incoming_payload
    );
}
    /*==========================================================================*/



/*
============================================================================
[REM BLOCK] PORT 3 MODULE: VIDEOS GUARD
============================================================================
*/
if ( ! empty( $active_ports['port_3'] ) ) {

    // Allowed video providers
    $video_whitelist = array(
        'youtube.com',
        'youtu.be',
        'vimeo.com',
        'dailymotion.com',
        'twitch.tv'
    );

    $incoming_payload = preg_replace_callback(
        '/<iframe\b([^>]*)>(.*?)<\/iframe>/i',
        function( $matches ) use ( $video_whitelist ) {

            // Full iframe string
            $iframe = $matches[0];

            /*
            Search iframe string for approved video providers
            */
            $video_url = '';

            foreach ( $video_whitelist as $provider ) {

                if ( preg_match(
                    '#(?:https?:)?//[^"\s<>]*' . preg_quote( $provider, '#' ) . '[^"\s<>]*#i',
                    $iframe,
                    $video_match
                ) ) {

                    $video_url = $video_match[0];
                    break;

                }
            }

            // Fix protocol-relative URLs
            if ( strpos( $video_url, '//' ) === 0 ) {
                $video_url = 'https:' . $video_url;
            }

            /*
            Capture iframe dimensions
            */
            preg_match(
                '/width\s*=\s*["\']([^"\']+)["\']/i',
                $iframe,
                $width_match
            );

            preg_match(
                '/height\s*=\s*["\']([^"\']+)["\']/i',
                $iframe,
                $height_match
            );

            $width  = ! empty( $width_match[1] ) ? $width_match[1] : '400';
            $height = ! empty( $height_match[1] ) ? $height_match[1] : '224';


            /*
            Replace iframe with WordPress embed format
            */
            return '[embed width="' . $width . '" height="' . $height . '"]'
                . $video_url .
                '[/embed]';

        },
        $incoming_payload
    );
}


  /*
============================================================================
[REM BLOCK] PORT 4 MODULE: TABLES GUARD
============================================================================
*/
if ( ! empty( $active_ports['port_4'] ) ) {

    // Clean table tags
    $incoming_payload = preg_replace(
        '/<(table|thead|tbody|tr|th|td)[^>]*>/i',
        '<$1>',
        $incoming_payload
    );
}

    /*==========================================================================*/

/*
============================================================================
[REM BLOCK] PORT 5 MODULE: COLOURS GUARD
============================================================================
*/
if ( ! empty( $active_ports['port_5'] ) ) {

    $incoming_payload = preg_replace_callback(
        '/(\s+style=["\'])([^"\']*)(["\'])/i',
        function( $hits ) {

            $raw_style_rules = $hits[2];
            $saved_css = [];

            if ( preg_match(
                '/\b(background-)?color\s*:\s*([^;]+)/i',
                $raw_style_rules,
                $matched
            ) ) {
                $saved_css[] = $matched[0];
            }

            return ! empty( $saved_css )
                ? ' style="' . esc_attr( implode( '; ', $saved_css ) ) . ';"'
                : '';

        },
        $incoming_payload
    );
}
/*==========================================================================*/


/*
============================================================================
[REM BLOCK] PORT 6 MODULE: FONTS GUARD
============================================================================
*/
if ( ! empty( $active_ports['port_6'] ) ) {

    $incoming_payload = preg_replace_callback(
        '/(\s+style=["\'])([^"\']*)(["\'])/i',
        function( $hits ) {

            $raw_style_rules = $hits[2];
            $saved_css = [];

            if ( preg_match(
                '/\bfont-family\s*:\s*([^;]+)/i',
                $raw_style_rules,
                $matched
            ) ) {
                $saved_css[] = $matched[0];
            }

            return ! empty( $saved_css )
                ? ' style="' . esc_attr( implode( '; ', $saved_css ) ) . ';"'
                : '';

        },
        $incoming_payload
    );
}
/*==========================================================================*/

    // Rem: Compute and output dynamic metrics audit trace inside comments
    $count_post_firewall = mb_strlen( $incoming_payload, 'UTF-8' );
    $vaporised_bytes     = $count_pre_firewall - $count_post_firewall;

    $incoming_payload .= "\n\n<!-- GATOR_GATE DEBUG [Forum:ID {$forum_id}]: [Before: {$count_pre_firewall} chars] -> [After: {$count_post_firewall} chars]. Purged {$vaporised_bytes} formatting bytes. -->";

    return $incoming_payload;

}

//end


//end


