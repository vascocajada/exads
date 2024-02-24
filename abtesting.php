<?php

require_once __DIR__ . '/vendor/autoload.php';

use Exads\ABTestData;

class DesignSelector
{
    private $designs;

    public function __construct(array $designs)
    {
        $this->designs = $designs;
    }

    public function selectDesign()
    {
        $rand = mt_rand(1, 100);
        $cumulative = 0;

        foreach ($this->designs as $design) {
            $cumulative += $design['splitPercent'];
            if ($rand <= $cumulative) {
                return $design;
            }
        }

        throw new \Exception('Something went wrong.');
    }
}

class Promotion
{
    private $designSelector;

    public function __construct(array $designs)
    {
        $this->designSelector = new DesignSelector($designs);
    }

    public function redirectToDesign()
    {
        $selectedDesign = $this->designSelector->selectDesign();
        echo "Redirect to: " . $selectedDesign['designName'] . "\n";
    }
}

for($i = 1; $i < 4; $i++) {
    $abTest = new ABTestData($i);
    $designs = $abTest->getAllDesigns();

    $promotion = new Promotion($designs);
    $promotion->redirectToDesign();
}


