<?php

namespace Paypal\PaypalPlusBrasil\Gateway\CreditCard\Validator;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Paypal\PaypalPlusBrasil\Logger\Logger;

class CancelValidator extends AbstractValidator
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param ResultInterfaceFactory $resultFactory
     * @param Logger $logger
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        Logger $logger
    ) {
        $this->logger = $logger;

        parent::__construct($resultFactory);
    }

    /**
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {
        $response = SubjectReader::readResponse($validationSubject);

        $errorMessages = [];
        $isValid = true;

        if (isset($response['response']['state'])) {
            $state = $response['response']['state'];
            if (strtolower($state) !== 'voided') {
                $isValid = false;
                $errorMessages[] = __("The cancel was denied.");
            }
        }

        if (!$response['success']) {
            $isValid = false;
            $errorMessages[] = $response['msg'];
        }

        return $this->createResult($isValid, $errorMessages);
    }
}
