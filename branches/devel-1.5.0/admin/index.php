<?PHP
    require '../includes/master.inc.php';

    // Kick-out users who aren't logged in as an admin
    $auth->admin();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>index</title>
    <link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.5.2/build/reset-fonts-grids/reset-fonts-grids.css">
    <link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.5.2/build/base/base-min.css">
    <link rel="stylesheet" href="<?PHP WEBROOT();?>styles/screen.css" type="text/css" media="screen" title="Screen" charset="utf-8" />
</head>

<body>
    <h1>Admin Area</h1>
    <p>If you can read this then you are logged in as an admin user.</p></body>
</html>