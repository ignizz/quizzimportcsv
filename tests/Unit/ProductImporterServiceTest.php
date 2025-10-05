<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\ProductImporterService;

class ProductImporterServiceTest extends TestCase
{
    /**
     * Invoke a non-public method on an object for testing purposes.
     * @created 2025-10-04
     * @author Kareem Lorenzana
     */
    protected function invokeMethod($object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
    /**
     * test if file pass business rules
     * @created 2025-10-05
     * @author Kareem Lorenzana
     */
    public function test_passes_business_rules_accepts_valid_product()
    {
        $service = new ProductImporterService('dummy.csv', true);

        $product = [
            'decCostGBP' => 50.00,
            'intStock' => 12,
        ];

        $result = $this->invokeMethod($service, 'passesBusinessRules', [$product]);

        $this->assertTrue($result);
    }
    /**
     * test if reject correctlty business rules
     * @created 2025-10-05
     * @author Kareem Lorenzana
     */
    public function test_passes_business_rules_rejects_low_cost_and_stock()
    {
        $service = new ProductImporterService('dummy.csv', true);

        $product = [
            'decCostGBP' => 3.00,
            'intStock' => 5,
        ];

        $result = $this->invokeMethod($service, 'passesBusinessRules', [$product]);

        $this->assertFalse($result);
    }

    /**
     * test if cost are correctly normilized
     * @created 2025-10-05
     * @author Kareem Lorenzana
     */
    public function test_normalize_cost_removes_currency_symbols()
    {
        $service = new ProductImporterService('dummy.csv', true);

        $result = $this->invokeMethod($service, 'normalizeCost', ['£45.99']);

        $this->assertEquals(45.99, $result);
    }

    /**
     * test if row are correctrly mapped
     * @created 2025-10-05
     * @author Kareem Lorenzana
     */
    public function test_map_row_to_product_maps_correctly()
    {
        $service = new ProductImporterService('dummy.csv', true);

        $row = ['P001', 'TV', 'Nice TV', '10', '399.99', 'yes'];

        $product = $this->invokeMethod($service, 'mapRowToProduct', [$row]);

        $this->assertEquals('P001', $product['strProductCode']);
        $this->assertEquals('TV', $product['strProductName']);
        $this->assertEquals('Nice TV', $product['strProductDesc']);
        $this->assertEquals(10, $product['intStock']);
        $this->assertEquals(399.99, $product['decCostGBP']);
        $this->assertNotNull($product['dtmDiscontinued']);
    }

}
