<?php

/**
 * Class TRP_Url_Converter
 *
 * Manages urls of translated pages.
 */
class TRP_Url_Converter {

    protected $absolute_home;
    protected $settings;

    /**
     * TRP_Url_Converter constructor.
     *
     * @param array $settings       Settings option.
     */
    public function __construct( $settings ){
        $this->settings = $settings;
    }

    /**
     * Redirects to default page for default language.
     *
     * Only if settings option add-subdirectory-to-default-language is set to no.
     *
     * Hooked to template redirect.
     */
    public function redirect_to_default_language() {
        global $TRP_LANGUAGE;
        if ( isset( $this->settings['add-subdirectory-to-default-language'] ) && $this->settings['add-subdirectory-to-default-language'] == 'no' && $TRP_LANGUAGE == $this->settings['default-language'] ) {
            return;
        }
        $lang_from_url = $this->get_lang_from_url_string( $this->cur_page_url() );

        if ( $lang_from_url == null ) {
            header( 'Location: ' . $this->get_url_for_language( $this->settings['default-language'] ) );
            exit;
        }
    }

    /**
     * Add language code as a subdirectory after home url.
     *
     * Hooked to home_url.
     *
     * @param string $url               Given Url.
     * @param string $path              Given path.
     * @param string $orig_scheme       Scheme.
     * @param int $blog_id              Blog id.
     * @return string
     */
    public function add_language_to_home_url( $url, $path, $orig_scheme, $blog_id ){
        global $TRP_LANGUAGE;
        if ( isset( $this->settings['add-subdirectory-to-default-language'] ) && $this->settings['add-subdirectory-to-default-language'] == 'no' && $TRP_LANGUAGE == $this->settings['default-language'] ) {
            return $url;
        }

        if( is_customize_preview() || is_admin() )
            return $url;


        $url_slug = $this->get_url_slug( $TRP_LANGUAGE );
        $abs_home = $this->get_abs_home();
        $new_url = trailingslashit( $abs_home ) . $url_slug;
        if ( ! empty( $path ) ){
            $new_url .= '/' . ltrim( $path, '/' );
        }

        return apply_filters( 'trp_home_url', $new_url, $abs_home, $TRP_LANGUAGE, $path );
    }

    /**
     * Add Hreflang entries for each language to Header.
     */
    public function add_hreflang_to_head(){
        $languages = $this->settings['publish-languages'];
        if ( isset( $_GET['trp-edit-translation'] ) && $_GET['trp-edit-translation'] == 'preview' ) {
            $languages = $this->settings['translation-languages'];
        }

        foreach ( $languages as $language ) {
            echo '<link rel="alternate" hreflang="' . $language . '" href="' . $this->get_url_for_language( $language ) . '"/>';
        }
    }

    /**
     * Function that changes the lang attribute in the html tag to the current language.
     *
     * @param string $output
     * @return string
     */
    public function change_lang_attr_in_html_tag( $output ){
        global $TRP_LANGUAGE;
        $lang = get_bloginfo('language');
        if ( $lang && !empty($TRP_LANGUAGE) && $this->settings["default-language"] != $TRP_LANGUAGE ) {
            $output = str_replace( 'lang="'. $lang .'"', 'lang="'. str_replace('_', '-', $TRP_LANGUAGE ) .'"', $output );
        }

        return $output;
    }

