<?php

namespace AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Doctrine\ORM\QueryBuilder;
use DataDog\PagerBundle\Pagination;
use DataDog\AuditBundle\Entity\AuditLog;
use AppBundle\Controller\DoctrineController;

class AuditController extends Controller
{
    use DoctrineController;

    public function filters(QueryBuilder $qb, $key, $val)
    {
        switch ($key) {
        case 'history':
            if ($val) {
                $orx = $qb->expr()->orX();
                $orx->add('s.fk = :fk');
                $orx->add('t.fk = :fk');

                $qb->andWhere($orx);
                $qb->setParameter('fk', intval($val));
            }
            break;
        case 'class':
            $orx = $qb->expr()->orX();
            $orx->add('s.class = :class');
            $orx->add('t.class = :class');

            $qb->andWhere($orx);
            $qb->setParameter('class', $val);
            break;
        case 'blamed':
            if ($val === 'null') {
                $qb->andWhere($qb->expr()->isNull('a.blame'));
            } else {
                // this allows us to safely ignore empty values
                // otherwise if $qb is not changed, it would add where the string is empty statement.
                $qb->andWhere($qb->expr()->eq('b.fk', ':blame'));
                $qb->setParameter('blame', $val);
            }
            break;
        default:
            // if user attemps to filter by other fields, we restrict it
            throw new \Exception("filter not allowed");
        }
    }

    /**
     * @Method("GET")
     * @Template
     * @Route("/audit")
     */
    public function indexAction(Request $request)
    {
        Pagination::$defaults = array_merge(Pagination::$defaults, ['limit' => 10]);

        $qb = $this->repo("DataDogAuditBundle:AuditLog")
            ->createQueryBuilder('a')
            ->addSelect('s', 't', 'b')
            ->innerJoin('a.source', 's')
            ->leftJoin('a.target', 't')
            ->leftJoin('a.blame', 'b');

        $options = [
            'sorters' => ['a.loggedAt' => 'DESC'],
            'applyFilter' => [$this, 'filters'],
        ];

        $sourceClasses = [
            Pagination::$filterAny => $this->get('translator')->trans('audit.pagination.any_source'),
        ];

        foreach ($this->getDoctrine()->getManager()->getMetadataFactory()->getAllMetadata() as $meta) {
            if ($meta->isMappedSuperclass || strpos($meta->name, 'DataDog\AuditBundle') === 0) {
                continue;
            }
            $parts = explode('\\', $meta->name);
            $sourceClasses[$meta->name] = end($parts);
        }

        $users = [
            Pagination::$filterAny => $this->get('translator')->trans('audit.pagination.any_user'),
            'null' => $this->get('translator')->trans('audit.pagination.unknown'),
        ];
        foreach ($this->repo('AppBundle:User')->findAll() as $user) {
            $users[$user->getId()] = (string) $user;
        }

        $logs = new Pagination($qb, $request, $options);
        return compact('logs', 'sourceClasses', 'users');
    }

    /**
     * @Route("/audit/diff/{id}")
     * @Method("GET")
     * @Template
     */
    public function diffAction(AuditLog $log)
    {
        return compact('log');
    }
}
