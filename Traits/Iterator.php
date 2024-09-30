<?php
namespace Bxx\Traits {
    trait Iterator
    {
        private $lstList = []; // Список
        private $IndexList = 0;

        public function key () {
            return $this->IndexList;
        }

        public function next () {
            $this->IndexList++;
        }

        public function rewind () {
            $this->IndexList = 0;
        }

        public function valid () {
            return $this->IndexList < count($this->lstList);
        }

        public function getArray (): array 
        {
            return $this->lstList;
        }

        public function getReference (string $Key): array
        {
            $lst = $this->getArray();
            $ref = array_combine(array_column($lst,$Key),$lst);
            return $ref;
        }
    }
}