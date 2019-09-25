<?php
declare(strict_types=1);

namespace Warehouse\Infrastructure;

use Common\EventDispatcher\EventDispatcher;
use Warehouse\Application\CreateProductService;
use Warehouse\Application\DeliverGoodsService;
use Warehouse\Application\PlacePurchaseOrderService;
use Warehouse\Application\PlaceSalesOrderService;
use Warehouse\Application\ReadModel\BalanceRepository;
use Warehouse\Application\ReadModel\UpdateBalance;
use Warehouse\Application\ReceiveGoodsService;
use Warehouse\Domain\Model\DeliveryNote\DeliveryNoteRepository;
use Warehouse\Domain\Model\DeliveryNote\GoodsDelivered;
use Warehouse\Domain\Model\Product\ProductCreated;
use Warehouse\Domain\Model\Product\ProductRepository;
use Warehouse\Domain\Model\ReceiptNote\GoodsReceived;
use Warehouse\Domain\Model\ReceiptNote\ReceiptNoteRepository;
use Warehouse\Domain\Model\SalesOrder\SalesOrderRepository;

final class ServiceContainer
{
    public function createProductService(): CreateProductService
    {
        return new CreateProductService($this->productRepository());
    }

    public function createPurchaseOrderService(): PlacePurchaseOrderService
    {
        return new PlacePurchaseOrderService($this->purchaseOrderRepository());
    }

    public function placeSalesOrderService(): PlaceSalesOrderService
    {
        return new PlaceSalesOrderService($this->salesOrderRepository());
    }

    public function receiveGoods(): ReceiveGoodsService
    {
        return new ReceiveGoodsService(
            $this->purchaseOrderRepository(),
            $this->receiptNoteRepository(),
            $this->productRepository()
        );
    }

    public function deliverGoodsService(): DeliverGoodsService
    {
        return new DeliverGoodsService(
            $this->salesOrderRepository(),
            $this->deliveryNoteRepository(),
            $this->productRepository()
        );
    }

    public function productRepository(): ProductRepository
    {
        static $service;

        return $service ?: $service = new ProductAggregateRepository($this->eventDispatcher());
    }

    public function purchaseOrderRepository(): PurchaseOrderAggregateRepository
    {
        static $service;

        return $service ?: $service = new PurchaseOrderAggregateRepository($this->eventDispatcher());
    }

    public function salesOrderRepository(): SalesOrderRepository
    {
        static $service;

        return $service ?: $service = new SalesOrderAggregateRepository($this->eventDispatcher());
    }

    public function receiptNoteRepository(): ReceiptNoteRepository
    {
        static $service;

        return $service ?: $service = new ReceiptNoteAggregateRepository($this->eventDispatcher());
    }

    public function deliveryNoteRepository(): DeliveryNoteRepository
    {
        static $service;

        return $service ?: $service = new DeliveryNoteAggregateRepository($this->eventDispatcher());
    }

    public function balanceRepository(): BalanceRepository
    {
        static $service;

        return $service ?: $service = new InMemoryBalanceRepository();
    }

    public function updateBalanceListener(): UpdateBalance
    {
        static $service;

        return $service ?: $service = new UpdateBalance($this->balanceRepository());
    }

    private function eventDispatcher(): EventDispatcher
    {
        static $service;

        if ($service === null) {
            $service = new EventDispatcher();

            // Register your event subscribers here:
            $service->registerSubscriber(
                ProductCreated::class,
                [$this->updateBalanceListener(), 'whenProductCreated']
            );
            $service->registerSubscriber(
                GoodsDelivered::class,
                [$this->updateBalanceListener(), 'whenGoodsDelivered']
            );
            $service->registerSubscriber(
                GoodsReceived::class,
                [$this->updateBalanceListener(), 'whenGoodsReceived']
            );

            // For debugging purposes:
            $service->subscribeToAllEvents(
                function ($event) {
                }
            );
        }

        return $service;
    }
}
