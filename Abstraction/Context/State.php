<?
namespace Bxx\Abstraction\Context {

    abstract class State {

        /**
         * название соятояния
         */
        abstract public function getTitle (): string;

        /**
         * возвращает список возможных вариантов состояния в виде массива
        [
                [
                        'name' => Кодовое имя возможного состояния
                        'title' => ЧП название варианта состояния
                    ]
            ]
         *  
         */
        abstract public function getList (): array;


        /**
         * возвращает имя/код текущего варианта состояния
         *  
         */
        abstract public function get (): string;

        /**
         * возвращает какие либо данные состояния по имени
         * если имя не указано, то данные текущего состояния
         * 
         */
        public function getData (string $Name=''): array
        {
            return [];
        }

    }
}
