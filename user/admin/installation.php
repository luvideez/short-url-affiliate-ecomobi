<?php


//Load Linkity.
require_once(dirname(__FILE__)."/../libs/linkity.php");
$linkity = new Linkity();
if(!$linkity->installing) { header("Location: ".$linkity->admin->base); exit; }



//Handle installation.
if($_POST) {
  try {
    //First check email & password.
    if(!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) { throw new Exception("Please enter a valid email"); }
    if(strlen($_POST["password"]) < 6) { throw new Exception("Password must be at least 6 characters long"); }

    $config = ["name" => "Linkity", "homepage" => true];
    if($_POST["name"]) { $config["name"] = $_POST["name"]; }
    if($_POST["db"]) { $config["db"] = $_POST["db"]; }

    //Check if we're running in a sub-directory.
    $path = rtrim(ltrim(rtrim(dirname($_SERVER["SCRIPT_NAME"]), "/admin"), "/"), "/");
    if($path && $path !== "/") {
      $config["path"] = "/".$path;
    }

    //Create config & user.
    $linkity->config($config);
    $user = $linkity->createUser($_POST["email"], $_POST["password"]);
    $linkity->login($_POST["email"], $_POST["password"]);

    http_response_code(200);
  } catch (\Exception $e) { http_response_code(400); echo $e->getMessage(); }
  exit;
}


