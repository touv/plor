<?php
// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 fdm=marker :
/**
 * PLOR
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
 * @category  PSO
 * @package   PLOR
 * @author    Nicolas Thouvenin <nthouvenin@gmail.com>
 * @copyright 2010 Nicolas Thouvenin
 * @license   http://opensource.org/licenses/bsd-license.php BSD Licence
 */

require_once 'PSO.php';
require_once 'Fetchor.php';
require_once 'Dumpable.php';
require_once 'Encoding.php';

/**
 * a Map of PSO Object
 *
 * @category  PSO
 * @package   PLOR
 * @author    Nicolas Thouvenin <nthouvenin@gmail.com>
 * @copyright 2010 Nicolas Thouvenin
 * @license   http://opensource.org/licenses/bsd-license.php BSD Licence
 */
class PSOMap implements Countable, Fetchor, Dumpable, Encoding
{
    protected $__encoding = 'UTF-8';
    protected $content = array();

    /**
     * Constructor
     * @param string 
     * @param string
     */
    public function __construct()
    {
        $this->exchange();
    }

    /**
     * Factory
     * @param string 
     * @param string
     * @return PSO
     */
    public static function factory()
    {
        return new PSOMap();
    }

    /**
     * Exchange
     *
     * @param string 
     * @param string
     * @return PSO
     */
    public function exchange() 
    {
        return $this;
    }

    /**
     * for Interface Countable
     * @return integer
     */
    public function count()
    {
        return count($this->content);
    }

    /**
     * Use the class as string
     * @return string
     */
    public function __toString()
    {
        return (string)$this->splice();
    }

    /**
     * Convert class to string
     * @return string
     */
    public function toString()
    {
        return (string)$this->splice();
    }

    /**
     * Ferme le curseur courant 
     *
     * @return PQO
     */
    public function close()
    {
        reset($this->content);
        return $this;
    }

    /**
     * set item
     * @param string
     * @param PSO
     * @return PSOMap
     */
    public function set($key, $value)
    {
        if (!is_string($key)) {
            throw new ErrorException('Argument 1 passed to '.__METHOD__.' must be a string, '.gettype($key).' given', E_USER_ERROR);
        }
        if (! $value instanceof PSO and ! $value instanceof PSOVector and ! $value instanceof PSOMap) {
            throw new ErrorException('Argument 1 passed to '.__METHOD__.' must be a instance of PSO, PSOVector or PSOMap, '.gettype($value).' given', E_USER_ERROR);
        }
        $key = preg_replace('/^([0-9])/', '_\\1', trim(preg_replace('/[^A-Za-z0-9_]/', '_', $key), '_'));
        $this->content[$key] = $value;
        return $this;
    }

    /**
     * magic method
     *
     * @param string
     * @param object
     */
    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * delete item
     *
     * @param string
     * @return PSOMap
     */
    public function del($key)
    {
        if (!is_string($key))
            throw new ErrorException('Argument 1 passed to '.__METHOD__.' must be a string, '.gettype($key).' given', E_USER_ERROR);

        if (isset($this->content[$key]))
            unset($this->content[$key]);
        return $this;
    }

    /**
     * magic method
     *
     * @param string
     * @param object
     */
    public function __unset($key)
    {
        $this->del($key);
    }

    /**
     * get item
     *
     * @param string
     * @return PSO
     */
    public function get($key)
    {
        if (!is_string($key))
            throw new ErrorException('Argument 1 passed to '.__METHOD__.' must be a string, '.gettype($key).' given', E_USER_ERROR);

        if (isset($this->content[$key]))
            return $this->content[$key];
//        else 
//            return new PSO('', $this->__encoding);
    }

    /**
     * magic method
     *
     * @param string
     * @return PSO
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * map function on fetch 
     *
     * @return object
     */
    public function map($f)
    {
        if (!is_callable($f))
            throw new ErrorException('Argument 1 passed to '.__METHOD__.' must be a function, '.gettype($f).' given', E_USER_ERROR);
        while($r = $this->fetch()) if (call_user_func($f, $r) === false) break;
        return $this;
    }

    /**
     * fetch item
     *
     * @return object
     */
    public function fetch()
    {
        $r = current($this->content);
        if ($r === false) {
            $this->close();
            return false;
        }
        next($this->content);
        return $r;
    }

    /**
     * Retourne toute les lignes du rÃ©sulat de la requete 
     *
     * @return PSOVector
     */
    public function fetchAll()
    {
        return $this;
    }
    
    /**
     * Dump content of the class
     * @return PSOVector
     */
    public function dump($s = null)
    {
        echo $this->toString(), $s;
        return $this;
    }

    /**
     * set string encoding
     * @return PSOVector
     */
    public function fixEncoding($e)
    {
        if (!is_string($e))
            throw new ErrorException('Argument 1 passed to '.__METHOD__.' must be a string, '.gettype($e).' given', E_USER_ERROR);
        $this->__encoding = $e;
        return $this;
    }

    /**
     * splice
     *
     * @return PSO
     */
    public function splice($glue = null)
    {
        $ret = new PSO('', $this->__encoding);
        while($row = $this->fetch()) {
            if (!is_null($glue)) {
                $ret->concat($glue);
            }
            $ret->concat($row);
        }
        return $ret;
    }

}

