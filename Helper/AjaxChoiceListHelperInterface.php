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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Helper for generate the AJAX data for the form choice list.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface AjaxChoiceListHelperInterface
{
    /**
     * Generates the ajax response.
     *
     * @param Request                            $request   The request
     * @param FormBuilderInterface|FormInterface $form      The choice loader or form or array
     * @param string                             $formChild The form child
     * @param string                             $prefix    The prefix of parameters
     *
     * @throws InvalidArgumentException When the format is not allowed
     * @throws NotFoundHttpException
     */
    public function generateResponse(Request $request, $form, string $formChild, string $prefix = ''): Response;

    /**
     * Generates the ajax values for the response.
     *
     * @param Request                            $request   The request
     * @param FormBuilderInterface|FormInterface $form      The choice loader or form or array
     * @param string                             $formChild The form child
     * @param string                             $prefix    The prefix of parameters
     *
     * @throws InvalidArgumentException When the format is not allowed
     * @throws NotFoundHttpException
     */
    public function generateValues(Request $request, $form, string $formChild, string $prefix = ''): array;

    /**
     * Gets the ajax data.
     */
    public function getData(Request $request, AjaxChoiceLoaderInterface $choiceLoader, AjaxChoiceListFormatterInterface $formatter, string $prefix = ''): array;
}
