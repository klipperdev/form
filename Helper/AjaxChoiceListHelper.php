<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Form\Helper;

use Klipper\Component\Form\ChoiceList\Formatter\AjaxChoiceListFormatterInterface;
use Klipper\Component\Form\ChoiceList\Loader\AjaxChoiceLoaderInterface;
use Symfony\Component\Form\Exception\InvalidArgumentException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Helper for generate the AJAX data for the form choice list.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class AjaxChoiceListHelper implements AjaxChoiceListHelperInterface
{
    public function generateResponse(Request $request, $form, ?string $formChild = null, string $prefix = ''): Response
    {
        return $this->extractAjaxFormatter($form, $formChild)
            ->formatResponse($this->generateValues($request, $form, $formChild, $prefix))
        ;
    }

    public function generateValues(Request $request, $form, ?string $formChild = null, string $prefix = ''): array
    {
        if (!$form instanceof FormBuilderInterface && !$form instanceof FormInterface) {
            throw new UnexpectedTypeException($form, FormInterface::class);
        }

        $formatter = $this->extractAjaxFormatter($form, $formChild);
        $choiceLoader = $this->extractChoiceLoader($form, $formChild);

        if (!$choiceLoader instanceof AjaxChoiceLoaderInterface) {
            throw new UnexpectedTypeException($choiceLoader, AjaxChoiceLoaderInterface::class);
        }

        return $this->getData($request, $choiceLoader, $formatter, $prefix);
    }

    public function getData(Request $request, AjaxChoiceLoaderInterface $choiceLoader, AjaxChoiceListFormatterInterface $formatter, string $prefix = ''): array
    {
        $ajaxIds = $request->get($prefix.'ids', '');

        if (\is_string($ajaxIds) && '' !== $ajaxIds) {
            $ajaxIds = explode(',', $ajaxIds);
        } elseif (!\is_array($ajaxIds) || \in_array($ajaxIds, [null, ''], true)) {
            $ajaxIds = [];
        }

        $choiceLoader->setPageSize((int) ($request->get($prefix.'limit', $choiceLoader->getPageSize())));
        $choiceLoader->setPageNumber((int) ($request->get($prefix.'page', $choiceLoader->getPageNumber())));
        $choiceLoader->setSearch($request->get($prefix.'q', ''));
        $choiceLoader->setIds($ajaxIds);
        $choiceLoader->reset();

        return $formatter->formatResponseData($choiceLoader);
    }

    /**
     * Extracts the ajax choice loader.
     *
     * @param FormBuilderInterface|FormInterface $form
     *
     * @throws InvalidArgumentException When the choice list is not an instance of AjaxChoiceListInterface
     */
    protected function extractChoiceLoader($form, ?string $formChild = null): AjaxChoiceLoaderInterface
    {
        $form = $this->getForm($form, $formChild);

        return $form->getAttribute('choice_loader', $form->getOption('choice_loader'));
    }

    /**
     * Extracts the ajax formatter.
     *
     * @param FormBuilderInterface|FormInterface $form
     *
     * @throws InvalidArgumentException When the ajax_formatter is not an instance of AjaxChoiceListFormatterInterface
     */
    protected function extractAjaxFormatter($form, ?string $formChild = null): AjaxChoiceListFormatterInterface
    {
        $form = $this->getForm($form, $formChild);
        $formatter = $form->getOption('ajax_formatter');

        if (!$formatter instanceof AjaxChoiceListFormatterInterface) {
            throw new UnexpectedTypeException($formatter, AjaxChoiceListFormatterInterface::class);
        }

        return $formatter;
    }

    /**
     * Get the form builder.
     *
     * @param FormBuilderInterface|FormInterface $form The form
     */
    protected function getForm($form, ?string $formChild = null): FormConfigInterface
    {
        if (null !== $formChild && !$form->has($formChild)) {
            throw new NotFoundHttpException();
        }

        $value = null !== $formChild ? $form->get($formChild) : $form;

        return $value instanceof FormInterface
            ? $value->getConfig()
            : $value;
    }
}
