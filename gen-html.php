<?php
$map = array(
    'cn' => 'Name',
    'comp' => array('Adresse', 'renderComp'),
    'mail' => 'E-Mail',
    'telephoneNumber' => 'Telefonnummer',
);

$d = dir(__DIR__ . '/entries');
$n = 0;
$list = array();
while (false !== ($name = $d->read())) {
    $fullfile = __DIR__ . '/entries/' . $name;

    $arEntry = unserialize(file_get_contents($fullfile));
    if ($arEntry === false) {
        continue;
    }

    $html = renderHtml($arEntry, $map);
    $file = getFilename($arEntry);
    file_put_contents('html/' . $file, $html);

    $listname = getListName($arEntry);
    $list[$listname] = sprintf(
        '<li><a href="%s">%s</a></li>' . "\n",
        htmlspecialchars($file),
        htmlspecialchars($listname)
    );

    if (++$n > 5) {
        //break;
    }
}

ksort($list);
$listhtml = implode('', $list);

file_put_contents(
    'html/index.htm',
    <<<HTM
<?xml version="1.0" encoding="utf-8"?>
<html>
 <head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <title>GW-Adressbuch</title>
 </head>
 <body>
  <ul>
   $listhtml
  </ul>
 </body>
</html>
HTM
);


function renderHtml($arEntry, $map)
{
    $name = getName($arEntry);
    $tbody = renderMap($arEntry, $map);
    $entrydump = htmlspecialchars(var_export($arEntry, true));
    return <<<HTM
<?xml version="1.0" encoding="utf-8"?>
<html>
 <head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <title>$name - Adressbuch</title>
  <style type="text/css">
    table th {
        vertical-align: top;
        text-align: left;
    }
    table {
        border-collapse: separate;
        border-spacing: 0.5em 1em;
    }
    pre {
        background-color: #EEE;
        font-size: 8px;
    }
    div.back {
        background-color: #EEE;
        font-size: 10px;
    }
  </style>
 </head>
 <body>
  <div class="back">
   <a href="index.htm">index</a>
  </div>
  <table>
   <tbody>
  $tbody
   </tbody>
  </table>
  <pre>$entrydump</pre>
 </body>
</html>
HTM;
}

function renderMap($arEntry, $map)
{
    $html = '';
    foreach ($map as $field => $title) {
        $value = '';
        if (is_array($title)) {
            list($title, $callback) = $title;
            $value = call_user_func($callback, $arEntry);
        } else if (isset($arEntry[$field])) {
            $value = renderValue($arEntry[$field], $field, $arEntry);
        }
        if ($value == '') {
            continue;
        }
        $html .= sprintf(
            '<tr><th>%s</th><td>%s</td></tr>' . "\n",
            htmlspecialchars($title),
            $value
        );
    }

    return $html;
}

function renderValue($value, $field, $arEntry)
{
    if (is_array($value)) {
        $htmls = array();
        foreach ($value as $single) {
            $htmls[] = renderValue($single, $field, $arEntry);
        }
        $html = implode('<br/>', $htmls);
    } else if ($field == 'mail') {
        $html = sprintf(
            '<a href="mailto:%s">%s</a>',
            htmlspecialchars(getName($arEntry) . ' <' . $value . '>'),
            htmlspecialchars($value)
        );
    } else if ($field == 'telephoneNumber') {
        $html = sprintf(
            '<a href="tel:%s">%s</a>',
            str_replace(
                array('+', ' ', '-', '/'),
                array('00', '', '', ''),
                $value
            ),
            htmlspecialchars($value)
        );
    } else {
        $html = htmlspecialchars($value);
    }
    return $html;
}

function renderComp($arEntry)
{
    $html = '';
    if (isset($arEntry['o'])) {
        $html .= $arEntry['o'] . '<br/>';
    }
    if (isset($arEntry['ou'])) {
        $html .= 'Abteilung ' . $arEntry['ou'] . '<br/>';
    }
    if (isset($arEntry['street'])) {
        $html .= $arEntry['street'] . '<br/>';
    }
    if (isset($arEntry['postalCode'])) {
        $html .= $arEntry['postalCode'];
    }
    if (isset($arEntry['l'])) {
        $html .= ' ' . $arEntry['l'];
    }

    if (isset($arEntry['postalCode']) && isset($arEntry['street'])) {
        $html .= sprintf(
            ' <a href="%s">map</a>',
            'http://maps.google.de/?q='
            . urlencode($arEntry['street'] . ', ' . $arEntry['postalCode'])
        );
    }

    return $html;
}

function getName($arEntry)
{
    if (isset($arEntry['cn'])) {
        return $arEntry['cn'];
    } else if (isset($arEntry['sn']) && isset($arEntry['givenName'])) {
        return $arEntry['givenName'] . ' ' . $arEntry['sn'];
    } else if (isset($arEntry['o'])) {
        //Firma
        return $arEntry['o'];
    }
    return $arEntry['dn'];
}

function getListName($arEntry)
{
    if (isset($arEntry['sn']) && isset($arEntry['givenName'])) {
        return $arEntry['sn'] . ', ' . $arEntry['givenName'];
    } else if (isset($arEntry['cn'])) {
        return $arEntry['cn'];
    } else if (isset($arEntry['o'])) {
        //Firma
        return $arEntry['o'];
    }
    return $arEntry['dn'];
}

function getFilename($arEntry)
{
    $name = '';
    if (isset($arEntry['cn'])) {
        $name = $arEntry['cn'];
    } else if (isset($arEntry['sn']) && isset($arEntry['givenName'])) {
        $name = $arEntry['givenName'] . '-' . $arEntry['sn'];
    }

    if (isset($arEntry['o'])) {
        if ($name != '') {
            $name .= '-';
        }
        $name .= $arEntry['o'];
    }
    return str_replace(array(' ', '/'), '-', strtolower($name)) . '.htm';
}

?>