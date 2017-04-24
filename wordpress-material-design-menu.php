<?php
/*
Plugin Name: WordPress Material Design Menu
Description: Displays Menu - Tablet / Mobile - Bootstrap based. <hr><strong>IMPORTANT</strong> Libraries: Velocity(animations), jQuery(js handler), WMDM(plugin script) | Styles: Bootstrap(Grid), Icofont(Material Icons), WMDM(plugin menu styles)
Version: 0.9
*/

class Custom_Walker_Nav_Menu extends Walker_Nav_Menu {

    function start_lvl(&$output, $depth) {
        $indent = str_repeat("\t", $depth);
        $output .= "\n$indent<ul class=\"sub-menu\">\n";

        // Change sub-menu to dropdown menu
//        $output .= "\n$indent<ul class=\"dropdown-menu\">\n";
    }

    function start_el ( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
        // Most of this code is copied from original Walker_Nav_Menu
        global $wp_query, $wpdb;
        $indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

        $class_names = $value = '';

        $classes = empty( $item->classes ) ? array() : (array) $item->classes;
        $classes[] = 'menu-item-' . $item->ID;

        $class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args ) );
        $class_names = ' class="' . esc_attr( $class_names ) . '"';

        $id = apply_filters( 'nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args );
        $id = strlen( $id ) ? ' id="' . esc_attr( $id ) . '"' : '';

        $has_children = $wpdb->get_var("SELECT COUNT(meta_id)
                            FROM wp_postmeta
                            WHERE meta_key='_menu_item_menu_item_parent'
                            AND meta_value='".$item->ID."'");

        $output .= $indent . '<li' . $id . $value . $class_names .'>';

        $attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
        $attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
        $attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
        $attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';

        // Check if menu item is in main menu
//        if ( $depth == 0 && $has_children > 0  ) {
//            // These lines adds your custom class and attribute
//            $attributes .= ' class="dropdown-toggle"';
//            $attributes .= ' data-toggle="dropdown"';
//        }

        $item_output = $args->before;
        $item_output .= '<a'. $attributes .'>';
        $item_output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;

        $item_output .= '</a>';
        $item_output .= $args->after;
        // Add the caret if menu level is 0
        if ( $has_children > 0  ) {
            $item_output .= '<span class="toggler"></span>';
        }



        $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
    }

}

class WMDM
{
    function __construct() {

        // Adding Plugin Menu
        add_action( 'admin_menu', array( &$this, 'wmdm_menu' ) );

        // Load custom assets on admin page.
        add_action( 'admin_enqueue_scripts', array( &$this, 'wmdm_assets' ) );

        // Load custom assets on front-end layer
        add_action( 'wp_head', array( &$this, 'enqueue_libs' ) );

        // Register Settings
        add_action( 'admin_init', array( &$this, 'wmdm_settings' ) );

        // Add Two Menus - Mobile & Tablet
        add_action( 'init', array( &$this, 'register_material_menus' ));

        //  Print front-end layer
        add_action( 'wp_head', array( &$this, 'print_on_frontend' ) );

    } // end constructor


    /*--------------------------------------------*
     * Admin Menu
     *--------------------------------------------*/

    function wmdm_menu()
    {
        $page_title = __('Material Design Menu', 'wmdm');
        $menu_title = __('Material Design Menu', 'wmdm');
        $capability = 'manage_options';
        $menu_slug = 'wmdm-options';
        $function = array(&$this, 'wmdm_menu_contents');
        add_options_page($page_title, $menu_title, $capability, $menu_slug, $function);

    }

    /*--------------------------------------------*
     * Load Necessary JavaScript Files
     *--------------------------------------------*/

    function wmdm_assets() {
        $debug = false;

        if (isset($_GET['page']) && $_GET['page'] == 'wmdm-options') {

            wp_enqueue_style( 'thickbox' ); // Stylesheet used by Thickbox
            wp_enqueue_script( 'thickbox' );
            wp_enqueue_script( 'media-upload' );
            wp_enqueue_style( 'wp-color-picker' );
            wp_enqueue_media();
            wp_register_script('wmdm_admin', plugins_url( '/js/wmdm_admin.js' , __FILE__ ), array( 'thickbox', 'media-upload', 'wp-color-picker' ));
            wp_enqueue_script('wmdm_admin');

            wp_enqueue_style( 'plugin-admin', plugin_dir_url(__FILE__).'styles/admin-styles.css' );

            if($_GET['settings-updated'] === 'true' && $debug == false){
                $this->wmdm_recompile_less();
            }
        }

    }


