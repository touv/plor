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
 * @category  DAT
 * @package   PLOR
 * @author    Nicolas Thouvenin <nthouvenin@gmail.com>
 * @copyright 2010 Nicolas Thouvenin
 * @license   http://opensource.org/licenses/bsd-license.php BSD Licence
 */

require_once 'Fetchor.php';
require_once 'Dumpable.php';
require_once 'Encoding.php';
require_once 'PSO.php';

/**
 * a Array & stdClass facade in PHP
 *
 * @category  DAT
 * @package   PLOR
 * @author    Nicolas Thouvenin <nthouvenin@gmail.com>
 * @copyright 2010 Nicolas Thouvenin
 * @license   http://opensource.org/licenses/bsd-license.php BSD Licence
 */
class DAT implements Fetchor, Countable, Dumpable, Encoding
{
    protected $__encoding = 'UTF-8';
    private $__reader;
    private $__size;

    /**
     * Constructor
     * @param string 
     * @param string
     */
    public function __construct($content = null)
    {
        $this->exchange($content);
    }

    /**
     * Factory
     * @param string 
     * @param string
     * @return PSO
     */
    public static function factory($content = null)
    {
        return new DAT($content);
    }

    /**
     * Exchange
     *
     * @param string 
     * @param string
     * @return PSO
     */
    public function exchange($content = null) 
    {
        $this->__reader = new stdClass;
        $this->__reader->stack = array($this);
        $this->__reader->types = array($this->_getcase($this));
        $this->__reader->names = array();
        $this->__reader->depth = 0;
        $this->__reader->position = 0;
        $this->__reader->allowed_types = array('DAT', 'array');
        $this->__size = 0;

        if (!is_null($content)) {
            if (in_array($this->_getcase($content), $this->__reader->allowed_types)) {
                $this->root = $this->_checkval($content);
                $this->__size  = count($this->fetchAll());
            }
            else {
                throw new ErrorException('Argument 1 passed to '.__METHOD__.' must be a allowed type, '.gettype($content).' given', E_USER_ERROR);
            }
        }


        return $this;
    }

    /**
     * define by Countable interface
     * @return integer
     */
    public function count()
    {
        return $this->__size;
    }

    /**
     * add
     * @return DAT
     */
    public function add($k, $v)
    {
        if (!to_string($k))
            throw new ErrorException('Argument 1 passed to '.__METHOD__.' must be a string, '.gettype($k).' given', E_USER_ERROR);

        $k = self::normalizeKey($k);

        if (!isset($this->{$k})) {
            $this->{$k} = array($this->_checkval($v));
        }
        elseif (!is_array($this->{$k})) {
            $this->{$k} = array($this->{$k}, $this->_checkval($v));
        }
        else {
            $this->{$k}[] = $this->_checkval($v);
        }
        ++$this->__size;
        return $this;
    }

    /**
     * set
     * @return DAT
     */
    public function set($k, $v)
    {
        if (!to_string($k))
            throw new ErrorException('Argument 1 passed to '.__METHOD__.' must be a string, '.gettype($k).' given', E_USER_ERROR);

        $k = self::normalizeKey($k);
        $this->{$k} = $this->_checkval($v);
        ++$this->__size;
        return $this;
    }

    /**
     * append
     * @return DAT
     */
    public function append($v)
    {
        if (!isset($this->root)) {
            $this->root = array($this->_checkval($v));
        }
        else {
            $this->root[] = $this->_checkval($v);
        }
        ++$this->__size;
        return $this;
    }



    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Use the class as string
     * @return string
     */
    public function __toString()
    {
        return (string)$this->slice(',')->toString();
    }

    /**
     * Convert class to string
     * @return string
     */
    public function toString()
    {
        return (string)$this->splice(',')->toString();
    }

    /**
     * Dump content of the class
     * @return DAT
     */
    public function dump($s = null)
    {
        echo $this->toString(), $s;
        return $this;
    }

