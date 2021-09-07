<?php
/*
Configuration file for Linkity.


NOTE: You probably don't need to change this file manually, our installer will make the changes for you. Just go to your server's URL!


NOTE 2: For ease of use, Linkity will use a self-contained SQLite database by default. There's no need to set up a database separately if you don't need extreme performance.


Constants:
+ CONFIGURED - Boolean, set this to true once you've configured the constants below.

+ NAME - The script's name. Defaults to Linkity.

+ HOMEPAGE - True/false. Should a homepage to shorten links be displayed?

+ DB_TYPE - The type of database you're using. Valid values are SQLite (Default), MySQL, PostgreSQL, MariaDB & MSSQL.
+ DB_NAME - The name of your database. Required for all databases except SQLite.
+ DB_USER - The username for accessing your database. Defaults to root for MySQL/MariaDB/MSSQL & postgres for PostgreSQL.
+ DB_PASS - The password of the user for accessing your database. Defaults to empty.
+ DB_HOST - The IP or URL where your database listens for connections Defaults to localhost (127.0.0.1).
+ DB_PORT - The port on which your database listens for connections Defaults to 3306 for MySQL/MariaDB, 5432 for PostgreSQL & 1433 for MSSQL.
+ DB_FILE - Only used for SQLite. The file where the database is stored Defaults to ./database/Linkity.db.

+ DOMAIN - Optional. The default domain on which this script runs. Defaults to the domain from which first request comes.
+ PATH - Optional. A path to ignore while resolving any shortnames. Useful if this script is in a subdirectory.

*/


define("CONFIGURED", false); //Set this to true once you're finished with the configuration.

define("NAME", "Linkity"); //This app's name.

define("HOMEPAGE", true); //Should the homepage be displayed?



//Database settings.
define("DB_TYPE", "sqlite"); //The database type.



//URL settings.
