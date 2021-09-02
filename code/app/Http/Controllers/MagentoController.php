<?php

namespace App\Http\Controllers;

use App\Categories;
use App\Products;
use Illuminate\Http\Request;
use Grayloon\Magento\Magento;

class MagentoController extends Controller {

	public static function products( $perPage = 100, $page = 1 ) {
		try {

			ini_set( 'max_execution_time', 0 );
			ini_set( "memory_limit", "-1" );
			set_time_limit( 0 );
			$magento  = new Magento();

			$categories = $magento->api( 'categories' )->all( 399, 1 );

			$categories = $categories->json();
			$parsed   = array();

			$productUrl='https://idmusikk.no/rest/V1/products?searchCriteria[current_page]='.$page.'&searchCriteria[page_size]='.$perPage .'&searchCriteria[filter_groups][0][filters][0][field]=status&searchCriteria[filter_groups][0][filters][0][value]=1&searchCriteria[filter_groups][0][filters][0][condition_type]=eq';

			$ch = curl_init($productUrl);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					"Content-Type: application/json",
					"Authorization: Bearer x7tdcd03164lwsuyeo9elivqfsmw6yjb"
				)
			);
			$productList = curl_exec($ch);
			$err      = curl_error($ch);
			$results = json_decode($productList);
			$results = json_decode(json_encode($results), true);
			curl_close($ch);


            //this fix bug from magento api (he will always return products even we pass total count with search criteria)
            if ($results['total_count'] < $results['search_criteria']['page_size'] * ($results['search_criteria']['current_page'] - 1)) {
                return null;
            }
            $i = 0;
                   foreach ($results['items'] as $result => $value) {

                       echo "<br>";
                       $parsed['price']       = isset($value['price']) ? $value['price'] : '';
                       $parsed['name']        = isset($value['name']) ? $value['name'] : '';
                       $parsed['sku']         = isset($value['sku']) ? str_replace(' ', '-', $value['sku']) : '';
                       $parsed['type']        = isset($value['type_id']) ? $value['type_id'] : '';
                       $parsed['weight']      = isset($value['weight']) ? $value['weight'] : '';
                       $parsed['_manage_stock'] = 'yes';
                       $parsed['_backorders']   = 'yes';
                       $parsed['category_id'] = '';
                       if (isset($value['extension_attributes']['category_links'])) {
                           foreach ($value['extension_attributes']['category_links'] as $extension_attribute) {
                               foreach ($categories['items'] as $category => $cat_value) {
                                   if ($extension_attribute['category_id'] == $cat_value['id']) {
                                       $parsed['category_name'] = $cat_value['name'];
                                   }
                               }

                               $parsed['category_id'] = $extension_attribute['category_id'];
                           }
                       }
                       //custom attributes
                       foreach ($value['custom_attributes'] as $custom_attribute) {

                           if ($custom_attribute['attribute_code'] == 'description') {
                               $parsed['description'] = $custom_attribute['value'];
                           }
                           if ($custom_attribute['attribute_code'] == 'color') {
                               $color = MagentoController::getAttributes('color',$custom_attribute['value']);
                               $parsed['color'] =  isset($color) ? $color : '';
                           }
                           if ($custom_attribute['attribute_code'] == 'country_of_manufacture') {
                           $country_of_manufacture =    MagentoController::getAttributes('country_of_manufacture',$custom_attribute['value']);
                               $parsed['country_of_manufacture'] = isset($country_of_manufacture) ? $country_of_manufacture : ''  ;
                           }
                           if ($custom_attribute['attribute_code'] == 'gripebrett') {
                             $gripebrett =   MagentoController::getAttributes('gripebrett',$custom_attribute['value']);
                               $parsed['gripebrett'] =  isset($gripebrett) ?  $gripebrett : '';
                            }
                           if ($custom_attribute['attribute_code'] == 'hand_type') {
                               $hand_type =  MagentoController::getAttributes('hand_type',$custom_attribute['value']);
                               $parsed['hand_type'] = isset($hand_type) ? $hand_type : '';
                           }
                           if ($custom_attribute['attribute_code'] == 'has_microphone') {
                               $has_microphone = MagentoController::getAttributes('has_microphone',$custom_attribute['value']);
                               $parsed['has_microphone'] =  isset($has_microphone) ? $has_microphone : '';
                           }
                           if ($custom_attribute['attribute_code'] == 'manufacturer') {
                               $manufacturer =   MagentoController::getAttributes('manufacturer',$custom_attribute['value']);
                               $parsed['manufacturer'] = isset($manufacturer) ? $manufacturer : '';
                           }
                       }

                       foreach ($value['media_gallery_entries'] as $media => $mediaValue) {
                           array_push($parsed['image'] ,'https://idmusikk.no/pub/media/catalog/product' . $mediaValue['file']);
//                           $parsed['image'] = 'https://idmusikk.no/pub/media/catalog/product' . $mediaValue['file'];
                       }

                      Products::create(['id' => $value['id'], 'product_info' => $parsed]);
                       ++$i;
                       print_r($i);
                   }
                   ++$page;
                   MagentoController::products($perPage, $page);

		} catch ( \Exception $exception ) {
			return response()->json( [ 'Exception' => $exception->getMessage() ], 401 );
		}

	}

	public function categories() {

		try {

			$magento  = new Magento();
			$response = $magento->api( 'categories' )->all();
			$results  = $response->json();

			$parsed = array();
			if ( count( $results['items'] ) > 0 ) {


				foreach ( $results['items'] as $result => $value ) {

					$parsed['id']        = $value['id'];
					$parsed['parent_id'] = $value['parent_id'];
					$parsed['name']      = $value['name'];

					foreach ($results['items'] as $result1 => $value1){
						if ($parsed['parent_id'] == $value1['id'] ){
							$parsed['parent_name'] = $value1['name'];
						}
					}
					foreach ( $value['custom_attributes'] as $custom_attribute ) {

						if ( $custom_attribute['attribute_code'] == 'url_path' ) {
							$parsed['url_path'] = $custom_attribute['value'];

						}
					}

					Categories::create( [ 'category_info' => $parsed ] );
				}
				return response()->json( [ 'Success' => 'Categories imported' ], 200 );
			}
		} catch ( \Exception $exception ) {
			return response()->json( [ 'Exception' => $exception->getMessage() ], 401 );
		}
	}

	public static function getAttributes($custom_attribute,$value)
    {
        if ( ! empty($custom_attribute)) {
            $attUrl = 'https://idmusikk.no/rest/V1/products/attributes/' . $custom_attribute . '/options';

            $ch = curl_init($attUrl);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt(
                $ch,
                CURLOPT_HTTPHEADER,
                array(
                    "Content-Type: application/json",
                    "Authorization: Bearer x7tdcd03164lwsuyeo9elivqfsmw6yjb"
                )
            );
            $attList    = curl_exec($ch);
            $err        = curl_error($ch);
            $attresults = json_decode($attList);
            $attresults = json_decode(json_encode($attresults), true);
            curl_close($ch);

            if ( ! empty($attresults)) {
                foreach ($attresults as $result) {
                    if ($result['value'] == $value) {
                        return $result['label'];
                    }
                }
            }
        }
    }
}
