<?php
/**
 * This file is part of the Pipeliner API client library for PHP
 *
 * Copyright 2014 Pipelinersales, Inc. All Rights Reserved.
 * For the full license information, see the attached LICENSE file.
 */

/**
 * Represents a HTTP 201 Created response
 */
abstract class PipelinerSales_Http_CreatedResponse extends PipelinerSales_Http_Response
{

    /**
     * Returns the ID of the newly created entity
     * @return string
     */
    abstract public function getCreatedId();
}
