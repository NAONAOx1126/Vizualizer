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
 * 入力チェックエラー用の例外クラスです。
 * システム上でエラーメッセージをリスト化して保持することができ、
 * モジュール内で処理されなかった場合は、次のモジュールに引き継いで処理させることができます。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer_Exception_Invalid extends Vizualizer_Exception_System
{

    /**
     * 入力のエラーメッセージリスト
     */
    private $errors;

    /**
     * コンストラクタ
     *
     * @param $errors 入力エラーのメッセージリスト
     * @param $code この例外のエラーコード
     */
    public function __construct($key, $message)
    {
        if(is_array($message)){
            foreach($message as $index => $value){
                $this->addError($key, $value);
            }
        }else{
            $this->addError($key, $message);
        }
        parent::__construct(implode("\r\n", $this->errors));
    }

    /**
     * 入力エラーを追加する。
     *
     * @param string $key
     * @param string $message
     */
    public function addError($key, $message)
    {
        if (!is_array($this->errors)) {
            $this->errors = array();
        }
        $this->errors[$key] = $message;
    }

    /**
     * 入力のエラーメッセージのリストを取得する。
     *
     * @return 入力エラーのメッセージリスト
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
