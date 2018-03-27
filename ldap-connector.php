<html>
<head>
<!-- for iPhones -->
<meta name="viewport" content="width=320" />
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
</style>
<title>LDAP Connection Checker</title>
</head>
<body>
<b>This script checks your LDAP server from the Internet.</b><br>
<font size=1>Caveats: If you check TLS/SSL, the port will be forced to 636</font><hr>
<p>
<? if (isset($_REQUEST[submit])) {
 
        if ($_REQUEST[server] == "" || $_REQUEST[port] == "") {
                echo "<h1 class=error>Please insert a server and its port</h1>";
        } else if ($_REQUEST[protocol] == "") {
                echo "<h1 class=error>Please select a protocol</h1>";
        } else if ($_REQUEST[bind] == "") {
                echo "<h1 class=error>Please select an authentication</h1>";
        } else if ($_REQUEST[bind] == "auth" && $_REQUEST[binddn] == "") {
                echo "<h1 class=error>Please insert a Bind-DN</h1>";
        } else if ($_REQUEST[bind] == "auth" && $_REQUEST[bindpw] == "") {
                echo "<h1 class=error>Please insert a Bind-PW</h1>";
        } else if ($_REQUEST[basedn] == "") {
                echo "<h1 class=error>Please insert a base DN </h1>";
        } else {
                echo "Checking connection to " . $_REQUEST[server] . ":";
                error_reporting(0);
                if ($_REQUEST[tls]) {
                        # with ldaps:// the port's obsolete 'cos it's forced to 636
                        echo "636: ";
                        $_REQUEST[port] = 636;
                        $ldapconn = ldap_connect("ldaps://$_REQUEST[server]");
                } else {
                        echo $_REQUEST[port] . ": ";
                        $ldapconn = ldap_connect("$_REQUEST[server]", $_REQUEST[port]);
                }
                ldap_set_option($ldapconn,LDAP_OPT_PROTOCOL_VERSION, $_REQUEST[protocol]);
                ldap_set_option($ldapconn,LDAP_OPT_TIMELIMIT, 20);
                if (!$ldapconn) {
                        echo "<font color=red>Connection failed: ";
                        echo ldap_err2str(ldap_errno($ldapconn));
                        echo "</font>";
                } else {
                        echo "<font color=green>Connection successful</font><br>";
 
                        echo "<p>Binding ";
                        if ($_REQUEST[bind] == "anon") {
                                echo "anonymously: ";
                                $ldapbind = ldap_bind($ldapconn);
                        } else {
                                echo "with DN " . $_REQUEST[binddn] . ": ";
                                $ldapbind = ldap_bind($ldapconn, $_REQUEST[binddn], $_REQUEST[bindpw]);
                        }
                        if ($ldapbind) {
                                echo "<font color=green>Binding successful</font><p>";
 
                                echo "Showing first entry in " . $_REQUEST[basedn] . ": ";
                                $ldapsearch = ldap_search($ldapconn, $_REQUEST[basedn], "objectclass=*");
                                $getentries = ldap_get_entries($ldapconn, $ldapsearch);
                                echo "<font color=green>" . $getentries[1]["dn"] . "</font>";
                        } else {
                                echo "<font color=red>Bind failed -> ";
                                echo ldap_err2str(ldap_errno($ldapconn));
                                echo "</font>";
                        }
                        ldap_unbind($ldapconn);
                }
                ldap_close($ldapconn);
        }
}
?>
<form method=post action="<? $PHP_SELF;?>">
<table align=center>
<tr class=row0><td>Server:</td><td><input type=text name=server value="<? echo $_REQUEST[server]?>"></td></tr>
<tr class=row1><td>Port:</td><td><input type=text name=port value=<? echo $_REQUEST[port]?>></td></tr>
<tr class=row0><td>Protocol:</td><td><input type=radio <? if ($_REQUEST[protocol] == "2") { echo "checked"; }?> name=protocol value=2>v2<br>
                                <input type=radio <? if ($_REQUEST[protocol] == "3") { echo "checked"; }?> name=protocol value=3>v3</td></tr>
<tr class=row1><td>TLS/SSL:</td><td><input type=checkbox <? if ($_REQUEST[tls]) { echo "checked"; }?> name=tls></td></tr>
<tr class=row0><td>Bind:</td><td><input type=radio <? if ($_REQUEST[bind] == "anon") { echo "checked"; }?> name=bind value=anon>Anonymous<br>
                                <input type=radio <? if ($_REQUEST[bind] == "auth") { echo "checked"; }?> name=bind value=auth>Authenticated</td></tr>
<tr class=row1><td>Bind-DN:</td><td><input type=text name=binddn value="<? echo $_REQUEST[binddn]?>"></td></tr>
<tr class=row0><td>Bind-PW:</td><td><input type=password name=bindpw value="<? echo $_REQUEST[bindpw]?>"></td></tr>
<tr class=row1><td>Base-DN:</td><td><input type=text name=basedn value="<? echo $_REQUEST[basedn]?>"></td></tr>
</table>
<input type="submit" name=submit value="Check">
</form>
</body>
</html>
