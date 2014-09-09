<?php
/**
 * TaxManagementModel Class
 *
 * This class acts as a database proxy model for TaxManagementBundle functionalities.
 *
 * @vendor      BiberLtd
 * @package     Core\Bundles\TaxManagementModel
 * @subpackage  Services
 * @name        TaxManagementModel
 *
 * @author      Can Berkol
 * @author      Said İmamoğlu
 *
 * @copyright   Biber Ltd. (www.biberltd.com)
 *
 * @version     1.0.4
 * @date        24.06.2014
 *
 * @use         Biberltd\Core\Services
 * @use         Biberltd\Core\CoreModel
 * @use         Biberltd\Core\Services\Encryption
 * @use         BiberLtd\Bundle\TaxManagementBundle\Entity
 * @use         BiberLtd\Bundle\TaxManagementBundle\Services
 *
 */
namespace BiberLtd\Bundle\TaxManagementBundle\Services;

/** Extends CoreModel */
use BiberLtd\Bundle\LocationManagementBundle\Services\LocationManagementModel;
use BiberLtd\Bundle\CoreBundle\CoreModel;
/** Entities to be used */
use BiberLtd\Bundle\TaxManagementBundle\Entity as BundleEntity;
use BiberLtd\Bundle\ProductManagementBundle\Entity as ProductEntity;
/** Helper Models */
use BiberLtd\Bundle\SiteManagementBundle\Services as SMMService;
use BiberLtd\Bundle\MultiLanguageSupportBundle\Services as MLSService;
/** Core Service */
use BiberLtd\Bundle\CoreBundle\Services as CoreServices;
use BiberLtd\Bundle\CoreBundle\Exceptions as CoreExceptions;

class TaxManagementModel extends CoreModel {

    public $by_opts = array('entity', 'id', 'code', 'url_key', 'post');
    public $entity = array(
            'tax_rate' => array('name' => 'TaxManagementBundle:TaxRate', 'alias' => 'tr'),
            'tax_rate_localization' => array('name' => 'TaxManagementBundle:TaxRateLocalization', 'alias' => 'trl'),
        );

    /**
     * @name        deleteTaxRate ()
     * Deletes an existing item from database.
     *
     * @since            1.0.0
     * @version         1.0.0
     * @author          Said İmamoğlu
     *
     * @use             $this->deleteTaxRates()
     *
     * @param           mixed $item Entity, id or url key of item
     * @param           string $by
     *
     * @return          mixed           $response
     */
    public function deleteTaxRate($item, $by = 'entity')
    {
        return $this->deleteTaxRates(array($item), $by);
    }

