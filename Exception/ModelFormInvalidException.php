<?php

declare(strict_types=1);

namespace HandcraftedInTheAlps\Bundle\SuluResourceBundle\Exception;

use RuntimeException;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;

class ModelFormInvalidException extends RuntimeException
{
    /**
     * @var FormInterface<mixed>
     */
    protected $form;

    /**
     * @param FormInterface<mixed> $form
     */
    public function __construct(FormInterface $form)
    {
        $this->form = $form;

        parent::__construct(
            sprintf(
                'Invalid form data for "%s" on form "%s": ' . json_encode($this->getErrors(), \JSON_PRETTY_PRINT),
                \get_class($this->form->getData()),
                \get_class($this->form)
            )
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $message = '';

        foreach ($this->getErrors() as $fieldName => $field) {
            $message .= ucfirst($fieldName) . ': ' . implode(',', $field['messages']) . \PHP_EOL;
        }

        return [
            'code' => $this->code,
            'message' => trim($message),
            'errors' => $this->getErrors(),
        ];
    }

    /**
     * @return FormInterface<mixed>
     */
    public function getForm(): FormInterface
    {
        return $this->form;
    }

    /**
     * @return mixed[]
     */
    public function getErrors(): array
    {
        $errors = [];

        /** @var FormError $error */
        foreach ($this->form->getErrors(true) as $key => $error) {
            $origin = $error->getOrigin();

            if (null === $origin) {
                continue;
            }

            $field = $origin->getName();

            if (!isset($errors[$field])) {
                $data = $origin->getData();
                $viewData = $origin->getViewData();

                $errors[$field] = [
                    'value' => is_scalar($data) ? $data : $viewData,
                    'messages' => [],
                ];
            }

            $errors[$field]['messages'][] = $error->getMessage();
        }

        return $errors;
    }
}
