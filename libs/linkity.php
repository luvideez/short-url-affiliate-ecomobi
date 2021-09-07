<?php


//Load required libraries.
require_once(dirname(__FILE__)."/Medoo/Medoo.php");
require_once(dirname(__FILE__)."/Parser-PHP/bootstrap.php");



/*
The Linkity Class.
+ Handles installation.
+ Link actions.
+ User actions.
+ Database connections.
*/
class Linkity {

  function __construct() {
    $this->installing = false;
    $this->config_file = dirname(__FILE__) . "/../config.php";

    //First check if a config file exists & load it.
    if(!file_exists($this->config_file)) { return $this->install(); }
    require_once($this->config_file);
    if(!defined("CONFIGURED") || !CONFIGURED) { return $this->install(); }

    //Set up defaults.
    $db = ["type" => "sqlite", "file" => null];
    if(defined("DB_TYPE")) {
      $db["type"] = str_replace(" ", "", strtolower(DB_TYPE));
      if($db["type"] == "postgressql" || $db["type"] == "postgres") { $db["type"] = "pgsql"; }
    }
    if($db["type"] !== "sqlite") {
      $db = array_merge($db, ["host" => "127.0.0.1", "port" => 3306, "user" => "root"]);
      if($db["type"] == "pgsql") { $db["port"] = 5432; $db["user"] = "postgres"; }
      else if($db["type"] == "mssql") { $db["port"] = 1433; }

      if(!defined("DB_NAME")) { throw new Exception("Database name is required. Please set it in the config file."); }
      $db["name"] = DB_NAME;
    }
    else {
      //Handle SQLite database file location.
      if(defined("DB_FILE")) { $db["file"] = DB_FILE; }
      else { $db["file"] = dirname(__FILE__) . "/../database/Linkity.db"; }
      if(!file_exists(dirname($db["file"]))) { mkdir(dirname($db["file"]), 0770, true); }
    }
    if(defined("DB_USER")) { $db["user"] = DB_USER; }
    if(defined("DB_PASS")) { $db["password"] = DB_PASS; }
    if(defined("DB_HOST")) { $db["host"] = DB_HOST; }
    if(defined("DB_PORT")) { $db["port"] = DB_PORT; }

    $this->homepage = true;
    if(defined("HOMEPAGE") && !HOMEPAGE) { $this->homepage = false; }

    //Figure out the domain & prefix path.
    if(defined("DOMAIN")) { $this->domain = DOMAIN; } else { $this->domain = preg_replace("#^www\.(.+\.)#i", "$1", parse_url("http://".$_SERVER["HTTP_HOST"])["host"]); }
    if($_SERVER["SERVER_PORT"] && $_SERVER["SERVER_PORT"] != 80 && $_SERVER["SERVER_PORT"] != 443) { $this->domain = $this->domain.":".$_SERVER["SERVER_PORT"]; }
    if(defined("PATH")) { $this->path = rtrim(ltrim(PATH, "/"), "/"); } else { $this->path = ""; }

    //Figure out the base & path at which admin should be served.
    $this->admin = new Admin();
    $adminPath = "/admin";
    if($this->path) { $adminPath = "/".$this->path.$adminPath; }
    $this->admin->path = $adminPath;
    $this->admin->base = getHTTP()."://".$this->domain.$adminPath;

    //Get name.
    if(defined("NAME")) { $this->name = htmlspecialchars(NAME); } else { $this->name = "Linkity"; }

    //Connect to the database.
    $this->dbSettings = $db;
    $this->db = new Medoo\Medoo(["charset" => "utf8", "collation" => "utf8_unicode_ci", "database_type" => $db["type"], "database_name" => $db["name"], "username" => $db["user"], "password" => $db["password"], "server" => $db["host"], "port" => $db["port"], "database_file" => $db["file"]]);
  }





  /********************************** LINKS **********************************/


