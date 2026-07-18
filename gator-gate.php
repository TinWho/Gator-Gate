/*
 * Code Snippet:       Gator-Gate for bbPress
 * Description:        Advanced per-forum content filtration, clipboard copy-paste baggage reduction.
 * Version:            0.1.0-Alpa
 * AUTHOR:             Tin Who (https://tinfoilwho.com)
 * License:            GPL-2.0-or-later
 *
 * AI-generated/AI-assisted code provided AS-IS.
 * User assumes all risk and responsibility for use.
 */


/*
======================================================================
Gator-Gate FOR bbPress

PART 1A: CORE FILTER ENGINE & DEBUG TRACKING (STRICT DEFAULT DENY)

System:
- Absolute Blank Slate Whitelist Initialisation (True Default Deny)
- Conditional Core Tag Injection Matrix
- Explicit Attribute Whitelisting Security Layer
======================================================================
*/

if (!defined('ABSPATH')) {
    exit;
}

/*
======================================================================
DEBUG REPORT STORAGE UTILITIES
======================================================================
*/
$GLOBALS['ugate_debug_report'] = array();

function ugate_bbpress_add_debug_report($message) {
    if (!isset($GLOBALS['ugate_debug_report'])) {
        $GLOBALS['ugate_debug_report'] = array();
    }
    $GLOBALS['ugate_debug_report'][] = $message;
}

function ugate_bbpress_get_debug_report() {
    if (isset($GLOBALS['ugate_debug_report'])) {
        return $GLOBALS['ugate_debug_report'];
    }
    return array();
}

