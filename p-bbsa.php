<?php
if(phpversion()>="4.1.0"){
  extract($_REQUEST);
  extract($_SERVER);
}
/*
 * P-BBS by ToR
 * http://php.s3.to
 *
 * 2000/12/02 pre  ����
 * 2001/03/06 v1.0 �����[
 * 2001/03/11 v1.1 HTML�����o��OnOff�A�������݌�Location�Ŕ�΂��A�Ǘ�Ӱ��pass��apass
 * 2001/04/16 v1.2 �ߋ����O�Ή��A�������ƐF�ς��B�f�U�C���ύX
 * 2001/04/24 v1.23 �������݌�\���֐����A�y�[�W���O�ύX�A�Ǘ�Ӱ�ގ��s��C���A�z�X�g�\���ARe:[2]
 * 2001/05/04 v1.231 �N�b�L�[��HTML�ɏ����o���Ă��܂��o�O�C��,�ߋ����O���[�h�̔�\��<br>
 * 2001/05/17 v1.232 �����������A�s�������ǉ�
 * 2001/05/27 v1.24 autolink�C���A�������݌�refresh�Ŕ�΂�
 * 2001/06/02 v1.25 GET���e�֎~�A�O�����e�֎~
 * 2001/11/15 v1.26 >�̌�̃X�y�[�X�������BPHP3�̎����X��<br>�ƂȂ�o�O�C��
 * 2002/05/25 v1.27 i18n�폜�A�������C��
 * 2002/02/11 v1.28 �N�b�L�[�̕��������΍�
 * 2003/05/25 v1.29 �֎~�z�X�g�A�֎~���[�h�ǉ�
 * 2003/06/07 v1.3  �����폜�o����悤��
 *
 * �V���v���Ȍf���ł��B�Ǘ����[�h�t
 * ��̃��O�t�@�C����p�ӂ��āA�p�[�~�b�V������606�ɂ��Ă�������
 * HTML�������o���ꍇ�́A���̃f�B���N�g����707��777����Ȃ��ƃ_���ł�
 */
//-------------�ݒ肱������-------------
/* <title>�ɓ����^�C�g�� */
$title1 = 'P-BBS';
/* �f����TOP�^�C�g���iHTML�j*/
$title2 = '<font size=5 face=Verdana color=gray><b>P-BBS</b></font>';
/* <body>�^�O */
$body = '<body bgcolor="#ddf2ed" text="#444444" link="#0000AA">';

/* �Ǘ��җp�p�X���[�h�B�K���ύX���ĉ������B*/
$admin_pass = '0123';

/* ���O�ۑ��t�@�C�� */
$logfile = 'bbs.log';

/* TOP�y�[�W��HTML�ɏ����o���� �iyes=1 no=0�j*/
$htmlw = 0;
/* �ÓIHTML�������o���ꍇ��HTML�t�@�C�� */
$html_file = 'pbbs.html';

/* �߂��iHOME�j*/
$home = 'http://php.s3.to';
/* ��y�[�W������̕\���L���� */
$page_def = 10;
/* �ő�L�^���� ������z����ƌÂ�������ߋ����O�ֈڂ�܂��B*/
$max = 30;
/* �����������i���O�A�薼�A�{���j�S�p���Ƃ��̔����ł� */
$maxn  = 40;
$maxs  = 40;
$maxv  = 1500;
/* �{���̉��s������ */
$maxline = 25;
/* ����z�X�g����̘A�����e�𐧌�
  --> �b�����L�q����Ƃ��̎��Ԉȏ���o�߂��Ȃ��ƘA�����e�ł��Ȃ�*/
