<?php

/**
 * TestController
 *
 * This controller is used to install default / test values to the system.
 * The controller can only be accessed from allowed IP address.
 *
 * @package		MemberManagementBundleBundle
 * @subpackage	Controller
 * @name	    TestController
 *
 * @author		Can Berkol
 *
 * @copyright   Biber Ltd. (www.biberltd.com)
 *
 * @version     1.0.0
 *
 */

namespace BiberLtd\Core\Bundles\TaxManagementBundle\Controller;
use BiberLtd\Core\CoreController;


class TestController extends CoreController {

    public function testAction(){
        $taxModel = $this->get('taxmanagement.model');
//        $tax = new \stdClass();
//        $tax->id = 10;
//        $tax->rate = 18;
//        $tax->country = 1;
//        $tax->state = 1;
//        $tax->city = 1;
//        $tax->product_category = 1;
//        $tax->site = 1;
//        $tax->local = new \stdClass();
//        $tax->local->tr = new \stdClass();
//        $tax->local->en = new \stdClass();
//        $tax->local->tr->name = 'Test';
//        $tax->local->tr->url_key = 'test';
//        $tax->local->en->name = 'Test2';
//        $tax->local->en->url_key = 'Test2';
//
////        $response = $taxModel->insertTaxRate($tax);
//        $response = $taxModel->updateTaxRate($tax);
//        if ($response['error']) {
//            exit('Kaydedilmedi');
//        }
//        foreach ($response['result']['set'] as $item) {
//            echo $item->getLocalization('tr')->getName();
//        }
//
//        $response = $taxModel->getTaxRate(10,'id');
//        if ($response['error']) {
//            exit('tax bulunamadı0');
//        }
//        echo $response['result']['set']->getId();die;

        $response = $taxModel->deleteTaxRate(7);
        if ($response['error']) {
            exit('tax bulunamadı0');
        }
        echo $response['result']['total_rows'].' row(s) deleted.';die;

    }
}
