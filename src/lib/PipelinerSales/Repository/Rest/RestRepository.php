<?php
/**
 * This file is part of the Pipeliner API client library for PHP
 *
 * Copyright 2014 Pipelinersales, Inc. All Rights Reserved.
 * For the full license information, see the attached LICENSE file.
 */

/**
 * Class for retrieving and manipulating entities using Pipeliner's REST API.
 */
class PipelinerSales_Repository_Rest_RestRepository implements PipelinerSales_Repository_RepositoryInterface
{

    private $httpClient;
    private $urlPrefix;
    private $entityType;
    private $entityPlural;
    private $dateTimeFormat;

    public function __construct($urlPrefix, $entity, $entityPlural, PipelinerSales_Http_HttpInterface $connection, $dateTimeFormat)
    {
        $this->httpClient = $connection;
        $this->urlPrefix = $urlPrefix;
        $this->entityType = $entity;
        $this->entityPlural = $entityPlural;
        $this->dateTimeFormat = $dateTimeFormat;
    }

    public function getById($id)
    {
        $result = $this->httpClient
                ->request('GET', $this->urlPrefix . '/' . $this->entityPlural . '/' . $id)
                ->decodeJson();
        return $this->decodeEntity($result);
    }

    public function get($criteria = null)
    {
        $criteriaUrl = '?';
        if (is_null($criteria)) {
            $criteriaUrl = '';
        } elseif (is_array($criteria)) {
            $criteriaUrl .= $this->arrayCriteriaToUrl($criteria);
        } elseif (is_object($criteria)) {
            $criteriaUrl .= $this->objectCriteriaToUrl($criteria);
        } elseif (is_string($criteria)) {
            $criteriaUrl .= $criteria;
        } else {
            throw new PipelinerSales_PipelinerClientException('Invalid criteria type');
        }

        $response = $this->httpClient
                ->request('GET', $this->urlPrefix . '/' . $this->entityPlural . $criteriaUrl);
        $results = $response->decodeJson();
        $decoded = array_map(array($this, 'decodeEntity'), $results);

        $range = $this->getContentRange($response);
        if ($range === null) {
            throw new PipelinerSales_Http_PipelinerHttpException($response, 'Content-Range header not found');
        }

        return new PipelinerSales_EntityCollection($decoded, $criteria, $range['start'], $range['end'], $range['total']);
    }

    public function create()
    {
        return new PipelinerSales_Entity($this->entityType, $this->dateTimeFormat);
    }

    public function delete($entity, $flags = self::FLAG_ROLLBACK_ON_ERROR)
    {
        //if $entity is an array, it can either be a single entity
        //represented as an associative array, or an array of multiple entities,
        //i.e. [ 'ID' => 'Something' ] vs. [ [ 'ID' => 'Something1' ], [ 'ID' => 'Something2 ] ],
        //so we need to distinguish between these two cases
        if (is_array($entity)) {
            $first = reset($entity);

            //if the first element of $entity is also an array,
            //we are dealing with multiple entities
            if (is_array($first) or $first instanceof PipelinerSales_Entity) {
                return $this->deleteById(array_map(function ($item) {
                    return $item['ID'];
                }, $entity), $flags);
            }
        }

        if (!isset($entity['ID'])) {
            throw new PipelinerSales_PipelinerClientException('Cannot delete an entity which has no ID');
        }
        return $this->deleteById($entity['ID']);
    }

    public function deleteById($id, $flags = self::FLAG_ROLLBACK_ON_ERROR)
    {
        if (is_array($id)) {
            //bulk delete
            $json = json_encode($id);
            $url = $this->urlPrefix . '/deleteEntities?entityName=' . $this->entityType;
            if ($flags) {
                $url .= '&flag=' . $flags;
            }
            return $this->httpClient->request('POST', $url, $json);
        } else {
            return $this->httpClient->request('DELETE', $this->urlPrefix . '/' . $this->entityPlural . '/' . $id);
        }
    }

