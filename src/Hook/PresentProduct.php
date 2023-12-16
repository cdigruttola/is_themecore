<?php

namespace Oksydan\Module\IsThemeCore\Hook;

use PrestaShop\PrestaShop\Core\Localization\Locale;

class PresentProduct extends AbstractHook
{
    public const HOOK_LIST = [
        'actionPresentProduct',
        'actionPresentProductListing',
    ];

    public function hookActionPresentProduct($params)
    {
        $this->getPriceLowerDecimal($params);
    }

    public function hookActionPresentProductListing($params)
    {
        $this->getPriceLowerDecimal($params);
    }

    /**
     * @param $params
     * @return void
     * @throws \PrestaShop\PrestaShop\Core\Localization\Exception\LocalizationException
     */
    private function getPriceLowerDecimal($params): void
    {
        $price = $params['presentedProduct']['price'];

        $priceSpecification = $this->context->currentLocale->getPriceSpecification($this->context->currency->iso_code);

        $symbols = $priceSpecification->getSymbolsByNumberingSystem(Locale::NUMBERING_SYSTEM_LATIN);
        $decimal_char = $symbols->getDecimal();

        $price = str_replace($decimal_char, $decimal_char . '<span class="decimal">', $price) . '</span>';
        $params['presentedProduct']['price_with_lower_decimal'] = $price;
    }

}
