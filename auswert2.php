<?php

/*------------------------------------------------------------------------------
// Individuelle Auswertung der Tippspielligen für Webmaster
// Autor: Marcus - www.bcerlbach.de
//------------------------------------------------------------------------------
// Download unter: http://forum.bcerlbach.de/downloads.php?cat=7

  * Wer immer über Updates informiert werden will, der sollte sich
  * im BCE-Forum registrieren. Denn dann hat man die Möglichkeit einen
  * Download zu abonieren. D.h. wenn etwas daran geändert wurde,
  * so wird umgehend eine E-Mail verschickt.

  *
  * This program is free software; you can redistribute it and/or
  * modify it under the terms of the GNU General Public License as
  * published by the Free Software Foundation; either version 2 of
  * the License, or (at your option) any later version.
  *
  * This program is distributed in the hope that it will be useful,
  * but WITHOUT ANY WARRANTY; without even the implied warranty of
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
  * General Public License for more details.
  *
  * REMOVING OR CHANGING THE COPYRIGHT NOTICES IS NOT ALLOWED!
  *
------------------------------------------------------------------------------*/


require_once(dirname(__FILE__).'/init.php');
require_once(dirname(__FILE__).'/auswert_funk.php');

// Variablen vorbelegen
$htmlbody = '';
$joker = 0;
$team = 0;
$show_team = 0;
$usejoker = 0;
$tippmodus = 1;
$fontcolor = '#000000';

// Konfigurationen laden
if (is_file(PATH_TO_LMO . '/auswert.cfg')) {
    $config = @file(PATH_TO_LMO . '/auswert.cfg');
} else {
    die('Konfigurationsdatei nicht vorhanden - neu anlegen!');
}
$config = array_pad(is_array($config) ? $config : [], 34, '');  // fehlende Zeilen als leer behandeln

// Auswertdatei übergeben (nur innerhalb des LMO-Ordners)
$auswertrel = trim($config[0]);
$array = ($auswertrel != '' && strpos($auswertrel, '..') === false) ? @file(PATH_TO_LMO . '/' . $auswertrel) : false;
if (!is_array($array)) {
    die('Auswertungsdatei nicht vorhanden - Tippspiel neu auswerten und auswert_admin.php ausführen!');
}

// Seitentitel
$title = awc_txt($config[1]);  //"BC Erlbach 1919 - Individuelle Auswertung der Tippspielligen";

// Hintergrundfarbe
$backgroundcolor = awc_css($config[2]);  //#B9C4CC;

// Schriftart - bei Standardschrift Feld leer lassen -> $fontface = "";
$fontfamily = awc_css($config[3]);  //"Arial,Courier,Sans-Serif";

// Die Schriftfarbe, die bei den Fehlermeldungenim Browser verwendet werden soll
$fontfehl = awc_css($config[4]);  //"#800000";

// Farbe des Tabellenkopfes
$tablehaedercolor = awc_css($config[5]);  //"#C7CFD6";

// Farbe des Tabellenintergrundes
$tablebackcolor = awc_css($config[6]);  //"#C7CFD6";

// Farbe der Tabellenschrift
$tablefontcolor = awc_css($config[7]);  //"#000000";

// Größe der Tabellenschrift in Punkt
$tablefontsize = awc_int($config[8], '10');  // "10";
$fontsize = $tablefontsize;  //"10";

// Anzeige-Tipper-Begrenzung laut config
$showtipper = awc_int($config[9], '-1');

// Sollen Tipper die noch keinen Tipp abgegeben haben angezeigt werden?
$shownichttipper = awc_01(trim($config[10]));  //0;  // 0=nein - 1=ja

// Was soll bei der Auswertung angezeigt werden?  1 = anzeigen; 0 nicht anzeigen
$show_sp_ges = awc_01(trim($config[11]));  // Anzahl Spiele getippt
$show_sp_proz = awc_01(trim($config[12]));  // Quote richtiger Tipps
$show_joker = awc_01(trim($config[13]));  // Jokerpunkte
$show_punkte = awc_01(trim($config[14]));  // Anzahl Punkte

// Zeichen im Tabellenkopf bei der Ausgabe einstellen - Variablen werden in der config angepasst
$var_spiele = awc_txt($config[15]);        // Anzahl Spiele getippt - Standard "Sp"
$var_joker = awc_txt($config[16]);         // durch Joker dazugewonnene Punkte - standard "JP"
$var_prozrichtig = awc_txt($config[17]);   // Prozent Spieltipp richtig - Standard "Sp%"
$var_tippsrichtig = awc_txt($config[18]);  // Anzahl Tipps richtig - Standard "P"

