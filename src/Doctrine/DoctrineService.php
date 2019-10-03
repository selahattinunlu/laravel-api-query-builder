<?php

namespace Unlu\Laravel\Api\Doctrine;

use Doctrine\ORM\EntityRepository;
use Unlu\Laravel\Api\Context\Context;

/**
 * Class DoctrineService
 */
class DoctrineService
{

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var EntityRepository
     */
    protected $entityRepository;

    /**
     * @var Fetcher
     */
    protected $fetcher;

    /**
     * DoctrineService constructor.
     * @param Fetcher $fetcher
     */
    public function __construct(Fetcher $fetcher)
    {
        $this->fetcher = $fetcher;
    }

    /**
     * Perform query against doctrine entity repository with given context.
     *
     * @return mixed
     */
    public function getResults()
    {
        return $this->fetcher->fetch($this->context, $this->entityRepository);
    }










    /**
     * @param Context $context
     */
    public function setContext(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @param EntityRepository $entityRepository
     */
    public function setEntityRepository(EntityRepository $entityRepository)
    {
        $this->entityRepository = $entityRepository;
    }

}
