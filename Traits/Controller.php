<?php
namespace Bxx\Traits {
    trait Controller
    {

        protected function init()
        {
            parent::init();
            foreach ($this->actionsConfig as $name=>$arConfig) {
                
                if (defined('APPLICATION_ENV') && APPLICATION_ENV == 'dev') {
                    // в режиме разработки всегда добавляем -prefilter с '\Bitrix\Main\Engine\ActionFilter\Csrf'
                    if (!isset($arConfig['-prefilters'])) {
                        $arConfig['-prefilters'] = ['\Bitrix\Main\Engine\ActionFilter\Csrf'];
                    } elseif (!in_array('\Bitrix\Main\Engine\ActionFilter\Csrf', $arConfig['-prefilters'])) {
                        $arConfig['-prefilters'][] = '\Bitrix\Main\Engine\ActionFilter\Csrf';
                    }
                }


                // преобразование строковых классов в объекты
                if ($arConfig['prefilters']) {
                    foreach ($arConfig['prefilters'] as $I=>$FilterClass) {
                        if (is_string($FilterClass)) {
                            $arConfig['prefilters'][$I] = new $FilterClass;
                        }
                    }
                }
                if ($arConfig['postfilters']) {
                    foreach ($arConfig['postfilters'] as $I=>$FilterClass) {
                        if (is_string($FilterClass)) {
                            $arConfig['postfilters'][$I] = new $FilterClass;
                        }
                    }
                }
                $this->setActionConfig($name, $arConfig);
            }
        }
    }
}

