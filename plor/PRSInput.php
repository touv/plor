<?php
// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 fdm=marker encoding=utf8 :
/**
 * P3C
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
 * @category  PRS
 * @package   PRSClient
 * @author    Nicolas Thouvenin <nthouvenin@gmail.com>
 * @copyright 2010 Nicolas Thouvenin
 * @license   http://opensource.org/licenses/bsd-license.php BSD Licence
 */


/**
 * access to http infos
 *
 * @category  PRS
 * @package   P3C
 * @author    Nicolas Thouvenin <nthouvenin@gmail.com>
 * @copyright 2010 Nicolas Thouvenin
 * @license   http://opensource.org/licenses/bsd-license.php BSD Licence
 */
class PRSInput
{
    public function __construct()
    {
    }

    /**
     * Get method of the server
     * @return string
     */
    public function method()
    {
        static $method;
        if (!is_null($method)) return $method;
        $method = strtoupper($_SERVER['REQUEST_METHOD']);

        return $method;
    }

    /**
     * Get host of the server
     * @return string
     */
    public function host()
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
    public function uri()
    {
        static $uri;
        if (!is_null($uri)) return $uri;
        $uri = $this->host().$this->fullpath();
        return $uri;
    }

    /**
     * Get full path of the server (including base)
     * @return string
     */
    public function fullpath()
    {
        static $fullpath;
        if (!is_null($fullpath)) return $fullpath;

        if (!empty($_SERVER['REQUEST_BASE']))
            $fullpath = $_SERVER['REQUEST_BASE'].$this->path();
        else 
            $fullpath = $this->path();
        return $fullpath;
    }


    /**
     * Get path of the server
     * @return string
     */
    public function path()
    {
        static $path;
        if (!is_null($path)) return $path;

        $uriAll = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $_SERVER['PHP_SELF'];
        $path = false !== ($q = strpos($uriAll, '?')) ? substr($uriAll, 0, $q) : $uriAll;
        if (!empty($_SERVER['REQUEST_BASE']))
            $path = preg_replace('/^'.preg_quote($_SERVER['REQUEST_BASE'], '/').'/',  '', $path);
        return $path;
    }

}
