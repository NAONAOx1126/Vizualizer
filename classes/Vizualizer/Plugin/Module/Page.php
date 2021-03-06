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
 * ページング一覧表示用のモジュールです。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
abstract class Vizualizer_Plugin_Module_Page extends Vizualizer_Plugin_Module
{

    private $condition = array();

    private $countColumn = "";

    private $groupBy = "";

    protected function addCondition($key, $value)
    {
        $this->condition[$key] = $value;
    }

    protected function setCountColumn($countColumn)
    {
        $this->countColumn = $countColumn;
    }

    protected function setGroupBy($groupBy)
    {
        $this->groupBy = $groupBy;
    }

    protected function executeImpl($params, $type, $name, $result, $defaultSortKey = "create_time")
    {
        $post = Vizualizer::request();
        if (!$params->check("search") || isset($post[$params->get("search")])) {
            $loader = new Vizualizer_Plugin($type);

            // ページャの初期化
            $pagerMode = $params->get("_pager_mode", Vizualizer_Pager::PAGE_SLIDE);
            $pagerDisplay = $params->get("_pager_dispmode", Vizualizer_Pager::DISPLAY_ATTR);
            if ($params->check("_pager_per_page_key") && $post[$params->get("_pager_per_page_key")] > 0) {
                $pagerCount = $post[$params->get("_pager_per_page_key")];
            } else {
                $pagerCount = $params->get("_pager_per_page", 20);
            }
            if ($params->check("_pager_displays_key")) {
                $pagerNumbers = $post[$params->get("_pager_displays_key")];
            } else {
                $pagerNumbers = $params->get("_pager_displays", 3);
            }
            $pager = new Vizualizer_Pager($pagerMode, $pagerDisplay, $pagerCount, $pagerNumbers);
            $pager->importTemplates($params);

            // カテゴリが選択された場合、カテゴリの商品IDのリストを使う
            $conditions = $this->condition;
            if (is_array($post["search"])) {
                foreach ($post["search"] as $key => $value) {
                    if (!$this->isEmpty($value)) {
                        $conditions[$key] = $value;
                    }
                }
            }

            // 追加の検索条件があれば設定
            if ($params->check("wkey")) {
                $conditions[$params->check("wkey")] = $params->check("wvalue");
            }
            if ($params->check("wkey2")) {
                $conditions[$params->check("wkey2")] = $params->check("wvalue2");
            }
            if ($params->check("wkey3")) {
                $conditions[$params->check("wkey3")] = $params->check("wvalue3");
            }
            if ($params->check("wkey4")) {
                $conditions[$params->check("wkey4")] = $params->check("wvalue4");
            }
            if ($params->check("wkey5")) {
                $conditions[$params->check("wkey5")] = $params->check("wvalue5");
            }
            if ($params->check("wkey6")) {
                $conditions[$params->check("wkey6")] = $params->check("wvalue6");
            }
            if ($params->check("wkey7")) {
                $conditions[$params->check("wkey7")] = $params->check("wvalue7");
            }
            if ($params->check("wkey8")) {
                $conditions[$params->check("wkey8")] = $params->check("wvalue8");
            }
            if ($params->check("wkey9")) {
                $conditions[$params->check("wkey9")] = $params->check("wvalue9");
            }

            // 並べ替え順序が指定されている場合に適用
            $sortOrder = "";
            $sortReverse = false;
            if ($params->check("sort_key")) {
                $sortOrder = $post[$params->get("sort_key")];
                if ($this->isEmpty($sortOrder)) {
                    $sortOrder = $defaultSortKey;
                    $sortReverse = true;
                } elseif (strpos($sortOrder, "rev@") === 0) {
                    list ($dummy, $sortOrder) = explode("@", $sortOrder);
                    $sortReverse = true;
                }
            } elseif ($params->check("sort")) {
                $sortOrder = $params->check("sort");
                if (strpos($sortOrder, "rev@") === 0) {
                    list ($dummy, $sortOrder) = explode("@", $sortOrder);
                    $sortReverse = true;
                }
            }
            $model = $loader->LoadModel($name);
            if ($params->get("force_operator", "0") == "1") {
                $model->setIgnoreOperator(true);
            }

            // 顧客データを検索する。
            if (!$this->isEmpty($this->countColumn)) {
                $pager->setDataSize($model->countBy($conditions, $this->countColumn));
            } else {
                $pager->setDataSize($model->countBy($conditions));
            }
            if ($this->groupBy) {
                $model->setGroupBy($this->groupBy);
            }
            $model->limit($pager->getPageSize(), $pager->getCurrentFirstOffset());
            $models = $model->findAllBy($conditions, $sortOrder, $sortReverse);

            $attr = Vizualizer::attr();
            $attr[$result . "_pager"] = $pager;
            $attr[$result] = $models;
        } elseif (!$params->check("reset") || isset($post[$params->get("reset")])) {
            $post["search"] = array();
            unset($post[$params->get("reset")]);
        }
    }
}
