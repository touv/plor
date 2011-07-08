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
require_once 'Dumpable.php';
require_once 'Encoding.php';

require_once 'PSOVector.php';
require_once 'PSOStream.php';


function to_string(&$v)
{
    if (is_string($v))
        return true;
    if (is_object($v) and method_exists($v,'__toString')) {
        $v = strval($v);
        return true;
    }
    return false;
}


/**
 * a string facade in PHP
 *
 * @category  PSO
 * @package   PLOR
 * @author    Nicolas Thouvenin <nthouvenin@gmail.com>
 * @copyright 2010 Nicolas Thouvenin
 * @license   http://opensource.org/licenses/bsd-license.php BSD Licence
 */
class PSO implements Countable, Fetchor, Dumpable, Encoding
{
    protected $__encoding = 'UTF-8';

    static public $funcs = array('ord');

    protected $ending = "\n";

    protected $content;
    protected $size;
    protected $position = 0;
    protected $nparameters = array();
    protected $kparameters = array();
 
    const PARAM_BOOL = PDO::PARAM_BOOL;
    const PARAM_NULL = PDO::PARAM_NULL;
    const PARAM_INT  = PDO::PARAM_INT;
    const PARAM_STR  = PDO::PARAM_STR;

    /**
     * Constructor
     * @param string 
     * @param string
     */
    public function __construct($content = '')
    {
        $this->exchange($content);
    }

    /**
     * Factory
     * @param string 
     * @param string
     * @return PSO
     */
    public static function factory($content = '')
    {
        return new PSO($content);
    }

    /**
     * Exchange
     *
     * @param string 
     * @param string
     * @return PSO
     */
    public function exchange($content = '') 
    {
        if (is_null($content)) $content = ''; // Pas de valeur null
        if (!to_string($content))
            throw new ErrorException('Argument 1 passed to '.__METHOD__.' must be a string, '.gettype($content).' given', E_USER_ERROR);
        $this->content = $content;
        $this->size = mb_strlen($this->content);
        $this->close();
        return $this;
    }

    /**
     * set string encoding
     * @param string
     * @return PSO
     */
    public function fixEncoding($e)
    {
        if (!to_string($e))
            throw new ErrorException('Argument 1 passed to '.__METHOD__.' must be a string, '.gettype($e).' given', E_USER_ERROR);
        $this->__encoding = $e;
        mb_internal_encoding($this->__encoding);
        $this->size = mb_strlen($this->content, $e);
        return $this;
    }


   /**
     * define by Countable interface
     * @return integer
     */
    public function count()
    {
        return $this->size;
    }