  /*
  Handles creating a link.
  */
  function createLink($url, $shortname = false, $user = false) {

    //Check if URL is valid.
    if(strpos($url, ".") === false) { throw new Exception("Please enter a valid URL"); }
    $url = preg_replace("/^(?!https?:\/\/)/", "http://", $url);
    if(!filter_var($url, FILTER_VALIDATE_URL)) { throw new Exception("Please enter a valid URL"); }

    //Get user details.
    $domain = $this->domain;
    if($user) {
      $user = $this->db->get("users", "*", ["id" => $user]);
      if(!$user || $user["disabled"]) { throw new Exception("No such user"); }
      if($user["domain"]) { $domain = $user["domain"]; }
    }

    //Create a random shortname or check if the provided one is available.
    if(!$shortname) {
      $unique = false;
      while(!$unique) {
        $shortname = randomShortname();
        if(!$this->db->has("links", ["shortname" => $shortname, "domain" => $domain])) { $unique = true; }
      }
    }
    else {
      if($this->db->has("links", ["shortname" => $shortname, "domain" => $domain])) { throw new Exception("This shortname has been taken already"); }
    }

    $link = [
      "id" => generate("lnk_"),
      "shortname" => $shortname,
      "domain" => $domain,
      "redirect" => $url,
      "created" => time()
    ];
    if($user && $user["id"]) { $link["user"] = $user["id"]; }

    $this->db->insert("links", $link);
    return $link;
  }



  /*
  Resolves a link using shortname & domain.
  */
  function resolveLink($shortname = false, $domain = false) {
    if(!$shortname) { $shortname = rtrim(ltrim(parse_url($_SERVER["REQUEST_URI"])["path"], "/"), "/"); }
    if($this->path) { $shortname = ltrim(rtrim(preg_replace("/^".preg_quote($this->path, "/")."/", "", $shortname), "/"), "/"); }
    if(!$domain) { $domain = $this->domain; }

    $link = $this->db->get("links", "*", ["shortname" => $shortname, "domain" => $domain]);
    if(!$link) { throw new Exception("No such link"); }

    return $link;
  }



  /*
  Retrieves a link.
  */
  function getLink($id) {
    $link = $this->db->get("links", "*", ["id" => $id]);
    if(!$link) { throw new Exception("No such link"); }

    return $link;
  }



  /*
  Updates a link.
  */
  function updateLink($id, $data = []) {
    $link = $this->db->get("links", "*", ["id" => $id]);
    if(!$link) { throw new Exception("No such link"); }

    if($data["redirect"]) {
      if(strpos($data["redirect"], ".") === false) { throw new Exception("Please enter a valid URL"); }
      $data["redirect"] = preg_replace("/^(?!https?:\/\/)/", "http://", $data["redirect"]);
      if(!filter_var($data["redirect"], FILTER_VALIDATE_URL)) { throw new Exception("Please enter a valid URL"); }
      $link["redirect"] = $data["redirect"];
    }

    $this->db->update("links", $link, ["id" => $id]);
    return $link;
  }



  /*
  Deletes a link.
  */
  function deleteLink($id) {
    $link = $this->db->delete("links", ["id" => $id]);
    if($link->rowCount() < 1) { throw new Exception("No such link"); }
    $this->db->delete("views", ["link" => $id]);

    return true;
  }



  /*
  Lists links.
  */
  function listLinks($limit = 20, $skip = 0) {
    $links = $this->db->select("links", "*", ["ORDER" => ["created" => "DESC"], "LIMIT" => [$skip, $limit]]);

    $result = ["data" => $links, "has_more" => false];
    if(count($links) == $limit && $this->db->select("links", "*", ["ORDER" => ["created" => "DESC"], "LIMIT" => [($skip + 1), 1]])) { $result["has_more"] = true; }

    return $result;
  }





  /********************************** VIEWS **********************************/


  /*
  Saves a view for a link.
  */
  function saveView($link) {
    $ip = getIP();
    $agent = new WhichBrowser\Parser(getallheaders());

    $view = [
      "link" => $link["id"],
      "country" => getCountry($ip),
      "ip" => $ip,
      "browser" => $agent->browser->name,
      "os" => $agent->os->name,
      "type" => $agent->device->type,
      "user_agent" => $_SERVER["HTTP_USER_AGENT"],
      "created" => time(),
      "date_day" => date("j F Y"),
      "date_month" => date("F Y")
    ];
    if($link["user"]) { $view["user"] = $link["user"]; }
    if(array_key_exists("HTTP_REFERER", $_SERVER)) {
      $view["referrer"] = $_SERVER["HTTP_REFERER"];
      $view["referrer_domain"] = parse_url($_SERVER["HTTP_REFERER"])["host"];
    }

    $this->db->insert("views", $view);
    return $view;
  }