?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Installation | Linkity</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha256-YLGeXaapI0/5IgZopewRJcFXomhRMlYYjugPLSyNjTY=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.8.1/css/all.min.css" integrity="sha256-7rF6RaSKyh16288E3hVdzQtHyzatA2MQRGu0cf6pqqM=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chosen-js@1.8.7/chosen.min.css" integrity="sha256-EH/CzgoJbNED+gZgymswsIOrM9XhIbdSJ6Hwro09WE4=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito+Sans:400,600,700|Source+Code+Pro:400,500">
    <style media="screen"><?php echo file_get_contents(dirname(__FILE__)."/../admin/assets/style.css").file_get_contents(dirname(__FILE__)."/../admin/assets/app.css"); ?></style>
    <style media="screen">
    #s1 .title {
      line-height: 1.6em;
      font-weight: 400;
      font-size: 32px;
      margin: 10px 0;
      margin-bottom: 20px;
    }

    .emoji {
      text-align: center;
      margin: 50px 0;
      font-size: 100px;
    }

    #s1 .link {
      display: block;
      text-align: center;
      font-size: 22px;
    }

    #s3 .link {
      font-size: 14px;
    }

    [tabindex] {
      outline: none;
    }



    .step-wrapper {
      overflow: hidden;
      margin-left: -20px;
      margin-right: -20px;
    }

    .step-wrapper form {
      width: 1000%;
    }

    .step {
      display: inline-block;
      vertical-align: top;
      width: 10%;
    }

    .step-inner {
      padding: 0 20px;
      display: none;
    }





    .additional, .advanced {
      display: none;
    }
    </style>
  </head>
  <body class="loading-done">



    <main style="min-height: calc(100vh - 60px);"></main>



    <footer class="footer active">
      <ul class="menu">
        <li class="copyright">With <i class="icon fas fa-heart"></i> from <a href="https://devuncoded.com" target="_blank">DevUncoded</a> </li>
      </ul>
    </footer>



    <aside class="card popup undismissable">
      <nav class="step-wrapper">
        <form>
          <section class="step" id="s1">
            <div class="step-inner">
              <h1 class="title">Thank you for purchasing Linkity!</h1>
              <h1 class="emoji">🎉</h1>
              <a tabindex="0" class="link" onclick="toStep('#s2')">Let's set it up <i class="icon fas fa-arrow-right"></i></a>
            </div>
          </section>

          <section class="step" id="s2">
            <div class="step-inner">
              <h1 class="title">App</h1>
              <div class="grey">
                <div class="group">
                  <label>Name</label>
                  <input class="input blue" type="text" name="name" value="Linkity">
                </div>
                <h4 style="margin-top: 20px; margin-bottom: 10px; color: #515866; line-height: 1.5em;">
                  Login
                </h4>
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
                <a tabindex="0" class="btn" onclick="toStep('#s3')">Next</a>
              </div>
            </div>
          </section>
          <section class="step" id="s3">
            <div class="step-inner">
              <h1 class="title">Database</h1>
              <div class="grey">
                <div class="group">
                  <label>Type</label>
                  <select class="input database_type" name="db[type]">
                    <?php if(extension_loaded("sqlite3")) { echo '<option value="sqlite">SQLite - Inbuilt</option>'; } ?>
                    <option value="mysql">MySQL</option>
                    <option value="pgsql">PostgreSQL</option>
                    <option value="mariadb">MariaDB</option>
                    <option value="mssql">Microsoft SQL Server</option>
                  </select>
                </div>
                <div class="additional" <?php if(!extension_loaded("sqlite3")) { echo 'style="display: block;" '; } ?>>
                  <div class="group">
                    <label>Name</label>
                    <input class="input" type="text" name="db[name]" placeholder="Database name" value="LinkityDB">
                  </div>
                  <div class="group">
                    <label>User</label>
                    <input class="input" type="text" name="db[user]" placeholder="root">
                  </div>
                  <div class="group">
                    <label>Password</label>
                    <input class="input" type="text" name="db[password]" placeholder="(None)">
                  </div>
                  <a tabindex="0" class="link" onclick="$('.advanced').slideDown(250);$(this).slideUp(250)">Advanced</a>
                  <div class="advanced">
                    <div class="group">
                      <label>Host</label>
                      <input class="input" type="text" name="db[host]" placeholder="127.0.0.1">
                    </div>
                    <div class="group">
                      <label>Port</label>
                      <input class="input" type="text" name="db[port]" placeholder="3306">
                    </div>
                  </div>
                </div>
              </div>
              <div class="btns-right">
                <a tabindex="0" class="btn simple" onclick="toStep('#s2')">Back</a>
                <button class="btn">Install</button>
              </div>
            </div>
          </section>
        </form>
      </nav>


    </aside>




    <script src="https://cdn.jsdelivr.net/npm/jquery@3.4.0/dist/jquery.min.js" integrity="sha256-BJeo0qm959uMBGb65z40ejJYGSgR7REI4+CW1fNKwOg=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chosen-js@1.8.7/chosen.jquery.min.js" integrity="sha256-c4gVE6fn+JRKMRvqjoDp+tlG4laudNYrXI1GncbfAYY=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/tippy.js@3.4.1/dist/tippy.all.min.js" integrity="sha256-iLOTBBYaCzN2utfyApj2yRw3ltH86LwYZrzOz3TTbyg=" crossorigin="anonymous"></script>
    <script><?php echo file_get_contents(dirname(__FILE__)."/../admin/assets/script.js"); ?></script>
    <script>
    $(document).ready(function() {
      page.renderElements()

      var el = $(".popup")
      el.fadeIn(200).addClass("active")
      setTimeout(function() {
        var input = el.find("input").get(0)
        if(input) { input.focus() }
      }, 350)

      toStep("#s1")
    })



    $(document).on("change", ".database_type", function(e) {
      var val = $(this).val()
      if(val == "sqlite") {
        $(".additional").slideUp(250)
      }
      else {
        $(".additional").slideDown(250)
        if(val == "pgsql") {
          $("input[name='db[user]']").attr("placeholder", "postgres")
          $("input[name='db[port]']").attr("placeholder", 5432)
        }
        else {
          $("input[name='db[user]']").attr("placeholder", "root")
          if(val == "mssql") {
            $("input[name='db[port]']").attr("placeholder", 1433)
          }
          else {
            $("input[name='db[port]']").attr("placeholder", 3306)
          }
        }
      }
    })


    $(document).on("submit", "form", function(e) {
      e.preventDefault()
      if($(".step.active").attr("id") == "s2") { return toStep("#s3") }
      $(".popup").addClass("loading")
      $.ajax({
        url: "?install",
        method: "post",
        data: $(this).serialize(),
        complete: function(res) {
          if(res.status == 200) { return location.reload() }
          notify(safe(res.responseText))
          $(".popup").removeClass("loading")
        }
      })
    })





    $(window).resize(function() {
      var step = $(".step.active")
      if(step.length) { toStep(step) }
    })



    function toStep(el) {
      el = $(el)
      var i = el.index()


      $(".step-wrapper").animate({scrollLeft: i * el.outerWidth() + (i * 5)}, 450)
      $(".step.active").removeClass("active").find(".step-inner").slideUp(450)
      el.addClass("active").find(".step-inner").slideDown(400)
      setTimeout(function() {
        var i = el.find("input").get(0)
        if(i) { i.focus() }
      }, 300)
    }
    </script>
  </body>
</html>
