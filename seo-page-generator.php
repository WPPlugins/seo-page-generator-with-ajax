<?php

/*
Plugin Name: SEO Page Generator
Description: Generates multiple Pages from a given HTML-Template. Additional sets the correct Metas for YOAST SEO Plugin
Version: 1.0
Author: Hendrik Bäker
Author URI: https://baeker-it.de
License: A "Slug" license name e.g. GPL2
*/

add_action('admin_menu', 'baekerIT_page_generator_menu');
add_action('admin_init', 'baekerIT_page_generator_init');

function baekerIT_page_generator_init()
{
    register_setting('baekerIT_page_generator_settings', 'baekerIT_page_generator_setting', 'baekerIT_page_generator_validate');
}

function baekerIT_page_generator_validate($input)
{
    return $input;
}

function baekerIT_page_generator_menu()
{
    add_menu_page('Page Generator', 'Page Generator', 'edit_posts', 'baekerIT_page_generator', 'baekerIT_page_generator', null, 3);
}

add_action('init', 'loadScripts');
function baekerIT_page_generatorloadScripts()
{
    wp_enqueue_script('page-generator', plugin_dir_url(__FILE__) . 'assets/js/page-generator.js', array('jquery'), false, true);
}

function baekerIT_page_generator()
{
    ?>
    <div class="wrap">
        <h3>Einstellungen</h3>
        <form action="options.php" method="post" enctype="multipart/form-data">
            <?php
            settings_fields('baekerIT_page_generator_settings');
            $settings = get_option('baekerIT_page_generator_setting');
            ?>
            <style>
                input.seo {
                    width: 50rem !important;
                }

                textarea.seo {
                    width: 50rem !important;
                    height: 400px !important;
                }
            </style>
            <table>
                <tr>
                    <td><label for="citys">Städte (mit komma getrennt)</label></td>
                    <td><textarea class="seo" name="baekerIT_page_generator_setting[citys]"
                                  id="citys"><?php echo $settings['citys'] ?></textarea></td>
                </tr>
                <tr>
                    <td><label for="placeholder">Zu ersetzender Platzhalter IM TEXT</label></td>
                    <td><input type="text" class="seo" name="baekerIT_page_generator_setting[placeholder]"
                               id="placeholder" value="<?php echo $settings['placeholder'] ?>"></td>
                </tr>
                <tr>
                    <td><label for="pagetitle">Titel der Seite MIT Platzhalter</label></td>
                    <td><input type="text" class="seo" name="baekerIT_page_generator_setting[pagetitle]" id="pagetitle"
                               value="<?php echo $settings['pagetitle'] ?>"></td>
                </tr>
                <tr>
                    <td><label for="text">Seiteninhalt (HTML)</label></td>
                    <td><textarea class="seo" name="baekerIT_page_generator_setting[text]"
                                  id="text"><?php echo $settings['text'] ?></textarea></td>
                </tr>
                <tr>
                    <td><label for="link_prefix">Slug (Link Präfix)</label></td>
                    <td><input type="text" class="seo" name="baekerIT_page_generator_setting[link_prefix]"
                               id="link_prefix" value="<?php echo $settings['link_prefix'] ?>"></td>
                </tr>
                <tr>
                    <td><label for="link_text">Zu ersetzende Link-Beschriftung</label></td>
                    <td><input type="text" class="seo" name="baekerIT_page_generator_setting[link_text]" id="link_text"
                               value="<?php echo $settings['link_text'] ?>"></td>
                </tr>
                <tr>
                    <td><label for="seo_keyword_prefix">YOAST SEO Keyword Prefix</label></td>
                    <td><input type="text" class="seo" name="baekerIT_page_generator_setting[yoast_keyword_prefix]"
                               id="seo_keyword_prefix" value="<?php echo $settings['yoast_keyword_prefix'] ?>"></td>
                </tr>
                <tr>
                    <td><label for="seo_title">YOAST SEO Titel</label></td>
                    <td><input type="text" class="seo" name="baekerIT_page_generator_setting[yoast_title]"
                               id="seo_title" value="<?php echo $settings['yoast_title'] ?>"></td>
                </tr>
                <tr>
                    <td><label for="seo_description">YOAST SEO Description</label></td>
                    <td><textarea class="seo" name="baekerIT_page_generator_setting[yoast_description]"
                                  id="seo_description"><?php echo $settings['yoast_description'] ?></textarea></td>
                </tr>
                <tr>
                    <td colspan="2"><input type="submit" name="submit" value="Speichern"></td>
                </tr>
            </table>

        </form>
        <h3>Seiten erstellen / aktualisieren / löschen</h3>
        <form action="" method="POST">
            <input type="button" id="create" name="create" value="Erstellen / Aktualisieren">
            <input type="button" id="delete" name="delete" value="Löschen">
        </form>
        <p id="status"></p>
        <p id="success"></p>
        <?php
        $pages = get_posts(['post_type' => 'page', 'post_status' => 'publish', 'numberposts' => -1]);
        foreach($pages as $post)
        {
            if(get_post_meta($post->ID, '_baekerIT_page_generator', true) != null)
            {
                $titles[] = $post->post_title;
            }
        }
        if(isset($titles)):
        ?>
        <table>
            <?php
            $array = explode(", ", $settings['citys']);
            foreach($array as $title)
            {
                if(!in_array($title, $titles))
                {
                    echo "<tr><td>$title</td></tr>";
                }

            }
            ?>
        </table>
            <?php
            endif;
            ?>
    </div>
    <?php

}