/*
======================================================================
MAIN FILTER ENGINE BLOCK
======================================================================
*/
function ugate_bbpress_filter_content($content, $rules = array()) {
    if (empty($content)) {
        return $content;
    }

    $original_size = strlen($content);

    $defaults = array(
        'allow_video'    => false,
        'allow_links'    => false,
        'allow_images'   => false,
        'allow_code'     => false,
        'allow_styling'  => false,
        'allow_headings' => false,
        'allow_colour'   => false,
        'clean_paste'    => true,
        'allow_preview'  => false,
        'debug_report'   => false
    );

    $rules = wp_parse_args($rules, $defaults);

    if (!empty($rules['debug_report'])) {
        $GLOBALS['ugate_debug_report'] = array();
        ugate_bbpress_add_debug_report('uGate filter started');
    }

    // Force strict safety encoding if user checks Allow Raw Code
    if (!empty($rules['allow_code'])) {
        if (!empty($rules['debug_report'])) {
            ugate_bbpress_add_debug_report('Raw code mode enabled');
        }
        return esc_html($content);
    }

    // Process clipboard baggage reduction (Located in Part 1B)
    if (!empty($rules['clean_paste']) && function_exists('ugate_bbpress_clean_paste')) {
        $content = ugate_bbpress_clean_paste($content);
    }

    // Manage iframe and media boundaries
    if (!empty($rules['allow_video'])) {
        $before_iframe = substr_count($content, '<iframe');
        if (function_exists('ugate_bbpress_convert_iframe')) {
            $content = preg_replace_callback('/<iframe[^>]*src=["\']([^"\']+)["\'][^>]*>.*?<\/iframe>/is', 'ugate_bbpress_convert_iframe', $content);
        }
        if (!empty($rules['debug_report']) && $before_iframe > 0) {
            ugate_bbpress_add_debug_report('Video iframe processing: ' . $before_iframe . ' detected');
        }
    } else {
        $removed = substr_count($content, '<iframe');
        $content = preg_replace('/<iframe.*?<\/iframe>/is', '', $content);
        if (!empty($rules['debug_report']) && $removed) {
            ugate_bbpress_add_debug_report('Removed blocked iframe(s): ' . $removed);
        }
    }

    // Strip links if disabled
    if (empty($rules['allow_links'])) {
        $links = preg_match_all('/<a\b[^>]*>/i', $content, $matches);
        $content = preg_replace('/<a\b[^>]*>(.*?)<\/a>/is', '$1', $content);
        if (!empty($rules['debug_report']) && $links) {
            ugate_bbpress_add_debug_report('Removed links: ' . $links);
        }
    }

    /*
    --------------------------------------------------------------
    IMAGE PROCESSING BOUNDARY
    --------------------------------------------------------------
    */
    if (!empty($rules['allow_images'])) {
        // RUN THE COMPRESSION ENGINE IF IMAGES ARE ALLOWED (Located in Part 1B)
        if (function_exists('ugate_bbpress_optimise_forum_images')) {
            $content = ugate_bbpress_optimise_forum_images($content);
        }
    } else {
        // STRIP IMAGES ENTIRELY IF DISABLED
        $images = preg_match_all('/<img\b[^>]*>/i', $content, $matches);
        $content = preg_replace('/<img\b[^>]*>/is', '', $content);
        if (!empty($rules['debug_report']) && $images) {
            ugate_bbpress_add_debug_report('Removed images: ' . $images);
        }
    }

    // Strip dangerous object/script nodes unconditionally
$dangerous_tags = array('script', 'object', 'embed', 'form');
foreach ($dangerous_tags as $tag) {
    $count = preg_match_all('/<' . $tag . '\b[^>]*>(.*?)<\/' . $tag . '>/is', $content, $matches);
    if ($count && !empty($rules['debug_report'])) {
        ugate_bbpress_add_debug_report('Removed unsafe ' . $tag . ' blocks: ' . $count);
    }
}
$content = preg_replace('/<(script|object|embed|form)\b[^>]*>.*?<\/\1>/is', '', $content);
    
    /*
    ----------------──────────────────────────────────────────────
    THE BLANK SLATE WHITELIST ENGINE (STRICT DEFAULT DENY)
    ----------------──────────────────────────────────────────────
    */
    // 1. Initialise with absolute zero baseline tags (only core layout structure nodes)
    $allowed_tags = array(
        'p'    => array(), // Allows paragraph spacing so layout blocks stay readable
        'br'   => array(), // Allows line breaks
        'span' => array()  // Standard wrapping node required for inline text variables
    );

    // 2. Inject core formatting text nodes ONLY if "Allow Fonts & Styling" is checked
    if (!empty($rules['allow_styling'])) {
        $styling_tags = array('strong', 'em', 'u', 's', 'div', 'font', 'blockquote', 'ul', 'ol', 'li');
        foreach ($styling_tags as $tag) {
            $allowed_tags[$tag] = array();
        }
    }

    // 3. Inject standalone anchor/link structures ONLY if "Allow Links" is checked
    if (!empty($rules['allow_links'])) {
        $allowed_tags['a'] = array(
            'href'   => true,
            'title'  => true,
            'target' => true,
            'rel'    => true
        );
    }

    // 4. Inject visual media nodes ONLY if "Allow Images" is checked
    if (!empty($rules['allow_images'])) {
        $allowed_tags['img'] = array(
            'src'    => true,
            'alt'    => true,
            'title'  => true,
            'width'  => true,
            'height' => true
        );
    }

    // 5. Inject headings and complex data matrices ONLY if "Allow Headings & Tables" is checked
    if (!empty($rules['allow_headings'])) {
        $advanced_layout_tags = array(
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 
            'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td'
        );
        foreach ($advanced_layout_tags as $tag) {
            $allowed_tags[$tag] = array();
        }
    }

    // 6. Loop across the explicitly allowed tags to assign attribute rule variations
    foreach ($allowed_tags as $tag => $attributes) {
        // Grant permission to change fonts, sizes, and layout classes
        if (!empty($rules['allow_styling'])) {
            $allowed_tags[$tag]['style'] = true;
            $allowed_tags[$tag]['class'] = true;
        }

        // Grant permission to isolate custom text colors
        if (!empty($rules['allow_colour'])) {
            $allowed_tags[$tag]['color'] = true; // Core legacy color attribute mapping
            
            // If general styling is off but color is on, open style attribute ONLY for colors
            if (empty($rules['allow_styling'])) {
                $allowed_tags[$tag]['style'] = true;
            }
        }
    }

    // Run final text filtering passing sequence using our custom whitelist
    $content = wp_kses($content, $allowed_tags);

    if (!empty($rules['debug_report'])) {
        $final_size = strlen($content);
        ugate_bbpress_add_debug_report('uGate filter finished. Footprint altered by: ' . ($original_size - $final_size) . ' bytes.');
    }

    return $content;
}


