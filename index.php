<?php
/*
The home page which allows anyone to shorten a link.
*/


//Load Linkity.
require_once(dirname(__FILE__)."/libs/linkity.php");
$linkity = new Linkity();
if($linkity->installing) { exit; }



//Redirect to admin if homepage isn't allowed.
if(!$linkity->homepage) { header("Location: ".$linkity->admin->base, true, 302); exit; }



//Handle link creation.
if(isset($_GET["url"])) {
  header("Content-Type: application/json");
  try {
    //Check nonce token exists & delete it.
    $deleted = $linkity->db->delete("nonces", ["token" => $_GET["nonce"]])->rowCount();
    if(!$deleted) {
      http_response_code(400);
      echo json_encode(["error" => "Please refresh this page"]);
      exit;
    }

    //Create link & send it back with a new token.
    $aff= 'https://t.ecomobi.com/?token=CefIfyCeUQxGSluumWMaG&url=';
    $link1 = $aff.$_GET["url"];
    $link = $linkity->createLink($link1);
    echo json_encode(["link" => $link, "nonce" => generateNonce()]);

  } catch (\Exception $e) { http_response_code(400); echo json_encode(["error" => $e->getMessage(), "nonce" => generateNonce()]); }
  exit;
}



/*
Generates an nonce token.
*/
function generateNonce() {
  global $linkity;
  $nonce = generate("nonce_", 35, 75);
  $linkity->db->insert("nonces", ["token" => $nonce, "created" => time()]);

  return $nonce;
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="robots" content="index, follow">
    <meta name="description" content="Shorten a URL using <?php echo htmlspecialchars($linkity->name); ?>. A free URL shortener. Powered by DevUncoded's Linkity."/>

    <title>Home | <?php echo htmlspecialchars($linkity->name); ?></title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha256-YLGeXaapI0/5IgZopewRJcFXomhRMlYYjugPLSyNjTY=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito+Sans:400,600">
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
        background: #000;
        color: #000;
        width: 100%;
        overflow-x: hidden;

        font-family: "Nunito Sans", "Segoe UI", "Montserrat", "Helvetica Neue", "Helvetica", sans-serif;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }



    .bg {
      position: fixed;
      z-index: -1;
      top: 0; left: 0;
      height: 100%; width: 100%;
      opacity: 0;
      transition: opacity 0.8s ease;
    }

    .bg.active {
      opacity: 0.7;
    }



    .page {
      position: absolute;
      z-index: 1;
      top: 50%; left: 50%;
      transform: translate(-50%, -50%);
      margin-top: 100px; opacity: 0;
      transition: margin-top 0.5s ease, opacity 0.35s ease;
      color: #fff;
    }

    .page.active {
      margin-top: 0;
      opacity: 1;
    }



    .shorten {
      position: relative;
      z-index: 2;
      width: 500px;
      max-width: 90vw;
      margin: 20px auto;
    }

    .shorten.active, .shorten.active input, .shorten.active button {
      cursor: progress;
    }

    .shorten input {
      border: 0;
      background: rgba(255, 255, 255, 0.2);
      padding: 10px 20px;
      border-radius: 100px;
      width: 100%;
      outline: 0;
      color: #fff;
      font-weight: 600;
    }

    .shorten input::placeholder {
      color: #ccd1d5;
    }

    .shorten button {
      position: absolute;
      z-index: 2;
      top: 0; right: 0;
      padding: 10px 20px;
      border-top-right-radius: 100px;
      border-bottom-right-radius: 100px;
      border: 0;
      background-color: #fff;
      transition: background-color 0.4s ease;
    }

    .shorten button:hover, .shorten button:focus {
      background-color: #dadada;
    }



    .shortened {
      margin: 30px 0;
      color: #5082ff;
    }



    .footer {
      position: absolute;
      z-index: 1;
      bottom: 10px; left: 50%;
      transform: translateX(-50%);
      color: #b4b5b5;
      line-height: 1.5em;
      font-size: 14px;
    }







    .tippy-popper[x-placement^=top] .tippy-tooltip.light-theme .tippy-arrow {
      border-top: 8px solid #fff;
      border-right: 8px solid transparent;
      border-left: 8px solid transparent
    }

    .tippy-popper[x-placement^=bottom] .tippy-tooltip.light-theme .tippy-arrow {
      border-bottom: 8px solid #fff;
      border-right: 8px solid transparent;
      border-left: 8px solid transparent
    }

    .tippy-popper[x-placement^=left] .tippy-tooltip.light-theme .tippy-arrow {
      border-left: 8px solid #fff;
      border-top: 8px solid transparent;
      border-bottom: 8px solid transparent
    }

    .tippy-popper[x-placement^=right] .tippy-tooltip.light-theme .tippy-arrow {
      border-right: 8px solid #fff;
      border-top: 8px solid transparent;
      border-bottom: 8px solid transparent
    }

    .tippy-tooltip.light-theme {
      padding: 8px 15px;
      font-size: 16px;
      line-height: 1.5em;
      color: #46596a;
      box-shadow: 0px 2px 80px -6px rgba(0,0,0,0.28);
      background-color: #fff;
      border-radius: 5px;
    }

    .tippy-tooltip.light-theme .tippy-backdrop {
      background-color: #fff
    }

    .tippy-tooltip.light-theme .tippy-roundarrow {
      fill: #fff
    }

    .tippy-tooltip.light-theme[data-animatefill] {
      background-color: transparent
    }





    /******************** COPY STYLING ********************/


    [copy] {
      cursor: pointer;
      outline: none;
      transition: color 0.3s ease;
    }

    [copy]:hover {
      color: #5082ff;
    }

    .tippy-tooltip.copy-theme {
      padding: 4px 10px;
      font-size: 15px;
      background: #fff;
      color: #657a8e;
      border-radius: 3px;
    }

    .copied {
      color: #5082ff;
      font-weight: 600;
    }



    .tippy-tooltip.error-theme {
      background: #ff5b5b;
      color: #fff;
    }

    .tippy-popper[x-placement^=top] .tippy-tooltip.error-theme .tippy-arrow {
      border-top: 8px solid #ff5b5b;
    }

    .tippy-popper[x-placement^=bottom] .tippy-tooltip.error-theme .tippy-arrow {
      border-bottom: 8px solid #ff5b5b;
    }

    .tippy-popper[x-placement^=left] .tippy-tooltip.error-theme .tippy-arrow {
      border-left: 8px solid #ff5b5b;
    }

    .tippy-popper[x-placement^=right] .tippy-tooltip.error-theme .tippy-arrow {
      border-right: 8px solid #ff5b5b;
    }
    </style>
  </head>
  <body>



    <img class="bg" src="https://source.unsplash.com/collection/3178572/1600x900" alt="">



    <main class="page">
      <h1>Shorten a URL</h1>
      <!-- <p>X</p> -->
      
 
      <form class="shorten">
          <input type="text" name="url" placeholder="URL"  value=''>
        <button>Shorten</button> 
		 

      </form>
    </main>



    <footer class="footer">
      &copy; <?php echo date("Y")." ".htmlspecialchars($linkity->name); ?>
    </footer>



    <script src="https://cdn.jsdelivr.net/npm/jquery@3.3.1/dist/jquery.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js" integrity="sha256-CjSoeELFOcH0/uxWu6mC/Vlrc1AARqbm/jiiImDGV3s=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@8.8.2/dist/sweetalert2.all.min.js" integrity="sha256-F35YL6dQdoi9Lri7XPCq9cB0iLetbJiNUfYfcIQwu24=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/tippy.js@3.4.1/dist/tippy.all.min.js" integrity="sha256-iLOTBBYaCzN2utfyApj2yRw3ltH86LwYZrzOz3TTbyg=" crossorigin="anonymous"></script>
    <script> var nonce = "<?php echo htmlspecialchars(generateNonce()); ?>"; </script>
    <script>

    /*
    Transition the page in once everything's loaded.
    */
    $(document).ready(function(e) {
      $(".bg").addClass("active")
      setTimeout(function() {
        $(".page").addClass("active")
        setTimeout(function() {
          $("input").focus()
        }, 400)
      }, 800)
    })



    /*
    Handles shortening a URL.
    */
    $(document).on("submit", ".shorten", function(e) {
      e.preventDefault()
      $(".shorten").addClass("active").find("input").attr("disabled", true)
      var val = $("input[name='url']").val()
      if(!val) { return }
      $.ajax("?url=" + encodeURIComponent(val) + "&nonce=" + encodeURIComponent(nonce), {
        success: function(data) {
          nonce = data.nonce
          var url = safe(data.link.domain + "/" + data.link.shortname)
          Swal.fire({
            title: "Link shortened",
            html: "<h1 class='shortened' copy='http://" + url + "'>" + url + "</h1>",
            type: "success"
          })
          $(".shorten").removeClass("active").find("input").attr("disabled", false)
        },
        error: function(e) {
          if(e.responseJSON.nonce) { nonce = e.responseJSON.nonce }
          var error = e.responseJSON.error, el = $(".shorten input").get(0)
          $(".shorten").removeClass("active").find("input").attr("disabled", false).focus()
          tippy(el, {content: error, animation: "scale", theme: "light error", arrow: true, placement: "bottom"})
          el._tippy.show()
        }
      })
    })



    /*
    Create copy elements.
    */
    setInterval(function() {
      $("[copy]:not(.copy-created)").addClass("copy-created").each(function() {
        tippy(this, {content: "Click to copy", animation: "scale", theme: "light copy", arrow: true, hideOnClick: false, onHidden: function(ins) { ins.setContent("Click to copy") }})
      })
    }, 150)



    /*
    Copies content on copy element click.
    */
    $(document).on("click", "[copy]", function(e) {
      e.preventDefault()
      var el = $(this), tippy = el.get(0)._tippy
      copy(el.attr("copy"))
      tippy.setContent("<span class='copied'>Copied</span>")
    })



    /*
    Handles copying to clipboard.
    */
    function copy(text, skip) {
      if(navigator.clipboard && navigator.clipboard.writeText && !skip) {
        return navigator.clipboard.writeText(text).catch(function(e) {
          return copy(text, true)
        })
      }
      var input = document.createElement("textarea")
      input.value = text
      document.body.appendChild(input)
      input.focus()
      input.select()
      document.execCommand("copy")
      document.body.removeChild(input)
    }



    /*
    Escapes text for safe usage.
    */
    function safe(text) {
      if(!text) { return "" }
      text = String(text).replace(/\&/gi, "&amp;").replace(/\</gi, "&lt;").replace(/\>/gi, "&gt;").replace(/\"/gi, "&quot;").replace(/\'/gi, "&#x27;").replace(/\//gi, "&#x2F;")
      return text
    }
    </script>
  </body>
</html>
