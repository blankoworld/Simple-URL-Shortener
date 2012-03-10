<?php

/** Simple Url Shortener 
  * Copyright (C) 2010 Hyacinthe Cartiaux <hyacinthe.cartiaux@free.fr>
  *
  * This program is free software: you can redistribute it and/or modify
  * it under the terms of the GNU Affero General Public License as
  * published by the Free Software Foundation, either version 3 of the
  * License, or (at your option) any later version.
  *
  * This program is distributed in the hope that it will be useful,
  * but WITHOUT ANY WARRANTY; without even the implied warranty of
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  * GNU Affero General Public License for more details.

  * You should have received a copy of the GNU Affero General Public License
  * along with this program.  If not, see <http://www.gnu.org/licenses/>.
  */

error_reporting(E_ALL);

/* Class used to convert base 10 id to base 66 code used in URI */
require_once("Base10Convert.php");

/* Config */

$TITLE  = "G&eacute;n&eacute;rateur d'adresses courtes";
$SITE   = "http://b4n.fr"; // Full path to the script
$PAGE_CONTENT = "";
$SOURCE = "http://www.0xf.fr/shorturl.tar.bz2";
$BLK_SOURCE = "https://github.com/blankoworld/Simple-URL-Shortener";

$FILE   = basename($_SERVER['PHP_SELF']);
$DBFILE = "index.db"; /* basename($FILE, ".php") . ".db";*/

/* Database object */
if (!file_exists($DBFILE))
{
  /* Create the DB*/
  $dbHandle = new SQLiteDatabase($DBFILE, 0666, &$error);
  createdb($dbHandle);
}
if (!isset($dbHandle))
  $dbHandle = new SQLiteDatabase($DBFILE);

/* Short Functions */

/* Create table shorturl in database, called if the database does not exist */
function createdb($dbHandle)
{
  $sql = "CREATE TABLE shorturl (
			id INTEGER AUTOINCREMENT,
			url VARCHAR NOT NULL,
			Primary Key(id)
			);";

  return $dbHandle->queryExec($sql);
}

/* Get the URL from DB with the code */
function geturl($dbHandle, $code)
{

  $code = sqlite_escape_string($code);

  try {
    $convert = new Base10Convert();
    $id = $convert->decode($code);
  }
  catch (Exception $e)
  {
    return FALSE;
  }

  $sql = "SELECT url 
	  FROM shorturl
	  WHERE id='". $id ."';";

  $result = $dbHandle->query($sql);
	
  if ($result->numRows() == 1)
    return $result->fetchSingle();
  else
    return FALSE;
}

/* Insert the URL in DB and return its code or FALSE */
function shortenurl($dbHandle, $url)
{

  // Check if the url is already in the database
  $sql = "SELECT COUNT(*) 
	  FROM shorturl
	  WHERE url='". $url ."';";

  $result = $dbHandle->query($sql);
  $count = $result->fetchSingle();

  // if there is no record for the url, we insert it
  if ($count == 0)
  {
    /* Very basic URL validation */
    if(!preg_match("!^[a-z0-9]*://.*!i", $url))
      return FALSE;

    $url = sqlite_escape_string($url);

    $sql = "INSERT INTO shorturl (url) 
	    VALUES ('" . $url . "');";

    $dbHandle->queryExec($sql);
  }

  // and we get the id
  $sql = "SELECT id 
	  FROM shorturl
	  WHERE url='". $url ."';";

  $result = $dbHandle->query($sql);

  $id = $result->fetchSingle();

  try {
    $convert = new Base10Convert();
    return $convert->encode((int) $id);
  }
  catch (Exception $e)
  {
    return FALSE;
  }
}

$PAGE_CONTENT = "";

if (isset($_GET['p']))
{
  if ($url = geturl($dbHandle, $_GET['p']))
  {
    header("Location: ". $url);
    exit();
  }
  else
  {
    $PAGE_CONTENT .= "<h2 class=\"error\">Cette adresse n'existe pas !</h2>";
  }
}
else if (isset($_POST['url']))
{
  if ($code = shortenurl($dbHandle, $_POST['url']))
  {
    $PAGE_CONTENT .= '<h2 class="success">'. $SITE . '/' . $code .'</h2>';
  }
  else
  {
    $PAGE_CONTENT .= "<h2 class=\"error\">Erreur durant la proc&eacute;dure de raccourcissement de l'URL soumise !</h2>";
  }
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title><?php echo $TITLE ?></title>
  <link href="style.css" rel="stylesheet" title="default" type="text/css" />
</head>
<body>

<div id="container">

  <h1>G&eacute;n&eacute;rateur d'adresses courtes</h1>

  <?php echo $PAGE_CONTENT ?>

  <form action="#" method="post">
    <fieldset>
      <label>Entrez une adresse&nbsp;: </label>
      <input type="text" name="url" value="http://" />
      <input type="submit" value="La rendre plus courte !" />
    </fieldset>
  </form>

  <h4>
    Propuls&eacute; par <a href="<?php echo $SOURCE ?>">Simple Url Shortener</a> <br />
    Minimalist PHP script using SQLite, GNU AGPL V3<br />
    &copy; 2010 Hyacinthe Cartiaux &lt;hyacinthe.cartiaux (a) free.fr&gt;<br />
    Modifi&eacute; sur <a href="<?php echo $BLK_SOURCE ?>">Github</a>.
    Th&egrave;me par <a href="http://m.b4n.fr/">Blankoworld</a>
  </h4>

</div>

</body>
</html>