add_action('wp_ajax_deletePages', 'baekerIT_page_generator_deletePages');
function baekerIT_page_generator_deletePages()
{
    foreach (get_posts(['post_status' => 'publish', 'numberposts' => -1, 'post_type' => 'page']) as $page) {
        if(get_post_meta($page->ID, '_baekerIT_page_generator', true) != null)
        {
            wp_delete_post($page->ID, true);
        }
    }
    wp_die();
}

add_action('wp_ajax_createPages', 'baekerIT_page_generator_createPages');

function baekerIT_page_generator_createPages()
{
    settings_fields('baekerIT_page_generator_settings');
    $settings = get_option('baekerIT_page_generator_setting');
    $pagetitle = $settings['pagetitle'];
    $text = $settings['text'];
    $link = strtolower(str_replace(' ', '-', $pagetitle));
    $link_prefix = $settings['link_prefix'];
    $link_text = $settings['link_text'];
    $placeholder = $settings['placeholder'];
    $seo_keyword_prefix = $settings['yoast_keyword_prefix'];
    $seo_title = $settings['yoast_title'];
    $seo_description = $settings['yoast_description'];
    $city = filter_input(INPUT_POST, 'city');
    $nextcity = filter_input(INPUT_POST, 'nextcity');
    $search = [
        ' ',
        'Ä',
        'Ü',
        'Ö',
        'ä',
        'ö',
        'ü'
    ];
    $replace = [
        '-',
        'Ae',
        'Ue',
        'Oe',
        'ae',
        'oe',
        'ue'
    ];
    $counter = 0;
        $title = str_replace($placeholder, $city, $pagetitle);
        $content = str_replace($placeholder, ucfirst($city), $text);
        $seotitle = str_replace($placeholder, $city, $seo_title);
        $seodescription = str_replace($placeholder, $city, $seo_description);
        if (!is_object(get_page_by_title($title))) {
            set_time_limit(0);

            if (isset($citys[$counter + 1])) {
                $content = str_replace($link_prefix . strtolower($link_text), $link_prefix . strtolower(trim(str_replace($search, $replace, $nextcity))), $content);
                $content = str_replace($link_text . '</a>', $nextcity . '</a>', $content);
            } else {
                $content = str_replace($link_prefix . strtolower($link_text), $link_prefix . strtolower(trim(str_replace($search, $replace, $nextcity))), $content);
                $content = str_replace($link_text . '</a>', $nextcity . '</a>', $content);
            }
            $current_post = wp_insert_post([
                'post_title' => $title,
                'post_content' => $content,
                'post_type' => 'page',
                'post_status' => 'publish'
            ]);
            update_post_meta($current_post, '_yoast_wpseo_title', $seotitle);
            update_post_meta($current_post, '_yoast_wpseo_metadesc', $seodescription);
            update_post_meta($current_post, '_yoast_wpseo_focuskw', $seo_keyword_prefix . ' ' . ucfirst($city));
            update_post_meta($current_post, '_yoast_wpseo_focuskw_text_input', $seo_keyword_prefix . ' ' . ucfirst($city));
            update_post_meta($current_post, '_baekerIT_page_generator', true);
            $counter++;

        } else {
            $page = get_page_by_title($title);
            if($title != $page->post_title or
                $content != $page->post_content or
                get_post_meta($page->ID, 'yoast_wpseo_title', true) != $seotitle or
            get_post_meta($page->ID, 'yoast_wpseo_metadesc', true) != $seodescription or
            get_post_meta($page->ID, 'yoast_wpseo_focuskw', true) != $seo_keyword_prefix . ' '. ucfirst($city) or
                get_post_meta($page->ID, 'yoast_wpseo_focuskw_text_input', true) != $seo_keyword_prefix . ' '. ucfirst($city)) {
                wp_update_post([
                    'id' => $page->ID,
                    'post_title' => $title,
                    'post_content' => $content
                ]);
                update_post_meta($page->ID, '_yoast_wpseo_title', $seotitle);
                update_post_meta($page->ID, '_yoast_wpseo_metadesc', $seodescription);
                update_post_meta($page->ID, '_yoast_wpseo_focuskw', $seo_keyword_prefix . ' ' . ucfirst($city));
                update_post_meta($page->ID, '_yoast_wpseo_focuskw_text_input', $seo_keyword_prefix . ' ' . ucfirst($city));
                update_post_meta($page->ID, '_baekerIT_page_generator', true);
            }
        }
    wp_die();
}