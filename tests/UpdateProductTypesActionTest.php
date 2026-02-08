<?php

namespace Ksfraser\FA_ProductAttributes_Variations\Test\Actions;

use PHPUnit\Framework\TestCase;
use Ksfraser\FA_ProductAttributes\Dao\ProductAttributesDao;
use Ksfraser\FA_ProductAttributes_Variations\Actions\UpdateProductTypesAction;

class UpdateProductTypesActionTest extends TestCase
{
    /** @var ProductAttributesDao|\PHPUnit\Framework\MockObject\MockObject */
    private $dao;

    /** @var UpdateProductTypesAction */
    private $action;

    protected function setUp(): void
    {
        $this->dao = $this->getMockBuilder(ProductAttributesDao::class)->disableOriginalConstructor()->onlyMethods(['getAllProducts', 'listCategoryAssignments'])->addMethods(['getProductParent', 'clearParentRelationship', 'setParentRelationship'])->getMock();
        $this->action = new UpdateProductTypesAction($this->dao);
    }

    public function testHandleWithValidData()
    {
        $postData = [
            'product_types' => [
                'PROD001' => 'variable',
                'PROD002' => 'simple',
                'PROD003' => 'variation'
            ],
            'parent_products' => [
                'PROD003' => 'PROD001'
            ]
        ];

        // Mock current product types
        $this->dao->expects($this->exactly(5))
            ->method('listCategoryAssignments')
            ->willReturnOnConsecutiveCalls(
                [], // PROD001: no categories (simple -> variable)
                [['id' => 1]], // PROD002: has categories (variable -> simple)
                [], // PROD003: no categories (simple -> variation)
                [], // PROD003: called again in clearProductAssignments
                [] // PROD003: called again? 
            );

        // Mock current product types - getProductParent called for products without categories
        $this->dao->expects($this->exactly(3))
            ->method('getProductParent')
            ->willReturnOnConsecutiveCalls(null, null, ['stock_id' => 'OLD_PARENT', 'description' => 'Old Parent']); // PROD001, PROD003, and PROD003 again for parent change check

        // Expect relationship management calls
        $this->dao->expects($this->exactly(2))
            ->method('clearParentRelationship')
            ->withConsecutive(['PROD001'], ['PROD002']); // PROD001: simple->variable, PROD002: variable->simple

        $this->dao->expects($this->once())
            ->method('setParentRelationship')
            ->with('PROD003', 'PROD001'); // Setting parent for variation

        $result = $this->action->handle($postData);

        $this->assertStringContainsString('Updated product types for 3 products', $result);
    }

    public function testHandleWithNoChanges()
    {
        $postData = [
            'product_types' => [
                'PROD001' => 'simple',
                'PROD002' => 'variable'
            ]
        ];

        // Mock current product types matching the requested types
        $this->dao->expects($this->exactly(2))
            ->method('listCategoryAssignments')
            ->willReturnOnConsecutiveCalls(
                [], // PROD001: simple (no change)
                [['id' => 1]] // PROD002: variable (no change)
            );

        $result = $this->action->handle($postData);

        $this->assertStringContainsString('Updated product types for 0 products', $result);
    }

    public function testHandleWithEmptyData()
    {
        $postData = [];

        $result = $this->action->handle($postData);

        $this->assertStringContainsString('Updated product types for 0 products', $result);
    }

    public function testHandleVariationWithoutParent()
    {
        $postData = [
            'product_types' => [
                'PROD001' => 'variation'
            ],
            'parent_products' => [
                'PROD001' => '' // Empty parent
            ]
        ];

        $this->dao->expects($this->once())
            ->method('listCategoryAssignments')
            ->with('PROD001')
            ->willReturn([]);

        $this->dao->expects($this->exactly(2))
            ->method('getProductParent')
            ->with('PROD001')
            ->willReturn(null);

        // Should not call setParentRelationship with empty parent
        $this->dao->expects($this->never())
            ->method('setParentRelationship');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Parent product is required for variation type');

        $this->action->handle($postData);
    }

    public function testHandleConvertingToVariationWithExistingParent()
    {
        $postData = [
            'product_types' => [
                'PROD001' => 'variation'
            ],
            'parent_products' => [
                'PROD001' => 'PARENT001'
            ]
        ];

        $this->dao->expects($this->exactly(2))
            ->method('listCategoryAssignments')
            ->with('PROD001')
            ->willReturn([]);

        $this->dao->expects($this->exactly(2))
            ->method('getProductParent')
            ->with('PROD001')
            ->willReturn(['stock_id' => 'OLD_PARENT', 'description' => 'Old Parent']);

        // Should clear old relationship and set new one
        $this->dao->expects($this->once())
            ->method('setParentRelationship')
            ->with('PROD001', 'PARENT001');

        $result = $this->action->handle($postData);

        $this->assertStringContainsString('Updated product types for 1 products', $result);
    }
}