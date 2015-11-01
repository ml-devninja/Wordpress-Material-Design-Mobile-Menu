<?php function print_frontend($options){ ?>


    <?php

    /**
     *  MENU BAR || MOBILE & TABLET
     */

    ?>

    <div class="container-fluid hidden-lg header-mobile">

    <div class="row depth-1 nav-bar display-flex">
        <div class="col-xs-4 col-sm-2">
            <div class="row">
                <div class="display-flex h-align-centered v-align-centered make-space wmdm-opener <?php echo ( $options['menu-type'] == 2 ? 'reversed' : false); ?>">
                    <i class="icon-menu"></i>
                    <span class="menu-word">Menu</span>
                </div>
            </div>
        </div>

        <div class="col-xs-6 col-xs-offset-2 col-sm-3 col-sm-offset-7 display-flex v-align-centered h-align-right" style="text-align:right;">
            <a href="<?php echo esc_url( home_url( '/' ) )?>" title="<?php bloginfo('name'); ?>" class="mobile-go-home visible-xs <?php echo ( $options['tablet-right-place'] == 'logo' ? 'visible-sm visible-md' : false); ?>">
                <img src="<?php echo esc_attr( $options['logo_image'] ); ?>" alt="<?php bloginfo('name'); ?>" id="mobile-header-logo">
            </a>



            <?php if( $options['tablet-right-place'] == 'search') : ?>
                <form role="search" method="get" id="searchform" class="wmdm-search reset-margin searchform push-right visible-sm visible-md" action="">
                    <input type="text" value="" name="s" id="s" placeholder="szukaj"><i class="md icon-search"></i>
                </form>
            <?php elseif($options['tablet-right-place'] == 'login'): ?>
                <a href="<?php echo $options['login_link']; ?>" class="login-btn visible-sm visible-md">
                    <i class="icon-lock"><span>Logowanie</span></i>
                </a>
            <?php endif; ?>




        </div>
    </div>

        <?php if( is_home() || is_front_page()) : ?>
        <div class="q-action-wrapper row visible-xs">
            <?php $counter = 1; ?>
            <?php while($counter<4) : ?>
                <div class="col-xs-4 q-action">
                    <div>
                        <a href="<?php echo esc_attr( $options['box_'.$counter.'_link'] ); ?>">
                            <i class="<?php echo esc_attr( $options['box_'.$counter.'_class'] ); ?>"></i>
                            <span><?php echo esc_attr( $options['box_'.$counter.'_label'] ); ?></span>
                        </a>
                    </div>
                </div>

                <?php $counter++; endwhile; ?>
        </div>
    <?php endif; ?>
    </div>









    <?php

    /**
     *  TOGGLED MENU
     */

    ?>

<div class="col-xs-10 col-sm-5 mobile-menu-toggle hidden-lg">
    <div class="row toggle-header display-flex">
        <div class="col-xs-9 q-bar">
            <?php if( $options['unfolded_1'] == 'phone') : ?>
                <a href="tel:<?php echo $options['login_phone']; ?>"><i class="icon-phone"></i>Skontaktuj się</a>
            <?php elseif( $options['unfolded_1'] == 'find') : ?>
                <form role="search" method="get" id="searchform" class="searchform wmdm-search" action="">
                    <input type="text" value="" name="s" id="s" placeholder="szukaj"><i class="icon-bs icon-search_bs"></i>
                </form>
            <?php else : ?>
                <a href="<?php echo $options['login_link']; ?>"><i class="icon-lock"></i>Logowanie</a>
            <?php endif; ?>
        </div>
        <div class="col-xs-3 close-menu display-flex h-align-centered v-align-centered">
            <i class="icon-close"></i>
        </div>
    </div>
    <div class="row visible-xs menu-wrapper">
        <?php wp_nav_menu( array( 'theme_location' => 'mobile-menu-material' ) ); ?>
    </div>
    <div class="row visible-sm visible-md menu-wrapper">
        <?php wp_nav_menu( array( 'theme_location' => 'tablet-menu-material' ) ); ?>
    </div>
</div>
<?php } ?>