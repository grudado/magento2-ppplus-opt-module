<?php

namespace Paypal\PaypalPlusBrasil\Api\Webhook;

interface WebhookManagementInterface
{
    /**
     * @param string $id
     * @param string $create_time
     * @param string $resource_type
     * @param string $event_version
     * @param string $event_type
     * @param string $summary
     * @param mixed $resource
     * @param mixed $links
     * @return mixed
     */
    public function handle($id, $create_time, $resource_type, $event_version, $event_type, $summary, $resource, $links);
}