    function enqueue_libs(){
        $options = get_option( 'wmdm_settings' );
        add_filter('show_admin_bar', '__return_false');

        wp_enqueue_style( 'bootstrap-base', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css' );
//        wp_enqueue_style( 'icofont-base', plugin_dir_url(__FILE__).'style.css' );
        wp_enqueue_style( 'plugin-base', plugin_dir_url(__FILE__).'styles/styles.css' );
//        wp_enqueue_script( 'jQuery', 'http://code.jquery.com/jquery-2.1.4.min.js', array(), '1.0.0', true );
//        wp_enqueue_script( 'velocity', '//cdn.jsdelivr.net/velocity/1.2.3/velocity.min.js', array(), '1.0.0', true );
        if($options['gestures']){
            wp_enqueue_script( 'jQuery-mobile', '//code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js', array(), '1.0.0', true );
        }
        wp_enqueue_script( 'plugin-base', plugin_dir_url(__FILE__).'js/wmdm.js', array(), '1.0.0', true );
    }


    /*--------------------------------------------*
     * Include color picker
     *--------------------------------------------*/

    function wmdm_recompile_less() {

            $options = get_option( 'wmdm_settings' );
            $color = $options["main_color"];
            $path = plugin_dir_path( __FILE__ );
            require "styles/lessc.inc.php";
            $less = new lessc;
            $less->setVariables(array(
                "main_color" => $color
            ));
            $css =  $less->compileFile($path."styles/styles.less");
            file_put_contents($path."styles/styles.css", $css);
    }

    /*--------------------------------------------*
     * Prepare array of icons
     *--------------------------------------------*/

    function returnIconClasses(){


       $string_1 = 'icon-3d-rotation,icon-accessibility,icon-account-balance,icon-account-balance-wallet,icon-account-box,icon-account-child,icon-account-circle,icon-add-shopping-cart,icon-alarm,icon-alarm-add,icon-alarm-off,icon-alarm-on,icon-android,icon-announcement,icon-aspect-ratio,icon-assessment,icon-assignment,icon-assignment-ind,icon-assignment-late,icon-assignment-return,icon-assignment-returned,icon-assignment-turned-in,icon-autorenew,icon-backup,icon-book,icon-bookmark,icon-bookmark-outline,icon-bug-report,icon-cached,icon-class,icon-credit-card,icon-dashboard,icon-delete,icon-description,icon-dns,icon-done,icon-done-all,icon-event,icon-exit-to-app,icon-explore,icon-extension,icon-face-unlock,icon-favorite,icon-favorite-outline,icon-find-in-page,icon-find-replace,icon-flip-to-back,icon-flip-to-front,icon-get-app,icon-grade,icon-group-work,icon-help,icon-highlight-remove,icon-history,icon-home,icon-https,icon-info,icon-info-outline,icon-input,icon-invert-colors,icon-label,icon-label-outline,icon-language,icon-launch,icon-list,icon-lock,icon-lock-open,icon-lock-outline,icon-loyalty,icon-markunread-mailbox,icon-note-add,icon-open-in-browser,icon-open-in-new,icon-open-with,icon-pageview,icon-payment,icon-perm-camera-m,icon-perm-contact-cal,icon-perm-data-setting,icon-perm-device-info,icon-perm-identity,icon-perm-media,icon-perm-phone-msg,icon-perm-scan-wifi,icon-picture-in-picture,icon-polymer,icon-print,icon-query-builder,icon-question-answer,icon-receipt,icon-redeem,icon-reorder,icon-report-problem,icon-restore,icon-room,icon-schedule,icon-search,icon-settings,icon-settings-applications,icon-settings-backup-restore,icon-settings-bluetooth,icon-settings-cell,icon-settings-display,icon-settings-ethernet,icon-settings-input-antenna,icon-settings-input-component,icon-settings-input-composite,icon-settings-input-hdmi,icon-settings-input-svideo,icon-settings-overscan,icon-settings-phone,icon-settings-power,icon-settings-remote,icon-settings-voice,icon-shop,icon-shop-two,icon-shopping-basket,icon-shopping-cart,icon-speaker-notes,icon-spellcheck,icon-star-rate,icon-stars,icon-store,icon-subject,icon-supervisor-account,icon-swap-horiz,icon-swap-vert,icon-swap-vert-circle,icon-system-update-tv,icon-tab,icon-tab-unselected,icon-theaters,icon-thumb-down,icon-thumb-up,icon-thumbs-up-down,icon-toc,icon-today,icon-track-changes,icon-translate,icon-trending-down,icon-trending-neutral,icon-trending-up,icon-turned-in,icon-turned-in-not,icon-verified-user,icon-view-agenda,icon-view-array,icon-view-carousel,icon-view-column,icon-view-day,icon-view-headline,icon-view-list,icon-view-module,icon-view-quilt,icon-view-stream,icon-view-week,icon-visibility,icon-visibility-off,icon-wallet-giftcard,icon-wallet-membership,icon-wallet-travel,icon-work,icon-error,icon-warning,icon-album,icon-av-timer,icon-closed-caption,icon-equalizer,icon-explicit,icon-fast-forward,icon-fast-rewind,icon-games,icon-hearing,icon-high-quality,icon-loop,icon-mic,icon-mnone,icon-moff,icon-movie,icon-my-library-add,icon-my-library-books,icon-my-library-mus,icon-new-releases,icon-not-interested,icon-pause,icon-pause-circle-fill,icon-pause-circle-outline,icon-play-arrow,icon-play-circle-fill,icon-play-circle-outline,icon-play-shopping-bag,icon-playlist-add,icon-queue,icon-queue-mus,icon-radio,icon-recent-actors,icon-repeat,icon-repeat-one,icon-replay,icon-shuffle,icon-skip-next,icon-skip-previous,icon-snooze,icon-stop,icon-subtitles,icon-surround-sound,icon-video-collection,icon-videocam,icon-videocam-off,icon-volume-down,icon-volume-mute,icon-volume-off,icon-volume-up,icon-web,icon-business,icon-call,icon-call-end,icon-call-made,icon-call-merge,icon-call-missed,icon-call-received,icon-call-split,icon-chat,icon-clear-all,icon-comment,icon-contacts,icon-dialer-sip,icon-dialpad,icon-dnd-on,icon-email,icon-forum,icon-import-export,icon-invert-colors-off,icon-invert-colors-on,icon-live-help,icon-location-off,icon-location-on,icon-message,icon-messenger,icon-no-sim,icon-phone,icon-portable-wifi-off,icon-quick-contacts-dialer,icon-quick-contacts-mail,icon-ring-volume,icon-stay-current-landscape,icon-stay-current-portrait,icon-stay-primary-landscape,icon-stay-primary-portrait,icon-swap-calls,icon-textsms,icon-voicemail,icon-vpn-key,icon-add,icon-add-box,icon-add-circle,icon-add-circle-outline,icon-archive,icon-backspace,icon-block,icon-clear,icon-content-copy,icon-content-cut,icon-content-paste,icon-create,icon-drafts,icon-filter-list,icon-flag,icon-forward,icon-gesture,icon-inbox,icon-link,icon-mail,icon-markunread,icon-redo,icon-remove,icon-remove-circle,icon-remove-circle-outline,icon-reply,icon-reply-all,icon-report,icon-save,icon-select-all,icon-send,icon-sort,icon-text-format,icon-undo,icon-access-alarm,icon-access-alarms,icon-access-time,icon-add-alarm,icon-airplanemode-off,icon-airplanemode-on,icon-battery-20,icon-battery-30,icon-battery-50,icon-battery-60,icon-battery-80,icon-battery-90,icon-battery-alert,icon-battery-charging-20,icon-battery-charging-30,icon-battery-charging-50,icon-battery-charging-60,icon-battery-charging-80,icon-battery-charging-90,icon-battery-charging-full,icon-battery-full,icon-battery-std,icon-battery-unknown,icon-bluetooth,icon-bluetooth-connected,icon-bluetooth-disabled,icon-bluetooth-searching,icon-brightness-auto,icon-brightness-high,icon-brightness-low,icon-brightness-medium,icon-data-usage,icon-developer-mode,icon-devices,icon-dvr,icon-gps-fixed,icon-gps-not-fixed,icon-gps-off,icon-location-disabled,icon-location-searching,icon-multitrack-audio,icon-network-cell,icon-network-wifi,icon-nfc,icon-now-wallpaper,icon-now-widgets,icon-screen-lock-landscape,icon-screen-lock-portrait,icon-screen-lock-rotation,icon-screen-rotation,icon-sd-storage,icon-settings-system-daydream,icon-signal-cellular-0-bar,icon-signal-cellular-1-bar,icon-signal-cellular-2-bar,icon-signal-cellular-3-bar,icon-signal-cellular-4-bar,icon-signal-cellular-connected-no-internet-0-bar,icon-signal-cellular-connected-no-internet-1-bar,icon-signal-cellular-connected-no-internet-2-bar,icon-signal-cellular-connected-no-internet-3-bar,icon-signal-cellular-connected-no-internet-4-bar,icon-signal-cellular-no-sim,icon-signal-cellular-null,icon-signal-cellular-off,icon-signal-wifi-0-bar,icon-signal-wifi-1-bar,icon-signal-wifi-2-bar,icon-signal-wifi-3-bar,icon-signal-wifi-4-bar,icon-signal-wifi-off,icon-signal-wifi-statusbar-1-bar,icon-signal-wifi-statusbar-2-bar,icon-signal-wifi-statusbar-3-bar,icon-signal-wifi-statusbar-4-bar,icon-signal-wifi-statusbar-connected-no-internet-1,icon-signal-wifi-statusbar-connected-no-internet-2,icon-signal-wifi-statusbar-connected-no-internet-3,icon-signal-wifi-statusbar-connected-no-internet-4,icon-signal-wifi-statusbar-connected-no-internet,icon-signal-wifi-statusbar-not-connected,icon-signal-wifi-statusbar-null,icon-storage,icon-usb,icon-wifi-lock,icon-wifi-tethering,icon-attach-file,icon-attach-money,icon-border-all,icon-border-bottom,icon-border-clear,icon-border-color,icon-border-horizontal,icon-border-inner,icon-border-left,icon-border-outer,icon-border-right,icon-border-style,icon-border-top,icon-border-vertical,icon-format-align-center,icon-format-align-justify,icon-format-align-left,icon-format-align-right,icon-format-bold,icon-format-clear,icon-format-color-fill,icon-format-color-reset,icon-format-color-text,icon-format-indent-decrease,icon-format-indent-increase,icon-format-ital,icon-format-line-spacing,icon-format-list-bulleted,icon-format-list-numbered,icon-format-paint,icon-format-quote,icon-format-size,icon-format-strikethrough,icon-format-textdirection-l-to-r,icon-format-textdirection-r-to-l,icon-format-underline,icon-functions,icon-insert-chart,icon-insert-comment,icon-insert-drive-file,icon-insert-emoticon,icon-insert-invitation,icon-insert-link,icon-insert-photo,icon-merge-type,icon-mode-comment,icon-mode-edit,icon-publish,icon-vertical-align-bottom,icon-vertical-align-center,icon-vertical-align-top,icon-wrap-text,icon-attachment,icon-cloud,icon-cloud-circle,icon-cloud-done,icon-cloud-download,icon-cloud-off,icon-cloud-queue,icon-cloud-upload,icon-file-download,icon-file-upload,icon-folder,icon-folder-open,icon-folder-shared,icon-cast,icon-cast-connected,icon-computer,icon-desktop-mac,icon-desktop-windows,icon-dock,icon-gamepad,icon-headset,icon-headset-m,icon-keyboard,icon-keyboard-alt,icon-keyboard-arrow-down,icon-keyboard-arrow-left,icon-keyboard-arrow-right,icon-keyboard-arrow-up,icon-keyboard-backspace,icon-keyboard-capslock,icon-keyboard-control,icon-keyboard-hide,icon-keyboard-return,icon-keyboard-tab,icon-keyboard-voice,icon-laptop,icon-laptop-chromebook,icon-laptop-mac,icon-laptop-windows,icon-memory,icon-mouse,icon-phone-android,icon-phone-iphone,icon-phonelink,icon-phonelink-off,icon-security,icon-sim-card,icon-smartphone,icon-speaker,icon-tablet,icon-tablet-android,icon-tablet-mac,icon-tv,icon-watch,icon-add-to-photos,icon-adjust,icon-assistant-photo,icon-audiotrack,icon-blur-circular,icon-blur-linear,icon-blur-off,icon-blur-on,icon-brightness-1,icon-brightness-2,icon-brightness-3,icon-brightness-4,icon-brightness-5,icon-brightness-6,icon-brightness-7,icon-brush,icon-camera,icon-camera-alt,icon-camera-front,icon-camera-rear,icon-camera-roll,icon-center-focus-strong,icon-center-focus-weak,icon-collections,icon-color-lens,icon-colorize,icon-compare,icon-control-point,icon-control-point-duplicate,icon-crop-3-2,icon-crop-5-4,icon-crop-7-5,icon-crop-16-9,icon-crop,icon-crop-din,icon-crop-free,icon-crop-landscape,icon-crop-original,icon-crop-portrait,icon-crop-square,icon-dehaze,icon-details,icon-edit,icon-exposure,icon-exposure-minus-1,icon-exposure-minus-2,icon-exposure-plus-1,icon-exposure-plus-2,icon-exposure-zero,icon-filter-1,icon-filter-2,icon-filter-3,icon-filter-4,icon-filter-5,icon-filter-6,icon-filter-7,icon-filter-8,icon-filter-9,icon-filter-9-plus,icon-filter,icon-filter-b-and-w,icon-filter-center-focus,icon-filter-drama,icon-filter-frames,icon-filter-hdr,icon-filter-none,icon-filter-tilt-shift,icon-filter-vintage,icon-flare,icon-flash-auto,icon-flash-off,icon-flash-on,icon-flip,icon-gradient,icon-grain,icon-grid-off,icon-grid-on,icon-hdr-off,icon-hdr-on,icon-hdr-strong,icon-hdr-weak,icon-healing,icon-image,icon-image-aspect-ratio,icon-iso,icon-landscape,icon-leak-add,icon-leak-remove,icon-lens,icon-looks-3,icon-looks-4,icon-looks-5,icon-looks-6,icon-looks,icon-looks-one,icon-looks-two,icon-loupe,icon-movie-creation,icon-nature,icon-nature-people,icon-navigate-before,icon-navigate-next,icon-palette,icon-panorama,icon-panorama-fisheye,icon-panorama-horizontal,icon-panorama-vertical,icon-panorama-wide-angle,icon-photo,icon-photo-album,icon-photo-camera,icon-photo-library,icon-portrait,icon-remove-red-eye,icon-rotate-left,icon-rotate-right,icon-slideshow,icon-straighten,icon-style,icon-switch-camera,icon-switch-video,icon-tag-faces,icon-texture,icon-timelapse,icon-timer-3,icon-timer-10,icon-timer,icon-timer-auto,icon-timer-off,icon-tonality,icon-transform,icon-tune,icon-wb-auto,icon-wb-cloudy,icon-wb-incandescent,icon-wb-irradescent,icon-wb-sunny,icon-beenhere,icon-directions,icon-directions-bike,icon-directions-bus,icon-directions-car,icon-directions-ferry,icon-directions-subway,icon-directions-train,icon-directions-transit,icon-directions-walk,icon-flight,icon-hotel,icon-layers,icon-layers-clear,icon-local-airport,icon-local-atm,icon-local-attraction,icon-local-bar,icon-local-cafe,icon-local-car-wash,icon-local-convenience-store,icon-local-drink,icon-local-florist,icon-local-gas-station,icon-local-grocery-store,icon-local-hospital,icon-local-hotel,icon-local-laundry-service,icon-local-library,icon-local-mall,icon-local-movies,icon-local-offer,icon-local-parking,icon-local-pharmacy,icon-local-phone,icon-local-pizza,icon-local-play,icon-local-post-office,icon-local-print-shop,icon-local-restaurant,icon-local-see,icon-local-shipping,icon-local-taxi,icon-location-history,icon-map,icon-my-location,icon-navigation,icon-pin-drop,icon-place,icon-rate-review,icon-restaurant-menu,icon-satellite,icon-store-mall-directory,icon-terrain,icon-traff,icon-apps,icon-arrow-back,icon-arrow-drop-down,icon-arrow-drop-down-circle,icon-arrow-drop-up,icon-arrow-forward,icon-cancel,icon-check,icon-chevron-left,icon-chevron-right,icon-close,icon-expand-less,icon-expand-more,icon-fullscreen,icon-fullscreen-exit,icon-menu,icon-more-horiz,icon-more-vert,icon-refresh,icon-unfold-less,icon-unfold-more,icon-adb,icon-bluetooth-audio,icon-disc-full,icon-dnd-forwardslash,icon-do-not-disturb,icon-drive-eta,icon-event-available,icon-event-busy,icon-event-note,icon-folder-special,icon-mms,icon-more,icon-network-locked,icon-phone-bluetooth-speaker,icon-phone-forwarded,icon-phone-in-talk,icon-phone-locked,icon-phone-missed,icon-phone-paused,icon-play-download,icon-play-install,icon-sd-card,icon-sim-card-alert,icon-sms,icon-sms-failed,icon-sync,icon-sync-disabled,icon-sync-problem,icon-system-update,icon-tap-and-play,icon-time-to-leave,icon-vibration,icon-voice-chat,icon-vpn-lock,icon-cake,icon-domain,icon-group,icon-group-add,icon-location-city,icon-mood,icon-notifications,icon-notifications-none,icon-notifications-off,icon-notifications-on,icon-notifications-paused,icon-pages,icon-party-mode,icon-people,icon-people-outline,icon-person,icon-person-add,icon-person-outline,icon-plus-one,icon-poll,icon-publ,icon-school,icon-share,icon-whatshot,icon-check-box,icon-check-box-outline-blank,icon-radio-button-off,icon-radio-button-on,icon-star,icon-star-half,icon-star-outline';
       $string_2 = 'e600,e601,e602,e603,e604,e605,e606,e607,e608,e609,e60a,e60b,e60c,e60d,e60e,e60f,e610,e611,e612,e613,e614,e615,e616,e617,e618,e619,e61a,e61b,e61c,e61d,e61e,e61f,e620,e621,e622,e623,e624,e625,e626,e627,e628,e629,e62a,e62b,e62c,e62d,e62e,e62f,e630,e631,e632,e633,e634,e635,e636,e637,e638,e639,e63a,e63b,e63c,e63d,e63e,e63f,e640,e641,e642,e643,e644,e645,e646,e647,e648,e649,e64a,e64b,e64c,e64d,e64e,e64f,e650,e651,e652,e653,e654,e655,e656,e657,e658,e659,e65a,e65b,e65c,e65d,e65e,e65f,e660,e661,e662,e663,e664,e665,e666,e667,e668,e669,e66a,e66b,e66c,e66d,e66e,e66f,e670,e671,e672,e673,e674,e675,e676,e677,e678,e679,e67a,e67b,e67c,e67d,e67e,e67f,e680,e681,e682,e683,e684,e685,e686,e687,e688,e689,e68a,e68b,e68c,e68d,e68e,e68f,e690,e691,e692,e693,e694,e695,e696,e697,e698,e699,e69a,e69b,e69c,e69d,e69e,e69f,e6a0,e6a1,e6a2,e6a3,e6a4,e6a5,e6a6,e6a7,e6a8,e6a9,e6aa,e6ab,e6ac,e6ad,e6ae,e6af,e6b0,e6b1,e6b2,e6b3,e6b4,e6b5,e6b6,e6b7,e6b8,e6b9,e6ba,e6bb,e6bc,e6bd,e6be,e6bf,e6c0,e6c1,e6c2,e6c3,e6c4,e6c5,e6c6,e6c7,e6c8,e6c9,e6ca,e6cb,e6cc,e6cd,e6ce,e6cf,e6d0,e6d1,e6d2,e6d3,e6d4,e6d5,e6d6,e6d7,e6d8,e6d9,e6da,e6db,e6dc,e6dd,e6de,e6df,e6e0,e6e1,e6e2,e6e3,e6e4,e6e5,e6e6,e6e7,e6e8,e6e9,e6ea,e6eb,e6ec,e6ed,e6ee,e6ef,e6f0,e6f1,e6f2,e6f3,e6f4,e6f5,e6f6,e6f7,e6f8,e6f9,e6fa,e6fb,e6fc,e6fd,e6fe,e6ff,e700,e701,e702,e703,e704,e705,e706,e707,e708,e709,e70a,e70b,e70c,e70d,e70e,e70f,e710,e711,e712,e713,e714,e715,e716,e717,e718,e719,e71a,e71b,e71c,e71d,e71e,e71f,e720,e721,e722,e723,e724,e725,e726,e727,e728,e729,e72a,e72b,e72c,e72d,e72e,e72f,e730,e731,e732,e733,e734,e735,e736,e737,e738,e739,e73a,e73b,e73c,e73d,e73e,e73f,e740,e741,e742,e743,e744,e745,e746,e747,e748,e749,e74a,e74b,e74c,e74d,e74e,e74f,e750,e751,e752,e753,e754,e755,e756,e757,e758,e759,e75a,e75b,e75c,e75d,e75e,e75f,e760,e761,e762,e763,e764,e765,e766,e767,e768,e769,e76a,e76b,e76c,e76d,e76e,e76f,e770,e771,e772,e773,e774,e775,e776,e777,e778,e779,e77a,e77b,e77c,e77d,e77e,e77f,e780,e781,e782,e783,e784,e785,e786,e787,e788,e789,e78a,e78b,e78c,e78d,e78e,e78f,e790,e791,e792,e793,e794,e795,e796,e797,e798,e799,e79a,e79b,e79c,e79d,e79e,e79f,e7a0,e7a1,e7a2,e7a3,e7a4,e7a5,e7a6,e7a7,e7a8,e7a9,e7aa,e7ab,e7ac,e7ad,e7ae,e7af,e7b0,e7b1,e7b2,e7b3,e7b4,e7b5,e7b6,e7b7,e7b8,e7b9,e7ba,e7bb,e7bc,e7bd,e7be,e7bf,e7c0,e7c1,e7c2,e7c3,e7c4,e7c5,e7c6,e7c7,e7c8,e7c9,e7ca,e7cb,e7cc,e7cd,e7ce,e7cf,e7d0,e7d1,e7d2,e7d3,e7d4,e7d5,e7d6,e7d7,e7d8,e7d9,e7da,e7db,e7dc,e7dd,e7de,e7df,e7e0,e7e1,e7e2,e7e3,e7e4,e7e5,e7e6,e7e7,e7e8,e7e9,e7ea,e7eb,e7ec,e7ed,e7ee,e7ef,e7f0,e7f1,e7f2,e7f3,e7f4,e7f5,e7f6,e7f7,e7f8,e7f9,e7fa,e7fb,e7fc,e7fd,e7fe,e7ff,e800,e801,e802,e803,e804,e805,e806,e807,e808,e809,e80a,e80b,e80c,e80d,e80e,e80f,e810,e811,e812,e813,e814,e815,e816,e817,e818,e819,e81a,e81b,e81c,e81d,e81e,e81f,e820,e821,e822,e823,e824,e825,e826,e827,e828,e829,e82a,e82b,e82c,e82d,e82e,e82f,e830,e831,e832,e833,e834,e835,e836,e837,e838,e839,e83a,e83b,e83c,e83d,e83e,e83f,e840,e841,e842,e843,e844,e845,e846,e847,e848,e849,e84a,e84b,e84c,e84d,e84e,e84f,e850,e851,e852,e853,e854,e855,e856,e857,e858,e859,e85a,e85b,e85c,e85d,e85e,e85f,e860,e861,e862,e863,e864,e865,e866,e867,e868,e869,e86a,e86b,e86c,e86d,e86e,e86f,e870,e871,e872,e873,e874,e875,e876,e877,e878,e879,e87a,e87b,e87c,e87d,e87e,e87f,e880,e881,e882,e883,e884,e885,e886,e887,e888,e889,e88a,e88b,e88c,e88d,e88e,e88f,e890,e891,e892,e893,e894,e895,e896,e897,e898,e899,e89a,e89b,e89c,e89d,e89e,e89f,e8a0,e8a1,e8a2,e8a3,e8a4,e8a5,e8a6,e8a7,e8a8,e8a9,e8aa,e8ab,e8ac,e8ad,e8ae,e8af,e8b0,e8b1,e8b2,e8b3,e8b4,e8b5,e8b6,e8b7,e8b8,e8b9,e8ba,e8bb,e8bc,e8bd,e8be,e8bf,e8c0,e8c1,e8c2,e8c3,e8c4,e8c5,e8c6,e8c7,e8c8,e8c9,e8ca,e8cb,e8cc,e8cd,e8ce,e8cf,e8d0,e8d1,e8d2,e8d3,e8d4,e8d5,e8d6,e8d7,e8d8,e8d9,e8da,e8db,e8dc,e8dd,e8de,e8df,e8e0,e8e1,e8e2,e8e3,e8e4,e8e5,e8e6,e8e7,e8e8,e8e9,e8ea,e8eb,e8ec,e8ed,e8ee,e8ef,e8f0,e8f1,e8f2,e8f3,e8f4';
       $exploded_1 = explode(',', $string_1);
       $exploded_2 = explode(',', $string_2);

        $combined = array_map(null,$exploded_1,$exploded_2);

        return $combined;
    }


    /*--------------------------------------------*
     * Print Box settings section
     *--------------------------------------------*/

    function iconSelect($data){
        $options = get_option( 'wmdm_settings' );

        foreach ($data as $option) :

        $select = $option.'_class';
        $label = $option.'_label';
        $link = $option.'_link';
        ?>
        <div class="q-action-box">
        <label for="wmdm_settings[<?php echo $select; ?>]">
            <select name='wmdm_settings[<?php echo $select; ?>]' class="material-icon-select" id='wmdm_settings[<?php echo $select; ?>]' value='<?php echo $options[$select]; ?>'>
                <?php foreach ($this->returnIconClasses() as $class) : ?>
                    <option value="<?php echo $class[0]; ?>" class="<?php echo $class[0]; ?>" <?php echo ($options[$select] == $class[0] ? 'selected' : false ); ?>>&#x<?php echo $class[1]; ?>; | <?php echo $class[0]; ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label for="wmdm_settings[<?php echo $label; ?>]"><input placeholder="Opis" type='text' id='wmdm_settings[<?php echo $label; ?>]' class='xregular-text' name='wmdm_settings[<?php echo $label; ?>]' value='<?php echo $options[$label]; ?>'/></label>
        <label for="wmdm_settings[<?php echo $link; ?>]"><input placeholder="Link" type='text' id='wmdm_settings[<?php echo $link; ?>]' class='xregular-text' name='wmdm_settings[<?php echo $link; ?>]' value='<?php echo $options[$link] ; ?>'/></label>
        </div>
    <?php endforeach; }


    /*--------------------------------------------*
     * Print radio-buttons based on prepared data (array)
     *--------------------------------------------*/

    function radioButtons($data){
        $options = get_option( 'wmdm_settings' );
        $i=1; foreach($data['options'] as $option) : ?>

            <label for="<?php echo $data['id'].$i; ?>"><input type="radio" id="<?php echo $data['id'].$i; ?>" name="wmdm_settings[<?php echo $data['setting']; ?>]" value="<?php echo $option[0]; ?>" <?php checked($option[0], $options[$data['setting']]); ?>/><?php echo $option[1]; ?></label>

        <?php $i++; endforeach; }


    /*--------------------------------------------*
    * Print checkboxes based on prepared data (array)
    *--------------------------------------------*/

    function checkboxes($data){
        $options = get_option('wmdm_settings');
        $i=1; foreach($data['options'] as $option) : ?>

            <label for="<?php echo $data['id'].$i; ?>">
                <input type="checkbox" id="<?php echo $data['id'].$i; ?>" name="wmdm_settings[<?php echo $data['setting'].$i; ?>]" value="<?php echo $option[2]; ?>" <?php checked($option[2], $options[$data['setting'].$i]); ?>/>
                <?php echo $option[1]; ?>
            </label><br/>

            <?php $i++; endforeach; }

    /*--------------------------------------------*
     * Settings & Settings Page
     *--------------------------------------------*/

    public function wmdm_menu_contents()
    {
        ?>
        <h2><?php _e('Material Design Menu', 'wmdm'); ?></h2>
        <div class="wrap">


            <form method="post" action="options.php">
                <?php //wp_nonce_field('update-options'); ?>
                <?php settings_fields('wmdm_settings'); ?>
                <?php do_settings_sections('wmdm_settings'); ?>
                <p class="submit">
                    <input name="Submit" type="submit" class="button-primary"
                           value="<?php _e('Zapisz zmiany', 'wmdm'); ?>"/>
                </p>

                <pre>
                <?php
                $options = get_option( 'wmdm_settings' );
                var_dump($options);
                ?>
                </pre>
            </form>
            <div class="helper">
                <?php echo '
                    <img src="'.plugins_url( '/sprite/class.png' , __FILE__ ).'" alt="" class="img-responsive"/>
                    <h3>Podgląd klas ikon:</h3> <a href="'.plugins_url( '/sprite/sprite.html' , __FILE__ ).'" target="_blank">zobacz podgląd</a>
                    <br><br><hr>
                    <img src="'.plugins_url( '/sprite/buttons.png' , __FILE__ ).'" alt="" class="img-responsive"/>
                    <h3>Pole <i>"link"</i></h3>
                    <p>
                    Linki powinny być zapisane w formacie:<br>
                    <h4><strong>http://</strong>link-do-strony.pl</h4>
                    Dodatkowe opcje to:<br>
                    <strong>mailto:</strong> adres@domena.pl<br>
                    <strong>tel:</strong> +48123456789 (z kierunkowym kraju)<br>
                    <strong>gps:</strong> 54.3610873,18.6900271
                    </p>
                    <hr>
                    <img src="'.plugins_url( '/sprite/palette.png' , __FILE__ ).'" alt="" class="img-responsive"/>
                    <h3>Paleta kolorów Material Design</h3>
                    <a href="http://www.google.com/design/spec/style/color.html" target="_blank">zobacz</a>
                    <hr>
                    <h3>Autor</h3>
                    <h4>Mateusz Lewandowski</h4>
                    <a href="mailto:developer@dev-ninja.pl">skontaktuj się</a>
                '; ?>
            </div>
        </div>

    <?php
    }

    function wmdm_settings()
    {
        register_setting('wmdm_settings', 'wmdm_settings');

        // SECTIONS
        add_settings_section('menu_type', 'Wybierz typ menu', array(&$this, 'select_menu'), 'wmdm_settings');
        add_settings_section('boxes', 'Trzy boksy wyświetlane na telefonach', array(&$this, 'section_boxes'), 'wmdm_settings');
        add_settings_section('unfolded', 'Ustawienia nagłówka menu po rozwinięciu', array(&$this, 'section_unfolded'), 'wmdm_settings');
        add_settings_section('logo_login', 'Ustawienia Logo / Panel logowania / Numer telefonu', array(&$this, 'section_logo_login'), 'wmdm_settings');
        add_settings_section('tablet_menu', 'Ustawienia menu na tablecie', array(&$this, 'section_tablet'), 'wmdm_settings');
        add_settings_section('other', 'Inner', array(&$this, 'other'), 'wmdm_settings');


        // FIELDS
        add_settings_field('menu_type_1', 'Typ menu', array( &$this, 'select_menu_type' ), 'wmdm_settings', 'menu_type');
        add_settings_field('box_1', 'Przyciski Szybkiej Akcji', array( &$this, 'section_box_1' ), 'wmdm_settings', 'boxes');
        add_settings_field('unfold_1', 'Pojawi się: ', array( &$this, 'section_unfold_1' ), 'wmdm_settings', 'unfolded');
        add_settings_field('logo_login_1', 'Logo', array( &$this, 'section_logo_login_1' ), 'wmdm_settings', 'logo_login');
        add_settings_field('tablet_menu_1', 'Prawa strona paska menu na tablecie', array( &$this, 'section_tablet_menu' ), 'wmdm_settings', 'tablet_menu');
        add_settings_field('main_color', 'Podstawowy kolor', array( &$this, 'main_color' ), 'wmdm_settings', 'other');
        add_settings_field('gestures', 'Obsługa gestów', array( &$this, 'gestures' ), 'wmdm_settings', 'other');

    }

    function select_menu_type(){
        $menu_type = array(
            'id' => 'menu_type',
            'setting' => 'menu-type',
            'options' => array(
                ['1', 'Biały pasek - bez wyróżnienia'], ['2', 'Biały pasek z wyróżnieniem menu']
            )
        );
        ?>
        <span class="radio">
            <?php $this->radioButtons($menu_type); ?>
        </span>
        <?php
    }

    function  section_tablet_menu(){

        $tablet = array(
                'id' => 'tablet_radio_',
                'setting' => 'tablet-right-place',
                'options' => array(
                        ['logo', 'Logo'], ['login', 'Link do logowania'], ['search', 'Wyszukiwarka']
                )
        );
        ?>
        <span class="radio">
            <?php $this->radioButtons($tablet); ?>
        </span>
        <?php
    }



    function section_logo_login_1() {
        $options = get_option( 'wmdm_settings' );
        ?>

        <span class='upload'>
            <img src='<?php echo esc_url( $options["logo_image"] ); ?>' class='preview-upload' />
            <label for="wmdm_settings[logo_image]">
                <input type='hidden' id='wmdm_settings[logo_image]' class='regular-text text-upload' name='wmdm_settings[logo_image]' value='<?php echo esc_url( $options["logo_image"] ); ?>' />
                <input type='button' class='button button-upload' value='Wybierz logo'/>
            </label>
        </span><br/>
        <span class="text">
           <label for="wmdm_settings[login_link]">Link logowania <input type='text' id='wmdm_settings[login_link]' class='regular-text' name='wmdm_settings[login_link]' value='<?php echo esc_url($options["login_link"]); ?>'/></label><br>
           <label for="wmdm_settings[login_phone]">Numer telefonu
               <input type='text' id='wmdm_settings[login_phone]' class='regular-text' name='wmdm_settings[login_phone]' value='<?php echo $options["login_phone"]; ?>'/>
           </label>
       </span>
    <?php
    }


    function section_box_1() {
            $this->iconSelect(array('box_1', 'box_2', 'box_3'));
    }


    function section_unfold_1() {
        $unfolded = array(
            'id' => 'header_menu',
            'setting' => 'unfolded_',
            'options' => array(
                ['find', 'Wyszukiwarka', 'Szukaj'], ['login', 'Link do logowania', 'Zaloguj się'], ['phone', 'Skontaktuj się z nami', 'Zadzwoń']
            )
        );
        ?>
        <span class='checkboxes'>
            <?php $this->checkboxes($unfolded); ?>
        </span>
    <?php
    }

    function other(){}
    function main_color(){
        $options = get_option( 'wmdm_settings' ); ?>

        <span class='text box'>
            <label for="wmdm_settings[main_color]">
                <input type='text' id='wmdm_settings[main_color]' class='color-field' name='wmdm_settings[main_color]' value='<?php echo $options["main_color"]; ?>'/>
            </label>
        </span>

    <?php }

    function gestures(){
        $options = get_option( 'wmdm_settings' );
        ?>
        <span class='checkboxes'>
            <label for="wmdm_settings[gestures]">
                <input type="checkbox" id="wmdm_settings[gestures]" name="wmdm_settings[gestures]" value="true" <?php checked( 'true' ,$options['gestures']); ?> />
            </label>
        </span>
<?php
    }





    function register_material_menus() {
        register_nav_menu('tablet-menu-material',__( 'Tablet Material Menu' ));
        register_nav_menu('mobile-menu-material',__( 'Mobile Material Menu' ));
    }





    function section_boxes(){}
    function section_unfolded(){}
    function section_logo_login(){}
    function section_tablet(){}
    function select_menu(){}

    function print_on_frontend(){
        $options = get_option('wmdm_settings');
        include_once 'wmdm-frontend.php';
        print_frontend($options);
    }

}
$wmdm = new WMDM(__FILE__);
