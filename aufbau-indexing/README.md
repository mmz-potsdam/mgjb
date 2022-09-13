# The Aufbau Indexing Project

https://calzareth.com/aufbau/ contains about 57,000 names that appeared
in Aufbau between 1941 and 2003 in the announcements of birth,
engagement, marriage, death and other special occasions.

It can be searched at https://calzareth.com/aufbau/search.html


## Installation

To get the dependencies for the PHP scripts, type

    composer install

## Parse the search results into aufbau-indexing.xlsx

    php aufbau-indexing-create.php

This requires the page
    https://calzareth.com/aufbau/2010db.php?LastNameKind=exact&LastNameMax=&LN2Kind=exact&LN2Max=&FirstNameKind=exact&FirstNameMax=&FN2Kind=exact&FN2Max=&FormerNameKind=exact&FormerNameMax=&EventDateKind=exact&EventDateMax=&LNcomboKind=contains&LNcomboMax=&FNComboKind=contains&FNComboMax=&GermanOriginKind=contains&GermanOriginMax=&EventKind=exact&EventMax=&IssueDateKind=exact&IssueDateMax=&AddlDataKind=exact&AddlDataMax=&IssueYearKind=between&IssueYearMin=&IssueYearMax=&IssueLinkKind=exact&IssueLinkMax=&offset=1&pagesize=60000
being stored locally as `Aufbau Indexing Project One-Step Search Results.htm`

## Naive match of aufbau-indexing.xlsx against the
Jüdisches Adressbuch für Gross-Berlin (1929/30, 1931)

This script builds simple name slugs (lastname_firstname) to present
possible matches auf the Aufbau Index with the Jewish Adress books

It only takes records in the Aufbau Index containing `berlin` in the
`GermanOrigin` column

    php aufbau-berlin-addressbook.php > adressbuch-berlin-aufbau-indexing.tsv

For ease of use, the resultung Tab-separated file is available as
adressbuch-berlin-aufbau-indexing.xlsx
