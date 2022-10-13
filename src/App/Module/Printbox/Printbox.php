<?php

declare( strict_types = 1 );

namespace App\Modules;

class Printbox {

    private int $customerId = 76093; // gergo.harkaly@paperstories.hu
    private string $API_URL = 'https://paperstories-eu-pbx2.getprintbox.com/api/ec/v4/';

    public function __construct()
    {
        // do nothing
    }

    public function init(): void
    {
        $this->setActions();
    }

    public function setActions()
    {
        add_action('woocommerce_order_status_changed', [$this, 'updateCustomerId'], 10, 3);
    }

    public function updateCustomerId($orderId, $oldStatus, $newStatus)
    {
        if ($newStatus === 'processing') {
            /** @var \WC_Order $order */
            $order = wc_get_order( $orderId );
            $orderItems = $order->get_items();
            echo '<pre>';
            foreach ($orderItems as $orderItem) {
                /** @var \WC_Order_Item_Product $orderItem */
                $orderItemMetaDatas = $orderItem->get_meta_data();
                /** @var \WC_Meta_Data $orderItemMetaData */
                foreach ($orderItemMetaDatas as $orderItemMetaData) {
                    if ($orderItemMetaData->get_data()['key']==='Projekt azonosító') {
                        $projectHash = $orderItemMetaData->get_data()['value'];
                        $this->validate($projectHash, 'validate');
                    }
                }
            }
            echo '</pre>';
            exit;
        }
    }

    public function validate($projectHash, $todo)
    {
        switch ($todo) {
            case 'validate':
            {
                $method = 'GET';
                $urlPart = '/validate';
                $payload = [];
                break;
            }
            case 'view':
            {
                $method = 'GET';
                $urlPart = '';
                $payload = [];
                break;
            }
            case 'update':
            {
                $method = 'PATCH';
                $urlPart = '';
                $payload = ['customer_id'=> $this->customerId];
                break;
            }
            case 'render':
            {
                $method = 'POST';
                $urlPart = '/render';
                $payload = ["preflight" => true];
                break;
            }
        }

        $url = $this->API_URL.'/projects/'.$projectHash.$urlPart.'/';

        $result = $this->sendCurl($url, [], $this->getRequestHeader(), $method, $payload);
    }

}