    /**
     * @name            deleteTaxRates ()
     * Deletes provided items from database.
     *
     * @since        1.0.0
     * @version         1.0.0
     * @author          Said İmamoğlu
     *
     * @use             $this->createException()
     *
     * @param           array $collection Collection of TaxRate entities, ids, or codes or url keys
     *
     * @return          array           $response
     */
    public function deleteTaxRates($collection)
    {
        $this->resetResponse();
        /** Parameter must be an array */
        if (!is_array($collection)) {
            return $this->createException('InvalidParameterValue', 'Array', 'err.invalid.parameter.collection');
        }
        $countDeleted = 0;
        foreach ($collection as $entry) {
            if ($entry instanceof BundleEntity\TaxRate) {
                $this->em->remove($entry);
                $countDeleted++;
            } else {
                switch ($entry) {
                    case is_numeric($entry):
                        $response = $this->getTaxRate($entry, 'id');
                        break;
                    case is_string($entry):
                        $response = $this->getProductCategory($entry, 'url_key');
                        break;
                }
                if ($response['error']) {
                    $this->createException('EntryDoesNotExist', $entry, 'err.invalid.entry');
                }
                $entry = $response['result']['set'];
                $this->em->remove($entry);
                $countDeleted++;
            }
        }

        if ($countDeleted < 0) {
            $this->response['error'] = true;
            $this->response['code'] = 'err.db.fail.delete';

            return $this->response;
        }
        $this->em->flush();
        $this->response = array(
            'rowCount' => 0,
            'result' => array(
                'set' => null,
                'total_rows' => $countDeleted,
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.db.deleted',
        );
        return $this->response;
    }

    /**
     * @name            listTaxRates ()
     * Lists tax_rate data from database with given params.
     *
     * @author          Said İmamoğlu
     * @version         1.0.0
     * @since           1.0.1
     *
     * @param           array $filter
     * @param           array $sortOrder
     * @param           array $limit
     * @param           string $queryStr
     *
     * @use             $this->createException()
     * @use             $this->prepareWhere()
     * @use             $this->addLimit()
     *
     * @return          array $this->response
     */
    public function listTaxRates($filter = null, $sortOrder = null, $limit = null, $queryStr = null)
    {
        $this->resetResponse();
        if (!is_array($sortOrder) && !is_null($sortOrder)) {
            return $this->createException('InvalidSortOrder', '', 'err.invalid.parameter.sortorder');
        }

        $order_str = '';
        $where_str = '';
        $group_str = '';
        $filter_str = '';

        /**
         * Start creating the query.
         *
         * Note that if no custom select query is provided we will use the below query as a start.
         */
        if (is_null($queryStr)) {
            $queryStr = 'SELECT ' . $this->entity['tax_rate']['alias']
                . ' FROM ' . $this->entity['tax_rate']['name'] . ' ' . $this->entity['tax_rate']['alias'];
        }
        /**
         * Prepare ORDER BY section of query.
         */
        if ($sortOrder != null) {
            foreach ($sortOrder as $column => $direction) {
                $order_str .= ' ' . $this->entity['tax_rate']['alias'] . '.' . $column . ' ' . strtoupper($direction) . ', ';
            }
            $order_str = rtrim($order_str, ', ');
            $order_str = ' ORDER BY ' . $order_str . ' ';
        }

        /**
         * Prepare WHERE section of query.
         */
        if ($filter != null) {
            $filter_str = $this->prepareWhere($filter);
            $where_str .= ' WHERE ' . $filter_str;
        }
        $queryStr .= $where_str . $group_str . $order_str;

        $query = $this->em->createQuery($queryStr);

        $query = $this->addLimit($query, $limit);

        /**
         * Prepare & Return Response
         */
        $result = $query->getResult();
        $stocks = array();
        $unique = array();
        foreach ($result as $entry) {
            $id = $entry->getId();
            if (!isset($unique[$id])) {
                $stocks[$id] = $entry;
                $unique[$id] = $entry->getId();
            }
        }

        $total_rows = count($stocks);

        if ($total_rows < 1) {
            $this->response['code'] = 'err.db.entry.notexist';
            return $this->response;
        }
        $newCollection = array();
        foreach ($stocks as $stock) {
            $newCollection[] = $stock;
        }
        unset($stocks, $unique);

        $this->response = array(
            'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $newCollection,
                'total_rows' => $total_rows,
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.db.entry.exist',
        );
        return $this->response;
    }

    /**
     * @name            listTaxRatesOfProduct()
     *                  Lists tax rates of a certain product.
     *
     * @author          Can Berkol
     * @version         1.0.2
     * @since           1.0.2
     *
     * @param           mixed       $product
     * @param           array       $sortOrder
     * @param           array       $limit
     *
     * @return          array       $this->response
     */
    public function listTaxRatesOfProduct($product, $sortOrder = null, $limit = null){
        if($product instanceof ProductEntity\Product){
            $product = $product->getId();
        }
        else if(!is_numeric($product)){
            $pModel = $this->kernel->getContainer()->get('productmanagement.model');
            $response = $pModel->getProduct($product, 'sku');
            if($response['error']){
                $this->response = array(
                    'rowCount' => $this->response['rowCount'],
                    'result' => array(
                        'set' => null,
                        'total_rows' => 1,
                        'last_insert_id' => null,
                    ),
                    'error' => true,
                    'code' => 'msg.error.db.entry.notexist',
                );
                return $this->response;
            }
            $product = $response['result']['set'];
            $product = $product->getId();
        }
        $column = $this->entity['tax_rate']['alias'] . '.product';
        $condition = array('column' => $column, 'comparison' => 'eq', 'value' => $product);
        $filter[] = array(
            'glue' => 'and',
            'condition' => array(
                array(
                    'glue' => 'and',
                    'condition' => $condition,
                )
            )
        );
        return $this->listTaxRates($filter, $sortOrder, $limit);
    }

    /**
     * @name            listTaxRatesOfProductCategory()
     *                  Lists tax rates of a certain product.
     *
     * @author          Can Berkol
     * @version         1.0.2
     * @since           1.0.2
     *
     * @param           mixed       $productCategory
     * @param           array       $sortOrder
     * @param           array       $limit
     *
     * @return          array       $this->response
     */
    public function listTaxRatesOfProductCategory($productCategory, $sortOrder = null, $limit = null){
        if($productCategory instanceof ProductEntity\ProductCategory){
            $productCategory = $productCategory->getId();
        }
        $column = $this->entity['tax_rate']['alias'] . '.product_category';
        $condition = array('column' => $column, 'comparison' => 'eq', 'value' => $productCategory);
        $filter[] = array(
            'glue' => 'and',
            'condition' => array(
                array(
                    'glue' => 'and',
                    'condition' => $condition,
                )
            )
        );
        return $this->listTaxRates($filter, $sortOrder, $limit);
    }
    /**
     * @name            getTaxRate ()
     *                  Returns details of a gallery.
     *
     * @since           1.0.0
     * @version         1.0.0
     * @author          Said İmamoğlu
     *
     * @use             $this->createException()
     * @use             $this->listTaxRates()
     *
     * @param           mixed $stock id, url_key
     * @param           string $by entity, id, url_key
     *
     * @return          mixed           $response
     */
    public function getTaxRate($stock, $by = 'id')
    {
        $this->resetResponse();
        $by_opts = array('id', 'sku', 'product');
        if (!in_array($by, $by_opts)) {
            return $this->createException('InvalidParameterValue', implode(',', $by_opts), 'err.invalid.parameter.by');
        }
        if (!is_object($stock) && !is_numeric($stock) && !is_string($stock)) {
            return $this->createException('InvalidParameter', 'ProductCategory or numeric id', 'err.invalid.parameter.product_category');
        }
        if (is_object($stock)) {
            if (!$stock instanceof BundleEntity\TaxRate) {
                return $this->createException('InvalidParameter', 'ProductCategory', 'err.invalid.parameter.product_category');
            }
            /**
             * Prepare & Return Response
             */
            $this->response = array(
                'rowCount' => $this->response['rowCount'],
                'result' => array(
                    'set' => $stock,
                    'total_rows' => 1,
                    'last_insert_id' => null,
                ),
                'error' => false,
                'code' => 'scc.db.entry.exist',
            );
            return $this->response;
        }
        $column = '';
        $filter[] = array(
            'glue' => 'and',
            'condition' => array(
                array(
                    'glue' => 'and',
                    'condition' => array('column' => $this->entity['tax_rate']['alias'] . '.' . $by, 'comparison' => '=', 'value' => $stock),
                )
            )
        );
        $response = $this->listTaxRates($filter, null, null, null, false);
        if ($response['error']) {
            return $response;
        }
        $collection = $response['result']['set'];
        /**
         * Prepare & Return Response
         */
        $this->response = array(
            'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $collection[0],
                'total_rows' => 1,
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.db.entry.exist',
        );
        return $this->response;
    }

    /**
     * @name        doesTaxRateExist ()
     * Checks if entry exists in database.
     *
     * @since           1.0.1
     * @version         1.0.0
     * @author          Said İmamoğlu
     *
     * @use             $this->getTaxRate()
     *
     * @param           mixed $item id, url_key
     * @param           string $by id, url_key
     *
     * @param           bool $bypass If set to true does not return response but only the result.
     *
     * @return          mixed           $response
     */
    public function doesTaxRateExist($item, $by = 'id', $bypass = false)
    {
        $this->resetResponse();
        $exist = false;

        $response = $this->getTaxRate($item, $by);

        if (!$response['error'] && $response['result']['total_rows'] > 0) {
            $exist = $response['result']['set'];
            $error = false;
        } else {
            $exist = false;
            $error = true;
        }

        if ($bypass) {
            return $exist;
        }
        /**
         * Prepare & Return Response
         */
        $this->response = array(
            'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $exist,
                'total_rows' => 1,
                'last_insert_id' => null,
            ),
            'error' => $error,
            'code' => 'scc.db.entry.exist',
        );
        return $this->response;
    }

