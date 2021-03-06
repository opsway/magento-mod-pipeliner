<?php
/**
 * This file is part of the Pipeliner API client library for PHP
 *
 * Copyright 2014 Pipelinersales, Inc. All Rights Reserved.
 * For the full license information, see the attached LICENSE file.
 */

interface PipelinerSales_Repository_RepositoryFactoryInterface
{
    /**
     * @param string $entitySingular
     * @param string $entityPlural
     * @return RepositoryInterface
     */
    public function createRepository($entitySingular, $entityPlural);
}
