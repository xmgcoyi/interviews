<?php

namespace Realmdigital\Web\Controller;

use DDesrosiers\SilexAnnotations\Annotations as SLX;
use Silex\Application;

/**
 * @SLX\Controller(prefix="product/")
 */
class ProductController {

    const SERVER_URL = 'http://192.168.0.241/eanlist?type=Web';
    const SOUTH_AFRICAN_CURRENCY = 'ZAR';

    private function curl(array $requestData)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL,  static::SERVER_URL);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $requestData);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($curl);
        $response = json_decode($response, true);
        curl_close($curl);

        return $response;
    }

    private function getViewData(array $arg)
    {
        $result = [];

        foreach($arg as $row) {
            $result[] = [
                'ean' => $row['barcode'],
                'name' => $row['itemName'],
                'prices' => $this->getPrices( $row['prices'] )
            ];
        }

        return $result;
    }

    private function getPrices(array $arg)
    {
        $data = [];
        foreach ($arg as $row) {
            if ($row['currencyCode'] != static::SOUTH_AFRICAN_CURRENCY) {
                $data[] = [
                    'price' => $row['sellingPrice'],
                    'currency' => $row['currencyCode']
                ];
            }
        }

        return $data;
    }

    /**
     * @SLX\Route(
     *      @SLX\Request(method="GET", uri="/{id}")
     * )
     * @param Application $app
     * @param $name
     * @return
     */
    public function getById_GET(Application $app, $id){
        $viewData = $this->getViewData(
            $this->curl([
                'id' => $id
            ])
        );

        return $app->render('products/product.detail.twig', $viewData);
    }

    /**
     * @SLX\Route(
     *      @SLX\Request(method="GET", uri="/search/{name}")
     * )
     * @param Application $app
     * @param $name
     * @return
     */
    public function getByName_GET(Application $app, $name){
        $viewData = $this->getViewData(
            $this->curl([
                'name' => $name
            ])
        );

        return $app->render('products/products.twig', $viewData);
    }

}
