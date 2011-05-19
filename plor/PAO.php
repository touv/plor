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
require_once 'Fetchor.php';

/**
 * a array facade in PHP
 *
 * @category  PAO
 * @package   PLOR
 * @author    Nicolas Thouvenin <nthouvenin@gmail.com>
 * @copyright 2010 Nicolas Thouvenin
 * @license   http://opensource.org/licenses/bsd-license.php BSD Licence
 */
class PAO implements Countable, Iterator, ArrayAccess, Fetchor
{
    public static $encoding = 'UTF-8';
 
    protected $content;

    /**
     * Constructor
     * @param string 
     * @param string
     */
    public function __construct($content = array())
    {
        $this->exchange($content);
    }

    /**
     * Factory
     * @param string 
     * @param string
     * @return PSO
     */
    public static function factory($content = array())
    {
        return new PAO($content);
    }

    /**
     * Exchange
     *
     * @param string 
     * @param string
     * @return PSO
     */
    public function exchange($content = array()) 
    {
        if (is_null($content)) $content = array(); // Pas de valeur null
        if (!is_array($content))
            trigger_error('Argument 1 passed to '.__METHOD__.' must be a array, '.gettype($content).' given', E_USER_ERROR);
        $this->content = $content;
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
     * for Interface Iterator
     * @return boolean
     */
    public function valid()
    {
        return array_key_exists(key($this->content), $this->content);
    }

    /**
     * for Interface Iterator
     * @return boolean
     */
    public function next()
    {
        next($this->content);
        return $this;
    }

    /**
     * for Interface Iterator
     * @return boolean
     */
    public function rewind()
    {
        reset($this->content);
        return $this;
    }

    /**
     * for Interface Iterator
     * @return boolean
     */
    public function key()
    {
        return key($this->content);
    }

    /**
     * for Interface Iterator
     * @return boolean
     */
    public function current()
    {
        return current($this->content);
    }

    /**
     * for Interface ArrayAccess
     * @return boolean
     */
    public function offsetGet($offset)
    {
        return $this->content[$offset];
    }

    /**
     * for Interface ArrayAccess
     * @return boolean
     */
    public function offsetSet($offset, $value)
    {
        $this->content[$offset] = $value;
    }

    /**
     * for Interface ArrayAccess
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->content[$offset]);
    }

    /**
     * for Interface ArrayAccess
     * @return boolean
     */
    public function offsetUnset($offset)
    {
        unset($this->content[$offset]);
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
     * ajout d'un élement
     *
     * @return object
     */
    public function append($value)
    {
        $this->content[] = $value;
        return $this;
    }

    /**
     * fixe un élement
     *
     * @return object
     */
    public function fix($key, $value)
    {
        if (!is_string($key))
            trigger_error('Argument 1 passed to '.__METHOD__.' must be a string, '.gettype($key).' given', E_USER_ERROR);

        $this->content[$key] = $value;
        return $this;
    }



    /**
     * Retourne un élement
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
     * Retourne toute les lignes du résulat de la requete 
     *
     * @return PAO
     */
    public function fetchAll()
    {
        return $this;
    }

    /**
     * splice
     *
     * @return PSO
     */
    public function splice($glue = null)
    {
        $ret = new PSO('', self::$encoding);
        while($row = $this->fetch()) {
            if (!is_null($glue)) {
                $ret->concat($glue);
            }
            $ret->concat($row);
        }
        return $ret;
    }

}
