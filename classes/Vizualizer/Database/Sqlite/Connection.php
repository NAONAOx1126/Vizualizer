<?php

/**
 * Copyright (C) 2012 Vizualizer All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author    Naohisa Minagawa <info@vizualizer.jp>
 * @copyright Copyright (c) 2010, Vizualizer
 * @license http://www.apache.org/licenses/LICENSE-2.0.html Apache License, Version 2.0
 * @since PHP 5.3
 * @version   1.0.0
 */

/**
 * SQLiteのコネクションを管理するためのクラスです。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer_Database_Sqlite_Connection implements Vizualizer_Database_Connection
{

    private $connection;

    private $inTransaction;

    /**
     * コンストラクタ
     *
     * @param array $configure
     */
    public function __construct($configure)
    {
        if (substr($configure["file"], 0, 1) != DIRECTORY_SEPARATOR) {
            $configure["file"] = VIZUALIZER_SITE_ROOT . DIRECTORY_SEPARATOR . $configure["file"];
        }
        $this->connection = new SQLite3($configure["file"]);
        $this->inTransaction = false;
    }

    /**
     * デストラクタ
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * テーブルのカラムを取得する。
     *
     * @param string $table テーブル名
     * @return array カラムのリスト
     * @throws Vizualizer_Exception_System
     */
    public function columns($table)
    {
        // テーブルの定義を取得
        if (($result = $this->query("SELECT * FROM sqlite_master WHERE name = '" . $this->unescapeIdentifier($table)."' AND type = 'table'")) === FALSE) {
            throw new Vizualizer_Exception_System("カラムの取得に失敗しました。");
        }
        $columns = array();
        if(($data = $result->fetch()) === null){
            throw new Vizualizer_Exception_System("カラムの取得に失敗しました。");
        }
        $createTable = str_replace("\n", "", $data["sql"]);
        if(preg_match("/^create\\s+table\\s+".$table."\\s+\((.+)\)$/i", $createTable, $params) == 0){
            throw new Vizualizer_Exception_System("カラムの取得に失敗しました。");
        }
        $columnDefinitions = explode(",", trim($params[1]));
        foreach($columnDefinitions as $define){
            if(preg_match("/^`(.+)`\\s+([a-z]+)(\\s+((primary key)|(not null)|(autoincrement)|(default\\s+(.+))))*$/i", trim($define), $params) > 0){
                $column = array("Field" => $params[1], "Type" => $params[2]);
                if(strtolower($params[5]) == "primary key"){
                    $column["Key"] = "PRI";
                }else{
                    $column["Key"] = "";
                }
                // NullとExtraは実効としては使っていないため、空文字を設定
                $column["Null"] = "";
                $column["Extra"] = "";
                $columns[] = $column;
            }
        }
        $result->close();
        return $columns;
    }

    /**
     * テーブルのキーを取得する。
     *
     * @param string $table テーブル名
     * @return array キーのリスト
     */
    public function keys($table)
    {
        $keys = array();
        $columns = $this->columns($table);
        foreach($columns as $column){
            if($column["Key"] == "PRI"){
                $keys[] = $column["Field"];
            }
        }
        return $keys;
    }

    /**
     * テーブルのインデックスを取得する。
     *
     * @param string $table テーブル名
     * @return array インデックスのリスト
     */
    public function indexes($table)
    {
        $indexes = array();
        return $indexes;
    }

    /**
     * トランザクションの開始
     */
    public function begin()
    {
        if(!$this->inTransaction){
            $this->query("BEGIN TRANSACTION");
            $this->inTransaction = true;
        }
    }

    /**
     * トランザクションのコミット
     */
    public function commit()
    {
        if($this->inTransaction){
            $this->query("COMMIT");
            $this->inTransaction = false;
        }
    }

    /**
     * トランザクションのロールバック
     */
    public function rollback()
    {
        if($this->inTransaction){
            $this->query("ROLLBACK");
            $this->inTransaction = false;
        }
    }

    /**
     * 値のエスケープ処理
     *
     * @param string $value エスケープする値
     * @return string エスケープした値
     */
    public function escape($value)
    {
        return $this->connection->escapeString($value);
    }

    /**
     * 識別子のエスケープ処理
     *
     * @param string $value エスケープする識別子
     * @return string エスケープした識別子
     */
    public function escapeIdentifier($identifier)
    {
        return "`" . $identifier . "`";
    }

    /**
     * 識別子のエスケープ処理
     *
     * @param string $value エスケープする識別子
     * @return string エスケープした識別子
     */
    public function unescapeIdentifier($identifier)
    {
        return substr($identifier, 1, -1);
    }

    /**
     * クエリの実行
     *
     * @param string $query 実行するクエリ
     * @return Vizualizer_Database_Result 実行結果
     */
    public function query($query)
    {
        if ($this->connection != null) {
            $result = $this->connection->query($query);
            if ($result === FALSE) {
                return FALSE;
            } elseif ($result !== TRUE) {
                return new Vizualizer_Database_Sqlite_Result($result);
            } else {
                return $this->connection->changes();
            }
        }
        return FALSE;
    }

    /**
     * 最後に挿入した自動採番を取得
     *
     * @return int 最後の自動採番値
     */
    public function lastInsertId()
    {
        return $this->connection->lastInsertRowID();
    }

    /**
     * 接続を閉じる
     */
    public function close()
    {
        if ($this->connection != null) {
            $this->rollback();
            $this->connection->close();
            $this->connection = null;
        }
    }
}
