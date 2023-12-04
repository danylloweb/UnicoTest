<?php

namespace App\Services;

use App\Criterias\AppRequestCriteria;
use App\Repositories\ProductRepository;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * PatientService
 */
class PatientService extends AppService
{
    protected $repository;

    /**
     * @param ProductRepository $repository
     */
    public function __construct(ProductRepository $repository) {
        $this->repository = $repository;
    }

    /**
     * @param int $limit
     * @return mixed
     * @throws RepositoryException
     */
    public function all(int $limit = 20)
    {
        return $this->repository
            ->resetCriteria()
            ->pushCriteria(app(AppRequestCriteria::class))
            ->paginate($limit);
    }

}
