=====================
LDAP-Adressbuchexport
=====================

Exportiert das Netresearch Genesis-World-LDAP-Adressbuch
in HTML-Dateien, die von der Suche indiziert werden können.

Ablauf
======
Einfach run.sh ausführen::

    $ ./run.sh

Die HTML-Dateien liegen in ``html/``.


Manuell
--------
1. LDAP-Adressbucheinträge exportieren ::

   $ rm entries/*; php fetch-from-ldap.php

2. HTML generieren ::

   $ rm html/*; php gen-html.php


Probleme
========
Genesis World exportiert nicht alle Attribute über LDAP.
Folgende fehlen:

- Stadt/Ort
- Mobiltelefon
- Bild
- Homepage
- Geburtstag
- Notizen
- Schlagworte

Vor allem das Fehlen von Stadt und Mobiltelefon sind sehr ärgerlich.


Abhängigkeiten
==============
- PHP
- LDAP-Modul für PHP
- PEAR-Paket Net_LDAP2