$w_regist = 30;
/* �����Ŏ��������N���邩�ǂ����iyes=1 no=0�j*/
$autolink = 1;
/* HTML�^�O��L���ɂ��邩�iyes=1 no=0)*/
$tag = 0;
/* �^�C�g�������œ��e���ꂽ�ꍇ */
$mudai = '(����)';
/* �����������̐F */
$re_color = "#225588";
/* �z�X�g��\�����邩�i�\�����Ȃ�=0 <!-->���ŕ\��=1 �\��=2�j*/
$hostview = 1;
/* �O���������݋֎~�ɂ���?(����=1,���Ȃ�=0) */
define("GAIBU", 0);

/* �g�p����t�@�C�����b�N�̃^�C�v�imkdir=1 flock=2 �g��Ȃ�=0�j*/
define("LOCKEY", 2); 		//�ʏ��2��OK
/* mkdir���b�N���g������lock�Ƃ������Ńf�B���N�g�����쐬����777�ɂ��Ă������� */
define("LOCK" , "lock/plock");	//lock�̒��ɍ�郍�b�N�t�@�C����

/* �ߋ����O�쐬����? */
$past_key = 0;
/* �ߋ����O�ԍ��t�@�C�� */
$past_no  = "pastno.log";
/* �ߋ����O�쐬�f�B���N�g��(�������݌����K�v) */
$past_dir = "./";
/* �ߋ����O��ɏ������ލs�� */
$past_line= "50";

// �{���֎~�z�X�g�i���K�\����
$no_host[] = 'kantei.go.jp';
$no_host[] = 'anonymizer.com';
$no_host[] = "pt$";
$no_host[] = "ph$";
$no_host[] = "my$";
$no_host[] = "th$";
$no_host[] = "rr.com";

// �g�p�֎~���[�h
$no_word[] = '����';
$no_word[] = '�n��';
$no_word[] = 'novapublic';
$no_word[] = 'http:';

