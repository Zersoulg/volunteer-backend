<?php

namespace App\CityBundle\Controller;


use App\CityBundle\Services\CityService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\{JsonResponse, Request};
use Symfony\Component\HttpKernel\Exception\HttpException;


class GetGeoNameCity
{

    private $cityService;

    public function __construct(CityService $cityService)
    {
        $this->cityService = $cityService;
    }

    public function __invoke(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $params = $request->query->all();

        if(empty($params['name'])){
            throw new HttpException(400, 'Укажите город');
        }

        $data = $this->cityService->getCities($params);

        return new JsonResponse($data,200);
    }

}