/*
======================================================================
Gator-Gate FOR bbPress

PART 1B: PASTE CLEANUP & IMAGE OPTIMISER UTILITIES (UPDATED)

System:
- Smart Forum Image Sizing Condenser (Targeting 920w / 1080w)
- Native Clipboard Baggage Reduction (Google Docs / MS Word)
- Isolated Inline Colour Style Attribute Preservation
======================================================================
*/

if (!defined('ABSPATH')) {
    exit;
}

/*
======================================================================
SMART IMAGE SIZING CONDENSER
======================================================================
*/
function ugate_bbpress_optimise_forum_images($content) {
    if (empty($content)) {
        return $content;
    }

    $debug = isset($GLOBALS['ugate_debug_report']);

    // Check if the paste contains a modern multi-image source string or responsive layout
    if (stripos($content, 'srcset') !== false || stripos($content, '<source') !== false || stripos($content, '6a482ba920302760ce74e83a') !== false) {
        $before_count = strlen($content);
        $target_url = '';

        // Extract all distinct picture paths out of the raw layout string
        if (preg_match_all('/https:\/\/[^\s"\']+\.(?:jpg|jpeg|png|webp|gif)/i', $content, $matches)) {
            $found_urls = array_unique($matches[0]);
            
            // Default fallback is the very first image link discovered
            $target_url = !empty($found_urls) ? reset($found_urls) : '';

            // Smart Scan: Target the optimized medium/thumbnail width items (920w or 1080w)
            foreach ($found_urls as $url) {
                if (stripos($url, '/thumbnail/') !== false || stripos($url, '/m/') !== false || stripos($url, '/920w') !== false || stripos($url, '/xs/') !== false) {
                    $target_url = $url;
                    break; // Perfect forum resolution discovered. Stop checking.
                }
            }
        }

        // Collapse the entire multi-line block into one single clean img asset tag
        if (!empty($target_url)) {
            // Strip out complex wrapping tags completely
            $content = preg_replace('/<(?:picture|source)\b[^>]*>.*?<\/(?:picture)>/is', '<img src="' . esc_url($target_url) . '" style="max-width:100%; height:auto;" alt="Forum Image" />', $content);
            
            // Clean up loose dangling attribute elements or raw widths left behind
            $content = preg_replace('/(?:srcset|data-srcset|media|sizes)=["\'][^"\']*["\']/is', '', $content);
            $content = preg_replace('/(?:\b\d+w\b|,)/is', '', $content);
            
            // If it was just a loose img tag with messy attributes, swap it clean
            if (stripos($content, '<img') === false) {
                $content .= '<img src="' . esc_url($target_url) . '" style="max-width:100%; height:auto;" alt="Forum Image" />';
            }
        }

        $after_count = strlen($content);

        if ($debug && ($before_count > $after_count)) {
            ugate_bbpress_add_debug_report('Optimised messy responsive image array down to medium forum size. Bytes saved: ' . ($before_count - $after_count));
        }
    }

    return $content;
}

