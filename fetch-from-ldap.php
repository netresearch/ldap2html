<?php
require_once 'Net/LDAP2.php';

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
        $search = $ldap->search(
            null, sprintf('(sn=%s%s*)', $a, $b)
        );
        if (Net_LDAP2::isError($search)) {
            die('Error searching: ' . $search->getMessage() . "\n");
        }

        while ($entry = $search->shiftEntry()) {
            $arEntry = $entry->getValues();
            $arEntry['dn'] = $entry->dn();
            file_put_contents(
                'entries/' . $arEntry['dn'] . '.ser',
                serialize($arEntry)
            );
            echo '.';
            ++$count;
        }
    }
}
echo "\n$count entries saved\n";
?>