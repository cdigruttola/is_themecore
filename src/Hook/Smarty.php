<?php

namespace Oksydan\Module\IsThemeCore\Hook;

class Smarty extends AbstractHook
{
    public const HOOK_LIST = [
        'actionDispatcherBefore',
    ];

    public function hookActionDispatcherBefore(): void
    {
        if (!isset($this->context->smarty->registered_plugins['function']['generateImagesSources'])) {
            $this->context->smarty->registerPlugin('function', 'generateImagesSources', ['Oksydan\Module\IsThemeCore\Core\Smarty\SmartyHelperFunctions', 'generateImagesSources']);
        }

        if (!isset($this->context->smarty->registered_plugins['function']['generateImageSvgPlaceholder'])) {
            $this->context->smarty->registerPlugin('function', 'generateImageSvgPlaceholder', ['Oksydan\Module\IsThemeCore\Core\Smarty\SmartyHelperFunctions', 'generateImageSvgPlaceholder']);
        }

        if (!isset($this->context->smarty->registered_plugins['function']['appendParamToUrl'])) {
            $this->context->smarty->registerPlugin('function', 'appendParamToUrl', ['Oksydan\Module\IsThemeCore\Core\Smarty\SmartyHelperFunctions', 'appendParamToUrl']);
        }

        if (!isset($this->context->smarty->registered_plugins['block']['images_block'])) {
            $this->context->smarty->registerPlugin('block', 'images_block', ['Oksydan\Module\IsThemeCore\Core\Smarty\SmartyHelperFunctions', 'imagesBlock']
            );
        }

        if (!isset($this->context->smarty->registered_plugins['block']['cms_images_block'])) {
            $this->context->smarty->registerPlugin('block', 'cms_images_block', ['Oksydan\Module\IsThemeCore\Core\Smarty\SmartyHelperFunctions', 'cmsImagesBlock']);
        }

        if (!isset($this->context->smarty->registered_plugins['block']['display_mobile'])) {
            $this->context->smarty->registerPlugin('block', 'display_mobile', ['Oksydan\Module\IsThemeCore\Core\Smarty\SmartyHelperFunctions', 'displayMobileBlock']);
        }

        if (!isset($this->context->smarty->registered_plugins['block']['display_desktop'])) {
            $this->context->smarty->registerPlugin('block', 'display_desktop', ['Oksydan\Module\IsThemeCore\Core\Smarty\SmartyHelperFunctions', 'displayDesktopBlock']);
        }
    }
}
