<?php
/*------------------------------------------------------------------------------
// Individuelle Auswertung der Tippspielligen für Webmaster
// Autor: Marcus - www.bcerlbach.de
//------------------------------------------------------------------------------

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


//------------------------------------------------------------------------------
// Eigene Funktion zum ermitteln des Dateinames
if (!function_exists('dateiname')) {
    function dateiname($zeile)
    {
        $dateiname = '';
        $pos = strpos($zeile, '=');  // suche nach dem =
        if ($pos++ === false) {
            // nicht gefunden...
        } else {  // gefunden
            $dateiname = substr($zeile, $pos++, strlen($zeile));  // Ligenname
        }
        $dateiname = str_replace('.aus', '.l98', $dateiname);
        return $dateiname;  // z.b. liga1.l98
    }  // function Dateiname end
}
//------------------------------------------------------------------------------

//------------------------------------------------------------------------------
// Hilfsfunktionen zum Bereinigen von Konfigurationswerten (Schutz vor eingeschleustem HTML/CSS)
if (!function_exists('awc_h')) {
    // Text für die HTML-Ausgabe
    function awc_h($v)
    {
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    }
    // Wert für CSS (Farben, Schriftart, Größen): nur harmlose Zeichen
    function awc_css($v, $def = '')
    {
        $v = trim((string)$v);
        return preg_match('/^[A-Za-z0-9#,.% _-]{0,80}$/', $v) ? $v : $def;
    }
    // einzeiliger Text ohne HTML-Tags
    function awc_txt($v)
    {
        return trim(str_replace(["\r", "\n"], ' ', strip_tags((string)$v)));
    }
    // ganze Zahl (auch negativ) oder Standardwert
    function awc_int($v, $def = '0')
    {
        $v = trim((string)$v);
        return preg_match('/^-?[0-9]{1,4}$/', $v) ? (string)(int)$v : (string)$def;
    }
    // 0/1
    function awc_01($v)
    {
        return ((string)$v === '1') ? '1' : '0';
    }
    // Ausrichtung
    function awc_align($v)
    {
        $v = trim((string)$v);
        return in_array($v, ['left', 'center', 'right'], true) ? $v : 'center';
    }
}
//------------------------------------------------------------------------------


//------------------------------------------------------------------------------
// Eigene Funktion zum Ermitteln der Ligeninfo
if (!function_exists('dateiinfo')) {
    function dateiinfo($datei)
    {
        $dateiname = $datei;  // wird benötigt, falls Datei nicht vorhanden
        $datei = getcwd() . '/ligen/'. basename((string)$datei);  // Ligenpfad

        // überprüfen ob Ligendatei existiert
        if (is_file($datei)) {
            $liga = file($datei);  // File wird in Array eingelesen
            $dateiinfo = str_replace('Name=', '', trim($liga[2] ?? ''));  // Ligainfo in der 3ten Zeile
            $liga = '';
        } else {
            // wenn Datei nicht vorhanden -> Dateiname als Info verwenden
            $dateiinfo = $dateiname;
            $dateiname = '';
        }
        return $dateiinfo;
    }  // function Ligeninfo end
}
//------------------------------------------------------------------------------
