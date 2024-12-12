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

/**
 * Классическое использование
use \Bxx\Traits\Iterator;

public function __construct ()
{
    $lst = self::getList();
    $this->lstList = array_column($lst,'ID'); // список id
    $this->refById = array_combine($this->lstList,$lst); // справончик по индентификаторам
}

private $refById = [];
public function current() 
{
    $Id = $this->lstList[$this->IndexList];
    return $this->refById[$Id];
}
*/