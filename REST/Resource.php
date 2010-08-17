<?php
// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 fdm=marker encoding=utf8 :
/**
 * REST_Server
 *
 * Copyright (c) 2010, Nicolas Thouvenin
 *
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of the author nor the names of its contributors may be
 *       used to endorse or promote products derived from this software without
 *       specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE REGENTS AND CONTRIBUTORS ``AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE REGENTS AND CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category  REST
 * @package   REST_Client
 * @author    Nicolas Thouvenin <nthouvenin@gmail.com>
 * @copyright 2010 Nicolas Thouvenin
 * @license   http://opensource.org/licenses/bsd-license.php BSD Licence
 */
require_once 'REST/Parameters.php';
/**
 * A REST Resource
 *
 * @category  REST
 * @package   REST_Server
 * @author    Nicolas Thouvenin <nthouvenin@gmail.com>
 * @copyright 2010 Nicolas Thouvenin
 * @license   http://opensource.org/licenses/bsd-license.php BSD Licence
 */
abstract class REST_Resource
{
    protected $actions = array();
    protected $mimetype;
    protected $extension;
    protected $namespace;
    protected $headers = array();
    /**
     * Constructor
     * @param string
     * @param string
     */
    function __construct($namespace, $extension, $mimetype = null)
    {
        $this->extension = trim($extension);

        if ($this->extension === '')
            $this->extension = null;

        $this->namespace = trim($namespace);
        if ($this->namespace === '')
            $this->namespace = null;

        $this->mimetype = trim($mimetype);
        if ($this->mimetype === '')
            $this->mimetype = null;

        if (!is_null($this->namespace) and !preg_match("/[a-z]+/", $this->namespace))
            return trigger_error(sprintf('%s::__construct expects parameter 0 to be string that only contains letters in lowercase', __CLASS__), E_USER_ERROR);
        if (!!is_null($this->extension) and !preg_match("/[[:alnum:]]+/i", $this->extension))
            return trigger_error(sprintf('%s::__construct expects parameter 1 to be string that only contains numbers and letters', __CLASS__), E_USER_ERROR);

        if (is_null($mimetype)) {
            include_once 'REST/Mime.php';
            if (!isset(REST_Mime::$types[$extension])) {
                 return trigger_error(sprintf('%s::__construct() unknown extension `%s`', $__CLASS__, $extension), E_USER_ERROR);
            }
            $this->mimetype = REST_Mime::$types[$extension];
        }
        else {
            $this->mimetype = $mimetype;
        }
        $this->headers['Content-Type'] = $this->mimetype;
    }

    /**
     * existsAction
     *
     * Test si une action exists pour cette ressource
     *
     * @param string
     */
    public function existsAction($method)
    {
        return isset($this->action[$method]);
    }

    /**
     * addAction
     *
     * Ajout d'une action sur la ressource
     *
     * @param string
     * @param callback
     * @param array
     * @param integer
     */
    public function addAction($method, $callback, $parameters = array(), $attribut = null)
    {
        if (!preg_match("/[A-Z]+/", $method))
            return trigger_error(sprintf('%s::addAction() expects parameter 0 to be string that only contains letters in uppercase. %s[%s]', __CLASS__, $method, $this), E_USER_ERROR);
        if (!is_callable($callback))
            return trigger_error(sprintf('%s::addAction() expects parameter 1 to be callable. %s[%s]', __CLASS__, $method, $this), E_USER_ERROR);
        if (!is_array($parameters))
            return trigger_error(sprintf('%s::addAction() expects parameter 2 to be array, %s given. %s[%s]', __CLASS__, gettype($parameters), $method, $this), E_USER_ERROR);

        $this->action[strtoupper($method)] = array(
            'callback'   => $callback,
            'parameters' => $parameters,
            'attribut'   => $attribut,
        );

        return $this;
    }

    /**
     * execAction
     *
     * execute une Action sur la ressource
     *
     * @param string
     */
    public function execAction($method, REST_Headers $headers, array $sections)
    {
        return (boolean) call_user_func(
            $this->action[$method]['callback'], 
            new REST_Parameters($sections, $this->action[$method]['parameters']),
            $headers,
            $this->action[$method]['attribut']
        );
    }

    /**
     * index
     *
     * Create a Index Resource
     *
     * @param string
     * @param string
     */
    static function index($extension, $mimetype = null)
    {
        include_once 'REST/Resource/Index.php';
        return new REST_Resource_Index(null, $extension, $mimetype);
    }

    /**
     * leaf
     *
     * Create a Leaf Resource
     *
     * @param string
     * @param string
     */
    static function leaf($extension, $mimetype = null)
    {
        include_once 'REST/Resource/Leaf.php';
        return new REST_Resource_Leaf(null, $extension, $mimetype);
    }

    /**
     * index
     *
     * Create a Index Resource
     *
     * @param string
     * @param string
     */
    static function indexNS($namespace, $extension, $mimetype = null)
    {
        include_once 'REST/Resource/Index.php';
        return new REST_Resource_Index($namespace, $extension, $mimetype);
    }

    /**
     * leaf
     *
     * Create a Leaf Resource
     *
     * @param string
     * @param string
     */
    static function leafNS($namespace, $extension, $mimetype = null)
    {
        include_once 'REST/Resource/Leaf.php';
        return new REST_Resource_Leaf($namespace, $extension, $mimetype);
    }

    /**
     * setHeaders
     *
     * @param REST_Headers
     * @return boolean
     */
    public function setHeaders(REST_Headers $h)
    {
        foreach($this->headers as $k => $v)
            $h->add($k, $v);
        return true;
    }


    /**
     * __tostring
     *
     * @return string
     */
    public function __toString()
    {
        return (!is_null($this->namespace) ? '::'.$this->namespace : '').' .'.$this->extension.' '.$this->mimetype;
    }

    abstract public function match(REST_Section $section);
}
