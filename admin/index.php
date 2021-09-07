<?php
http_response_code(200);
$uri = parse_url($_SERVER["REQUEST_URI"])["path"];
$method = strtoupper($_SERVER["REQUEST_METHOD"]);


//Load Linkity.
require_once(dirname(__FILE__)."/../libs/linkity.php");
$linkity = new Linkity();
if($linkity->installing) { exit; }
if($linkity->path) { $uri = "/".preg_replace("/^".preg_quote("/".$linkity->path."/", "/")."/", "", $uri); }



//Make sure user is logged in.
try {
  $login = $linkity->resolveLogin();
  $user = $login["user"];
  $session = $login["session"];
} catch (\Exception $e) {
  header("Location: ".$linkity->base."/admin/sign-in", true, 302);
  exit;
}





//The admin API.
if(strpos($uri, "/admin/api") !== false) {
  try {
    header("Content-Type: application/json");



    if($uri == "/admin/api/home" && $method == "GET") {
      $since = 0; $till = time();
      if($_GET["since"]) { $since = $_GET["since"]; }
      if($_GET["till"]) { $till = $_GET["till"]; }

      $view = $linkity->getViews(false, false, $since, $till);
      $view["chart"] = [];

      //Get data for chart.
      $earliest = $linkity->db->get("views", "created", ["ORDER" => "created"]);
      if($earliest) {
        if(true === true || date("F Y", $earliest) == date("F Y")) {
          $days = date("t"); $monthYear = date("F Y"); $today = date("j");
          for($current = 1; $current <= $days; $current++) {
            $date = $current." ".$monthYear;
            if($current > $today) { $view["chart"][$date] = null; break; }
            $view["chart"][$date] = $linkity->db->count("views", ["date_day" => $date]);
          }
        }
        else {
          $timeData = $linkity->db->query("SELECT <date_month>, COUNT(*) FROM <views> WHERE <date_month> != '' AND created BETWEEN :min AND :max GROUP BY <date_month>", ["min" => $since, "max" => $till])->fetchAll();
          foreach ($timeData as $data) { $view["chart"][$data["date_month"]] = $data["count"]; }
        }
      }

      echo json_encode($view);
    }
    else if($uri == "/admin/api/links" && $method == "GET") {

      if(!$_GET["search"]) {
        $skip = 0;
        if($_GET["skip"] >= 1) { $skip = $_GET["skip"]; }
        $links = $linkity->listLinks(20, $skip);
      }
      else {
        $links = ["data" => $linkity->db->select("links", "*", [
          "OR" => [
            "redirect[~]" => $_GET["search"],
            "shortname[~]" => $_GET["search"],
            "domain[~]" => $_GET["search"],
            "id[~]" => $_GET["search"]
          ]
        ]), "has_more" => false];
      }

      foreach ($links["data"] as $i => $link) {
        $links["data"][$i]["views"] = $linkity->db->count("views", ["link" => $link["id"]]);
      }
      echo json_encode($links);
    }
    else if($uri == "/admin/api/links" && $method == "POST") {
      echo json_encode($linkity->createLink($_POST["url"], $_POST["shortname"], $_POST["user"]));
    }
    else if($uri == "/admin/api/link" && $method == "GET") {
      $link = $linkity->getLink($_GET["id"]);

      $since = 0; $till = false;
      if($_GET["since"]) { $since = $_GET["since"]; }
      if($_GET["till"]) { $till = $_GET["till"]; }

      $view = $linkity->getViews($link["id"], false, $since, $till);
      $view["chart"] = [];

      //Get data for chart.
      $earliest = $linkity->db->get("views", "created", ["ORDER" => "created", "link" => $link["id"]]);
      if($earliest) {
        if(true === true || date("F Y", $earliest) == date("F Y")) {
          $days = date("t"); $monthYear = date("F Y"); $today = date("j");
          for($current = 1; $current <= $days; $current++) {
            $date = $current." ".$monthYear;
            if($current > $today) { $view["chart"][$date] = null; break; }
            $view["chart"][$date] = $linkity->db->count("views", ["date_day" => $date, "link" => $link["id"]]);
          }
        }
        else {
          $timeData = $linkity->db->query("SELECT <date_month>, COUNT(*) FROM <views> WHERE <link> = :link AND <date_month> != '' AND created BETWEEN :min AND :max GROUP BY <date_month>", ["min" => $since, "max" => $till, "link" => $link["id"]])->fetchAll();
          foreach ($timeData as $data) { $view["chart"][$data["date_month"]] = $data["count"]; }
        }
      }

      echo json_encode(["link" => $link, "views" => $view]);
    }
    else if($uri == "/admin/api/link" && $method == "POST") {
      echo json_encode($linkity->updateLink($_GET["id"], $_POST));
    }
    else if($uri == "/admin/api/link" && $method == "DELETE") {
      echo json_encode($linkity->deleteLink($_GET["id"]));
    }
    else if($uri == "/admin/api/settings" && $method == "POST") {

      $update = [];
      if($_POST["path"]) { $update["path"] = $_POST["path"]; }
      if($_POST["domain"]) { $update["domain"] = $_POST["domain"]; }
      if($_POST["name"]) { $update["name"] = $_POST["name"]; }
      if($_POST["homepage"] == "true") { $update["homepage"] = true; } else { $update["homepage"] = true; }

      $linkity->config($update);

      echo json_encode(["success" => true]);
    }
    else if($uri == "/admin/api/profile" && $method == "POST") {
      echo json_encode($linkity->updateUser($user["id"], $_POST));
    }
    else if($uri == "/admin/api/profile/sign-out" && $method == "POST") {
      echo json_encode($linkity->logout());
    }

  } catch (\Exception $e) { http_response_code(400); echo json_encode(["error" => $e->getMessage()]); }

  exit;
}





