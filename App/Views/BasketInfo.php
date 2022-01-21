<?php

namespace App\Views;

use App\Entity\Basket\Basket;
use App\Entity\Basket\OrderAddress;
use App\Entity\Branch;
use App\Widget\DeliveryDateWidget;
use Src\Entity\Translation;
use Src\Entity\Variable;
use Src\Views\TextElement;
use Src\Views\ViewGroup;

class BasketInfo extends ViewGroup
{
    private Basket $basket;
    public function __construct(Basket $basket, $editable = false)
    {
        parent::__construct("div", "");
        $this->basket = $basket;
        $usePostcodeList = Variable::getByKey("use_postcode_list")->value->getValue();
        switch ($this->basket->type->getValue()) {
            case Basket::TYPE_DELIVERY:
            case null:
                /** @var OrderAddress */
                $address = OrderAddress::get([
                    "order" => $this->basket->ID->getValue()
                ]);
                $this->addField(
                    TextElement::create(
                        Translation::getTranslation("delivery_address")
                    )->setTagName("p")
                    ->addClass("text-primary fw-bold")
                );
                $this->addField(
                    TextElement::create(
                        $address
                    )->setTagName("p")
                );
                if ($usePostcodeList) {
                    if (!$editable) {
                        $this->addField(
                            TextElement::create(
                                Translation::getTranslation("delivery_date")
                            )->setTagName("p")
                            ->addClass("text-primary fw-bold")
                        );
                        $this->addField(
                            TextElement::create(
                                date("d-m-Y H:i", strtotime($this->basket->delivery_date->getValue()))
                            )->setTagName("p")
                        );
                    } else {
                        [$disabledDays, $availableDays] = $this->basket->getDeliveryDayInfo();
                        if (date("H") >= "18") {
                            $startOf = "+2 day";
                        } else {
                            $startOf = "tomorrow";
                        }
                        $this->addField(
                            (new DeliveryDateWidget("delivery_date", $startOf))
                            ->setLabel(Translation::getTranslation("delivery_date"))
                            ->setValue(
                                $this->basket->isDeliveryDayIsValid($this->basket->delivery_date->getValue()) ?
                                $this->basket->delivery_date->getValue() : null
                            )
                            ->addAttribute("data-days-of-week-disabled", json_encode($disabledDays))
                            ->addAttribute("required", "true")
                        );
                    }
                }
                break;
            case Basket::TYPE_COLLECTION:
                /** @var Branch */
                $branch = Branch::get($this->basket->branch->getValue());
                $this->addField(
                    TextElement::create(
                        Translation::getTranslation("collection_address")
                    )->setTagName("p")
                    ->addClass("text-primary fw-bold")
                );
                $this->addField(
                    TextElement::create(
                        $branch
                    )
                );
                $this->addField(
                    TextElement::create(
                        Translation::getTranslation("opening_hours")
                    )->setTagName("p")
                    ->addClass("text-primary fw-bold")
                );
                $this->addField(
                    TextElement::create(
                        $branch->opening_hours
                    )->setIsRaw(true)
                    ->setTagName("p")
                    ->addClass("text-info fw-bold")
                );
                if ($usePostcodeList) {
                    if (!$editable) {
                        $this->addField(
                            TextElement::create(
                                Translation::getTranslation("collection_date")
                            )->setTagName("p")
                            ->addClass("text-primary fw-bold")
                        );
                        $this->addField(
                            TextElement::create(
                                date("d-m-Y H:i", strtotime($this->basket->delivery_date->getValue()))
                            )->setTagName("p")
                        );
                    } else {
                        $this->addField(
                            (new DeliveryDateWidget("delivery_date"))
                            ->setLabel(Translation::getTranslation("collection_date"))
                            ->setValue(
                                $this->basket->isDeliveryDayIsValid($this->basket->delivery_date->getValue()) ?
                                $this->basket->delivery_date->getValue() : null
                            )
                            ->addAttribute("required", "true")
                        );
                    }
                }
                break;
        }
    }
}