    /**
     * Returns language-specific url for given language.
     *
     * Defaults to current Url and current language.
     *
     * @param string $language      Language code.
     * @param string $url           Url to encode.
     * @return string
     */
    public function get_url_for_language ( $language = null, $url = null, $trp_link_is_processed = '#TRPLINKPROCESSED') {
        global $post, $TRP_LANGUAGE;

        // we're appending $trp_link_is_processed string to the end of each processed link so we don't process them again in the render class.
        // we're stripping this from each url in the render class
        // $trp_link_is_processed is part of the function params so we can pass an empty link in case we need get_url_for_language() in other places that don't go through the render.
        // since the render doesn't work on the default language, we're striping the processed tag.
        if( $TRP_LANGUAGE == $this->settings['default-language'] ){
            $trp_link_is_processed = '';
        }

        $trp_language_copy = $TRP_LANGUAGE;

        if ( empty( $language ) ) {
            $language = $TRP_LANGUAGE;
        }


        // if we have the homepage, we replace it with the filtered homepage that contains the language url.
        if( trailingslashit( $this->cur_page_url() ) == trailingslashit($this->get_abs_home()) ){
            $TRP_LANGUAGE = $language;
            $new_url = home_url();
            $TRP_LANGUAGE = $trp_language_copy;
            return $new_url . $trp_link_is_processed;
        }


        if ( empty( $url ) && is_object( $post ) && ( $post->ID != '0' ) && is_singular() ) {
            // if we have a $post we need to run the language switcher through get_permalink so we apply the correct slug that can be different.
            $TRP_LANGUAGE = $language;
            $new_url = get_permalink( $post->ID );
            $TRP_LANGUAGE = $trp_language_copy;
        }else{
            if( empty( $url ) ){
                $url = $this->cur_page_url();
            }
            // If no $post is set we simply replace the current language root with the new language root.
	        // we can't assume the URL's have / at the end so we need to untrailingslashit both $abs_home and $new_language_root
	        $abs_home = trailingslashit( $this->get_abs_home() );

            $current_lang_root =  untrailingslashit($abs_home . $this->get_url_slug( $TRP_LANGUAGE ));
            $new_language_root =  untrailingslashit($abs_home . $this->get_url_slug( $language ) );

            if( $this->get_lang_from_url_string($url) === null ){
                // this is for forcing the custom url's. We expect them to not have a language in them.
	            $new_url = str_replace(untrailingslashit($abs_home), $new_language_root, $url);
			} else {
                $new_url = str_replace($current_lang_root, $new_language_root, $url);
	        }
        }

        
        /* fix links for woocommerce on language switcher for product categories and product tags */
        if( class_exists( 'WooCommerce' ) ){
            if ( is_product_category() ) {
                $current_cat_slug = trp_x( 'product-category', 'slug', 'woocommerce', $TRP_LANGUAGE );
                $translated_cat_slug = trp_x( 'product-category', 'slug', 'woocommerce', $language );
                $new_url = str_replace( '/'.$current_cat_slug.'/', '/'.$translated_cat_slug.'/', $new_url );
            }elseif( is_product_tag() ){
                $current_tag_slug = trp_x( 'product-tag', 'slug', 'woocommerce', $TRP_LANGUAGE );
                $translated_tag_slug = trp_x( 'product-tag', 'slug', 'woocommerce', $language );
                $new_url = str_replace( '/'.$current_tag_slug.'/', '/'.$translated_tag_slug.'/', $new_url );
            }
        }

        if ( empty( $new_url ) ) {
            $new_url = $url;
        }

        return $new_url . $trp_link_is_processed ;
    }

    /**
     * Get language code slug to use in url.
     *
     * @param string $language_code         Full language code.
     * @param bool $accept_empty_return     Whether to take into account the add-subdirectory-to-default-language setting.
     * @return string                       Url slug.
     */
    public function get_url_slug( $language_code, $accept_empty_return = true ){
        $url_slug = $language_code;
        if( isset( $this->settings['url-slugs'][$language_code] ) ) {
            $url_slug = $this->settings['url-slugs'][$language_code];
        }

        if ( $accept_empty_return && isset( $this->settings['add-subdirectory-to-default-language'] ) && $this->settings['add-subdirectory-to-default-language'] == 'no' && $language_code == $this->settings['default-language'] ) {
            $url_slug = '';
        }

        return $url_slug;
    }

