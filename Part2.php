<?php

interface ItemInterface
{
    public function getTierPrice();

    public function setTierPrice(array $tieredPrice);

    public function getPrice();

    public function setPrice($price);
}

interface TerminalInterface
{
    public function getTotal();

    public function scan(AbstractItem $item);
}


abstract class AbstractItem implements ItemInterface
{
    protected $price;
    protected $tieredPrice;
    protected $entity_id;


    public function getPrice()
    {
        return $this->price;
    }

    public function setPrice($price)
    {
        $this->price = $price;
    }

    public function getTierPrice()
    {
        return $this->tieredPrice;
    }

    public function setTierPrice($tieredPrice)
    {
        $this->tieredPrice = $tieredPrice;
    }

    public function getId()
    {
        return $this->entity_id;
    }

    public function setId($id)
    {
        $this->entity_id = $id;
    }
}


class BasketItem
{
    protected $qty;
    protected $item;
    protected $name;

    public function getQty()
    {
        return $this->qty;
    }

    public function setQty($qty)
    {
        $this->qty = $qty;
    }

    /**
     * @return Item
     */
    public function getItem()
    {
        return $this->item;
    }

    public function setItem($item)
    {
        $this->item = $item;
    }
}

class BasketItemFactory
{
    public function create()
    {
        return new BasketItem();
    }
}

class Basket
{
    protected $basketItemFactory;
    protected $items = [];

    public function __construct(BasketItemFactory $basketItemFactory)
    {
        $this->basketItemFactory = $basketItemFactory;
    }


    public function getItems()
    {
        return $this->items;
    }

    public function addItem(AbstractItem $item)
    {
        if (empty($this->items) || !isset($this->items[$item->getId()])) {
            $basketItem = $this->basketItemFactory->create();
            $basketItem->setQty(1);
            $basketItem->setItem($item);
            $this->items[$item->getId()] = $basketItem;

        } else {
            /**
             * @var BasketItem $existing_item
             */
            $existing_item = $this->items[$item->getId()];
            $existing_item->setQty($existing_item->getQty() + 1);
        }

    }

    public function removeItem(AbstractItem $item)
    {
        if (empty($items) || !isset($items[$item->getId()])) {
            return;
        } else {
            /**
             * @var BasketItem $existing_item
             */
            $existing_item = $items[$item->getId()];
            if ($existing_item->getQty() > 0) {
                $existing_item->setQty($existing_item->getQty() - 1);
            }
        }
    }

}

class Item extends AbstractItem
{
    public function __construct($id, $price, $tiers = [])
    {
        $this->setId($id);
        $this->setPrice($price);
        $this->setTierPrice($tiers);
    }
}

/**
 * Class terminal
 */
class terminal implements TerminalInterface
{
    /**
     * @var float
     */
    private $total;
    /**
     * @var Basket
     */
    protected $basket;

    /**
     * terminal constructor.
     * @param Basket $basket
     */
    public function __construct(Basket $basket)
    {
        $this->total = 0.0000;
        $this->basket = $basket;
    }

    /**
     * @param AbstractItem $item
     */
    private function addItem(AbstractItem $item)
    {
        $this->basket->addItem($item);
    }

    /**
     * @param AbstractItem $item
     */
    public function scan(AbstractItem $item)
    {
        $this->addItem($item);
    }

    /**
     * Calulate cart totals
     */
    protected function calculateTotal()
    {
        $total = 0.0000;
        /**
         * @var BasketItem $item
         */
        foreach ($this->basket->getItems() as $item) {
            $tiers = $item->getItem()->getTierPrice();
            // Determine Tier Pricing
            if (!empty($tiers)) {
                foreach ($tiers as $tier => $price) {
                    //todo account better for mutliple tiers
                    if ($item->getQty() >= $tier) {
                        $floor = floor($item->getQty() / $tier);
                        $tier_price = $floor * $price;
                        $remainder = $item->getItem()->getPrice() * ($item->getQty() - ($floor * $tier));
                        $total += ($tier_price + $remainder);
                    } else {
                        $total += $item->getQty() * $item->getItem()->getPrice();
                    }
                }
            } else {
                $total += $item->getQty() * $item->getItem()->getPrice();
            }
        }
        $this->total = $total;
    }

    /**
     * @return float
     */
    public function getTotal()
    {
        $this->calculateTotal();
        return $this->total;
    }
}

// TEST CASES
$a = new Item('a', 2.0000, [4 => 7.0000]);
$b = new Item('b', 12.0000);
$c = new Item('c', 1.2500, [6 => 6.0000]);
$d = new Item('d', 0.1500);

$tests = [
    [
        'case' => [$a, $b, $c, $d, $a, $b, $a, $a],
        'result' => 32.40
    ],
    [
        'case' => [$c, $c, $c, $c, $c, $c, $c],
        'result' => 7.25,
    ],
    [
        'case' => [$a, $b, $c, $d],
        'result' => 15.40,
    ]
];

foreach ($tests as $test) {
    $terminal = new terminal(new Basket(new BasketItemFactory()));

    foreach ($test['case'] as $item) {
        $terminal->scan($item);
    }
    $total = $terminal->getTotal();
    echo $total;
    echo $total == $test['result'] ? '<span style="color: #0F0"> Pass </span>' : '<span style="color: #F00"> Fail </span>>';

}