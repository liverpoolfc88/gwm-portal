<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Artisaninweb\SoapWrapper\SoapWrapper;
use SoapClient;

class SoapController extends Controller
{
    protected $soapWrapper;
      
    public function index($params)
    {
      $context = stream_context_create([
        'ssl' => [
            // set some SSL/TLS specific options
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
          ]
      ]);

      $this->soapWrapper = new SoapWrapper;
            $this->soapWrapper->add('zsiis_gwm_date', function ($service) use ($context){
                    $service
                      ->wsdl(config("app.APP_SAP_WSDL"))
                      ->trace(true)
                      ->certificate(true)
                      //->cache(WSDL_CACHE_NONE)
                      ->cache(true)
                      ->options([
                        'login' => config("app.APP_SAP_LOGIN"),
                        'password' => config("app.APP_SAP_PASSWORD"),
                        'location' => config("app.APP_SAP_LOCATION"),
                        'stream_context' => $context,
                        'exceptions' => true
                      ]);
                      
          });
          ini_set('max_execution_time', 180);
          $data = $this->soapWrapper->call('zsiis_gwm_date._-UZAUTO_-SD_FG_GWM_SET_SALES_DA', [$params]);
          return array($data);
    }

    public function testss6()
    {
      //return 'Pass: '.config("app.APP_SAP_S6T_LOCATION");

      $context = stream_context_create([
        'ssl' => [
            // set some SSL/TLS specific options
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
          ]
      ]);

      $this->soapWrapper = new SoapWrapper;
      $this->soapWrapper->add('zsiis_gwm_date1', function ($service) use ($context) {
              $service
                ->wsdl(config("app.APP_SAP_S6T_WSDL"))
                ->trace(true)
                //->cache(WSDL_CACHE_NONE)
                ->cache(true)
                ->certificate(true)
                ->options([
                  'login' => config("app.APP_SAP_S6T_LOGIN"),
                  'password' => config("app.APP_SAP_S6T_PASSWORD"),
                  'location' => config("app.APP_SAP_S6T_LOCATION"),
                  'stream_context' => $context,
                  'exceptions' => true
                ]);
      });
      
      // dd($this->soapWrapper); 
      //return 'Password='.config('app.APP_SAP_PASSWORD');
      
          $bac = '293998';
          $s_date = '2021-10-05';
          $vin = 'XWBMA48N9MA627054';
          
          $params = ['IS_DATA' => [
                      'item' => [
                          'DEALER_CODE' => $bac,
                          'LINES' => [
                                  'item' => [
                                      'SALE_DATE' => $s_date,
                                      'VIN' => $vin
                                        ]
                          ]
                      ]
                    ]           
          ];
              
          ini_set('max_execution_time', 180);
          $data = $this->soapWrapper->call('zsiis_gwm_date1._-UZAUTO_-SD_FG_GWM_DATE', [$params]);
          return array($data);
    }
    
    public function testss5()
    {
      //return 'Pass: '.config("app.APP_SAP_S5_LOCATION");

      $context = stream_context_create([
        'ssl' => [
            // set some SSL/TLS specific options
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => false
          ]
      ]);

      $this->soapWrapper = new SoapWrapper;
      $this->soapWrapper->add('zsiis_gwm_date', function ($service) use ($context) {
              $service
                ->wsdl(config("app.APP_SAP_S5_WSDL"))
                ->trace(true)
                ->certificate(false)
                //->cache(WSDL_CACHE_NONE)
                ->cache(true)
                ->certificate(true)
                ->options([
                  'login' => config("app.APP_SAP_S5_LOGIN"),
                  'password' => config("app.APP_SAP_S5_PASSWORD"),
                  'location' => config("app.APP_SAP_S5_LOCATION"),
                  'stream_context' => $context,
                  'exceptions' => true
                ]);
      });
      
      // dd($this->soapWrapper); 
      //return 'Password='.config('app.APP_SAP_PASSWORD');
      
          $bac = '293998';
          $s_date = '2021-10-05';
          $vin = 'XWBMA48N9MA627053';
          
          $params = ['IS_DATA' => [
                      'item' => [
                          'DEALER_CODE' => $bac,
                          'LINES' => [
                                  'item' => [
                                      'SALE_DATE' => $s_date,
                                      'VIN' => $vin
                                        ]
                          ]
                      ]
                    ]           
          ];
              
          ini_set('max_execution_time', 180);
          $data = $this->soapWrapper->call('zsiis_gwm_date._-UZAUTO_-SD_FG_GWM_SET_SALES_DA', [$params]);
          return array($data);
    }

    public function testss4()
    {
            $this->soapWrapper = new SoapWrapper;
            $this->soapWrapper->add('zsiis_gwm_date', function ($service) {
                    $service
                      ->wsdl(config("app.APP_SAP_S4_WSDL"))
                      ->trace(true)
                      ->options([
                        'login' => config("app.APP_SAP_S4_LOGIN"),
                        'password' => config("app.APP_SAP_S4_PASSWORD")
                      ]);
                      
                      
          });
          //var_dump($this->soapWrapper);
          
          $params = ['IS_DATA' => [
                      'DEALER_CODE' => '298766',
                      'LINES' => [
                               'item' => [
                                  'SALE_DATE' => '20210831',
                                  'VIN' => 'XWBTA69V9MA205627'
                               ]
                      ]
                                  ] 

                ];
                // echo "<pre>";
                // print_r($params);
                // echo "</pre>";

          //$data = $this->soapWrapper->call('_-UZAUTO_-SIIS_GWM_DATE._-UZAUTO_-SD_FG_GWM_SET_SALES_DA', [$params]);
          $data = $this->soapWrapper->call('zsiis_gwm_date._-UZAUTO_-SD_FG_GWM_SET_SALES_DA', [$params]);
          return array($data);
    }
}
