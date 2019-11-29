<?php


namespace App\CityBundle\Services;


use App\CityBundle\Entity\City;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\DependencyInjection\Container;


class CityService
{

    private $em, $geoNameUrl, $geoNameUser;

    public function request($url)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec ($ch);

        curl_close ($ch);

        $result = json_decode($result, true);

        return $result;
    }

    public function __construct(Container $container, EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->geoNameUrl = $container->getParameter('geoname_url');
        $this->geoNameUser = $container->getParameter('geoname_username');
    }


    public function getCityById($id)
    {

        $url=$this->geoNameUrl . 'getJSON?' . $this->geoNameUser."&geonameId=$id";

        return $this->request($url);
    }


    public function getCities($params)
    {
        $url = $this->geoNameUrl . 'searchJSON?' . $this->geoNameUser . '&featureClass=P&';
        $url .= 'maxRows=';

        if (isset($params['limit'])) {
            $url .= $params['limit'];

        } else {
            $url .= '6';
        }

        $urlSaved = $url;

        $params['name'] = str_replace(' ', '&nbsp;', $params['name']);

        if (isset($params['name'])) {
            $urlSaved .= '&name_startsWith=' . $params['name'];
            $url .= '&q=' . $params['name'];
        }

        $data = $this->request($url);

        if ($data['totalResultsCount'] === 0){
             $data = $this->request($urlSaved);
         }

        return $data;
    }


    public function setCity($id): City
    {

        $city = $this->em->getRepository(City::class)->findOneBy(['geoNameId' => $id]);

        if (!isset($city)) {

            $geoNameCity = $this->getCityById($id);

            if (empty($geoNameCity['geonameId'])) {
                throw new HttpException(404, 'Город не найден');
            }

            $city = new City();
            $city->geoNameId = $geoNameCity['geonameId'];
            $city->name = $geoNameCity['name'];

            $this->em->persist($city);
            $this->em->flush();
        }

        return $city;
    }
}