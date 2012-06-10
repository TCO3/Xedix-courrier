<?php
$connect_array=array();
   $serveur="http://localhost:5290" ;
   $connect_array = xedix_connect ( $serveur ) ;
   $cleSession = $connect_array[1];

$id = $_GET['id'];
# Requetage

$requete= $id." <DANS> id";

# On encode la requete pour la passer en argument
   $requete_url=my_encode2($requete) ;

##################################################################################################################
    # Selection de l'id
  $select1="from<all|0>;to<all|0>;subject<all|0>;body<all|0>;";

# On encode la selection pour la passer en argument
   $select_url=my_encode2($select1);

# On envoie l'appel regroupant requete+selection a XediX
   $flux1 = xedix_send ($connect_array[0],$serveur,$cleSession,$requete_url,$select_url) ;
   $idArray= $flux1;
   
##################################################################################################################
 # Selection de l'expediteur
  $select1="id<all|0>;to<all|0>;subject<all|0>;body<all|0>;";

# On encode la selection pour la passer en argument
   $select_url=my_encode2($select1);

# On envoie l'appel regroupant requete+selection a XediX
   $flux1 = xedix_send ($connect_array[0],$serveur,$cleSession,$requete_url,$select_url) ;
     	
   $fromArray = split(".com",$flux1);
   
   $nbvaleurs = count($fromArray) - 1;
   
  for($i=0; $i < $nbvaleurs;$i++){
   	$fromArray[$i] = $fromArray[$i].".com";
   }
   
##################################################################################################################
    # Selection du sujet
  $select1="id<all|0>;to<all|0>;body<all|0>;";

# On encode la selection pour la passer en argument
   $select_url=my_encode2($select1);

# On envoie l'appel regroupant requete+selection a XediX
   $flux1 = xedix_send ($connect_array[0],$serveur,$cleSession,$requete_url,$select_url) ;
   
   $subjectArray = split($fromArray[0],$flux1);

##################################################################################################################
    # Selection du destinataire
  $select1="id<all|0>;from<all|0>;subject<all|0>;body<all|0>;";

# On encode la selection pour la passer en argument
   $select_url=my_encode2($select1);

# On envoie l'appel regroupant requete+selection a XediX
   $flux1 = xedix_send ($connect_array[0],$serveur,$cleSession,$requete_url,$select_url) ;
   
  $toArray = split(".com",$flux1);
   
   $nbvaleurs = count($fromArray) - 1;
   
  for($i=0; $i < $nbvaleurs;$i++){
   	$toArray[$i] = $toArray[$i].".com";
   	}

##################################################################################################################
    # Selection du body
  $select1="id<all|0>;to<all|0>;subject<all|0>;";

# On encode la selection pour la passer en argument
   $select_url=my_encode2($select1);

# On envoie l'appel regroupant requete+selection a XediX
   $flux1 = xedix_send ($connect_array[0],$serveur,$cleSession,$requete_url,$select_url) ;
   
   $bodyArray = split($fromArray[0],$flux1);
      
 ##################################################################################################################

   print "<HTML><HEAD><TITLE>Mail</TITLE>";
   print "<link rel='stylesheet' type='text/css' href='gdc.css'>" ;  
   print "</HEAD>";
   print "<BODY>";
   print "<div id ='page'>";
	print "<div id='header'>";
	print "<a href = main.php>&#60;-Retour</a>";
 	print "<h1 align=center>Message</h1>";
 	print "</div>";
 	print "<div id ='content'>";
 	print "<OL>" ;
		
	$nb = 0;
   for ( $i=0; $i < $nbvaleurs ; $i++ ) {
   	if($idArray[$i] == $id){
         print "<p><b> Exp&eacutediteur: </b>".$fromArray[$i];
         print "<p><b> Destinataire: </b>".$toArray[$i];
         print "<p><b> Sujet: </b>".$subjectArray[$i+1];
         print "<p><b> Message: </b><p>".$bodyArray[$i+1];
            $nb++;
         break;
      }
   }
 
   if($nb == 0){
   	print "<P><i>Pas de contenu.</i>";
   	}

   print "</ol>";
   print "</div>";
   print "<div id = 'footer'><a href = './mail.php?id=".$id."'> ^^ Haut de page ^^</a></div>";
   print "</div>";
   print "</body></html>";


# Deconnexion de la base

  xedix_disconnect($connect_array[0]) ;

#
#
#   Fonctions
#
#
#
  
function tagextract ($tag,$f) {
	
   $tago="<".$tag.">" ;
   $tagf="</".$tag.">" ;
   $temp=explode($tago,$f);
   $temp1=$temp[1];
   $temp2=explode($tagf,$temp1);
   return $temp2[0];
}

function xedix_connect ( $serveur ) {

#  Identification de l'utilisateur

   $login='admin' ;
   $password=rawurlencode('xedix#amodifier') ;
   $c='';

#  Ouverture de la session

   $fi=fopen($serveur.'/cgi-bin/client?X2Admin+13++login='.$login.'&pwd='.$password.'&output=xml', 'r');

   if ( $fi == 0 ) {
        echo 'Connexion impossible' ;
        exit (1) ;
   }

   while(!feof($fi)){
       $c .= fread($fi, 4096);
       }

# Extraction de la valeur de la cle de session

    $cleSession=tagextract("clefsession",$c) ;
    $retour=array();
    $retour[0]=$fi ;
    $retour[1]= $cleSession ;
    return  $retour ;
}

function xedix_send ($fr,$serveur,$cleSession,$requete_url,$select_url) {
	
    $cc='';
    $fr=fopen($serveur."/cgi-bin/client?X2Xsearch+7+admin,".$cleSession."+allrequest=".$requete_url."&elems=".$select_url."&output=xml&targetcoll=listepropre&high=no&display=simple",'r');

   if ( $fr == 0 ) {
        echo "L'envoi de donnees a echoue" ;
        exit (1) ;
   }

   while(!feof($fr)){

        $cc .= fread($fr, 4096);
   }

# Nettoyage du flux XML

    $flux=tagextract("doclisteetendue",$cc);

    return $flux;
}


function xedix_disconnect ($fr) {

	fclose($fr) ;
	return ;
}


function my_encode2 ( $chaine ) {
	$temp1=str_replace(" ","+",$chaine);
	return $temp1;
}
?>
