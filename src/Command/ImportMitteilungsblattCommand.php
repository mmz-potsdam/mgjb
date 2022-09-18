<?php

// src/App/Command/ImportAdressbuchCommand.php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

use Doctrine\ORM\EntityManagerInterface;

class ImportMitteilungsblattCommand extends Command
{
    protected $FIELD_MAP = [
        'Identifier' => 'identifier',
        'Entry' => 'name',
        'Omeka' => 'url',
        'Wikidata' => 'wikidata',
        'RelatedLocations' => 'coverageLocation',
        'StartYear' => 'coverageDateStart',
        'endYear' => 'coverageDateEnd',
    ];

    protected $DATAPROVIDER = 4; // TODO: come up with a table

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
            ->setName('import:mitteilungsblatt')
            ->setDescription('Import Mitteilungsblatt Name Register')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $fname = $this->dataDir
               . '/MB_namenregister.json';

        $fs = new Filesystem();

        if (!$fs->exists($fname)) {
            $output->writeln(sprintf('<error>%s does not exist</error>',
                                     $fname));

            return 1;
        }

        $data = json_decode(file_get_contents($fname), true);

        $count = 0;

        $personRepository = $this->em->getRepository('\App\Entity\Person');

        foreach ($data as $identifier => $values) {
            $row = [
                'identifier' => $identifier,
            ];

            if (array_key_exists('name', $values)) {
                foreach ([ 'first' => 'givenName', 'last' => 'familyName', 'title' => 'honorificPrefix' ] as $src => $dst) {
                    if (array_key_exists($src, $values['name'])) {
                        $row[$dst] = $values['name'][$src];
                    }
                }
            }

            if (array_key_exists('info', $values)) {
                $row['disambiguatingDescription'] = join("\n", $values['info']);
            }

            if (empty($row['identifier'])) {
                continue;
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
                    case 'honorificSuffix':
                    case 'hasOccupation':
                    case 'disambiguatingDescription':
                    case 'deathDate':
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

            $nameSlug = $this->slugify->slugify($person->getName());

            if ('a' != $nameSlug[0]) {
                // currently only letter a
                continue;
            }

            $this->em->persist($person);
        }

        $this->em->flush();

        return 0;
    }
}
