<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('einvoice_error_dispatcher')) {
    function einvoice_error_dispatcher($result)
    {
        if (isset($result->rejectedDocuments)) {
            return einvoice_error_readable($result);
        }

        if (isset($result->validationResults)) {
            return einvoice_validation_error_readable($result);
        }

        return 'Unknown MyInvois error format.';
    }
}

if (!function_exists('einvoice_error_readable')) {
    function einvoice_error_readable($result) {

        $messages = [];

        if (isset($result->rejectedDocuments) && is_array($result->rejectedDocuments)) {
            foreach ($result->rejectedDocuments as $doc) {
                $invoice_no = $doc->invoiceCodeNumber ?? 'Unknown';
                $doc_errors = [];

                if (isset($doc->error->details) && is_array($doc->error->details)) {
                    foreach ($doc->error->details as $detail) {
                        $field = $detail->target ?? 'Unknown field';
                        $msg   = $detail->message ?? 'Unknown error';

                        // Simplify technical field names
                        $field_labels = [
                            'LineItem_TaxType'              => 'Line Item Tax Type',
                            'ContactNumber'                 => 'Company Phone (Config Management)',
                            'MSICCode'                      => 'Company Msic (Config Management)',
                            'BusinessActivityDescription'   => 'Company Msic Desc (Config Management)',
                            'Name'                          => 'Company Full Name (Config Management)',
                            'add1'                          => 'Company Addr 1 (Config Management)',
                            'IdValue'                       => 'Company Brn (Config Management)',
                            'CityName'                      => 'Company City (Config Management)',
                            'Country'                       => 'Company Country (Config Management)'
                        ];
                        
                        if (isset($field_labels[$field])) {
                            $field = $field_labels[$field];
                        }

                        $doc_errors[] = "- {$msg} (Field: {$field})";
                    }
                } else {
                    $doc_errors[] = "- " . ($doc->error->message ?? 'Unknown error');
                }

                $messages[] = "Invoice {$invoice_no}:\n" . implode("\n", $doc_errors);
            }
        } else {
            $messages[] = "Unknown error occurred or no response from MyInvois.";
        }

        return implode("\n\n", $messages);
    }
}

if (!function_exists('einvoice_validation_error_readable')) {

    function einvoice_validation_error_readable($result)
    {
        $messages = [];

        if (isset($result->internalId)) {
            $messages[] = "Invoice {$result->internalId}:";
        }

        if (
            !isset($result->validationResults->validationSteps) ||
            !is_array($result->validationResults->validationSteps)
        ) {
            return 'Unknown validation error or invalid MyInvois response.';
        }

        foreach ($result->validationResults->validationSteps as $step) {

            if (($step->status ?? '') !== 'Invalid') {
                continue;
            }

            $stepName = $step->name ?? 'Unknown Validation Step';
            $error    = $step->error ?? null;

            $messages[] = "{$stepName}:";

            if (!$error) {
                $messages[] = "- Unknown error.";
                continue;
            }

            $innerErrors = [];

            if (isset($error->innerError)) {
                $innerErrors = is_array($error->innerError)
                    ? $error->innerError
                    : [$error->innerError];
            }

            if ($innerErrors) {
                foreach ($innerErrors as $inner) {

                    $field = $inner->propertyName ?? 'Unknown Field';
                    $msg   = $inner->error ?? 'Unknown error';

                    $fieldLabels = [
                        'CustomerIdentity'           => 'Customer Identity (NRIC)',
                        'ShippingRecipientIdentity' => 'Shipping Recipient Identity (NRIC)',
                    ];

                    if (isset($fieldLabels[$field])) {
                        $field = $fieldLabels[$field];
                    }

                    $messages[] = "- {$msg} (Field: {$field})";
                }
            } else {
                $messages[] = "- " . ($error->error ?? 'Unknown error');
            }
        }

        return implode("\n", $messages);
    }
}