/*
======================================================================
PASTE CLEANUP UTILITY
======================================================================
*/
function ugate_bbpress_clean_paste($content) {
    if (empty($content)) {
        return $content;
    }

    $debug = isset($GLOBALS['ugate_debug_report']);

    // 1. Remove HTML comments / editor metadata
    $comments = preg_match_all('/<!--.*?-->/is', $content, $matches);
    $content = preg_replace('/<!--.*?-->/is', '', $content);
    if ($debug && $comments) {
        ugate_bbpress_add_debug_report('Removed editor comments/metadata: ' . $comments);
    }

    // 2. Remove Microsoft Word metadata styles
    $mso = preg_match_all('/\s*mso-[^:]+:[^;"]+;?/i', $content, $matches);
    $content = preg_replace('/\s*mso-[^:]+:[^;"]+;?/i', '', $content);
    if ($debug && $mso) {
        ugate_bbpress_add_debug_report('Removed Microsoft Word styles: ' . $mso);
    }

    // 3. Remove Microsoft Word formatting classes
    $mso_classes = preg_match_all('/\s*class=["\']?Mso[^"\']*["\']?/i', $content, $matches);
    $content = preg_replace('/\s*class=["\']?Mso[^"\']*["\']?/i', '', $content);
    if ($debug && $mso_classes) {
        ugate_bbpress_add_debug_report('Removed Word classes: ' . $mso_classes);
    }

    // 4. Fix malformed lists
    $content = preg_replace('/<li\s*=[^>]*>/i', '<li>', $content);

    // 5. Remove empty layout containers
    $empty_blocks = preg_match_all('/<(div|span|p)[^>]*>\s*<\/\1>/i', $content, $matches);
    $content = preg_replace('/<(div|span|p)[^>]*>\s*<\/\1>/i', '', $content);
    if ($debug && $empty_blocks) {
        ugate_bbpress_add_debug_report('Removed empty layout blocks: ' . $empty_blocks);
    }

    // 6. Reduce structural whitespace bloat
    $before_spaces = strlen($content);
    $content = preg_replace('/\s{3,}/', ' ', $content);
    $after_spaces = strlen($content);
    if ($debug && $before_spaces > $after_spaces) {
        ugate_bbpress_add_debug_report('Reduced excess spacing by: ' . ($before_spaces - $after_spaces) . ' bytes');
    }

    // 7. Normalise font declarations
    $fonts = preg_match_all('/font-family\s*:/i', $content, $matches);
    if ($fonts) {
        $content = preg_replace_callback('/font-family\s*:\s*([^;"]+);?/i', function($m){
            $font = trim(str_replace(array('"', "'"), '', $m[1]));
            return 'font-family:' . $font . ';';
        }, $content);
        if ($debug) { ugate_bbpress_add_debug_report('Normalised font declarations: ' . $fonts); }
    }

    // 8. Filter down inline attributes to safe layout variables only
    $style_count = preg_match_all('/style=["\'][^"\']*["\']/i', $content, $matches);
    if ($style_count) {
        // Fetch active forum rules context to guide the callback logic
        $forum_id = isset($_POST['bbp_forum_id']) ? absint($_POST['bbp_forum_id']) : 0;
        $meta = get_post_meta($forum_id, '_ugate_rules', true);
        $rules = is_array($meta) ? $meta : array();

        $content = preg_replace_callback('/style=["\']([^"\']*)["\']/i', function($m) use ($rules) {
            $style = $m[1];
            $keep = array();

            // Retain sizing and layout typography strictly if general styling is checked
            if (!empty($rules['allow_styling'])) {
                if (preg_match('/font-size\s*:[^;]+/i', $style, $sub))   { $keep[] = $sub[0]; }
                if (preg_match('/font-family\s*:[^;]+/i', $style, $sub)) { $keep[] = $sub[0]; }
            }

            // Retain layout positions and alignment rules universally if styling is checked
            if (!empty($rules['allow_styling'])) {
                if (preg_match('/text-align\s*:[^;]+/i', $style, $sub))  { $keep[] = $sub[0]; }
            }

            // Isolate and capture colour properties if checked, independent of styling switches
            if (!empty($rules['allow_colour']) || !isset($rules['allow_colour'])) {
                if (preg_match('/(?<!-)color\s*:[^;]+/i', $style, $sub)) { $keep[] = $sub[0]; }
            }

            return !empty($keep) ? 'style="' . implode(';', $keep) . '"' : '';
        }, $content);
        if ($debug) { ugate_bbpress_add_debug_report('Optimised inline styles: ' . $style_count); }
    }

    // 9. Wipe clean empty lists elements
    $content = preg_replace('/<(ul|ol)>\s*<\/\1>/i', '', $content);
    $content = preg_replace('/<li>\s*<\/li>/i', '', $content);

    return $content;
}

