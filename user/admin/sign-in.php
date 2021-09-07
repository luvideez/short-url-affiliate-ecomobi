<?php
$error = false;


//Load Linkity.
require_once(dirname(__FILE__)."/../libs/linkity.php");
$linkity = new Linkity();
if($linkity->installing) { exit; }


//Handle signing in.
if($_POST) {
  try {
    $linkity->login($_POST["email"], $_POST["password"]);
    header("Location: ".$linkity->base."/admin", true, 302);
    exit;
  } catch (\Exception $e) { $error = $e->getMessage(); }
}


?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login | <?php echo htmlspecialchars($linkity->name); ?></title>
    <meta name="robots" content="index, follow">
    <meta name="description" content="Log into <?php echo htmlspecialchars($linkity->name); ?>. A free URL shortener. Powered by DevUncoded's Linkity."/>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha256-YLGeXaapI0/5IgZopewRJcFXomhRMlYYjugPLSyNjTY=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.8.1/css/all.min.css" integrity="sha256-7rF6RaSKyh16288E3hVdzQtHyzatA2MQRGu0cf6pqqM=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chosen-js@1.8.7/chosen.min.css" integrity="sha256-EH/CzgoJbNED+gZgymswsIOrM9XhIbdSJ6Hwro09WE4=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pretty-checkbox@3.0.3/dist/pretty-checkbox.min.css" integrity="sha256-sI14MHRjSf+KF9MjQHjqHkbDPwsdKXUkhBUdnGCg1iU=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.5.7/dist/flatpickr.min.css" integrity="sha256-SjAq687XUZtaah0K6nf62lqS5pdcOD7r33HxyBZ5lJg=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito+Sans:400,600,700|Source+Code+Pro:400,500">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($linkity->admin->base); ?>/assets/map/jquery-jvectormap-2.0.3.css">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($linkity->admin->base); ?>/assets/style.css">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($linkity->admin->base); ?>/assets/app.css">
  </head>
  <body class="loading-done">



    <main style="min-height: calc(100vh - 60px);"></main>



    <footer class="footer active">
      <ul class="menu">
        <li class="copyright">With <i class="icon fas fa-heart"></i> from <a href="https://devuncoded.com" target="_blank">DevUncoded</a> </li>
      </ul>
    </footer>



    <aside class="card popup undismissable">
      <h1 class="title">Login</h1>
      <form method="post">
        <div class="grey">
          <div class="group">
            <label>Email</label>
            <input class="input" type="email" name="email" placeholder="peter@oscorp.com">
          </div>
          <div class="group">
            <label>Password</label>
            <input class="input" type="password" name="password" placeholder="••••••••">
          </div>
        </div>
        <div class="btns-right">
          <button class="btn">Login</button>
        </div>
      </form>
    </aside>



    <script src="https://cdn.jsdelivr.net/npm/jquery@3.4.0/dist/jquery.min.js" integrity="sha256-BJeo0qm959uMBGb65z40ejJYGSgR7REI4+CW1fNKwOg=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chosen-js@1.8.7/chosen.jquery.min.js" integrity="sha256-c4gVE6fn+JRKMRvqjoDp+tlG4laudNYrXI1GncbfAYY=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/tippy.js@3.4.1/dist/tippy.all.min.js" integrity="sha256-iLOTBBYaCzN2utfyApj2yRw3ltH86LwYZrzOz3TTbyg=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.5.7/dist/flatpickr.min.js" integrity="sha256-G8zoqUF5tPdnKqIP/YD+QSvirWve3Ma9p+T8eFxhGiY=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.24.0/moment.js" integrity="sha256-H9jAz//QLkDOy/nzE9G4aYijQtkLt9FvGmdUTwBk6gs=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/aviation@1.2.0/aviation.min.js" integrity="sha256-EDXzQeInNk6ULD8P/nsNWt1SBeNauqrzze1UNajM2LM=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha256-fzFFyH01cBVPYzl16KT40wqjhgPtq6FFUB6ckN2+GGw=" crossorigin="anonymous"></script>
    <script src="<?php echo $linkity->admin->base; ?>/assets/map/jquery-jvectormap-2.0.3.min.js"></script>
    <script src="<?php echo $linkity->admin->base; ?>/assets/map/mill.js"></script>
    <script src="<?php echo $linkity->admin->base; ?>/assets/script.js"></script>
    <script>
    $(document).ready(function() {
      page.renderElements()

      var el = $(".popup")
      el.fadeIn(200).addClass("active")
      setTimeout(function() {
        var input = el.find("input").get(0)
        if(input) { input.focus() }
      }, 350)

      <?php if($error) { echo 'notify("'.htmlspecialchars($error).'")'; } ?>
    })
    </script>
  </body>
</html>
