<?php declare(strict_types=1);

namespace Diy\Tools;

use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestListenerDefaultImplementation;

class TestVisualisationListener implements TestListener
{
    use TestListenerDefaultImplementation;

    public function endTest(Test $test, $time)
    {
        $this->extractData($test);
    }

    private function extractData(Test $test)
    {
        $testName = get_class($test);
        $testMethod= $test->getName();

        /** @var TestScenario $scenario */
        $scenario = $test->scenario;
        if (is_null($scenario)) {
            return;
        }

        $items = [];

        $givenEvents = $scenario->getGivenEvents();
        foreach ($givenEvents as $givenEvent) {
            $items[] = $this->extractItemData($givenEvent, 'event');
        }

        $whenCommand = $scenario->getWhenCommand();
        $items[] = $this->extractItemData($whenCommand, 'command');

        $thenEvents = $scenario->getThenEvents();
        if (false === $thenEvents) {
            $items[] = [
                'type' => 'event',
                'title' => 'No outcome specified',
                'note' => 'If this is desired change your test to use `thenNothing` to signal explicitly that nothing should happen.',
            ];
        } else {
            foreach ($thenEvents as $thenEvent) {
                $items[] = $this->extractItemData($thenEvent, 'event');
            }
        }

print_r($items);
    }

    /**
     * @param $item
     *
     * @return array
     */
    private function extractItemData($item, $type): array
    {
        $reflectionClass = new \ReflectionClass($item);
        $properties = $reflectionClass->getProperties();

        $currentData = [
            'type' => $type,
            'title' => str_replace('\\', '.', $reflectionClass->getName()),
            'properties' => [],
        ];
        foreach ($properties as $property) {
            $property->setAccessible(true);
            $propertyName = $property->getName();
            $value = $property->getValue($item);

            $currentData['properties'][$propertyName] = $value;
        }
        return $currentData;
    }
}