// Zellenhintergrundfarbe für Platz 1 bis 3
$colorplatz1 = awc_css($config[19]);  // Platz 1
$colorplatz2 = awc_css($config[20]);  // Platz 2
$colorplatz3 = awc_css($config[21]);  // Platz 3

// Tabellenfüllabstände
$tablespace = awc_int($config[22], '1');  //1
$tablepad = awc_int($config[23], '2');    //2

// Klammern um Usernamen zeigen
$show_klammern = awc_01(trim($config[24]));  // 1

// Ausrichtung Spalten
$ausrichtung_user = awc_align($config[25]);    // zentriert
$ausrichtung_anzsp = awc_align($config[26]);   // zentriert
$ausrichtung_jokpkt = awc_align($config[27]);  // zentriert
$ausrichtung_prozsp = awc_align($config[28]);  // zentriert
$ausrichtung_anztip = awc_align($config[29]);  // zentriert
$ausrichtung_platz = awc_align($config[30]);   // zentriert
$ausrichtung_team = awc_align($config[31]);    // zentriert

// Punkt hinter Platzierung
$show_platzpkt = awc_01(trim($config[32]));

$show_headline = awc_01(trim($config[33]));  // zentriert


$anfang = 34;   // Endwert + 1

/*
Wenn weitere Einstellungen aus cfg-datei geladen werden: 3 Stellen sind anzupassen
--- zeile 127: for ($i = XX + 1; $i <= count($tipp_modus); $i ++) {
*/

// Anzahl der Tipp-Ligen ermitteln
$zeile = trim($array[1]);  // unnötige Zeilenumbrüche ... entfernen
$anzligen = substr($zeile, 9, strlen($zeile));  // -> eigentlich immer ab der 10ten Stelle

$tipp_modus = @file(PATH_TO_LMO.'/config/tipp/cfg.txt');
if (!is_array($tipp_modus)) {
    $tipp_modus = [];
}

// Tippmodus ermitteln
for ($i = 0; $i < count($tipp_modus); $i++) {
    if (substr($tipp_modus[$i], 0, 9) == 'tippmodus') {
        //echo "tippmodus:".$i;
        $tippmodus = substr($tipp_modus[$i], 10, 1);  // 0=Tendenz  1=Ergebnis
    }
    if (substr($tipp_modus[$i], 0, 9) == 'jokertipp') {
        //echo "jokertipp:".$i;
        $usejoker = substr($tipp_modus[$i], 10, 1);  // 0=nein  1=ja
    }
}  // for end

// Werte aus der Addon-Konfiguration haben Vorrang (falls vorhanden)
if (isset($tipp_tippmodus)) {
    $tippmodus = (int)$tipp_tippmodus;
}
if (isset($tipp_jokertipp)) {
    $usejoker = (int)$tipp_jokertipp;
}

$ver = '1.8.4';  // Version

// eingeloggten User ermitteln
$username = '';
if ((isset($_SESSION['lmotippername']) && $_SESSION['lmotippername'] != '') && (isset($_SESSION['lmotipperok']) && $_SESSION['lmotipperok'] > 0)) {
    //echo "...mach dies, wenn eingeloggt... ". $_SESSION['lmotippername'];
    $username = '['. $_SESSION['lmotippername'] .']';
}
/*
else {
  echo "nicht eingeloggt...";
}
*/

$zurueck = '<br><br><br><b><a href="javascript:history.go(-1);">zurück</a></b><br>';

// HTML Datei generieren
$htmlhead = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
      "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
 <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
 <title>'.awc_h($title).'</title>
<style type="text/css">
body {
font-size: '. $fontsize .'pt;
font-family: '. $fontfamily .';
color: '. $fontcolor .';
background-color: '. $backgroundcolor .';

/* Farbe der Scrollbalken */
  scrollbar-face-color: #B9C4CC;
  scrollbar-track-color: #B9C4CC;
  scrollbar-highlight-color: #B9C4CC;
  scrollbar-3dlight-color: #B9C4CC;
  scrollbar-darkshadow-color: #B9C4CC;
  scrollbar-base-color:#B9C4CC;
  scrollbar-arrow-color:  #889CB0;
  scrollbar-shadow-color: #889CB0;
/* Ende Farbe der Scrollbalken */

}