    /**
     * Return absolute home url as stored in database, unfiltered.
     *
     * @return string
     */
    public function get_abs_home() {
        global $wpdb;

        // returns the unfiltered home_url by directly retrieving it from wp_options.
        $this->absolute_home = $this->absolute_home
            ? $this->absolute_home
            : ( ! is_multisite() && defined( 'WP_HOME' )
                ? WP_HOME
                : ( is_multisite() && ! is_main_site()
                    ? ( preg_match( '/^(https)/', get_option( 'home' ) ) === 1 ? 'https://'
                        : 'http://' ) . $wpdb->get_var( "	SELECT CONCAT(b.domain, b.path)
									FROM {$wpdb->blogs} b
									WHERE blog_id = {$wpdb->blogid}
									LIMIT 1" )

                    : $wpdb->get_var( "	SELECT option_value
									FROM {$wpdb->options}
									WHERE option_name = 'home'
									LIMIT 1" ) )
            );

        return $this->absolute_home;
    }

    /**
     * Return the language code from the url.
     *
     * Uses current url if none given.
     *
     * @param string $url       Url.
     * @return string           Language code.
     */
    public function get_lang_from_url_string( $url = null ) {
        if ( ! $url ){
            $url = $this->cur_page_url();
        }

        // we remove http or https
        // if the user links to a http link but the abs_home_url is https, we're serving the https one so we don't brake cookies if he doesn't have proper redirects
        $lang = preg_replace( '#^(http|https)://#', '', $url );
        $abs_home = preg_replace( '#^(http|https)://#', '', $this->get_abs_home() );

        // we have removed the home path from our URL. We're adding a / in case it's the homepage of one of the languages
        // removing / from the front so it's easier for understanding explode()
        $lang = ltrim( trailingslashit( str_replace($abs_home, '', $lang)),'/' );

        // We now have to see if the first part of the string is actually a language slug
        $lang = explode('/', $lang);
        if( $lang == false ){
            return null;
        }
        // If we have a language in the URL, the first element of the array should be it.
        $lang = $lang[0];

        // the lang slug != actual lang. So we need to do array_search so we don't end up with en instead of en_US
        if( isset($this->settings['url-slugs']) && in_array($lang, $this->settings['url-slugs']) ){
            return array_search($lang, $this->settings['url-slugs']);
        } else {
            return null;
        }
    }

    /**
     * Return current page url.
     * Always using $this->get_abs_home(), instead of home_url() since that one is filtered by TP
     * @return string
     */
    public function cur_page_url() {

        $req_uri = $_SERVER['REQUEST_URI'];

        $home_path = trim( parse_url( $this->get_abs_home(), PHP_URL_PATH ), '/' );
        $home_path_regex = sprintf( '|^%s|i', preg_quote( $home_path, '|' ) );

        // Trim path info from the end and the leading home path from the front.
        $req_uri = ltrim($req_uri, '/');
        $req_uri = preg_replace( $home_path_regex, '', $req_uri );
        $req_uri = trim($this->get_abs_home(), '/') . '/' . ltrim( $req_uri, '/' );


        if ( function_exists('apply_filters') ) $pageURL = apply_filters('trp_curpageurl', $req_uri);

        return $req_uri;
    }

    /**
     * we need to modify the permalinks structure for woocommerce when we switch languages
     * when woo registers post_types and taxonomies in the rewrite parameter of the function they change the slugs of the items (they are localized with _x )
     * we can't flush the permalinks on every page load so we filter the rewrite_rules option
     */
    public function woocommerce_filter_permalinks_on_other_languages( $rewrite_rules ){
        if( class_exists( 'WooCommerce' ) ){
            global $TRP_LANGUAGE;
            if( $TRP_LANGUAGE != $this->settings['default-language'] ){
                global $default_language_wc_permalink_structure; //we use a global because apparently you can't do switch to locale and restore multiple times. I should keep an eye on this
                /* get rewrite rules from original language */
                if( empty($default_language_wc_permalink_structure) ) {
                    $default_language_wc_permalink_structure = array();
                    $default_language_wc_permalink_structure['product_rewrite_slug'] = trp_x( 'product', 'slug', 'woocommerce', $this->settings['default-language'] );
                    $default_language_wc_permalink_structure['category_rewrite_slug'] = trp_x( 'product-category', 'slug', 'woocommerce', $this->settings['default-language'] );
                    $default_language_wc_permalink_structure['tag_rewrite_slug'] = trp_x( 'product-tag', 'slug', 'woocommerce', $this->settings['default-language'] );
                }

                if( function_exists( 'wc_get_permalink_structure' ) ){
                    $current_language_permalink_structure = wc_get_permalink_structure();
                }
                else{
                    $current_language_permalink_structure = array();
                    $current_language_permalink_structure['product_rewrite_slug'] = trp_x( 'product', 'slug', 'woocommerce', $TRP_LANGUAGE );
                    $current_language_permalink_structure['category_rewrite_slug'] = trp_x( 'product-category', 'slug', 'woocommerce', $TRP_LANGUAGE );
                    $current_language_permalink_structure['tag_rewrite_slug'] = trp_x( 'product-tag', 'slug', 'woocommerce', $TRP_LANGUAGE );
                }

                $new_rewrite_rules = array();

                $search = array( '/^'.$default_language_wc_permalink_structure['product_rewrite_slug'].'\//', '/^'.$default_language_wc_permalink_structure['category_rewrite_slug'].'\//', '/^'.$default_language_wc_permalink_structure['tag_rewrite_slug'].'\//' );
                $replace = array( $current_language_permalink_structure['product_rewrite_slug'].'/', $current_language_permalink_structure['category_rewrite_slug'].'/', $current_language_permalink_structure['tag_rewrite_slug'].'/' );

                if( !empty( $rewrite_rules ) && is_array($rewrite_rules) ) {
                    foreach ($rewrite_rules as $rewrite_key => $rewrite_values) {
                        $new_rewrite_rules[preg_replace($search, $replace, $rewrite_key)] = preg_replace($search, $replace, $rewrite_values);
                    }
                }

            }
        }

        if( !empty($new_rewrite_rules) ) {
            return $new_rewrite_rules;
        }
        else
            return $rewrite_rules;
    }
    

}