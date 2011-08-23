<?php
require_once 'Net/LDAP2.php';
//ldapsearch -h ldap.nr -x -LLL -b 'dc=netresearch,dc=de' '(sn=Ab*)'
//ldapsearch -h ldap.nr -x -LLL -b 'dc=netresearch,dc=de' '(o=Otto*)'
//ldapsearch -h ldap.nr -x -LLL -b 'dc=netresearch,dc=de' '(|(sn=Otto*)(&(!(sn=*))(o=Otto*)))'

$debug = true;
if (in_array('--quiet', $argv)) {
    $debug = false;
}

$ldapcfg = array(
    'host' => 'ldap.nr',
    'basedn' => 'dc=netresearch,dc=de'
);

$ldap = Net_LDAP2::connect($ldapcfg);
if (Net_LDAP2::isError($ldap)) {
    die('Could not connect to LDAP-server: ' . $ldap->getMessage() . "\n");
}

$count = 0;
foreach (range('a', 'z') as $a) {
    foreach (range('a', 'z') as $b) {
        foreach (array('sn', 'o') as $t) {
            $search = $ldap->search(
                null,
                sprintf(
                    '(%s=%s%s*)',
                    $t, $a, $b
                )
            );
            if (Net_LDAP2::isError($search)) {
                die('Error searching: ' . $search->getMessage() . "\n");
            }

            while ($entry = $search->shiftEntry()) {
                $arEntry = $entry->getValues();
                $arEntry['dn'] = $entry->dn();
                $arEntry['timestamp'] = time();
                file_put_contents(
                    'entries/' . $arEntry['dn'] . '.ser',
                    serialize($arEntry)
                );
                if ($debug) { echo '.'; }
                ++$count;
            }
        }
    }
}
if ($debug) {
    echo "\n$count entries saved\n";
}
?>