    /**
     * @name        insertTaxRate ()
     * Inserts one or more item into database.
     *
     * @since        1.0.1
     * @version         1.0.3
     * @author          Said İmamoğlu
     *
     * @use             $this->insertFiles()
     *
     * @param           array $item Collection of entities or post data.
     *
     * @return          array           $response
     */

    public function insertTaxRate($item)
    {
        $this->resetResponse();
        return $this->insertTaxRates(array($item));
    }

    /**
     * @name            insertTaxRates ()
     * Inserts one or more items into database.
     *
     * @since           1.0.1
     * @version         1.0.3
     * @author          Said İmamoğlu
     *
     * @use             $this->createException()
     *
     * @throws          InvalidParameterException
     * @throws          InvalidMethodException
     *
     * @param           array $collection Collection of entities or post data.
     *
     * @return          array           $response
     */

    public function insertTaxRates($collection)
    {
        $countInserts = 0;
        $countLocalizations=0;
        foreach ($collection as $data) {
            if ($data instanceof BundleEntity\TaxRate) {
                $entity = $data;
                $this->em->persist($entity);
                $insertedItems[] = $entity;
                $countInserts++;
            } else if (is_object($data)) {
                $localizations = array();
                $locationModel = $this->kernel->getContainer()->get('locationmanagement.model');
//                $locationModel = new LocationManagementModel($this->kernel);
                $entity = new BundleEntity\TaxRate();
                foreach ($data as $column => $value) {
                    $localeSet=false;
                    $set = 'set' . $this->translateColumnName($column);
                    switch ($column) {
                        case 'local':
                            $localizations[$countInserts]['localizations'] = $value;
                            $localeSet = true;
                            $countLocalizations++;
                            break;
                        case 'product_category':
                            $productModel = $this->kernel->getContainer()->get('productmanagement.model');
                            $response = $productModel->getProductCategory($value,'id');
                            if ($response['error']) {
                                new CoreExceptions\SiteDoesNotExistException($this->kernel, $data);
                            }
                            $entity->$set($response['result']['set']);
                            unset($response, $productModel);
                            break;
                        case 'product':
                            $productModel = $this->kernel->getContainer()->get('productmanagement.model');
                            $response = $productModel->getProduct($value,'id');
                            if ($response['error']) {
                                new CoreExceptions\SiteDoesNotExistException($this->kernel, $data);
                            }
                            $entity->$set($response['result']['set']);
                            unset($response, $productModel);
                            break;
                        case 'country':
                            $response = $locationModel->getCountry($value,'id');
                            if ($response['error']) {
                                new CoreExceptions\EntityDoesNotExistException($this->kernel, 'Country can not found.');
                            }
                            $entity->$set($response['result']['set']);
                            unset($response);
                            break;
                        case 'state':
                            $response = $locationModel->getState($value,'id');
                            if ($response['error']) {
                                new CoreExceptions\EntityDoesNotExistException($this->kernel, 'State can not found.');
                            }
                            $entity->$set($response['result']['set']);
                            unset($response);
                            break;
                        case 'city':
                            $response = $locationModel->getCity($value,'id');
                            if ($response['error']) {
                                new CoreExceptions\EntityDoesNotExistException($this->kernel, 'City can not found.');
                            }
                            $entity->$set($response['result']['set']);
                            unset($response);
                            break;
                        case 'site':
                            $siteModel = $this->kernel->getContainer()->get('sitemanagement.model');
                            $response = $siteModel->getSite($value,'id');
                            if ($response['error']) {
                                new CoreExceptions\EntityDoesNotExistException($this->kernel, 'Site can not found.');
                            }
                            $entity->$set($response['result']['set']);
                            unset($response,$siteModel);
                            break;
                        case 'date_added':
                        case 'date_updated':
                            new $entity->$set(\DateTime('now', new \DateTimeZone($this->kernel->getContainer()->getParameter('app_timezone'))));
                            break;
                        default:
                            $entity->$set($value);
                            break;
                    }
                    if ($localeSet) {
                        $localizations[$countInserts]['entity'] = $entity;
                    }
                }
                unset($locationModel);
                $this->em->persist($entity);
                $insertedItems[] = $entity;
                $countInserts++;
            } else {
                new CoreExceptions\InvalidDataException($this->kernel);
            }
        }
        /**
         * Save data.
         */
        if ($countInserts > 0) {
            $this->em->flush();
        }
        /** Now handle localizations */
        if ($countInserts > 0 && $countLocalizations > 0) {
            $this->insertTaxRateLocalizations($localizations);
        }
        /**
         * Prepare & Return Response
         */
        $this->response = array(
            'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $insertedItems,
                'total_rows' => $countInserts,
                'last_insert_id' => $entity->getId(),
            ),
            'error' => false,
            'code' => 'scc.db.insert.done',
        );
        return $this->response;
    }

    /**
     * @name            insertTaxRateLocalizations ()
     *                  Inserts one or more tax rate  localizations into database.
     *
     * @since           1.0.1
     * @version         1.0.1
     * @author          Said İmamoğlu
     *
     * @use             $this->createException()
     *
     * @param           array $collection Collection of entities or post data.
     *
     * @return          array           $response
     */
    public function insertTaxRateLocalizations($collection)
    {
        $this->resetResponse();
        /** Parameter must be an array */
        if (!is_array($collection)) {
            return $this->createException('InvalidParameter', 'Array', 'err.invalid.parameter.collection');
        }
        $countInserts = 0;
        $insertedItems = array();
        foreach ($collection as $item) {
            if ($item instanceof BundleEntity\TaxRateLocalization) {
                $entity = $item;
                $this->em->persist($entity);
                $insertedItems[] = $entity;
                $countInserts++;
            } else {
                foreach ($item['localizations'] as $language => $data) {
                    $entity = new BundleEntity\TaxRateLocalization;
                    $entity->setTaxRate($item['entity']);
                    $mlsModel = $this->kernel->getContainer()->get('multilanguagesupport.model');
                    $response = $mlsModel->getLanguage($language, 'iso_code');
                    if (!$response['error']) {
                        $entity->setLanguage($response['result']['set']);
                    } else {
                        break 1;
                    }
                    foreach ($data as $column => $value) {
                        $set = 'set' . $this->translateColumnName($column);
                        $entity->$set($value);
                    }
                    $this->em->persist($entity);
                }
                $insertedItems[] = $entity;
                $countInserts++;
            }
        }
        if ($countInserts > 0) {
            $this->em->flush();
        }
        $this->response = array(
            'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $insertedItems,
                'total_rows' => $countInserts,
                'last_insert_id' => -1,
            ),
            'error' => false,
            'code' => 'scc.db.insert.done',
        );
        return $this->response;
    }

    /**
     * @name            updateTaxRate()
     * Updates single item. The item must be either a post data (array) or an entity
     *
     * @since           1.0.1
     * @version         1.0.0
     * @author          Said İmamoğlu
     *
     * @use             $this->resetResponse()
     * @use             $this->updateTaxRates()
     *
     * @param           mixed   $item     Entity or Entity id of a folder
     *
     * @return          array   $response
     *
     */

    public function updateTaxRate($item){
        return $this->updateTaxRates(array($item));
    }

    /**
     * @name            updateTaxRates()
     * Updates one or more item details in database.
     *
     * @since           1.0.1
     * @version         1.0.0
     * @author          Said İmamoğlu
     *
     * @use             $this->update_entities()
     * @use             $this->createException()
     * @use             $this->listTaxRates()
     *
     *
     * @throws          InvalidParameterException
     *
     * @param           array   $collection     Collection of item's entities or array of entity details.
     *
     * @return          array   $response
     *
     */

    public function updateTaxRates($collection)
    {
        $countUpdates = 0;
        foreach ($collection as $data) {
            if ($data instanceof BundleEntity\TaxRate) {
                $entity = $data;
                $this->em->persist($entity);
                $updatedItems[] = $entity;
                $countUpdates++;
            } else if (is_object($data)) {
                $response = $this->getTaxRate($data->id, 'id');
                if ($response['error']) {
                    return $this->createException('EntityDoesNotExist', 'TaxRate with id ' . $data->id, 'err.invalid.entity');
                }
                $oldEntity = $response['result']['set'];
                $locationModel = new LocationManagementModel($this->kernel);
                foreach ($data as $column => $value) {
                    $set = 'set' . $this->translateColumnName($column);
                    switch ($column) {
                        case 'local':
                            $localizations = array();
                            foreach ($value as $langCode => $translation) {
                                $localization = $oldEntity->getLocalization($langCode, true);
                                $newLocalization = false;
                                if (!$localization) {
                                    $newLocalization = true;
                                    $localization = new BundleEntity\TaxRateLocalization();
                                    $mlsModel = $this->kernel->getContainer()->get('multilanguagesupport.model');
                                    $response = $mlsModel->getLanguage($langCode, 'iso_code');
                                    $localization->setLanguage($response['result']['set']);
                                    $localization->setProduct($oldEntity);
                                }
                                foreach ($translation as $transCol => $transVal) {
                                    $transSet = 'set' . $this->translateColumnName($transCol);
                                    $localization->$transSet($transVal);
                                }
                                if ($newLocalization) {
                                    $this->em->persist($localization);
                                }
                                $localizations[] = $localization;
                            }
                            $oldEntity->setLocalizations($localizations);
                            break;
                        case 'product_category':
                            $productModel = $this->kernel->getContainer()->get('productmanagement.model');
                            $response = $productModel->getProductCategory($value,'id');
                            if ($response['error']) {
                                new CoreExceptions\SiteDoesNotExistException($this->kernel, $data);
                            }
                            $oldEntity->$set($response['result']['set']);
                            unset($response, $productModel);
                            break;
                        case 'country':
                            $response = $locationModel->getCountry($value,'id');
                            if ($response['error']) {
                                new CoreExceptions\EntityDoesNotExistException($this->kernel, 'Country can not found.');
                            }
                            $oldEntity->$set($response['result']['set']);
                            unset($response);
                            break;
                        case 'state':
                            $response = $locationModel->getState($value,'id');
                            if ($response['error']) {
                                new CoreExceptions\EntityDoesNotExistException($this->kernel, 'State can not found.');
                            }
                            $oldEntity->$set($response['result']['set']);
                            unset($response);
                            break;
                        case 'city':
                            $response = $locationModel->getCity($value,'id');
                            if ($response['error']) {
                                new CoreExceptions\EntityDoesNotExistException($this->kernel, 'City can not found.');
                            }
                            $oldEntity->$set($response['result']['set']);
                            unset($response);
                            break;
                        case 'site':
                            $siteModel = $this->kernel->getContainer()->get('sitemanagement.model');
                            $response = $siteModel->getSite($value,'id');
                            if ($response['error']) {
                                new CoreExceptions\EntityDoesNotExistException($this->kernel, 'Site can not found.');
                            }
                            $oldEntity->$set($response['result']['set']);
                            unset($response,$siteModel);
                            break;
                        case 'date_added':
                        case 'date_updated':
                            new $oldEntity->$set(\DateTime('now', new \DateTimeZone($this->kernel->getContainer()->getParameter('app_timezone'))));
                            break;
                        case 'id':
                            break;
                        default:
                            $oldEntity->$set($value);
                            break;
                    }
                }
                unset($locationModel);
                $this->em->persist($oldEntity);
                $updatedItems[] = $oldEntity;
                $countUpdates++;
            } else {
                new CoreExceptions\InvalidDataException($this->kernel);
            }
        }
        /**
         * Save data.
         */
        if ($countUpdates > 0) {
            $this->em->flush();
        }
        /**
         * Prepare & Return Response
         */
        $this->response = array(
            'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $updatedItems,
                'total_rows' => $countUpdates,
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.db.insert.done',
        );
        return $this->response;
    }
}
/**
 * Change Log:
 * **************************************
 * v1.0.4                      Can Berkol
 * 24.06.2014
 * **************************************
 * A getTaxRateOfProduct()
 * **************************************
 * v1.0.3                      Can Berkol
 * 23.05.2014
 * **************************************
 * U updateTaxRate()
 *
 * **************************************
 * v1.0.2                   Said İmamoğlu
 * 20.05.2014
 * **************************************
 * A listTaxRatesOfProduct()
 * A listTaxRatesOfProductCategories()
 *
 * **************************************
 * v1.0.1                   Said İmamoğlu
 * 21.03.2014
 * **************************************
 * U deleteTaxRate()
 * U deleteTaxRates()
 * U listTaxRate()
 * U getTaxRate()
 * U doesTaxRateExist()
 * U inserTaxRate()
 * U inserTaxRates()
 * U updateTaxRate()
 * U updateTaxRates() 
 * **************************************
 * v1.0.0                   Said İmamoğlu
 * 30.01.2014
 * **************************************
 * A deleteTaxRate()
 * A deleteTaxRates()
 * A listTaxRate()
 * A getTaxRate()
 * A doesTaxRateExist()
 * A inserTaxRate()
 * A inserTaxRates()
 * A updateTaxRate()
 * A updateTaxRates()
 * 
 */