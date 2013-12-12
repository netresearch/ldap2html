#!/bin/sh
if [ ! -d entries ]; then
    mkdir entries
else
    rm entries/*
fi
php fetch-from-ldap.php --quiet || exit $?

if [ ! -d html ]; then
    mkdir html
else
    rm html/*
fi
php gen-html.php || exit $?