?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Dashboard | <?php echo htmlspecialchars($linkity->name); ?></title>
    <meta name="robots" content="noindex, nofollow">

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
  <body>



    <nav class="sidebar">
      <img class="logo" src="<?php echo htmlspecialchars($linkity->admin->base); ?>/assets/logo.png" alt="<?php echo htmlspecialchars($linkity->name); ?>">

      <ul class="menu">
        <a href="<?php echo htmlspecialchars($linkity->admin->base); ?>/">
          <i class="icon fas fa-home"></i>
          Home
        </a>
        <a href="<?php echo htmlspecialchars($linkity->admin->base); ?>/links">
          <i class="icon fas fa-link"></i>
          Links
        </a>
      </ul>

      <ul class="menu">
        <a href="<?php echo htmlspecialchars($linkity->admin->base); ?>/settings">
          <i class="icon fas fa-cog"></i>
          Settings
        </a>
      </ul>
    </nav>



    <aside class="top-bar">
      <i class="icon menu-icon fas fa-bars"></i>

      <a tabindex="0" class="btn simple blue new-link" slideover="#new-link"><i class="icon fas fa-plus"></i> New Link</a>

      <form class="search">
        <i class="icon fas fa-search"></i>
        <input class="input" type="text" name="search" placeholder="Search links">
      </form>

      <a href="<?php echo htmlspecialchars($linkity->admin->base); ?>/profile" class="profile">
        <img src="https://www.gravatar.com/avatar/<?php echo md5($user["email"]); ?>.jpg?s=100&d=<?php echo urlencode($linkity->admin->base."/assets/profile.png"); ?>" alt="Profile">
      </a>

      <i class="icon search-icon fas fa-search"></i>
    </aside>



    <main class="page">
    </main>



    <footer class="footer">
      <ul class="menu">
        <li class="copyright">With <i class="icon fas fa-heart"></i> from <a href="https://devuncoded.com" target="_blank">DevUncoded</a> </li>
      </ul>
    </footer>





    <aside class="slideover" id="new-link">
      <div class="shortened">
        <h1 class="shortened-link">x</h1>
        <a class="link shorten-back"><i class="icon back fas fa-arrow-left"></i> Shrink another</a>
      </div>

      <form class="link-wrapper">
        <input class="input" type="text" name="url" placeholder="website.com" spellcheck="false" required>
        <div class="shortname">
          <?php echo $linkity->domain; ?>/<input type="text" name="shortname" placeholder="shortname" spellcheck="false">
        </div>
        <button class="btn white" style="color: #5082ff;">Shorten</button>
      </form>
    </aside>



    <aside class="dropdown" id="link-dropdown">
      <ul class="dropdown-list">
        <a class="danger delete-link" tabindex="0">
          Delete
        </a>
      </ul>
    </aside>



    <aside class="card popup" id="change-password">
      <h1 class="title">Change Password</h1>
      <form>
        <div class="grey">
          <div class="group">
            <label>New Password</label>
            <input class="input" type="password" name="password" placeholder="••••••••">
          </div>
        </div>
        <div class="btns-right">
          <button class="btn">Change</button>
        </div>
      </form>
    </aside>



    <script>
    var name = "<?php echo htmlspecialchars($linkity->name); ?>";
    var base = "<?php echo htmlspecialchars($linkity->admin->base); ?>";
    var basePath = "<?php echo htmlspecialchars($linkity->admin->path); ?>";
    var user = {
      name: "<?php echo htmlspecialchars($user["name"]); ?>",
      email: "<?php echo htmlspecialchars($user["email"]); ?>"
    };
    var homepage = <?php if($linkity->homepage) { echo "true"; } else { echo "false"; }  ?>;
    var domain = "<?php if(defined("DOMAIN")) { echo htmlspecialchars(DOMAIN); } ?>";
    var path = "<?php echo htmlspecialchars($linkity->path); ?>";
    </script>
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
    <script src="<?php echo $linkity->admin->base; ?>/assets/app.js"></script>
  </body>
</html>
