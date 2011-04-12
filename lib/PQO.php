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
 * @category  PQO
 * @package   P3C
 * @author    Nicolas Thouvenin <nthouvenin@gmail.com>
 * @copyright 2010 Nicolas Thouvenin
 * @license   http://opensource.org/licenses/bsd-license.php BSD Licence
 */

/**
 * a PDOStatement facade in PHP
 *
 * @category  PQO
 * @package   P3C
 * @author    Nicolas Thouvenin <nthouvenin@gmail.com>
 * @copyright 2010 Nicolas Thouvenin
 * @license   http://opensource.org/licenses/bsd-license.php BSD Licence
 */

class PQO
{
    protected static $queries = array();

    private $statement;
    private $qid;
    private $query;
    private $parameters = array();
    private $executed = false;

     // {{{ factory
    /**
     * Un chaque requete, une nouvelle instance
     *
     * @param PDO $pdo
     * @param string query
     * @return PQO
     */
    static public function factory($pdo, $query)
    {
        return new PQO($pdo, $query);
    }
    // }}}

    // {{{ singleton
    /**
     * A même requete, même instance
     *
     * @param PDO $pdo
     * @param string query
     * @return PQO
     */
    static public function singleton($pdo, $query)
    {
        $qid = crc32($query);
        if (!isset(self::$queries[$qid])) {
            self::$queries[$qid] = new PQO($pdo, $query, $qid);
        }
        return self::$queries[$qid];
    }
    // }}}

     // {{{ __construct
    /**
     * Une requete une classe
     *
     * @param PDO $pdo
     * @param string query
     * @param string qid
     */
    public function __construct(PDO $pdo, $query, $qid = null)
    {
        $this->qid       = is_null($qid) ? md5($query) : $qid;
        $this->query     = $query;
        $this->statement = $pdo->prepare($query);
    }
    // }}}

     // {{{ __destruct
    /**
     * Une requete une classe
     *
     * @param PDO $pdo
     * @param string query
     * @param string qid
     */
    public function __destruct()
    {
        $this->close();
    }
    // }}}

     // {{{ bind
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
    // }}}

     // {{{ bindValue
    /**
     * Association de paramètres par valeur
     *
     */
    public function bindValue($parameter, $value, $data_type = PDO::PARAM_STR, $length = null)
    {
        $this->statement->bindParam($parameter, $value, $data_type, $length);
        return $this;
    }
    // }}}

    // {{{ with
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
    // }}}

    // {{{ with
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
    // }}}

    // {{{ fire
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
    // }}}

    // {{{ fire
    /**
     * Retourne une ligne du résulat de la requete
     *
     * @return object
     */
    public function fetch()
    {
        if (!$this->executed) return false;
        $ret = $this->statement->fetch(PDO::FETCH_OBJ);
        if (!$ret) $this->close();
        return $ret;
    }
    // }}}

    // {{{ fetchAll
    /**
     * Retourne toute les lignes du résulat de la requete 
     *
     * @return ArrayObject
     */
    public function fetchAll()
    {
        if (!$this->executed) return false;
        $ret = new ArrayObject();
        while($row = $this->fetch())
            $ret->append($row);
        return $ret;
    }
    // }}}

    // {{{ close
    /**
     * Ferme le curseur courant 
     *
     * @return PQO
     */
    private function close()
    {
        if ($this->executed) 
            $this->statement->closeCursor();
        return $this;
    }
    // }}}
}
