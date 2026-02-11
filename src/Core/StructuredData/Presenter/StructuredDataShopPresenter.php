<?php

namespace Oksydan\Module\IsThemeCore\Core\StructuredData\Presenter;

class StructuredDataShopPresenter implements StructuredDataPresenterInterface
{
    private $presentedData = [];
    private $shopData;
    private $context;

    public function __construct(\Context $context)
    {
        $this->context = $context;
    }

    public function present($data): array
    {
        $this->shopData = $data;

        $this->presentShopData();

        return $this->presentedData;
    }

    private function presentShopData(): void
    {
        $this->presentedData['@context'] = 'http://schema.org';
        $this->presentedData['@type'] = 'Bakery';
        $this->presentedData['name'] = $this->shopData['name'];
        $this->presentedData['url'] = $this->context->link->getPageLink('index');
        $this->presentedData['priceRange'] = '$ - $$';
        $this->presentedData['servesCuisine'] = 'Italian';
        $this->presentedData['logo'] = [
            '@type' => 'ImageObject',
            'url' => $this->shopData['logo'],
        ];
        $this->presentedData['image'] = [
            '@type' => 'ImageObject',
            'url' => $this->shopData['logo'],
        ];

        if ($this->shopData['phone']) {
            $this->presentedData['contactPoint'] = [
                '@type' => 'ContactPoint',
                'telephone' => $this->shopData['phone'],
                'contactType' => 'customer service',
                'email' => $this->shopData['email'],

            ];
            $this->presentedData['telephone'] = $this->shopData['phone'];
        }

        $address = $this->shopData['address'];
        $postalCode = $address['postcode'];
        $city = $address['city'];
        $country = $address['country'];
        $addressRegion = $address['state'];
        $streetAddress = $address['address1'];

        if ($postalCode || $city || $country || $addressRegion || $streetAddress) {
            $this->presentedData['address'] = [
                '@type' => 'PostalAddress',
            ];

            if ($postalCode) {
                $this->presentedData['address']['postalCode'] = $postalCode;
            }
            if ($streetAddress) {
                $this->presentedData['address']['streetAddress'] = $streetAddress;
            }
            if ($country || $city) {
                $addressLocality = '';
                if ($city) {
                    $addressLocality = $city;
                }
                if ($addressRegion) {
                    $addressLocality .= ($addressLocality != '' ? ', ' : '') . $addressRegion;
                }

                $this->presentedData['address']['addressLocality'] = $addressLocality;
				$this->presentedData['address']['addressCountry'] = $country;
            }
        }
    }
}
