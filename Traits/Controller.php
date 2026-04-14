<?php
namespace Bxx\Traits {
    trait Controller
    {

        protected function init()
        {
            parent::init();
            foreach ($this->getActionsConfig() as $Name=>$arConfig) {

                // Инициализируем классы фильтров, если они указаны строкой, а не объектом
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
                $this->setActionConfig($Name, $arConfig);
            }
        }


        protected function getActionsConfig()
        {
            return $this->actionsConfig??[];
        }
    }
}

