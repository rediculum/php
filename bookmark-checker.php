<html>
<head>
<title>Online Bookmark Checker</title>
<style type="text/css">
        body {
                color: black; background-color: #EEEEEE;
                font-size: 14px;
                font-family: Verdana,sans-serif;
                margin: 0; padding: 1em 0;
                text-align: center;
        }
        h1 { font-size: 12px; }
        h1.error { color: red; }
        h1.ok { color: green; }
        tr {
                font-size: 12px;
                border: 1px solid #CCCCCC;
        }
        tr.row0 { background-color:#DDD; }
        tr.row1 { background-color:#FFF }
        tr.row0:hover ,tr.row1:hover { background-color:#AABBCC; }
</style>
</head>
<body>
This script checks your bookmarks if the links are broken or not.<br>
Please upload your exported bookmarks as .html file
<p>
<? if (isset($_REQUEST[submit])) {
        if ($_FILES["bookmarks"]["type"] != "text/html") {
                echo "<h1 class=error>Only HTML plaintext bookmarks allowed<br>You tried to upload a file with meta " . $_FILES["bookmarks"]["type"] . "</h1>";
                exit;
        }
        $target_path = "/tmp/" . basename( $_FILES['bookmarks']['name']);
        if(move_uploaded_file($_FILES['bookmarks']['tmp_name'], $target_path)) {?>
                <h1 class=ok>Verified <i>"<? echo basename( $_FILES['bookmarks']['name']) ?>"</i></h1>
                <table width=100%>
                <?$handler = fopen($target_path,"r");
                $file = fread($handler,filesize($target_path));
                preg_match_all('/(<A HREF=\")(.*)(\" ADD_DATE)/Ui',$file,$urls);
                $urls = $urls[2];
                $rowclass = 0;
                error_reporting(0);     # Turn off PHP error
                set_time_limit(3);      # Set timeout limit for get_headers 3 secs
                ob_implicit_flush(true);  # Turn implicit flush on
                foreach ($urls as $url) {?>
                        <tr class=row<?echo $rowclass ?>><td>
                        <?$rowclass = 1 - $rowclass;?>
                        <a href="<?print($url)?>"><?print($url)?></a>
                        </td><td>
                        <?$urlcheck = get_headers($url);
                        if (preg_match("/200 OK/",$urlcheck[0])) {
                                echo "<font color=green>OK</font>";
                        } else {
                                echo "<font color=red>" . $urlcheck[0] . "</font>";
                        }?>
                        </td></tr>
                <?}?>
                </table><p>Finished
        <?} else {
                echo "There was an error uploading the file!";
        }
} else {?>
        <form enctype="multipart/form-data" method="post" action="<? $PHP_SELF;?>">
        <input type="file" name="bookmarks" accept="text/html">
        <input type="submit" name=submit value="Check">
        </form>
<?}?>
</body>
</html>
