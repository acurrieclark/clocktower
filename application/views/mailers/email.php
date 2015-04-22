<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<!-- If you delete this tag, the sky will fall on your head -->
<meta name="viewport" content="width=device-width" />

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />


<style type="text/css">
  .ExternalClass {width:100%;} /* Forces Hotmail to display emails at full width */

                        .ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {
                            line-height: 100%;
                            }

                        body {-webkit-text-size-adjust:none; -ms-text-size-adjust:none;}

                        body {margin:0; padding:0;}

                        table td {border-collapse:collapse;}

p {margin:0; padding:0; margin-bottom:0;}

                        h1, h2, h3, h4, h5, h6 {
                           color: black;
                           line-height: 100%;
                           }

                        body, #body_style {
                           background:#FFF;
                           min-height:1000px;
                           color:#000;
                           font-family: "Helvetica Neue", "Helvetica", Helvetica, Arial, sans-serif;
                           font-size:12px;
                           }

                        span.yshortcuts { color:#000; background-color:none; border:none;}
                        span.yshortcuts:hover,
                        span.yshortcuts:active,
                        span.yshortcuts:focus {color:#000; background-color:none; border:none;}


/* -------------------------------------------
    PHONE
    For clients that support media queries.
    Nothing fancy.
-------------------------------------------- */
@media (max-width: 600px) {

  .display-error-message {
    display: none;
  }

  .mobile-hide {
    display: none;
  }

  a[class="btn"] { display:block!important; margin-bottom:10px!important; background-image:none!important; margin-right:0!important;}

  div[class="column"] { width: auto!important; float:none!important;}

  table.social div[class="column"] {
    width:auto!important;
  }

  .news-items .header-image-column, .news-items .header-image-description-column {
    width: auto!important;
  }

  .social .contact-column {
    width: auto!important;
  }

.article-header-image-holder, .main-header-image {
  max-width: 100%!important;
}

}

</style>


</head>

<body bgcolor="#FFFFFF">

<div id="body_style" style="padding: 0px">

<!-- HEADER -->
<table class="head-wrap">
  <tr>
    <td>&nbsp;</td>
    <td class="header container">

        <div class="content">
          <table>
            <tr>
              <td>
                <h1>Clocktower Email</h1>
              </td>
            </tr>
          </table>
        </div>

    </td>
    <td>&nbsp;</td>
  </tr>
</table><!-- /HEADER -->


<!-- BODY -->
<table class="body-wrap" cellspacing="0" cellpadding="0">
  <tr>
    <td>&nbsp;</td>
    <td class="container" bgcolor="#FFFFFF">

      <div class="content main-content">

      <table>

        <tr>
          <td>

            <?php if ($email_content_template && file_exists(ROOT . DS . 'application/views/mailers/$'.$email_content_template.'.php')): ?>

                <?php include(ROOT . DS . 'application/views/mailers/$'.$email_content_template.'.php') ?>

            <?php endif ?>

          </td>
        </tr>

      </table>

    </td>
    <td>&nbsp;</td>
  </tr>
</table>


</div>

</body>
</html>
