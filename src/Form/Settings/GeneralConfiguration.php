<?php

declare(strict_types=1);

namespace Oksydan\Module\IsThemeCore\Form\Settings;

use PrestaShop\PrestaShop\Core\Configuration\AbstractMultistoreConfiguration;
use PrestaShopBundle\Service\Form\MultistoreCheckboxEnabler;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Configuration is used to save data to configuration table and retrieve from it
 */
final class GeneralConfiguration extends AbstractMultistoreConfiguration
{
    private const CONFIGURATION_FIELDS = [
        'list_display_settings',
        'early_hints',
        'preload_css',
        'load_party_town',
        'debug_party_town',
        'show_product_details',
        'google_maps_api_key'
    ];

    /**
     * @var string
     */
    public const THEMECORE_DISPLAY_LIST = 'THEMECORE_DISPLAY_LIST';
    public const THEMECORE_EARLY_HINTS = 'THEMECORE_EARLY_HINTS';
    public const THEMECORE_PRELOAD_CSS = 'THEMECORE_PRELOAD_CSS';
    public const THEMECORE_LOAD_PARTY_TOWN = 'THEMECORE_LOAD_PARTY_TOWN';
    public const THEMECORE_DEBUG_PARTY_TOWN = 'THEMECORE_DEBUG_PARTY_TOWN';
    public const THEMECORE_SHOW_PRODUCT_DETAILS = 'THEMECORE_SHOW_PRODUCT_DETAILS';
    public const THEMECORE_GOOGLE_MAPS_API_KEY = 'THEMECORE_GOOGLE_MAPS_API_KEY';

    /**
     * @var array<string, string>
     */
    private array $fields = [
        'list_display_settings' => self::THEMECORE_DISPLAY_LIST,
        'early_hints' => self::THEMECORE_EARLY_HINTS,
        'preload_css' => self::THEMECORE_PRELOAD_CSS,
        'load_party_town' => self::THEMECORE_LOAD_PARTY_TOWN,
        'debug_party_town' => self::THEMECORE_DEBUG_PARTY_TOWN,
        'show_product_details' => self::THEMECORE_SHOW_PRODUCT_DETAILS,
        'google_maps_api_key' => self::THEMECORE_GOOGLE_MAPS_API_KEY,
    ];

    /**
     * {@inheritdoc}
     *
     * @return array<string, mixed>
     */
    public function getConfiguration(): array
    {
        $configurationValues = [];

        foreach ($this->fields as $field => $configurationKey) {
            $configurationValues[$field] = $this->configuration->get($configurationKey);
        }

        return $configurationValues;
    }

    /**
     * {@inheritdoc}
     *
     * @param array<string, mixed> $configuration
     *
     * @return array<int, array<string, mixed>>
     */
    public function updateConfiguration(array $configuration): array
    {
        $errors = [];

        if (!$this->validateConfiguration($configuration)) {
            $errors[] = [
                'key' => 'Invalid configuration',
                'parameters' => [],
                'domain' => 'Admin.Notifications.Warning',
            ];
        } else {
            $shopConstraint = $this->getShopConstraint();

            try {
                foreach ($this->fields as $field => $configurationKey) {
                    $this->updateConfigurationValue($configurationKey, $field, $configuration, $shopConstraint);
                }
            } catch (\Exception $exception) {
                $errors[] = [
                    'key' => $exception->getMessage(),
                    'parameters' => [],
                    'domain' => 'Admin.Notifications.Warning',
                ];
            }
        }

        return $errors;
    }

    /**
     * Ensure the parameters passed are valid.
     *
     * @param array<string, mixed> $configuration
     *
     * @return bool Returns true if no exception are thrown
     */
    public function validateConfiguration(array $configuration): bool
    {
        foreach ($this->fields as $field => $configurationKey) {
            $multistoreKey = MultistoreCheckboxEnabler::MULTISTORE_FIELD_PREFIX . $field;
            $this->fields[$multistoreKey] = '';
        }

        foreach ($configuration as $key => $value) {
            if (!key_exists($key, $this->fields)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return OptionsResolver
     */
    protected function buildResolver(): OptionsResolver
    {
        return (new OptionsResolver())
            ->setDefined(self::CONFIGURATION_FIELDS)
            ->setAllowedTypes('list_display_settings', ['string', 'null'])
            ->setAllowedTypes('early_hints', 'bool')
            ->setAllowedTypes('preload_css', 'bool')
            ->setAllowedTypes('load_party_town', 'bool')
            ->setAllowedTypes('debug_party_town', 'bool')
            ->setAllowedTypes('show_product_details', 'bool')
            ->setAllowedTypes('google_maps_api_key', 'string');
    }
}
