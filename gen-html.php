<?php
$map = array(
    'cn' => 'Name',
    'mail' => 'E-Mail',
    'telephoneNumber' => 'Telefonnummer',
    'comp' => array('Adresse', 'renderComp'),
);

$d = dir(__DIR__ . '/entries');
$n = 0;
while (false !== ($name = $d->read())) {
    $fullfile = __DIR__ . '/entries/' . $name;

    $arEntry = unserialize(file_get_contents($fullfile));
    if ($arEntry === false) {
        continue;
    }

    $html = renderHtml($arEntry, $map);
    $file = 'html/' . getFilename($arEntry);
    file_put_contents($file, $html);

    if (++$n > 5) {
        //break;
    }
}


function renderHtml($arEntry, $map)
{
    $name = getName($arEntry);
    $tbody = renderMap($arEntry, $map);
    $entrydump = htmlspecialchars(var_export($arEntry, true));
    return <<<HTM
<?xml version="1.0" encoding="utf-8"?>
<html>
 <head>
  <title>$name</title>
  <style type="text/css">
    table th {
        vertical-align: top;
        text-align: left;
    }
    pre {
        background-color: #EEE;
        font-size: 8px;
    }
  </style>
 </head>
 <body>
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
        if (is_array($title)) {
            list($title, $callback) = $title;
            $value = call_user_func($callback, $arEntry);
        } else {
            if (!isset($arEntry[$field])) {
                continue;
            }
            $value = renderValue($arEntry[$field], $field);
        }
        $html .= sprintf(
            '<tr><th>%s</th><td>%s</td></tr>' . "\n",
            htmlspecialchars($title),
            $value
        );
    }

    return $html;
}

function renderValue($value, $field)
{
    if (is_array($value)) {
        $htmls = array();
        foreach ($value as $single) {
            $htmls[] = renderValue($single, $field);
        }
        $html = implode('<br/>', $htmls);
    } else if ($field == 'mail') {
        $html = sprintf(
            '<a href="mailto:%s">%s</a>',
            htmlspecialchars($value),
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
    if (isset($arEntry['street'])) {
        $html .= $arEntry['street'] . '<br/>';
    }
    if (isset($arEntry['postalCode'])) {
        $html .= $arEntry['postalCode'];
    }
    if (isset($arEntry['l'])) {
        $html .= ' ' . $arEntry['l'];
    }

    return $html;
}

function getName($arEntry)
{
    if (isset($arEntry['cn'])) {
        return $arEntry['cn'];
    } else if (isset($arEntry['sn']) && isset($arEntry['givenName'])) {
        return $arEntry['givenName'] . ' ' . $arEntry['sn'];
    }
    return $arEntry['dn'];
}

function getFilename($arEntry)
{
    if (isset($arEntry['cn'])) {
        $name = $arEntry['cn'];
    } else if (isset($arEntry['sn']) && isset($arEntry['givenName'])) {
        $name = $arEntry['givenName'] . '-' . $arEntry['sn'];
    }

    if (isset($arEntry['o'])) {
        $name .= '-' . $arEntry['o'];
    }
    return str_replace(array(' ', '/'), '-', strtolower($name)) . '.htm';
}

?>