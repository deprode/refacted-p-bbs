<?phpfunction check_spam(){    // RBLサーバリスト    $rbl_list = array("all.rbl.jp", "niku.2ch.net", "bsb.spamlookup.net");    $chkip = '';    $ip = (getenv("HTTP_X_FORWARDED_FOR") != "") ? getenv("HTTP_X_FORWARDED_FOR") : getenv("REMOTE_ADDR");    if (preg_match("/^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$/", $ip, $reg)) {        $chkip = $reg[4] . "." . $reg[3] . "." . $reg[2] . "." . $reg[1];    }    foreach ($rbl_list as $rbl) {        $check = $chkip . "." . $rbl;        $result = gethostbyname($check);        $flag = ($result != $check) ? true : false;    }    return $flag;}