  /*
  Creates a summary of views on a link.
  */
  function getViews($link = false, $user = false, $since = 0, $till = false) {
    $min = $since;
    if($till) { $max = $till; } else { $max = time(); }

    //Fetch data.
    $where = "AND created BETWEEN :min AND :max"; $vals = ["min" => $min, "max" => $max]; $whereArr = [];
    if($link) { $where .= " AND <link> = :link"; $vals["link"] = $link; $whereArr["link"] = $link; }
    if($user) { $where .= " AND <user> = :user"; $vals["user"] = $user; $whereArr["user"] = $user; }

    $totalViews = $this->db->count("views", $whereArr);
    $whereArr["created[<>]"] = [$min, $max];
    $views = $this->db->count("views", $whereArr);

    $countriesData = $this->db->query("SELECT <country>, COUNT(*) FROM <views> WHERE <country> != '' ".$where." GROUP BY <country>", $vals)->fetchAll();
    $browsersData = $this->db->query("SELECT <browser>, COUNT(*) FROM <views> WHERE <browser> != '' ".$where." GROUP BY <browser>", $vals)->fetchAll();
    $osData = $this->db->query("SELECT <os>, COUNT(*) FROM <views> WHERE <os> != '' ".$where." GROUP BY <os>", $vals)->fetchAll();
    $typesData = $this->db->query("SELECT <type>, COUNT(*) FROM <views> WHERE <type> != '' ".$where." GROUP BY <type>", $vals)->fetchAll();
    $referrersData = $this->db->query("SELECT <referrer_domain>, COUNT(*) FROM <views> WHERE <referrer_domain> != '' ".$where." GROUP BY <referrer_domain>", $vals)->fetchAll();

    //Clean data.
    $countries = []; $browsers = []; $os = []; $types = []; $referrers = []; $selector = 1;
    if(array_key_exists("count", $countriesData[0])) { $selector = "count"; }
    else if(array_key_exists("COUNT(*)", $countriesData[0])) { $selector = "COUNT(*)"; }
    foreach ($countriesData as $data) { $countries[$data["country"]] = $data[$selector]; }
    foreach ($browsersData as $data) { $browsers[$data["browser"]] = $data[$selector]; }
    foreach ($osData as $data) { $os[$data["os"]] = $data[$selector]; }
    foreach ($typesData as $data) { $types[ucfirst($data["type"])] = $data[$selector]; }
    foreach ($referrersData as $data) { $referrers[$data["referrer_domain"]] = $data[$selector]; }
    arsort($countries); arsort($browsers); arsort($os); arsort($types); arsort($referrers);

    return ["count" => $views, "total_count" => $totalViews, "countries" => $countries, "browsers" => $browsers, "operating_systems" => $os, "types" => $types, "referrers" => $referrers];
  }





  /********************************** USERS **********************************/


  /*
  Creates a user.
  */
  function createUser($email, $password, $role = "admin", $name = "") {
    $email = strtolower($email);
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) { throw new Exception("Please enter a valid email"); }
    if($this->db->has("users", ["email" => $email])) { throw new Exception("A user with this email address already exists"); }
    if(strlen($password) < 6) { throw new Exception("Password must be at least 6 characters long"); }

    $user = [
      "id" => generate("usr_"),
      "role" => $role,
      "name" => $name,
      "email" => $email,
      "password" => password_hash($password, PASSWORD_DEFAULT),
      "created" => time()
    ];

