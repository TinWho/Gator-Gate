/*
 * Code Snippet:       Gator-Gate-Firewall for bbPress
 * Description:        Advanced per-forum content filtration, clipboard copy-paste baggage reduction.
 * Version:            0.0.4-Alpa
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
SECTION 1: BACKEND FORUM ATTRIBUTES UI
============================================================================
*/

// Register an independent Meta Box container for Forums
add_action( 'add_meta_boxes', 'gator_gate_register_metabox' );

// Hook directly into the absolute WordPress core Custom Post Type save hook
add_action( 'save_post_forum', 'gator_gate_save_metabox_data', 10, 2 );


function gator_gate_register_metabox() {

    add_meta_box(
        'gator_gate_firewall_ports',
        'Gator Gate Firewall Ports',
        'gator_gate_render_metabox_content',
        'forum',
        'side',
        'high'
    );
}


function gator_gate_render_metabox_content( $post ) {

    // Nonce safety check token to protect form data packages
    wp_nonce_field(
        'gator_gate_secure_save_action',
        'gator_gate_secure_save_field'
    );


    /*
    =========================================================================
    GATOR-GATE PORT MAP
    =========================================================================
    */

    $gator_gate_ports = [

        'port_1' => 'PORT 1: HEADINGS WITH ALIGNMENT & LISTS ALLOWED',
        'port_2' => 'PORT 2: IMAGES ALLOWED',
        'port_3' => 'PORT 3: VIDEOS ALLOWED',
        'port_4' => 'PORT 4: TABLES ALLOWED',
        'port_5' => 'PORT 5: COLOURS ALLOWED',
        'port_6' => 'PORT 6: TEXT ALIGNMENT ALLOWED',
        'port_7' => 'PORT 7: FONTS & SIZES ALLOWED',
        'port_8' => 'PORT 8: PRESERVE PREFORMATTED TEXT',
        'port_9' => 'PORT 9: BYPASS FIREWALL (DISABLE ALL RULES)',

    ];


    echo '<p style="font-size:12px; color:#646970; margin-bottom:12px;">Toggle specific isolated firewall ports for this forum container:</p>';

    echo '<div id="gator_gate_ui_wrapper">';


    foreach ( $gator_gate_ports as $key_id => $box_label ) {

        $is_checked = get_post_meta(
            $post->ID,
            '_gator_gate_' . $key_id,
            true
        );


        echo '<p style="margin: 8px 0; line-height: 1.5;">';

        echo '<label style="display:inline-flex; align-items:center; width:100%; cursor:pointer; font-weight:500;">';

        echo '<input type="checkbox" name="gator_gate_' .
            esc_attr( $key_id ) .
            '" value="1" ' .
            checked( 1, $is_checked, false ) .
            ' style="margin:0 10px 0 0;" /> ';

        echo '<span style="vertical-align:middle;">' .
            esc_html( $box_label ) .
            '</span>';

        echo '</label>';

        echo '</p>';
    }


    echo '</div>';


    /*
    =========================================================================
    PER-FORUM SUBMISSION LIMIT
    =========================================================================
    */

    $gator_gate_submission_limit = get_post_meta(
        $post->ID,
        '_gator_gate_submission_limit',
        true
    );


    // Existing forums with no saved value use the default of 5000
    if ( $gator_gate_submission_limit === '' ) {
        $gator_gate_submission_limit = 5000;
    }


    echo '<div style="margin-top:15px; padding-top:12px; border-top:1px solid #dcdcde;">';


    echo '<label style="display:block; font-size:12px; font-weight:600; margin-bottom:6px;">';
    echo 'MAXIMUM SUBMISSION SIZE';
    echo '</label>';


    echo '<input
        type="number"
        name="gator_gate_submission_limit"
        value="' . esc_attr( $gator_gate_submission_limit ) . '"
        min="1"
        max="10000"
        step="1"
        inputmode="numeric"
        style="width:80px;"
        />';


    echo '<span style="font-size:14px; color:#646970; margin-left:6px;">characters</span>';


    echo '<p style="font-size:11px; color:#646970; margin:5px 0 0;">';
    echo 'Allowed range: 1–10,000. Default: 5,000.';
    echo '</p>';


    echo '</div>';


    /*
    =========================================================================
    ISOLATED BUTTON POSITIONED UNDER PORT 9
    =========================================================================
    */

    echo '<div style="margin-top:15px; padding-top:12px; border-top:1px solid #dcdcde; text-align:right;">';


    echo '<button
        type="submit"
        name="gator_gate_isolated_save"
        value="1"
        class="button button-secondary button-large"
        style="width:100%; text-align:center; background:#135e96; color:#fff; border-color:#135e96; font-weight:600;">
        Update Firewall Configuration Only
    </button>';


    echo '<p style="font-size:11px; color:#646970; margin:6px 0 0; text-align:center;">';
    echo 'Updates ports instantly without modifying ambient post content text.';
    echo '</p>';


    echo '</div>';
}



