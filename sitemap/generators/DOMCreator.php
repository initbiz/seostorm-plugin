<?php

declare(strict_types=1);

namespace Initbiz\SeoStorm\Sitemap\Generators;

use DOMAttr;
use DOMElement;
use DOMDocument;

class DOMCreator
{
    /**
     * DOMDocument that we create elements and attributes on
     *
     * @var DOMDocument
     */
    protected DOMDocument $document;

    public function __construct(DOMDocument $document)
    {
        $this->document = $document;
    }

    /**
     * Method that creates DOMElement using our DOMDocument instance
     *
     * @param string $name
     * @param string $value
     * @return DOMElement|false
     */
    public function createElement(string $name, string $value = ""): DOMElement|false
    {
        return $this->document->createElement($name, $value);
    }

    /**
     * Method to create attribute that can be then appended to any DOMElement
     *
     * @param string $localName
     * @return DOMAttr|false
     */
    public function createAttribute(string $localName): DOMAttr|false
    {
        return $this->document->createAttribute($localName);
    }
}
