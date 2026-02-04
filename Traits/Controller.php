<?php
namespace Bxx\Traits {
    trait Controller
    {

        protected function init()
        {
            parent::init();
            foreach ($this->actionsConfig as $Name=>$arConfig) {
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
    }
}

