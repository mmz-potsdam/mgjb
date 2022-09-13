<?php

/**
 * Naive match (lastname_firstname) of
 * aufbau-indexing.xlsx (7213 Entries with Berlin-relation)
 * against the Juedisches Adressbuch fuer Gross-Berlin (1929/30, 1931)
 * ../juedisches-adressbuch-gross-berlin-1931.xlsx
 *
 * Run as
 *
 *  php aufbau-berlin-addressbook.php > adressbuch-berlin-aufbau-indexing.tsv
 *
 * for a Tab-separated list of possible matches
 */
require_once 'vendor/autoload.php';

use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;

use Cocur\Slugify\Slugify;

$aufbauIn = 'aufbau-indexing.xlsx';
$addressbookIn = '../juedisches-adressbuch-gross-berlin-1931.xlsx';

function add_candidate(&$candidates, $key, $row) {
    if (!array_key_exists($key, $candidates)) {
        $candidates[$key] = [];
    }

    $candidates[$key][] = $row;
}

$slugify = new Slugify();

$candidates = [];

$aufbauReader = ReaderEntityFactory::createReaderFromFile($aufbauIn);
$aufbauReader->open($aufbauIn);

$headers = [];
foreach ($aufbauReader->getSheetIterator() as $sheet) {
    foreach ($sheet->getRowIterator() as $rowObj) {
        $values = $rowObj->toArray();

        $unique_values = array_unique(array_values($values));
        if (1 == count($unique_values) && null === $unique_values[0]) {
            // all values null
            continue;
        }

        if (empty($headers)) {
            $headers = $values;
            continue;
        }

        // make assoc
        $row = [];
        foreach ($headers as $i => $key) {
            $value = array_key_exists($i, $values)
                ? trim(str_replace("\xc2\xa0", ' ', $values[$i])) // replace &nbsp;
                : null;

            // some cleanup, improve for brackets, titles, ...
            while (preg_match('/^\"(.*)\"$/', $value)) {
                $value = trim(preg_replace('/^\s*"(.*)"\s*$/', '\1', $value));
            }

            $row[$key] = $value;
        }

        if (is_null($row['GermanOrigin']) || !preg_match('/Berlin/i', $row['GermanOrigin'])) {
            // for a first try, only look for explicitely Berlin-tagged records
            continue;
        }

        $firstNames = preg_split('/\s+\&\s+/', $row['FirstName']);
        if (!empty($row['FN2'])) {
            $firstNames[] = $row['FN2'];
        }

        // unique non-empty firstNames
        $firstNames = array_diff(array_unique($firstNames), ['']);

        if (0 == count($firstNames)) {
            // just last name
            $key = $slugify->slugify($row['LastName'], '_');
            add_candidate($candidates, $key, $row);
        }
        else {
            // first and last name combinations
            foreach ($firstNames as $firstName) {
                $nameParts = [ $row['LastName'], $firstName ];
                $key = $slugify->slugify(join(' ', $nameParts), '_');
                add_candidate($candidates, $key, $row);
            }

            if (!empty($row['FormerName'])) {
                $nameParts = [ $row['FormerName'], $firstNames[count($firstNames) - 1] ];
                $key = $slugify->slugify(join(' ', $nameParts), '_');
                add_candidate($candidates, $key, $row);
            }
        }

        /*
        // for testing, finish early
        if (count($candidates) > 50) {
            break;
        }
        */
    }

    break; // only first $sheet
}
$aufbauHeaders = $headers;
$aufbauReader->close();
// echo join("\n", array_keys($candidates));

$addressbookReader = ReaderEntityFactory::createReaderFromFile($addressbookIn);
$addressbookReader->open($addressbookIn);

$matches = [];

$headers = [];
foreach ($addressbookReader->getSheetIterator() as $sheet) {
    foreach ($sheet->getRowIterator() as $rowObj) {
        $values = $rowObj->toArray();

        $unique_values = array_unique(array_values($values));
        if (1 == count($unique_values) && null === $unique_values[0]) {
            // all values null
            continue;
        }

        if (empty($headers)) {
            $headers = $values;
            continue;
        }

        // make assoc
        $row = [];
        foreach ($headers as $i => $key) {
            $value = array_key_exists($i, $values)
                ? trim(str_replace("\xc2\xa0", ' ', $values[$i])) // replace &nbsp;
                : null;

            $row[$key] = $value;
        }

        $nameParts = [ $row['Familienname'] ];
        if (!empty($row['Vorname'])) {
            $nameParts[] = $row['Vorname'];
        }

        $key = $slugify->slugify(join(' ', $nameParts), '_');

        if (array_key_exists($key, $candidates)) {
            if (!array_key_exists($key, $matches)) {
                $matches[$key] = [
                    'aufbau' => $candidates[$key],
                    'addressbuch' => [],
                ];
            }

            $matches[$key]['adressbuch'][] = $row;

            // break; // for testing, finish early
        }
    }

    break; // only first $sheet
}

$addressbookReader->close();

if (!empty($matches)) {
    $fullHeaders = array_merge($headers, $aufbauHeaders);
    echo join("\t", $fullHeaders) . "\n";

    foreach ($matches as $match) {
        foreach ($match['adressbuch'] as $candidate) {
            $row = [];
            foreach ($headers as $key) {
                $row[] = array_key_exists($key, $candidate)
                    ? $candidate[$key] : '';
            }

            echo join("\t", $row) . "\n";
        }

        foreach ($match['aufbau'] as $candidate) {
            $row = [];

            foreach ($headers as $key) {
                $row[] = '';
            }

            foreach ($aufbauHeaders as $key) {
                $row[] = array_key_exists($key, $candidate)
                    ? $candidate[$key] : '';
            }

            echo join("\t", $row) . "\n";
        }
    }
}
