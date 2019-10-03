<?php

namespace Unlu\Laravel\Api\Doctrine;

use Doctrine\ORM\EntityRepository;
use Unlu\Laravel\Api\Context\Context;

/**
 * Class Fetcher
 * @package Unlu\Laravel\Api\Doctrine
 */
class Fetcher
{

    /**
     * @param Context $context
     * @param EntityRepository $entityRepository
     * @return mixed
     */
    public function fetch(Context $context, EntityRepository $entityRepository)
    {
        return [];
    }

}