function gator_gate_save_metabox_data( $post_id, $post ) {

    // Only execute database updates if our custom firewall button was explicitly clicked
    if (
        ! isset( $_POST['gator_gate_isolated_save'] ) ||
        '1' !== $_POST['gator_gate_isolated_save']
    ) {
        return $post_id;
    }


    if (
        ! isset( $_POST['gator_gate_secure_save_field'] ) ||
        ! wp_verify_nonce(
            $_POST['gator_gate_secure_save_field'],
            'gator_gate_secure_save_action'
        )
    ) {
        return $post_id;
    }


    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return $post_id;
    }


    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return $post_id;
    }


    /*
    =========================================================================
    PORTS 1 - 9
    =========================================================================
    */

    $gator_gate_keys = [
        'port_1',
        'port_2',
        'port_3',
        'port_4',
        'port_5',
        'port_6',
        'port_7',
        'port_8',
        'port_9'
    ];


    foreach ( $gator_gate_keys as $key_id ) {

        $post_var_name = 'gator_gate_' . $key_id;


        if (
            isset( $_POST[ $post_var_name ] ) &&
            '1' === $_POST[ $post_var_name ]
        ) {

            update_post_meta(
                $post_id,
                '_gator_gate_' . $key_id,
                1
            );

        } else {

            if (
                get_post_meta(
                    $post_id,
                    '_gator_gate_' . $key_id,
                    true
                )
            ) {

                delete_post_meta(
                    $post_id,
                    '_gator_gate_' . $key_id
                );
            }
        }
    }


    /*
    =========================================================================
    SAVE PER-FORUM SUBMISSION LIMIT
    =========================================================================
    */

    if ( isset( $_POST['gator_gate_submission_limit'] ) ) {

        $submission_limit = absint(
            $_POST['gator_gate_submission_limit']
        );


        // Enforce hard limits server-side
        $submission_limit = max(
            1,
            min(
                10000,
                $submission_limit
            )
        );


        update_post_meta(
            $post_id,
            '_gator_gate_submission_limit',
            $submission_limit
        );
    }


    return $post_id;
}



/*
============================================================================
SECTION 2: SUBMISSION LIMIT ERROR DISPLAY
============================================================================
*/

add_action(
    'wp_footer',
    'gator_gate_display_submission_limit_error',
    999
);


function gator_gate_display_submission_limit_error() {

    if ( ! is_user_logged_in() ) {
        return;
    }


    $user_id = get_current_user_id();


    $error_key = 'gator_gate_limit_error_' . $user_id;


    $message = get_transient(
        $error_key
    );


    if ( empty( $message ) ) {
        return;
    }


    // Remove immediately so the message only appears once
    delete_transient(
        $error_key
    );


    ?>

    <div
        id="gator-gate-submission-error"
        style="
            position:fixed;
            top:20px;
            right:20px;
            z-index:999999;
            max-width:450px;
            padding:15px 20px;
            background:#fff;
            border:2px solid #d63638;
            color:#d63638;
            box-shadow:0 4px 15px rgba(0,0,0,.2);
            font-size:15px;
            line-height:1.5;
        "
    >
        <strong>Gator Gate:</strong>
        <?php echo esc_html( $message ); ?>
    </div>

    <?php
}



/*
============================================================================
SECTION 3: CONTEXT-AWARE SECURITY FILTER ENGINE
============================================================================
*/

add_filter(
    'bbp_new_reply_pre_content',
    'gator_gate_run_contextual_scrubber',
    9999,
    1
);

add_filter(
    'bbp_new_topic_pre_content',
    'gator_gate_run_contextual_scrubber',
    9999,
    1
);

add_filter(
    'bbp_edit_reply_pre_content',
    'gator_gate_run_contextual_scrubber',
    9999,
    1
);

add_filter(
    'bbp_edit_topic_pre_content',
    'gator_gate_run_contextual_scrubber',
    9999,
    1
);


