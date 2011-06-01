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
 * @category  PQO
 * @package   PLOR
 * @author    Nicolas Thouvenin <nthouvenin@gmail.com>
 * @copyright 2010 Nicolas Thouvenin
 * @license   http://opensource.org/licenses/bsd-license.php BSD Licence
 */

require_once 'Fetchor.php';
require_once 'Encoding.php';
require_once 'DAT.php';

/**
 * a PDOStatement facade in PHP
 *
 * @category  PQO
 * @package   PLOR
 * @author    Nicolas Thouvenin <nthouvenin@gmail.com>
 * @copyright 2010 Nicolas Thouvenin
 * @license   http://opensource.org/licenses/bsd-license.php BSD Licence
 */

class PQO implements Fetchor, Encoding
{
    protected $__encoding = 'UTF-8';
    protected static $queries = array();

    private $statement;
    private $query;
    private $parameters = array();
    private $executed = false;

    /**
     * Constructor
     * @param PDO
     * @param string 
     * @param string
     */
    public function __construct(PDO $pdo, $query)
    {
        $this->exchange($pdo, $query);
    }

    /**
     * Factory
     * @param PDO
     * @param string 
     * @param string
     * @return PQO
     */
    public static function factory(PDO $pdo, $query)
    {
        return new PSO($pdo, $query);
    }

    /**
     * Exchange
     *
     * @param PDO
     * @param string 
     * @param string
     * @return PQO
     */
    public function exchange(PDO $pdo, $query) 
    {
        if (!is_string($query))
            trigger_error('Argument 1 passed to '.__METHOD__.' must be a string, '.gettype($query).' given', E_USER_ERROR);
        $this->close();
        $this->query = $query;
        $this->statement = $pdo->prepare($query);
        return $this;
    }

    /**
     * set string encoding
     * @param string 
     * @return PQO
     */
    public function fixEncoding($e)
    {
        if (!is_string($e))
            trigger_error('Argument 1 passed to '.__METHOD__.' must be a string, '.gettype($e).' given', E_USER_ERROR);
        $this->__encoding = $e;
        return $this;
    }

    /**
     * A même requete, même instance
     *
     * @param PDO 
     * @param string
     * @return PQO
     */
    static public function singleton(PDO $pdo, $query)
    {
        $qid = md5($query);
        if (!isset(self::$queries[$qid])) {
            self::$queries[$qid] = new PQO($pdo, $query);
        }
        return self::$queries[$qid];
    }

    /**
     * __destruct
     *
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Association de paramètres
     *
     * @see http://www.php.net/manual/fr/pdostatement.bindparam.php
     */
    public function bind($parameter, &$value, $data_type = PDO::PARAM_STR, $length = null)
    {
        $this->statement->bindParam($parameter, $value, $data_type, $length);
        return $this;
    }

    /**
     * Association de paramètres par valeur
     *
     */
    public function bindValue($parameter, $value, $data_type = PDO::PARAM_STR, $length = null)
    {
        $this->statement->bindParam($parameter, $value, $data_type, $length);
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
    public function with($parameter, $data_type = PDO::PARAM_STR, $length = null)
    {
        $this->parameters[$parameter] = null;
        $this->statement->bindParam($parameter, $this->parameters[$parameter], $data_type, $length);
        return $this;
    }

    /**
     * Donne une valeur à un paramètre associé
     *
     * @param mixed $parameter
     * @param mixed $data_value
     * @return PQO
     */
    public function set($parameter, $data_value)
    {
        $this->parameters[$parameter] = $data_value;
        return $this;
    }

    /**
     * Exécute la requète
     *
     * @return PQO
     */
    public function fire()
    {
        if ($this->executed) $this->statement->closeCursor();
        $this->statement->execute();
        $this->executed = true;
        return $this;
    }

    /**
     * Retourne une ligne du résulat de la requete
     *
     * @return object
     */
    public function fetch()
    {
        if (!$this->executed) return false;
        if (!$row = $this->statement->fetch(PDO::FETCH_ASSOC)) {
            $this->close();
            return false;
        }
        $ret = new stdClass;
        foreach($row as $k => $v) 
            $ret->$k = PSO::factory($v)->fixEncoding($this->__encoding);
        return $ret;
    }

    /**
     * Retourne toute les lignes du résulat de la requete 
     *
     * @return DAT
     */
    public function fetchAll()
    {
        if (!$this->executed) return false;
        $ret = new DAT;
        while($row = $this->fetch())
            $ret->append($row);
        return $ret;
    }

    /**
     * Ferme le curseur courant 
     *
     * @return PQO
     */
    public function close()
    {
        if ($this->executed) 
            $this->statement->closeCursor();
        return $this;
    }
}
