<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

use Doctrine\ORM\EntityManagerInterface;

use Knp\Component\Pager\PaginatorInterface;

/**
 *
 */
class PersonController
extends BaseController
{
    protected $pageSize = 100;

    /**
     * @Route("/person", name="person-index")
     */
    public function indexAction(Request $request,
                                EntityManagerInterface $entityManager,
                                PaginatorInterface $paginator,
                                TranslatorInterface $translator)
    {
        $qb = $entityManager
            ->createQueryBuilder();

        $qb->select([
                'P',
                "CONCAT(COALESCE(P.familyName,P.givenName), ' ', COALESCE(P.givenName, '')) HIDDEN nameSort"
            ])
            ->distinct()
            ->from('\App\Entity\Person', 'P')
            ->where('P.status IN (0,1)')
            ->orderBy('nameSort')
            ;

        $pagination = $this->buildPagination($request, $paginator, $qb->getQuery(), [
            // the following leads to wrong display in combination with our
            // helper.pagination_sortable()
            // 'defaultSortFieldName' => 'nameSort', 'defaultSortDirection' => 'asc',
        ]);

        return $this->render('Person/index.html.twig', [
            'pageTitle' => $translator->trans('Persons'),
            'pagination' => $pagination,
        ]);
    }

    /**
     * @Route("/person/ulan/{ulan}.jsonld", requirements={"ulan"="[0-9]+"}, name="person-by-ulan-jsonld")
     * @Route("/person/ulan/{ulan}", requirements={"ulan"="[0-9]+"}, name="person-by-ulan")
     * @Route("/person/gnd/{gnd}.jsonld", requirements={"gnd"="[0-9xX]+"}, name="person-by-gnd-jsonld")
     * @Route("/person/gnd/{gnd}", requirements={"gnd"="[0-9xX]+"}, name="person-by-gnd")
     * @Route("/person/{id}.jsonld", name="person-jsonld", requirements={"id"="\d+"})
     * @Route("/person/{id}", name="person", requirements={"id"="[^\/]+"})
     */
    public function detailAction(Request $request, EntityManagerInterface $entityManager,
                                 $id = null, $ulan = null, $gnd = null)
    {
        $criteria = new \Doctrine\Common\Collections\Criteria();

        if (!empty($id)) {
            $criteria->where($criteria->expr()->eq('identifier', $id));
        }
        else if (!empty($ulan)) {
            $criteria->where($criteria->expr()->eq('ulan', $ulan));
        }
        else if (!empty($gnd)) {
            $criteria->where($criteria->expr()->eq('gnd', $gnd));
        }
        else {
            return $this->redirectToRoute('person-index');
        }

        $criteria->andWhere($criteria->expr()->neq('status', -1));

        $personRepo = $entityManager
                ->getRepository('App\Entity\Person');
        $persons = $personRepo->matching($criteria);

        if (0 == count($persons)) {
            return $this->redirectToRoute('person-index');
        }

        $person = $persons[0];
        // $person->setDateModified(\App\Search\PersonListBuilder::fetchDateModified($entityManager->getConnection(), $person->getId()));

        $locale = $request->getLocale();
        if (in_array($request->get('_route'), [ 'person-jsonld', 'person-by-ulan-json', 'person-by-gnd-jsonld' ])) {
            return new JsonLdResponse($person->jsonLdSerialize($locale));
        }

        $routeName = 'person';
        $routeParams = [ 'id' => $person->getId() ];

        /*
        if (!empty($person->getUlan())) {
            $routeName = 'person-by-ulan';
            $routeParams = [ 'ulan' => $person->getUlan() ];
        }
        else if (!empty($person->getGnd())) {
            $routeName = 'person-by-gnd';
            $routeParams = [ 'gnd' => $person->getGnd() ];
        }
        */

        return $this->render('Person/detail.html.twig', [
            'pageTitle' => $person->getFullname(true), // TODO: lifespan in brackets
            'person' => $person,
            'pageMeta' => [
                // 'jsonLd' => $person->jsonLdSerialize($locale),
                // 'og' => $this->buildOg($person, $routeName, $routeParams),
                // 'twitter' => $this->buildTwitter($person, $routeName, $routeParams),
            ],
        ]);
    }

    /**
     * @Route("/person/gnd/beacon", name="person-gnd-beacon")
     *
     * Provide a BEACON file as described in
     *  https://de.wikipedia.org/wiki/Wikipedia:BEACON
     */
    public function gndBeaconAction(EntityManagerInterface $entityManager, TranslatorInterface $translator, \Twig\Environment $twig)
    {
        $ret = '#FORMAT: BEACON' . "\n"
             . '#PREFIX: http://d-nb.info/gnd/'
             . "\n";
        $ret .= sprintf('#TARGET: %s/gnd/{ID}',
                        $this->generateUrl('person-index', [], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL))
              . "\n";

        $globals = $twig->getGlobals();

        $ret .= '#NAME: ' . /** @ignore */$translator->trans($globals['site_name'], [], 'additional')
              . "\n";
        // $ret .= '#MESSAGE: ' . "\n";

        $repo = $entityManager
                ->getRepository('App\Entity\Person');

        $query = $repo
                ->createQueryBuilder('P')
                ->where('P.status >= 0')
                ->andWhere('P.gnd IS NOT NULL')
                ->orderBy('P.gnd')
                ->getQuery()
                ;

        foreach ($query->execute() as $actor) {
            $ret .=  $actor->getGnd() . "\n";
        }

        return new Response($ret, Response::HTTP_OK, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
