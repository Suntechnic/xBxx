<?
namespace Bxx\Abstraction {
    abstract class Service
    {
        /**
         * Возвращает имя сервиса
         *
         * @return string
         */
        public function getServiceName (): string
        {
            $serviceLocator = \Bitrix\Main\DI\ServiceLocator::getInstance();
            $reflection = new \ReflectionClass($serviceLocator);
            // Получаем приватное свойство services
            $servicesProperty = $reflection->getProperty('services');
            $servicesProperty->setAccessible(true);
            $lstServices = $servicesProperty->getValue($serviceLocator);

            foreach ($lstServices as $ServiceName => $ulServiceClass) {
                if (is_a($this,$ulServiceClass[0])) return $ServiceName;
            }

            throw new \Exception("Service name not found for class: " . $this->getServiceClassName());
        }
    }
}