    $this->db->insert("users", $user);
    return $user;
  }



  /*
  Updates a user.
  */
  function updateUser($id, $data = []) {
    $user = $this->db->get("users", "*", ["id" => $id]);
    if(!$user || $user["disabled"]) { throw new Exception("No such user"); }

    if($data["email"]) {
      $data["email"] = strtolower($data["email"]);
      if(!filter_var($data["email"], FILTER_VALIDATE_EMAIL)) { throw new Exception("Please enter a valid email"); }
      if($data["email"] !== $user["email"] && $this->db->has("users", ["email" => $data["email"]])) { throw new Exception("A user with this email address already exists"); }
      $user["email"] = $data["email"];
    }
    if($data["name"]) {
      $user["name"] = $data["name"];
    }
    if($data["password"]) {
      if(strlen($data["password"]) < 6) { throw new Exception("Password must be at least 6 characters long"); }
      $user["password"] = password_hash($data["password"], PASSWORD_DEFAULT);
    }

    $this->db->update("users", $user, ["id" => $id]);
    return $user;
  }



  /*
  Creates a login session.
  */
  function login($email, $password) {
    $email = strtolower($email);
    $user = $this->db->get("users", "*", ["email" => $email]);
    if(!$user || $user["disabled"]) { throw new Exception("No user with this email address exists"); }
    if(!password_verify($password, $user["password"])) { throw new Exception("Incorrect password"); }

    $ip = getIP();
    $ssn = [
      "id" => generate("ssn_", 80, 100),
      "status" => "active",
      "user" => $user["id"],
      "ip" => $ip,
      "country" => getCountry($ip),
      "user_agent" => $_SERVER["HTTP_USER_AGENT"],
      "created" => time()
    ];

    setcookie("ssn", $ssn["id"], time() + (10 * 365 * 24 * 60 * 60), "/");
    $_COOKIE["ssn"] = $ssn["id"];
    $this->db->insert("login_sessions", $ssn);
    return ["user" => $user, "session" => $ssn];
  }



  /*
  Resolves a login session to a user.
  */
  function resolveLogin($ssn = false) {
    if(!$ssn) { $ssn = $_COOKIE["ssn"]; }
    if(!$ssn) { throw new Exception("You're not logged in"); }

    $ssn = $this->db->get("login_sessions", "*", ["id" => $ssn]);
    if(!$ssn || $ssn["status"] !== "active") {
      unset($_COOKIE["ssn"]);
      setcookie("ssn", "", time() - 3600, "/");
      throw new Exception("You're not logged in");
    }

    $user = $this->db->get("users", "*", ["id" => $ssn["user"]]);
    if(!$user || $user["disabled"]) {
      unset($_COOKIE["ssn"]);
      setcookie("ssn", "", time() - 3600, "/");
      throw new Exception("You're not logged in");
    }

    return ["user" => $user, "session" => $ssn];
  }



  /*
  Logs out a login session.
  */
  function logout($ssn = false) {
    if(!$ssn) { $ssn = $_COOKIE["ssn"]; }
    if(!$ssn) { return true; }

    $this->db->update("login_sessions", ["status" => "logged_out"], ["id" => $ssn]);
    unset($_COOKIE["ssn"]);
    setcookie("ssn", "", time() - 3600, "/");
    return true;
  }





  /********************************** INSTALLATION **********************************/


  /*
  Handles the installation flow.
  */
  function install() {
    $this->installing = true;

    require_once(dirname(__FILE__)."/../admin/installation.php");
  }



  /*
  Writes the config file.
  */
  function config($new = []) {
    if(isset($new["name"])) { $this->name = $new["name"]; }
    if(isset($new["homepage"])) { $this->homepage = $new["homepage"]; }

    $dbSettings = "";
    if($new["db"]) {
      $db = ["type" => "sqlite"];

      if($new["db"]["type"]) {
        $dbSettings .= "\n".'define("DB_TYPE", "'.htmlspecialchars($new["db"]["type"]).'"); //The database type.';

        $db["type"] = str_replace(" ", "", strtolower($new["db"]["type"]));
        if($db["type"] == "postgressql" || $db["type"] == "postgres") { $db["type"] = "pgsql"; }
      }
      if($new["db"]["name"]) {
        $dbSettings .= "\n".'define("DB_NAME", "'.htmlspecialchars($new["db"]["name"]).'"); //The database name.';

        $db["name"] = $new["db"]["name"];
      }
      if($new["db"]["user"]) {
        $dbSettings .= "\n".'define("DB_USER", "'.htmlspecialchars($new["db"]["user"]).'"); //The database access username.';

        $db["user"] = $new["db"]["user"];
      }
      if($new["db"]["password"]) {
        $dbSettings .= "\n".'define("DB_PASS", "'.htmlspecialchars($new["db"]["password"]).'"); //The database access password.';

        $db["password"] = $new["db"]["password"];
      }
      if($new["db"]["host"]) {
        $dbSettings .= "\n".'define("DB_HOST", "'.htmlspecialchars($new["db"]["host"]).'"); //The database access host.';

        $db["host"] = $new["db"]["host"];
      }
      if($new["db"]["port"]) {
        $dbSettings .= "\n".'define("DB_PORT", "'.htmlspecialchars($new["db"]["port"]).'"); //The database access port.';

        $db["port"] = $new["db"]["port"];
      }
      if($new["db"]["file"]) {
        $dbSettings .= "\n".'define("DB_FILE", "'.htmlspecialchars($new["db"]["file"]).'"); //The SQLite database file.';

        $db["file"] = $new["db"]["file"];
      }

      if($db["type"] == "sqlite") {
        //Handle SQLite database file location.
        if(!$db["file"]) { $db["file"] = dirname(__FILE__) . "/../database/Linkity.db"; }
        if(!file_exists(dirname($db["file"]))) { mkdir(dirname($db["file"]), 0770, true); }
      }
      else {
        if(!$db["host"]) { $db["host"] = "127.0.0.1"; }
        if(!$db["user"]) {
          $db["user"] = "root";
          if($db["type"] == "pgsql") { $db["user"] = "postgres"; }
        }
        if(!$db["port"]) {
          $db["port"] = 3306;
          if($db["type"] == "pgsql") { $db["port"] = 5432; }
          else if($db["type"] == "mssql") { $db["port"] = 1433; }
        }
      }

      $this->db = new Medoo\Medoo(["charset" => "utf8", "collation" => "utf8_unicode_ci", "database_type" => $db["type"], "database_name" => $db["name"], "username" => $db["user"], "password" => $db["password"], "server" => $db["host"], "port" => $db["port"]]);
      $test = new PDO($this->db->info()["dsn"], $db["user"], $db["password"], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

      $this->createTables();
    }
    else {
      if(defined("DB_TYPE")) { $dbSettings .= "\n".'define("DB_TYPE", "'.htmlspecialchars(DB_TYPE).'"); //The database type.'; }
      if(defined("DB_NAME")) { $dbSettings .= "\n".'define("DB_NAME", "'.htmlspecialchars(DB_NAME).'"); //The database name.'; }
      if(defined("DB_USER")) { $dbSettings .= "\n".'define("DB_USER", "'.htmlspecialchars(DB_USER).'"); //The database access username.'; }
      if(defined("DB_PASS")) { $dbSettings .= "\n".'define("DB_PASS", "'.htmlspecialchars(DB_PASS).'"); //The database access password.'; }
      if(defined("DB_HOST")) { $dbSettings .= "\n".'define("DB_HOST", "'.htmlspecialchars(DB_HOST).'"); //The database access host.'; }
      if(defined("DB_PORT")) { $dbSettings .= "\n".'define("DB_PORT", "'.htmlspecialchars(DB_PORT).'"); //The database access port.'; }
      if(defined("DB_FILE")) { $dbSettings .= "\n".'define("DB_FILE", "'.htmlspecialchars(DB_FILE).'"); //The SQLite database file.'; }
    }

    $urlSettings = "";
    if($new["domain"]) { $urlSettings .= "\n".'define("DOMAIN", "'.htmlspecialchars(preg_replace("#^www\.(.+\.)#i", "$1", parse_url($new["domain"])["host"])).'"); //The domain on which this script runs.'; }
    else if(defined("DOMAIN")) { $urlSettings .= "\n".'define("DOMAIN", "'.htmlspecialchars(DOMAIN).'"); //The domain on which this script runs.'; }
    if($new["path"]) { $urlSettings .= "\n".'define("PATH", "'.htmlspecialchars(rtrim(ltrim($new["path"], "/"), "/")).'"); //The sub directory in which this script runs.'; }
    else if(defined("PATH")) { $urlSettings .= "\n".'define("PATH", "'.htmlspecialchars(PATH).'"); //The sub directory in which this script runs.'; }


    $config = '<?php
/*
Configuration file for Linkity.


NOTE: You probably don\'t need to change this file manually, our installer will make the changes for you. Just go to your server\'s URL!


NOTE 2: For ease of use, Linkity will use a self-contained SQLite database by default. There\'s no need to set up a database separately if you don\'t need extreme performance.


Constants:
+ CONFIGURED - Boolean, set this to true once you\'ve configured the constants below.

+ NAME - The script\'s name. Defaults to Linkity.

+ HOMEPAGE - True/false. Should a homepage to shorten links be displayed?

+ DB_TYPE - The type of database you\'re using. Valid values are SQLite (Default), MySQL, PostgreSQL, MariaDB & MSSQL.
+ DB_NAME - The name of your database. Required for all databases except SQLite.
+ DB_USER - The username for accessing your database. Defaults to root for MySQL/MariaDB/MSSQL & postgres for PostgreSQL.
+ DB_PASS - The password of the user for accessing your database. Defaults to empty.
+ DB_HOST - The IP or URL where your database listens for connections Defaults to localhost (127.0.0.1).
+ DB_PORT - The port on which your database listens for connections Defaults to 3306 for MySQL/MariaDB, 5432 for PostgreSQL & 1433 for MSSQL.
+ DB_FILE - Only used for SQLite. The file where the database is stored Defaults to ./database/Linkity.db.

+ DOMAIN - Optional. The default domain on which this script runs. Defaults to the domain from which first request comes.
+ PATH - Optional. A path to ignore while resolving any shortnames. Useful if this script is in a subdirectory.

*/


define("CONFIGURED", true); //Set this to true once you\'re finished with the configuration.

define("NAME", "'.htmlspecialchars($this->name).'"); //This app\'s name.

define("HOMEPAGE", '.($this->homepage ? 'true' : 'false' ).'); //Should the homepage be displayed?



//Database settings.'.$dbSettings.'



//URL settings.'.$urlSettings;


    //Write to the config file.
    $success = file_put_contents($this->config_file, $config);
    if(!$success) { throw new \Exception("Automatically writing config file failed"); }

    //Make sure htaccess files also exists.
    file_put_contents(dirname(__FILE__) . "/../.htaccess", "RewriteEngine on\nErrorDocument 404 /redirect.php\n\nRewriteCond %{REQUEST_FILENAME} !-f\nRewriteCond %{REQUEST_FILENAME} !-d\nRewriteRule .* redirect.php [QSA,L]");
    file_put_contents(dirname(__FILE__) . "/../admin/.htaccess", "RewriteEngine on\nErrorDocument 404 /admin/index.php\n\nRewriteCond %{REQUEST_FILENAME} !-d\nRewriteCond %{REQUEST_FILENAME}\.php -f\nRewriteRule ^(.*)$ $1.php\n\nRewriteCond %{REQUEST_FILENAME} !-f\nRewriteCond %{REQUEST_FILENAME} !-d\nRewriteRule .* index.php [QSA,L]");

    return true;
  }



  /*
  Creates the database tables used by us.
  */
  function createTables() {

    $this->db->query('CREATE TABLE IF NOT EXISTS <links> (
    "id" text NOT NULL,
    "redirect" text,
    "shortname" text,
    "domain" text,
    "created" numeric,
    "user" text
    )');

    $this->db->query('CREATE TABLE IF NOT EXISTS <views> (
    "link" text,
    "user" text,
    "country" text,
    "browser" text,
    "os" text,
    "type" text,
    "ip" text,
    "user_agent" text,
    "referrer" text,
    "referrer_domain" text,
    "created" numeric,
    "date_day" text,
    "date_month" text
    )');

    $this->db->query('CREATE TABLE IF NOT EXISTS <users> (
    "id" text NOT NULL,
    "role" text,
    "name" text,
    "email" text,
    "password" text,
    "domain" text,
    "created" numeric
    )');

    $this->db->query('CREATE TABLE IF NOT EXISTS <login_sessions> (
    "id" text NOT NULL,
    "status" text,
    "user" text,
    "ip" text,
    "country" text,
    "user_agent" text,
    "created" numeric
    )');

    $this->db->query('CREATE TABLE IF NOT EXISTS <nonces> (
    "token" text,
    "created" numeric
    )');

    return true;
  }



}



