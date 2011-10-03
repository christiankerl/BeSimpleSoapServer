<?php

/*
 * This file is part of the BeSimpleSoapServer.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 * (c) Francis Besset <francis.besset@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapServer;

use BeSimple\SoapCommon\SoapBaseBuilder;

/**
 * SoapServerBuilder provides a fluent interface to configure and create a SoapServer instance.
 * 
 * @author Christian Kerl
 */
class SoapServerBuilder extends SoapBaseBuilder
{
    public static function createEmpty()
    {
        return new self();
    }
    
    public static function createWithDefaults()
    {
        $builder = new self();
        $builder
            ->withSoapVersion12()
            ->withEncoding('UTF-8')
            ->withNoWsdlCache()
            ->withErrorReporting(false)
            ->withSingleElementArrays()
        ;
        
        return $builder;
    }
    
    private $optionPersistence;
    private $optionErrorReporting;
    
    private $optionHandlerClass = null;
    private $optionHandlerObject = null;
    
    /**
     * Initializes all options with the defaults used in the ext/soap SoapServer.
     */
    protected function __construct()
    {
        parent::__construct();
        
        $this->optionPersistence = SOAP_PERSISTENCE_REQUEST;
        
        // TODO: this is not the default, but safer
        $this->optionErrorReporting = false;
    }
    
    public function build()
    {
        $this->validateOptions();
        
        use_soap_error_handler($this->optionErrorReporting);
        
        $server = new SoapServer($this->optionWsdl, $this->options);
        $server->setPersistence($this->optionPersistence);
        
        if(null !== $this->optionHandlerClass)
        {
            $server->setClass($this->optionHandlerClass);
        }
        
        if(null !== $this->optionHandlerObject)
        {
            $server->setObject($this->optionHandlerObject);
        }
        
        return $server;
    }
    
    private function validateOptions()
    {
        if(null === $this->optionWsdl)
        {
            throw new \InvalidArgumentException('The WSDL has to be configured!');
        }
        
        if(null === $this->optionHandlerClass && null === $this->optionHandlerObject)
        {
            throw new \InvalidArgumentException('The handler has to be configured!');
        }
    }
    
    public function withActor($actor)
    {
        $this->options['actor'] = $actor; 
        
        return $this;
    }
    
    /**
     * Enables the HTTP session. The handler object is persisted between multiple requests in a session.
     */
    public function withHttpSession()
    {
        $this->optionPersistence = SOAP_PERSISTENCE_SESSION;
        
        return $this;
    }
    
    /**
     * Enables reporting of internal errors to clients. This should only be enabled in development environments.
     * 
     * @param boolean $enable
     */
    public function withErrorReporting($enable = true)
    {
        $this->optionErrorReporting = $enable;
        
        return $this;
    }
    
    /**
     * 
     * 
     * @param mixed $handler Can be either a class name or an object.
     */
    public function withHandler($handler)
    {
        if(is_string($handler) && class_exists($handler))
        {
            $this->optionHandlerClass = $handler;
            $this->optionHandlerObject = null;
        }
        
        if(is_object($handler))
        {
            $this->optionHandlerClass = null;
            $this->optionHandlerObject = $handler;
        }
        
        throw new \InvalidArgumentException('The handler has to be a class name or an object!');
    }
}
