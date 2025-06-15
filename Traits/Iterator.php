<?php
namespace Bxx\Traits {
    trait Iterator
    {
        private $lstList = []; // Список
        private $IndexList = 0;

        public function key (): int
        {
            return $this->IndexList;
        }

        public function next (): void
        {
            $this->IndexList++;
        }

        public function rewind (): void
        {
            $this->IndexList = 0;
        }

        public function valid (): bool
        {
            return $this->IndexList < count($this->lstList);
        }

        public function count (): int
        {
            return count($this->lstList);
        }

        
        ////////////////////////////////////////////////////////////////////////
        // Методы требующие переопределения при реолизации на справочнике
        public function getByIndex (int $Index)
        {
            return $this->lstList[$Index] ?? null;
        }

        public function getArray (): array 
        {
            return $this->lstList;
        }

        // тольок если элименты перечесления являются словарями
        public function getReference (string $Key): array
        {
            $lst = $this->getArray();
            $ref = array_combine(array_column($lst,$Key),$lst);
            return $ref;
        }
    }
}

/**
 * Классическое использование со справчником по id
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

public function getByIndex (int $Index)
{
    $Id = $this->lstList[$Index];
    if (is_int($Id)) {
        return $this->refById[$Id];
    }
    return null;
}

public function getArray (): array 
{
    $lst = [];
    foreach ($this->lstList as $Id) {
        $lst[] = $this->refById[$Id];
    }
    return $lst;
}

public function getReference (string $Key='ID'): array
{
    if ($Key='ID') {
        return $this->refById;
    }
    $lst = $this->getArray();
    $ref = array_combine(array_column($lst,$Key),$lst);
    return $ref;
}
*/