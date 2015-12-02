<!-- Fixed navbar -->

<div id="top" class="grey">
    <div class="container">
        <div class="row">
            <div class="col-xs-6">
                <ul class="contact-details">
                    <li class="text-uppercase">Appelez-nous au <strong>01 42 360 330</strong></li>
                    <li>
                        <a href="/contact" title="Informations d'accès" class="text-uppercase">Trouver notre spa</a>
                    </li>
                </ul>
            </div>
            <div class="col-xs-6">
                <ul class="social-links">
                    <?php if (is_user_logged_in()) { ?>
                        <li><a href="<?php echo wp_logout_url() ?>">Se déconnecter</a></li>
                        <li><a class="text-uppercase" rel="nofollow"
                               href="<?php echo get_permalink(get_option('woocommerce_myaccount_page_id')); ?>"
                               title="<?php _e('Mon Compte', 'woothemes'); ?>"><?php _e('Mon Compte', 'woothemes'); ?></a>
                        </li>

                    <?php } else { ?>
                        <li><a class="text-uppercase" rel="nofollow"
                               href="<?php echo get_permalink(get_option('woocommerce_myaccount_page_id')); ?>"
                               title="<?php _e('Login', 'woothemes'); ?>"><?php _e('Se connecter', 'woothemes'); ?></a>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<header id="header">
    <div class="container clearfix">
        <div class="row">
            <div class="col-md-6">
                <?php if (is_front_page()): ?>
                    <h1 id="logo">
                        <a href="/">
                            <img alt="MontKailash - Centre de bien être du tibet - Spa Paris"
                                 src="<?php echo get_template_directory_uri(); ?>/img/spa-paris.png">
                        </a>

                        <div id="tagline">
                            <span class="text-pink">གངས་རིན་པོ་ཆེ་</span>
                        </div>
                    </h1>
                <?php else: ?>
                    <div id="logo">
                        <a href="/">
                            <img alt="spa paris mont kailash"
                                 src="<?php echo get_template_directory_uri(); ?>/img/spa-paris.png">
                        </a>

                        <div id="tagline">
                            <span class="text-pink">གངས་རིན་པོ་ཆེ་</span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <nav id="navigation" class="navbar navbar-default">
                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                                data-target=".navbar-collapse">
                            <span class="sr-only">Toggle navigation</span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>
                    </div>
                    <div class="navbar-collapse collapse">
                        <ul id="menu" class="nav navbar-nav">
                            <li <?php if (get_the_title() === 'Home' || basename(get_permalink()) === 'bien-etre-et-detente-au-spa-mont-kailash' || basename(get_permalink()) === 'massage-tibetain' || basename(get_permalink()) === 'fitness'): ?>
                                class="active"
                            <?php endif; ?>><a href="/">
                                    Accueil </a>
                                <ul>
                                    <li>
                                        <a href="/bien-etre-et-detente-au-spa-mont-kailash">Présentation du spa</a>
                                    </li>
                                    <li>
                                        <a href="/massage-tibetain">Le massage tibétain</a>
                                    </li>
                                    <li>
                                        <a href="/yoga-meditation">Yoga et Méditation</a>
                                    </li>
                                </ul>
                            </li>
                            <?php $page = explode('/', get_permalink()) ?>
                            <li <?php if (isset($page[3]) && $page[3] === 'massages-et-soins'): ?> class="active" <?php endif; ?>>
                                <a href="/massages-et-soins">Massages &amp; soins</a>
                                <ul>
                                    <li>
                                        <a href="/categorie/carte-des-massages">Carte des massages</a>
                                    </li>
                                    <li>
                                        <a href="/categorie/formules">Formules</a>
                                    </li>
                                    <li>
                                        <a href="/categorie/soins">Soins</a>
                                    </li>
                                    <?php /*<li>
                                        <a href="/categorie/soins-flash">Soins flash</a>
                                    </li> */ ?>
                                </ul>
                            </li>
                            <li <?php if (basename(get_permalink()) === 'presse'): ?> class="active" <?php endif; ?>>
                                <a href="/presse">Presse</a>
                            </li>
                            <li <?php if (basename(get_permalink()) === 'lequipe'): ?> class="active" <?php endif; ?>>
                                <a href="/lequipe">L'équipe</a>
                            </li>
                            <li <?php if (basename(get_permalink()) === 'contact'): ?> class="active" <?php endif; ?>>
                                <a href="/contact">Contact</a>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>
        </div>
    </div>
</header>
