<?php
/**
 * Syncroton
 *
 * @package     Model
 * @license     http://www.tine20.org/licenses/lgpl.html LGPL Version 3
 * @copyright   Copyright (c) 2012-2012 Metaways Infosystems GmbH (http://www.metaways.de)
 * @author      Lars Kneschke <l.kneschke@metaways.de>
 */

/**
 * abstract class to handle ActiveSync entry
 *
 * @package     Model
 */

abstract class Syncroton_Model_AEntry implements Syncroton_Model_IEntry, IteratorAggregate, Countable
{
    protected $_elements = array();
    
    protected $_properties = array();
    
    public function __construct($properties = null)
    {
        if ($properties instanceof SimpleXMLElement) {
            $this->setFromSimpleXMLElement($properties);
        } elseif (is_array($properties)) {
            $this->setFromArray($properties);
        }
    }
    
    #abstract public function appendXML(DOMElement $_domParrent);
        
    public function count()
    {
        return count($this->_elements);
    }
    
    public function getIterator() 
    {
        return new ArrayIterator($this->_elements);
    }
    
    public function setFromArray(array $properties)
    {
        $this->_elements = array();
        
        foreach($properties as $key => $value) {
            try {
                $this->$key = $value;
            } catch (InvalidArgumentException $iae) {
                //ignore invalid properties
            }
        }
    }
    
    /**
     * 
     * @param SimpleXMLElement $xmlCollection
     * @throws InvalidArgumentException
     */
    public function setFromSimpleXMLElement(SimpleXMLElement $properties)
    {
        if ($properties->getName() !== 'ApplicationData') {
            throw new InvalidArgumentException('Unexpected element name: ' . $properties->getName());
        }
        
        $this->_elements = array();
        
        $this->_parseContactsNamespace($properties);
        $this->_parseContacts2Namespace($properties);
        
        $airSyncBaseData = $properties->children('uri:AirSyncBase');
        
        return;
    }
    
    public function &__get($name)
    {
        if (!array_key_exists($name, $this->_properties['Contacts']) && !array_key_exists($name, $this->_properties['Contacts2'])) {
            throw new InvalidArgumentException("$name is no valid property of this object");
        }
        
        return $this->_elements[$name];
    }
    
    public function __set($name, $value)
    {
        if (!array_key_exists($name, $this->_properties['Contacts']) && !array_key_exists($name, $this->_properties['Contacts2'])) {
            throw new InvalidArgumentException("$name is no valid property of this object");
        }
        
        $properties = isset($this->_properties['Contacts'][$name]) ? $this->_properties['Contacts'][$name] : $this->_properties['Contacts2'][$name];
        
        if ($properties['type'] == 'datetime' && !$value instanceof DateTime) {
            throw new InvalidArgumentException("value for $name must be an instance of DateTime");
        }
        
        $this->_elements[$name] = $value;
    }
    
    public function __isset($name)
    {
        return isset($this->_elements[$name]);
    }
    
    public function __unset($name)
    {
        unset($this->_elements[$name]);
    }
}