table.auswert {
  font-size: '. $tablefontsize .'pt;
  color: '. $tablefontcolor .';
  background-color: '. $tablebackcolor .';
  text-align: center;
  border: #8CA0B4 1px dotted;
}

th.auswert {
  background-color: '. $tablehaedercolor .';
}

hr.auswert {
  height: 0px;
  border: dashed #525E6E 0px;
  border-top-width: 1px;
}

acronym.auswert {
  cursor:help;
  border-bottom:1px dotted;
  a:visited  { text-decoration: underline; color: #3E4753; }
}

font.foot {
  font-size: 8pt;
}

font.fehler {
  font-size: '. $fontfehl .';
}

a.foot { text-decoration: overline,underline; color: #3E4753; font-size: 8pt;}

.rechts { text-align:right; }

.auswertheadline {  /*background: #ff0000;*/ text-align: left; }

.ausrichtung_anzsp { text-align: '. $ausrichtung_anzsp .'; }
.ausrichtung_jokpkt { text-align: '. $ausrichtung_jokpkt .'; }
.ausrichtung_prozsp { text-align: '. $ausrichtung_prozsp .'; }
.ausrichtung_anztip { text-align: '. $ausrichtung_anztip .'; }
.ausrichtung_platz { text-align: '. $ausrichtung_platz .'; }
.ausrichtung_team { text-align: '. $ausrichtung_team .'; }

</style>
';

/*if (strlen($stylesheet) > 0) {
  $htmlhead .= '<link rel="stylesheet" href="'.$stylesheet.'" type="text/css">';
}*/

$htmlhead .= '</head>';

/*$htmlfoot = '
<hr class="auswert" width="195" align="right" />
<p style="line-height:12px; margin-top:0px" align="right">
<font class="foot"><a href="http://forum.bcerlbach.de/downloads.php?cat=7" class="foot" target="_blank" title="zum Download">Auswertskript für Webmaster</a> v'. $ver .' &copy; by <a href="http://www.bcerlbach.de" class="foot" target="_blank" title="zur Homepage">Marcus</a></font></p>
</body></html>';*/

$htmlfoot = '<div class="rechts"><small>[<acronym title="Auswertskript von Marcus - www.bcerlbach.de">&copy;</acronym>]</small></div>
</body></html>';


$ligenkurz = [];       // Beinhaltet Kürzel für Ligen
$anzgetipptkurz = [];  // Beinhaltet Kürzel für Anzahl getippter Spiele
$jokerkurz = [];       // Beinhaltet Punkte für Joker

for ($i = $anfang; $i <= count($config) - 1; $i++) {
    if (trim($config[$i]) == '') {
        continue;
    }  // leere Zeilen überspringen (sonst würden alle Zeilen mit "=" gezählt)
    // Ligen aus config übergeben
    array_push($ligenkurz, trim($config[$i]));

    // Anzahl getippter Spiele
    $sg = str_replace('TP', 'SG', trim($config[$i]));
    array_push($anzgetipptkurz, $sg);  //-- kürzel = 'SG'.$i

    // Jokerpunkte
    $jok = str_replace('TP', 'P', trim($config[$i]));
    array_push($jokerkurz, $jok);  //-- joker = 'P6'.$i
}

$zligen = count($ligenkurz);  // Zähler der gewählten Ligen

// Überprüfen dass mind. eine Liga ausgewählt wurde
if ($zligen > 0) {
    $auswert = [];              // ausgefiltertes Array
    $goal = [];  // zweidimensionales Array anlegen

    $anzgoal = -1;

    // for durchläuft jede Zeile der Auswertungsdatei
    for ($i = $anzligen + 3; $i < sizeof($array); $i++) {
        // Usernamen ermitteln, wenn gefunden in Array speichern
        $posname = strpos($array[$i], '[');
        if ($posname !== false) {
            // gefundenen Namen ins Array speichern
            $goal[++$anzgoal] = [$array[$i], 0, 0, 0, ''];
        }

        // foreach1 ermittelt die erzielten Punkte
        foreach ($ligenkurz as $value) {
            $value = $value.'=';  // = muss stehen da bei TP1 auch TP10 TP11 erfasst
            $pos1 = strpos($array[$i], $value);

            if ($pos1 !== false && $anzgoal >= 0) {
                // Punkte gleich Array dazu addieren
                $goal[$anzgoal][1] += (int)ltrim(strrchr($array[$i], '='), '=');
            }
        }  // foreach1 end

        // foreach2 ermittelt die Anzahl an getippten Spielen
        foreach ($anzgetipptkurz as $value) {
            $value = $value.'=';  // = muss stehen da bei TP1 auch TP10 TP11 erfasst
            $pos1 = strpos($array[$i], $value);

            if ($pos1 !== false && $anzgoal >= 0) {
                // Anzahl getippter Spiele gleich Array dazu addieren
                $goal[$anzgoal][2] += (int)ltrim(strrchr($array[$i], '='), '=');
            }
        }  // foreach2 end

        // Wird nur benötigt, wenn die Joker-Punkte angezeigt werden sollen
        if (($show_joker == 1) and ($usejoker == 1)) {
            // foreach3 ermittelt Jokerpunkte
            foreach ($jokerkurz as $value) {
                $value = $value.'=';  // = muss stehen da bei TP1 auch TP10 TP11 erfasst
                $pos1 = strpos($array[$i], $value);

                if ($pos1 !== false && $anzgoal >= 0) {
                    // Anzahl getippter Spiele gleich Array dazu addieren
                    $goal[$anzgoal][3] += (int)ltrim(strrchr($array[$i], '='), '=');
                    // Variable zeigt ob Jokerpunkte genutzt werden, wenn ja joker=1
                    $joker = 1;
                }
            }  // foreach3 end
        }  // if joker

        // wird nur benötigt, wenn Teams angezeigt werden sollen
        if ($show_team == 1) {
            // Teamname ermitteln, wenn gefunden in Array speichern
            $pos1 = strpos($array[$i], 'Team=');
            if ($pos1 !== false && $anzgoal >= 0) {
                // gefundenen Namen ins Array speichern
                $goal[$anzgoal][4] = ltrim(strrchr($array[$i], '='), '=');
                // var. zeigt ob Teams genutzt werden muss, wenn ja team=1
                if (strlen($goal[$anzgoal][4]) != 1) {
                    $team = 1;
                }
            }
        }  // if show_team end



    }  // end for

    /*
      for ($i=0; $i < count($goal); $i++) {
        echo "$i  ".$goal[$i][0]."  ".$goal[$i][1]." ".$goal[$i][2]."  ".$goal[$i][3]."<br>";
      }
    */


    /*-------------------------------------------*/
    /* BUBBLE SORT  des zweidimensionalen Arrays */
    /*-------------------------------------------*/

    $anzahl_elemente = count($goal);  // Anzahl der Elemente ermittlen. -1 da Arrays mit 0 beginnen! ;o)

    // Schleife wird entsprechend der Anzahl der Elemente im Array $zahlen wiederholt
    for ($y = 0; $y < $anzahl_elemente; $y++) {

        // Jedes Element wird einzelen angesprochen und verschoben wenn das linke Element grösser ist als der rechte
        for ($x = 0; $x < $anzahl_elemente - 1; $x++) {

            // In diesem Beispiel aufsteigend.
            // Möchte man absteigend sortieren, einfach das grösser Zeichen mit einem kleiner Zeichen tauschen

            // tauschen wenn:
            // 1. erzielte Punkte unterschiedlich  oder
            // 2. erzielte Punkte gleich + erzielte Punkte > 0 + anz. Tipp unterschiedlich
            if (($goal[$x][1] < $goal[$x + 1][1])
            or (($goal[$x][1] == $goal[$x + 1][1])
            and ($goal[$x][1] > 0)
            and ($goal[$x][2] > $goal[$x + 1][2]))) {

                // Werte werden zwischengespeichert...
                $grosser_wert = $goal[$x][1];
                $kleiner_wert = $goal[$x + 1][1];
                // Namen ebenfalls
                $grosser_name = $goal[$x][0];
                $kleiner_name = $goal[$x + 1][0];
                // Anzahl getippter Spiele
                $grosse_anz = $goal[$x][2];
                $kleine_anz = $goal[$x + 1][2];
                // Joker
                $grosse_joker = $goal[$x][3];
                $kleine_joker = $goal[$x + 1][3];
                // Team
                $grosse_team = $goal[$x][4];
                $kleine_team = $goal[$x + 1][4];

                // ... und anschließen werte vertauschen
                $goal[$x][1] = $kleiner_wert;
                $goal[$x + 1][1] = $grosser_wert;
                // Namen tauschen
                $goal[$x][0] = $kleiner_name;
                $goal[$x + 1][0] = $grosser_name;
                // Anzahl getippter Spiele tauschen
                $goal[$x][2] = $kleine_anz;
                $goal[$x + 1][2] = $grosse_anz;
                // Joker
                $goal[$x][3] = $kleine_joker;
                $goal[$x + 1][3] = $grosse_joker;
                // Team
                $goal[$x][4] = $kleine_team;
                $goal[$x + 1][4] = $grosse_team;
            }  // if end
        }  // for2 end
    }  // for1 end


    /*-------------------------------------------*/
    /* endausgabe des sortierten Arrays          */
    /*-------------------------------------------*/

    $htmlbody .= '
<body>
<table align="center" class="auswert" cellspacing="'.$tablespace.'" cellpadding="'.$tablepad.'" style="text-align:center; color:'.$tablefontcolor.'; ">';

    if ($show_headline == 1) {
        $htmlbody .= '<tr><td class="auswertheadline" colspan="5">'.awc_h($title).'</td></tr>';
    }

    $htmlbody .= '<tr>
      <th class="auswert">Platz</th><th class="auswert" align="'.$ausrichtung_user.'">Name</th>';

    // gesamte getippte Spiele ausgeben?
    if ($show_sp_ges == 1) {
        $htmlbody .= '<th class="auswert"><acronym class="auswert" title="Anzahl Spiele getippt"><u>'.$var_spiele.'</u></acronym></th>';
    }

    // werden Jokerpunkte zugelassen? Wenn ja, spalte einblenden - ja oder nein?
    if (($show_joker == 1) and ($usejoker == 1)) {
        $htmlbody .= '<th class="auswert"><acronym class="auswert" title="durch Joker dazugewonnene Punkte"><u>'.$var_joker.'</u></acronym></th>';
    }

    // Quote richtiger Spieler ausgeben - ja oder nein?
    if ($show_sp_proz == 1) {
        if ($tippmodus == 0) {  // Tendenz
            $htmlbody .= '<th class="auswert"><acronym class="auswert" title="Prozent Spieltipp richtig%"><u>'.$var_prozrichtig.'</u></acronym></th>';
        } elseif ($tippmodus == 1) {  // Ergebnis
            $htmlbody .= '<th class="auswert"><acronym class="auswert" title="Punkte pro Spiel"><u>'.$var_prozrichtig.'</u></acronym></th>';
        }
    }

    // Anzahl Tipps ausgeben - ja oder nein?
    if ($show_punkte == 1) {
        $htmlbody .= '<th class="auswert"><acronym class="auswert" title="Anzahl Tipps richtig"><u>'.$var_tippsrichtig.'</u></acronym></th>';
    }

    // Team ausgeben - ja oder nein?
    if (($show_team == 1) and ($team == 1)) {
        $htmlbody .= '<th class="auswert"><acronym title="Teamzugehörigkeit"><u>'.$var_team.'</u></acronym></th>';
    }


    $htmlbody .= '</tr>
';

    $platz = 0;
    $platz2 = 0;

    // Für HTML aufbereiten
    for ($i = 0; $i <= count($goal) - 1; $i++) {

        // begrenzung Anzahl Tipper
        if ($i == $showtipper) {
            break;
        }

        //wenn Klammern nicht angezeigt werden sollen
        if ($show_klammern == 0) {
            $goal[$i][0] = str_replace('[', '', $goal[$i][0]);
            $goal[$i][0] = str_replace(']', '', $goal[$i][0]);
        }

        // Bedingung, die alle Nicht-Tipper ausfiltert, falls gewünscht
        if (($shownichttipper != 0) or ($goal[$i][2] != 0)) {

            // Wert im Array mit Username vergleichen -> fett darstellen
            if (chop($goal[$i][0]) == $username) {
                $goal[$i][0] = '<b>'. $goal[$i][0] .'</b>';
            }

            $platz++;

            // Ausgabe der Platzierung wenn:
            // 1. Punkte ungleich dem Vorgänger
            // 2. Punkte gleich, aber Anzahl getippter Spiele unterschiedlich
            $vorg1 = $i > 0 ? $goal[$i - 1][1] : null;  // Punkte des Vorgängers
            $vorg2 = $i > 0 ? $goal[$i - 1][2] : null;  // getippte Spiele des Vorgängers
            if (($goal[$i][1] != $vorg1) or (($goal[$i][1] == $vorg1) and (($goal[$i][2] != $vorg2)))) {

                $platz2++;

                if ($platz == 1) {
                    $htmlbody .= '<tr bgcolor="'.$colorplatz1.'"> <td class="ausrichtung_platz">';
                } elseif ($platz == 2) {
                    $htmlbody .= '<tr bgcolor="'.$colorplatz2.'"> <td class="ausrichtung_platz">';
                } elseif ($platz == 3) {
                    $htmlbody .= '<tr bgcolor="'.$colorplatz3.'"> <td class="ausrichtung_platz">';
                } else {
                    $htmlbody .= '<tr bgcolor="'.$tablebackcolor.'"> <td class="ausrichtung_platz">';
                }

                $htmlbody .= $platz;
                if ($show_platzpkt == 1) {
                    $htmlbody .= '.';
                }
            } else {
                if ($platz2 == 1) {
                    $htmlbody .= '<tr bgcolor="'.$colorplatz1.'"> <td>';
                } elseif ($platz2 == 2) {
                    $htmlbody .= '<tr bgcolor="'.$colorplatz2.'"> <td>';
                } elseif ($platz2 == 3) {
                    $htmlbody .= '<tr bgcolor="'.$colorplatz3.'"> <td>';
                } else {
                    $htmlbody .= '<tr bgcolor="'.$tablebackcolor.'"> <td>';
                }
                //$platz2 = $platz-1;
                $htmlbody .= '&nbsp;';
            }

            /*
                // Wenn unterschieldliche Punkte und getippte Spiele -> ausgabe Platz
                if ($goal[$i][1] != $goal[$i-1][1]) {
                  $htmlbody .= $platz;
                }
                // Prüfen auf gleiche Anzahl an getippten Spielen:

                else if ($goal[$i][2] == $goal[$i-1][2]) {
                  $htmlbody .= '&nbsp;';  // wenn ja, wird leerzeichen ausgegeben
                }
                else {  // platz auch ausgeben, wenn Punkte gleich aber unterschiedlich viele Spiele getippt
                  $htmlbody .= $platz;  // wenn nein, wird platz ausgeben
                }
            */

            // Erfolgsquote in Prozent oder in Punkte pro spiel
            if ($goal[$i][2] > 0) {
                if ($tippmodus == 0) {  // Tendenz
                    $quote = $goal[$i][1] / $goal[$i][2] * 100;
                } else {  // Ergebnis
                    $quote = $goal[$i][1] / $goal[$i][2];
                }
            } else {
                $quote = 0;
            }

            $htmlbody .= '</td> <td align="'.$ausrichtung_user.'">'.$goal[$i][0].'</td>';


            // gesamte getippte Spiele ausgeben?
            if ($show_sp_ges == 1) {
                $htmlbody .= '<td class="ausrichtung_anzsp">'.$goal[$i][2].'&nbsp;</td>';
            }

            // Werden Jokerpunkte zugelassen? wenn ja, Spalte einblenden - ja oder nein?
            if (($show_joker == 1) and ($joker == 1)) {
                $htmlbody .= '<td class="ausrichtung_jokpkt">'.$goal[$i][3].'&nbsp;</td>';
            }

            // Quote richtiger Spieler ausgeben - ja oder nein?
            if ($show_sp_proz == 1) {
                $htmlbody .= '<td class="ausrichtung_prozsp">'.round($quote, 2).'&nbsp;</td>';
            }

            // Anzahl Tipps ausgeben - ja oder nein?
            if ($show_punkte == 1) {
                $htmlbody .= '<td class="ausrichtung_anztip">'.$goal[$i][1].'&nbsp;</td>';
            }

            // Teamname anzeigen - ja oder nein?
            if (($show_team == 1) and ($team == 1)) {
                $htmlbody .= '<td class="ausrichtung_team">'.$goal[$i][4].'&nbsp;</td>';
            }

            $htmlbody .= '</tr>
';

        }  // filter Nicht-Tipper end

    }  // for end

    $htmlbody .= '</table>';


} else {  // keine Liga gewählt
    $htmlbody .= '<p align="center"><br><br><font class="fehler">keine Liga gewählt</font><br><br>'.$zurueck.'</p>';
}  // else $zligen > 0; end

// Ausgabe HTML Code an Browser
echo $htmlhead . $htmlbody . $htmlfoot;

clearstatcache();