function gator_gate_run_contextual_scrubber( $incoming_payload ) {

    $forum_id = 0;


    /*
    =========================================================================
    DETECT TARGET FORUM LOCATION CONTEXT
    =========================================================================
    */

    if (
        function_exists( 'bbp_get_reply_forum_id' ) &&
        bbp_is_reply_edit()
    ) {

        $forum_id = bbp_get_reply_forum_id(
            bbp_get_reply_id()
        );

    } elseif (
        function_exists( 'bbp_get_topic_forum_id' ) &&
        bbp_is_topic_edit()
    ) {

        $forum_id = bbp_get_topic_forum_id(
            bbp_get_topic_id()
        );

    } elseif (
        isset( $_POST['bbp_forum_id'] )
    ) {

        $forum_id = absint(
            $_POST['bbp_forum_id']
        );

    } elseif (
        isset( $_POST['bbp_topic_id'] )
    ) {

        $topic_id = absint(
            $_POST['bbp_topic_id']
        );

        $forum_id = function_exists( 'bbp_get_topic_forum_id' )
            ? bbp_get_topic_forum_id( $topic_id )
            : 0;
    }



    /*
    =========================================================================
    PORT 9: ABSOLUTE FIREWALL BYPASS
    =========================================================================
    */

    if ( $forum_id > 0 ) {

        $is_bypass_active = get_post_meta(
            $forum_id,
            '_gator_gate_port_9',
            true
        );


        if ( ! empty( $is_bypass_active ) ) {

            return $incoming_payload .
                "\n\n<!-- GATOR_GATE FIREWALL BYPASS: [Port 9 Active on Forum ID {$forum_id}]. Content unscrubbed. -->";
        }
    }



    /*
    =========================================================================
    LOAD ACTIVE PORTS
    =========================================================================
    */

    $active_ports = [];


    if ( $forum_id > 0 ) {

        for ( $i = 1; $i <= 8; $i++ ) {

            $active_ports[ 'port_' . $i ] = get_post_meta(
                $forum_id,
                '_gator_gate_port_' . $i,
                true
            );
        }
    }



    /*
    ============================================================================
    CAPTURE INITIAL COUNT BEFORE CLEANUP
    ============================================================================
    */

    $count_pre_firewall = mb_strlen(
        $incoming_payload,
        'UTF-8'
    );


    /*
    ============================================================================
    DYNAMIC ATTRIBUTE BLACKLIST FILTER: STEP 1A
    ============================================================================
    */

    $attribute_blacklist = [
        'data-src',
        'data-scr',
        'data-sizes',
        'data-srcset',
        'sizes',
        'srcset',
        'onclick',
        'onload'
    ];


    if ( ! empty( $attribute_blacklist ) ) {

        // Decode entities like &quot; first
        $incoming_payload = html_entity_decode(
            $incoming_payload,
            ENT_QUOTES,
            'UTF-8'
        );


        // Clean whitespace gaps inside tags, but explicitly ignore &nbsp;
        $incoming_payload = preg_replace_callback(
            '/<[^>]+>/s',
            function( $tag_match ) {

                return preg_replace(
                    '/[^\S\x{00A0}]+|[\r\n\t]+/u',
                    ' ',
                    $tag_match[0]
                );
            },
            $incoming_payload
        );


        $attr_pattern = implode(
            '|',
            array_map(
                'preg_quote',
                $attribute_blacklist
            )
        );


        // Strip quoted attributes
        $incoming_payload = preg_replace(
            '/\s+(?:' . $attr_pattern . ')\s*=\s*(?:"[^"]*"|\'[^\']*\')/is',
            '',
            $incoming_payload
        );


        // Strip curly-quoted attributes
        $incoming_payload = preg_replace(
            '/\s+(?:' . $attr_pattern . ')\s*=\s*(?:[”’][^”’]*[”’])/ius',
            '',
            $incoming_payload
        );


        // Strip unquoted attributes
        $incoming_payload = preg_replace(
            '/\s+(?:' . $attr_pattern . ')\s*=\s*[^[:space:]>]+/i',
            '',
            $incoming_payload
        );
    }



    /*
    ============================================================================
    MASTER WHITELIST FILTER: STEP 1B
    ============================================================================
    */

    $master_whitelist = [
        '<p>',
        '<br>',
        '<a>',
        '<span>',
        '<em>',
        '<strong>',
        '<h1>',
        '<h2>',
        '<h3>',
        '<h4>',
        '<h5>',
        '<h6>',
        '<ul>',
        '<ol>',
        '<li>',
        '<img>',
        '<picture>',
        '<source>',
        '<table>',
        '<thead>',
        '<tbody>',
        '<tr>',
        '<th>',
        '<td>',
        '<pre>',
        '<code>',
    ];


    // Convert layout summary divs to regular paragraphs
    $incoming_payload = preg_replace(
        '/<div([^>]*?)class="[^"]*summary[^"]*"[^>]*>(.*?)<\/div>/is',
        '<p>$2</p>',
        $incoming_payload
    );


    // Execute absolute baseline whitelist tag stripping layer
    $incoming_payload = strip_tags(
        $incoming_payload,
        implode( '', $master_whitelist )
    );


    /*
    ============================================================================
    PORT PROCESSING PIPELINE
    ============================================================================
    */


    /*
    ---------------------------------------------------------------------------
    PORT 1 MODULE: HEADINGS WITH ALIGNMENT & LISTS
    ---------------------------------------------------------------------------
    */

    if ( empty( $active_ports['port_1'] ) ) {

        // Strip headings completely if Port 1 is unchecked
        $incoming_payload = preg_replace(
            '/<(h[1-6])[^>]*>|<\/(h[1-6])>/i',
            '',
            $incoming_payload
        );


        // Strip lists completely if Port 1 is unchecked
        $incoming_payload = preg_replace(
            '/<\/?(ul|ol|li)[^>]*>/i',
            '',
            $incoming_payload
        );

    } else {

        // Headings, heading alignment and lists are allowed.
    }



    /*
    ---------------------------------------------------------------------------
    PORT 2 MODULE: IMAGES
    ---------------------------------------------------------------------------
    */

    if ( empty( $active_ports['port_2'] ) ) {

        // Strip images completely if Port 2 is unchecked
        $incoming_payload = preg_replace(
            '/<(img|picture|source)[^>]*>|<\/(picture|source)>/i',
            '',
            $incoming_payload
        );

    } else {

        // Existing image cleanup retained
        $incoming_payload = preg_replace(
            '/\s*(srcset|data-srcset|sizes|data-sizes|media)="[^"]*"/i',
            '',
            $incoming_payload
        );


        // Remove picture/source wrappers after cleanup
        $incoming_payload = preg_replace(
            '/<\/?(picture|source)[^>]*>/i',
            '',
            $incoming_payload
        );
    }



    /*
    ---------------------------------------------------------------------------
    PORT 3 MODULE: VIDEO EMBED SHORTCODE
    ---------------------------------------------------------------------------
    */

    if ( empty( $active_ports['port_3'] ) ) {

        // Strip video shortcodes completely if Port 3 is unchecked
        $incoming_payload = preg_replace(
            '/\[embed\b[^\]]*\](.*?)\[\/embed\]/i',
            '',
            $incoming_payload
        );
    }



    /*
    ---------------------------------------------------------------------------
    PORT 4 MODULE: TABLES
    ---------------------------------------------------------------------------
    */

    if ( empty( $active_ports['port_4'] ) ) {

        // Strip tables completely if Port 4 is unchecked
        $incoming_payload = preg_replace(
            '/<(table|thead|tbody|tr|th|td)[^>]*>|<\/(table|thead|tbody|tr|th|td)>/i',
            '',
            $incoming_payload
        );

    } else {

        // Standardise tables if Port 4 is checked
        $incoming_payload = preg_replace(
            '/<(table|thead|tbody|tr|th|td)[^>]*>/i',
            '<$1>',
            $incoming_payload
        );
    }



    /*
    ---------------------------------------------------------------------------
    PORT 5 MODULE: COLOURS
    ---------------------------------------------------------------------------
    */

    if ( empty( $active_ports['port_5'] ) ) {

        // Strip colours and background-colors globally
        $incoming_payload = preg_replace(
            '/\b(color|background-color)\s*:\s*[^;"]+;?/i',
            '',
            $incoming_payload
        );
    }



    /*
    ---------------------------------------------------------------------------
    PORT 6 MODULE: TEXT ALIGNMENT
    ---------------------------------------------------------------------------
    */

    if ( empty( $active_ports['port_6'] ) ) {

        // Process paragraphs intelligently
        $incoming_payload = preg_replace_callback(
            '/<p([^>]*?)>(.*?)<\/p>/is',
            function( $matches ) {

                $attributes = $matches[1];
                $content    = $matches[2];


                // Preserve alignment around videos and images
                if (
                    stripos( $content, '[embed' ) !== false ||
                    preg_match( '/<img\b/i', $content )
                ) {

                    return $matches[0];
                }


                // Strip alignment from normal paragraphs
                $clean_attributes = preg_replace(
                    '/\btext-align\s*:\s*[^;"]+;?/i',
                    '',
                    $attributes
                );


                return '<p' .
                    $clean_attributes .
                    '>' .
                    $content .
                    '</p>';
            },
            $incoming_payload
        );


        // Strip text-align from other non-heading/non-paragraph elements
        $incoming_payload = preg_replace(
            '/<(?!h[1-6]\b|p\b)[a-z1-6]+[^>]*?\K\btext-align\s*:\s*[^;"]+;?/i',
            '',
            $incoming_payload
        );
    }



    /*
    ---------------------------------------------------------------------------
    PORT 7 MODULE: FONTS FAMILY & SIZES
    ---------------------------------------------------------------------------
    */

    if ( empty( $active_ports['port_7'] ) ) {

        // Strip custom font families, weights, styles and sizing
        $incoming_payload = preg_replace(
            '/\b(font-family|font-size|font-weight|font-style)\s*:[^;]+;?/i',
            '',
            $incoming_payload
        );
    }



    /*
    ---------------------------------------------------------------------------
    PORT 8 MODULE: PREFORMATTED TEXT
    ---------------------------------------------------------------------------
    */

    if ( empty( $active_ports['port_8'] ) ) {

        // Strip preformatted/code tags if Port 8 is unchecked
        $incoming_payload = preg_replace(
            '/<\/?(pre|code)[^>]*>/i',
            '',
            $incoming_payload
        );
    }



    /*
    ============================================================================
    UTILITY CLEANUP LAYER
    ============================================================================
    */

    // Clean empty style attributes left behind by Port 5 or Port 7
    $incoming_payload = preg_replace(
        '/\s+style=["\']\s*;?\s*["\']/i',
        '',
        $incoming_payload
    );



    /*
    ============================================================================
    FINAL CLEANED SUBMISSION SIZE CHECK
    ============================================================================
    */

    // Default limit
    $gator_gate_submission_limit = 5000;


    // Load this forum's saved limit
    if ( $forum_id > 0 ) {

        $saved_submission_limit = get_post_meta(
            $forum_id,
            '_gator_gate_submission_limit',
            true
        );


        if ( $saved_submission_limit !== '' ) {

            $gator_gate_submission_limit = max(
                1,
                min(
                    10000,
                    absint( $saved_submission_limit )
                )
            );
        }
    }


    /*
    -------------------------------------------------------------------------
    COUNT THE CLEANED SUBMISSION
    -------------------------------------------------------------------------
    */

    $current_submission_length = mb_strlen(
        $incoming_payload,
        'UTF-8'
    );


    /*
    -------------------------------------------------------------------------
    BLOCK IF CLEANED SUBMISSION EXCEEDS FORUM LIMIT
    -------------------------------------------------------------------------
    */

    if (
        $current_submission_length >
        $gator_gate_submission_limit
    ) {

        if ( is_user_logged_in() ) {

            set_transient(
                'gator_gate_limit_error_' . get_current_user_id(),
                sprintf(
                    'Your cleaned submission is too long. Maximum allowed: %s characters. Your cleaned submission: %s characters.',
                    number_format_i18n(
                        $gator_gate_submission_limit
                    ),
                    number_format_i18n(
                        $current_submission_length
                    )
                ),
                60
            );
        }


        // Stop the submission. Nothing is saved.
        return '';
    }



    /*
    ============================================================================
    FINAL METRICS AUDIT TRACE
    ============================================================================
    */

    $count_post_firewall = mb_strlen(
        $incoming_payload,
        'UTF-8'
    );


    $vaporised_bytes =
        $count_pre_firewall -
        $count_post_firewall;


    /*
    -------------------------------------------------------------------------
    GATOR-GATE AUDIT COMMENT
    -------------------------------------------------------------------------
    */

    $incoming_payload .=
        "\n\n<!-- GATOR_GATE FIREWALL [Forum:ID {$forum_id}]: [Before: {$count_pre_firewall} chars] -> [After: {$count_post_firewall} chars]. Purged {$vaporised_bytes} formatting bytes. -->";


    return $incoming_payload;
}

// end
