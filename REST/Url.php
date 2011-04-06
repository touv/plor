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
require_once 'REST/Parameters.php';

/**
 * A REST Url
 *
 * @category  REST
 * @package   REST_Server
 * @author    Nicolas Thouvenin <nthouvenin@gmail.com>
 * @copyright 2010 Nicolas Thouvenin
 * @license   http://opensource.org/licenses/bsd-license.php BSD Licence
 */
class REST_Url
{
    static $splitters = array();
    protected $rules;
    protected $callbacks = array();
    protected $sections = array();
    protected $constants = array();
    protected $methods = array();
    protected $input;
    protected $hook_parameters;

    public function __construct($tpl)
    {
        $this->rules = self::compile($tpl);
    }

    public function __destruct()
    {
    }

    /**
     * set REST_Request
     * @return REST_Url
     */
    public function setInput(REST_Input $r)
    {
        $this->input = $r;
        return $this;
    }

    /**
     * Register a template splitter
     * @params string
     * @params callbacks
     * @static
     * @return boolean
     */
    public static function registerSplitter($name, $callback)
    {
        if (!is_string($name))
            trigger_error('Argument 1 passed to '.__METHOD__.' must be a string, '.gettype($name).' given', E_USER_ERROR);
        if (!is_callable($callback)) 
            trigger_error('Argument 2 passed to '.__METHOD__.' must be callable, '.(string)$callback.' given', E_USER_ERROR);
        self::$splitters[$name] = $callback;
        return true;
    }

    /**
     * REST_Url factory
     * @param string
     * @return REST_Url
     */
    public static function factory($name)
    {
        if (!is_string($name))
            trigger_error('Argument 1 passed to '.__METHOD__.' must be a string, '.gettype($name).' given', E_USER_ERROR);
        return new REST_Url($name);
    }

    /**
     * Link parameter with a callback
     * @param string
     * @param callback
     * @param array
     * @return REST_Url
     */
    public function bindParameter($paraname, $callback)
    {
        if (!is_string($paraname))
            trigger_error('Argument 1 passed to '.__METHOD__.' must be a string, '.gettype($paraname).' given', E_USER_ERROR);
        if (!is_callable($callback)) 
            trigger_error('Argument 2 passed to '.__METHOD__.' must be callable, '.(string)$callback.' given', E_USER_ERROR);
        $this->callbacks[] = array($paraname, $callback);
        return $this;
    }

    /**
     * Link method with a callback
     * @param string
     * @param callback
     * @param array
     * @return REST_Url
     */
    public function bindMethod($method, $callback, $params = array())
    {
        if (!is_string($method))
            trigger_error('Argument 1 passed to '.__METHOD__.' must be a string, '.gettype($method).' given', E_USER_ERROR);
        if (!is_callable($callback)) 
            trigger_error('Argument 2 passed to '.__METHOD__.' must be callable, '.(string)$callback.' given', E_USER_ERROR);
        if (!is_array($params)) 
            trigger_error('Argument 3 passed to '.__METHOD__.' must be a array, '.gettype($params).' given', E_USER_ERROR);

        $this->callbacks[] = array($method, $callback, $params);
        $this->methods[] = $method;
        return $this;
    }

    /**
     * Add a parameter like a constant
     * @param string
     * @param mixed
     * @return REST_Url
     */
    public function addConstant($name, $value)
    {
        if (!is_string($name))
            trigger_error('Argument 1 passed to '.__METHOD__.' must be a string, '.gettype($name).' given', E_USER_ERROR);
        if (!preg_match(',^\w+$,', $name)) 
            trigger_error('Argument 1 passed to '.__METHOD__.' must be a valid string, '.$name.' given', E_USER_ERROR);
        $this->constants[$name] = $value;
        return $this;
    }



    /**
     * Check if url match with the current context
     *
     * @return boolean
     */
    public function check()
    {
        if (is_null($this->input)) return false;
        $path = $this->input->path();
        $this->sections = array();
        foreach($this->rules as $rule) {
            $catch = false;
            $section = new REST_Section;
            if ($rule[0] === '{') {
                $name = trim($rule, '{}');
                if (!isset(self::$splitters[$name])) return false;
                $path = call_user_func(self::$splitters[$name], $path, $section);
                $catch = true;
            }
            elseif ($rule[0] === '(') {
                if (!preg_match(','.$rule.',', $path, $m)) return false;
                $section->set($m[1]);
                $catch = true;
            }
            elseif (strpos($path, $rule) === 0) {
                $section->set($rule);
            }
            else return false;
            if ($section->isEmpty()) return false;
            $path = substr($path, $section->length());
            if ($catch) {
                $this->sections[] = $section;
            }
        }
        return true;
    }

    /**
     * Applique l'URL sur le contexte courant
     *
     * @return boolean
     */
    public function apply(REST_Headers $headers)
    {
        if (is_null($this->input)) return false;
        $ret = false;
        $method = $this->input->method();
        $parameters = REST_Parameters::singleton();
        foreach($this->constants as $constant => $value) {
            $parameters->set($constant, $value);
        }
        $parameters->set('__sections',  new ArrayObject($this->sections));
        $parameters->set('__server',  $this->input);
        $parameters->set('__method',  $this->input->method());
        $parameters->set('__methods', $this->methods);

        $stream = null;
        if (sizeof($this->callbacks)) {
            foreach($this->callbacks as $binding) {
                if (isset($binding[2]) and ($binding[0] === $method or $binding[0] === '*')) {
                    $ret = true;
                    $parameters->exchange($binding[2]);
                    $stream = call_user_func($binding[1], $parameters, $headers, $stream);
                }
                elseif (!isset($binding[2]) and (isset($parameters->$binding[0]) or $binding[0] === '*')) {
                    $stream = call_user_func($binding[1], $parameters, $headers, $stream);
                }
            }
        }
        return $ret;
    }

    /**
     * Transform template in sections
     *
     * @param string
     * @return array
     */
    protected static function compile($str)
    {
        $rules = array();
        $index = 0;
        $acco = 0;
        $brack = 0;
        $tpl = trim($str);
        $len = strlen($tpl);
        for($i = 0; $i < $len; $i++) { 
            $step = 0;
            $chr = $tpl[$i];
            if ($chr === '{' and $acco === 0) {
                ++$acco;
                ++$index;
            }
            elseif ($chr === '{' and $acco !== 0) {
                return array(a);
            }
            elseif ($chr === '}' and $acco === 0) {
                return array(aa);
            }
            elseif ($chr === '}' and $acco !== 0) {
                --$acco;
                ++$step;
            }
            elseif ($chr === '(' and $brack === 0) {
                ++$brack;
                ++$index;
            }
            elseif ($chr === '(' and $brack !== 0) {
                return array(b);
            }
            elseif ($chr === ')' and $brack === 0) {
                return array(bb);
            }
            elseif ($chr === ')' and $brack !== 0) {
                --$brack;
                ++$step;
            }
            elseif ($brack === 0 and $acco === 0) {
//                ++$index;
            }
            if (!isset($rules[$index]))  {
                $rules[$index] = '';
            }
            $rules[$index] .= $chr;
            $index += $step;
        }
        return $rules;
    }

}
