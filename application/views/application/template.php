<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>{@=title}</title>
        <meta name="description" content="{@=description}">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
        <!--[if lt IE 9]>
          <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->

<?php

    // NB. bootstrap and main.css are automatically included

 ?>

    </head>
    <body>
        <!--[if lt IE 7]>
            <p class="browsehappy">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
        <![endif]-->

      <?php $this->template->content_for("body"); ?>

        <header id="header" class="">
            <?php $this->template->content_for("header"); ?>
        </header><!-- /header -->

        <div class="container">
            <?php $this->template->content_for("main"); ?>
        </div>

        <footer>
            <?php $this->template->content_for("footer"); ?>
        </footer>

        <?php

        // NB. Modenizr, bootstrap, jquery and main.js are automatically included

         ?>

    </body>
</html>