//Create an empty admin class.
class Admin {}





/********************************************* HELPER FUNCTIONS **********************************************/


/*
Creates a random shortname.
*/
function randomShortname($min = 3, $max = 7) {
  $length = rand($min, $max);
  $chars = array_merge(range("a", "z"), range("A", "Z"), range("0", "9"));
  $max = count($chars) - 1;
  for($i = 0; $i < $length; $i++) {
    $char = random_int(0, $max);
    $url .= $chars[$char];
  }
  return $url;
}



/*
Generates IDs.
*/
function generate($prefix = false, $min = 22, $max = 31) {
  $length = rand($min, $max);
  $chars = array_merge(range("a", "z"), range("A", "Z"), range("0", "9"));
  $max = count($chars) - 1;
  for($i = 0; $i < $length; $i++) {
    $char = random_int(0, $max);
    $id .= $chars[$char];
  }

  if($prefix) { $id = $prefix.$id; }
  return $id;
}



/*
Retrieves the client's IP address.
*/
function getIP() {
  if(isset($_SERVER["HTTP_CF_CONNECTING_IP"])) { return $_SERVER["HTTP_CF_CONNECTING_IP"]; }
  else if(isset($_SERVER["HTTP_CLIENT_IP"])) { return $_SERVER["HTTP_CLIENT_IP"]; }
  else if(isset($_SERVER["HTTP_X_FORWARDED_FOR"])) { return $_SERVER["HTTP_X_FORWARDED_FOR"]; }
  else if(isset($_SERVER["HTTP_X_FORWARDED"])) { return $_SERVER["HTTP_X_FORWARDED"]; }
  else if(isset($_SERVER["HTTP_FORWARDED_FOR"])) { return $_SERVER["HTTP_FORWARDED_FOR"]; }
  else if(isset($_SERVER["HTTP_FORWARDED"])) { return $_SERVER["HTTP_FORWARDED"]; }
  else if(isset($_SERVER["REMOTE_ADDR"])) { return $_SERVER["REMOTE_ADDR"]; }
}



