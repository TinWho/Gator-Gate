/*
 * Code Snippet:       Gator-Gate-Firewall for bbPress
 * Description:        Advanced per-forum content filtration, clipboard copy-paste baggage reduction.
 * Version:            0.1.3-Alpa
 * AUTHOR:             Tin Who (https://tinfoilwho.com)
 * License:            GPL-2.0-or-later
 *
 * AI-generated/AI-assisted code provided AS-IS.
 * User assumes all risk and responsibility for use.
 */



if ( ! defined( 'ABSPATH' ) ) {
    exit; // Block direct access
}

/*
============================================================================
SECTION 1: BACKEND FORUM ATTRIBUTES UI (PART 1)
============================================================================
*/

// Register an independent Meta Box container for Forums
add_action( 'add_meta_boxes', 'gator_gate_register_metabox' );

// Hook directly into the absolute WordPress core Custom Post Type save hook
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
    // Nonce safety check token to protect form data packages
    wp_nonce_field( 'gator_gate_secure_save_action', 'gator_gate_secure_save_field' );

    $gator_gate_ports = [
        'port_1' => 'PORT 1: HEADINGS ALLOWED',
        'port_2' => 'PORT 2: IMAGES ALLOWED',
        'port_3' => 'PORT 3: VIDEOS ALLOWED',
        'port_4' => 'PORT 4: TABLES ALLOWED',
        'port_5' => 'PORT 5: COLOURS, ALIGNMENT & LISTS ALLOWED',
        'port_6' => 'PORT 6: FONTS FAMILY & SIZES ALLOWED',
        'port_7' => 'PORT 7: BYPASS FIREWALL (DISABLE ALL RULES)',
    ];

    echo '<p style="font-size:12px; color:#646970; margin-bottom:12px;">Toggle specific isolated firewall ports for this forum container:</p>';

    echo '<div id="gator_gate_ui_wrapper">';
    foreach ( $gator_gate_ports as $key_id => $box_label ) {
        $is_checked = get_post_meta( $post->ID, '_gator_gate_' . $key_id, true );
        
        echo '<p style="margin: 8px 0; line-height: 1.5;">';
        echo '<label style="display:inline-flex; align-items:center; width:100%; cursor:pointer; font-weight:500;">';
        echo '<input type="checkbox" name="gator_gate_' . esc_attr( $key_id ) . '" value="1" ' . checked( 1, $is_checked, false ) . ' style="margin:0 10px 0 0;" /> ';
        echo '<span style="vertical-align:middle;">' . esc_html( $box_label ) . '</span>';
        echo '</label>';
        echo '</p>';
    }
    echo '</div>';

    // ISOLATED BUTTON POSITIONED UNDER PORT 7
    echo '<div style="margin-top:15px; padding-top:12px; border-top:1px solid #dcdcde; text-align:right;">';
    echo '<button type="submit" name="gator_gate_isolated_save" value="1" class="button button-secondary button-large" style="width:100%; text-align:center; background:#135e96; color:#fff; border-color:#135e96; font-weight:600;">Update Firewall Configuration Only</button>';
    echo '<p style="font-size:11px; color:#646970; margin: 6px 0 0 0; text-align:center;">Updates ports instantly without modifying ambient post content text.</p>';
    echo '</div>';
}

function gator_gate_save_metabox_data( $post_id, $post ) {
    // TIMING BUG FIX: Only execute database updates if our custom firewall button was explicitly clicked
    if ( ! isset( $_POST['gator_gate_isolated_save'] ) || '1' !== $_POST['gator_gate_isolated_save'] ) {
        return $post_id;
    }

    if ( ! isset( $_POST['gator_gate_secure_save_field'] ) || ! wp_verify_nonce( $_POST['gator_gate_secure_save_field'], 'gator_gate_secure_save_action' ) ) {
        return $post_id;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return $post_id;
    }

    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return $post_id;
    }

    $gator_gate_keys = [ 'port_1', 'port_2', 'port_3', 'port_4', 'port_5', 'port_6', 'port_7' ];

    foreach ( $gator_gate_keys as $key_id ) {
        $post_var_name = 'gator_gate_' . $key_id;
        
        if ( isset( $_POST[ $post_var_name ] ) && '1' === $_POST[ $post_var_name ] ) {
            update_post_meta( $post_id, '_gator_gate_' . $key_id, 1 );
        } else {
            if ( get_post_meta( $post_id, '_gator_gate_' . $key_id, true ) ) {
                delete_post_meta( $post_id, '_gator_gate_' . $key_id );
            }
        }
    }
    
    return $post_id;
}


/*
============================================================================
SECTION 2: CONTEXT-AWARE SECURITY FILTER ENGINE (PART 2)
============================================================================
*/

