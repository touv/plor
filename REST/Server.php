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

require_once 'REST/Section.php';
require_once 'REST/Level.php';
require_once 'REST/Headers.php';
/**
 * a simple REST Server in PHP
 *
 * @category  REST
 * @package   REST_Server
 * @author    Nicolas Thouvenin <nthouvenin@gmail.com>
 * @copyright 2010 Nicolas Thouvenin
 * @license   http://opensource.org/licenses/bsd-license.php BSD Licence
 */
class REST_Server
{
    protected $options = array(
        'ignore_user_abort' => true,
        'time_limit'        => 0,
        'error_reporting'   => E_ALL,
        'implicit_flush'    => true,
        'powered_by'        => 'REST_Server/1.0-php',
        'compatible'        => false,
    );
    protected $root;
    protected $sections;
    protected $format;
    protected $method;

    /**
     * Constructor
     * @param string
     * @param array
     */
    function __construct($rfs = null, $options = array())
    {
        $this->options = array_merge($this->options, $options);

        ignore_user_abort($this->options['ignore_user_abort']);
        set_time_limit($this->options['time_limit']);
        error_reporting($this->options['error_reporting']);
        ob_implicit_flush($this->options['implicit_flush']);

        $this->root = new REST_Level();

        $r = explode('/', ltrim(self::path(), '/'));
        foreach($r as $s) {
            $sec = new REST_Section($s);
            $this->format = $sec->getExtension();
            $this->sections[] = $sec;
        }

        $this->method = strtoupper($_SERVER['REQUEST_METHOD']);
        if ($this->options['compatible'] and isset($_GET['_']))
            $this->method = strtoupper($_GET['_']);
    }

    /**
     * return the root of the resource
     * @return REST_Level
     */
    public function root()
    {
        return $this->root;
    }

    /**
     * handle
     */
    public function handle()
    {
        $levels = array($this->root);

        $l = $this->root;
        while($l = $l->nextLevel()) 
            $levels[] = $l;

        $i = count($this->sections) - 1;
        if ($i < 0) $i = 0;

        $found = null;
        if (isset($levels[$i])) foreach($levels[$i] as $resource) {
            if ($resource->match($this->sections[$i])) {
                $found = $resource;
                break;
            }
        }

        $headers = new REST_Headers($this->options['powered_by']);
        if (is_null($found)) 
            return $headers->send(404, true);

        if (!$found->existsAction($this->method))
            return $headers->send(405, true);

        if (!$found->setHeaders($headers))
            return $headers->send(500, true);

        $found->execAction($this->method, $headers, $this->sections);

        if (!$headers->getStatus())
            return $headers->send(500, true);

        return $headers->getStatus();
    }

    /**
     * Get host of the server
     * @return string
     */
    static public function host()
    {
        static $host;
        if (!is_null($host)) return $host;

        if (isset($_SERVER['HTTP_HOST'])) {
            $host = 'http'.
                (!isset($_SERVER['HTTPS'])||strtolower($_SERVER['HTTPS'])!= 'on'?'':'s').
                '://'.
                (isset($_SERVER["HTTP_X_FORWARDED_HOST"]) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST']);
        } else {
            $host = '';
        }
        $host = rtrim($host, '/');
        return $host;
    }

    /**
     * Get uri of the server
     * @return string
     */
    static public function uri()
    {
        static $uri;
        if (!is_null($uri)) return $uri;
        $uri = self::host().self::path();
        return $uri;
    }

    /**
     * Get path of the server
     * @return string
     */
    static public function path()
    {
        static $path;
        if (!is_null($path)) return $path;

        $uriAll = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $_SERVER['PHP_SELF'];
        $path = false !== ($q = strpos($uriAll, '?')) ? substr($uriAll, 0, $q) : $uriAll;
        return $path;
    }

}
