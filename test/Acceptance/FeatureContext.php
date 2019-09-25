<?php
declare(strict_types=1);

namespace Test\Acceptance;

use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
use PHPUnit\Framework\Assert;
use Warehouse\Domain\Model\Product\ProductId;
use Warehouse\Infrastructure\ServiceContainer;

final class FeatureContext implements Context
{
    /**
     * @var ServiceContainer
     */
    private $serviceContainer;

    /**
     * @var ProductId
     */
    private $productId;

    /**
     * @BeforeScenario
     */
    public function setUp()
    {
        $this->serviceContainer = new ServiceContainer();
    }

    /**
     * @When I create a product :description
     * @Given a product :description
     */
    public function iCreateAProduct($description)
    {
        $this->productId = $this->serviceContainer->createProductService()->create($description);
    }

    /**
     * @Then the balance for this product should be :quantityInStock
     */
    public function theBalanceForThisProductShouldBe($quantityInStock)
    {
        $balance = $this->serviceContainer->balanceRepository()->getById($this->productId);

        Assert::assertEquals($quantityInStock, $balance->quantityInStock());
    }

    /**
     * @Given /^I have received (\d+) items of this product$/
     * @When /^I receive (\d+) items of this product$/
     */
    public function iReceiveItemsOfThisProduct(int $quantity)
    {
        $productsAndQuantities = [(string) $this->productId => $quantity];
        $purchaseOrderId = $this->serviceContainer->createPurchaseOrderService()->place($productsAndQuantities);
        $this->serviceContainer->receiveGoods()->receive((string) $purchaseOrderId, $productsAndQuantities);
    }

    /**
     * @When /^I deliver (\d+) items of this product$/
     */
    public function iDeliverItemsOfThisProduct(int $quantity)
    {
        $productsAndQuantities = [(string) $this->productId => $quantity];
        $salesOrderId = $this->serviceContainer->placeSalesOrderService()->place($productsAndQuantities);
        $this->serviceContainer->deliverGoodsService()->deliver((string) $salesOrderId);
    }
}
