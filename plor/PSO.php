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
require_once 'PAO.php';
require_once 'PSOStream.php';

/**
 * a string facade in PHP
 *
 * @category  PSO
 * @package   PLOR
 * @author    Nicolas Thouvenin <nthouvenin@gmail.com>
 * @copyright 2010 Nicolas Thouvenin
 * @license   http://opensource.org/licenses/bsd-license.php BSD Licence
 */
class PSO implements Countable, Fetchor
{
    static public $funcs = array('ord');

    protected $ending = "\n";

    protected $content;
    protected $size;
    protected $encoding;
    protected $position = 0;

    /**
     * Constructor
     * @param string 
     * @param string
     */
    public function __construct($content = '', $encoding = 'UTF-8')
    {
        $this->exchange($content, $encoding);
    }

    /**
     * Factory
     * @param string 
     * @param string
     * @return PSO
     */
    public static function factory($content = '', $encoding = 'UTF-8')
    {
        return new PSO($content, $encoding);
    }

    /**
     * Exchange
     *
     * @param string 
     * @param string
     * @return PSO
     */
    public function exchange($content = '', $encoding = 'UTF-8') 
    {
        if (is_null($content)) $content = ''; // Pas de valeur null
        if (!is_string($content))
            trigger_error('Argument 1 passed to '.__METHOD__.' must be a string, '.gettype($content).' given', E_USER_ERROR);
        if (!is_string($encoding))
            trigger_error('Argument 2 passed to '.__METHOD__.' must be a string, '.gettype($encoding).' given', E_USER_ERROR);
        $this->content = $content;
        $this->encoding = $encoding;
        $this->size = mb_strlen($this->content, $this->encoding);
        $this->close();
        return $this;
    }

   /**
     * Use the class as string
     * @return integer
     */
    public function count()
    {
        return $this->size;
    }

    /**
     *  invoking inaccessible methods in an object context.
     *  @param string
     *  @param array
     */
    public function __call($name, $args)
    {
        if (is_callable($name) and in_array($name, self::$funcs)) {
            array_unshift($args, $this->content);
            $this->content = call_user_func_array($name, $args);
        }
        return $this;
    }

    /**
     * Use the class as string
     * @return string
     */
    public function __toString()
    {
        return (string)$this->content;
    }

    /**
     * Convert class to string
     * @return string
     */
    public function toString()
    {
        return (string)$this->content;
    }

    /**
     * Convert class to string
     * @return string
     */
    public function toInteger()
    {
        return (integer)$this->content;
    }

    /**
     * Convert class to boolean
     * @return string
     */
    public function toBoolean()
    {
        return (boolean)$this->content;
    }

     /**
     * Convert class to Stream
     * @return string
     */
    public function toURL($id = null)
    {
        if (is_null($id) or !is_string($id)) {
            $id = uniqid();
        }
        PSOStream::$handles[$id] = $this;
        return 'pso://'.$id;
    }

    /**
     * isEmpty
     * @return boolean
     */
    public function isEmpty()
    {
        return ($this->content == '');
    }

   
    /**
     * isEqual
     * @return boolean
     */
    public function isEqual($s)
    {
        return ($this->content === $s);
    }

    /**
     * isMatch
     * @return boolean
     */
    public function isMatch($pattern, &$matches = null, $flags = 0 , $offset = 0)
    {
        $m = array();
        $b = preg_match($pattern, $this->content, $m, $flags, $offset);
        if (!is_null($matches)) $matches = $m;
        return (boolean)$b;
    }

    /**
     *  contains
     *  @return boolean
     */
    public function contains($needle, $offset = 0)
    {
        return (mb_strpos($this->content, $needle, $offset, $this->encoding) !== false);
    }

    /**
     *  replace
     *  @return PSO
     */
    public function replace($pattern, $replacement,  $limit = -1 , $count = null)
    {
        $this->content = preg_replace($pattern, $replacement, $this->content, $limit, $count);
        return $this;
    }

    /**
     *  slice
     *  @return PSO
     */
    public function slice($start, $length = null)
    {
        $this->content = mb_substr($this->content, $start, $length, $this->encoding);
        return $this;
    }

    /**
     *  substr
     *  @return new PSO
     */
    public function substr($start, $length = null)
    {
        return new PSO(mb_substr($this->content, $start, $length, $this->encoding), $this->encoding);
    }


    /**
     * Fixe le sépérateur de ligne
     *
     * @return object
     */
    public function setEnding($s)
    {
        if (!is_string($s))
            trigger_error('Argument 1 passed to '.__METHOD__.' must be a string, '.gettype($s).' given', E_USER_ERROR);
        $this->ending = $s;
        return $this;
    }

    /**
     * Retourne une portion de chaine
     *
     * @return object
     */
    public function fetch()
    {
        $s = sizeof($this->ending);
        if ($this->position >= $this->size) {
            $this->close();
            return false;
        }
        $p = mb_strpos($this->content, $this->ending, $this->position, $this->encoding);
        if ($p === false) {
            $start = $this->position;
            $length = $this->size - $this->position;
            $this->position = $this->size;
        }
        else {
            $start = $this->position;
            $length = $p - $this->position;
            $this->position = $p + $s;
        }

        return new PSO(mb_substr($this->content, $start, $length, $this->encoding), $this->encoding);
    }

    /**
     * Retourne toute les lignes du résulat de la requete 
     *
     * @return PAO
     */
    public function fetchAll()
    {
        $ret = new PAO();
        while($row = $this->fetch()) 
            $ret->append($row);
        return $ret;
    }

    /**
     * Ferme le cursor
     *
     * @return PAO
     */
    public function close()
    {
        $this->position = 0;
        return $this;
    }


    /**
     *  concat
     *  @return PSO
     */
    public function concat()
    {
        for($i = 0, $j = func_num_args(); $i < $j; $i++){
            $a = func_get_arg($i);
            if ($a instanceof PSO) {
                $this->content .= $a->toString();
            }
            elseif (is_string($a)) {
                $this->content .= $a;
            }
            else {
                $this->content .= strval($a);
            }
        }
        return $this;
    }

    /**
     * upper
     * @return PSO
     */
    public function upper()
    {
        $this->content = mb_convert_case($this->content, MB_CASE_UPPER, $this->encoding);
        return $this;
    }

    /**
     * lower
     * @return PSO
     */
    public function lower()
    {
        $this->content = mb_convert_case($this->content, MB_CASE_LOWER, $this->encoding);
        return $this;
    }
    /**
     * title
     * @return PSO
     */
    public function title()
    {
        $this->content = mb_convert_case($this->content, MB_CASE_TITLE, $this->encoding);
        return $this;
    }

    /**
     * md5
     * @return PSO
     */
    public function md5()
    {
        $this->content = md5($this->content);
        return $this;
    }

    /**
     * trim
     * @return PSO
     */
    public function trim($charlist = null)
    {
        $this->content = trim($this->content, $charlist);
        return $this;
    }

    /**
     * ltrim
     * @return PSO
     */
    public function ltrim($charlist = null)
    {
        $this->content = ltrim($this->content, $charlist);
        return $this;
    }

    /**
     * rtrim
     * @return PSO
     */
    public function rtrim($charlist = null)
    {
        $this->content = rtrim($this->content, $charlist);
        return $this;
    }

    /**
     * urlencode
     * @return PSO
     */
    public function urlencode()
    {
        $this->content = urlencode($this->content);
        return $this;
    }

    /**
     * urldecode
     * @return PSO
     */
    public function urldecode()
    {
        $this->content = urldecode($this->content);
        return $this;
    }
}
