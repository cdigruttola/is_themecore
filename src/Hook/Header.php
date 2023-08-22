<?php

namespace Oksydan\Module\IsThemeCore\Hook;

use Media;
use Oksydan\Module\IsThemeCore\Core\Breadcrumbs\ThemeBreadcrumbs;
use Oksydan\Module\IsThemeCore\Core\ListingDisplay\ThemeListDisplay;
use Oksydan\Module\IsThemeCore\Core\Partytown\PartytownScript;
use Oksydan\Module\IsThemeCore\Core\Partytown\PartytownScriptUriResolver;
use Oksydan\Module\IsThemeCore\Core\StructuredData\BreadcrumbStructuredData;
use Oksydan\Module\IsThemeCore\Core\StructuredData\Presenter\StructuredDataPresenterInterface;
use Oksydan\Module\IsThemeCore\Core\StructuredData\Presenter\StructuredDataProductPresenter;
use Oksydan\Module\IsThemeCore\Core\StructuredData\ProductStructuredData;
use Oksydan\Module\IsThemeCore\Core\StructuredData\ShopStructuredData;
use Oksydan\Module\IsThemeCore\Core\StructuredData\StructuredDataInterface;
use Oksydan\Module\IsThemeCore\Core\StructuredData\WebsiteStructuredData;
use Oksydan\Module\IsThemeCore\Form\Settings\GeneralConfiguration;
use Oksydan\Module\IsThemeCore\Form\Settings\WebpConfiguration;

class Header extends AbstractHook
{
    public const HOOK_LIST = [
        'displayHeader',
        'actionBuildFrontEndObject',
        'actionFrontControllerSetVariables',
        'displayMetadataMiniature'
    ];

    public function hookDisplayMetadataMiniature($params) {
        return $this->getStructuredDataForListing($params['product']);
    }

    public function hookActionBuildFrontEndObject($params) {
        $params['obj']['configuration']['google'] = \Configuration::get(GeneralConfiguration::THEMECORE_GOOGLE_MAPS_API_KEY);
    }

    public function hookActionFrontControllerSetVariables($params) {
        return ['show_product_details' => \Configuration::get(GeneralConfiguration::THEMECORE_SHOW_PRODUCT_DETAILS)];
    }

    public function hookDisplayHeader(): string
    {
        $themeListDisplay = new ThemeListDisplay();
        $breadcrumbs = (new ThemeBreadcrumbs())->getBreadcrumb();

        if ($breadcrumbs['count']) {
            $this->context->smarty->assign([
                'breadcrumb' => $breadcrumbs,
            ]);
        }

        $this->context->smarty->assign([
            'listingDisplayType' => $themeListDisplay->getDisplay(),
            'preloadCss' => \Configuration::get(GeneralConfiguration::THEMECORE_PRELOAD_CSS),
            'webpEnabled' => \Configuration::get(WebpConfiguration::THEMECORE_WEBP_ENABLED),
            'jsonData' => $this->getStructuredData(),
            'loadPartytown' => (bool) \Configuration::get(GeneralConfiguration::THEMECORE_LOAD_PARTY_TOWN),
            'debugPartytown' => (bool) \Configuration::get(GeneralConfiguration::THEMECORE_DEBUG_PARTY_TOWN),
            'partytownScript' => $this->getPartytownScript(),
            'partytownScriptUri' => $this->getPartytownScriptUri(),
        ]);

        $this->context->controller->addJS($this->module->getPathUri() . 'views/js/lang.js');
        Media::addJsDef(
            [
                'lang' => $this->context->language
            ]
        );

        return $this->module->fetch('module:is_themecore/views/templates/hook/head.tpl');
    }

    private function getPartytownScriptUri(): string
    {
        try {
            $uriResolver = $this->module->get(PartytownScriptUriResolver::class);
        } catch (\Exception $e) {
            $uriResolver = null;
        }

        if ($uriResolver) {
            return $uriResolver->getScriptUri();
        }

        return '';
    }

    private function getPartytownScript(): string
    {
        try {
            $partytownScript = $this->module->get(PartytownScript::class);
        } catch (\Exception $e) {
            $partytownScript = null;
        }

        if ($partytownScript instanceof PartytownScript) {
            return $partytownScript->getScriptContent();
        }

        return '';
    }

    private function getStructuredData(): array
    {
        $dataArray = [];

        if ($this->context->controller instanceof \ProductControllerCore && $this->context->controller->getProduct()->id !== null) {
            try {
                $productData = $this->module->get(ProductStructuredData::class);
            } catch (\Exception $e) {
                $productData = null;
            }

            if ($productData instanceof StructuredDataInterface) {
                $dataArray[] = $productData->getFormattedData();
            }
        }

        try {
            $breadcrumbData = $this->module->get(BreadcrumbStructuredData::class);
        } catch (\Exception $e) {
            $breadcrumbData = null;
        }

        if ($breadcrumbData instanceof StructuredDataInterface) {
            $dataArray[] = $breadcrumbData->getFormattedData();
        }

        try {
            $shopData = $this->module->get(ShopStructuredData::class);
        } catch (\Exception $e) {
            $shopData = null;
        }

        if ($shopData instanceof StructuredDataInterface) {
            $dataArray[] = $shopData->getFormattedData();
        }

        if ($this->context->controller->getPageName() === 'index') {
            try {
                $website = $this->module->get(WebsiteStructuredData::class);
            } catch (\Exception $e) {
                $website = null;
            }

            if ($website instanceof StructuredDataInterface) {
                $dataArray[] = $website->getFormattedData();
            }
        }

        return $dataArray;
    }

    /**
     * @param $presentedProduct
     * @return string
     * @throws \Exception
     */
    private function getStructuredDataForListing($presentedProduct) : string
    {
        $data = '';
        try {
            $productData = $this->module->get(StructuredDataProductPresenter::class);
        } catch (\Exception $e) {
            $productData = null;
        }

        if ($productData instanceof StructuredDataPresenterInterface) {
            $jsonData = $productData->present($presentedProduct);
            if (!empty($jsonData)) {
                $data = json_encode($jsonData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }
        }
        $this->context->smarty->assign([
            'jsonElem' => $data,
        ]);

        return $this->module->fetch('module:is_themecore/views/templates/hook/metadataMiniature.tpl');
    }
}
