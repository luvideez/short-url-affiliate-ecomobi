<?php
/*
Redirects a shortlink to the long URL.
+ Also handles 404 (not found) links.
*/



//Load Linkity.
require_once(dirname(__FILE__)."/libs/linkity.php");
$linkity = new Linkity();
if($linkity->installing) { exit; }


//Resolve the shortname.
try {
  $link = $linkity->resolveLink();
  $view = $linkity->saveView($link);

  header("Location: ".$link["redirect"], true, 302);
  exit;
} catch (\Exception $e) {
  http_response_code(404);
}

?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="robots" content="noindex, follow">

    <title>404 | <?php echo $linkity->name; ?></title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:500,600">
    <style media="screen">
    body,
    html {
        margin: 0;
        padding: 0;
    }

    *, ::before, ::after {
        -webkit-tap-highlight-color: transparent;
        -webkit-backface-visibility: hidden;
        -webkit-appearance: none;
        -webkit-overflow-scrolling: touch;
        box-sizing: border-box;
        word-wrap: break-word;
        word-spacing: 0.07em;
    }

    body {
        background: #fff;
        color: #000;
        width: 100%;
        overflow-x: hidden;

        font-family: "Open Sans", "Segoe UI", "Montserrat", "Helvetica Neue", "Helvetica", sans-serif;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;

        background: linear-gradient(135deg, white 50%, #fff270 50%);
        background-attachment: fixed;
    }

    @media screen and (max-width: 600px) {
      body {
        background: #fff270;
        background-attachment: fixed;
      }
    }



    .center {
      position: absolute;
      z-index: 2;
      top: 50%; left: 50%;
      transform: translate(-50%, -50%);
    }

    h1 {
      font-size: 60px;
      border: 5px #000 solid;
      padding: 0 20px;
      letter-spacing: 2px;
      margin-bottom: 0;
    }

    h3 {
      font-weight: 500;
    }
    </style>
  </head>
  <body>
    <div class="center">
      <h1>404</h1>
      <h3>No such link exists</h3>
    </div>
  </body>
</html>