/*
Gets the country of the visitor.
*/
function getCountry($ip = false) {
  if(!$ip) { $ip = getIP(); }

  if(isset($_SERVER["HTTP_CF_IPCOUNTRY"])) { return $_SERVER["HTTP_CF_IPCOUNTRY"]; }
  else if(filter_var($ip, FILTER_VALIDATE_IP)) {
    //Use a GEO IP service.
    try {
      $geo = json_decode(file_get_contents("http://ip-api.com/json/$ip"));
      return $geo->countryCode;
    } catch (\Exception $e) {}
  }
}



/*
Returns the protocol being used.
*/
function getHTTP() {
  if(isset($_SERVER["HTTPS"])) { return "https"; }
  else if($_SERVER["HTTP_CF_VISITOR"] && json_decode($_SERVER["HTTP_CF_VISITOR"])->scheme == "https") {
    return "https";
  }
  else if($_SERVER["HTTP_X_FORWARDED_PROTO"] && $_SERVER["HTTP_X_FORWARDED_PROTO"] == "https") {
    return "https";
  }
  return "http";
}



/*
Polyfill for the getallheaders() function.
*/
if(!function_exists("getallheaders")) {
  function getallheaders() {
    $headers = [];
    foreach ($_SERVER as $name => $value) {
      if(substr($name, 0, 5) == "HTTP_") {
        $headers[str_replace(" ", "-", ucwords(strtolower(str_replace("_", " ", substr($name, 5)))))] = $value;
      }
    }
    return $headers;
  }
}
