<?php

// src/App/Command/ImportAdressbuchCommand.php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

use Doctrine\ORM\EntityManagerInterface;

use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;

class ImportBuberCommand extends Command
{
    protected $FIELD_MAP = [
        'key' => 'identifier',
        'persNames' => 'name',
        'GND-Nummer' => 'gnd',
    ];

    protected $DATAPROVIDER = 6; // TODO: come up with a table

    protected $em;
    protected $slugify;
    protected $dataDir;

    public function __construct(EntityManagerInterface $em,
                                \Cocur\Slugify\SlugifyInterface $slugify,
                                ParameterBagInterface $params)
    {
        parent::__construct();

        $this->em = $em;
        $this->slugify = $slugify;

        $this->dataDir = $params->get('kernel.project_dir') . '/data';
    }

    protected function configure()
    {
        $this
            ->setName('import:buber')
            ->setDescription('Import Buber Correspondence')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $fname = $this->dataDir
               . '/buberpersons.xlsx';

        $fs = new Filesystem();

        if (!$fs->exists($fname)) {
            $output->writeln(sprintf('<error>%s does not exist</error>',
                                     $fname));

            return 1;
        }

        $reader = ReaderEntityFactory::createReaderFromFile($fname);

        $reader->open($fname);

        $count = 0;

        $personRepository = $this->em->getRepository('\App\Entity\Person');

        $headers = [];

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $rowObj) {
                $values = $rowObj->toArray();

                for ($i = 0; $i < count($values); $i++) {
                    if ($values[$i] instanceof \DateTime) {
                        $values[$i] = $values[$i]->format('Y-m-d');
                    }
                }

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
                foreach ($headers as $i => $src) {
                    if (!array_key_exists($src, $this->FIELD_MAP)) {
                        continue;
                    }

                    $target = $this->FIELD_MAP[$src];

                    $row[$target] = array_key_exists($i, $values)
                        && '' !== trim($values[$i])
                        ? trim($values[$i])
                        : null;
                }

                if (empty($row['identifier'])) {
                    continue;
                }

                $parts = explode(', ', $row['name'], 2);
                $row['familyName'] = $parts[0];
                if (2 == count($parts)) {
                    $row['givenName'] = $parts[1];
                }

                $output->writeln('Insert/Update: ' . $row['identifier']);

                $person = $personRepository->findOneBy([
                    'dataProvider' => $this->DATAPROVIDER,
                    'identifier' => $row['identifier'],
                ]);

                if (is_null($person)) {
                    $person = new \App\Entity\Person();
                    $person->identifier = $row['identifier'];
                    $person->dataProvider = $this->DATAPROVIDER;
                }

                foreach ($row as $property => $value) {
                    switch ($property) {
                        case 'name':
                        case 'familyName':
                        case 'givenName':
                        case 'hasOccupation':
                        case 'disambiguatingDescription':
                        case 'birthDate':
                        case 'deathDate':
                        case 'birthPlaceLabel':
                        case 'deathPlaceLabel':
                        case 'gender':
                        case 'wikidata':
                        case 'url':
                            $setter = 'set' . ucfirst($property);
                            if (method_exists($person, $setter)) {
                                $person->$setter($value);
                            }
                            else {
                                $person->$property = $value;
                            }

                            break;

                        case 'wikidata:P7571':
                            $person->setExternalIdentifier($property, $value);
                            break;

                        default:
                            // $output->writeln('Skip : ' . $property);
                    }
                }

                $name = $person->getFullname(true);
                $person->disambiguatingDescription = 'Buber Korrespondenz';
                $nameSlug = $this->slugify->slugify($person->getName());

                if ('a' != $nameSlug[0]) {
                    // currently only letter a
                    continue;
                }

                $this->em->persist($person);
            }

            break; // only get first sheet
        }

        $this->em->flush();

        return 0;
    }
}