add_filter( 'bbp_new_reply_pre_content',  'gator_gate_run_contextual_scrubber', 9999, 1 );
add_filter( 'bbp_new_topic_pre_content',  'gator_gate_run_contextual_scrubber', 9999, 1 );
add_filter( 'bbp_edit_reply_pre_content', 'gator_gate_run_contextual_scrubber', 9999, 1 );
add_filter( 'bbp_edit_topic_pre_content', 'gator_gate_run_contextual_scrubber', 9999, 1 );

function gator_gate_run_contextual_scrubber( $incoming_payload ) {
    $forum_id = 0;

    // Detect target forum location context dynamically
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

    if ( $forum_id > 0 ) {
        // =====================================================================
        // ABSOLUTE FIREWALL BYPASS CHECK (PORT 7)
        // =====================================================================
        $is_bypass_active = get_post_meta( $forum_id, '_gator_gate_port_7', true );
        
        if ( ! empty( $is_bypass_active ) ) {
            return $incoming_payload . "\n\n<!-- GATOR_GATE FIREWALL BYPASS: [Port 7 Active on Forum ID {$forum_id}]. Content unscrubbed. -->";
        }
    }

    $active_ports = [];
    if ( $forum_id > 0 ) {
        for ( $i = 1; $i <= 6; $i++ ) {
            $active_ports[ 'port_' . $i ] = get_post_meta( $forum_id, '_gator_gate_port_' . $i, true );
        }
    }

          /*
    ============================================================================
    DYNAMIC ATTRIBUTE BLACKLIST FILTER: STEP 1A (RUNS ABSOLUTE FIRST)
    ============================================================================
    Decodes submission trickery and strips multiline attributes completely.
    */
    $attribute_blacklist = [
        'data-src', 'data-scr', 'data-sizes', 'data-srcset', 
        'sizes', 'srcset', 'onclick', 'onload'
    ];

    if ( ! empty( $attribute_blacklist ) ) {
        // 1. Decode entities like &quot; first
        $incoming_payload = html_entity_decode( $incoming_payload, ENT_QUOTES, 'UTF-8' );

        // 2. Convert raw multi-line spaces/newlines into clean single spaces inside tags
        // This stops massive white space gaps inside srcset=" ... " from tricking the regex
        $incoming_payload = preg_replace_callback('/<[^>]+>/s', function($tag_match) {
            return preg_replace('/\s+/', ' ', $tag_match[0]);
        }, $incoming_payload);

        $attr_pattern = implode( '|', array_map( 'preg_quote', $attribute_blacklist ) );
        
        // 3. Strip the attributes out completely with single-line tracking
        $incoming_payload = preg_replace( '/\s+(?:' . $attr_pattern . ')\s*=\s*(?:"[^"]*"|\'[^\']*\')/is', '', $incoming_payload );
        $incoming_payload = preg_replace( '/\s+(?:' . $attr_pattern . ')\s*=\s*(?:[”’][^”’]*[”’])/ius', '', $incoming_payload );
        $incoming_payload = preg_replace( '/\s+(?:' . $attr_pattern . ')\s*=\s*[^[:space:]>]+/i', '', $incoming_payload );
    }

    /*
    ============================================================================
    MASTER WHITELIST FILTER: STEP 1B (THE DESIGN UPDATE)
    ============================================================================
    */
    $master_whitelist = [
        '<p>', '<br>', '<a>', '<span>', '<em>', '<strong>',
        '<h1>', '<h2>', '<h3>', '<h4>', '<h5>', '<h6>', 
        '<ul>', '<ol>', '<li>', 
        '<img>', '<picture>', '<source>', 
        '<table>', '<thead>', '<tbody>', '<tr>', '<th>', '<td>'
    ];

    // Optional: Convert layout summary <div> tags to regular <p> tags before strip_tags deletes them
    $incoming_payload = preg_replace('/<div([^>]*?)class="[^"]*summary[^"]*"[^>]*>(.*?)<\/div>/is', '<p>$2</p>', $incoming_payload);

    // Execute absolute baseline whitelist tag stripping layer immediately
    $incoming_payload = strip_tags( $incoming_payload, implode( '', $master_whitelist ) );

    /* 
    ============================================================================
    PORT PROCESSING PIPELINE CONTINUES BELOW...
    ============================================================================
    */


   /* --- PORT 1 MODULE: HEADINGS ALLOWED --- */
if ( empty( $active_ports['port_1'] ) ) {
    // Strip headings completely if Port 1 is UNCHECKED
    $incoming_payload = preg_replace( '/<(h[1-6])[^>]*>|<\/(h[1-6])>/i', '', $incoming_payload );
} else {
    // Headings are allowed! Pass them through with styles intact for lower ports.
}



 /* --- PORT 2 MODULE: IMAGES ALLOWED --- */
if ( empty( $active_ports['port_2'] ) ) {
    // Strip images completely if port 2 is UNCHECKED (empty)
    $incoming_payload = preg_replace( '/<(img|picture|source)[^>]*>|<\/(picture|source)>/i', '', $incoming_payload );
} else {
    // If port 2 is CHECKED, strip out heavy srcset, data-srcset, and media/sizes attributes to save weight
    // This cleans up <source> and <img> tags, leaving only standard src attributes
    $incoming_payload = preg_replace( '/\s*(srcset|data-srcset|sizes|data-sizes|media)="[^"]*"/i', '', $incoming_payload );
    
    // Optional: Remove now-empty <picture> wrapper tags if they wrap the <img>
    $incoming_payload = preg_replace( '/<\/?(picture|source)[^>]*>/i', '', $incoming_payload );
}


    /* --- PORT 3 MODULE: VIDEO EMBED SHORTCODE ALLOWED --- */
    if (  empty( $active_ports['port_3'] ) ) {
        // Strip video shortcodes completely if checked
        $incoming_payload = preg_replace( '/\[embed\b[^\]]*\](.*?)\[\/embed\]/i', '', $incoming_payload );
    }

    /* --- PORT 4 MODULE: TABLES ALLOWED --- */
    if (  empty( $active_ports['port_4'] ) ) {
        // Strip tables completely if checked
        $incoming_payload = preg_replace( '/<(table|thead|tbody|tr|th|td)[^>]*>|<\/(table|thead|tbody|tr|th|td)>/i', '', $incoming_payload );
    } else {
        // Standardise tables if unchecked
        $incoming_payload = preg_replace( '/<(table|thead|tbody|tr|th|td)[^>]*>/i', '<$1>', $incoming_payload );
    }

/* --- PORT 5 MODULE: ALLOW COLOURS, ALIGNMENTS  & REMOVE LISTS --- */
if ( empty( $active_ports['port_5'] ) ) {

    // 1. Strip colors and background-colors globally from ALL tags
    $incoming_payload = preg_replace( '/\b(color|background-color)\s*:\s*[^;"]+;?/i', '', $incoming_payload );

    // 2. Process paragraphs intelligently based on what is inside them
    $incoming_payload = preg_replace_callback('/<p([^>]*?)>(.*?)<\/p>/is', function($matches) {
        $attributes = $matches[1];
        $content = $matches[2];

        // If the paragraph wraps a video embed OR a standard image tag, PRESERVE its alignment
        if (stripos($content, '[embed') !== false || preg_match('/<img\b/i', $content)) {
            return $matches[0]; // Do not touch anything, return original paragraph
        }

        // Otherwise, it is a plain text paragraph, so strip its alignment style safely
        $clean_attributes = preg_replace('/\btext-align\s*:\s*[^;"]+;?/i', '', $attributes);
        return "<p" . $clean_attributes . ">" . $content . "</p>";
    }, $incoming_payload);

    // 3. Strip text-align from all other non-heading/non-paragraph elements (div, span, etc.)
    $incoming_payload = preg_replace( '/<(?!h[1-6]\b|p\b)[a-z1-6]+[^>]*?\K\btext-align\s*:\s*[^;"]+;?/i', '', $incoming_payload );

    // 4. Remove HTML list tags only
    $incoming_payload = preg_replace('/<\/?(ul|ol|li)[^>]*>/i', '', $incoming_payload);

    // 5. Remove list-style CSS only
    $incoming_payload = preg_replace(
        '/\blist-style(?:-type|-position|-image)?\s*:\s*[^;"]+;?/i',
        '',
        $incoming_payload
    );
}




    /* --- PORT 6 MODULE: ALLOW FONTS FAMILY & SIZES (SEPARATE) --- */
    if (  empty( $active_ports['port_6'] ) ) {
        // Strip custom font families, weights, styles, and sizing layouts
        $incoming_payload = preg_replace( '/\b(font-family|font-size|font-weight|font-style)\s*:[^;]+;?/i', '', $incoming_payload );
    }

    /* --- UTILITY CLEANUP LAYER --- */
    // Clean empty style tags left behind by Port 5 or 6 removals
    $incoming_payload = preg_replace( '/\s+style=["\']\s*;?\s*["\']/i', '', $incoming_payload );


    // Compute dynamic metrics audit trace inside comments
    $count_post_firewall = mb_strlen( $incoming_payload, 'UTF-8' );
    $vaporised_bytes     = $count_pre_firewall - $count_post_firewall;

    $incoming_payload .= "\n\n<!-- GATOR_GATE DEBUG [Forum:ID {$forum_id}]: [Before: {$count_pre_firewall} chars] -> [After: {$count_post_firewall} chars]. Purged {$vaporised_bytes} formatting bytes. -->";

    return $incoming_payload;
}
//end