    /**
     * set string encoding
     * @return DAT
     */
    public function fixEncoding($e)
    {
        $this->__encoding = $e instanceof PSO ? $e->toString() : $e;
        if (!to_string($this->__encoding))
            throw new ErrorException('Argument 1 passed to '.__METHOD__.' must be a string, '.gettype($e).' given', E_USER_ERROR);
        return $this;
    }

    /**
     * Ferme le curseur courant 
     *
     * @return PQO
     */
    public function close()
    {
        $this->__reader->position = 0;
        foreach($this->__reader->stack as $k => $v)
            reset($this->__reader->stack[$k]);
        return $this;
    }


    private function _getcase(&$o) 
    {
        if ($o instanceof PSO) {
            return 'PSO';
        }
        elseif ($o instanceof DAT) {
            return 'DAT';
        }
        elseif (is_object($o)) {
            return get_class($o);
        }
        elseif (is_array($o)) {
            return 'array';
        }
        elseif (to_string($o)) {
            return 'string';
        }
        elseif (is_int($o) or is_float($o)) {
            return 'numeric';
        }
        else {
            return null;
        }
    }
    private function _checkval($v) 
    {
        $type = $this->_getcase($v);
        if ($type == 'PSO'  or in_array($type, $this->__reader->allowed_types)) {
            return $v;
        }
        elseif ($type == 'string') {
            return PSO::factory($v)->fixEncoding($this->__encoding);
        }
        elseif ($type == 'numeric') {
            return $v;
        }
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
     * Retourne une portion de chaine
     *
     * @return object
     */
    public function fetch()
    {
        $cur = current($this->__reader->stack[$this->__reader->depth]);
        $nam = key($this->__reader->stack[$this->__reader->depth]);
        if ($cur !== false and isset($nam[0]) and !ord($nam[0])) {
            next($this->__reader->stack[$this->__reader->depth]);
            return $this->fetch();
        }

        if ($cur === false and $this->__reader->depth == 0) {
            $this->close();
            return false;
        }
        elseif ($cur === false and $this->__reader->depth > 0) {
            array_pop($this->__reader->stack);
            array_pop($this->__reader->names);
            array_pop($this->__reader->types);
            --$this->__reader->depth;
            return $this->fetch();
        }
        $bu = implode($this->__reader->names, ':');
        $r = self::factory()
            ->set('name', $nam) 
            ->set('baseURI', $bu)
            ->set('uri', ltrim(rtrim($bu, ':').':'.$nam, ':'))
            ->set('value', $cur)
            ->set('index', $this->__reader->position++)
            ->set('type', $this->_getcase($cur))
            ->set('depth', $this->__reader->depth);

        next($this->__reader->stack[$this->__reader->depth]);
        if ($r->type === $this->__reader->types[$this->__reader->depth] or in_array($r->type, $this->__reader->allowed_types)) {
            ++$this->__reader->depth;
            $this->__reader->stack[$this->__reader->depth] =& $cur;
            reset($this->__reader->stack[$this->__reader->depth]);
            $this->__reader->types[$this->__reader->depth] = $r->type;
            $this->__reader->names[$this->__reader->depth] = $r->name;
            return $this->fetch();
        }
//        return $r;
        return $cur;
    }

    /**
     * Retourne toute les lignes du résulat de la requete 
     *
     * @return DAT
     */
    public function fetchAll()
    {
        $ret = new DAT;
        while($row = $this->fetch()) 
            $ret->append($row);
        return $ret;
    }

    /**
     * splice
     *
     * @return PSO
     */
    public function splice($glue = null)
    {
        $ret = new PSO;
        while(($row = $this->fetch()) !== false) {
            if (!is_null($glue)) {
                $ret->concat($glue);
            }
            $ret->concat($row);
        }
        return $ret;
    }


    /**
     * set
     * @return DAT
     */
    static public function normalizeKey($k)
    {
        $k = trim(preg_replace('/[^A-Za-z0-9_]/', '_', (string) $k), '_');
        $k = preg_replace('/^([0-9])/', '_\\1', $k);
        return $k;
    }


}