    /**
     * Clone 
     * @return PSO
     */
    public function duplicate()
    {
        return PSO::factory($this->content)->fixEncoding($this->__encoding);
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
     * Dump content of the class
     * @return PSO
     */
    public function dump($s = null)
    {
        echo $this->toString(), $s;
        return $this;
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
        if (is_null($id) or !to_string($id)) {
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
        return (mb_strpos($this->content, $needle, $offset, $this->__encoding) !== false);
    }

    /**
     *  replace
     *  @see http://fr.php.net/manual/fr/function.mb-ereg-replace.php
     *  @return PSO
     */
    public function replace($pattern, $replacement,  $option = "msr")
    {
        $this->content = mb_ereg_replace($pattern, $replacement, $this->content, $option);
        return $this;
    }

    /**
     *  slice
     *  @return PSO
     */
    public function slice($start, $length = null)
    {
        $this->content = mb_substr($this->content, $start, $length, $this->__encoding);
        return $this;
    }

    /**
     * Fixe le sépérateur de ligne
     *
     * @return object
     */
    public function setEnding($s)
    {
        if (!to_string($s))
            throw new ErrorException('Argument 1 passed to '.__METHOD__.' must be a string, '.gettype($s).' given', E_USER_ERROR);
        $this->ending = $s;
        return $this;
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
        $s = sizeof($this->ending);
        if ($this->position >= $this->size) {
            $this->close();
            return false;
        }
        $p = mb_strpos($this->content, $this->ending, $this->position, $this->__encoding);
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

        return PSO::factory(mb_substr($this->content, $start, $length, $this->__encoding))->fixEncoding($this->__encoding);
    }

    /**
     * Retourne toute les lignes du résulat de la requete 
     *
     * @return PSOVector
     */
    public function fetchAll()
    {
        $ret = new PSOVector();
        while($row = $this->fetch()) 
            $ret->append($row);
        return $ret;
    }

    /**
     * Ferme le cursor
     *
     * @return PSO
     */
    public function close()
    {
        $this->position = 0;
        return $this;
    }

    /**
     * Association de paramètres
     *
     * @see 
     */
    public function bind($parameter, &$value, $data_type = PSO::PARAM_STR, $length = null)
    {
        if (to_string($parameter) and !preg_match(',:\w+,', $parameter))
            throw new ErrorException('Argument 1 passed to '.__METHOD__.' must be a valid string', E_USER_ERROR);

        if (!is_null($length) and !is_integer($length))
            throw new ErrorException('Argument 4 passed to '.__METHOD__.' must be a integer, '.gettype($length).' given', E_USER_ERROR);

        if (is_integer($parameter)) 
            $this->nparameters[$parameter] = array(&$value, $data_type, $length);
        else 
            $this->kparameters[$parameter] = array(&$value, $data_type, $length);
        return $this;
    }

    /**
     * Association de paramètres par valeur
     *
     */
    public function bindValue($parameter, $value, $data_type = PSO::PARAM_STR, $length = null)
    {
        if (to_string($parameter) and !preg_match(',:\w+,', $parameter))
            throw new ErrorException('Argument 1 passed to '.__METHOD__.' must be a valid string', E_USER_ERROR);

        if (!is_null($length) and !is_integer($length))
            throw new ErrorException('Argument 4 passed to '.__METHOD__.' must be a integer, '.gettype($length).' given', E_USER_ERROR);

        if (is_integer($parameter)) 
            $this->nparameters[$parameter] = array($value, $data_type, $length);
        else 
            $this->kparameters[$parameter] = array($value, $data_type, $length);
        return $this;
    }

    /**
     * Déclaration d'une association de paramètres
     *
     * @param mixed $parameter
     * @param int $data_type
     * @param int $length
     * @return PQO
     */
    public function with($parameter, $data_type = PSO::PARAM_STR, $length = null)
    {
        return $this->bindValue($parameter, '?', $data_type, $length);
    }

    /**
     * Donne une valeur à un paramètre associé
     *
     * @param mixed $parameter
     * @param mixed $data_value
     * @return PQO
     */
    public function set($parameter, $value)
    {
        if (is_integer($parameter) and isset($this->nparameters[$parameter])) { 
            $this->nparameters[$parameter][0] = $value;
        }
        elseif (isset($this->kparameters[$parameter])) {
            $this->kparameters[$parameter][0] = $value;
        }
        else {
            throw new ErrorException('Argument 1 passed to '.__METHOD__.' must be a key of known parameter', E_USER_ERROR);
        }

        return $this;
    }

    protected function _par2val(&$value, $data_type, $length)
    {
        if ($data_type === PSO::PARAM_INT or $data_type === PSO::PARAM_BOOL) {
            if ($value instanceof PSO) settype($value, 'string');
            settype($value, 'integer');
        }
        else {
            settype($value, 'string');
        }
        $value = is_null($length) ? $value : substr($value, 0, $length);
    }

    /**
     * Exécute la requète
     *
     * @return PQO
     */
    public function fire()
    {
        if (sizeof($this->nparameters)) {
            $r = PSO::factory()->fixEncoding($this->__encoding);
            $segments = mb_split('(?<=[\s\w])\?', $this->content);
            foreach($segments as $k => $segment) {
                $r->concat($segment);
                if (isset($this->nparameters[$k+1]) and !is_null($this->nparameters[$k+1])) {
                    $this->_par2val($this->nparameters[$k+1][0], $this->nparameters[$k+1][1], $this->nparameters[$k+1][2]);
                    $r->concat($this->nparameters[$k+1][0]);
                    $this->nparameters[$k+1] = null;
                }
            }
            $r->replace(preg_quote('\?'), '?', 'm');
            $this->nparameters = array();
        }
        else {
            $r = $this->duplicate();
        }
        if (sizeof($this->kparameters)) {
            foreach($this->kparameters as $k => $v) if (!is_null($v)) {
                $this->_par2val($this->kparameters[$k][0], $this->kparameters[$k][1], $this->kparameters[$k][2]);
                $r->replace(preg_quote($k).'(?!\w)', $this->kparameters[$k][0], 'm');
                $this->kparameters[$k] = null;
            }
            $this->kparameters = array();
        }
        return $r;
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
            elseif (to_string($a)) {
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
        $this->content = mb_convert_case($this->content, MB_CASE_UPPER, $this->__encoding);
        return $this;
    }

    /**
     * lower
     * @return PSO
     */
    public function lower()
    {
        $this->content = mb_convert_case($this->content, MB_CASE_LOWER, $this->__encoding);
        return $this;
    }
    /**
     * title
     * @return PSO
     */
    public function title()
    {
        $this->content = mb_convert_case($this->content, MB_CASE_TITLE, $this->__encoding);
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
     * pad
     * @return PSO
     */
    public function pad($length, $string = " ")
    {
        $this->size = mb_strlen($this->content, $this->__encoding);
        $ds = $length - $this->size;
        if ($ds <= 0) return $this;
        $rs = $ds / 2;
        $ls = $ds - $rs;

        for($lc = '', $i = 1; $i <= $ls; $i++)
            $lc .= $string;
        for($rc = '', $i = 1; $i <= $rs; $i++)
            $rc .= $string;

        $this->content = $lc . $this->content . $rc;
        return $this;
    }

    /**
     * lpad
     * @return PSO
     */
    public function lpad($length, $string = " ")
    {
        $this->size = mb_strlen($this->content, $this->__encoding);
        $ds = $length - $this->size;
        if ($ds <= 0) return $this;
        for($c = '', $i = 1; $i <= $ds; $i++)
            $c .= $string;

        $this->content = $c . $this->content ;
        return $this;
    }

    /**
     * rpad
     * @return PSO
     */
    public function rpad($length, $string = " ")
    {
        $this->size = mb_strlen($this->content, $this->__encoding);
        $ds = $length - $this->size;
        if ($ds <= 0) return $this;
        for($c = '', $i = 1; $i <= $ds; $i++)
            $c .= $string;

        $this->content .= $c;
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