    public function bulkUpdate($data, $flags = self::FLAG_ROLLBACK_ON_ERROR, $sendFields = self::SEND_MODIFIED_FIELDS)
    {
        $payload = array();

        foreach ($data as $entity) {
            if ($entity instanceof PipelinerSales_Entity) {
                $values = (
                    $sendFields == self::SEND_MODIFIED_FIELDS ? $entity->getModifiedFields() : $entity->getFields()
                );
                $values['ID'] = $entity->getId();
                $payload[] = $values;
            } else {
                $payload[] = $entity;
            }
        }

        $json = json_encode($payload);
        $url = $this->urlPrefix . '/setEntities?entityName=' . $this->entityType;
        if ($flags) {
            $url .= '&flag=' . $flags;
        }
        return $this->httpClient->request('POST', $url, $json);
    }

    public function save($entity, $sendFields = self::SEND_MODIFIED_FIELDS)
    {
        if ($entity instanceof PipelinerSales_Entity) {
            $id = $entity->isFieldSet('ID') ? $entity->getId() : null;
            $data = ($sendFields == self::SEND_MODIFIED_FIELDS ? $entity->modifiedToJson() : $entity->allToJson());
        } elseif (is_array($entity)) {
            $id = isset($entity['ID']) ? $entity['ID'] : null;
            $data = json_encode($entity);
        } else {
            throw new \InvalidArgumentException('Invalid entity');
        }

        if (!empty($id)) {
            //The server currently interprets both of these:
            //  - POST to /Accounts, where the entity contains an ID
            //  - PUT to /Accounts/{id}
            //as partial updates.
            //Full PUT replacement is not implemented on the server.
            $method = 'PUT';
            $url = $this->urlPrefix . '/' . $this->entityPlural . '/' . $id;
        } else {
            $method = 'POST';
            $url = $this->urlPrefix . '/' . $this->entityPlural;
        }

        $response = $this->httpClient->request($method, $url, $data);

        //new entity was created, we need to get its ID
        if ($response->getStatusCode() == 201) {
            $response = new PipelinerSales_Repository_Rest_RestCreatedResponse($response);
            $newId = $response->getCreatedId();
            if ($entity instanceof PipelinerSales_Entity) {
                $entity->setId($newId);
            }
        }

        if (($response->getStatusCode() == 200 or $response->getStatusCode() == 201)
                and ( $entity instanceof PipelinerSales_Entity )) {
            $entity->resetModified();
        }

        return $response;
    }

    public function getEntireRangeIterator(PipelinerSales_EntityCollection $collection)
    {
        return new PipelinerSales_EntityCollectionIterator($this, $collection);
    }

    private function arrayCriteriaToUrl(array $criteria)
    {
        return http_build_query($criteria);
    }

    private function objectCriteriaToUrl($criteria)
    {
        if ($criteria instanceof PipelinerSales_Query_Criteria) {
            return $criteria->toUrlQuery();
        } elseif ($criteria instanceof PipelinerSales_Query_Filter) {
            $c = new PipelinerSales_Query_Criteria();
            $c->filter($criteria);
            return $c->toUrlQuery();
        } elseif ($criteria instanceof PipelinerSales_Query_Sort) {
            $c = new PipelinerSales_Query_Criteria();
            $c->sort($criteria);
            return $c->toUrlQuery();
        }
        throw new PipelinerSales_PipelinerClientException('Invalid criteria object');
    }

    private function decodeEntity(\stdClass $object)
    {
        $entity = new PipelinerSales_Entity($this->entityType, $this->dateTimeFormat);

        foreach ($object as $field => $value) {
            $entity->setField($field, $value);
        }
        $entity->resetModified();

        return $entity;
    }

    private function getContentRange(PipelinerSales_Http_Response $response)
    {
        $result = preg_match('~Content-Range: items (\d+)-([\d-]+)/(\d+)~i', $response->getHeaders(), $matches);
        if (!$result) {
            return null;
        }

        return array(
            'start' => intval($matches[1]),
            'end' => intval($matches[2]),
            'total' => intval($matches[3])
        );
    }
}
