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
    protected $input = null;
    protected $parameters = null;

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
     * set REST_Parameters
     * @return REST_Url
     */
    public function setParameters(REST_Parameters $p)
    {
        $this->parameters = $p;
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
        if (is_callable($callback)) {
            self::$splitters[$name] = $callback;
            return true;
        }
        return false;
    }

    /**
     * REST_Url factory
     * @param string
     * @return REST_Url
     */
    public static function factory($tpl)
    {
        return new REST_Url($tpl);
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
        if (is_callable($callback) and is_array($params)) {
            $this->callbacks[] = array($method, $callback, $params);
            $this->methods[] = $method;
        }
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
        if (preg_match(',^\w+$,', $name)) {
            $this->constants[$name] = $value;
        }
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
        if (is_null($this->parameters)) {
            $this->parameters = REST_Parameters::factory($this->sections, $this->input);
        }
        else {
            $this->parameters->register($this->sections, $this->input);
        }
        $stream = null;
        if (sizeof($this->callbacks)) {
            foreach($this->callbacks as $binding) {
                if ($binding[0] === $method or $binding[0] === '*') {
                    $ret = true;
                    $this->parameters->exchange($binding[2]);
                    foreach($this->constants as $constant => $value) {
                        $this->parameters->set($constant, $value);
                    }
                    $this->parameters->set('__methods', $this->methods);
                    $stream = call_user_func($binding[1], $this->parameters, $headers, $stream);
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
