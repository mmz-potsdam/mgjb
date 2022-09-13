<?php

/**
 * Put the search results of https://calzareth.com/aufbau/2010db.php
 * into aufbau-indexing.xlsx
 *
 * For more information about The AUFBAU Indexing Project
 * see https://calzareth.com/aufbau/
 *
 */
require_once 'vendor/autoload.php';

use Symfony\Component\DomCrawler\Crawler;

use Box\Spout\Writer\Common\Creator\WriterEntityFactory;

// grab HTML from https://calzareth.com/aufbau/2010db.php?LastNameKind=exact&LastNameMax=&LN2Kind=exact&LN2Max=&FirstNameKind=exact&FirstNameMax=&FN2Kind=exact&FN2Max=&FormerNameKind=exact&FormerNameMax=&EventDateKind=exact&EventDateMax=&LNcomboKind=contains&LNcomboMax=&FNComboKind=contains&FNComboMax=&GermanOriginKind=contains&GermanOriginMax=&EventKind=exact&EventMax=&IssueDateKind=exact&IssueDateMax=&AddlDataKind=exact&AddlDataMax=&IssueYearKind=between&IssueYearMin=&IssueYearMax=&IssueLinkKind=exact&IssueLinkMax=&offset=1&pagesize=60000
$fnameIn = 'Aufbau Indexing Project One-Step Search Results.htm';
$fnameOut = 'aufbau-indexing.xlsx';

$crawler = new Crawler(file_get_contents($fnameIn));

$writer = WriterEntityFactory::createXLSXWriter();
$writer->openToFile($fnameOut); // write data to a file or to a PHP stream

$crawler = $crawler->filter('body table');

$crawler->filter('tr')->each(function (Crawler $node, $i) use ($writer) {
    $cells = $node->filter('td')
        ->reduce(function (Crawler $node, $i) {
            if (($i / 2) >= 10 && ($i / 2) < 14) {
                return false;
            }

            // filters every other node
            return ($i % 2) == 0;
        });

    if (0 == $i) {
        // header
        $values = $cells->each(function (Crawler $cell, $i) {
            if (0 == $i) {
                return 'ID';
            }

            return $cell->text();
        });

        $values[] = 'IssueUrl';
    }
    else {
        $values = $cells->each(function (Crawler $cell, $i) {
            if ($i == 10) {
                $a = $cell->filter('a')->first();
                if ($a->count() > 0) {
                    $href = $a->attr('href');

                    return  $cell->text() . '|' . $href;
                }
            }

            return $cell->text();
        });

        if (count($values) >= 10) {
            $parts = explode('|', $values[10], 2);
            $values[10] = $parts[0];
            $values[] = count($parts) > 1 ? $parts[1] : '';
        }
    }

    $row = WriterEntityFactory::createRowFromArray($values);
    $writer->addRow($row);
});

$writer->close();
