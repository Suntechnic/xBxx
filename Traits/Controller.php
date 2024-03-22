<?php
namespace Bxx\Traits {
    trait Controller
    {
        // private $actionsConfig;

        protected function init()
        {
            parent::init();
            foreach ($this->actionsConfig as $name=>$arConfig) $this->setActionConfig($name, $arConfig);
        }
    }
}

