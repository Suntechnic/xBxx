<?php
namespace Bxx\Traits {
    trait Iterator
    {
        private $lstList = []; // Список
        private $IndexList = 0;

        public function key() {
            return $this->IndexList;
        }

        public function next() {
            $this->IndexList++;
        }

        public function rewind() {
            $this->IndexList = 0;
        }

        public function valid() {
            return $this->IndexList < count($this->lstList);
        }
    }
}