/*
======================================================================
Gator-Gate FOR bbPress

PART 2: bbPress SUBMISSION HOOKS (UPDATED)

System:
- Topic Filtering (Create & Edit)
- Reply Filtering (Create & Edit)
- Dynamic Context Pipeline Connection
======================================================================
*/

if (!defined('ABSPATH')) {
    exit;
}

// Bind Create and Edit State Hooks for Topics
add_filter('bbp_new_topic_pre_content', 'ugate_bbpress_filter_topic', 10, 1);
add_filter('bbp_edit_topic_pre_content', 'ugate_bbpress_filter_topic', 10, 1);

function ugate_bbpress_filter_topic($content) {
    $forum_id = 0;

    if (isset($_POST['bbp_forum_id'])) {
        $forum_id = absint($_POST['bbp_forum_id']);
    }

    $meta = get_post_meta($forum_id, '_ugate_rules', true);
    $rules = is_array($meta) ? $meta : array();

    return ugate_bbpress_filter_content($content, $rules);
}

// Bind Create and Edit State Hooks for Replies
add_filter('bbp_new_reply_pre_content', 'ugate_bbpress_filter_reply', 10, 1);
add_filter('bbp_edit_reply_pre_content', 'ugate_bbpress_filter_reply', 10, 1);

function ugate_bbpress_filter_reply($content) {
    $topic_id = 0;

    if (isset($_POST['bbp_topic_id'])) {
        $topic_id = absint($_POST['bbp_topic_id']);
    }

    $forum_id = 0;
    if (function_exists('bbp_get_topic_forum_id')) {
        $forum_id = bbp_get_topic_forum_id($topic_id);
    }

    $meta = get_post_meta($forum_id, '_ugate_rules', true);
    $rules = is_array($meta) ? $meta : array();

    return ugate_bbpress_filter_content($content, $rules);
}
/*
======================================================================
Gator-Gate FOR bbPress

PART 3: FORUM SETTINGS UI (UPDATED FOR HEADINGS, STYLING & COLOURS)

System:
- Flat Naming Parameters
- Granular Styling & Colour Management UI
- jQuery Code Interlocking Toggle Rules
======================================================================
*/

if (!defined('ABSPATH')) {
    exit;
}

add_action('add_meta_boxes', 'ugate_bbpress_add_forum_settings_box');

function ugate_bbpress_add_forum_settings_box() {
    add_meta_box(
        'ugate_forum_settings',
        'U_Gate-bbPress Content Rules',
        'ugate_bbpress_forum_settings_box',
        'forum',
        'side',
        'default',
        array('__back_compat_meta_box' => false) 
    );
}

