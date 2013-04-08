=== Affiliate Power ===
Contributors: JonasBreuer
Donate link: http://www.j-breuer.de/wordpress-plugins/affiliate-power/
Tags: affiliate, tracking, überblick, provisionen, subid, sales, leads, affili, affili.net, zanox, tradedoubler, belboon, superclix, cj, commission junction
Requires at least: 3.1
Tested up to: 3.5.1
Stable tag: trunk
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html


Finde automatisch heraus welche Artikel, Besucherquellen, Keywords etc. zu welchen Affiliate-Einnahmen geführt haben.

== Description ==

Mit Affiliate Power werden deine erzielten Affiliate-Einnahmen automatisch deinen Artikeln und Seiten zugeordnet. So kannst du herausfinden, welcher Artikel sich gelohnt hat und welcher weniger. Alle Daten werden dabei auf deinem Server ausgewertet und sind somit sicher.

Bei Fragen oder Problemen hinterlasse einfach einen Kommentar auf der [Plugin Seite](http://www.j-breuer.de/wordpress-plugins/affiliate-power/). Ich helfe gerne.

= Premium Version =

Zusätzlich zur kostenlosen Basic-Version gibt es eine Premium-Version des Plugins. Da sich das Plugin in der Beta-Phase befindet ist auch die Premium-Version momentan kostenlos. Mehr Infos zur Teilnahme am Betatest gibt es auf der [Plugin Seite](http://www.j-breuer.de/wordpress-plugins/affiliate-power/#premium).

= Features =

Features die nur in der Premium-Version verfügbar sind, sind *kursiv*

* Aktuell verfügbare Netzwerke: affili.net, commission junction, belboon, superclix, tradedoubler, zanox
* Automatische Zuweisung von Einnahmen zu Artikeln, Seiten, *Landing-Pages, Besucherquellen und Keywords*
* Filterung des Imports nach Webseiten
* Übersicht über alle Leads und Sales (ausführlich und als Dashboard-Widget)
* Automatische Infomail bei neuen oder geänderten Leads und Sales
* Export aller Leads und Sales als Excel-CSV
* Statistiken über einen frei wählbaren Zeitraum sortiert nach Artikeln, Partnern, Netzwerken, *Landing-Pages, Besucherquellen und Keywords*
* Deine Daten bleiben in deiner Datenbank


== Installation ==

Wie immer.

1. Lade das Verzeichnis `affiliate-power` in `/wp-content/plugins/` hoch (oder installiere das Plugin über den Plugin-Manager von Wordpress)
2. Aktiviere das Plugin über den Plugin-Manager von Wordpress.
3. Das Plugin erstellt ein eigenes Untermenü `Affiliate Power`. Unter `Einstellungen` kannst du das Artikel-Tracking aktivieren und die Daten der Affiliate-Netzwerke hinterlegen
4. Im Menüpunkt `Leads/Sales` kannst du nach dem Eintrag der Netzwerk-Daten auf `Transaktionen aktualisieren` klicken, um deine bisheringen Transaktionen zu importieren
5. Das Plugin aktualisiert künftig einmal täglich automatisch deine Transaktionen.


== Frequently Asked Questions ==

= Sind meine Daten sicher? =

Ja, alle Daten bleiben auf deinem Server.

= Werden noch weitere Netzwerke hinzugefügt? = 

Auf jeden Fall! Hinterlasse doch einen Kommentar auf der [Plugin Seite](http://www.j-breuer.de/wordpress-plugins/affiliate-power/), welches Netzwerk dir noch fehlt.

= Was ist mit dem Amazon Partnerprogramm? =

Amazon bietet leider kein vernünftiges Tracking an. Daher wird das Amazon Partnerprogramm vorerst nicht in das Plugin integriert.

= Funktioniert das Plugin mit einem Link-Cloaker wie Pretty Link? = 

Ja, bei Pretty Links wird das Artikel-Tracking automatisch integriert. Bei anderen Link-Cloakern funktioniert leider nur der Download der Transaktionen in den Adminbereich.

= Kann ich das Plugin zusammen mit einem eigenen SubId Tracking benutzen =

Ja, du kannst das Plugin benutzen, solltest allerdings das Artikel Tracking nicht aktivieren, da dieses ebenfalls Gebrauch von den SubIds macht.

= Wie oft werden die Leads / Sales aktualisiert? =

Einmal täglich findet eine automatische Aktualisierung statt. Du kannst im Backend zusätzlich jederzeit eine manuelle Aktualisierung auslösen.


== Screenshots ==

1. Berechnung der Einnahmen für jeden Artikel
2. Übersicht über die erzielten Provisionen

== Changelog ==

= 0.6.2 =
* Bug gefixt, durch den die SubId bei Belboon Deeplinks nicht richtig angehängt wurde
* Performance-Verbesserung bei Sales-Übersicht und Statistik

= 0.6.0 =
* Frei wählbarer Zeitraum bei den Statistiken
* Robusteres Tracking
* Einführung Premium-Version
* Code aufgeräumt
* Kleine Bugfixes

= 0.5.1 =
* Bug gefixt, bei dem Leads und Sales bei Bestätigung auf 0€ gesetzt wurden (Hotfix)

= 0.5.0 =
* Neues Netzwerk: Commission Junction
* Optionale Infomail bei neuen oder geänderten Leads und Sales
* Dashboard-Widget zur Übersicht über die Einnahmen
* CSV-Export für Excel
* Wordpress 3.5 Kompatibilität
* Kleine Usability-Verbesserungen

= 0.4.0 =
* Neues Netzwerk: Superclix
* Neue Statistiken: Netzwerke, Tage, Wochen
* Neue Spalten in der Transaktions-Übersicht: Typ (Sale oder Lead), Einkaufswert
* Einstellungsseite überarbeitet (Untersektionen, Erklärungstexte)
* Performance Verbesserungen
* Kleine Bugfixes
* Code aufgeräumt

= 0.3.2 =
* Artikel-Tracking von Zanox Deeplinks gefixt (Hotfix)

= 0.3.1 =
* Zuweisung von Belboon Sales zu Artikeln gefixt (Hotfix)

= 0.3.0 =
* Integration Belboon
* Filter nach Webseiten
* Pretty Link Tracking auf der Startseite gefixt
* Kleine Bugfixes

= 0.2.0 =
* Integration in Pretty Links

= 0.1.0 =
* Erste Beta-Version