//---------�ݒ肱���܂�--------------
// �֎~�z�X�g
if (is_array($no_host)) {
  $host = gethostbyaddr(getenv("REMOTE_ADDR"));
  foreach ($no_host as $user) {
    if(preg_match("/$user/i", $host)){
      header("Status: 204\n\n");//�󔒃y�[�W
      exit;
    }
  }
}
function head(&$dat){ 		//�w�b�_�[�\����
  global $mode,$no,$PHP_SELF,$logfile,$title1,$title2,$body,$p_bbs,$htmlw,$max,$page_def;

  //�N�b�L�[�𒸂��܂�
  if (get_magic_quotes_gpc()) $p_bbs = stripslashes($p_bbs);
  if(!$htmlw) list($r_name,$r_mail) = explode(",", $p_bbs);
  if($mode == "resmsg"){	//���X�̏ꍇ
    $res = file($logfile);
    $flag = 0;
    while (list($key, $value) = each ($res)) {
      list($rno,$date,$name,$email,$sub,$com,$url) = explode("<>", $value);
      if ($no == "$rno"){ $flag=1; break; }
    }
    if ($flag == 0) error("�Y���L����������܂���");

    if(ereg("Re\[([0-9]+)\]:", $sub, $reg)){
      $reg[1]++;
      $r_sub=ereg_replace("Re\[([0-9]+)\]:", "Re[$reg[1]]:", $sub);
    }elseif(ereg("^Re:", $sub)){ 
      $r_sub=ereg_replace("^Re:", "Re[2]:", $sub);
    }else{ $r_sub = "Re:$sub"; }
    $r_com = "&gt;$com";
    $r_com = eregi_replace("<br( /)?>","\r&gt;",$r_com);
  }
  $head='<html><head>
<META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=Shift_JIS">
<title>'.$title1.'</title>
</head>';
$dat=$head.$body.'
<form method="POST" action="'.$PHP_SELF.'">
<input type="hidden" name="mode" value="regist">
<BASEFONT SIZE="3">'.$title2.'<hr size=1><br>
<TT>
�����O <input type=text name="name" size=20 value="'.$r_name.'" maxlength=24><br>
���[�� <input type=text name="email" size=30 value="'.$r_mail.'"><br>
�薼�@ <input type=text name="sub" size=30 value="'.$r_sub.'">
<input type=submit value="     ���e     "><input type=reset value="����"><br>
<textarea name="com" rows=5 cols=82>'.$r_com.'</textarea><br><br>
�t�q�k�@ <input type=text name="url" size=70 value="http://"><br>
�폜�L�[ <input type=password name="password" size=8 value="'.$r_pass.'">(�L���̍폜�p�B�p������8�����ȓ�)
</form></TT>
<hr size=1><font size=-2>�V�����L������\�����܂��B�ō�'.$max.'���̋L�����L�^����A����𒴂���ƌÂ��L������ߋ����O�ֈڂ�܂��B<br>
 �P��̕\����'.$page_def.'�����z����ꍇ�́A���̃{�^�����������ƂŎ��̉�ʂ̋L����\�����܂��B</font>
';
}
function foot(&$dat){ //�t�b�^�[�\����
  global $PHP_SELF,$home,$past_key;

$dat.='<div align="right"><form method="POST" action="'.$PHP_SELF.'">
<input type=hidden name=mode value="usrdel">No <input type=text name=no size=2>
pass <input type=password name=pwd size=4 maxlength=8>
<input type=submit value="Del"></form>
[ <a href='.$home.'>�z�[��</a> ] [ <a href='.$PHP_SELF.'?mode=admin>�Ǘ�</a> ] ';
if($past_key) $dat.='[ <a href='.$PHP_SELF.'?mode=past>�ߋ����O</a> ]'; 
$dat.='<br><br><small><!-- P-BBS v1.232 -->- <a href="http://php.s3.to" target="_top">P-BBS</a> -</small></div>
</body></html>';
}
function Main(&$dat){	//�L���\����
  global $logfile,$page_def,$page,$PHP_SELF,$autolink,$re_color,$hostview;
 
  $view  = file($logfile);
  $total = sizeof($view);
  $total2= $total;

  (isset($page)) ? $start = $page : $start = 0;
  $end = $start + $page_def;
  $st  = $start + 1;

  for($s = $start;$s < $end;$s++){
    if(!$view[$s]) break;
    list($no,$now,$name,$email,$sub,$com,$url,
         $host,$pw) = explode("<>", $view[$s]);

    if($url){ $url = "<a href=\"http://$url\" target=\"_blank\">http://$url</a>";}
    if($email){ $name = "<a href=\"mailto:$email\">$name</a>";}
    // �������鎞�͐F�ύX
    $com = eregi_replace("(^|>)(&gt;[^<]*)", "\\1<font color=$re_color>\\2</font>", $com);
    // URL���������N
    if ($autolink) { $com=auto_link($com); }
    // Host�\���`��
    if($hostview==1){ $host="<!--$host-->"; }
    elseif($hostview==2){ $host="[ $host ]"; }
    else{ $host=""; }

    $dat.='<hr size=1>[<a href="'.$PHP_SELF.'?mode=resmsg&no='.$no.'">'.$no.'</a>] ';
    $dat.='<font size="+1" color="#D01166"><b>'.$sub.'</b></font><br>';
    $dat.='�@Name�F<font color="#007000"><b>'.$name.'</b></font><font size="-1">�@Date�F '.$now.'</font>';
    $dat.='<p><blockquote><tt>'.$com.'<br></tt>';
    $dat.='<p>'.$url.'<br>'.$host.'</blockquote><p>';

    $p++;
  } //end for
  $prev = $page - $page_def;
  $next = $page + $page_def;
  $dat.= sprintf("<hr size=1> %d �Ԗڂ��� %d �Ԗڂ̋L����\��<br><center>Page:[<b> ",$st,$st+$p-1);
  ($page > 0) ? $dat.="<a href=\"$PHP_SELF?page=$prev\">&lt;&lt;</a> " : $dat.=" ";
  $p_no=1;$p_li=0;
  while ($total > 0) {
    if ($page == $p_li) { $dat.="$p_no ";
    }else{ $dat.="<a href=\"$PHP_SELF?page=$p_li\">$p_no</a> "; }
    $p_no++;
    $p_li  = $p_li  + $page_def;
    $total = $total - $page_def;
  }
  ($total2 > $next) ? $dat.=" <a href=\"$PHP_SELF?page=$next\">&gt;&gt;</a>" : $dat.=" ";
  $dat.="</b> ]\n";
}
function regist(){	//���O��������
  global $name,$email,$sub,$com,$url,$tag,$past_key,$maxn,$maxs,$maxv,$maxline;
  global $password,$html_url,$logfile,$jisa,$max,$w_regist,$autolink,$mudai,
	$PHP_SELF,$REQUEST_METHOD,$no_word;

  if (preg_match("/(<a\b[^>]*?>|\[url(?:\s?=|\]))|href=/i", $com)) error("�֎~���[�h�G���[�I�I");
  if($REQUEST_METHOD != "POST") error("�s���ȓ��e�����Ȃ��ŉ�����");
  if(GAIBU && !eregi($PHP_SELF,getenv("HTTP_REFERER"))) error("�O�����珑�����݂ł��܂���"); 
  // �t�H�[�����e���`�F�b�N
  if(!$name||ereg("^( |�@)*$",$name)){ error("���O���������܂�Ă��܂���"); }
  if(!$com||ereg("^( |�@|\t|\r|\n)*$",$com)){ error("�{�����������܂�Ă��܂���"); }
  if(!$sub||ereg("^( |�@)*$",$sub)){ $sub=$mudai; }

  if(strlen($name) > $maxn){ error("���O���������܂����I"); }
  if(strlen($sub) > $maxs){ error("�^�C�g�����������܂����I"); }
  if(strlen($com) > $maxv){ error("�{�����������܂����I"); }

  // �֎~���[�h
  if (is_array($no_word)) {
    foreach ($no_word as $fuck) {
      if (preg_match("/$fuck/", $com)) error("�g�p�ł��Ȃ����t���܂܂�Ă��܂��I");
      if (preg_match("/$fuck/", $sub)) error("�g�p�ł��Ȃ����t���܂܂�Ă��܂��I");
      if (preg_match("/$fuck/", $name)) error("�g�p�ł��Ȃ����t���܂܂�Ă��܂��I");
    }
  }
  $times = time();

  $check = file($logfile);
  $tail = sizeof($check);

  list($tno,$tdate,$tname,$tmail,$tsub,$tcom,,,$tpw,$ttime) = explode("<>", $check[0]);
  if($name == $tname && $com == $tcom) error("��d���e�͋֎~�ł�");

  if ($w_regist && $times - $ttime < $w_regist)
    error("�A�����e�͂������΂炭���Ԃ�u���Ă��炨�肢�v���܂�");

  // �L��No���̔�
  $no = $tno + 1;

  // �z�X�g�����擾
  $host = getenv("REMOTE_HOST");
  $addr = getenv("REMOTE_ADDR");
  if($host == "" || $host == $addr){//gethostbyddr���g���邩
    $host=@gethostbyaddr($addr);
  }

  // �폜�L�[���Í���
  if ($password) { $PW = crypt(($password),aa); }

  $now = gmdate( "Y/m/d(D) H:i",time()+9*60*60);
  $url = ereg_replace( "^http://",  "",$url);

  if (get_magic_quotes_gpc()) {//\���폜
    $com = stripslashes($com);
    $sub = stripslashes($sub);
    $name = stripslashes($name);
    $email = stripslashes($email);
    $url = stripslashes($url);
  }
  if ($tag == 0){
    $sub = htmlspecialchars($sub);//�^�O���֎~
    $name = htmlspecialchars($name);
    $com = htmlspecialchars($com);
    $email = htmlspecialchars($email);
    $url = htmlspecialchars($url);
    $com = str_replace("&amp;", "&", $com); 
  }
  $com = str_replace( "\r\n",  "\r", $com);  //���s�����̓���B 
  $com = str_replace( "\r",  "\n", $com);   
  /* \n������isubstr_count�̑���j*/
  $temp = str_replace("\n", "\n"."a",$com); 
  $str_cnt=strlen($temp)-strlen($com); 
  if($str_cnt > $maxline){ error("�s�����������܂����I"); }
  $com = ereg_replace("\n((�@| |\t)*\n){3,}","\n",$com);//�A�������s����s
  $com = nl2br($com);  //���s�����̑O��<br>��������B
  $com = ereg_replace( "\n",  "", $com);  //\n�𕶎��񂩂�����B

  $new_msg="$no<>$now<>$name<>$email<>$sub<>$com<>$url<>$host<>$PW<>$times\n";

  //�N�b�L�[�ۑ�
  $cookvalue = implode(",", array($name,$email));
  setcookie ("p_bbs", $cookvalue,time()+14*24*3600);  /* 2�T�ԂŊ����؂� */

  $old_log = file($logfile);
  $line = sizeof($old_log);
  $new_log[0] = $new_msg;//�擪�ɐV�L��
  if($past_key && $line >= $max){//�͂ݏo�����L�����ߋ����O��
    for($s=$max; $s<=$line; $s++){//�O�̈ו����s�Ή�
      past_log($old_log[$s-1]);
    }
  }
  for($i=1; $i<$max; $i++) {//�ő�L��������
    $new_log[$i] = $old_log[$i-1];
  }
  renewlog($new_log);//���O�X�V

} 
function usrdel(){	//���[�U�[�폜
  global $pwd,$no,$logfile;
  if ($no == "" || $pwd == "")
    { error("�폜No�܂��͍폜�L�[�����̓����ł�"); }

  $logall = file($logfile);
  $flag=0;

  while(list(,$lines)=each($logall)){
    list($ono,$dat,$name,$email,$sub,$com,$url,$host,$opas) = explode("<>",$lines);
    if ($no == "$ono") { $flag=1; $pass=$opas; }
    else { $pushlog[]=$lines; }
  }

  if ($flag == 0) { error("�Y���L������������܂���"); }
  if ($pass == "") { error("�Y���L���ɂ͍폜�L�[���ݒ肳��Ă��܂���"); }

  // �폜�L�[���ƍ�
  $match = crypt(($pwd),aa);
  if (($match != $pass)) { error("�폜�L�[���Ⴂ�܂�"); }
	
  // ���O���X�V
  renewlog($pushlog);
}
function admin(){	//�Ǘ��@�\
  global $admin_pass,$PHP_SELF,$logfile;
  global $del,$apass,$head,$body;
  if ($apass && $apass != "$admin_pass")
    { error("�p�X���[�h���Ⴂ�܂�"); }
  echo "$head";
  echo "$body";
  echo "[<a href=\"$PHP_SELF?\">�f���ɖ߂�</a>]\n";
  echo "<table width='100%'><tr><th bgcolor=\"#508000\">\n";
  echo "<font color=\"#FFFFFF\">�Ǘ����[�h</font>\n";
  echo "</th></tr></table>\n";

  if (!$apass) {
    echo "<P><center><h4>�p�X���[�h����͂��ĉ�����</h4>\n";
    echo "<form action=\"$PHP_SELF\" method=\"POST\">\n";
    echo "<input type=hidden name=mode value=\"admin\">\n";
    echo "<input type=password name=apass size=8>";
    echo "<input type=submit value=\" �F�� \"></form>\n";
  }else {
    // �폜����
    if (is_array($del)) {
      // �폜�����}�b�`���O���X�V
      $delall = file($logfile);

      for($i=0; $i<count($delall); $i++) {
        list($no,) = explode("<>",$delall[$i]);
        if (in_array($no, $del)) $delall[$i] = "";
      }
      // ���O���X�V
      renewlog($delall);
    }

    // �폜��ʂ�\��
    echo "<form action=\"$PHP_SELF\" method=\"POST\">\n";
    echo "<input type=hidden name=mode value=\"admin\">\n";
    echo "<input type=hidden name=apass value=\"$apass\">\n";
    echo "<center><P>�폜�������L���̃`�F�b�N�{�b�N�X�Ƀ`�F�b�N�����A�폜�{�^���������ĉ������B\n";
    echo "<P><table border=0 cellspacing=0>\n";
    echo "<tr bgcolor=bbbbbb><th>�폜</th><th>�L��No</th><th>���e��</th><th>�薼</th>";
    echo "<th>���e��</th><th>�R�����g</th><th>�z�X�g��</th>";
    echo "</tr>\n";

    $delmode = file($logfile);

    if (is_array($delmode)) {
      while (list($l,$val)=each($delmode)){
        list($no,$date,$name,$email,$sub,$com,$url,
             $host,$pw,$tail,$w,$h,$time,$chk) = explode("<>",$val);

        list($date,$dmy) = split("\(", $date);
        if ($email) { $name="<a href=\"mailto:$email\">$name</a>"; }
        $com = str_replace("<br>","",$com);
        $com = htmlspecialchars($com);
        if(strlen($com) > 40){ $com = substr($com,0,38) . " ..."; }

        echo ($l % 2) ? "<tr bgcolor=F8F8F8>" : "<tr bgcolor=DDDDDD>";
        echo "<th><input type=checkbox name=del[] value=\"$no\"></th>";
        echo "<th>$no</th><td><small>$date</small></td><td>$sub</td>";
        echo "<td><b>$name</b></td><td><small>$com</small></td>";
        echo "<td>$host</td>\n</tr>\n";
      }
    }

    echo "</table>\n";
    echo "<P><input type=submit value=\"�폜����\">";
    echo "<input type=reset value=\"���Z�b�g\"></form>\n";

  }
  echo "</center></body></html>\n";
}
function lock_dir($name=""){//�f�B���N�g�����b�N
  if($name=="") $name="lock";

  // 3���ȏ�O�̃f�B���N�g���Ȃ�������s�Ƃ݂Ȃ��č폜
  if ((file_exists($name))&&filemtime($name) < time() - 180) {
    @RmDir($name);
  }

  do{
    if (@MkDir($name,0777)){
      return 1;
    }
    sleep(1);// ��b�҂��čăg���C
    $i++;
  }while($i < 5);

  return 0;
}
function unlock_dir($name=""){//���b�N����
  if($name=="") $name="lock";
  @rmdir($name);
}
function renewlog($arrline){//���O�X�V  ����:�z��
  global $logfile;

  if(LOCKEY==1){ lock_dir(LOCK) 
	or error("���b�N�G���[<br>���΂炭�҂��Ă���ɂ��ĉ�����"); }

  $rp = fopen($logfile, "w");
  if(LOCKEY==2){ flock($rp, 2); }
  while(list(,$val)=each($arrline)){
    fputs($rp,$val);
  }
  fclose($rp);
  if(LOCKEY==1){ unlock_dir(LOCK); }
}
function MakeHtml(){	//HTML����
  global $html_file;

  head($buf);
  Main($buf);
  foot($buf);

  $hp = @fopen ($html_file,"w");
  flock($hp,2);
  fputs($hp, $buf);
  fclose($hp);
}
function ShowHtml(){
  head($buf);
  Main($buf);
  foot($buf);

  echo $buf;
}
function past_log($data){//�ߋ����O�쐬
  global $past_no,$past_dir,$past_line,$autolink;

  $fc = @fopen($past_no, "r") or die(__LINE__.$past_no."���J���܂���");
  $count = fgets($fc, 10);
  fclose($fc);
  $pastfile = $past_dir."index".$count.".html";
  if(file_exists($pastfile)) $past  = file($pastfile);

  if(sizeof($past) > $past_line){
    $count++;
    $pf = fopen($past_no, "w");
    fputs($pf, $count);
    fclose($pf);
    $pastfile = $past_dir."index".$count.".html";
    $past = "";
  }

  list($pno,$pdate,$pname,$pemail,$psub,
       $pcom,$purl,$pho,$ppw) = explode("<>", $data);

  if($purl){ $purl = "<a href=\"http://$purl\" target=\"_blank\">HP</a>";}
  if($pemail){ $pname = "<a href=\"mailto:$pemail\">$pname</a>";}
  // �������鎞�͐F�ύX
  $pcom = eregi_replace("(&gt;)([^<]*)", "<font color=999999>\\1\\2</font>", $pcom);
  // URL���������N
  if ($autolink) { $pcom=auto_link($pcom); }

  $dat.="<hr>[$pno] <font color=\"#009900\"><b>$psub</b></font> Name�F<b>$pname</b> <small>Date�F$pdate</small> $purl<br><ul>$pcom</ul><!-- $pho -->\n";

  $np = fopen($pastfile, "w");
  fputs($np, $dat);
  if($past){
    while(list(, $val)=each($past)){ fputs($np, $val); }
  }
  fclose($np);
  
}
function past_view(){
  global $past_no,$past_dir,$past_line,$body,$pno;

  $pno = htmlspecialchars($pno);

  $fc = @fopen($past_no, "r") or die(__LINE__.$past_no."���J���܂���");
  $count = fgets($fc, 10);
  fclose($fc);
  if(!$pno) $pno = $count;
  echo '<html><head><title>�� �ߋ����O '.$pno.' ��</title>
'.$body.'<font size=2>[<a href="'.$PHP_SELF.'?">�f���ɖ߂�</a>]</font><br>
<center>�� �ߋ����O '.$pno.' ��<P>new�� ';
  $pastkey = $count;
  while ($pastkey > 0) {
    if ($pno == $pastkey) {
      echo "[<b>$pastkey</b>]";
    } else {
      echo "<a href=\"$PHP_SELF?mode=past&pno=$pastkey\">[$pastkey]</a>";
    }
    $pastkey--;
  }
  echo ' ��old</center>'.$past_line.'�����\��';
  $pastfile = $past_dir."index".$pno.".html";
  if(!file_exists($pastfile)) error("<br>�ߋ����O���݂���܂���");
  include($pastfile);
  die("</body></html>");
}
function auto_link($proto){//���������N5/25�C��
  $proto = ereg_replace("(https?|ftp|news)(://[[:alnum:]\+\$\;\?\.%,!#~*/:@&=_-]+)","<a href=\"\\1\\2\" target=\"_blank\">\\1\\2</a>",$proto);
  return $proto;
}
function error($mes){	//�G���[�t�H�[�}�b�g
  global $body;
	?>
<html><head>
<meta http-equiv="Content-Type" content="text/html; charset=x-sjis"></head>
<? echo $body; ?>
<br><br><hr size=1><br><br>
<center><font color=red size=5><b><? echo $mes; ?></b></font></center>
<br><br><hr size=1></body></html>
	<?
	exit;
}
/*=====================
       ���C��
 ======================*/
switch($mode):
	case 'regist':
  require_once("../rbl.php");
  if (check_spam()) die("�~�����ׂĂ����ς��܂�I�I");
		regist();
		if($htmlw) MakeHtml();
		echo "<META HTTP-EQUIV=\"refresh\" content=\"0;URL=$PHP_SELF?\">";
		break;
	case 'admin':
		admin();
		break;
	case 'usrdel':
		usrdel();
		if($htmlw) MakeHtml();
		ShowHtml();
		break;
        case 'past':
                past_view();
                break;
	default:
		ShowHtml();
		break;
  endswitch;
?>