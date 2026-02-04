<?php
namespace Bxx\Traits {
    trait Controller
    {
        // private $actionsConfig;

        protected function init()
        {
            parent::init();
            foreach ($this->actionsConfig as $Name=>$refConfig) {
                
                foreach ($refConfig as $NameFilter=>$lstConfig) {
                    if ($NameFilter == 'prefilters') {
                        foreach ($lstConfig as $I=>$Filter) {
                            if (is_string($Filter)) {
                                $refConfig[$NameFilter][$I] = new $Filter;
                            }
                        }
                    }
                }
                
                $this->setActionConfig($Name, $refConfig);
            }
        }
    }
}

