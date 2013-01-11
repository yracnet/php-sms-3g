<!--
By yracnet
email: yracnet@gmail.com
date: 07/01/2012
site: www.communitysec.com
-->
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="es-ES">
  <head>
    <style>
	   *{font-family: monospace;font-size:14px;color:white;}
	   body{background-color:black;color:white;}
	   ul.log{
		 font-family: arial;font-size:10px;
		 position:absolute;
		 width:4em;
		 height:1.2em;
		 background-color:black;
		 border:1px solid white;
		 margin:0px;
		 padding:2px 5px;
		 white-space:nowrap; 
		 overflow:hidden;
		 text-overflow: ellipsis;
	   }
	   ul.log:hover {
		 width:auto;
		 height:auto;
		 height:auto;
		 display:block;
	   }
	   ul.log:hover > b{
		font-size: 1.1em;
		text-align: center;
		width: 100%;
	   }
	   ul.log p {
		 margin:0;
		 padding:0;
		 font-size:0.8em;
		 font-family: monospace;
	   }
	   form.sms {
		 width:26em;
		 margin:0 auto;
		 padding:1em;
		 border:1px solid white;
	   }
	   form.sms > p > i{
		 vertical-align:top;
		 width:5em;
		 display:inline-block;
	   }
	   form.sms > b{
		border: 1px solid white;
		display: block;
		font-size: 1.2em;
		text-align: center;
		width: 100%;
	   }
	   form.sms > p > input,
	   form.sms > p > textarea{
		 width:20em!important;
		 min-width:20em!important;
		 background-color:transparent;
		 border:1px solid white;
	   }
	   input[type=submit],
	   input[type=reset]{
		 background-color:transparent;
		 border:1px solid white;
	   }
	   a {
		 color: white;
		 font-style: italic;
		 font-weight: bold;
		 text-decoration: none;
		 font-size:0.7em;
	   }
	   /**,body{background-color:white!important;color:black!important;}*/
	  </style>
  </head>
  <body>
    <ul class="log">
	  <b>LOG DE PROCESOS DEL MODEM 3G</b>
<?php
  $cel = $_POST['cel'];
  $text = $_POST['text'];
  $token = $_POST['token'];
  if($cel && $text && $token){
    //validar token este en session! para evitar hacking x request
	enviarSms($cel,$text);
  }
  $lista = listarSms();
?>
	</ul>
    <form class="sms" method="post">
	  <b>ENVIO DE SMS - MODEM 3G EN PHP</b>
	  <p>
	    <i>Celular:</i>
	    <input name="cel" size="20" value="<?php echo $cel?>" autocomplete="off" maxlength="8" type="text"/>
	  </p>
	  <p>
	    <i>Mensaje:</i>
	    <textarea name="text" cols="20" rows="5" maxlength="150"><?php echo $text?></textarea>
	  </p>
	  <input name="token" value="<?php $token = rand (70100000,79899999); echo $token; ?>" type="hidden"/>
	  <input value="Enviar" type="submit"/>
	  <input value="Restaurar" type="reset"/>
	  <br/>
	  <br/>
	  <b>SMS RECIBIDOS - MODEM 3G EN PHP</b>
	  <dl>
	    <?php foreach($lista as $sms){?>
		  <dt>Cel:<?php echo $sms[2]?>: <i>Fecha: <?php echo $sms[4]?></i></dt>
		  <dd>Sms:<?php echo $sms[5]?></dd>
	    <?php }?>
	  </dl>
	  <a href="mailto:yracnet@gmail.com">By yracnet:<?php echo $token; ?></a>
	</form>
  </body>
</html>
<?php
//enviar sms por MODEM
function enviarSms($cel, $text){
    $text1 = substr($text,0,10);
	$port = "COM9";
    webLog("PARAM: $cel: $text1...");
	webLog("PORT: $port");
	$modem = null;
	try{
	  $config = "mode $port: baud=9600 data=8 stop=1 parity=n xon=on"; 
	  webLog($config);
      exec($config);
	  //==============================
	  webLog("OPEN $port");
	  $modem = dio_open("$port:", O_RDWR);
	  $c = 0;
	  $res = 'NOK';
	  while($c < 20 && $res != 'OK'){
	    $c++;
		$res = callAT("at", $modem, false);
	  }
	  if($res == 'OK'){
		  $res = callAT("at", $modem);
		  $res = callAT("at+cmgf=1", $modem);
		  $res = callAT("at+cmgs=\"$cel\"", $modem);
		  $res = callAT("$text", $modem, true, 0x1A);
	  } else {
	    webLog("NO SINCRONIZADO....","MODEM");
	  }
	  //==============================
	  webLog("CLOSE $port");
	  dio_close($modem);
	}catch( Exception  $error ){
	  webLog($error, "ERROR");
	}
	webLog("FIN del proceso..");
}
//listar sms del MODEM
function listarSms(){
    $port = "COM9";
    webLog("PORT: $port");
	try{
	  $config = "mode $port: baud=9600 data=8 stop=1 parity=n xon=on"; 
	  webLog($config);
      exec($config);
	  //==============================
	  webLog("OPEN $port");
	  $modem = dio_open("$port:", O_RDWR);
	  $c = 0;
	  $res = 'NOK';
	  while($c < 20 && $res != 'OK'){
	    $c++;
		$res = callAT("AT", $modem, false);
	  }
	  if($res == 'OK'){
		  $res = callAT("AT", $modem);
		  $res = callAT("AT+CMGF=1", $modem);
		  $res = callAT("AT+CMGL=\"ALL\"", $modem);
		  $res =  explode("+CMGL", $res);
		  foreach( $res as $k => $v){
		    $v = str_replace( chr(0x0A), ',',$v);
		    $res [ $k ] = mb_split('(,)(?=(?:[^"]|"[^"]*")*$)', $v);
		  }
	  } else {
	    webLog("NO SINCRONIZADO....","MODEM");
	  }
	  //==============================
	  webLog("CLOSE $port");
	  dio_close($modem);
	  return $res;
	}catch( Exception  $error ){
	  webLog($error, "ERROR");
	}
	webLog("FIN del proceso..");
	return array();
}
//ejecutar commando AT + Respuesta
function callAT($cmd, $modem, $log = true, $end = 0x0D ){
      $cmd = trim($cmd);
	  $cmd = $cmd.chr($end);
	  if($log){webLog($cmd,"&gt;",true);}
      dio_write ($modem, $cmd);
	  sleep(1);
      $cmd = dio_read ($modem);
	  $cmd = trim($cmd);
	  if($log){webLog($cmd,"&lt;");}
	  return $cmd;
}
//COUNT LOG..
$COUNT = 0;
//escribir log <li>.....</li>
function webLog($text,$group=-666, $hex=false){
	if($group == -666){
	    global $COUNT;
		$group = (++$COUNT);
	}
	$time = microtime(true) ;
	$time = date("h.i.s.u");
	if($hex){
	    $hex='<p>';
		for ($i=0; $i < strlen($text); $i++){
			$hex = $hex.' 0x'.dechex(ord($text[$i]));
		}
	    $hex.='</p>';
	} else {
	  $hex = '';
	}
	echo "<li>$time: <i>$group</i>: $text $hex</li>";
}
//convertir string to HEX
function strToHex($string){
    $hex='';
    for ($i=0; $i < strlen($string); $i++){
        $hex = $hex.' 0x'.dechex(ord($string[$i]));
    }
    return $hex;
}
?>
