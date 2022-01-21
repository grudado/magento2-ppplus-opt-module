<?php

namespace Paypal\PaypalPlusBrasil\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Widget\Button;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class SaveWebhook extends Field
{
    /**
     * Remove scope label
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope();//->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Return element html
     *
     * @param AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $url = $this->getUrl('paypalplusbrasil/webhook/save', ['_current' => true]);
        $button = $this->getLayout()->createBlock(
            Button::class
        )->setData(
            [
                'id' => 'send_webhook_event',
                'label' => __('Send Webhook Event'),
                'button_url' => $url,
                'after_html' => $this->getJs($url),
                'type' => 'button'
            ]
        );

        return $button->toHtml();
    }

    private function getJs($url)
    {
        return <<<string
                <script type="text/x-magento-init">
                    {
                        "#send_webhook_event": {
                            "Paypal_PaypalPlusBrasil/js/system/config/saveWebhookType": {
                                "ajaxUrl": "$url"
                            }
                        }
                    }
                </script>
            string;
    }
}