function ugate_bbpress_forum_settings_box($post) {
    $meta = get_post_meta($post->ID, '_ugate_rules', true);
    
    // Explicitly check if we have ANY saved history to avoid default overrides
    $has_history = is_array($meta);
    $rules = $has_history ? $meta : array();
    
    // FIX: Only apply the true default state if this forum has never been saved before
    $allow_links    = $has_history ? !empty($rules['allow_links'])    : true;
    $allow_images   = $has_history ? !empty($rules['allow_images'])   : true;
    $allow_video    = $has_history ? !empty($rules['allow_video'])    : true;
    $allow_styling  = $has_history ? !empty($rules['allow_styling'])  : true;
    $allow_headings = $has_history ? !empty($rules['allow_headings']) : true; 
    $allow_colour   = $has_history ? !empty($rules['allow_colour'])   : true; // New color parameter extraction
    
    $allow_code    = !empty($rules['allow_code']);
    $allow_preview = !empty($rules['allow_preview']);
    $debug_report  = !empty($rules['debug_report']);

    wp_nonce_field('ugate_save_rules', 'ugate_rules_nonce');
    ?>

    <div id="ugate-meta-wrapper" style="position:relative; z-index:999;">
        <p><strong>Configure U_Gate-bbPress Rules</strong></p>

        <p><label><input type="checkbox" name="ugate_allow_links" value="1" <?php checked($allow_links); ?>> Allow Links</label></p>
        <p><label><input type="checkbox" name="ugate_allow_images" value="1" <?php checked($allow_images); ?>> Allow Images</label></p>
        <p><label><input type="checkbox" name="ugate_allow_video" value="1" <?php checked($allow_video); ?>> Allow Video Embeds</label></p>
        <p><label><input type="checkbox" name="ugate_allow_styling" value="1" <?php checked($allow_styling); ?>> Allow Fonts & Styling</label></p>
        <p><label><input type="checkbox" name="ugate_allow_headings" value="1" <?php checked($allow_headings); ?>> Allow Headings & Tables</label></p> 
        <p><label><input type="checkbox" name="ugate_allow_colour" value="1" <?php checked($allow_colour); ?>> Allow Colour</label></p> <!-- Isolated color rule option -->
        <p><label><input type="checkbox" name="ugate_allow_code" value="1" <?php checked($allow_code); ?>> Allow Raw Code / HTML</label></p>

        <hr style="margin: 15px 0; border:0; border-top:1px solid #ccd0d4;">

        <p><label><input type="checkbox" name="ugate_allow_preview" value="1" <?php checked($allow_preview); ?>> Enable uGate Preview Button</label></p>
        <p><label><input type="checkbox" name="ugate_debug_report" value="1" <?php checked($debug_report); ?>> Show Preview Debug Report</label></p>
    </div>

    <script>
    jQuery(document).ready(function($){
        var container = $('#ugate-meta-wrapper');
        
        container.on('change', 'input[name="ugate_allow_code"]', function(){
            if($(this).is(':checked')){
                container.find('input[name="ugate_allow_links"], input[name="ugate_allow_images"], input[name="ugate_allow_video"], input[name="ugate_allow_styling"], input[name="ugate_allow_headings"], input[name="ugate_allow_colour"]').prop('checked', false);
            }
        });
        
        container.on('change', 'input[name="ugate_allow_links"], input[name="ugate_allow_images"], input[name="ugate_allow_video"], input[name="ugate_allow_styling"], input[name="ugate_allow_headings"], input[name="ugate_allow_colour"]', function(){
            if($(this).is(':checked')){ 
                container.find('input[name="ugate_allow_code"]').prop('checked', false); 
            }
        });
    });
    </script>
    <?php
}

/*
======================================================================
Gator-Gate FOR bbPress

PART 4: FORUM RULE SAVE ENGINE (UPDATED FOR HEADINGS & COLOURS)

System:
- Flat Parameter Form Capture
- Total Value State Preservation
- Deep-level bbPress Storage Synchronization
======================================================================
*/

if (!defined('ABSPATH')) {
    exit;
}

add_action('bbp_forum_attributes_save', 'ugate_bbpress_save_forum_rules', 99, 1);
add_action('save_post_forum', 'ugate_bbpress_save_forum_rules', 99, 1);
add_action('save_post', 'ugate_bbpress_save_forum_rules', 99, 1);

