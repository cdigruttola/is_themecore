<?php

declare(strict_types=1);

namespace Oksydan\Module\IsThemeCore\Controller\Admin;

use Oksydan\Module\IsThemeCore\Core\Htaccess\HtaccessGenerator;
use Oksydan\Module\IsThemeCore\Core\Webp\WebpFilesEraser;
use PrestaShop\PrestaShop\Core\Form\FormHandlerInterface;
use PrestaShop\PrestaShop\Core\Form\Handler;
use PrestaShopBundle\Controller\Admin\PrestaShopAdminController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SettingsController
 */
class SettingsController extends PrestaShopAdminController
{
    public function __construct(private readonly HtaccessGenerator $htaccessGenerator)
    {
    }


    /**
     * #[AdminSecurity("is_granted('read', request.get('_legacy_controller'))", message="You do not have permission to access this.)]
     *
     * @return Response
     */
    public function indexAction(
        #[Autowire(service: 'oksydan.module.is_themecore.form.settings.general_form_data_handler')]
        Handler $generalFormDataHandler,
        #[Autowire(service: 'oksydan.module.is_themecore.form.settings.webp_form_data_handler')]
        Handler $webpFormDataHandler,
    ): Response {
        /** @var FormInterface<string, mixed> $generalForm */
        $generalForm = $generalFormDataHandler->getForm();
        $webpForm = $webpFormDataHandler->getForm();

        return $this->render('@Modules/is_themecore/views/templates/back/components/layouts/settings.html.twig', [
            'general_form' => $generalForm->createView(),
            'webp_form' => $webpForm->createView(),
        ]);
    }

    /**
     *  #[AdminSecurity(
     *      "is_granted('update', request.get('_legacy_controller')) && is_granted('create', request.get('_legacy_controller')) && is_granted('delete', request.get('_legacy_controller'))",
     *      message="You do not have permission to update this.",
     *      redirectRoute="is_themecore_module_settings"
     * )]
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws \LogicException
     */
    public function processGeneralFormAction(
        Request $request,
        #[Autowire(service: 'oksydan.module.is_themecore.form.settings.general_form_data_handler')]
        Handler $generalFormDataHandler,
    ) {
        return $this->processForm(
            $request,
            $generalFormDataHandler,
        );
    }

    /**
     * #[AdminSecurity(
     *      "is_granted('update', request.get('_legacy_controller')) && is_granted('create', request.get('_legacy_controller')) && is_granted('delete', request.get('_legacy_controller'))",
     *      message="You do not have permission to update this.",
     *      redirectRoute="is_themecore_module_settings"
     * )]
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws \LogicException
     */
    public function processWebpFormAction(
        Request $request,
        #[Autowire(service: 'oksydan.module.is_themecore.form.settings.webp_form_data_handler')]
        Handler $webpFormDataHandler,
    ) {
        return $this->processForm(
            $request,
            $webpFormDataHandler
        );
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws \LogicException
     */
    public function processWebpEraseImages(
        Request $request,
        #[Autowire(service: 'oksydan.module.is_themecore.core.webp.webp_files_eraser')]
        WebpFilesEraser $eraser,
    ) {
        $time_start = microtime(true);

        switch ($request->get('type')) {
            case 'all':
                $eraser->setQuery(_PS_ROOT_DIR_);
                break;
            case 'product':
                $eraser->setQuery(_PS_PRODUCT_IMG_DIR_);
                break;
            case 'module':
                $eraser->setQuery(_PS_MODULE_DIR_);
                break;
            case 'cms':
                $eraser->setQuery(_PS_IMG_DIR_ . 'cms/');
                break;
            case 'themes':
                $eraser->setQuery(_PS_ROOT_DIR_ . '/themes/');
                break;
            default:
                $eraser->setQuery(_PS_ROOT_DIR_);
                break;
        }

        $eraser->eraseFiles();

        $time_end = microtime(true);
        $execution_time = round($time_end - $time_start, 2);

        $this->addFlash('success', $this->trans('%1$s - webp images has been erased successfully in %2$ss', [$eraser->getFilesCount(), $execution_time], 'Modules.Isthemecore.Admin'));

        return $this->redirectToRoute('is_themecore_module_settings');
    }

    /**
     * Process form.
     *
     * @param Request $request
     * @param FormHandlerInterface $formHandler
     * @param string $hookName
     *
     * @return RedirectResponse
     */
    private function processForm(Request $request, FormHandlerInterface $formHandler)
    {
        $form = $formHandler->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $data = $form->getData();
                $saveErrors = $formHandler->save($data);

                if (!empty($data['webp_enabled'])) {
                    $this->htaccessGenerator->generate((bool) $data['webp_enabled']);
                    $this->htaccessGenerator->writeFile();
                }

                if (0 === count($saveErrors)) {
                    $this->addFlash('success', $this->trans('Successful update.', [], 'Admin.Notifications.Success'));
                } else {
                    $this->addFlashErrors($saveErrors);
                }
            }

            $formErrors = [];
            foreach ($form->getErrors(true) as $error) {
                $formErrors[] = $error->getMessage();
            }
            $this->addFlashErrors($formErrors);
        }

        return $this->redirectToRoute('is_themecore_module_settings');
    }
}
