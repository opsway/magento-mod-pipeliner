<?php
/**
 * This file is part of the Pipeliner API client library for PHP
 *
 * Copyright 2014 Pipelinersales, Inc. All Rights Reserved.
 * For the full license information, see the attached LICENSE file.
 */

class PipelinerSales_Repository_Rest_RestRepositoryFactory implements PipelinerSales_Repository_RepositoryFactoryInterface
{
    private $baseUrl;
    private $httpClient;
    private $dateTimeFormat;
    
    public function __construct($baseUrl, $httpClient, $dateTimeFormat)
    {
        $this->baseUrl = $baseUrl;
        $this->httpClient = $httpClient;
        $this->dateTimeFormat = $dateTimeFormat;
    }

    public function createRepository($entitySingular, $entityPlural)
    {
        return new PipelinerSales_Repository_Rest_RestRepository(
            $this->baseUrl,
            $entitySingular,
            $entityPlural,
            $this->httpClient,
            $this->dateTimeFormat
        );
    }
}
