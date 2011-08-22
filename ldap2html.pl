#!/usr/bin/perl
########################################################################
# File:           publicfiles/ldap2html.pl                             #
# myLinux Server: Copyright (c) 2003 Michael Oberg                     #
# Version:        0.92                                                 #
# Author:         Michael Oberg <michael.oberg@fourier-group.de>       #
#                                                                      #
# This program is free software; you can redistribute it and/or modify #
# it under the terms of the GNU General Public License as published by #
# the Free Software Foundation; either version 2 of the License, or    #
# (at your option) any later version.                                  #
#                                                                      #
# This program is distributed in the hope that it will be useful,      #
# but WITHOUT ANY WARRANTY; without even the implied warranty of       #
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the         #
# GNU General Public License for more details.                         #
#                                                                      #
# You should have received a copy of the GNU Public License along      #
# with this package; if not, write to the Free Software Foundation,    #
# Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.       #
########################################################################

# ----------------------------------------------------------- #
# This script can be copied to another world readable         #
# location an can be executed anonymous by any user. It reads #
# only world readable LDAP entries and exports these to an    #
# html table file. This file can be imported into Microsoft   #
# Excel or OpenOffice Calc, or can be used as data source for #
# serial documents in Microsoft Word or OpenOffice Writer.    #
# If Microsoft Office 2000/XP Web Components are installed,   #
# the Internet Explorer will display the HTML page as Excel   #
# Data Sheet.                                                 #
# ----------------------------------------------------------- #

use mylinux::env;

# if called as cgi script, a http header has to be written
#print "Content-type: text/html\n\n";
print "Content-type: application/vnd.ms-excel\n\n";

#@ATTRLIST = ("dn", "cn", "givenName", "sn", "personalTitle", "title", "o", "ou", "l", "st", "c", "postalAddress", "postalCode", "physicalDeliveryOfficeName", "telephoneNumber", "mobile", "facsimileTelephoneNumber", "homePostalAddress", "homePhone", "otherFacsimileTelephoneNumber", "URL", "mail", "comment");
@ATTRLIST = ("cn", "givenName", "sn", "personalTitle", "title", "o", "l", "st", "c", "postalAddress", "postalCode", "physicalDeliveryOfficeName", "telephoneNumber", "mobile", "facsimileTelephoneNumber", "URL", "mail", "comment");

$output  = "<html>\n<head>\n";
$output .= "<meta http-equiv=Content-Type content='application/vnd.ms-excel; charset=iso-8859-1'>\n";
$output .= "<meta name=ProgId content=Excel.Sheet>\n";
$output .= "<meta name=Generator content='Microsoft Excel 9'>\n";
$output .= "<title>myLinux Addressbook</title>\n";
$output .= "</head>\n<body>\n<table border=1>\n<tr>\n";
foreach $attribute (@ATTRLIST) {
  $tableline{$attribute} = "";
  $output .= "<th>" . $attribute . "</th>\n";
}
$output .= "</tr>\n";

$LDAP  = `ldapsearch -x -LLL -b 'ou=users,$env::ldapbase' '(objectclass=person)'`;
$LDAP .= `ldapsearch -x -LLL -b 'ou=contacts,$env::ldapbase' '(&(objectclass=person)(!(givenName=Vorname)))'`;

while ($LDAP =~ /:: (.*)/) {
  $UUENCODED = $1;
  $UTF8 = `echo -n "$UUENCODED" | /usr/local/bin/base64 -d`;
  $STRING = pack ("C*", unpack ("U*", $UTF8));

  $LDAP =~ s/:: $UUENCODED/: $STRING/g;
}

foreach $entry (split /\n\n/, $LDAP) {
  foreach $line (split /\n/, $entry) {
    $line =~ /([^:]*): (.*)/;
    $name = $1;
    $value = $2;

    if ($value =~ /http:\/\//) {
      $value = "<a target=_blank href=\"$value\">$value</a>";
    }
    if ($value =~ /^[^@]+@[^@]+$/) {
      $value = "<a target=_blank href=\"mailto:$value\">$value</a>";
    }

    if ($tableline{$name}) {
      $tableline{$name} .= ", $value";
    }
    else {
      $tableline{$name} = $value;
    }
  }

  $outline = "<tr>\n";
  foreach $attribute (@ATTRLIST) {
    $outline .= "<td>" . $tableline{$attribute} . "</td>\n";
    $tableline{$attribute} = "";
  }
  $outline .= "</tr>\n";

  $output .= $outline;
}

$output .= "</table>\n</body>";

$output =~ s/ä/\&auml;/gs;
$output =~ s/ö/\&ouml;/gs;
$output =~ s/ü/\&uuml;/gs;
$output =~ s/Ä/\&Auml;/gs;
$output =~ s/Ö/\&Ouml;/gs;
$output =~ s/Ü/\&Uuml;/gs;
$output =~ s/ß/\&szlig;/gs;

print $output;