function ugate_bbpress_save_forum_rules($post_id) {

    if (!isset($_POST['ugate_rules_nonce'])) {
        return;
    }

    if (!wp_verify_nonce(sanitize_key($_POST['ugate_rules_nonce']), 'ugate_save_rules')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    /*
    --------------------------------------------------------------
    Capture and commit explicit form selection values
    --------------------------------------------------------------
    */
    $rules = array(
        'allow_links'    => isset($_POST['ugate_allow_links'])    ? true : false,
        'allow_images'   => isset($_POST['ugate_allow_images'])   ? true : false,
        'allow_video'    => isset($_POST['ugate_allow_video'])    ? true : false,
        'allow_styling'  => isset($_POST['ugate_allow_styling'])  ? true : false,
        'allow_headings' => isset($_POST['ugate_allow_headings']) ? true : false, // Headings & Tables parameter
        'allow_colour'   => isset($_POST['ugate_allow_colour'])   ? true : false, // New isolated colour rule parameter
        'allow_code'     => isset($_POST['ugate_allow_code'])     ? true : false,
        'allow_preview'  => isset($_POST['ugate_allow_preview'])  ? true : false,
        'debug_report'   => isset($_POST['ugate_debug_report'])   ? true : false,
        'clean_paste'    => true
    );

    // Update metadata directly with exact values
    update_post_meta($post_id, '_ugate_rules', $rules);
}
/*
======================================================================
Gator-Gate FOR bbPress

PART 5: PREVIEW, SOURCE TOGGLE & EDITOR CAPTURE ENGINE (UPDATED)

System:
- Visual / HTML Code Source Toggle Engine
- Event Delegated Preview Button 
- Native Color-Aware Render Response Pipeline
======================================================================
*/

if (!defined('ABSPATH')) {
    exit;
}

/*
======================================================================
AJAX RECEIVER
======================================================================
*/
add_action('wp_ajax_ugate_preview_test', 'ugate_bbpress_preview_test');
add_action('wp_ajax_nopriv_ugate_preview_test', 'ugate_bbpress_preview_test');

function ugate_bbpress_preview_test() {
    $content  = isset($_POST['content']) ? wp_unslash($_POST['content']) : '';
    $forum_id = isset($_POST['forum_id']) ? absint($_POST['forum_id']) : 0;

    if (empty($content)) {
        wp_send_json_success(array('message' => 'AJAX working, but no editor content received.'));
    }

    $meta = get_post_meta($forum_id, '_ugate_rules', true);
    $rules = is_array($meta) ? $meta : array();
    
    // Explicitly lock debug report creation tracing internally
    $rules['debug_report'] = true;

    $filtered_content = ugate_bbpress_filter_content($content, $rules);
    $debug_logs = ugate_bbpress_get_debug_report();
    $debug_output = '';

    if (!empty($meta['debug_report'])) {
        $debug_output = ugate_bbpress_render_debug_report($debug_logs);
    }

    $html_response = '<strong>Filtered Content Preview:</strong><br><br>' . wp_kses_post($filtered_content);
    if (!empty($debug_output)) {
        $html_response .= '<hr style="margin:20px 0; border:0; border-top:1px dashed #ccc;">' . $debug_output;
    }

    wp_send_json_success(array('message' => $html_response));
}

/*
======================================================================
BUTTON INJECTION
======================================================================
*/
add_action('bbp_theme_before_topic_form_submit_wrapper', 'ugate_bbpress_preview_button');
add_action('bbp_theme_before_reply_form_submit_wrapper', 'ugate_bbpress_preview_button');

function ugate_bbpress_preview_button() {
    $forum_id = 0;

    if (function_exists('bbp_get_forum_id')) { $forum_id = bbp_get_forum_id(); }
    if (!$forum_id && function_exists('bbp_get_topic_forum_id')) {
        if (bbp_is_single_topic()) { $forum_id = bbp_get_topic_forum_id(); }
    }

    if (!$forum_id) { return; }

    $meta = get_post_meta($forum_id, '_ugate_rules', true);
    $rules = is_array($meta) ? $meta : array();

    if (empty($rules['allow_preview'])) { return; }
    ?>

    <div id="ugate-button-wrapper" style="margin-bottom: 15px; display:inline-block; width:100%;">
        <button type="button" id="ugate-preview-button" class="button" style="margin-right: 5px;">Preview uGate Filter</button>
        <button type="button" id="ugate-source-toggle" class="button" data-mode="visual">Toggle HTML Source</button>

        <textarea id="ugate-code-editor-fallback" style="display:none; width:100%; height:200px; font-family:monospace; font-size:13px; margin-top:10px; padding:10px; background:#1e1e1e; color:#f8f8f2; border:1px solid #111; border-radius:4px;"></textarea>
        <div id="ugate-preview-result" style="display:none; margin-top:15px; padding:15px; border:1px solid #ccd0d4; border-radius:4px; background:#f6f7f7;"></div>
    </div>

    <script>
    jQuery(document).ready(function($){
        var body = $('body');

        body.on('click', '#ugate-source-toggle', function(e) {
            e.preventDefault();
            var btn = $(this);
            var currentMode = btn.attr('data-mode');
            var rawTextarea = $('#bbp_topic_content, #bbp_reply_content').first();
            var codeFallback = $('#ugate-code-editor-fallback');
            var hasTinyMCE = (typeof tinymce !== 'undefined' && tinymce.activeEditor && !tinymce.activeEditor.isHidden());

            if (currentMode === 'visual') {
                var rawHTML = '';
                if (hasTinyMCE) {
                    rawHTML = tinymce.activeEditor.getContent();
                    tinymce.activeEditor.hide();
                } else if (rawTextarea.length) {
                    rawHTML = rawTextarea.val();
                    rawTextarea.hide();
                }
                codeFallback.val(rawHTML).show().focus();
                btn.attr('data-mode', 'code').addClass('button-primary').text('Return to Visual View');
            } else {
                var updatedHTML = codeFallback.val();
                codeFallback.hide();
                if (hasTinyMCE) {
                    tinymce.activeEditor.show();
                    tinymce.activeEditor.setContent(updatedHTML);
                } else if (rawTextarea.length) {
                    rawTextarea.val(updatedHTML).show();
                }
                btn.attr('data-mode', 'visual').removeClass('button-primary').text('Toggle HTML Source');
            }
        });

        body.on('click', '#ugate-preview-button', function(e){
            e.preventDefault();
            var content = '';
            var codeFallback = $('#ugate-code-editor-fallback');

            if (codeFallback.is(':visible')) {
                content = codeFallback.val();
            } else {
                if (typeof tinymce !== 'undefined') {
                    var activeEditor = tinymce.activeEditor;
                    if (activeEditor && !activeEditor.isHidden()) { content = activeEditor.getContent(); }
                }
                if (!content) {
                    var textarea = $('#bbp_topic_content, #bbp_reply_content').first();
                    if (textarea.length) { content = textarea.val(); }
                }
            }

            $.post(
                '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',
                {
                    action: 'ugate_preview_test',
                    content: content,
                    forum_id: '<?php echo absint($forum_id); ?>'
                },
                function(response){
                    if (response.success) {
                        $('#ugate-preview-result').html(response.data.message).slideDown();
                    } else {
                        $('#ugate-preview-result').html('<span style="color:#d63638;">Preview processing failed.</span>').slideDown();
                    }
                }
            );
        });
    });
    </script>
    <?php
}

function ugate_bbpress_render_debug_report($report = array()) {
    if (empty($report)) { return ''; }
    $html = '<div class="ugate-debug-report" style="background:#fff; border-left:4px solid #72aee6; padding:12px; margin-top:10px;">';
    $html .= '<strong style="color:#1d2327; display:block; margin-bottom:8px;">uGate System Activity Log:</strong>';
    foreach ($report as $item) {
        $html .= '<div style="font-family:monospace; font-size:12px; color:#2c3338; margin-bottom:4px;"><span style="color:#00a32a; font-weight:bold;">✓</span> ' . esc_html($item) . '</div>';
    }
    $html .= '</div>';
    return $html;
}

